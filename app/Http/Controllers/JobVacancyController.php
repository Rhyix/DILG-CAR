<?php


namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
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
use Illuminate\Support\Facades\Storage;
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
            ->leftJoin('exam_details', function ($join) {
                $join->whereRaw('job_vacancies.vacancy_id COLLATE utf8mb4_unicode_ci = exam_details.vacancy_id COLLATE utf8mb4_unicode_ci');
            })
            ->with('examDetail')
            ->orderByRaw("CASE 
                WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NOT NULL AND exam_details.date >= CURDATE() THEN 1 
                WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NULL THEN 2 
                WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NOT NULL AND exam_details.date < CURDATE() THEN 3 
                ELSE 4 
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
        $signatories = \App\Models\Signatory::query()->orderBy('id')->limit(1)->get();
        $vacancyType = (string) ($vacancy->vacancy_type ?? '');
        $view = strcasecmp(trim($vacancyType), 'Plantilla') === 0
            ? 'admin.vacancy_add_plantilla'
            : 'admin.vacancy_add_cos';

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
            'csc_form' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
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

        $vacancyUpdateData = [
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
        ];

        if ($this->hasJobVacancyLastModifiedAtColumn()) {
            $vacancyUpdateData['last_modified_at'] = now();
        }

        $vacancy->update($vacancyUpdateData);

        // Handle CSC Form file upload only when the column exists in this database.
        if ($this->hasJobVacancyCscFormPathColumn() && request()->hasFile('csc_form')) {
            if ($vacancy->csc_form_path) {
                Storage::disk('public')->delete($vacancy->csc_form_path);
            }
            $vacancy->update([
                'csc_form_path' => request()->file('csc_form')->store('csc_forms', 'public'),
            ]);
        }

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

            // CSC Form
            'csc_form' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
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

        $hasCscFormPathColumn = $this->hasJobVacancyCscFormPathColumn();

        // 🔷 Create vacancy
        $vacancyData = [
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
        ];

        // Some environments may not yet have the csc_form_path column.
        if ($hasCscFormPathColumn) {
            $vacancyData['csc_form_path'] = $request->hasFile('csc_form')
                ? $request->file('csc_form')->store('csc_forms', 'public')
                : null;
        }

        $vacancy = JobVacancy::create($vacancyData);


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
            ]),
        ];
        $requiredDocsModalState = [
            'hasMissing' => false,
            'previewDocs' => [],
            'vacancyTrack' => $normalizedVacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $normalizedVacancyTrack,
                'vacancy_id' => $vacancy->vacancy_id,
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

        /*activity()
            ->causedBy(auth()->user())
            ->log('Filtered job vacancies (admin).');
        */

        return view('partials.admin_vacancy_list', compact('vacancies'))->render();
    }


    public function filterVacancy(Request $request)
    {
        $vacancies = JobVacancy::select('job_vacancies.*')
            ->leftJoin('exam_details', function ($join) {
                $join->whereRaw('job_vacancies.vacancy_id COLLATE utf8mb4_unicode_ci = exam_details.vacancy_id COLLATE utf8mb4_unicode_ci');
            })
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

        // Priority sorting: Scheduled (future exam) → Open (unscheduled) → Completed (past exam) → Closed
        $vacancies->orderByRaw("CASE 
            WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NOT NULL AND exam_details.date >= CURDATE() THEN 1 
            WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NULL THEN 2 
            WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NOT NULL AND exam_details.date < CURDATE() THEN 3 
            ELSE 4 
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

        $vacancies = collect();
        $openVacanciesQuery = JobVacancy::query()->where('status', 'OPEN');
        $openVacancyCount = (clone $openVacanciesQuery)->count();
        $cosVacancyCount = (clone $openVacanciesQuery)
            ->whereRaw('UPPER(vacancy_type) = ?', ['COS'])
            ->count();
        $plantillaVacancyCount = max($openVacancyCount - $cosVacancyCount, 0);

        $applications = \App\Models\Applications::query()
            ->select([
                'id',
                'user_id',
                'vacancy_id',
                'status',
                'qs_result',
                'deadline_date',
                'deadline_time',
                'created_at',
            ])
            ->where('user_id', $userId)
            ->with(['vacancy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdsProgress = (int) round($this->calculatePdsProgress(Auth::id()));
        $hasPDS = PersonalInformation::where('user_id', Auth::id())->exists();
        $hasWES = WorkExpSheet::where('user_id', Auth::id())->exists();

        // Application Status Summary
        $statusSummary = $applications->groupBy('status')->map->count();

        // Upcoming exams for user's applied vacancies
        $vacancyIds = $applications->pluck('vacancy_id')->filter()->unique()->values();
        $now = Carbon::now()->toDateTimeString();
        $upcomingExamsCount = ExamDetail::whereIn('vacancy_id', $vacancyIds)
            ->whereRaw("STR_TO_DATE(CONCAT(`date`, ' ', `time`), '%Y-%m-%d %H:%i:%s') > ?", [$now])
            ->count();
        $upcomingExams = collect();

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
        $now = Carbon::now();
        $deadlineCountdown = $applications
            ->filter(function ($app) {
                if (!$app->deadline_date || !$app->deadline_time) {
                    return false;
                }

                $applicationStatus = strtolower(trim((string) ($app->status ?? '')));
                $qsResult = strtolower(trim((string) ($app->qs_result ?? '')));
                $isTerminalStatus = in_array($applicationStatus, ['closed', 'qualified'], true);
                $isVacancyClosed = $app->vacancy && strtolower((string) ($app->vacancy->status ?? '')) === 'closed';

                return !$isTerminalStatus && !$isVacancyClosed && $qsResult !== 'qualified';
            })
            ->map(function ($app) use ($now) {
                $deadline = Carbon::parse($app->deadline_date . ' ' . $app->deadline_time);
                $secondsRemaining = $now->diffInSeconds($deadline, false);
                if ($secondsRemaining <= 0) {
                    return null;
                }

                return [
                    'vacancy_id' => $app->vacancy_id,
                    'position_title' => $app->vacancy->position_title ?? '',
                    'deadline' => $deadline->toDateTimeString(),
                    'days_remaining' => (int) ceil($secondsRemaining / 86400),
                ];
            })
            ->filter()
            ->sortBy('days_remaining')
            ->values();

        // Notifications/Alerts (latest 5, and unread count)
        $recentNotifications = Auth::user()?->notifications()->orderBy('created_at', 'desc')->take(5)->get() ?? collect();
        $unreadNotificationsCount = Auth::user()?->unreadNotifications()->count() ?? 0;

        return view('dashboard_user.dashboard_user', [
            'vacancies' => $vacancies,
            'openVacancyCount' => $openVacancyCount,
            'applications' => $applications,
            'pdsProgress' => $pdsProgress,
            'hasPDS' => $hasPDS,
            'hasWES' => $hasWES,
            'statusSummary' => $statusSummary,
            'cosVacancyCount' => $cosVacancyCount,
            'plantillaVacancyCount' => $plantillaVacancyCount,
            'upcomingExams' => $upcomingExams,
            'upcomingExamsCount' => $upcomingExamsCount,
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

        $requiredDocumentIds = $this->getRequiredDocumentIdsForVacancyType((string) $vacancy->vacancy_type);
        $this->seedVacancyDocumentsFromReusableUploads(
            (int) Auth::id(),
            (string) $vacancy->vacancy_id,
            $requiredDocumentIds
        );

        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $applicationLetterDocQuery = UploadedDocument::where('user_id', Auth::id())
            ->where('document_type', 'application_letter')
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT');
        if ($supportsVacancyScopedDocs) {
            $applicationLetterDocQuery->orderByRaw(
                "CASE WHEN vacancy_id = ? THEN 0 WHEN vacancy_id IS NULL THEN 1 ELSE 2 END",
                [(string) $vacancy->vacancy_id]
            );
        }
        $applicationLetterDoc = $applicationLetterDocQuery
            ->latest('updated_at')
            ->first();

        if (!$applicationLetterDoc) {
            $latestApplicationLetter = Applications::where('user_id', Auth::id())
                ->whereNotNull('file_storage_path')
                ->latest('updated_at')
                ->first();

            if ($latestApplicationLetter) {
                $applicationLetterDoc = UploadedDocument::updateOrCreate(
                    $supportsVacancyScopedDocs
                        ? [
                            'user_id' => Auth::id(),
                            'vacancy_id' => (string) $vacancy->vacancy_id,
                            'document_type' => 'application_letter',
                        ]
                        : [
                            'user_id' => Auth::id(),
                            'document_type' => 'application_letter',
                        ],
                    [
                        'original_name' => (string) ($latestApplicationLetter->file_original_name
                            ?: basename((string) $latestApplicationLetter->file_storage_path)),
                        'stored_name' => (string) ($latestApplicationLetter->file_stored_name
                            ?: basename((string) $latestApplicationLetter->file_storage_path)),
                        'storage_path' => (string) $latestApplicationLetter->file_storage_path,
                        'mime_type' => 'application/pdf',
                        'file_size_8b' => (int) ($latestApplicationLetter->file_size_8b ?? 0),
                        'status' => 'Pending',
                        'remarks' => '',
                        'last_modified_by' => Auth::user()?->name ?? 'System',
                    ]
                );
            }
        }

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

        if (
            $supportsVacancyScopedDocs
            && (string) ($applicationLetterDoc->vacancy_id ?? '') !== (string) $vacancy->vacancy_id
        ) {
            $applicationLetterDoc = $this->upsertVacancyDocumentFromSource(
                $applicationLetterDoc,
                (string) $vacancy->vacancy_id,
                'application_letter'
            );
        }


        // Create application
        $application = \App\Models\Applications::create([
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => ApplicationStatus::PENDING->value,
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
                    'link' => route('admin.applicant_status', ['user_id' => Auth::id(), 'vacancy_id' => $vacancy->vacancy_id], false),
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
        $applications = $this->buildMyApplicationsQuery(request())->get();
        $filterOptions = $this->getMyApplicationFilterOptions();
        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Viewed my applications.');
        */

        return view('dashboard_user.my_applications', [
            'applications' => $applications,
            'filterOptions' => $filterOptions,
        ]);
    }

    // USEREND application status
    public function applicationStatus($user_id, $vacancy_id)
    {
        if ((int) Auth::id() !== (int) $user_id) {
            abort(403, 'Unauthorized access to this application.');
        }

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->with(['personalInformation', 'vacancy'])
            ->firstOrFail();

        if (strcasecmp(trim((string) ($application->status ?? '')), 'Not Qualified') === 0) {
            return redirect()->route('my_applications')
                ->with('error', 'This application is already marked as Not Qualified and can no longer be opened.');
        }

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
        $isFinalRevisionDisqualified = $this->hasFinalRevisionDisqualification($application, $uploadedDocuments);
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
        // Show only manually saved QS values from admin review.
        $displayQsEducation = $application->qs_education ?? 'no';
        $displayQsEligibility = $application->qs_eligibility ?? 'no';
        $displayQsExperience = $application->qs_experience ?? 'no';
        $displayQsTraining = $application->qs_training ?? 'no';
        $displayQsResult = $application->qs_result ?? 'Not Qualified';
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
            'isFinalRevisionDisqualified',
            'user_id',
            'vacancy_id'
        ));
    }

    /**
     * Get updated documents for AJAX refresh (user endpoint)
     */
    public function getUpdatedDocumentsUser(Request $request, $user_id, $vacancy_id)
    {
        if ((int) Auth::id() !== (int) $user_id) {
            return response()->json(['error' => 'Unauthorized access to this application.'], 403);
        }

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

        if (strcasecmp(trim((string) ($application->status ?? '')), 'Not Qualified') === 0) {
            return response()->json([
                'error' => 'This application is already marked as Not Qualified and can no longer be opened.'
            ], 403);
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
        $isFinalRevisionDisqualified = $this->hasFinalRevisionDisqualification($application, $uploadedDocuments);
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
                'qs_result' => $application->qs_result ?? null,
                'file_last_modified_by' => $application->file_last_modified_by ?? null,
                'deadline_date' => $application->deadline_date ?? null,
                'deadline_time' => $application->deadline_time ?? null,
                'final_revision_disqualified' => $isFinalRevisionDisqualified,
            ]
        ]);
    }

    private function isRevisionStatus(?string $status): bool
    {
        $normalized = strtolower(trim((string) $status));
        return in_array($normalized, ['needs revision', 'disapproved with deficiency'], true);
    }

    private function hasSatisfiedLatestRevisionRequest(?string $requestedAt, ?string $submittedAt): bool
    {
        if (empty($submittedAt)) {
            return false;
        }

        if (empty($requestedAt)) {
            return true;
        }

        try {
            return Carbon::parse($submittedAt)->greaterThanOrEqualTo(Carbon::parse($requestedAt));
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function isRevisionComplianceLocked(int $requestedCount, ?string $requestedAt, ?string $submittedAt): bool
    {
        return $requestedCount >= 2 && $this->hasSatisfiedLatestRevisionRequest($requestedAt, $submittedAt);
    }

    private function hasFinalRevisionDisqualification(Applications $application, $uploadedDocuments): bool
    {
        if ($this->isRevisionComplianceLocked(
            (int) ($application->file_revision_requested_count ?? 0),
            $application->file_revision_requested_at ?? null,
            $application->file_revision_submitted_at ?? null
        )) {
            return true;
        }

        foreach ($uploadedDocuments as $doc) {
            if ($this->isRevisionComplianceLocked(
                (int) ($doc->revision_requested_count ?? 0),
                $doc->revision_requested_at ?? null,
                $doc->revision_submitted_at ?? null
            )) {
                return true;
            }
        }

        return false;
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

    private function loadReusableUploadedDocumentsMap(int $userId, ?string $vacancyId = null)
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');

        $docsQuery = UploadedDocument::where('user_id', $userId)
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT');

        if ($supportsVacancyScopedDocs && !empty($vacancyId)) {
            $docsQuery->orderByRaw(
                "CASE WHEN vacancy_id = ? THEN 0 WHEN vacancy_id IS NULL THEN 1 ELSE 2 END",
                [(string) $vacancyId]
            );
        } elseif ($supportsVacancyScopedDocs) {
            $docsQuery->orderByRaw('CASE WHEN vacancy_id IS NULL THEN 0 ELSE 1 END');
        }

        $docs = $docsQuery
            ->orderByDesc('updated_at')
            ->get();

        return $docs
            ->unique('document_type')
            ->keyBy('document_type');
    }

    private function hasStoredUploadedDocument($uploadedDocuments, string $docType): bool
    {
        $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
        return $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
    }

    private function seedVacancyDocumentsFromReusableUploads(int $userId, string $vacancyId, array $requiredDocs): void
    {
        if (
            empty($vacancyId)
            || empty($requiredDocs)
            || !Schema::hasColumn('uploaded_documents', 'vacancy_id')
        ) {
            return;
        }

        $vacancyDocs = UploadedDocument::where('user_id', $userId)
            ->where('vacancy_id', $vacancyId)
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT')
            ->orderByDesc('updated_at')
            ->get()
            ->unique('document_type')
            ->keyBy('document_type');

        $reusableDocs = $this->loadReusableUploadedDocumentsMap($userId, $vacancyId);

        foreach ($requiredDocs as $docType) {
            if ($this->hasStoredUploadedDocument($vacancyDocs, (string) $docType)) {
                continue;
            }

            $sourceDoc = $this->resolveUploadedDocument($reusableDocs, (string) $docType);
            if (!$sourceDoc || empty($sourceDoc->storage_path) || $sourceDoc->storage_path === 'NOINPUT') {
                continue;
            }

            $seeded = $this->upsertVacancyDocumentFromSource($sourceDoc, $vacancyId, (string) $docType);
            $vacancyDocs->put((string) $docType, $seeded);
        }
    }

    private function upsertVacancyDocumentFromSource(
        UploadedDocument $source,
        string $vacancyId,
        string $targetDocType
    ): UploadedDocument {
        $destination = UploadedDocument::where('user_id', (int) $source->user_id)
            ->where('vacancy_id', $vacancyId)
            ->where('document_type', $targetDocType)
            ->orderByDesc('updated_at')
            ->first();

        $payload = [
            'original_name' => $source->original_name,
            'stored_name' => $source->stored_name,
            'storage_path' => $source->storage_path,
            'mime_type' => $source->mime_type,
            'file_size_8b' => $source->file_size_8b,
            'status' => 'Pending',
            'remarks' => '',
            'last_modified_by' => Auth::user()?->name ?? 'System',
        ];

        if ($destination) {
            $destination->update($payload);
            return $destination;
        }

        return UploadedDocument::create(array_merge($payload, [
            'user_id' => (int) $source->user_id,
            'vacancy_id' => $vacancyId,
            'document_type' => $targetDocType,
        ]));
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
            'passport_photo' => '2" x 2" or Passport Size Picture',
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

    private function getRequiredDocsModalState(int $userId, ?string $vacancyType, ?string $vacancyId = null): array
    {
        $vacancyTrack = $this->normalizeTrack($vacancyType);
        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        $requiredDocs = $requiredDocsByTrack[$vacancyTrack] ?? [];
        $documentLabels = $this->getDocumentLabelMap();

        $previewDocs = array_map(function (string $docType) use ($documentLabels) {
            return [
                'key' => $docType,
                'label' => $documentLabels[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
            ];
        }, $requiredDocs);

        $uploadedDocuments = $this->loadReusableUploadedDocumentsMap($userId, $vacancyId);
        $hasApplicationLetterInApplications = Applications::where('user_id', $userId)
            ->whereNotNull('file_storage_path')
            ->exists();

        $hasMissing = collect($requiredDocs)->contains(function (string $docType) use ($uploadedDocuments, $hasApplicationLetterInApplications) {
            if ($docType === 'application_letter' && $hasApplicationLetterInApplications) {
                return false;
            }

            return !$this->hasStoredUploadedDocument($uploadedDocuments, $docType);
        });

        return [
            'hasMissing' => $hasMissing,
            'previewDocs' => $previewDocs,
            'vacancyTrack' => $vacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $vacancyTrack,
                'vacancy_id' => $vacancyId,
            ]),
        ];
    }

    private function getTrackCompletenessByUser(int $userId): array
    {
        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        $uploadedDocuments = UploadedDocument::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get()
            ->unique('document_type')
            ->keyBy('document_type');
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
        $userId = (int) $userId;

        // Determine required docs from tracks the user has applied for.
        $applicationTracks = Applications::where('user_id', $userId)
            ->with('vacancy:vacancy_id,vacancy_type')
            ->get()
            ->map(fn($app) => $this->normalizeTrack($app->vacancy?->vacancy_type))
            ->filter()
            ->unique()
            ->values();

        if ($applicationTracks->isEmpty()) {
            $applicationTracks = collect(['Plantilla']);
        }

        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        $requiredDocumentIds = $applicationTracks
            ->flatMap(fn($track) => $requiredDocsByTrack[$track] ?? [])
            ->unique()
            ->values();

        $totalRequiredDocs = $requiredDocumentIds->count();
        if ($totalRequiredDocs === 0) {
            return 0;
        }

        $uploadedDocuments = UploadedDocument::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get()
            ->unique('document_type')
            ->keyBy('document_type');

        $hasApplicationLetterInApplications = Applications::where('user_id', $userId)
            ->whereNotNull('file_storage_path')
            ->exists();

        $completedRequiredDocs = $requiredDocumentIds->filter(function (string $docType) use ($uploadedDocuments, $hasApplicationLetterInApplications) {
            if ($docType === 'application_letter') {
                if ($hasApplicationLetterInApplications) {
                    return true;
                }

                $applicationLetterDoc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                return $applicationLetterDoc && !empty($applicationLetterDoc->storage_path) && $applicationLetterDoc->storage_path !== 'NOINPUT';
            }

            $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
            return $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
        })->count();

        return (int) round(($completedRequiredDocs / $totalRequiredDocs) * 100);
    }

    private function hasJobVacancyCscFormPathColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        try {
            $hasColumn = Schema::hasColumn('job_vacancies', 'csc_form_path');
        } catch (\Throwable $e) {
            $hasColumn = false;
            Log::warning('Unable to detect job_vacancies.csc_form_path column.', [
                'error' => $e->getMessage(),
            ]);
        }

        return $hasColumn;
    }

    private function hasJobVacancyLastModifiedAtColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        try {
            $hasColumn = Schema::hasColumn('job_vacancies', 'last_modified_at');
        } catch (\Throwable $e) {
            $hasColumn = false;
            Log::warning('Unable to detect job_vacancies.last_modified_at column.', [
                'error' => $e->getMessage(),
            ]);
        }

        return $hasColumn;
    }

    public function sortMyApplications(Request $request)
    {
        $applications = $this->buildMyApplicationsQuery($request)->get();
        $hasActiveFilters = $this->requestHasMyApplicationFilters($request);

        return view('partials.application_list_container', [
            'applications' => $applications,
            'hasActiveFilters' => $hasActiveFilters,
        ])->render();
    }

    private function buildMyApplicationsQuery(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $place = trim((string) $request->query('place', ''));
        $vacancyType = trim((string) $request->query('vacancy_type', ''));
        $status = trim((string) $request->query('status', ''));
        $sortOrder = strtolower(trim((string) $request->query('sort_order', 'latest')));

        $query = Applications::query()
            ->where('user_id', Auth::id())
            ->with('vacancy');

        if ($search !== '') {
            $query->where(function ($applicationQuery) use ($search) {
                $applicationQuery
                    ->where('vacancy_id', 'like', '%' . $search . '%')
                    ->orWhereHas('vacancy', function ($vacancyQuery) use ($search) {
                        $vacancyQuery->where('position_title', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($place !== '') {
            $query->whereHas('vacancy', function ($vacancyQuery) use ($place) {
                $vacancyQuery->where('place_of_assignment', $place);
            });
        }

        if ($vacancyType !== '') {
            $query->whereHas('vacancy', function ($vacancyQuery) use ($vacancyType) {
                $vacancyQuery->whereRaw("LOWER(TRIM(COALESCE(vacancy_type, ''))) = ?", [strtolower($vacancyType)]);
            });
        }

        if ($status !== '') {
            $query->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", [strtolower($status)]);
        }

        $query->orderByRaw("CASE WHEN LOWER(TRIM(COALESCE(status, ''))) = 'not qualified' THEN 1 ELSE 0 END");
        $query->orderBy('created_at', $sortOrder === 'oldest' ? 'asc' : 'desc');

        return $query;
    }

    private function getMyApplicationFilterOptions(): array
    {
        $userId = Auth::id();

        $statuses = Applications::query()
            ->where('user_id', $userId)
            ->whereNotNull('status')
            ->pluck('status')
            ->map(fn($status) => trim((string) $status))
            ->filter()
            ->unique(fn($status) => strtolower($status))
            ->sortBy(fn($status) => strtolower($status))
            ->values();

        return [
            'vacancyTypes' => collect(['COS', 'Plantilla']),
            'statuses' => $statuses,
        ];
    }

    private function requestHasMyApplicationFilters(Request $request): bool
    {
        foreach (['search', 'place', 'vacancy_type', 'status'] as $key) {
            if (trim((string) $request->query($key, '')) !== '') {
                return true;
            }
        }

        return false;
    }
}
