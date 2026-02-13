<?php

namespace App\Http\Controllers;

use App\Models\Applications;
use App\Models\PersonalInformation;
use App\Models\JobVacancy;
use Illuminate\Http\Request;

class ShowApplicantsProfile extends Controller
{
    public function index(Request $request, $vacancy_id)
    {
        logger()->info("Filtering applicants for vacancy: " . $vacancy_id);

        $applications = Applications::with(['vacancy', 'personalInformation'])
            ->where('vacancy_id', $vacancy_id)
            ->where('status', 'Pending')
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
                    : 'N/A',
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
        $sortStatus = $request->input('sort_status');

        $query = Applications::with(['vacancy', 'personalInformation'])
            ->where('status', '!=', 'Pending')
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
                    : 'N/A',
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

        $query = Applications::with(['vacancy', 'personalInformation'])
            ->where('status', '!=', 'Pending');

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
                    : 'N/A',
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

        $query = JobVacancy::query();

        // Filter by search text
        if (!empty($search)) {
            $query->where('position_title', 'LIKE', '%' . $search . '%');
        }

        // Filter by status (if used in future)
        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Get all vacancies with counts per status
        $vacancies = $query->withCount([
            'applications as pending_count' => function ($q) {
                $q->where('status', 'Pending');
            },
            'applications as compliance_count' => function ($q) {
                $q->where('status', 'Compliance');
            },
            'applications as qualified_count' => function ($q) {
                $q->where('status', 'Qualified');
            },
        ])->get();

        // Sort logic: Open first, then Closed. Inside each, sort by newest created.
        $vacancies = $vacancies->sortBy(function ($vacancy) {
            $statusPriority = match (strtolower($vacancy->status)) {
                'open' => 1,
                'closed' => 2,
                default => 99
            };

            // Combine status priority and inverse of created_at timestamp
            return [$statusPriority, -strtotime($vacancy->created_at)];
        });

        // Return JSON if AJAX (for search)
        if ($request->ajax()) {
            return response()->json($vacancies->values()); // reset keys
        }

        return view('admin.applications_list', [
            'vacancies' => $vacancies
        ]);
    }

    public function ajaxSortApplicants(Request $request)
    {
        $sortOrder = $request->input('sort_order', 'latest');
        $vacancyId = $request->input('vacancy_id');

        $query = Applications::with(['vacancy', 'personalInformation'])
            ->where('vacancy_id', $vacancyId)
            ->where('status', 'Pending');

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
                    : 'N/A',
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return response()->view('partials.applicants_list_ajax', ['applicants' => $formattedApplications]);
    }

    public function allApplicants($vacancy_id)
    {
        $applications = Applications::with(['vacancy', 'personalInformation'])
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
                    : 'N/A',
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
        // Get new applicants (Pending status)
        $newApplications = Applications::with(['vacancy', 'personalInformation'])
            ->where('vacancy_id', $vacancy_id)
            ->where('status', 'Pending')
            ->orderByDesc('created_at')
            ->get();

        // Get compliance applicants
        $complianceApplications = Applications::with(['vacancy', 'personalInformation'])
            ->where('vacancy_id', $vacancy_id)
            ->where('status', 'Compliance')
            ->orderByDesc('created_at')
            ->get();

        // Get qualified applicants
        $qualifiedApplications = Applications::with(['vacancy', 'personalInformation'])
            ->where('vacancy_id', $vacancy_id)
            ->where('status', 'Qualified')
            ->orderByDesc('created_at')
            ->get();

        // Format new applicants
        $formattedNewApplicants = $newApplications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : 'N/A',
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        // Format compliance applicants
        $formattedComplianceApplicants = $complianceApplications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : 'N/A',
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        // Format qualified applicants
        $formattedQualifiedApplicants = $qualifiedApplications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : 'N/A',
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

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

        $query = Applications::with(['vacancy', 'personalInformation'])
            ->where('vacancy_id', $vacancyId)
            ->where('status', 'Pending');

        // Apply search filter
        if (!empty($search)) {
            $query->whereHas('personalInformation', function ($q) use ($search) {
                $q->where('first_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('surname', 'LIKE', '%' . $search . '%')
                    ->orWhere('middle_name', 'LIKE', '%' . $search . '%');
            });
        }

        // Apply sort order
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
                    : 'N/A',
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return response()->view('partials.manage_new_applicants_list', ['applicants' => $formattedApplications]);
    }

    public function ajaxFilterComplianceApplicants(Request $request)
    {
        $vacancyId = $request->input('vacancy_id');
        $search = $request->input('search');
        $sortOrder = $request->input('sort_order', 'latest');

        $query = Applications::with(['vacancy', 'personalInformation'])
            ->where('vacancy_id', $vacancyId)
            ->where('status', 'Compliance');

        // Apply search filter
        if (!empty($search)) {
            $query->whereHas('personalInformation', function ($q) use ($search) {
                $q->where('first_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('surname', 'LIKE', '%' . $search . '%')
                    ->orWhere('middle_name', 'LIKE', '%' . $search . '%');
            });
        }

        // Apply sort order
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
                    : 'N/A',
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        // Use same partial as new applicants since structure is similar
        return response()->view('partials.manage_new_applicants_list', ['applicants' => $formattedApplications]);
    }

    public function ajaxFilterQualifiedApplicants(Request $request)
    {
        $vacancyId = $request->input('vacancy_id');
        $search = $request->input('search');
        // $status filter removed as we only show Qualified here

        $query = Applications::with(['vacancy', 'personalInformation'])
            ->where('vacancy_id', $vacancyId)
            ->where('status', 'Qualified');

        // Apply search filter
        if (!empty($search)) {
            $query->whereHas('personalInformation', function ($q) use ($search) {
                $q->where('first_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('surname', 'LIKE', '%' . $search . '%')
                    ->orWhere('middle_name', 'LIKE', '%' . $search . '%');
            });
        }

        $applications = $query->orderByDesc('created_at')->get();

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
                    : 'N/A',
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return response()->view('partials.manage_reviewed_applicants_list', ['applicants' => $formattedApplications]);
    }

}
