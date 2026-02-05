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
use App\Mail\NotifyApplicantMail;
use Spatie\Activitylog\Models\Activity;


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

    // Update the answers field
    $answerRecord->answers = $validated['answers'];
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

        activity()
            ->causedBy(auth('admin')->user())
            ->event('view')
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
            ->log('Accessed edit exam page.');

        return view('admin.exam_edit', ['exam_items' => $exam_items, 'vacancy_id' => $vacancy_id]);
    }

    public function updateExam(Request $request, $vacancy_id)
    {
        $questions = json_decode($request->questions, true);

        // Validate if needed
        foreach ($questions as $q) {
            // Example validation logic per question
            if (!isset($q['text']) || empty($q['text'])) {
                return back()->withErrors(['msg' => 'Each question must have text.']);
            }
        }
        $existingItemsCount = ExamItems::where('vacancy_id', $vacancy_id)->count();

        // Example: if you want to delete existing and insert all new questions
        ExamItems::where('vacancy_id', $vacancy_id)->delete();

        foreach ($questions as $q) {
            ExamItems::insert([
                'vacancy_id' => $vacancy_id,
                'question' => $q['text'],
                'is_essay' => $q['type'] === 'Essay' ? 1 : 0,
                'ans' => $q['type'] === 'MCQ' ? $q['answer'] : null,
                'choices' => $q['type'] === 'MCQ' ? json_encode($q['choices']) : null,
                'updated_at' => now(),
            ]);
        }

        $exam_items = ExamItems::where('vacancy_id', $vacancy_id)->get();

        $action = ($existingItemsCount > 0) ? 'update' : 'ureate';

        activity()
            ->causedBy(auth('admin')->user())
            ->event($action)
            ->withProperties(['vacancy_id' => $vacancy_id, 'questions_count' => count($questions), 'section' => 'Exam Management'])
            ->log($action . 'd exam questions.');


        return redirect()->route('admin.exam.edit', ['exam_items' => $exam_items, 'vacancy_id' => $vacancy_id])->with('success', 'Exam updated successfully.');
    }

    public function examManagement(Request $request)
    {
        $search = $request->input('search');
        $jobType = $request->input('job_type');

        $jobVacancies = JobVacancy::query()
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('position_title', 'like', '%' . $search . '%')
                      ->orWhere('vacancy_id', 'like', '%' . $search . '%');
                });
            })
            ->when($jobType, function ($query, $jobType) {
                $query->where('vacancy_type', $jobType);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json($jobVacancies);
        }
/*
        activity()
            ->causedBy(auth()->user())
            ->log('Viewed exam management page.');
*/

        return view('admin.exam_management', [
            'vacancies' => $jobVacancies,
            'search' => $search
        ]);
    }

    public function manageExam(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::select('vacancy_id','position_title', 'vacancy_type')->where('vacancy_id', $vacancy_id)->first();
        $participants = Applications::where('vacancy_id', $vacancy_id)->get();
        $examDetails = ExamDetail::where('vacancy_id', $vacancy_id)->first();

        $user_name = [];
        foreach($participants as $p){
            $user_id = $p['user_id'];
            $user = User::find($user_id);
            $user_name[] = $user ? $user->name : 'Unknown User';
        }

        //info($user_name);
        //connect the user name here
        //info($vacancy);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
            ->log('Managed exam participants and details.');

        return view('admin.manage_exam', ['vacancy' => $vacancy, 'participants' => $participants, 'user_name' => $user_name, 'examDetails' => $examDetails]);
    }

    public function examLobby(Request $request, $vacancy_id){

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam'])
            ->log('Entered exam lobby.');

        return view('exam_user.exam_lobby', $vacancy_id);
    }

    public function examQuestion(Request $request, $vacancy_id){
        $columns = Schema::getColumnListing('exam_items');
        $columns = array_diff($columns, ['ans']);

        $examItems = ExamItems::select($columns)
            ->where('vacancy_id', $vacancy_id)
            ->get();

        //info($examItems);
        activity()
            ->causedBy(auth()->user())
            ->event('view')
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam'])
            ->log('Viewed exam questions page.');

        return view('exam_user.exam_question_page',  compact('vacancy_id', 'examItems'));
    }

    public function viewExam(Request $request, $vacancy_id, $user_id ){
        //dd($request->all());
        info($user_id);
        $application = Applications::select('user_id','answers', 'scores')->where('user_id', $user_id)->where('vacancy_id', $vacancy_id)->firstOrFail();
        $examItems = ExamItems::select('id','question', 'ans', 'is_essay')->where('vacancy_id', $vacancy_id)->get();
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

        return view('admin.exam_view_answers', ['examResults' => $examResults,
                                                'positionTitle' => $positionTitle,
                                                'vacancy_id' => $vacancy_id,
                                                'user_id' => $user_id,
                                                'userName' => $userName]);
    }

    public function saveResult(Request $request, $vacancy_id, $user_id){
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


        return redirect()->route('admin.manage_exam',  ['vacancy_id' => $vacancy_id, 'massage' => 'Result Saved!']);
    }

    public function notifyApplicants(Request $request, $vacancy_id)
    {

        $exam_id = ExamDetail::select('id')->where('vacancy_id', $vacancy_id)->first();

        // Example: get applications for this vacancy
        $participants = Applications::where('vacancy_id', $vacancy_id)->get();
        info($participants);
        foreach ($participants as $p) {
            $user_id = $p->user_id;
            $user_email = User::select('email')->where('id', $user_id)->firstOrFail();
            if ($user_id) {
                Mail::to($user_email)->queue(new NotifyApplicantMail($vacancy_id, $user_id, $exam_id->id));
            }
        }

        //info('check');
        ExamDetail::where('vacancy_id',$vacancy_id)->first()->update(['notified_at' => now()]);

        //info('check');

        activity()
            ->causedBy(auth('admin')->user())
            ->event('notify')
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
            ->log('Notified all applicants for exam.');

        return response()->json(['success' => true, 'notified_at' => now()->format('Y-m-d H:i:s'), 'message' => 'Applications notified.']);
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

        ExamDetail::updateOrCreate(
            ['vacancy_id' => $vacancy_id],
            $validated
        );

        $examDetails = ExamDetail::where('vacancy_id', $vacancy_id)->first();

        activity()
            ->causedBy(auth()->user())
            ->event('save')
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
            ->log('Saved exam schedule and details.');

        return response()->json(['success' => true, 'message' => 'Exam details saved.', 'examDetails' => $examDetails]);
    }

}
