<?php


namespace App\Http\Controllers;

use App\Models\JobVacancy;
use App\Models\ExamDetail;
use App\Models\ExamItems;
use App\Models\Applications;
use Illuminate\Http\Request;
use App\Models\Vacancy;
use App\Models\UploadedDocument;
use App\Models\PersonalInformation;
use App\Models\WorkExperience;
use App\Models\CivilServiceEligibility;
use App\Models\VoluntaryWork;
use App\Models\OtherInformation;
use App\Models\MiscInfos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use App\Models\WorkExpSheet;

use function Symfony\Component\String\s;

class JobVacancyController extends Controller
{
    public function jobVacancy()
    {
        $jobVacancies = JobVacancy::orderByRaw("CASE WHEN status = 'OPEN' THEN 1 ELSE 2 END")
            ->orderBy('closing_date', 'asc')
            ->get();

        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Viewed job vacancy list.');
        */

        return view('dashboard_user.job_vacancy', ['vacancies' => $jobVacancies]);
    }

    public function jobVacancyManagement()
    {
        $jobVacancies = JobVacancy::orderByRaw("CASE WHEN status = 'OPEN' THEN 1 ELSE 2 END")
            ->orderBy('closing_date', 'asc')
            ->get();

        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Accessed job vacancy management page.');
        */

        return view('admin.vacancies_management', ['vacancies' => $jobVacancies]);
    }

    public function edit($vacancy_id)
    {
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();
        $signatories = \App\Models\Signatory::all();
        $view = $vacancy->vacancy_type === 'Plantilla' ? 'admin.vacancy_add_plantilla' : 'admin.vacancy_add_cos';

        activity()
            ->event('view')
            ->causedBy(auth('admin')->user())
            ->performedOn($vacancy)
            ->withProperties(['vacancy_id' => $vacancy->vacancy_id, 'section' => 'Job Vacancy'])
            ->log('Editing job vacancy.');

        return view($view, ['vacancy' => $vacancy, 'signatories' => $signatories]);
    }

    public function update(Request $request, $vacancy_id)
    {
        $validated = $request->validate([
            'vacancy_type' => 'required|in:Plantilla,COS',
            'position_title' => 'required|string|max:255',
            'monthly_salary' => 'required|numeric',
            'place_of_assignment' => 'required|string',
            //'vacancies' => 'required|integer|min:1',
            'closing_date' => 'required|date',
            //'status' => 'required|in:OPEN,CLOSED',
            'qualification_education' => 'required|string',
            'qualification_experience' => 'required|string',
            'qualification_training' => 'required|string',
            'qualification_eligibility' => 'required|string',

            // Plantilla-only
            'competencies' => 'nullable|string',

            // COS only
            'scope_of_work' => 'nullable|string',
            'expected_output' => 'nullable|string',
            'duration_of_work' => 'nullable|string',

            'to_person' => 'required|string',
            'to_position' => 'required|string',
            'to_office' => 'required|string',
            'to_office_address' => 'required|string',

            'salary_grade' => 'nullable|string',
            'pcn_no' => 'nullable|string',
            'plantilla_item_no' => 'nullable|string',
        ]);

        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();

        $changes = [];
        foreach ($validated as $key => $value) {
            if ($vacancy->$key != $value) {
                $changes[$key] = [
                    'old' => $vacancy->$key,
                    'new' => $value
                ];
            }
        }


        $closingDate = Carbon::parse($validated['closing_date']);
        $today = Carbon::today();

        $status = $closingDate->lt($today) ? 'CLOSED' : 'OPEN';

        $vacancy->update([
            'vacancy_type' => $validated['vacancy_type'],
            'position_title' => $validated['position_title'],
            'monthly_salary' => $validated['monthly_salary'],
            'place_of_assignment' => $validated['place_of_assignment'],
            //'vacancies' => $validated['vacancies'],
            'closing_date' => $validated['closing_date'],
            'status' => $status,

            'qualification_education' => $validated['qualification_education'],
            'qualification_experience' => $validated['qualification_experience'],
            'qualification_training' => $validated['qualification_training'],
            'qualification_eligibility' => $validated['qualification_eligibility'],

            // Plantilla only
            'competencies' => $validated['competencies'] ?? null,

            // COS-only
            'expected_output' => $validated['expected_output'] ?? null,
            'scope_of_work' => $validated['scope_of_work'] ?? null,
            'duration_of_work' => $validated['duration_of_work'] ?? null,

            'to_person' => $validated['to_person'],
            'to_position' => $validated['to_position'],
            'to_office' => $validated['to_office'],
            'to_office_address' => $validated['to_office_address'],

            'salary_grade' => $validated['salary_grade'] ?? null,
            'pcn_no' => $validated['pcn_no'] ?? null,
            'plantilla_item_no' => $validated['plantilla_item_no'] ?? null,

            'last_modified_by' => Auth::user()?->name ?? 'System',
            'last_modified_at' => now(),
        ]);

        if (!empty($changes)) {
            activity()
                ->event('edit')
                ->causedBy(auth('admin')->user())
                ->performedOn($vacancy)
                ->withProperties(['changes' => $changes, 'section' => 'Job Vacancy'])
                ->log('Updated job vacancy fields.');
        }


        return redirect()->route('vacancies_management')->with('success', 'Job vacancy updated successfully.');
    }

    public function storeVacancy(Request $request)
    {
        //try {
        $validated = $request->validate([
            'position_title' => 'required|string|max:255',
            'vacancy_type' => 'required|in:COS,Plantilla',
            'pcn_no' => 'nullable|string',
            'plantilla_item_no' => 'nullable|string',
            'closing_date' => 'required|date',
            //'status' => 'required|in:OPEN,CLOSED',
            'monthly_salary' => 'required|numeric',
            'salary_grade' => 'nullable|string',
            'place_of_assignment' => 'required|string',

            // Qualification standards
            'qualification_education' => 'required|string',
            'qualification_training' => 'required|string',
            'qualification_experience' => 'required|string',
            'qualification_eligibility' => 'required|string',

            // Plantilla-only
            'competencies' => 'nullable|string',

            // COS-only
            'expected_output' => 'nullable|string',
            'scope_of_work' => 'nullable|string',
            'duration_of_work' => 'nullable|string',

            // Application submission
            'to_person' => 'required|string',
            'to_position' => 'required|string',
            'to_office' => 'required|string',
            'to_office_address' => 'required|string',
        ]);

        // 🔷 Generate vacancy_id
        /*
        $positionTitle = $validated['position_title'];
        $words = preg_split('/\s+/', $positionTitle);
        $ranks = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII'];
        $filteredWords = array_values(array_filter($words, fn($word) => !in_array(strtoupper($word), $ranks)));

        if(count($filteredWords) == 1){
            $letters = strtoupper(substr($filteredWords[0], 0, 3));
        } else {
            $letters = '';
            for($i = 0; $i < min(3, count($filteredWords)); $i++){
                $letters .= strtoupper(substr($filteredWords[$i], 0, 1));
            }
        }

        $latestVacancy = JobVacancy::where('vacancy_id', 'like', $letters . '-%')->latest('vacancy_id')->first();
        $num = $latestVacancy ? intval(substr($latestVacancy->vacancy_id, strpos($latestVacancy->vacancy_id, '-') + 1)) + 1 : 1;
        $vacancy_id = $letters . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        */

        $closingDate = Carbon::parse($validated['closing_date']);
        $today = Carbon::today();

        $status = $closingDate->lt($today) ? 'CLOSED' : 'OPEN';

        // 🔷 Create vacancy
        $vacancy = JobVacancy::create([
            //'vacancy_id' => $vacancy_id,
            'position_title' => $validated['position_title'],
            'vacancy_type' => $validated['vacancy_type'],
            'pcn_no' => $validated['pcn_no'] ?? null,
            'plantilla_item_no' => $validated['plantilla_item_no'] ?? null,
            'closing_date' => $validated['closing_date'],

            'status' => $status,
            'monthly_salary' => $validated['monthly_salary'],
            'salary_grade' => $validated['salary_grade'] ?? null,
            'place_of_assignment' => $validated['place_of_assignment'],

            // Qualification standards
            'qualification_education' => $validated['qualification_education'],
            'qualification_training' => $validated['qualification_training'],
            'qualification_experience' => $validated['qualification_experience'],
            'qualification_eligibility' => $validated['qualification_eligibility'],

            // Plantilla only
            'competencies' => $validated['competencies'] ?? null,

            // COS only
            'expected_output' => $validated['expected_output'] ?? null,
            'scope_of_work' => $validated['scope_of_work'] ?? null,
            'duration_of_work' => $validated['duration_of_work'] ?? null,


            // Application submission
            'to_person' => $validated['to_person'],
            'to_position' => $validated['to_position'],
            'to_office' => $validated['to_office'],
            'to_office_address' => $validated['to_office_address'],
        ]);


        ExamDetail::create(['vacancy_id' => $vacancy->vacancy_id]);
        Log::info('Competencies field debug:', ['competencies' => $validated['competencies'] ?? 'NOT SET']);

        activity()
            ->event('create')
            ->causedBy(auth('admin')->user())
            ->performedOn($vacancy)
            ->withProperties(['vacancy_id' => $vacancy->vacancy_id, 'section' => 'Job Vacancy'])
            ->log('Created new job vacancy.');


        return redirect()->route('vacancies_management')->with('success', 'Vacancy created successfully.');
        /*} catch (\Exception $e) {
            Log::error('Vacancy Store Error: '.$e->getMessage());
            Log::error('Request Data: ' . json_encode($request->all()));
            return back()->with('error', 'Error: '.$e->getMessage());
        }*/

    }


    public function delete(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();

        ExamDetail::where('vacancy_id', $vacancy_id)->delete();
        ExamItems::where('vacancy_id', $vacancy_id)->delete();
        Applications::where('vacancy_id', $vacancy_id)->delete();

        $vacancy->delete();

        activity()
            ->event('delete')
            ->causedBy(auth('admin')->user())
            ->performedOn($vacancy)
            ->withProperties(['position_title' => $vacancy->position_title, 'section' => 'Job Vacancy'])
            ->log('Deleted job vacancy.');


        return redirect()->route('vacancies_management')->with('success', 'Vacancy deleted successfully.');
    }

    public function jobDescription(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::with('applications')->where('vacancy_id', $vacancy_id)->firstOrFail();

        $hasPDS = PersonalInformation::where('user_id', Auth::id())->exists();

        $hasApplied = $vacancy->applications->contains('user_id', Auth::id());

        return view('dashboard_user.job_description', [
            'vacancy' => $vacancy,
            'hasPDS' => $hasPDS,
            'hasApplied' => $hasApplied,
        ]);

        /*
        activity()
            ->causedBy(auth()->user())
            ->performedOn($vacancy)
            ->withProperties(['vacancy_id' => $vacancy->vacancy_id])
            ->log('Viewed job description.');
        */

        return view('dashboard_user.job_description', ['vacancy' => $vacancy, 'hasPDS' => $hasPDS]);
    }

    public function adminFilterVacancy(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        $job = $request->get('job');

        $vacancies = JobVacancy::when($status, function ($query) use ($status) {
            $query->where('status', $status);
        })
            ->when($job, function ($query) use ($job) {
                $query->where('vacancy_type', $job);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q
                        ->orWhere('vacancy_id', 'like', "%{$search}%")
                        ->orWhere('position_title', 'like', "%{$search}%")
                        ->orWhere('vacancy_type', 'like', "%{$search}%")
                        ->orWhere('monthly_salary', 'like', "%{$search}%")
                        ->orWhere('place_of_assignment', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('closing_date', 'like', "%{$search}%")
                        ->orWhere('qualification_education', 'like', "%{$search}%")
                        ->orWhere('qualification_training', 'like', "%{$search}%")
                        ->orWhere('qualification_experience', 'like', "%{$search}%")
                        ->orWhere('qualification_eligibility', 'like', "%{$search}%")
                        ->orWhere('scope_of_work', 'like', "%{$search}%")
                        ->orWhere('expected_output', 'like', "%{$search}%")
                        ->orWhere('duration_of_work', 'like', "%{$search}%")
                        ->orWhere('to_person', 'like', "%{$search}%")
                        ->orWhere('to_position', 'like', "%{$search}%")
                        ->orWhere('to_office', 'like', "%{$search}%")
                        ->orWhere('to_office_address', 'like', "%{$search}%")
                        ->orWhere('created_at', 'like', "%{$search}%")
                        ->orWhere('updated_at', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE WHEN status = 'OPEN' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

        session(['vacancyFilterSearch' => $search]);
        session(['vacancyFilterJob' => $job]);
        session(['vacancyFilterStatus' => $status]);

        info(session()->all());
        //dd(session()->all());

        /*activity()
            ->causedBy(auth()->user())
            ->log('Filtered job vacancies (admin).');
        */

        return view('partials.admin_vacancy_list', compact('vacancies'))->render();
    }


    public function filterVacancy(Request $request)
    {
        $vacancies = JobVacancy::query();

        if ($request->status) {
            $vacancies->where('status', $request->status);
        }

        if ($request->type) {
            $vacancies->where('vacancy_type', $request->type);
        }

        if ($request->place) {
            $vacancies->where('place_of_assignment', $request->place);
        }

        if ($request->salary) {
            [$min, $max] = explode('-', $request->salary);
            $vacancies->whereBetween('monthly_salary', [$min * 1000, $max * 1000]);
        }

        if ($request->sort == 'latest') {
            $vacancies->orderBy('created_at', 'desc');
        } elseif ($request->sort == 'oldest') {
            $vacancies->orderBy('created_at', 'asc');
        }

        $vacancies = $vacancies->get();

        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Filtered job vacancies (user).');

        */

        return view('partials.vacancy_list', compact('vacancies'))->render();
    }

    public function getOpenVacanciesForDashboard()
    {
        $userId = Auth::id();

        $vacancies = JobVacancy::where('status', 'OPEN')->orderBy('created_at', 'desc')->get();

        $applications = \App\Models\Applications::where('user_id', $userId)
            ->with(['vacancy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdsProgress = (int) round($this->calculatePdsProgress(Auth::id()));
        $hasPDS = PersonalInformation::where('user_id', Auth::id())->exists();
        $hasWES = WorkExpSheet::where('user_id', Auth::id())->exists();

        // Application Status Summary
        $statusSummary = $applications->groupBy('status')->map->count();

        // COS vs Plantilla counts (OPEN)
        $cosVacancyCount = $vacancies->where('vacancy_type', 'COS')->count();
        $plantillaVacancyCount = $vacancies->where('vacancy_type', 'Plantilla')->count();

        // Upcoming exams for user's applied vacancies
        $vacancyIds = $applications->pluck('vacancy_id')->filter()->unique()->values();
        $now = Carbon::now()->toDateTimeString();
        $upcomingExams = ExamDetail::whereIn('vacancy_id', $vacancyIds)
            ->whereRaw("STR_TO_DATE(CONCAT(`date`, ' ', `time`), '%Y-%m-%d %H:%i:%s') > ?", [$now])
            ->orderByRaw("STR_TO_DATE(CONCAT(`date`, ' ', `time`), '%Y-%m-%d %H:%i:%s')")
            ->with('vacancy')
            ->get();

        // Required Documents Status
        $uploadedDocuments = UploadedDocument::where('user_id', $userId)->get()->keyBy('document_type');
        $documentStatusSummary = [];
        foreach (UploadedDocument::DOCUMENTS as $docType) {
            if ($docType === 'isApproved') continue;
            $doc = $uploadedDocuments->get($docType);
            $documentStatusSummary[] = [
                'type' => $docType,
                'status' => $doc ? ($doc->status ?? 'PENDING') : 'Not Submitted',
            ];
        }
        // Include quick flags for PDS/WES completion
        $documentStatusSummary[] = ['type' => 'pds', 'status' => $hasPDS ? 'Completed' : 'Incomplete'];
        $documentStatusSummary[] = ['type' => 'wes', 'status' => $hasWES ? 'Completed' : 'Incomplete'];

        // Recently closed positions among user's applications
        $recentlyClosedApplications = $applications->filter(function ($app) {
            return $app->vacancy && $app->vacancy->status === 'CLOSED';
        })->values();

        // Deadline countdown per active application
        $deadlineCountdown = $applications
            ->filter(function ($app) {
                return $app->deadline_date && $app->deadline_time && strtolower($app->status) !== 'closed';
            })
            ->map(function ($app) {
                $deadline = Carbon::parse($app->deadline_date . ' ' . $app->deadline_time);
                return [
                    'vacancy_id' => $app->vacancy_id,
                    'position_title' => $app->vacancy->position_title ?? '',
                    'deadline' => $deadline->toDateTimeString(),
                    'days_remaining' => Carbon::now()->diffInDays($deadline, false),
                ];
            })
            ->sortBy('days_remaining')
            ->values();

        // Notifications/Alerts (latest 5, and unread count)
        $recentNotifications = Auth::user()?->notifications()->orderBy('created_at', 'desc')->take(5)->get() ?? collect();
        $unreadNotificationsCount = Auth::user()?->unreadNotifications()->count() ?? 0;

        return view('dashboard_user.dashboard_user', [
            'vacancies' => $vacancies,
            'applications' => $applications,
            'pdsProgress' => $pdsProgress,
            'hasPDS' => $hasPDS,
            'hasWES' => $hasWES,
            'statusSummary' => $statusSummary,
            'cosVacancyCount' => $cosVacancyCount,
            'plantillaVacancyCount' => $plantillaVacancyCount,
            'upcomingExams' => $upcomingExams,
            'documentStatusSummary' => $documentStatusSummary,
            'recentlyClosedApplications' => $recentlyClosedApplications,
            'deadlineCountdown' => $deadlineCountdown,
            'recentNotifications' => $recentNotifications,
            'unreadNotificationsCount' => $unreadNotificationsCount,
        ]);

    }


    public function apply(Request $request, $vacancy_id)
    {
        $request->validate([
            'application_file' => 'required|file|mimes:pdf|max:5120', // max 5MB
        ]);
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();

        // Check if user already applied
        $existing = \App\Models\Applications::where('user_id', Auth::id())
            ->where('vacancy_id', $vacancy->vacancy_id)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'You have already applied for this vacancy.');
        }

        // Handle file upload
        $file = $request->file('application_file');
        $hashed_name = $file->hashName(); // auto-generates unique name
        $store_path = $file->store("uploads/application-files", 'public'); // stores in public/uploads/application-files


        // Create application
        \App\Models\Applications::create([
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'Pending',
            'is_valid' => true,

            'file_original_name' => $file->getClientOriginalName(),
            'file_stored_name' => $hashed_name,
            'file_storage_path' => $store_path,
            'file_status' => 'Submitted',
            'file_remarks' => null,
            'file_size_8b' => $file->getSize(),
        ]);

        // Create notification for admin
        \App\Models\Notification::create([
            'title' => 'New Applicant',
            'message' => 'Applicant ' . Auth::user()->name . ' has applied for ' . $vacancy->position_title,
            'type' => 'info',
            'link' => route('admin.manage_applicants', ['vacancy_id' => $vacancy->vacancy_id]),
            'is_read' => false
        ]);

        activity()
            ->event('apply job')
            ->causedBy(Auth::user())
            ->performedOn($vacancy)
            ->withProperties(['vacancy_id' => $vacancy->vacancy_id, 'section' => 'Job Vacancy'])
            ->log('Applied to job vacancy.');

        return redirect()->back()->with('success', 'Application submitted successfully!');
    }

    public function myApplications()
    {
        $applications = Applications::where('user_id', Auth::id())
            ->with('vacancy')
            ->orderBy('created_at', 'desc')
            ->get();
        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Viewed my applications.');
        */

        return view('dashboard_user.my_applications', compact('applications'));
    }

    // USEREND application status
    public function applicationStatus($user_id, $vacancy_id)
    {
        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->with(['personalInformation', 'vacancy'])
            ->firstOrFail();

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first(); // 🟢 Get exam for this vacancy

        // Get who last updated the application
        $adminName = null;
        if ($application->updated_at && $application->updated_at != $application->created_at) {
            $adminUser = $application->updatedByAdmin;
            if ($adminUser) {
                $adminName = $adminUser->username;
            }
        }

        // TODO create controller here for Application Status
        $userId = Auth::id();
        $uploadedDocuments = UploadedDocument::where('user_id', $userId)->get()->keyBy('document_type');
        $documents = [];

        $labelMap = [
            'application_letter' => 'Application Letter',
            'signed_pds' => 'Signed Personal Data Sheet',
            'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
            'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
            'cert_eligibility' => 'Certificate of Eligibility / Board Rating',
            'ipcr' => 'Performance Rating/IPCR in the last period (if applicable)',
            'non_academic' => 'Non-Academic Awards Received',
            'cert_training' => 'Certificate/s of Training Attended/Participated relevant to the position being applied',
            'designation_order' => 'List with Certified Photocopy of Duly Confirmed Designation Order/s',
            'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
            'photocopy_diploma' => 'Diploma',
            'grade_masteraldoctorate' => 'Certified Photocopy of Certificate of Grades with Masteral/Doctorate Units Earned',
            'tor_masteraldoctorate' => 'Certified Photocopy of TOR with Masteral/Doctorate Degree',
            'cert_employment' => 'Certificate of Employment (If Any)',
            'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
            'passport_photo' => '2" x 2" or Passport Size Picture',
            'other_documents' => 'Other Documents Submitted',
        ];

        foreach (UploadedDocument::DOCUMENTS as $docType) {
            // Skip "isApproved" since it's not a document
            if ($docType === 'isApproved')
                continue;

            $doc = $uploadedDocuments->get($docType);

            if ($docType === 'application_letter') {
                $documents[] = [ // Get from Applications table instead
                    'id' => 'application_letter',
                    'text' => $labelMap['application_letter'],
                    'status' => $application->file_status ?? 'invalid',
                    //'preview' => $application->file_storage_path ? asset('storage/' . $application->file_storage_path) : '',
                    'preview' => $application->file_storage_path
                        ? url('/preview-file/' . base64_encode($application->file_storage_path))
                        : '',
                    'remarks' => $application->file_remarks ?? 'No remarks provided.',
                    'isBold' => true,
                ];
            } else {
                $doc = $uploadedDocuments->get($docType);

                $documents[] = [
                    'id' => $docType,
                    'text' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'status' => $doc ? $doc->status : 'invalid',
                    //'preview' => $doc ? asset('storage/' . $doc->storage_path) : '',
                    'preview' => $doc ? url('/preview-file/' . base64_encode($doc->storage_path)) : '',
                    'remarks' => $doc ? ($doc->remarks ?: $doc->original_name) : 'Document missing.',
                    'isBold' => true,
                ];
            }
        }

        /*
        activity()
            ->causedBy(auth()->user())
            ->performedOn($application)
            ->withProperties(['vacancy_id' => $application->vacancy_id])
            ->log('Viewed application status.');
        */

        return view('dashboard_user.application_status', compact('application', 'examDetail', 'documents', 'adminName'));
    }

    public function calculatePdsProgress($userId)
    {
        $progress = 0;

        // C1: Personal, Family, and Education Info
        $c1 = \App\Models\PersonalInformation::where('user_id', $userId)->first();
        $family = \App\Models\FamilyBackground::where('user_id', $userId)->first();
        $education = \App\Models\EducationalBackground::where('user_id', $userId)->first();

        $simpleFields = [
            // Personal Info 14 fields
            'surname',
            'first_name',
            'civil_status',
            'date_of_birth',
            'place_of_birth',
            'citizenship',
            'sex',
            'mobile_no',
            'email_address',
            'height',
            'weight',
            'permanent_address',
            'residential_address',
            // Family Background
            'mother_maiden_surname',
            'mother_maiden_first_name',

            // Elementary
            'elem_year_graduated',
            'elem_from',
            'elem_to',
            'elem_school',

            // Junior High
            'jhs_year_graduated',
            'jhs_from',
            'jhs_to',
            'jhs_school',

            //College 2 fields
            'college',
            'grad',

        ];

        $filledFields = collect($simpleFields)->filter(function ($field) use ($c1, $family, $education) {
            return !empty($c1?->$field ?? $family?->$field ?? $education?->$field);
        });


        $c1Progress = round(($filledFields->count() / count($simpleFields)) * 20);
        $progress += $c1Progress;

        //C2 will automatically grant 20% if C4 is reached
        // If the user has proceeded to MiscInfos (C4), we assume C2 is complete or intentionally skipped
        $hasReachedC4 = \App\Models\MiscInfos::where('user_id', $userId)->exists();
        $progress += $hasReachedC4 ? 20 : 0;

        // C3: Automatically grant 20% if C4 started
        $hasReachedC4 = \App\Models\MiscInfos::where('user_id', $userId)->exists();
        $progress += $hasReachedC4 ? 20 : 0;

        // C4: Misc Info Section
        $c4 = \App\Models\MiscInfos::where('user_id', $userId)->first();
        $c4Fields = [
            'related_34_a',
            'related_34_b',
            'guilty_35_a',
            'criminal_35_b',
            'convicted_36',
            'separated_37',
            'candidate_38',
            'resigned_38_b',
            'immigrant_39',
            'indigenous_40_a',
            'pwd_40_b',
            'solo_parent_40_c',
            'govt_id_type',
            'govt_id_number',
            'govt_id_date_issued',
            'govt_id_place_issued',
            'photo_upload',
        ];

        $c4Filled = collect($c4Fields)->filter(function ($field) use ($c4) {
            $value = $c4?->$field;
            return is_array($value) ? !empty(array_filter($value)) : !empty($value);
        });

        $c4Progress = round(($c4Filled->count() / count($c4Fields)) * 20);
        $progress += $c4Progress;

        // C5: Uploaded PDF Documents
        $c5Documents = \App\Models\UploadedDocument::where('user_id', $userId)->get();
        $pdfFields = [
            'pqe_result',
            'cert_eligibility',
            'ipcr',
            'non_academic',
            'cert_training',
            'designation_order',
            'transcript_records',
            'photocopy_diploma',
            'grade_masteraldoctorate',
            'tor_masteraldoctorate',
            'cert_employment',
            'cert_lgoo_induction',
            'passport_photo',
            'other_documents',
        ];

        $c5Filled = collect($pdfFields)->filter(fn($type) => $c5Documents->contains('document_type', $type));
        $c5Progress = round(($c5Filled->count() / count($pdfFields)) * 20);
        $progress += $c5Progress;

        return min($progress, 100);
    }

    public function sortMyApplications(Request $request)
    {
        $sortOrder = $request->query('sort_order', 'latest');

        $query = Applications::with('vacancy')
            ->where('user_id', Auth::id());

        if ($sortOrder === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $applications = $query->get();

        return view('partials.application_list_container', compact('applications'))->render();
    }
}
