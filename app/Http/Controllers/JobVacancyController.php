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
use App\Models\FamilyBackground;
use App\Models\EducationalBackground;
use App\Models\MiscInfos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use App\Models\WorkExpSheet;

use function Symfony\Component\String\s;

class JobVacancyController extends Controller
{
    private const DOCUMENT_TYPE_ALIASES = [
        'cert_eligibility' => ['cert_elegibility'],
        'cert_employment' => ['certificate_employment'],
        'grade_masteraldoctorate' => ['certificate_grades'],
        'tor_masteraldoctorate' => ['certified_tor'],
        'ipcr' => ['performance_rating'],
        'non_academic' => ['non_academic_awards'],
        'cert_training' => ['certificates_participation'],
        'designation_order' => ['designation_orders'],
        'transcript_records' => ['transcript'],
        'photocopy_diploma' => ['diploma'],
    ];

    private const COS_REQUIRED_DOCUMENTS = [
        'passport_photo',
        'signed_pds',
        'signed_work_exp_sheet',
        'photocopy_diploma',
        'application_letter',
        'cert_training',
    ];
    public function jobVacancy()
    {
        $jobVacancies = JobVacancy::select('job_vacancies.*')
            ->leftJoin('exam_details', 'job_vacancies.vacancy_id', '=', 'exam_details.vacancy_id')
            ->with('examDetail')
            ->orderByRaw("CASE 
                WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NULL THEN 1 
                WHEN job_vacancies.status = 'OPEN' THEN 2 
                ELSE 3 
            END")
            ->orderBy('job_vacancies.closing_date', 'asc')
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

        $status = 'OPEN';

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
            'closing_date' => 'required|date|after_or_equal:today',
            // 'status' => 'nullable|in:OPEN,CLOSED', // Status is auto-set to OPEN
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

        $status = 'OPEN'; // Default status for new vacancies

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
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();

        $hasPDS = PersonalInformation::where('user_id', Auth::id())->exists();
        $hasCompletedPdsForApply = Auth::check()
            ? $this->hasCompletedPdsForApply((int) Auth::id())
            : false;

        $hasApplied = Applications::where('user_id', Auth::id())
            ->where('vacancy_id', $vacancy_id)
            ->exists();

        $normalizedVacancyTrack = $this->normalizeTrack($vacancy->vacancy_type);
        $docTrackMismatchState = [
            'hasMismatch' => false,
            'submittedTrack' => null,
            'vacancyTrack' => $normalizedVacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $normalizedVacancyTrack,
                'vacancy_id' => $vacancy->vacancy_id,
                'fresh_upload' => 1,
            ]),
        ];
        $requiredDocsModalState = [
            'hasMissing' => false,
            'previewDocs' => [],
            'vacancyTrack' => $normalizedVacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $normalizedVacancyTrack,
                'vacancy_id' => $vacancy->vacancy_id,
                'fresh_upload' => 1,
            ]),
        ];

        if (Auth::check()) {
            $docTrackMismatchState = $this->getDocumentTrackMismatchState((int) Auth::id(), (string) $vacancy->vacancy_type, (string) $vacancy->vacancy_id);
            $requiredDocsModalState = $this->getRequiredDocsModalState((int) Auth::id(), (string) $vacancy->vacancy_type, (string) $vacancy->vacancy_id);
        }

        return view('dashboard_user.job_description', [
            'vacancy' => $vacancy,
            'hasPDS' => $hasPDS,
            'hasCompletedPdsForApply' => $hasCompletedPdsForApply,
            'hasApplied' => $hasApplied,
            'docTrackMismatch' => $docTrackMismatchState['hasMismatch'],
            'mismatchSubmittedTrack' => $docTrackMismatchState['submittedTrack'],
            'vacancyTrack' => $requiredDocsModalState['vacancyTrack'],
            'docUploadRedirectUrl' => $requiredDocsModalState['redirectUrl'],
            'hasMissingRequiredDocs' => $requiredDocsModalState['hasMissing'],
            'requiredDocsPreview' => $requiredDocsModalState['previewDocs'],
        ]);


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
        $vacancies = JobVacancy::select('job_vacancies.*')
            ->leftJoin('exam_details', 'job_vacancies.vacancy_id', '=', 'exam_details.vacancy_id')
            ->with('examDetail');

        if ($request->search) {
            $s = trim($request->search);
            $vacancies->where(function ($q) use ($s) {
                $q->where('position_title', 'like', "%{$s}%")
                    ->orWhere('place_of_assignment', 'like', "%{$s}%")
                    ->orWhere('job_vacancies.vacancy_id', 'like', "%{$s}%")
                    ->orWhere('vacancy_type', 'like', "%{$s}%");
            });
        }
        if ($request->status) {
            $vacancies->where('job_vacancies.status', $request->status);
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

        // Priority sorting: Unscheduled & Open first
        $vacancies->orderByRaw("CASE 
            WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NULL THEN 1 
            WHEN job_vacancies.status = 'OPEN' THEN 2 
            ELSE 3 
        END");

        if ($request->sort == 'latest') {
            $vacancies->orderBy('job_vacancies.created_at', 'desc');
        } elseif ($request->sort == 'oldest') {
            $vacancies->orderBy('job_vacancies.created_at', 'asc');
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
            if ($docType === 'isApproved')
                continue;
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
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();
        Log::info('Apply request received', [
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancy_id,
        ]);

        if (!$this->hasCompletedPdsForApply((int) Auth::id())) {
            Log::info('Apply blocked: incomplete PDS', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('pds_required_prompt', true);
        }

        // Check if user already applied
        $existing = \App\Models\Applications::where('user_id', Auth::id())
            ->where('vacancy_id', $vacancy->vacancy_id)
            ->first();

        if ($existing) {
            Log::info('Apply skipped: already applied', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
                'application_id' => $existing->id,
            ]);
            return redirect()
                ->route('my_applications')
                ->with('success', 'Application already exists for this vacancy.');
        }

        $requiredDocsModalState = $this->getRequiredDocsModalState((int) Auth::id(), (string) $vacancy->vacancy_type, (string) $vacancy->vacancy_id);
        if ($requiredDocsModalState['hasMissing']) {
            Log::info('Apply blocked: required docs missing', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('required_docs_prompt', [
                    'vacancy_id' => $vacancy->vacancy_id,
                    'vacancy_track' => $requiredDocsModalState['vacancyTrack'],
                    'redirect_url' => $requiredDocsModalState['redirectUrl'],
                    'preview_docs' => $requiredDocsModalState['previewDocs'],
                ]);
        }

        $docTrackMismatchState = $this->getDocumentTrackMismatchState((int) Auth::id(), (string) $vacancy->vacancy_type, (string) $vacancy->vacancy_id);
        if ($docTrackMismatchState['hasMismatch']) {
            Log::info('Apply blocked: doc track mismatch', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
                'submitted_track' => $docTrackMismatchState['submittedTrack'],
                'vacancy_track' => $docTrackMismatchState['vacancyTrack'],
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('doc_track_mismatch', [
                    'vacancy_id' => $vacancy->vacancy_id,
                    'submitted_track' => $docTrackMismatchState['submittedTrack'],
                    'vacancy_track' => $docTrackMismatchState['vacancyTrack'],
                    'redirect_url' => $docTrackMismatchState['redirectUrl'],
                ]);
        }

        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $applicationLetterDocQuery = UploadedDocument::where('user_id', Auth::id())
            ->where('document_type', 'application_letter')
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT');
        if ($supportsVacancyScopedDocs) {
            $applicationLetterDocQuery->where('vacancy_id', $vacancy->vacancy_id);
        }
        $applicationLetterDoc = $applicationLetterDocQuery
            ->latest('updated_at')
            ->first();

        if (!$applicationLetterDoc) {
            Log::info('Apply blocked: application letter not found in UploadedDocument', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('required_docs_prompt', [
                    'vacancy_track' => $requiredDocsModalState['vacancyTrack'],
                    'redirect_url' => $requiredDocsModalState['redirectUrl'],
                    'preview_docs' => $requiredDocsModalState['previewDocs'],
                ]);
        }


        // Create application
        $application = \App\Models\Applications::create([
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'Pending',
            'is_valid' => true,

            'file_original_name' => $applicationLetterDoc->original_name,
            'file_stored_name' => $applicationLetterDoc->stored_name,
            'file_storage_path' => $applicationLetterDoc->storage_path,
            'file_status' => 'Submitted',
            'file_remarks' => null,
            'file_size_8b' => $applicationLetterDoc->file_size_8b,
        ]);
        Log::info('Apply success: application created', [
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancy_id,
            'application_id' => $application->id,
        ]);

        // Consume fresh-upload marker for this vacancy after successful application submit.
        $vacancyUploads = session('vacancy_doc_uploads', []);
        if (is_array($vacancyUploads) && array_key_exists((string) $vacancy->vacancy_id, $vacancyUploads)) {
            unset($vacancyUploads[(string) $vacancy->vacancy_id]);
            session(['vacancy_doc_uploads' => $vacancyUploads]);
        }

        // Keep apply response fast: store lightweight DB notifications directly.
        $admins = \App\Models\Admin::all();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'notifiable_type' => 'App\Models\Admin',
                'notifiable_id' => $admin->id,
                'type' => 'warning',
                'data' => [
                    'title' => 'New Job Application',
                    'message' => Auth::user()->name . ' submitted an application for ' . $vacancy->position_title . '.',
                    'link' => route('admin.applicant_status', ['user_id' => Auth::id(), 'vacancy_id' => $vacancy->vacancy_id]),
                    'section' => 'Application List',
                    'category' => 'document_verification',
                    'user_id' => Auth::id(),
                    'vacancy_id' => $vacancy->vacancy_id,
                ],
                'read_at' => null,
            ]);
        }

        activity()
            ->event('apply job')
            ->causedBy(Auth::user())
            ->performedOn($vacancy)
            ->withProperties(['vacancy_id' => $vacancy->vacancy_id, 'section' => 'Job Vacancy'])
            ->log('Applied to job vacancy.');

        return redirect()->route('my_applications')->with('success', 'Application submitted successfully!');
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

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();

        $snapshotNotification = \App\Models\Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user_id)
            ->where('data->type', 'application_overview')
            ->where('data->vacancy_id', $vacancy_id)
            ->latest()
            ->first();
        $snapshotData = $snapshotNotification?->data ?? null;
        $snapshotDocumentsById = collect($snapshotData['documents'] ?? [])->keyBy('id');

        $adminName = $snapshotData['last_modified_by'] ?? null;
        $lastModifiedAt = $snapshotData['notified_at'] ?? null;

        $uploadedDocuments = $this->loadUploadedDocumentsMap((int) $user_id, (string) $vacancy_id);
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

            if ($docType === 'application_letter') {
                // Always get from Applications table for live data
                $documents[] = [ // Get from Applications table instead
                    'id' => 'application_letter',
                    'name' => $labelMap['application_letter'],
                    'text' => $labelMap['application_letter'],
                    'status' => $application->file_status ?? ($application->file_storage_path ? 'Pending' : 'Not Submitted'),
                    'preview' => $application->file_storage_path
                        ? url('/preview-file/' . base64_encode($application->file_storage_path))
                        : '',
                    'remarks' => $application->file_remarks ?? '',
                    'last_modified_by' => $application->file_last_modified_by ?? null,
                    'isBold' => true,
                ];
            } else {
                // Always prioritize live data over snapshot
                $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                $hasFile = $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';

                $status = 'Not Submitted';
                if ($doc) {
                    if (!empty($doc->status)) {
                        $status = $doc->status;
                    } elseif ($hasFile) {
                        $status = 'Pending';
                    }
                }

                $documents[] = [
                    'id' => $docType,
                    'name' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'text' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'status' => $status,
                    'preview' => ($doc && !empty($doc->storage_path)) ? url('/preview-file/' . base64_encode($doc->storage_path)) : '',
                    'remarks' => $doc?->remarks ?? '',
                    'last_modified_by' => $doc?->last_modified_by,
                    'isBold' => true,
                ];
            }
        }

        $requiredDocumentIds = $this->getRequiredDocumentIdsForVacancyType($application->vacancy?->vacancy_type);
        $documents = $this->sortDocumentsForRequiredPriority($documents, $requiredDocumentIds);

        $displayApplicationStatus = $application->status ?? 'Pending';
        // Derive QS display from latest snapshot (preferred) or live document statuses to match admin view
        $vacancyType = $application->vacancy?->vacancy_type ?? null;
        $isVerified = function ($docId) use ($snapshotDocumentsById, $uploadedDocuments) {
            if ($snapshotDocumentsById && $snapshotDocumentsById->has($docId)) {
                $st = $snapshotDocumentsById->get($docId)['status'] ?? null;
                return in_array($st, ['Verified', 'Okay/Confirmed'], true);
            }
            $doc = $uploadedDocuments->get($docId);
            $st = $doc?->status;
            return in_array($st, ['Verified', 'Okay/Confirmed'], true);
        };

        $displayQsEducation = 'no';
        $displayQsEligibility = 'no';
        $displayQsExperience = 'no';
        $displayQsTraining = 'no';

        // Unified QS derivation based on live verifications
        $displayQsExperience = $isVerified('signed_work_exp_sheet') ? 'yes' : 'no';
        $displayQsEducation = ($isVerified('transcript_records') && $isVerified('photocopy_diploma')) ? 'yes' : 'no';
        $displayQsTraining = $isVerified('cert_training') ? 'yes' : 'no';
        $displayQsEligibility = $isVerified('cert_eligibility') ? 'yes' : 'no';

        // Check if all necessary ones are verified (Assuming all 4 must be yes for 'Qualified')
        // Or we just rely on HR's explicit save in $application->qs_result
        // If HR has set a final result from the Admin side, we should respect that instead of auto-calculating it in the view.
        // The admin side saves the latest QS into the application model.
        if (isset($application->qs_result) && $application->qs_result !== 'Not Qualified') {
            $displayQsResult = $application->qs_result;
        } else {
            $relevant = [$displayQsEducation, $displayQsEligibility, $displayQsExperience, $displayQsTraining];
            $allOk = empty($relevant) ? false : collect($relevant)->every(fn($v) => $v === 'yes');
            $displayQsResult = $allOk ? 'Qualified' : 'Not Qualified';
        }

        // Ensure manual overrides from admin page are reflected if they exist
        $displayQsEducation = $application->qs_education ?? $displayQsEducation;
        $displayQsEligibility = $application->qs_eligibility ?? $displayQsEligibility;
        $displayQsExperience = $application->qs_experience ?? $displayQsExperience;
        $displayQsTraining = $application->qs_training ?? $displayQsTraining;
        $displayDeadlineDate = $application->deadline_date ?? null;
        $displayDeadlineTime = $application->deadline_time ?? null;
        $displayApplicationRemarks = $application->application_remarks ?? '';

        /*
        activity()
            ->causedBy(auth()->user())
            ->performedOn($application)
            ->withProperties(['vacancy_id' => $application->vacancy_id])
            ->log('Viewed application status.');
        */

        return view('dashboard_user.application_status', compact(
            'application',
            'examDetail',
            'documents',
            'requiredDocumentIds',
            'adminName',
            'lastModifiedAt',
            'displayApplicationStatus',
            'displayQsEducation',
            'displayQsEligibility',
            'displayQsExperience',
            'displayQsTraining',
            'displayQsResult',
            'displayDeadlineDate',
            'displayDeadlineTime',
            'displayApplicationRemarks',
            'user_id',
            'vacancy_id'
        ));
    }

    /**
     * Get updated documents for AJAX refresh (user endpoint)
     */
    public function getUpdatedDocumentsUser(Request $request, $user_id, $vacancy_id)
    {
        // Debug logging
        \Log::info("getUpdatedDocumentsUser called", [
            'user_id' => $user_id,
            'vacancy_id' => $vacancy_id,
            'auth_user_id' => Auth::id(),
            'method' => $request->method()
        ]);

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->with(['personalInformation', 'vacancy'])
            ->first();

        if (!$application) {
            \Log::error("Application not found", ['user_id' => $user_id, 'vacancy_id' => $vacancy_id]);
            return response()->json(['error' => 'Application not found'], 404);
        }

        // Use the same logic as applicationStatus method
        $snapshotNotification = \App\Models\Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user_id)
            ->where('data->type', 'application_overview')
            ->where('data->vacancy_id', $vacancy_id)
            ->latest()
            ->first();
        $snapshotData = $snapshotNotification?->data ?? null;
        $snapshotDocumentsById = collect($snapshotData['documents'] ?? [])->keyBy('id');

        $uploadedDocuments = $this->loadUploadedDocumentsMap((int) $user_id, (string) $vacancy_id);
        $documents = [];

        // Debug: Log uploaded documents count
        \Log::info("Uploaded documents found", ['count' => $uploadedDocuments->count()]);

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

            if ($docType === 'application_letter') {
                // Always get from Applications table for live data
                $documents[] = [ // Get from Applications table instead
                    'id' => 'application_letter',
                    'name' => $labelMap['application_letter'],
                    'text' => $labelMap['application_letter'],
                    'status' => $application->file_status ?? ($application->file_storage_path ? 'Pending' : 'Not Submitted'),
                    'preview' => $application->file_storage_path
                        ? url('/preview-file/' . base64_encode($application->file_storage_path))
                        : '',
                    'remarks' => $application->file_remarks ?? '',
                    'last_modified_by' => $application->file_last_modified_by ?? null,
                    'isBold' => true,
                ];
            } else {
                // Always prioritize live data over snapshot
                $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                $hasFile = $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';

                // Debug: Log document details
                \Log::info("Document check for {$docType} in getUpdatedDocumentsUser", [
                    'doc_found' => $doc ? true : false,
                    'has_file' => $hasFile,
                    'storage_path' => $doc?->storage_path,
                    'status' => $doc?->status,
                    'last_modified_by' => $doc?->last_modified_by
                ]);

                // Use actual status from database if document exists
                $status = 'Not Submitted';
                if ($doc) {
                    if (!empty($doc->status)) {
                        $status = $doc->status;
                    } elseif ($hasFile) {
                        $status = 'Pending';
                    }
                }

                $documents[] = [
                    'id' => $docType,
                    'name' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'text' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'status' => $status,
                    'preview' => ($doc && !empty($doc->storage_path)) ? url('/preview-file/' . base64_encode($doc->storage_path)) : '',
                    'remarks' => $doc?->remarks ?? '',
                    'last_modified_by' => $doc?->last_modified_by,
                    'isBold' => true,
                ];
            }
        }

        $requiredDocumentIds = $this->getRequiredDocumentIdsForVacancyType($application->vacancy?->vacancy_type);
        $documents = $this->sortDocumentsForRequiredPriority($documents, $requiredDocumentIds);

        \Log::info("Final documents array in getUpdatedDocumentsUser", ['count' => count($documents)]);

        return response()->json([
            'documents' => $documents,
            'requiredDocumentIds' => $requiredDocumentIds,
            'application' => [
                'status' => $application->status ?? 'Pending',
                'file_last_modified_by' => $application->file_last_modified_by ?? null,
                'deadline_date' => $application->deadline_date ?? null,
                'deadline_time' => $application->deadline_time ?? null,
            ]
        ]);
    }

    private function resolveUploadedDocument($uploadedDocuments, string $docType): ?UploadedDocument
    {
        $doc = $uploadedDocuments->get($docType);
        if ($doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT') {
            return $doc;
        }
        foreach (self::DOCUMENT_TYPE_ALIASES[$docType] ?? [] as $alias) {
            $aliasDoc = $uploadedDocuments->get($alias);
            if ($aliasDoc && !empty($aliasDoc->storage_path) && $aliasDoc->storage_path !== 'NOINPUT') {
                return $aliasDoc;
            }
        }
        return $doc ?: null;
    }

    private function loadUploadedDocumentsMap(int $userId, ?string $vacancyId = null)
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $docsQuery = UploadedDocument::where('user_id', $userId);
        if ($supportsVacancyScopedDocs) {
            if (!empty($vacancyId)) {
                $docsQuery->where('vacancy_id', $vacancyId);
            } else {
                $docsQuery->whereNull('vacancy_id');
            }
        }

        $docs = $docsQuery
            ->orderByDesc('updated_at')
            ->get();

        return $docs
            ->unique('document_type')
            ->keyBy('document_type');
    }

    private function getRequiredDocumentIdsForVacancyType(?string $vacancyType): array
    {
        $vacancyTrack = $this->normalizeTrack($vacancyType);
        $requiredDocumentIds = $this->getRequiredDocsByTrack()[$vacancyTrack] ?? [];

        usort($requiredDocumentIds, function ($a, $b) {
            $labelA = strtolower($this->getDocumentLabelMap()[$a] ?? $a);
            $labelB = strtolower($this->getDocumentLabelMap()[$b] ?? $b);
            return $labelA <=> $labelB;
        });

        return $requiredDocumentIds;
    }

    private function sortDocumentsForRequiredPriority(array $documents, array $requiredDocumentIds): array
    {
        $requiredLookup = array_fill_keys($requiredDocumentIds, true);

        usort($documents, function ($a, $b) use ($requiredLookup) {
            $requiredA = isset($requiredLookup[$a['id'] ?? '']) ? 0 : 1;
            $requiredB = isset($requiredLookup[$b['id'] ?? '']) ? 0 : 1;

            if ($requiredA !== $requiredB) {
                return $requiredA - $requiredB;
            }

            $labelA = strtolower((string) ($a['text'] ?? $a['name'] ?? $a['id'] ?? ''));
            $labelB = strtolower((string) ($b['text'] ?? $b['name'] ?? $b['id'] ?? ''));
            return $labelA <=> $labelB;
        });

        return $documents;
    }

    private function getDocumentLabelMap(): array
    {
        return [
            'application_letter' => 'Application Letter',
            'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
            'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
            'photocopy_diploma' => 'Diploma',
            'signed_pds' => 'Signed Personal Data Sheet',
            'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
            'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
            'passport_photo' => '2\" x 2\" or Passport Size Picture',
            'cert_eligibility' => 'Certificate of Eligibility/Board Rating',
            'ipcr' => 'Certification of Numerical Rating/Performance Rating/IPCR',
            'non_academic' => 'Non-Academic Awards Received',
            'cert_training' => 'Certificates of Training/Participation',
            'designation_order' => 'Confirmed Designation Order/s',
            'grade_masteraldoctorate' => 'Certificate of Grades with Masteral/Doctorate Units Earned',
            'tor_masteraldoctorate' => 'TOR with Masteral/Doctorate Degree',
            'cert_employment' => 'Certificate of Employment',
            'other_documents' => 'Other Documents Submitted',
        ];
    }

    private function getRequiredDocsByTrack(): array
    {
        $allDocumentTypes = array_values(array_filter(
            UploadedDocument::DOCUMENTS,
            fn($doc) => $doc !== 'isApproved'
        ));

        return [
            'COS' => self::COS_REQUIRED_DOCUMENTS,
            'Plantilla' => array_values(array_diff(
                $allDocumentTypes,
                ['tor_masteraldoctorate', 'grade_masteraldoctorate', 'cert_lgoo_induction', 'other_documents', 'pqe_result']
            )),
        ];
    }

    private function normalizeTrack(?string $track): string
    {
        return strcasecmp((string) $track, 'COS') === 0 ? 'COS' : 'Plantilla';
    }

    private function hasVacancyDocumentUploadForApply(int $userId, ?string $vacancyId): bool
    {
        if (!$vacancyId) {
            return false;
        }

        $vacancyUploads = session('vacancy_doc_uploads', []);
        $entry = $vacancyUploads[$vacancyId] ?? null;
        $hasSessionMarker = is_array($entry)
            && ((int) ($entry['user_id'] ?? 0) === $userId)
            && !empty($entry['uploaded_at']);
        if ($hasSessionMarker) {
            return true;
        }

        if (Schema::hasColumn('uploaded_documents', 'vacancy_id')) {
            return UploadedDocument::where('user_id', $userId)
                ->where('vacancy_id', $vacancyId)
                ->whereNotNull('storage_path')
                ->where('storage_path', '!=', 'NOINPUT')
                ->exists();
        }

        return false;
    }

    private function getRequiredDocsModalState(int $userId, ?string $vacancyType, ?string $vacancyId = null): array
    {
        $vacancyTrack = $this->normalizeTrack($vacancyType);
        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        $requiredDocs = $requiredDocsByTrack[$vacancyTrack] ?? [];
        $documentLabels = $this->getDocumentLabelMap();
        $hasFreshUploadForVacancy = $this->hasVacancyDocumentUploadForApply($userId, $vacancyId);

        $previewDocs = array_map(function (string $docType) use ($documentLabels) {
            return [
                'key' => $docType,
                'label' => $documentLabels[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
            ];
        }, $requiredDocs);

        $hasMissing = true;
        if ($vacancyId) {
            if (Schema::hasColumn('uploaded_documents', 'vacancy_id')) {
                $uploadedDocuments = $this->loadUploadedDocumentsMap($userId, $vacancyId);
                $hasApplicationLetterInApplications = Applications::where('user_id', $userId)
                    ->where('vacancy_id', $vacancyId)
                    ->whereNotNull('file_storage_path')
                    ->exists();

                $hasMissing = collect($requiredDocs)->contains(function (string $docType) use ($uploadedDocuments, $hasApplicationLetterInApplications) {
                    if ($docType === 'application_letter' && $hasApplicationLetterInApplications) {
                        return false;
                    }

                    $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                    return !($doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT');
                });
            } else {
                // Legacy fallback: session marker based freshness when vacancy-scoped docs are unavailable.
                $hasMissing = !$hasFreshUploadForVacancy;
            }
        } else {
            // Fallback behavior when vacancy context is not present.
            $uploadedDocuments = UploadedDocument::where('user_id', $userId)->get()->keyBy('document_type');
            $hasApplicationLetterInApplications = Applications::where('user_id', $userId)
                ->whereNotNull('file_storage_path')
                ->exists();

            $hasMissing = collect($requiredDocs)->contains(function (string $docType) use ($uploadedDocuments, $hasApplicationLetterInApplications) {
                if ($docType === 'application_letter' && $hasApplicationLetterInApplications) {
                    return false;
                }

                $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                return !($doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT');
            });
        }

        return [
            'hasMissing' => $hasMissing,
            'previewDocs' => $previewDocs,
            'vacancyTrack' => $vacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $vacancyTrack,
                'vacancy_id' => $vacancyId,
                'fresh_upload' => 1,
            ]),
        ];
    }

    private function getTrackCompletenessByUser(int $userId): array
    {
        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        $uploadedDocuments = UploadedDocument::where('user_id', $userId)->get()->keyBy('document_type');
        $hasApplicationLetter = Applications::where('user_id', $userId)
            ->whereNotNull('file_storage_path')
            ->exists();

        $isComplete = [];
        foreach ($requiredDocsByTrack as $track => $requiredDocs) {
            $isComplete[$track] = collect($requiredDocs)->every(function (string $docType) use ($uploadedDocuments, $hasApplicationLetter) {
                if ($docType === 'application_letter') {
                    if ($hasApplicationLetter) {
                        return true;
                    }
                    $appLetterDoc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                    return $appLetterDoc && !empty($appLetterDoc->storage_path) && $appLetterDoc->storage_path !== 'NOINPUT';
                }

                $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                return $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
            });
        }

        return $isComplete;
    }

    private function getDocumentTrackMismatchState(int $userId, ?string $vacancyType, ?string $vacancyId = null): array
    {
        $vacancyTrack = $this->normalizeTrack($vacancyType);
        $otherTrack = $vacancyTrack === 'COS' ? 'Plantilla' : 'COS';
        $trackCompleteness = $this->getTrackCompletenessByUser($userId);

        $hasMismatch = ($trackCompleteness[$otherTrack] ?? false) && !($trackCompleteness[$vacancyTrack] ?? false);

        return [
            'hasMismatch' => $hasMismatch,
            'submittedTrack' => $hasMismatch ? $otherTrack : null,
            'vacancyTrack' => $vacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $vacancyTrack,
                'vacancy_id' => $vacancyId,
                'fresh_upload' => 1,
            ]),
        ];
    }

    private function hasCompletedPdsForApply(int $userId): bool
    {
        $personalInfo = PersonalInformation::where('user_id', $userId)->first();
        $familyBackground = FamilyBackground::where('user_id', $userId)->first();
        $educationBackground = EducationalBackground::where('user_id', $userId)->first();
        $miscInfo = MiscInfos::where('user_id', $userId)->first();
        $hasWes = WorkExpSheet::where('user_id', $userId)->exists();

        if (!$personalInfo || !$familyBackground || !$educationBackground || !$miscInfo || !$hasWes) {
            return false;
        }

        return $this->hasMeaningfulValue($personalInfo->surname)
            && $this->hasMeaningfulValue($personalInfo->first_name)
            && $this->hasMeaningfulValue($personalInfo->mobile_no)
            && $this->hasMeaningfulValue($personalInfo->email_address)
            && $this->hasMeaningfulValue($familyBackground->mother_maiden_surname)
            && $this->hasMeaningfulValue($familyBackground->mother_maiden_first_name)
            && $this->hasMeaningfulValue($educationBackground->elem_school)
            && $this->hasMeaningfulValue($educationBackground->jhs_school)
            && $this->hasMeaningfulValue($miscInfo->govt_id_type)
            && $this->hasMeaningfulValue($miscInfo->govt_id_number);
    }

    private function hasMeaningfulValue($value): bool
    {
        if (is_array($value)) {
            return !empty(array_filter($value, fn($item) => $this->hasMeaningfulValue($item)));
        }

        $normalized = trim((string) $value);
        return $normalized !== '' && strtoupper($normalized) !== 'NOINPUT';
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
