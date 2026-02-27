<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Applications;
use App\Models\PersonalInformation;
use App\Models\JobVacancy;
use App\Models\UploadedDocument;
use App\Models\AdminVacancyAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ShowApplicantsProfile extends Controller
{
    private const REVISION_STATUSES = [
        'needs revision',
        'disapproved with deficiency',
    ];

    private function complianceStageStatuses(): array
    {
        return ApplicationStatus::complianceStages();
    }

    private function currentAdmin()
    {
        return Auth::guard('admin')->user();
    }

    private function buildHrDivisionAccessSignature(array $vacancyIds): string
    {
        $normalized = collect($vacancyIds)
            ->map(fn($value) => trim((string) $value))
            ->filter(fn($value) => $value !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();

        return hash('sha256', implode('|', $normalized));
    }

    private function hrDivisionGrantedVacancyIds(): array
    {
        $admin = $this->currentAdmin();
        if (($admin->role ?? null) !== 'hr_division') {
            return [];
        }

        if (!Schema::hasTable('admin_vacancy_accesses')) {
            return [];
        }

        return AdminVacancyAccess::query()
            ->where('admin_id', $admin->id)
            ->pluck('vacancy_id')
            ->map(fn($value) => (string) $value)
            ->values()
            ->all();
    }

    private function hrDivisionAccessSignature(): string
    {
        $admin = $this->currentAdmin();
        if (($admin->role ?? null) !== 'hr_division') {
            return '';
        }

        return $this->buildHrDivisionAccessSignature($this->hrDivisionGrantedVacancyIds());
    }

    private function hrDivisionCanAccessVacancy(?string $vacancyId): bool
    {
        $admin = $this->currentAdmin();
        if (($admin->role ?? null) !== 'hr_division') {
            return true;
        }

        if (!Schema::hasTable('admin_vacancy_accesses')) {
            return false;
        }

        $vacancyId = trim((string) $vacancyId);
        if ($vacancyId === '') {
            return false;
        }

        $hasGrant = AdminVacancyAccess::query()
            ->where('admin_id', $admin->id)
            ->where('vacancy_id', $vacancyId)
            ->exists();

        if (!$hasGrant) {
            return false;
        }

        return JobVacancy::query()
            ->where('vacancy_id', $vacancyId)
            ->whereRaw('UPPER(vacancy_type) = ?', ['COS'])
            ->exists();
    }

    private function normalizeStatus(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    private function buildRevisionLookup($applications, string $vacancyId): array
    {
        $userIds = $applications->pluck('user_id')->filter()->unique()->values();
        if ($userIds->isEmpty()) {
            return [];
        }

        $query = UploadedDocument::query()
            ->whereIn('user_id', $userIds)
            ->whereRaw('LOWER(TRIM(status)) IN (?, ?)', self::REVISION_STATUSES);

        if (Schema::hasColumn('uploaded_documents', 'vacancy_id')) {
            $query->where(function ($sub) use ($vacancyId) {
                $sub->where('vacancy_id', $vacancyId)
                    ->orWhereNull('vacancy_id');
            });
        }

        $revisionUserIds = $query->pluck('user_id')->unique()->all();

        return array_fill_keys($revisionUserIds, true);
    }

    private function determineApplicantStage($application, array $revisionLookup): string
    {
        $status = $this->normalizeStatus($application->status ?? '');
        $qsResult = $this->normalizeStatus($application->qs_result ?? '');
        $fileStatus = $this->normalizeStatus($application->file_status ?? '');

        $isQualified = $qsResult === 'qualified'
            || $status === $this->normalizeStatus(ApplicationStatus::QUALIFIED->value)
            || $status === 'complete';

        if ($isQualified) {
            return 'qualified';
        }

        $hasRevision = isset($revisionLookup[$application->user_id])
            || in_array($fileStatus, self::REVISION_STATUSES, true);

        $complianceStatuses = collect($this->complianceStageStatuses())
            ->map(fn($value) => $this->normalizeStatus($value))
            ->all();

        $isComplianceStatus = in_array($status, $complianceStatuses, true) || $status === 'incomplete';

        if ($hasRevision || $isComplianceStatus) {
            return 'compliance';
        }

        return 'new';
    }

    private function partitionApplicantsByStage($applications, string $vacancyId): array
    {
        $revisionLookup = $this->buildRevisionLookup($applications, $vacancyId);
        $stages = [
            'new' => collect(),
            'compliance' => collect(),
            'qualified' => collect(),
        ];

        foreach ($applications as $application) {
            $stage = $this->determineApplicantStage($application, $revisionLookup);
            $stages[$stage]->push($application);
        }

        return $stages;
    }

    private function formatApplicants($applications)
    {
        return $applications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : ($application->user?->name ?? 'N/A'),
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });
    }

    private function filterApplicantsBySearch($applications, string $search)
    {
        $needle = strtolower(trim($search));
        if ($needle === '') {
            return $applications;
        }

        return $applications->filter(function ($application) use ($needle) {
            $pi = $application->personalInformation;
            $nameParts = [
                $pi?->first_name,
                $pi?->middle_name,
                $pi?->surname,
                $pi?->name_extension,
                $application->user?->name,
            ];
            $haystack = strtolower(trim(implode(' ', array_filter($nameParts))));
            return $haystack !== '' && str_contains($haystack, $needle);
        });
    }

    private function sortApplicantsByDate($applications, string $sortOrder)
    {
        return $sortOrder === 'oldest'
            ? $applications->sortBy('created_at')
            : $applications->sortByDesc('created_at');
    }

    public function index(Request $request, $vacancy_id)
    {
        if (!$this->hrDivisionCanAccessVacancy((string) $vacancy_id)) {
            return redirect()->route('applications_list')
                ->with('error', 'Access denied. This COS vacancy is not assigned to your account.');
        }

        logger()->info("Filtering applicants for vacancy: " . $vacancy_id);

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancy_id)
            ->statusEquals(ApplicationStatus::PENDING->value)
            ->orderByDesc('created_at') // Sort from newest to oldest
            ->get();

        $formattedApplications = $applications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : ($application->user?->name ?? 'N/A'),
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return view('admin.applicants_profile', [
            'applicants' => $formattedApplications,
            'filteredVacancyId' => $vacancy_id,
        ]);
    }


    public function reviewedIndex(Request $request, $vacancy_id)
    {
        if (!$this->hrDivisionCanAccessVacancy((string) $vacancy_id)) {
            return redirect()->route('applications_list')
                ->with('error', 'Access denied. This COS vacancy is not assigned to your account.');
        }

        $sortStatus = $request->input('sort_status');

        $query = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->whereRaw('LOWER(TRIM(status)) <> ?', [strtolower(ApplicationStatus::PENDING->value)])
            ->where('vacancy_id', $vacancy_id); // filter by vacancy_id

        if ($sortStatus) {
            $query->where('status', $sortStatus);
        }

        $applications = $query->get();

        $statusOrder = ['Incomplete' => 1, 'Complete' => 2, 'Closed' => 3];

        $applications = $applications->sortBy(function ($application) use ($statusOrder) {
            return $statusOrder[$application->status] ?? 999;
        });

        $formattedApplications = $applications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : ($application->user?->name ?? 'N/A'),
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return view('admin.reviewed_applicants', [
            'applicants' => $formattedApplications,
            'filteredVacancyId' => $vacancy_id,
        ]);
    }


    public function ajaxSort(Request $request)
    {
        $status = $request->input('sort_status');
        $vacancyId = $request->input('vacancy_id'); // ✅ Add this line

        if (($this->currentAdmin()->role ?? null) === 'hr_division' && empty($vacancyId)) {
            return response()->view('partials.reviewed_applicants_list', [
                'applicants' => collect(),
            ]);
        }

        if (!empty($vacancyId) && !$this->hrDivisionCanAccessVacancy((string) $vacancyId)) {
            return response()->view('partials.reviewed_applicants_list', [
                'applicants' => collect(),
            ]);
        }

        $query = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->whereRaw('LOWER(TRIM(status)) <> ?', [strtolower(ApplicationStatus::PENDING->value)]);

        if ($vacancyId) {
            $query->where('vacancy_id', $vacancyId); // ✅ Filter by current vacancy
        }

        if ($status) {
            $query->where('status', $status);
        }

        $applications = $query->get();

        $statusOrder = ['Incomplete' => 1, 'Complete' => 2, 'Closed' => 3];

        $applications = $applications->sortBy(function ($application) use ($statusOrder) {
            return $statusOrder[$application->status] ?? 999;
        });

        $formattedApplications = $applications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : ($application->user?->name ?? 'N/A'),
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return response()->view('partials.reviewed_applicants_list', [
            'applicants' => $formattedApplications
        ]);
    }

    public function applicationsList(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $admin = $this->currentAdmin();
        $grantedVacancyIds = $this->hrDivisionGrantedVacancyIds();
        $isHrDivisionUser = (($admin->role ?? null) === 'hr_division');
        $accessSignature = $isHrDivisionUser
            ? $this->buildHrDivisionAccessSignature($grantedVacancyIds)
            : '';

        $query = JobVacancy::query()
            ->select(['vacancy_id', 'position_title', 'vacancy_type', 'status', 'created_at']);

        if ($isHrDivisionUser) {
            if (empty($grantedVacancyIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereRaw('UPPER(vacancy_type) = ?', ['COS'])
                    ->whereIn('vacancy_id', $grantedVacancyIds);
            }
        }

        // Filter by search text
        if (!empty($search)) {
            $query->where('position_title', 'LIKE', '%' . $search . '%');
        }

        // Filter by status (if used in future)
        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Get all vacancies with counts per status
        $vacancyQuery = $query->withCount([
            'applications as pending_count' => function ($q) {
                $q->statusEquals(ApplicationStatus::PENDING->value);
            },
            'applications as compliance_count' => function ($q) {
                $q->where(function ($sub) {
                    $sub->statusIn($this->complianceStageStatuses())
                        ->orWhereRaw('LOWER(TRIM(status)) = ?', ['incomplete']);
                });
            },
            'applications as qualified_count' => function ($q) {
                $q->where(function ($sub) {
                    $sub->statusEquals(ApplicationStatus::QUALIFIED->value)
                        ->orWhereRaw('LOWER(TRIM(status)) = ?', ['complete'])
                        ->orWhereRaw('LOWER(TRIM(qs_result)) = ?', ['qualified']);
                });
            },
        ])
            ->orderByRaw("CASE WHEN LOWER(status) = 'open' THEN 1 WHEN LOWER(status) = 'closed' THEN 2 ELSE 99 END")
            ->orderByDesc('created_at');

        // Return JSON if AJAX (for search)
        if ($request->ajax()) {
            $vacancies = $vacancyQuery->limit(200)->get();
            return response()->json($vacancies->values()); // reset keys
        }

        $vacancies = $vacancyQuery->paginate(20)->withQueryString();

        return view('admin.applications_list', [
            'vacancies' => $vacancies,
            'isHrDivisionUser' => $isHrDivisionUser,
            'accessSignature' => $accessSignature,
        ]);
    }

    public function hrDivisionAccessState()
    {
        $admin = $this->currentAdmin();
        if (!$admin) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401)->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        $isHrDivisionUser = (($admin->role ?? null) === 'hr_division');
        return response()->json([
            'is_hr_division' => $isHrDivisionUser,
            'access_signature' => $isHrDivisionUser ? $this->hrDivisionAccessSignature() : '',
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function ajaxSortApplicants(Request $request)
    {
        $sortOrder = $request->input('sort_order', 'latest');
        $vacancyId = $request->input('vacancy_id');

        if (!$this->hrDivisionCanAccessVacancy((string) $vacancyId)) {
            return response()->view('partials.applicants_list_ajax', ['applicants' => collect()]);
        }

        $query = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancyId)
            ->statusEquals(ApplicationStatus::PENDING->value);

        if ($sortOrder === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $applications = $query->get();

        $formattedApplications = $applications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : ($application->user?->name ?? 'N/A'),
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return response()->view('partials.applicants_list_ajax', ['applicants' => $formattedApplications]);
    }

    public function allApplicants($vacancy_id)
    {
        if (!$this->hrDivisionCanAccessVacancy((string) $vacancy_id)) {
            return redirect()->route('applications_list')
                ->with('error', 'Access denied. This COS vacancy is not assigned to your account.');
        }

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancy_id)
            ->orderByDesc('created_at') // Newest first
            ->get();

        $formattedApplications = $applications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : ($application->user?->name ?? 'N/A'),
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return view('admin.all_applicants_profile', [
            'applicants' => $formattedApplications,
            'filteredVacancyId' => $vacancy_id,
        ]);
    }

    public function manageApplicants(Request $request, $vacancy_id)
    {
        if (!$this->hrDivisionCanAccessVacancy((string) $vacancy_id)) {
            return redirect()->route('applications_list')
                ->with('error', 'Access denied. This COS vacancy is not assigned to your account.');
        }

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancy_id)
            ->orderByDesc('created_at')
            ->get();

        $partitioned = $this->partitionApplicantsByStage($applications, (string) $vacancy_id);

        $newApplications = $partitioned['new']->sortByDesc('created_at');
        $complianceApplications = $partitioned['compliance']->sortByDesc('created_at');
        $qualifiedApplications = $partitioned['qualified']->sortByDesc('created_at');

        $formattedNewApplicants = $this->formatApplicants($newApplications);
        $formattedComplianceApplicants = $this->formatApplicants($complianceApplications);
        $formattedQualifiedApplicants = $this->formatApplicants($qualifiedApplications);

        // Fetch vacancy info for header
        $vacancyInfo = JobVacancy::select('position_title', 'vacancy_type', 'place_of_assignment')
            ->where('vacancy_id', $vacancy_id)
            ->first();

        return view('admin.manage_applicants', [
            'newApplicants' => $formattedNewApplicants,
            'complianceApplicants' => $formattedComplianceApplicants,
            'qualifiedApplicants' => $formattedQualifiedApplicants,
            'newApplicantsCount' => $newApplications->count(),
            'complianceApplicantsCount' => $complianceApplications->count(),
            'qualifiedApplicantsCount' => $qualifiedApplications->count(),
            'vacancyId' => $vacancy_id,
            'positionTitle' => $vacancyInfo?->position_title,
            'vacancyType' => $vacancyInfo?->vacancy_type,
            'placeOfAssignment' => $vacancyInfo?->place_of_assignment,
        ]);
    }

    public function ajaxFilterNewApplicants(Request $request)
    {
        $vacancyId = $request->input('vacancy_id');
        $search = $request->input('search');
        $sortOrder = $request->input('sort_order', 'latest');

        if (!$this->hrDivisionCanAccessVacancy((string) $vacancyId)) {
            return response()->view('partials.manage_new_applicants_list', ['applicants' => collect()]);
        }

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancyId)
            ->get();

        $partitioned = $this->partitionApplicantsByStage($applications, (string) $vacancyId);
        $filtered = $this->filterApplicantsBySearch($partitioned['new'], (string) $search);
        $sorted = $this->sortApplicantsByDate($filtered, (string) $sortOrder)->values();

        return response()->view('partials.manage_new_applicants_list', [
            'applicants' => $this->formatApplicants($sorted)
        ]);
    }

    public function ajaxFilterComplianceApplicants(Request $request)
    {
        $vacancyId = $request->input('vacancy_id');
        $search = $request->input('search');
        $sortOrder = $request->input('sort_order', 'latest');

        if (!$this->hrDivisionCanAccessVacancy((string) $vacancyId)) {
            return response()->view('partials.manage_new_applicants_list', ['applicants' => collect()]);
        }

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancyId)
            ->get();

        $partitioned = $this->partitionApplicantsByStage($applications, (string) $vacancyId);
        $filtered = $this->filterApplicantsBySearch($partitioned['compliance'], (string) $search);
        $sorted = $this->sortApplicantsByDate($filtered, (string) $sortOrder)->values();

        return response()->view('partials.manage_new_applicants_list', [
            'applicants' => $this->formatApplicants($sorted)
        ]);
    }

    public function ajaxFilterQualifiedApplicants(Request $request)
    {
        $vacancyId = $request->input('vacancy_id');
        $search = $request->input('search');
        $sortOrder = $request->input('sort_order', 'latest');
        // $status filter removed as we only show Qualified here

        if (!$this->hrDivisionCanAccessVacancy((string) $vacancyId)) {
            return response()->view('partials.manage_qualified_applicants_list', ['applicants' => collect()]);
        }

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancyId)
            ->get();

        $partitioned = $this->partitionApplicantsByStage($applications, (string) $vacancyId);
        $filtered = $this->filterApplicantsBySearch($partitioned['qualified'], (string) $search);
        $sorted = $this->sortApplicantsByDate($filtered, (string) $sortOrder)->values();

        return response()->view('partials.manage_qualified_applicants_list', [
            'applicants' => $this->formatApplicants($sorted)
        ]);
    }

}

