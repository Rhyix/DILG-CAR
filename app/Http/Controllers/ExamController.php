<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\JobVacancy;
use App\Models\ExamDetail;
use App\Models\Applications;
use App\Models\ExamItems;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Jobs\SendExamNotification;

class ExamController extends Controller
{
    public function submit(Request $request, $vacancy_id)
    {    //dd($request->all());

        $validated = $request->validate([
            'vacancy_id' => 'required|string',
            'user_id' => 'required|integer',
            'answers' => 'nullable|array',
        ]);

        $answerRecord = Applications::where('vacancy_id', $validated['vacancy_id'])
            ->where('user_id', $validated['user_id'])
            ->firstOrFail();

        // Time Validation (Allow 2 minutes grace period for network latency)
        if ($answerRecord->exam_end_time) {
            $endTime = \Carbon\Carbon::parse($answerRecord->exam_end_time)->addMinutes(2);
            if (now()->gt($endTime)) {
                Log::warning('Late exam submission detected', [
                    'user_id' => $validated['user_id'], 
                    'vacancy_id' => $vacancy_id,
                    'delay_seconds' => now()->diffInSeconds($endTime)
                ]);
                // We still accept it because the client might have auto-submitted late due to lag,
                // but strictly speaking we could reject answers if we wanted to be harsh.
                // For now, we allow it to ensure data isn't lost.
            }
        }

        // Update the answers field
        $answerRecord->answers = $validated['answers'];
        $answerRecord->status = 'submitted'; // Ensure status is updated to 'submitted'
        $answerRecord->save();

        //$message = "submitted successfully";
        //info($message);

        activity()
            ->causedBy(auth()->user())
            ->event('submit')
            ->withProperties(['vacancy_id' => $vacancy_id, 'user_id' => $validated['user_id'], 'section' => 'Exam'])
            ->log('Submitted exam answers.');

        return redirect()->route('user.exam_thankyou', compact('vacancy_id', ));
    }


    public function logSwitch(Request $request)
    {
        Log::info('User switched tab at ' . $request->input('time'));

        activity()
            ->causedBy(auth()->user())
            ->event('switch tab')
            ->withProperties(['time' => $request->input('time'), 'section' => 'Exam'])
            ->log('Switched browser tab during exam.');


        return response()->noContent();
    }

    public function editExam(Request $request, $vacancy_id)
    {
        //info('edit_exam');
        $exam_items = ExamItems::where('vacancy_id', $vacancy_id)->get();
        $vacancy = JobVacancy::select('position_title', 'vacancy_type')->where('vacancy_id', $vacancy_id)->firstOrFail();

        activity()
            ->causedBy(auth('admin')->user())
            ->event('view')
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
            ->log('Accessed edit exam page.');

        return view('admin.exam_edit', ['exam_items' => $exam_items, 'vacancy_id' => $vacancy_id, 'vacancy' => $vacancy]);
    }

    public function updateExam(Request $request, $vacancy_id)
    {
        // Handle both form-encoded and JSON requests
        $raw = $request->input('questions') ?? $request->getContent();
        
        // If it's a JSON request, parse the JSON body
        if ($request->isJson() && is_null($request->input('questions'))) {
            $jsonData = json_decode($request->getContent(), true);
            $raw = $jsonData['questions'] ?? '';
        }
        
        \Log::info('updateExam called', ['vacancy_id' => $vacancy_id, 'raw_questions' => substr($raw, 0, 200), 'is_json' => $request->isJson()]);
        $questions = json_decode($raw, true);

        // Try a fallback if JSON decode failed (sometimes escaped strings arrive)
        if (is_null($questions) && is_string($raw) && $raw !== '') {
            $questions = json_decode(stripslashes($raw), true);
        }

        if (!is_array($questions)) {
            \Log::error('Invalid questions payload', ['raw' => $raw]);
            
            if ($request->isJson()) {
                return response()->json(['msg' => 'Invalid questions payload.'], 400);
            }
            return back()->withErrors(['msg' => 'Invalid questions payload.']);
        }

        // Validate if needed
        foreach ($questions as $idx => $q) {
            // Check question text - frontend may use 'text' or 'duration'
            // Use ternary to handle empty strings properly (not just null)
            $questionText = trim((string) ($q['text'] ?? '')) ?: trim((string) ($q['duration'] ?? ''));

            if ($questionText === '') {
                $msg = "Question #".($idx+1)." must have text.";
                
                if ($request->isJson()) {
                    return response()->json(['msg' => $msg], 422);
                }
                return back()->withErrors(['msg' => $msg]);
            }
        }

        $existingItemsCount = ExamItems::where('vacancy_id', $vacancy_id)->count();

        // Delete existing questions for this vacancy
        ExamItems::where('vacancy_id', $vacancy_id)->delete();

        try {
            foreach ($questions as $q) {
                $typeRaw = strtolower((string) ($q['type'] ?? ''));
                $isMCQ = in_array($typeRaw, ['mcq', 'multiple_choice', 'multiple choice', 'multiple-choice']);
                $isEssay = in_array($typeRaw, ['essay', 'essays']);

                $ans = null;
                $choices = null;

                if ($isMCQ) {
                    $choices = is_array($q['choices'] ?? null) ? array_values($q['choices']) : [];

                    if (isset($q['correctAnswer']) && is_numeric($q['correctAnswer'])) {
                        $idx = (int) $q['correctAnswer'];
                        if (isset($choices[$idx])) $ans = $choices[$idx];
                    }

                    if ($ans === null && !empty($q['answer'])) {
                        $ans = $q['answer'];
                    }

                    // Ensure choices is null when empty
                    if (empty($choices)) $choices = null;
                }

                // Prefer 'text' then 'duration'
                $questionText = trim((string) ($q['text'] ?? '')) ?: trim((string) ($q['duration'] ?? ''));

                $created = ExamItems::create([
                    'vacancy_id' => $vacancy_id,
                    'question' => $questionText,
                    'is_essay' => $isEssay ? 1 : 0,
                    'ans' => $ans,
                    'choices' => $choices,
                ]);
                
                \Log::info('Question created', ['id' => $created->id, 'question' => substr($questionText, 0, 50)]);
            }
        } catch (\Exception $e) {
            \Log::error('Error creating exam items', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $msg = 'Error saving questions: ' . $e->getMessage();
            
            if ($request->isJson()) {
                return response()->json(['msg' => $msg], 500);
            }
            return back()->withErrors(['msg' => $msg]);
        }

        $exam_items = ExamItems::where('vacancy_id', $vacancy_id)->get();

        $action = ($existingItemsCount > 0) ? 'update' : 'create';

        activity()
            ->causedBy(auth('admin')->user())
            ->event($action)
            ->withProperties(['vacancy_id' => $vacancy_id, 'questions_count' => count($questions), 'section' => 'Exam Management'])
            ->log($action . 'd exam questions.');

        if ($request->isJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'Exam updated successfully.',
                'questions_count' => count($questions)
            ]);
        }

        return redirect()->route('admin.exam.edit', ['vacancy_id' => $vacancy_id])->with('success', 'Exam updated successfully.');
    }

    public function examManagement(Request $request)
    {
        $search = $request->input('search');
        $jobType = $request->input('job_type');
        $examStatus = $request->input('exam_status');

        $jobVacancies = JobVacancy::query()
            ->with(['examDetail']) // Eager load the relationship
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('position_title', 'like', '%' . $search . '%')
                        ->orWhere('vacancy_id', 'like', '%' . $search . '%');
                });
            })
            ->when($jobType, function ($query, $jobType) {
                $query->where('vacancy_type', $jobType);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Append status to each vacancy
        $jobVacancies->transform(function ($vacancy) {
            $status = 'Unscheduled';
            if ($vacancy->examDetail) {
                $detail = $vacancy->examDetail;
                if ($detail->date && $detail->time && $detail->duration) {
                    $startDateTime = \Carbon\Carbon::parse($detail->date . ' ' . $detail->time);
                    $endDateTime = $startDateTime->copy()->addMinutes($detail->duration);
                    $now = now();

                    if ($now->between($startDateTime, $endDateTime)) {
                        $status = 'Ongoing';
                    } elseif ($now->gt($endDateTime)) {
                        $status = 'Completed';
                    } else {
                        $status = 'Scheduled';
                    }
                }
            }
            $vacancy->exam_status = $status;
            return $vacancy;
        });

        // Filter by Exam Status (PHP-side filtering since status is calculated)
        if ($examStatus) {
            $jobVacancies = $jobVacancies->filter(function ($vacancy) use ($examStatus) {
                return $vacancy->exam_status === $examStatus;
            })->values(); // Reset keys
        }

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json($jobVacancies);
        }

        return view('admin.exam_management', [
            'vacancies' => $jobVacancies,
            'search' => $search
        ]);
    }

    public function manageExam(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::select('vacancy_id', 'position_title', 'vacancy_type')->where('vacancy_id', $vacancy_id)->first();
        // Only fetch participants who have entered the lobby (read_at is not null)
        $participants = Applications::where('vacancy_id', $vacancy_id)
            ->whereNotNull('read_at')
            ->with('user')
            ->get();
            
        $examDetails = ExamDetail::where('vacancy_id', $vacancy_id)->first();

        $user_name = [];
        foreach ($participants as $p) {
            $user_id = $p['user_id'];
            $user = User::find($user_id);
            $user_name[] = $user ? $user->name : 'Unknown User';
        }

        // Get qualified applicants for Tab 1
        $qualifiedApplicants = Applications::where('vacancy_id', $vacancy_id)
            ->where('status', 'qualified')
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'user_id' => $app->user_id,
                    'vacancy_id' => $app->vacancy_id,
                    'name' => $app->user->name ?? 'Unknown',
                    'email' => $app->user->email ?? 'N/A',
                    'application_date' => $app->created_at->format('M d, Y'),
                    'status' => $app->status,
                    'link_sent_at' => $app->link_sent_at,
                    'link_sent' => !is_null($app->link_sent_at),
                    'read_at' => $app->read_at,
                    'is_read' => !is_null($app->read_at),
                ];
            });

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
            ->log('Managed exam participants and details.');

        return view('admin.manage_exam', [
            'vacancy' => $vacancy,
            'participants' => $participants,
            'user_name' => $user_name,
            'examDetails' => $examDetails,
            'qualifiedApplicants' => $qualifiedApplicants
        ]);
    }

    public function getQualifiedApplicants(Request $request, $vacancy_id)
    {
        $search = $request->get('search', '');
        $status = $request->get('status', '');

        $query = Applications::where('vacancy_id', $vacancy_id)
            ->where('status', 'qualified')
            ->with(['user', 'personalInformation']);

        // Apply search filter
        if (!empty($search)) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $applicants = $query->orderBy('created_at', 'desc')->get();

        // Transform data for the view
        $qualifiedApplicants = $applicants->map(function ($app) {
            return [
                'id' => $app->id,
                'user_id' => $app->user_id,
                'vacancy_id' => $app->vacancy_id,
                'name' => $app->user->name ?? 'Unknown',
                'email' => $app->user->email ?? 'N/A',
                'application_date' => $app->created_at->format('M d, Y'),
                'status' => $app->status,
                'link_sent_at' => $app->link_sent_at,
                'link_sent' => !is_null($app->link_sent_at),
                'read_at' => $app->read_at,
                'is_read' => !is_null($app->read_at),
            ];
        });

        // If AJAX request, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'applicants' => $qualifiedApplicants
            ]);
        }

        return $qualifiedApplicants;
    }

    public function getLobbyData(Request $request, $vacancy_id)
    {
        // Get all applications that are considered participants for this exam
        // Filter by those who have "read" the notification or entered the lobby (read_at is not null)
        $participants = Applications::where('vacancy_id', $vacancy_id)
            ->whereNotNull('read_at')
            ->with('user')
            ->get();

        $lobbyData = $participants->map(function ($p) {
            $statusColors = [
                'ready' => '#4ade80',        // green-400
                'in-progress' => '#facc15',  // yellow-400
                'submitted' => '#3b82f6',    // blue-500
                'pending' => '#f75555',      // red
            ];

            $status = strtolower($p->status ?? 'pending');
            $color = $statusColors[$status] ?? '#9ca3af';

            return [
                'user_id' => $p->user_id,
                'name' => $p->user->name ?? 'Unknown User',
                'result' => $p->result ?: '-',
                'status' => $p->status ?? 'Pending',
                'status_color' => $color,
                'vacancy_id' => $p->vacancy_id // needed for view button link
            ];
        });

        return response()->json([
            'success' => true,
            'participants' => $lobbyData
        ]);
    }

    public function examLobby(Request $request, $vacancy_id)
    {
        // Mark the applicant as having entered the lobby
        $user_id = auth()->id();
        $application = Applications::where('vacancy_id', $vacancy_id)
            ->where('user_id', $user_id)
            ->firstOrFail();

        // If already submitted, redirect to thank you
        if ($application->status === 'submitted') {
            return redirect()->route('user.exam_thankyou', ['vacancy_id' => $vacancy_id]);
        }

        // If already started, redirect to questions
        if ($application->exam_started_at) {
            return redirect()->route('user.exam_question_page', ['vacancy_id' => $vacancy_id]);
        }

        if ($application && is_null($application->read_at)) {
            $application->update(['read_at' => now()]);
        }

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();
        $vacancy = JobVacancy::select('position_title')->where('vacancy_id', $vacancy_id)->first();

        // Check if exam is already started globally
        if ($examDetail && $examDetail->is_started) {
             // We can optionally auto-redirect here too, but the view's JS will handle it or they click ready.
             // But if the user refreshes, they might want to go straight in?
             // The prompt says "waiting logic", so let's stick to the lobby view which will poll and redirect.
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam'])
            ->log('Entered exam lobby.');

        return view('exam_user.exam_lobby', compact('vacancy_id', 'examDetail', 'vacancy'));
    }

    public function examQuestion(Request $request, $vacancy_id)
    {
        $user_id = auth()->id();
        $application = Applications::where('vacancy_id', $vacancy_id)
            ->where('user_id', $user_id)
            ->firstOrFail();
            
        // Check if already submitted
        if ($application->status === 'submitted') {
            return redirect()->route('user.exam_thankyou', ['vacancy_id' => $vacancy_id]);
        }

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->firstOrFail();

        // If admin hasn't started the exam yet, redirect back to lobby
        if (!$examDetail->is_started) {
            return redirect()->route('user.exam_lobby', ['vacancy_id' => $vacancy_id]);
        }

        // Initialize exam start time for the user if not set yet
        if (!$application->exam_started_at) {
            $now = now();
            $duration = $examDetail->duration; // in minutes
            
            $application->update([
                'exam_started_at' => $now,
                'exam_end_time' => $now->copy()->addMinutes($duration),
                'status' => 'in-progress'
            ]);
            
            // Refresh application to get the new values
            $application->refresh();
        }

        $now = now();
        $endTime = \Carbon\Carbon::parse($application->exam_end_time);
        $remaining_seconds = $now->diffInSeconds($endTime, false);

        // If time is up (allow 1 minute grace period for latency)
        if ($remaining_seconds < -60) {
            $application->update(['status' => 'submitted']);
            return redirect()->route('user.exam_thankyou', ['vacancy_id' => $vacancy_id]);
        }
        
        if ($remaining_seconds < 0) $remaining_seconds = 0;

        $columns = Schema::getColumnListing('exam_items');
        $columns = array_diff($columns, ['ans']);

        $examItems = ExamItems::select($columns)
            ->where('vacancy_id', $vacancy_id)
            ->get();
            
        $vacancy = JobVacancy::select('position_title')->where('vacancy_id', $vacancy_id)->first();

        activity()
            ->causedBy(auth()->user())
            ->event('view')
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam'])
            ->log('Viewed exam questions page.');

        return view('exam_user.exam_question_page', compact('vacancy_id', 'examItems', 'remaining_seconds', 'vacancy'));
    }

    public function viewExam(Request $request, $vacancy_id, $user_id)
    {
        //dd($request->all());
        info($user_id);
        $application = Applications::select('user_id', 'answers', 'scores')->where('user_id', $user_id)->where('vacancy_id', $vacancy_id)->firstOrFail();
        $examItems = ExamItems::select('id', 'question', 'ans', 'is_essay')->where('vacancy_id', $vacancy_id)->get();
        $positionTitle = JobVacancy::select('position_title')->where('vacancy_id', $vacancy_id)->firstOrFail();
        $userName = User::select('name')->find($user_id);


        $answers = json_decode($application->answers, true);
        $scores = $application->scores;

        //info($answers);

        $result = 0;

        foreach ($examItems as $item) {
            $givenAnswer = $answers[$item->id] ?? null;
            $score = $scores[$item->id] ?? null;

            $is_correct = ($item->is_essay == 0) ? ($item->ans == $givenAnswer) : null;

            $examResults[] = [
                'id' => $item->id,
                'question' => $item->question,
                'given_answer' => $givenAnswer,
                'score' => $score,
                'is_correct' => $is_correct,
                'is_essay' => $item->is_essay,
            ];
        }

        //info($examResults);

        activity()
            ->causedBy(auth('admin')->user())
            ->event('view')
            ->withProperties(['vacancy_id' => $vacancy_id, 'user_id' => $user_id, 'section' => 'Exam Management'])
            ->log('Viewed applicant exam answers.');

        return view('admin.exam_view_answers', [
            'examResults' => $examResults,
            'positionTitle' => $positionTitle,
            'vacancy_id' => $vacancy_id,
            'user_id' => $user_id,
            'userName' => $userName
        ]);
    }

    public function saveResult(Request $request, $vacancy_id, $user_id)
    {
        //dd($request->all());

        $scores = $request->input('scores');
        $result = $request->input('result');

        $validated = $request->validate([
            'scores' => 'nullable|array',
        ]);

        Applications::where('vacancy_id', $vacancy_id)
            ->where('user_id', $user_id)
            ->update([
                'scores' => json_encode($scores),
                'result' => $result,
            ]);

        activity()
            ->causedBy(auth('admin')->user())
            ->event('save')
            ->withProperties(['vacancy_id' => $vacancy_id, 'user_id' => $user_id, 'section' => 'Exam Management'])
            ->log('Saved exam results.');


        return redirect()->route('admin.manage_exam', ['vacancy_id' => $vacancy_id, 'massage' => 'Result Saved!']);
    }

    public function notifyApplicants(Request $request, $vacancy_id)
    {
        try {
            // Check if details have been saved first
            $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();

            if (!$examDetail || !$examDetail->details_saved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please save exam details first before sending links.'
                ], 400);
            }

            $participants = Applications::where('vacancy_id', $vacancy_id)->get();

            if ($participants->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No participants found for this vacancy.']);
            }

            $userIds = $participants->pluck('user_id')->toArray();

            $this->sendRefinedNotifications($userIds, $vacancy_id, $examDetail);

            // Update exam details as notified
            $examDetail->update([
                'notified_at' => now(),
                'link_sent' => true,
                'link_sent_at' => now()
            ]);

            activity()
                ->causedBy(auth('admin')->user())
                ->event('notify')
                ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
                ->log('Queued exam notifications for all applicants.');

            return response()->json([
                'success' => true,
                'notified_at' => now()->format('Y-m-d H:i:s'),
                'link_sent_at' => now()->format('Y-m-d H:i:s'),
                'message' => 'Notifications sent successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error("Error notifying applicants: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function notifySelectedApplicants(Request $request, $vacancy_id)
    {
        try {
            $validated = $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'integer|exists:users,id'
            ]);

            $userIds = $validated['user_ids'];
            $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->firstOrFail();

            if (!$examDetail->details_saved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please save exam details first before sending links.'
                ], 400);
            }

            $count = $this->sendRefinedNotifications($userIds, $vacancy_id, $examDetail);

            activity()
                ->causedBy(auth('admin')->user())
                ->event('notify_selected')
                ->withProperties(['vacancy_id' => $vacancy_id, 'count' => $count, 'section' => 'Exam Management'])
                ->log('Queued exam notifications for selected applicants.');

            return response()->json([
                'success' => true,
                'message' => "$count applicants notified successfully."
            ]);

        } catch (\Exception $e) {
            Log::error("Error in notifySelectedApplicants: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    private function sendRefinedNotifications(array $userIds, string $vacancy_id, ExamDetail $examDetail)
    {
        return DB::transaction(function () use ($userIds, $vacancy_id, $examDetail) {
            $sender_email = auth('admin')->user()->email ?? config('mail.from.address');
            $count = 0;

            foreach ($userIds as $user_id) {
                // Find application
                $application = Applications::where('vacancy_id', $vacancy_id)
                    ->where('user_id', $user_id)
                    ->lockForUpdate()
                    ->first();

                if ($application) {
                    // Generate Token if not exists or expired (though we might just regenerate always for new link sending)
                    $token = Str::random(64);

                    // Set expiration to Exam Date + Duration + Buffer (e.g. 1 day)
                    // Or simply 48 hours for now as a default
                    $expiresAt = now()->addHours(48);

                    $application->update([
                        'exam_token' => $token,
                        'exam_token_expires_at' => $expiresAt,
                        'link_sent_at' => now(),
                    ]);

                    // Dispatch Job
                    SendExamNotification::dispatch($vacancy_id, $user_id, $examDetail->id, $sender_email);
                    $count++;
                }
            }
            return $count;
        });
    }

    public function saveExamDetails(Request $request, $vacancy_id)
    {
        //info($request->all());
        $validated = $request->validate([
            'time' => 'required',
            'date' => 'required|date',
            'place' => 'required|string',
            'duration' => 'required|integer',
        ]);

        // Add details_saved flag
        $validated['details_saved'] = true;

        ExamDetail::updateOrCreate(
            ['vacancy_id' => $vacancy_id],
            $validated
        );

        $examDetails = ExamDetail::where('vacancy_id', $vacancy_id)->first();

        $notified = false;
        $notified_at = null;

        if ($request->boolean('notify')) {
            $this->notifyApplicants($request, $vacancy_id);
            $examDetails->refresh();
            $notified = true;
            $notified_at = $examDetails->notified_at;
        }

        activity()
            ->causedBy(auth()->user())
            ->event('save')
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
            ->log('Saved exam schedule and details.');

        return response()->json([
            'success' => true,
            'message' => 'Exam details saved.',
            'examDetails' => $examDetails,
            'notified' => $notified,
            'notified_at' => $notified_at
        ]);
    }

    public function startExam(Request $request, $vacancy_id)
    {
        try {
            $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();

            if (!$examDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam details not found.'
                ], 404);
            }

            if (!$examDetail->link_sent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please send exam links to applicants first.'
                ], 400);
            }

            // Mark exam as started
            $examDetail->update(['is_started' => true]);

            activity()
                ->causedBy(auth('admin')->user())
                ->event('start')
                ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
                ->log('Started the exam.');

            return response()->json([
                'success' => true,
                'message' => 'Exam started successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error("Error starting exam: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkExamStatus(Request $request, $vacancy_id)
    {
        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();

        if (!$examDetail) {
            return response()->json(['started' => false]);
        }

        return response()->json([
            'started' => (bool) $examDetail->is_started
        ]);
    }

    public function confirmNotification($token)
    {
        $application = Applications::where('exam_token', $token)->first();

        if (!$application) {
            abort(404, 'Invalid token');
        }

        if (!$application->read_at) {
            $application->update(['read_at' => now()]);
        }

        return view('exam.confirmation');
    }
}
