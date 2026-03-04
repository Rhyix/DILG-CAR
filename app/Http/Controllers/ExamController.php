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
use Illuminate\Support\Facades\Gate;
use App\Jobs\SendExamNotification;
use App\Mail\NotifyApplicantMail;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Cookie;
use App\Models\ExamTabViolation;

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

        // Auto-check MCQ answers and compute per-item scores (tolerant to key/value mismatch and case)
        $items = ExamItems::select('id', 'ans', 'is_essay', 'choices')
            ->where('vacancy_id', $vacancy_id)
            ->get();

        $scores = [];
        $totalMcq = 0;
        $correctMcq = 0;

        foreach ($items as $item) {
            $given = $validated['answers'][$item->id] ?? null;
            if ((int)$item->is_essay === 0) {
                $totalMcq++;
                $isCorrect = false;
                if (!is_null($given)) {
                    $givenStr = trim((string)$given);
                    $ansStr = trim((string)($item->ans ?? ''));
                    // Direct key match (e.g., "A" === "A"), case-insensitive
                    if (strcasecmp($givenStr, $ansStr) === 0) {
                        $isCorrect = true;
                    } else {
                        // Fallback: if 'ans' stores the choice text, verify mapping key->value match
                        $choices = is_array($item->choices) ? $item->choices : [];
                        foreach ($choices as $key => $val) {
                            if (strcasecmp(trim((string)$val), $ansStr) === 0 && strcasecmp(trim((string)$key), $givenStr) === 0) {
                                $isCorrect = true;
                                break;
                            }
                        }
                    }
                }
                $scores[$item->id] = $isCorrect ? 1 : 0;
                if ($isCorrect) $correctMcq++;
            } else {
                // Essays are scored later by admin
                $scores[$item->id] = null;
            }
        }

        $resultStr = $totalMcq > 0 ? ($correctMcq . '/' . $totalMcq) : null;

        // Update the answers and scores fields
        $answerRecord->answers = $validated['answers'] ?? [];
        $answerRecord->scores = $scores;
        $answerRecord->result = $resultStr;
        $answerRecord->status = 'submitted'; // Ensure status is updated to 'submitted'
        $answerRecord->exam_submitted_at = now();
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

    public function autoSave(Request $request, $vacancy_id)
    {
        $validated = $request->validate([
            'vacancy_id' => 'required|string',
            'user_id' => 'required|integer',
            'answers' => 'nullable|array',
        ]);

        $answerRecord = Applications::where('vacancy_id', $validated['vacancy_id'])
            ->where('user_id', $validated['user_id'])
            ->firstOrFail();

        // If exam is already submitted, don't allow autosave
        if ($answerRecord->status === 'submitted') {
            return response()->json(['success' => false, 'message' => 'Exam already submitted']);
        }

        // Calculate scores similar to submit, but don't finalize
        $items = ExamItems::select('id', 'ans', 'is_essay', 'choices')
            ->where('vacancy_id', $vacancy_id)
            ->get();

        $scores = [];
        $totalMcq = 0;
        $correctMcq = 0;

        foreach ($items as $item) {
            $given = $validated['answers'][$item->id] ?? null;
            if ((int)$item->is_essay === 0) {
                $totalMcq++;
                $isCorrect = false;
                if (!is_null($given)) {
                    $givenStr = trim((string)$given);
                    $ansStr = trim((string)($item->ans ?? ''));
                    // Direct key match (e.g., "A" === "A"), case-insensitive
                    if (strcasecmp($givenStr, $ansStr) === 0) {
                        $isCorrect = true;
                    } else {
                        // Fallback: if 'ans' stores the choice text, verify mapping key->value match
                        $choices = is_array($item->choices) ? $item->choices : [];
                        foreach ($choices as $key => $val) {
                            if (strcasecmp(trim((string)$val), $ansStr) === 0 && strcasecmp(trim((string)$key), $givenStr) === 0) {
                                $isCorrect = true;
                                break;
                            }
                        }
                    }
                }
                $scores[$item->id] = $isCorrect ? 1 : 0;
                if ($isCorrect) $correctMcq++;
            } else {
                // Essays are scored later by admin
                $scores[$item->id] = null;
            }
        }

        $resultStr = $totalMcq > 0 ? ($correctMcq . '/' . $totalMcq) : null;

        // Update the answers and scores fields
        $answerRecord->answers = $validated['answers'] ?? [];
        $answerRecord->scores = $scores;
        $answerRecord->result = $resultStr;
        // Do NOT change status to submitted
        $answerRecord->save();

        return response()->json(['success' => true]);
    }

    public function getExaminationDates(Request $request)
    {
        try {
            // Fetch all scheduled exams with vacancy details
            $exams = ExamDetail::with('vacancy')
                ->where('date', '>=', now()->toDateString()) // Only upcoming exams
                ->whereNotNull('date')
                ->whereNotNull('time')
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get()
                ->map(function ($exam) {
                    return [
                        'id' => $exam->id,
                        'vacancy_id' => $exam->vacancy_id,
                        'position_title' => $exam->vacancy->position_title ?? 'N/A',
                        'vacancy_type' => $exam->vacancy->vacancy_type ?? 'N/A',
                        'date' => $exam->date,
                        'time' => $exam->time,
                        'time_end' => $exam->time_end,
                        'venue' => $exam->place ?? 'TBA',
                        'formatted_date' => \Carbon\Carbon::parse($exam->date)->format('F d, Y'),
                        'formatted_time' => \Carbon\Carbon::parse($exam->time)->format('h:i A'),
                        'formatted_time_end' => $exam->time_end ? \Carbon\Carbon::parse($exam->time_end)->format('h:i A') : null,
                        'status' => $this->getExamStatus($exam),
                    ];
                });

            // Group by date for easier frontend processing
            $groupedByDate = $exams->groupBy('date')->map(function ($items, $date) {
                return [
                    'date' => $date,
                    'formatted_date' => \Carbon\Carbon::parse($date)->format('F d, Y'),
                    'exams' => $items,
                    'count' => $items->count()
                ];
            })->values();

            return response()->json([
                'success' => true,
                'exams' => $exams,
                'grouped_by_date' => $groupedByDate,
                'count' => $exams->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching examination dates: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch examination dates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getExamStatus($exam)
    {
        if (!$exam->date || !$exam->time) {
            return 'Unscheduled';
        }
        
        $startDateTime = \Carbon\Carbon::parse($exam->date . ' ' . $exam->time);
        $endDateTime = $exam->time_end 
            ? \Carbon\Carbon::parse($exam->date . ' ' . $exam->time_end)
            : $startDateTime->copy()->addMinutes($exam->duration ?? 0);
        
        $now = now();
        
        if ($now->gt($endDateTime)) {
            return 'Completed';
        } elseif ($exam->is_started || $now->between($startDateTime, $endDateTime)) {
            return 'Ongoing';
        } else {
            return 'Scheduled';
        }
    }

    private function denyViewerAccess(Request $request, string $message = 'Viewer role has read-only exam monitoring access.')
    {
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $admin = auth('admin')->user();
        if (Gate::forUser($admin)->allows('admin.exam.manage')) {
            return null;
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        return redirect()
            ->route('admin_exam_management')
            ->with('error', $message);
    }

    private function isViewerRole(): bool
    {
        if (!auth('admin')->check()) {
            return false;
        }

        $admin = auth('admin')->user();
        return Gate::forUser($admin)->allows('admin.exam.monitor')
            && Gate::forUser($admin)->denies('admin.exam.manage');
    }

    public function logSwitch(Request $request)
    {
        Log::info('User switched tab at ' . $request->input('time'));

        $user = auth()->user();
        $vacancyId = $request->input('vacancy_id');
        $countFromClient = (int) ($request->input('count') ?? 0);
        $startedAt = $request->input('started_at');
        $endedAt = $request->input('ended_at');
        $duration = $request->input('duration_seconds');

        $applicationQuery = Applications::where('user_id', $user?->id);
        if ($vacancyId) {
            $applicationQuery->where('vacancy_id', $vacancyId);
        } else {
            $applicationQuery->where('status', 'in-progress');
        }
        $application = $applicationQuery->orderByDesc('exam_started_at')->first();

        if ($application) {
            $application->tab_violations = (int)($application->tab_violations ?? 0) + 1;
            $application->last_tab_violation_at = now();
            $application->save();

            try {
                ExamTabViolation::create([
                    'user_id' => $application->user_id,
                    'vacancy_id' => $application->vacancy_id,
                    'started_at' => $startedAt ? \Carbon\Carbon::parse($startedAt) : now(),
                    'ended_at' => $endedAt ? \Carbon\Carbon::parse($endedAt) : null,
                    'duration_seconds' => is_numeric($duration) ? (int)$duration : null,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to persist exam tab violation', ['error' => $e->getMessage()]);
            }

            activity()
                ->causedBy($user)
                ->event('tab_violation')
                ->withProperties([
                    'vacancy_id' => $application->vacancy_id,
                    'user_id' => $application->user_id,
                    'count' => $application->tab_violations,
                    'time' => $request->input('time'),
                    'duration_seconds' => $duration,
                    'section' => 'Exam'
                ])
                ->log('Exam tab switch violation recorded.');

            // Broadcast-style admin notification (visible to all admins)
            try {
                \App\Models\Notification::create([
                    'notifiable_type' => 'App\Models\Admin',
                    'notifiable_id' => null, // visible to all admins
                    'type' => 'warning',
                    'data' => [
                        'category' => 'exam_lifecycle',
                        'title' => 'Tab Switch Detected',
                        'message' => ($user?->name ?? 'Applicant') . ' switched tabs (' . $application->tab_violations . ' total).',
                        'user_id' => $application->user_id,
                        'vacancy_id' => $application->vacancy_id,
                        'count' => $application->tab_violations,
                        'link' => route('admin.view_exam', ['vacancy_id' => $application->vacancy_id, 'user_id' => $application->user_id], false),
                    ],
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to create admin notification for tab switch', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['ok' => true]);
    }

    public function editExam(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot edit exam content.')) {
            return $denied;
        }

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
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot update exam content.')) {
            return $denied;
        }

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
                $msg = "Question #" . ($idx + 1) . " must have text.";

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
                        if (isset($choices[$idx]))
                            $ans = $choices[$idx];
                    }

                    if ($ans === null && !empty($q['answer'])) {
                        $ans = $q['answer'];
                    }

                    // Ensure choices is null when empty
                    if (empty($choices))
                        $choices = null;
                }

                // Prefer 'text' then 'duration'
                $questionText = trim((string) ($q['text'] ?? '')) ?: trim((string) ($q['duration'] ?? ''));

                $essayMax = null;
                if ($isEssay) {
                    $essayMax = isset($q['essayMax']) && is_numeric($q['essayMax']) ? max(0, (int)$q['essayMax']) : null;
                }

                $created = ExamItems::create([
                    'vacancy_id' => $vacancy_id,
                    'question' => $questionText,
                    'is_essay' => $isEssay ? 1 : 0,
                    'ans' => $ans,
                    'choices' => $choices,
                    'essay_max_score' => $essayMax,
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
        $isViewer = $this->isViewerRole();
        $search = $request->input('search');
        $jobType = $isViewer ? null : $request->input('job_type');
        $examStatus = $isViewer ? 'Ongoing' : $request->input('exam_status');

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
                $status = $this->getExamStatus($vacancy->examDetail);
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
            'search' => $search,
            'isViewer' => $isViewer,
        ]);
    }

    public function manageExam(Request $request, $vacancy_id)
    {
        $isViewer = $this->isViewerRole();
        $vacancy = JobVacancy::select('vacancy_id', 'position_title', 'vacancy_type')->where('vacancy_id', $vacancy_id)->first();
        // Only fetch participants who have entered the lobby (read_at is not null)
        $participants = Applications::where('vacancy_id', $vacancy_id)
            ->whereNotNull('read_at')
            ->with('user')
            ->get();

        $examDetails = ExamDetail::where('vacancy_id', $vacancy_id)->first();

        if ($isViewer) {
            $viewerExamStatus = $examDetails ? $this->getExamStatus($examDetails) : 'Unscheduled';
            if ($viewerExamStatus !== 'Ongoing') {
                return redirect()
                    ->route('admin_exam_management')
                    ->with('error', 'Viewer can only monitor ongoing exams.');
            }
        }

        $isExamExpired = false;
        if ($examDetails && $examDetails->date && $examDetails->time) {
             $startDateTime = \Carbon\Carbon::parse($examDetails->date . ' ' . $examDetails->time);
             $endDateTime = $examDetails->time_end 
                ? \Carbon\Carbon::parse($examDetails->date . ' ' . $examDetails->time_end)
                : $startDateTime->copy()->addMinutes($examDetails->duration ?? 0);
             
             if (now()->gt($endDateTime)) {
                 $isExamExpired = true;
             }
        }

        $user_name = [];
        foreach ($participants as $p) {
            $user_id = $p['user_id'];
            $user = User::find($user_id);
            $user_name[] = $user ? $user->name : 'Unknown User';
        }

        // Pre-calculate scores for view
        $examItems = ExamItems::where('vacancy_id', $vacancy_id)->get(['id', 'is_essay']);
        $mcItemIds = $examItems->where('is_essay', 0)->pluck('id')->toArray();
        $essayItemIds = $examItems->where('is_essay', 1)->pluck('id')->toArray();

        foreach ($participants as $p) {
            $scores = $p->scores ?? [];
            $answers = $p->answers ?? [];
            $status = strtolower($p->status ?? 'pending');
            
            $mcString = '-';
            $essayString = '-';

            if ($status === 'in-progress') {
                $mcScore = 0;
                foreach ($mcItemIds as $id) {
                    if (isset($scores[$id])) {
                        $mcScore += (int)$scores[$id];
                    }
                }
                $mcString = count($mcItemIds) > 0 ? "$mcScore / " . count($mcItemIds) : '-';

                $answeredEssay = 0;
                foreach ($essayItemIds as $id) {
                    $val = $answers[$id] ?? null;
                    if (!is_null($val) && trim((string)$val) !== '') {
                        $answeredEssay++;
                    }
                }
                $essayString = count($essayItemIds) > 0 ? "$answeredEssay / " . count($essayItemIds) : '-';
            } elseif ($status === 'submitted' || $isExamExpired) {
                $mcScore = 0;
                foreach ($mcItemIds as $id) {
                    if (isset($scores[$id])) $mcScore += (int)$scores[$id];
                }
                $mcString = count($mcItemIds) > 0 ? "$mcScore / " . count($mcItemIds) : '-';
    
                $essayScore = 0;
                foreach ($essayItemIds as $id) {
                    if (isset($scores[$id])) $essayScore += (int)$scores[$id];
                }
                $essayString = count($essayItemIds) > 0 ? "$essayScore" : '-';
            }

            $p->mc_score_str = $mcString;
            $p->essay_score_str = $essayString;
        }

        // Get qualified applicants for Tab 1
        $qualifiedApplicants = collect();
        if (!$isViewer) {
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
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
            ->log('Managed exam participants and details.');

        // Resolve last notifier for schedule notifications, if any
        $notifiedByName = null;
        if ($examDetails && $examDetails->notified_at) {
            $lastSchedule = Activity::where('event', 'notify_schedule')
                ->where('properties->vacancy_id', $vacancy_id)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($lastSchedule && $lastSchedule->causer) {
                $notifiedByName = $lastSchedule->causer->name ?? $lastSchedule->causer->email ?? null;
            }
        }

        if ($isViewer) {
            return view('viewer.manage_exam_monitor', [
                'vacancy' => $vacancy,
                'participants' => $participants,
                'user_name' => $user_name,
                'examDetails' => $examDetails,
            ]);
        }

        return view('admin.manage_exam', [
            'vacancy' => $vacancy,
            'participants' => $participants,
            'user_name' => $user_name,
            'examDetails' => $examDetails,
            'qualifiedApplicants' => $qualifiedApplicants,
            'notifiedByName' => $notifiedByName
        ]);
    }

    public function getQualifiedApplicants(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot access qualified applicants list.')) {
            return $denied;
        }

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
        if ($this->isViewerRole()) {
            $viewerExamDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();
            $viewerExamStatus = $viewerExamDetail ? $this->getExamStatus($viewerExamDetail) : 'Unscheduled';
            if ($viewerExamStatus !== 'Ongoing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Viewer can only monitor ongoing exams.',
                ], 403);
            }
        }

        // Get all applications that are considered participants for this exam
        // Filter by those who have "read" the notification or entered the lobby (read_at is not null)
        $participants = Applications::where('vacancy_id', $vacancy_id)
            ->whereNotNull('read_at')
            ->with('user')
            ->get();

        // Get Exam Items to distinguish MC vs Essay
        $examItems = ExamItems::where('vacancy_id', $vacancy_id)->get(['id', 'is_essay']);
        $mcItemIds = $examItems->where('is_essay', 0)->pluck('id')->toArray();
        $essayItemIds = $examItems->where('is_essay', 1)->pluck('id')->toArray();

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();
        $isExamExpired = false;
        if ($examDetail && $examDetail->date && $examDetail->time) {
             $startDateTime = \Carbon\Carbon::parse($examDetail->date . ' ' . $examDetail->time);
             $endDateTime = $examDetail->time_end 
                ? \Carbon\Carbon::parse($examDetail->date . ' ' . $examDetail->time_end)
                : $startDateTime->copy()->addMinutes($examDetail->duration ?? 0);
             
             if (now()->gt($endDateTime)) {
                 $isExamExpired = true;
             }
        }

        $lobbyData = $participants->map(function ($p) use ($mcItemIds, $essayItemIds, $isExamExpired) {
            $statusColors = [
                'ready' => '#4ade80',        // green-400
                'in-progress' => '#facc15',  // yellow-400
                'submitted' => '#3b82f6',    // blue-500
                'pending' => '#f75555',      // red
            ];

            $status = strtolower($p->status ?? 'pending');
            $color = $statusColors[$status] ?? '#9ca3af';

            $scores = $p->scores ?? [];
            $answers = $p->answers ?? [];
             
            $mcString = '-';
            $essayString = '-';

            if ($status === 'in-progress') {
                $mcScore = 0;
                foreach ($mcItemIds as $id) {
                    if (isset($scores[$id])) {
                        $mcScore += (int)$scores[$id];
                    }
                }
                $mcString = count($mcItemIds) > 0 ? "$mcScore / " . count($mcItemIds) : '-';

                $answeredEssay = 0;
                foreach ($essayItemIds as $id) {
                    $val = $answers[$id] ?? null;
                    if (!is_null($val) && trim((string)$val) !== '') {
                        $answeredEssay++;
                    }
                }
                $essayString = count($essayItemIds) > 0 ? "$answeredEssay / " . count($essayItemIds) : '-';
            } elseif ($status === 'submitted' || $isExamExpired) {
                $mcScore = 0;
                foreach ($mcItemIds as $id) {
                    if (isset($scores[$id])) $mcScore += (int)$scores[$id];
                }
                $mcString = count($mcItemIds) > 0 ? "$mcScore / " . count($mcItemIds) : '-';
    
                $essayScore = 0;
                foreach ($essayItemIds as $id) {
                    if (isset($scores[$id])) $essayScore += (int)$scores[$id];
                }
                $essayString = count($essayItemIds) > 0 ? "$essayScore" : '-';
            }

            return [
                'user_id' => $p->user_id,
                'name' => $p->user->name ?? 'Unknown User',
                'result' => $p->result ?: '-',
                'mc_score' => $mcString,
                'essay_score' => $essayString,
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
        if (!auth()->check()) {
            $token = $request->query('token');
            return redirect()->route('login.form', [
                'redirect' => 'exam_lobby',
                'vacancy' => $vacancy_id,
                'token' => $token,
            ]);
        }

        // Mark the applicant as having entered the lobby
        $user_id = auth()->id();
        $application = Applications::where('vacancy_id', $vacancy_id)
            ->where('user_id', $user_id)
            ->firstOrFail();

        $sessionKey = 'exam_access_' . $vacancy_id;
        $token = $request->query('token');
        $deviceId = Cookie::get('device_id');

        if (is_null($application->exam_token_used_at)) {
            if (empty($token) || $token !== $application->exam_token) {
                abort(403, 'Invalid or missing exam link.');
            }
            if (!$application->exam_token_expires_at || now()->greaterThan($application->exam_token_expires_at)) {
                abort(403, 'This exam link has expired.');
            }
            if (!$deviceId) {
                $deviceId = Str::random(40);
                Cookie::queue(cookie('device_id', $deviceId, 60 * 24 * 365));
            }
            session([$sessionKey => true]);
            $application->update([
                'exam_token_used_at' => now(),
                'exam_token_device_id' => $deviceId,
                'exam_token_used_ip' => $request->ip(),
                'exam_token_used_ua' => (string)$request->userAgent(),
            ]);
        } else {
            $hasSession = session()->get($sessionKey, false);
            $matchesDevice = $deviceId && $application->exam_token_device_id && hash_equals($application->exam_token_device_id, $deviceId);
            if (!$hasSession && !$matchesDevice) {
                abort(403, 'This exam link is already used on a different device.');
            }
        }

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

        // If admin has already started the exam, route user straight to questions
        if ($examDetail && $examDetail->is_started) {
            return redirect()->route('user.exam_question_page', ['vacancy_id' => $vacancy_id]);
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
            $payload = ['status' => 'submitted'];
            if (!$application->exam_submitted_at) {
                $payload['exam_submitted_at'] = now();
            }
            $application->update($payload);
            return redirect()->route('user.exam_thankyou', ['vacancy_id' => $vacancy_id]);
        }

        if ($remaining_seconds < 0)
            $remaining_seconds = 0;

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

        $total_seconds = $examDetail->duration * 60;
        $savedAnswers = is_array($application->answers) ? $application->answers : [];

        return view('exam_user.exam_question_page', compact('vacancy_id', 'examItems', 'remaining_seconds', 'vacancy', 'total_seconds', 'savedAnswers'));
    }

    public function viewExam(Request $request, $vacancy_id, $user_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot check applicant answers.')) {
            return $denied;
        }

        //dd($request->all());
        info($user_id);
        $application = Applications::select('user_id', 'answers', 'scores', 'exam_started_at', 'exam_end_time', 'exam_submitted_at', 'tab_violations', 'last_tab_violation_at', 'status')->where('user_id', $user_id)->where('vacancy_id', $vacancy_id)->firstOrFail();
        $examItems = ExamItems::select('id', 'question', 'ans', 'is_essay', 'choices', 'essay_max_score')->where('vacancy_id', $vacancy_id)->get();
        $positionTitle = JobVacancy::select('position_title')->where('vacancy_id', $vacancy_id)->firstOrFail();
        $userName = User::select('name')->find($user_id);

        $answers = $application->answers; 
        $scores = $application->scores;   
        // $answers = json_decode($application->answers, true);
        // $scores = $application->scores;

        //info($answers);

        $result = 0;

        $examineeCode = strtoupper('EXM-' . substr(hash('sha256', $vacancy_id . '-' . $user_id), 0, 8));

        foreach ($examItems as $item) {
            $givenAnswer = $answers[$item->id] ?? null;
            $score = $scores[$item->id] ?? null;

            $choices = is_array($item->choices) ? $item->choices : [];
            // Normalize to strings
            $givenKey = is_null($givenAnswer) ? null : (string)$givenAnswer;
            $correctKey = is_null($item->ans) ? null : (string)$item->ans;
            $givenText = $givenKey !== null && isset($choices[$givenKey]) ? (string)$choices[$givenKey] : null;
            $correctText = $correctKey !== null && isset($choices[$correctKey]) ? (string)$choices[$correctKey] : null;

            // Determine correctness:
            // Prefer stored score if available; otherwise compute tolerant comparison
            if ($item->is_essay == 0) {
                if (!is_null($score)) {
                    $is_correct = ((int)$score) === 1;
                } else {
                    $isKeyMatch = (!is_null($givenKey) && !is_null($correctKey) && strcasecmp(trim($givenKey), trim($correctKey)) === 0);
                    $is_correct = $isKeyMatch;
                }
            } else {
                $is_correct = null;
            }

            $examResults[] = [
                'id' => $item->id,
                'question' => $item->question,
                'given_answer' => $givenAnswer,
                'given_answer_text' => $givenText,
                'correct_answer' => $item->ans,
                'correct_answer_text' => $correctText,
                'score' => $score,
                'is_correct' => $is_correct,
                'is_essay' => $item->is_essay,
                'essay_max_score' => (int) ($item->essay_max_score ?? 0),
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
            'userName' => $userName,
            'examineeCode' => $examineeCode,
            'application' => $application,
        ]);
    }

    public function getExamAnswersJson(Request $request, $vacancy_id, $user_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot check applicant answers.')) {
            return $denied;
        }

        $application = Applications::select('user_id', 'answers', 'scores', 'tab_violations', 'last_tab_violation_at')->where('user_id', $user_id)->where('vacancy_id', $vacancy_id)->firstOrFail();
        $examItems = ExamItems::select('id', 'question', 'ans', 'is_essay', 'choices', 'essay_max_score')->where('vacancy_id', $vacancy_id)->get();

        $answers = $application->answers;
        $scores = $application->scores;

        $examResults = [];

        foreach ($examItems as $item) {
            $givenAnswer = $answers[$item->id] ?? null;
            $score = $scores[$item->id] ?? null;

            $choices = is_array($item->choices) ? $item->choices : [];
            // Normalize to strings
            $givenKey = is_null($givenAnswer) ? null : (string)$givenAnswer;
            $correctKey = is_null($item->ans) ? null : (string)$item->ans;
            $givenText = $givenKey !== null && isset($choices[$givenKey]) ? (string)$choices[$givenKey] : null;
            $correctText = $correctKey !== null && isset($choices[$correctKey]) ? (string)$choices[$correctKey] : null;

            // Determine correctness:
            // Prefer stored score if available; otherwise compute tolerant comparison
            if ($item->is_essay == 0) {
                if (!is_null($score)) {
                    $is_correct = ((int)$score) === 1;
                } else {
                    $isKeyMatch = (!is_null($givenKey) && !is_null($correctKey) && strcasecmp(trim($givenKey), trim($correctKey)) === 0);
                    $is_correct = $isKeyMatch;
                }
            } else {
                $is_correct = null;
            }

            $examResults[] = [
                'id' => $item->id,
                'question' => $item->question,
                'given_answer' => $givenAnswer,
                'given_answer_text' => $givenText,
                'correct_answer' => $item->ans,
                'correct_answer_text' => $correctText,
                'score' => $score,
                'is_correct' => $is_correct,
                'is_essay' => $item->is_essay,
                'essay_max_score' => (int) ($item->essay_max_score ?? 0),
            ];
        }

        $logsRaw = \App\Models\ExamTabViolation::where('vacancy_id', $vacancy_id)
            ->where('user_id', $user_id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['started_at', 'ended_at', 'duration_seconds', 'created_at']);
        $logs = $logsRaw->map(function ($l) {
            return [
                'started_at_iso' => optional($l->started_at)->toIso8601String(),
                'ended_at_iso' => optional($l->ended_at)->toIso8601String(),
                'duration_seconds' => $l->duration_seconds,
                'created_at_iso' => optional($l->created_at)->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'examResults' => $examResults,
            'tab_violations' => (int) ($application->tab_violations ?? 0),
            'last_violation_at' => $application->last_tab_violation_at,
            'tab_violation_logs' => $logs
        ]);
    }

    public function downloadExamPdf(Request $request, $vacancy_id, $user_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot export applicant exam answers.')) {
            return $denied;
        }

        $application = Applications::select('user_id', 'answers', 'scores', 'exam_started_at', 'exam_end_time', 'status')
            ->where('user_id', $user_id)->where('vacancy_id', $vacancy_id)->firstOrFail();
        $examItems = ExamItems::select('id', 'question', 'is_essay', 'choices', 'essay_max_score', 'ans')
            ->where('vacancy_id', $vacancy_id)->get();
        $positionTitle = JobVacancy::select('position_title')->where('vacancy_id', $vacancy_id)->firstOrFail();
        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();

        $examineeCode = strtoupper('EXM-' . substr(hash('sha256', $vacancy_id . '-' . $user_id), 0, 8));
        $answers = $application->answers ?? [];
        $scores = $application->scores ?? [];

        // Build PDF inline using FPDF (installed via setasign/fpdf)
        $pdf = new \FPDF();
        $pdf->SetMargins(20, 20, 20);
        $pdf->AddPage();
        $pdf->SetTitle('Examination Result');

        /*
        |--------------------------------------------------------------------------
        | APPLICANT INFORMATION TABLE
        |--------------------------------------------------------------------------
        */

        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 6, 'APPLICANT INFORMATION', 0, 1);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(4);

        $pdf->SetFont('Times', '', 11);

        $dateStr = optional($examDetail)->date
            ? \Carbon\Carbon::parse($examDetail->date)->format('F d, Y')
            : '-';

        $pdf->Cell(55, 6, 'Examinee Code:', 0, 0);
        $pdf->Cell(0, 6, $examineeCode, 0, 1);

        $pdf->Cell(55, 6, 'Position Applied For:', 0, 0);
        // Encoding-safe text conversion for PDF core fonts
        $toPdf = function ($text) {
            $text = (string)$text;
            $replacements = [
                "’" => "'",
                "‘" => "'",
                "“" => '"',
                "”" => '"',
                "–" => "-",
                "—" => "-",
                "…" => "...",
                "•" => "*",
                " " => " ", // non-breaking space
            ];
            $text = strtr($text, $replacements);
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
            if ($converted === false) {
                $converted = utf8_decode($text);
            }
            return $converted;
        };
        $pdf->Cell(0, 6, $toPdf($positionTitle->position_title ?? '-'), 0, 1);

        $pdf->Cell(55, 6, 'Date of Examination:', 0, 0);
        $pdf->Cell(0, 6, $toPdf($dateStr), 0, 1);

        $pdf->Cell(55, 6, 'Time Started:', 0, 0);
        $startedStr = $application->exam_started_at
            ? \Carbon\Carbon::parse($application->exam_started_at)->format('g:i A')
            : '-';
        $pdf->Cell(0, 6, $startedStr, 0, 1);

        $pdf->Cell(55, 6, 'Time Submitted:', 0, 0);
        // Use dedicated submission timestamp if available
        $submittedStr = $application->exam_submitted_at
            ? \Carbon\Carbon::parse($application->exam_submitted_at)->format('g:i A')
            : '-';
        $pdf->Cell(0, 6, $submittedStr, 0, 1);

        $pdf->Cell(55, 6, 'Extracted At:', 0, 0);
        $pdf->Cell(0, 6, now()->format('F d, Y g:i A'), 0, 1);

        $pdf->Ln(8);

        /*
        |--------------------------------------------------------------------------
        | RESPONSES SECTION
        |--------------------------------------------------------------------------
        */

        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 6, 'EXAMINATION RESPONSES', 0, 1);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(6);

        $idx = 1;

        foreach ($examItems as $item) {

            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
            }

            // Question header with right-aligned essay points (if applicable)
            $pdf->SetFont('Times', 'B', 11);
            $qStartY = $pdf->GetY();
            $questionText = $toPdf($idx . '. ' . ($item->question ?? ''));

            if ((int)$item->is_essay === 1) {
                $maxPts = (int)($item->essay_max_score ?? 0);
                if ($maxPts > 0) {
                    $scoreVal = $scores[$item->id] ?? null;
                    $ptsLabel = is_numeric($scoreVal) ? ($scoreVal . '/' . $maxPts . ' pts') : ('/' . $maxPts . ' pts');
                    $label = $toPdf($ptsLabel);
                    $labelW = $pdf->GetStringWidth($label);
                    $rightX = 210 - 20 - $labelW; // page width 210mm, right margin 20mm
                    $pdf->SetXY($rightX, $qStartY);
                    $pdf->SetFont('Times', 'I', 10);
                    $pdf->Cell($labelW, 6, $label, 0, 0, 'R');
                    $pdf->SetXY(20, $qStartY);
                    $pdf->SetFont('Times', 'B', 11);
                }
            }

            $pdf->MultiCell(0, 6, $questionText);

            if ((int)$item->is_essay === 1) {

                $ans = isset($answers[$item->id]) ? (string)$answers[$item->id] : '';

                $pdf->SetFont('Times', '', 11);

                $boxHeight = 36;
                $startY = $pdf->GetY();

                $pdf->Rect(20, $startY, 170, $boxHeight);
                $pdf->SetXY(22, $startY + 3);
                $pdf->MultiCell(166, 6, $toPdf($ans));

                $pdf->SetY($startY + $boxHeight + 8);

            } else {

            $given = $answers[$item->id] ?? null;
            $choiceMap = is_array($item->choices) ? $item->choices : [];

            $display = '-';

            if (!is_null($given)) {
                $keyRaw = (string)$given;
                // Map numeric keys to letters (0->A, 1->B, ...)
                if (is_numeric($keyRaw)) {
                    $idxKey = (int)$keyRaw;
                    $labelKey = chr(ord('A') + $idxKey);
                } else {
                    $labelKey = strtoupper($keyRaw);
                }

                // Handle choice maps that use numeric indexes
                $choiceLookupKey = isset($choiceMap[$keyRaw]) ? $keyRaw : ( (is_numeric($keyRaw) && isset($choiceMap[(int)$keyRaw])) ? (int)$keyRaw : $keyRaw );
                if (isset($choiceMap[$choiceLookupKey])) {
                    $choiceText = (string)$choiceMap[$choiceLookupKey];
                    $display = $labelKey . '. ' . $choiceText;   // eg. B. Lorem Ipsum
                } else {
                    $display = $labelKey; // fallback if mapping missing
                }
            }

            $pdf->SetFont('Times', '', 11);
            $pdf->Cell(45, 6, $toPdf("Applicant's Answer:"), 0, 0);

            $pdf->SetFont('Times', 'B', 11);
            $pdf->MultiCell(0, 6, $toPdf($display));
            }

            $pdf->Ln(8);
            $idx++;
        }

        $content = $pdf->Output('S');
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="exam-result.pdf"',
        ]);
    }

    public function saveResult(Request $request, $vacancy_id, $user_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot score applicant exams.')) {
            return $denied;
        }

        $scores = $request->input('scores');
        $result = $request->input('result');

        $validated = $request->validate([
            'scores' => 'nullable|array',
        ]);

        // Clamp essay scores within [0, essay_max_score]
        $items = ExamItems::where('vacancy_id', $vacancy_id)->get(['id', 'is_essay', 'essay_max_score']);
        $essayMaxMap = [];
        foreach ($items as $it) {
            if ((int)$it->is_essay === 1) {
                $essayMaxMap[$it->id] = is_null($it->essay_max_score) ? null : (int)$it->essay_max_score;
            }
        }

        if (is_array($scores)) {
            foreach ($scores as $qId => $val) {
                if (array_key_exists($qId, $essayMaxMap)) {
                    if ($val === '' || is_null($val)) {
                        // Not scored
                        continue;
                    }
                    $num = (int)$val;
                    $max = $essayMaxMap[$qId] ?? 0;
                    if (!is_null($max)) {
                        if ($num < 0) $num = 0;
                        if ($num > $max) $num = $max;
                    } else {
                        if ($num < 0) $num = 0;
                    }
                    $scores[$qId] = $num;
                } else {
                    // MCQ should be 0/1
                    if ($val === '' || is_null($val)) {
                        $scores[$qId] = null;
                    } else {
                        $scores[$qId] = (int)$val ? 1 : 0;
                    }
                }
            }
        }

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
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot send exam notifications.')) {
            return $denied;
        }

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

            $notificationResult = $this->sendRefinedNotifications($userIds, $vacancy_id, $examDetail);
            $sentCount = (int) ($notificationResult['sent'] ?? 0);
            $failedCount = (int) ($notificationResult['failed'] ?? 0);
            $skippedCount = (int) ($notificationResult['skipped'] ?? 0);

            if ($sentCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No exam links were sent. Please check applicant emails and mail configuration.',
                    'sent_count' => 0,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                ], 500);
            }

            // Update exam details as notified
            $examDetail->update([
                'notified_at' => now(),
                'link_sent' => true,
                'link_sent_at' => now()
            ]);

            activity()
                ->causedBy(auth('admin')->user())
                ->event('notify')
                ->withProperties([
                    'vacancy_id' => $vacancy_id,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                    'section' => 'Exam Management',
                ])
                ->log('Sent exam notifications for applicants.');

            $message = $failedCount > 0 || $skippedCount > 0
                ? "{$sentCount} applicant(s) notified; {$failedCount} failed, {$skippedCount} skipped."
                : "{$sentCount} applicant(s) notified successfully.";

            return response()->json([
                'success' => true,
                'notified_at' => now()->format('Y-m-d H:i:s'),
                'link_sent_at' => now()->format('Y-m-d H:i:s'),
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'skipped_count' => $skippedCount,
                'partial' => ($failedCount > 0 || $skippedCount > 0),
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error("Error notifying applicants: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send exam schedule notification (no join link) to all applicants.
     * Used by "Save & Notify Applicants".
     */
    public function notifyApplicantsSchedule(Request $request, $vacancy_id)
    {
        try {
            $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();
            if (!$examDetail || !$examDetail->details_saved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please save exam details first before notifying applicants.'
                ], 400);
            }

            $participants = Applications::where('vacancy_id', $vacancy_id)->get();
            if ($participants->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No participants found for this vacancy.']);
            }

            $sentCount = 0;
            $failedCount = 0;
            $skippedCount = 0;
            foreach ($participants as $app) {
                $user = User::find($app->user_id);
                if (!$user || empty($user->email)) {
                    $skippedCount++;
                    continue;
                }

                try {
                    Mail::to($user->email)->sendNow(new NotifyApplicantMail($vacancy_id, $user->id, $examDetail->id));
                    $sentCount++;
                } catch (\Throwable $mailException) {
                    $failedCount++;
                    Log::error('Schedule email send failed', [
                        'vacancy_id' => $vacancy_id,
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $mailException->getMessage(),
                    ]);
                }
            }

            if ($sentCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedule emails were sent. Please check applicant emails and mail configuration.',
                    'sent_count' => 0,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                ], 500);
            }

            $examDetail->update([
                'notified_at' => now(),
            ]);

            activity()
                ->causedBy(auth('admin')->user())
                ->event('notify_schedule')
                ->withProperties([
                    'vacancy_id' => $vacancy_id,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                    'section' => 'Exam Management',
                ])
                ->log('Sent exam schedule notifications for applicants.');

            $message = $failedCount > 0 || $skippedCount > 0
                ? "Exam schedule emails sent to {$sentCount} applicant(s); {$failedCount} failed, {$skippedCount} skipped."
                : "Exam schedule emails sent to {$sentCount} applicant(s).";

            return response()->json([
                'success' => true,
                'notified_at' => now()->format('Y-m-d H:i:s'),
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'skipped_count' => $skippedCount,
                'partial' => ($failedCount > 0 || $skippedCount > 0),
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error("Error notifying applicants (schedule): " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function notifySelectedApplicants(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot send exam notifications.')) {
            return $denied;
        }

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

            $notificationResult = $this->sendRefinedNotifications($userIds, $vacancy_id, $examDetail);
            $sentCount = (int) ($notificationResult['sent'] ?? 0);
            $failedCount = (int) ($notificationResult['failed'] ?? 0);
            $skippedCount = (int) ($notificationResult['skipped'] ?? 0);

            if ($sentCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No exam links were sent to selected applicants.',
                    'sent_count' => 0,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                ], 500);
            }

            activity()
                ->causedBy(auth('admin')->user())
                ->event('notify_selected')
                ->withProperties([
                    'vacancy_id' => $vacancy_id,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                    'section' => 'Exam Management',
                ])
                ->log('Sent exam notifications for selected applicants.');

            $message = $failedCount > 0 || $skippedCount > 0
                ? "{$sentCount} applicant(s) notified; {$failedCount} failed, {$skippedCount} skipped."
                : "{$sentCount} applicants notified successfully.";

            return response()->json([
                'success' => true,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'skipped_count' => $skippedCount,
                'partial' => ($failedCount > 0 || $skippedCount > 0),
                'message' => $message
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
            $result = [
                'sent' => 0,
                'failed' => 0,
                'skipped' => 0,
            ];

            foreach ($userIds as $user_id) {
                // Find application
                $application = Applications::where('vacancy_id', $vacancy_id)
                    ->where('user_id', $user_id)
                    ->lockForUpdate()
                    ->first();

                if (!$application) {
                    $result['skipped']++;
                    continue;
                }

                // Generate token for this send.
                $token = Str::random(64);
                $expiresAt = now()->addMinutes(2);

                $application->update([
                    'exam_token' => $token,
                    'exam_token_expires_at' => $expiresAt,
                    'link_sent_at' => now(),
                    'exam_token_used_at' => null,
                    'exam_token_device_id' => null,
                    'exam_token_used_ip' => null,
                    'exam_token_used_ua' => null,
                ]);

                try {
                    // Send immediately so the endpoint reflects real delivery attempts.
                    SendExamNotification::dispatchSync($vacancy_id, $user_id, $examDetail->id, $sender_email);
                    $result['sent']++;
                } catch (\Throwable $sendException) {
                    $result['failed']++;
                    Log::error('Failed to send exam link notification', [
                        'vacancy_id' => $vacancy_id,
                        'user_id' => $user_id,
                        'error' => $sendException->getMessage(),
                    ]);
                }
            }
            return $result;
        });
    }

    public function saveExamDetails(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot modify exam details.')) {
            return $denied;
        }

        try {
            Log::info('saveExamDetails called', ['vacancy_id' => $vacancy_id, 'notify' => $request->boolean('notify')]);

            $validated = $request->validate([
                'time' => 'required',
                'time_end' => 'required',
                'date' => 'required|date',
                'place' => 'required|string',
                'duration' => 'required|integer',
                'message' => 'nullable|string',
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
            $notifyMessage = null;
            $sentCount = 0;
            $failedCount = 0;
            $skippedCount = 0;

            if ($request->boolean('notify')) {
                Log::info('Calling notifyApplicantsSchedule', ['vacancy_id' => $vacancy_id]);
                $response = $this->notifyApplicantsSchedule($request, $vacancy_id);

                // Check if notification was successful
                $responseData = $response->getData(true);
                if (isset($responseData['success']) && $responseData['success']) {
                    $examDetails->refresh();
                    $notified = true;
                    $notified_at = $examDetails->notified_at;
                    $notifyMessage = $responseData['message'] ?? null;
                    $sentCount = (int) ($responseData['sent_count'] ?? 0);
                    $failedCount = (int) ($responseData['failed_count'] ?? 0);
                    $skippedCount = (int) ($responseData['skipped_count'] ?? 0);
                    Log::info('Schedule notifications sent successfully', ['vacancy_id' => $vacancy_id]);
                } else {
                    Log::error('Schedule notification failed', ['vacancy_id' => $vacancy_id, 'response' => $responseData]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Exam details saved, but schedule notification failed: ' . ($responseData['message'] ?? 'Unknown error')
                    ], 500);
                }
            }

            activity()
                ->causedBy(auth('admin')->user())
                ->event('save')
                ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
                ->log('Saved exam schedule and details.');

            Log::info('saveExamDetails completed successfully', ['vacancy_id' => $vacancy_id, 'notified' => $notified]);

            return response()->json([
                'success' => true,
                'message' => 'Exam details saved.',
                'examDetails' => $examDetails,
                'notified' => $notified,
                'notified_at' => $notified_at,
                'notify_message' => $notifyMessage,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'skipped_count' => $skippedCount,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in saveExamDetails', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', array_map(fn($err) => implode(', ', $err), $e->errors()))
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in saveExamDetails', [
                'vacancy_id' => $vacancy_id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function startExam(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot start exams.')) {
            return $denied;
        }

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
