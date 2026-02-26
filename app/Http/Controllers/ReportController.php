<?php

namespace App\Http\Controllers;

use App\Models\Applications;
use App\Models\ExamDetail;
use App\Models\JobVacancy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    private const PASSING_PERCENTAGE = 75.0;
    private const REPORT_CACHE_TTL_SECONDS = 300;
    private const REPORT_CACHE_VERSION = 'v1';

    private const REPORT_TYPES = [
        'vacancy_summary',
        'vacancy_performance',
        'vacancy_detailed',
        'applicant_master_list',
        'applicant_status_analytics',
        'exam_schedule',
        'exam_result_summary',
        'exam_vacancy_based_result',
    ];

    public function index()
    {
        $vacancies = Cache::remember(
            'reports:index:vacancies:' . self::REPORT_CACHE_VERSION,
            now()->addSeconds(self::REPORT_CACHE_TTL_SECONDS),
            function () {
                return JobVacancy::query()
                    ->select(['vacancy_id', 'position_title', 'vacancy_type', 'status'])
                    ->orderBy('position_title')
                    ->orderBy('vacancy_id')
                    ->get();
            }
        );

        return view('admin.reports.index', compact('vacancies'));
    }

    public function getData(Request $request)
    {
        $type = trim((string) $request->input('type', 'vacancy_summary'));
        if (!in_array($type, self::REPORT_TYPES, true)) {
            return response()->json(['error' => 'Invalid report type'], 400);
        }

        [$start, $end] = $this->resolveDateRange($request);
        $filters = $this->resolveFilters($request, $start, $end);
        $payload = $this->reportPayloadFromCache($type, $filters);

        if ($payload === null) {
            return response()->json(['error' => 'Unable to build report'], 422);
        }

        return response()->json($payload);
    }

    public function export(Request $request)
    {
        $type = trim((string) $request->input('type', 'applicant_master_list'));
        if (!in_array($type, self::REPORT_TYPES, true)) {
            return response()->json(['error' => 'Invalid report type'], 400);
        }

        [$start, $end] = $this->resolveDateRange($request);
        $filters = $this->resolveFilters($request, $start, $end);
        $payload = $this->reportPayloadFromCache($type, $filters);

        if ($payload === null || empty($payload['table']['headers'] ?? [])) {
            return response()->json(['error' => 'No exportable data found for this report'], 422);
        }

        $format = strtolower(trim((string) $request->input('format', 'csv')));
        if ($format === 'xlsx') {
            $format = 'excel';
        }

        $headers = $payload['table']['headers'];
        $rows = $payload['table']['rows'] ?? [];
        $baseName = Str::slug($type . '-' . now()->format('Y-m-d'));

        if ($format === 'pdf') {
            if ($type !== 'applicant_master_list') {
                return response()->json(['error' => 'PDF export is available for Applicant Master List only.'], 422);
            }

            return $this->exportApplicantMasterListPdf($baseName . '.pdf', $headers, $rows, $payload['title'] ?? 'Applicant Master List');
        }

        if ($format === 'excel') {
            if ($type !== 'applicant_master_list') {
                return response()->json(['error' => 'Excel export is available for Applicant Master List only.'], 422);
            }

            return $this->exportExcel($baseName . '.xlsx', $headers, $rows, $payload['title'] ?? 'Applicant Master List');
        }

        return $this->exportCsv($baseName . '.csv', $headers, $rows);
    }

    private function resolveDateRange(Request $request): array
    {
        $start = Carbon::now()->startOfYear();
        $end = Carbon::now()->endOfDay();

        try {
            if ($request->filled('start_date')) {
                $start = Carbon::parse((string) $request->input('start_date'))->startOfDay();
            }
            if ($request->filled('end_date')) {
                $end = Carbon::parse((string) $request->input('end_date'))->endOfDay();
            }
        } catch (\Throwable $e) {
            $start = Carbon::now()->startOfYear();
            $end = Carbon::now()->endOfDay();
        }

        if ($end->lt($start)) {
            $swap = $start->copy();
            $start = $end->copy()->startOfDay();
            $end = $swap->copy()->endOfDay();
        }

        return [$start, $end];
    }

    private function resolveFilters(Request $request, Carbon $start, Carbon $end): array
    {
        return [
            'start' => $start,
            'end' => $end,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'vacancy_id' => trim((string) $request->input('vacancy_id', '')),
            'status' => trim((string) $request->input('status', '')),
            'qualification' => trim((string) $request->input('qualification', '')),
        ];
    }

    private function buildReportPayload(string $type, array $filters): ?array
    {
        return match ($type) {
            'vacancy_summary' => $this->buildVacancySummaryReport($filters),
            'vacancy_performance' => $this->buildVacancyPerformanceReport($filters),
            'vacancy_detailed' => $this->buildVacancyDetailedReport($filters),
            'applicant_master_list' => $this->buildApplicantMasterListReport($filters),
            'applicant_status_analytics' => $this->buildApplicantStatusAnalyticsReport($filters),
            'exam_schedule' => $this->buildExamScheduleReport($filters),
            'exam_result_summary' => $this->buildExamResultSummaryReport($filters),
            'exam_vacancy_based_result' => $this->buildExamVacancyBasedResultReport($filters),
            default => null,
        };
    }

    private function reportPayloadFromCache(string $type, array $filters): ?array
    {
        $cacheKey = $this->buildReportCacheKey($type, $filters);

        return Cache::remember(
            $cacheKey,
            now()->addSeconds(self::REPORT_CACHE_TTL_SECONDS),
            fn() => $this->buildReportPayload($type, $filters)
        );
    }

    private function buildReportCacheKey(string $type, array $filters): string
    {
        $normalized = [
            'type' => $type,
            'start_date' => (string) ($filters['start_date'] ?? ''),
            'end_date' => (string) ($filters['end_date'] ?? ''),
            'vacancy_id' => (string) ($filters['vacancy_id'] ?? ''),
            'status' => (string) ($filters['status'] ?? ''),
            'qualification' => (string) ($filters['qualification'] ?? ''),
        ];

        return 'reports:data:' . self::REPORT_CACHE_VERSION . ':' . sha1(json_encode($normalized));
    }

    private function buildVacancySummaryReport(array $filters): array
    {
        $vacancies = $this->vacanciesForScope($filters);
        $applications = $this->applicationsForScope($filters);
        $appsByVacancy = $applications->groupBy('vacancy_id');

        $totalVacancies = $vacancies->count();
        $activeCount = $vacancies->filter(fn($v) => strtoupper((string) $v->status) !== 'CLOSED')->count();
        $closedCount = $totalVacancies - $activeCount;
        $cosCount = $vacancies->filter(fn($v) => strtoupper((string) $v->vacancy_type) === 'COS')->count();
        $plantillaCount = $totalVacancies - $cosCount;

        $rows = $vacancies
            ->map(function ($vacancy) use ($appsByVacancy) {
                $vacancyApps = $appsByVacancy->get((string) $vacancy->vacancy_id, collect());
                $isFilled = $this->isVacancyFilled($vacancyApps);

                return [
                    (string) $vacancy->vacancy_id,
                    (string) $vacancy->position_title,
                    $this->normalizeVacancyType((string) $vacancy->vacancy_type),
                    strtoupper((string) $vacancy->status),
                    $vacancyApps->count(),
                    $isFilled ? 'Filled' : 'Unfilled',
                ];
            })
            ->sortByDesc(fn($row) => (int) $row[4])
            ->values();

        $filledCount = $rows->where(5, 'Filled')->count();
        $unfilledCount = max($totalVacancies - $filledCount, 0);

        return [
            'type' => 'vacancy_summary',
            'title' => 'Vacancy Summary Report',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Total Vacancies', 'value' => $totalVacancies],
                ['label' => 'Active Vacancies', 'value' => $activeCount],
                ['label' => 'Closed Vacancies', 'value' => $closedCount],
                ['label' => 'Filled vs Unfilled', 'value' => "{$filledCount} / {$unfilledCount}"],
            ],
            'charts' => [
                [
                    'title' => 'Active vs Closed',
                    'type' => 'doughnut',
                    'labels' => ['Active', 'Closed'],
                    'datasets' => [[
                        'label' => 'Vacancies',
                        'data' => [$activeCount, $closedCount],
                        'backgroundColor' => ['#0D2B70', '#F97316'],
                    ]],
                ],
                [
                    'title' => 'COS vs Plantilla Distribution',
                    'type' => 'pie',
                    'labels' => ['COS', 'Plantilla'],
                    'datasets' => [[
                        'label' => 'Vacancies',
                        'data' => [$cosCount, $plantillaCount],
                        'backgroundColor' => ['#0EA5E9', '#22C55E'],
                    ]],
                ],
                [
                    'title' => 'Applicants per Vacancy (Top 10)',
                    'type' => 'bar',
                    'labels' => $rows->take(10)->map(fn($row) => $this->shortLabel($row[1]))->values()->all(),
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => $rows->take(10)->map(fn($row) => (int) $row[4])->values()->all(),
                        'backgroundColor' => '#0D2B70',
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Applicants per Vacancy',
                'headers' => ['Vacancy ID', 'Vacancy Title', 'Type', 'Status', 'Applicants', 'Fill State'],
                'rows' => $rows->all(),
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }
    private function buildVacancyPerformanceReport(array $filters): array
    {
        $vacancies = $this->vacanciesForScope($filters);
        $applications = $this->applicationsForScope($filters);
        $appsByVacancy = $applications->groupBy('vacancy_id');

        $rows = $vacancies
            ->map(function ($vacancy) use ($appsByVacancy) {
                $vacancyApps = $appsByVacancy->get((string) $vacancy->vacancy_id, collect());
                $appCount = $vacancyApps->count();

                $firstSuccessful = $vacancyApps
                    ->filter(fn($app) => $this->resolveOutcomeForApplication($app) === 'passed')
                    ->sortBy('created_at')
                    ->first();

                $timeToFillDays = null;
                $firstSuccessfulDate = '-';
                if ($firstSuccessful) {
                    $createdAt = Carbon::parse((string) $vacancy->created_at);
                    $firstSuccessfulAt = Carbon::parse((string) $firstSuccessful->created_at);
                    $timeToFillDays = $createdAt->diffInDays($firstSuccessfulAt);
                    $firstSuccessfulDate = $firstSuccessfulAt->format('M d, Y');
                }

                return [
                    'vacancy_id' => (string) $vacancy->vacancy_id,
                    'position_title' => (string) $vacancy->position_title,
                    'applications' => $appCount,
                    'first_successful_date' => $firstSuccessfulDate,
                    'time_to_fill_days' => $timeToFillDays,
                    'status' => strtoupper((string) $vacancy->status),
                ];
            })
            ->sortByDesc('applications')
            ->values();

        $totalVacancies = $vacancies->count();
        $totalApplications = $applications->count();
        $averageApplicants = $totalVacancies > 0 ? round($totalApplications / $totalVacancies, 2) : 0.0;

        $filledRows = $rows->whereNotNull('time_to_fill_days')->values();
        $avgTimeToFill = $filledRows->isNotEmpty()
            ? round($filledRows->avg('time_to_fill_days'), 2)
            : null;

        $mostApplied = $rows->first();
        $mostAppliedText = $mostApplied
            ? $this->shortLabel((string) $mostApplied['position_title']) . ' (' . (int) $mostApplied['applications'] . ')'
            : 'N/A';

        return [
            'type' => 'vacancy_performance',
            'title' => 'Vacancy Performance Report',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Average Applicants / Vacancy', 'value' => number_format($averageApplicants, 2)],
                ['label' => 'Average Time-to-Fill (Days)', 'value' => $avgTimeToFill === null ? 'N/A' : number_format($avgTimeToFill, 2)],
                ['label' => 'Most Applied Vacancy', 'value' => $mostAppliedText],
                ['label' => 'Vacancies Evaluated', 'value' => $totalVacancies],
            ],
            'charts' => [
                [
                    'title' => 'Applicants per Vacancy',
                    'type' => 'bar',
                    'labels' => $rows->take(10)->map(fn($row) => $this->shortLabel((string) $row['position_title']))->all(),
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => $rows->take(10)->pluck('applications')->map(fn($v) => (int) $v)->all(),
                        'backgroundColor' => '#0D2B70',
                    ]],
                ],
                [
                    'title' => 'Time-to-Fill by Vacancy (Days)',
                    'type' => 'line',
                    'labels' => $filledRows->map(fn($row) => $this->shortLabel((string) $row['position_title']))->all(),
                    'datasets' => [[
                        'label' => 'Days to Fill',
                        'data' => $filledRows->pluck('time_to_fill_days')->map(fn($v) => (int) $v)->all(),
                        'borderColor' => '#F97316',
                        'backgroundColor' => 'rgba(249, 115, 22, 0.2)',
                        'fill' => true,
                        'tension' => 0.2,
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Vacancy Performance Details',
                'headers' => ['Vacancy ID', 'Vacancy Title', 'Applicants', 'First Successful Applicant', 'Time-to-Fill (Days)', 'Vacancy Status'],
                'rows' => $rows->map(function ($row) {
                    return [
                        $row['vacancy_id'],
                        $row['position_title'],
                        (int) $row['applications'],
                        $row['first_successful_date'],
                        $row['time_to_fill_days'] === null ? 'N/A' : (int) $row['time_to_fill_days'],
                        $row['status'],
                    ];
                })->all(),
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }

    private function buildVacancyDetailedReport(array $filters): array
    {
        $vacancies = $this->vacanciesForScope($filters);
        $applications = $this->applicationsForScope($filters);
        $appsByVacancy = $applications->groupBy('vacancy_id');

        $rows = $vacancies->map(function ($vacancy) use ($appsByVacancy) {
            $vacancyApps = $appsByVacancy->get((string) $vacancy->vacancy_id, collect());
            $breakdown = ['reviewed' => 0, 'ongoing' => 0, 'passed' => 0, 'failed' => 0];

            foreach ($vacancyApps as $application) {
                $bucket = $this->resolveAnalyticsBucket($application);
                if (array_key_exists($bucket, $breakdown)) {
                    $breakdown[$bucket]++;
                }
            }

            return [
                (string) $vacancy->position_title,
                $this->normalizeVacancyType((string) $vacancy->vacancy_type),
                Carbon::parse((string) $vacancy->created_at)->format('M d, Y'),
                $vacancy->closing_date ? Carbon::parse((string) $vacancy->closing_date)->format('M d, Y') : '-',
                $vacancyApps->count(),
                $breakdown['reviewed'],
                $breakdown['ongoing'],
                $breakdown['passed'],
                $breakdown['failed'],
            ];
        })->values();

        $reviewedTotal = $rows->sum(fn($row) => (int) $row[5]);
        $ongoingTotal = $rows->sum(fn($row) => (int) $row[6]);
        $passedTotal = $rows->sum(fn($row) => (int) $row[7]);
        $failedTotal = $rows->sum(fn($row) => (int) $row[8]);

        return [
            'type' => 'vacancy_detailed',
            'title' => 'Vacancy Detailed Report (Printable)',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Total Vacancies', 'value' => $vacancies->count()],
                ['label' => 'Total Applicants', 'value' => $rows->sum(fn($row) => (int) $row[4])],
                ['label' => 'Passed', 'value' => $passedTotal],
                ['label' => 'Failed', 'value' => $failedTotal],
            ],
            'charts' => [
                [
                    'title' => 'Status Breakdown',
                    'type' => 'bar',
                    'labels' => ['Reviewed', 'Ongoing', 'Passed', 'Failed'],
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => [$reviewedTotal, $ongoingTotal, $passedTotal, $failedTotal],
                        'backgroundColor' => ['#2563EB', '#F59E0B', '#16A34A', '#DC2626'],
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Vacancy Detailed Listing',
                'headers' => ['Vacancy Title', 'Type', 'Opening Date', 'Closing Date', 'Total Applicants', 'Reviewed', 'Ongoing', 'Passed', 'Failed'],
                'rows' => $rows->all(),
            ],
            'meta' => [
                'generated_at' => now()->toDateTimeString(),
                'printable' => true,
            ],
        ];
    }

    private function buildApplicantMasterListReport(array $filters): array
    {
        $applications = $this->applicantMasterListQuery($filters)->get();
        $statusFilter = strtolower($filters['status']);

        if (in_array($statusFilter, ['reviewed', 'ongoing', 'passed', 'failed', 'withdrawn'], true)) {
            $applications = $applications->filter(function ($application) use ($statusFilter) {
                return $this->resolveAnalyticsBucket($application) === $statusFilter;
            })->values();
        }

        $rows = $applications->map(function ($application) {
            $scorePct = $this->extractScorePercentage((string) ($application->result ?? ''));
            $outcome = $this->resolveOutcomeForApplication($application);
            $userName = trim((string) optional($application->user)->name);
            $userEmail = trim((string) optional($application->user)->email);
            $vacancyTitle = trim((string) optional($application->vacancy)->position_title);

            return [
                Carbon::parse((string) $application->created_at)->format('Y-m-d'),
                $userName !== '' ? $userName : ('Applicant #' . (int) $application->user_id),
                $userEmail !== '' ? $userEmail : '-',
                (string) $application->vacancy_id,
                $vacancyTitle !== '' ? $vacancyTitle : '-',
                (string) $application->status,
                (string) ($application->qs_result ?: '-'),
                (string) ($application->result ?: '-'),
                $scorePct === null ? '-' : number_format($scorePct, 2) . '%',
                $outcome ? ucfirst($outcome) : 'N/A',
            ];
        })->values();

        $bucketCounts = $this->countAnalyticsBuckets($applications);

        return [
            'type' => 'applicant_master_list',
            'title' => 'Applicant Master List',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Total Applicants', 'value' => $applications->count()],
                ['label' => 'Reviewed', 'value' => $bucketCounts['reviewed']],
                ['label' => 'Ongoing', 'value' => $bucketCounts['ongoing']],
                ['label' => 'Passed / Failed', 'value' => $bucketCounts['passed'] . ' / ' . $bucketCounts['failed']],
            ],
            'charts' => [
                [
                    'title' => 'Applicant Outcome Distribution',
                    'type' => 'bar',
                    'labels' => ['Reviewed', 'Ongoing', 'Passed', 'Failed', 'Withdrawn'],
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => [
                            $bucketCounts['reviewed'],
                            $bucketCounts['ongoing'],
                            $bucketCounts['passed'],
                            $bucketCounts['failed'],
                            $bucketCounts['withdrawn'],
                        ],
                        'backgroundColor' => ['#2563EB', '#F59E0B', '#16A34A', '#DC2626', '#6B7280'],
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Applicant Master Listing',
                'headers' => ['Date Applied', 'Applicant Name', 'Email', 'Vacancy ID', 'Vacancy Title', 'Status', 'Qualification', 'Exam Result', 'Score %', 'Outcome'],
                'rows' => $rows->all(),
            ],
            'meta' => [
                'generated_at' => now()->toDateTimeString(),
                'export_pdf' => true,
                'export_excel' => true,
            ],
        ];
    }

    private function buildApplicantStatusAnalyticsReport(array $filters): array
    {
        $applications = $this->applicationsForScope($filters);
        if ($filters['qualification'] !== '') {
            $applications = $applications->filter(fn($app) => strcasecmp((string) ($app->qs_result ?? ''), $filters['qualification']) === 0)->values();
        }

        $bucketCounts = $this->countAnalyticsBuckets($applications);

        return [
            'type' => 'applicant_status_analytics',
            'title' => 'Applicant Status Analytics',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Reviewed', 'value' => $bucketCounts['reviewed']],
                ['label' => 'Ongoing', 'value' => $bucketCounts['ongoing']],
                ['label' => 'Passed', 'value' => $bucketCounts['passed']],
                ['label' => 'Failed', 'value' => $bucketCounts['failed']],
                ['label' => 'Withdrawn', 'value' => $bucketCounts['withdrawn']],
            ],
            'charts' => [
                [
                    'title' => 'Applicant Lifecycle Distribution',
                    'type' => 'doughnut',
                    'labels' => ['Reviewed', 'Ongoing', 'Passed', 'Failed', 'Withdrawn'],
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => [
                            $bucketCounts['reviewed'],
                            $bucketCounts['ongoing'],
                            $bucketCounts['passed'],
                            $bucketCounts['failed'],
                            $bucketCounts['withdrawn'],
                        ],
                        'backgroundColor' => ['#2563EB', '#F59E0B', '#16A34A', '#DC2626', '#6B7280'],
                    ]],
                ],
                [
                    'title' => 'Applicant Lifecycle Counts',
                    'type' => 'bar',
                    'labels' => ['Reviewed', 'Ongoing', 'Passed', 'Failed', 'Withdrawn'],
                    'datasets' => [[
                        'label' => 'Count',
                        'data' => [
                            $bucketCounts['reviewed'],
                            $bucketCounts['ongoing'],
                            $bucketCounts['passed'],
                            $bucketCounts['failed'],
                            $bucketCounts['withdrawn'],
                        ],
                        'backgroundColor' => '#0D2B70',
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Applicant Status Breakdown',
                'headers' => ['Category', 'Count'],
                'rows' => [
                    ['Reviewed', $bucketCounts['reviewed']],
                    ['Ongoing', $bucketCounts['ongoing']],
                    ['Passed', $bucketCounts['passed']],
                    ['Failed', $bucketCounts['failed']],
                    ['Withdrawn', $bucketCounts['withdrawn']],
                ],
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }

    private function buildExamScheduleReport(array $filters): array
    {
        $examDetails = ExamDetail::query()
            ->with('vacancy:vacancy_id,position_title,vacancy_type')
            ->when($filters['vacancy_id'] !== '', function ($query) use ($filters) {
                $query->where('vacancy_id', $filters['vacancy_id']);
            })
            ->whereNotNull('date')
            ->whereBetween('date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $vacancyIds = $examDetails->pluck('vacancy_id')->filter()->unique()->values()->all();

        $appsByVacancy = Applications::query()
            ->when($filters['vacancy_id'] !== '', function ($query) use ($filters) {
                $query->where('vacancy_id', $filters['vacancy_id']);
            })
            ->when(!empty($vacancyIds), function ($query) use ($vacancyIds) {
                $query->whereIn('vacancy_id', $vacancyIds);
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->get(['vacancy_id', 'link_sent_at', 'exam_token', 'exam_started_at', 'exam_submitted_at'])
            ->groupBy('vacancy_id');

        $now = now();
        $upcomingCount = 0;
        $pastCount = 0;
        $totalInvited = 0;
        $totalAttended = 0;

        $rows = $examDetails->map(function ($exam) use ($appsByVacancy, $now, &$upcomingCount, &$pastCount, &$totalInvited, &$totalAttended) {
            $date = $exam->date ? Carbon::parse((string) $exam->date) : null;
            $startDateTime = null;
            if ($date && $exam->time) {
                $startDateTime = Carbon::parse($date->toDateString() . ' ' . (string) $exam->time);
            }

            $scheduleType = 'Unscheduled';
            if ($startDateTime) {
                if ($startDateTime->gte($now)) {
                    $scheduleType = 'Upcoming';
                    $upcomingCount++;
                } else {
                    $scheduleType = 'Past';
                    $pastCount++;
                }
            }

            $vacancyApps = $appsByVacancy->get((string) $exam->vacancy_id, collect());
            $invited = $vacancyApps->filter(fn($app) => !empty($app->link_sent_at) || !empty($app->exam_token))->count();
            $attended = $vacancyApps->filter(fn($app) => !empty($app->exam_started_at) || !empty($app->exam_submitted_at))->count();

            $totalInvited += $invited;
            $totalAttended += $attended;

            $attendanceRate = $invited > 0 ? round(($attended / $invited) * 100, 2) : 0.0;

            return [
                (string) $exam->vacancy_id,
                (string) (optional($exam->vacancy)->position_title ?? '-'),
                $date ? $date->format('M d, Y') : '-',
                $exam->time ? Carbon::parse((string) $exam->time)->format('h:i A') : '-',
                $exam->time_end ? Carbon::parse((string) $exam->time_end)->format('h:i A') : '-',
                (string) ($exam->place ?: '-'),
                $scheduleType,
                $invited,
                $attended,
                number_format($attendanceRate, 2) . '%',
            ];
        })->values();

        return [
            'type' => 'exam_schedule',
            'title' => 'Exam Schedule Report',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Upcoming Exams', 'value' => $upcomingCount],
                ['label' => 'Past Exams', 'value' => $pastCount],
                ['label' => 'Invited Applicants', 'value' => $totalInvited],
                ['label' => 'Attended Applicants', 'value' => $totalAttended],
            ],
            'charts' => [
                [
                    'title' => 'Upcoming vs Past Exams',
                    'type' => 'doughnut',
                    'labels' => ['Upcoming', 'Past'],
                    'datasets' => [[
                        'label' => 'Exam Schedules',
                        'data' => [$upcomingCount, $pastCount],
                        'backgroundColor' => ['#0D2B70', '#F97316'],
                    ]],
                ],
                [
                    'title' => 'Attendance Rate by Vacancy',
                    'type' => 'bar',
                    'labels' => $rows->map(fn($row) => $this->shortLabel((string) $row[1]))->all(),
                    'datasets' => [[
                        'label' => 'Attendance %',
                        'data' => $rows->map(function ($row) {
                            return (float) str_replace('%', '', (string) $row[9]);
                        })->all(),
                        'backgroundColor' => '#16A34A',
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Exam Schedules and Attendance',
                'headers' => ['Vacancy ID', 'Vacancy Title', 'Exam Date', 'Start Time', 'End Time', 'Venue', 'Schedule Type', 'Invited', 'Attended', 'Attendance Rate'],
                'rows' => $rows->all(),
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }

    private function buildExamResultSummaryReport(array $filters): array
    {
        $applications = Applications::query()
            ->with(['user:id,name,email', 'vacancy:vacancy_id,position_title'])
            ->whereBetween('created_at', [$filters['start'], $filters['end']])
            ->when($filters['vacancy_id'] !== '', function ($query) use ($filters) {
                $query->where('vacancy_id', $filters['vacancy_id']);
            })
            ->where(function ($query) {
                $query->whereNotNull('result')
                    ->orWhereNotNull('exam_submitted_at')
                    ->orWhereNotNull('scores');
            })
            ->get();

        $scored = $applications->map(function ($application) {
            $pct = $this->extractScorePercentage((string) ($application->result ?? ''));
            $outcome = $this->resolveOutcomeForApplication($application);

            if ($pct !== null && $outcome === null) {
                $outcome = $pct >= self::PASSING_PERCENTAGE ? 'passed' : 'failed';
            }

            return [
                'application' => $application,
                'score_pct' => $pct,
                'outcome' => $outcome,
            ];
        })->filter(fn($row) => $row['score_pct'] !== null)->values();

        $passedCount = $scored->filter(fn($row) => $row['outcome'] === 'passed')->count();
        $failedCount = $scored->filter(fn($row) => $row['outcome'] === 'failed')->count();
        $scoredCount = $scored->count();
        $passRate = $scoredCount > 0 ? round(($passedCount / $scoredCount) * 100, 2) : 0.0;
        $averageScore = $scoredCount > 0 ? round((float) $scored->avg('score_pct'), 2) : 0.0;

        $topPerformers = $scored
            ->sortByDesc('score_pct')
            ->take(10)
            ->values();

        $scoreBuckets = [
            '0-49' => 0,
            '50-59' => 0,
            '60-69' => 0,
            '70-79' => 0,
            '80-89' => 0,
            '90-100' => 0,
        ];

        foreach ($scored as $row) {
            $score = (float) $row['score_pct'];
            if ($score < 50) {
                $scoreBuckets['0-49']++;
            } elseif ($score < 60) {
                $scoreBuckets['50-59']++;
            } elseif ($score < 70) {
                $scoreBuckets['60-69']++;
            } elseif ($score < 80) {
                $scoreBuckets['70-79']++;
            } elseif ($score < 90) {
                $scoreBuckets['80-89']++;
            } else {
                $scoreBuckets['90-100']++;
            }
        }

        return [
            'type' => 'exam_result_summary',
            'title' => 'Exam Result Summary',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Pass Rate %', 'value' => number_format($passRate, 2) . '%'],
                ['label' => 'Average Score', 'value' => number_format($averageScore, 2) . '%'],
                ['label' => 'Top Performers', 'value' => $topPerformers->count()],
                ['label' => 'Failed Count', 'value' => $failedCount],
            ],
            'charts' => [
                [
                    'title' => 'Pass vs Failed',
                    'type' => 'doughnut',
                    'labels' => ['Passed', 'Failed'],
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => [$passedCount, $failedCount],
                        'backgroundColor' => ['#16A34A', '#DC2626'],
                    ]],
                ],
                [
                    'title' => 'Score Distribution',
                    'type' => 'bar',
                    'labels' => array_keys($scoreBuckets),
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => array_values($scoreBuckets),
                        'backgroundColor' => '#0D2B70',
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Top Performers',
                'headers' => ['Applicant', 'Email', 'Vacancy', 'Score %', 'Exam Result', 'Outcome', 'Submitted At'],
                'rows' => $topPerformers->map(function ($row) {
                    $application = $row['application'];
                    return [
                        (string) (optional($application->user)->name ?? ('Applicant #' . (int) $application->user_id)),
                        (string) (optional($application->user)->email ?? '-'),
                        (string) (optional($application->vacancy)->position_title ?? $application->vacancy_id),
                        number_format((float) $row['score_pct'], 2) . '%',
                        (string) ($application->result ?: '-'),
                        ucfirst((string) ($row['outcome'] ?? 'n/a')),
                        $application->exam_submitted_at ? Carbon::parse((string) $application->exam_submitted_at)->format('M d, Y h:i A') : '-',
                    ];
                })->all(),
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }

    private function buildExamVacancyBasedResultReport(array $filters): array
    {
        $vacancies = $this->vacanciesForScope($filters);
        $applications = $this->applicationsForScope($filters);
        $appsByVacancy = $applications->groupBy('vacancy_id');

        $rows = $vacancies->map(function ($vacancy) use ($appsByVacancy) {
            $vacancyApps = $appsByVacancy->get((string) $vacancy->vacancy_id, collect());

            $invited = $vacancyApps->filter(fn($app) => !empty($app->link_sent_at) || !empty($app->exam_token))->count();
            $attended = $vacancyApps->filter(fn($app) => !empty($app->exam_started_at) || !empty($app->exam_submitted_at))->count();
            $passed = $vacancyApps->filter(fn($app) => $this->resolveOutcomeForApplication($app) === 'passed')->count();
            $failed = $vacancyApps->filter(fn($app) => $this->resolveOutcomeForApplication($app) === 'failed')->count();

            return [
                (string) $vacancy->vacancy_id,
                (string) $vacancy->position_title,
                $invited,
                $attended,
                $passed,
                $failed,
            ];
        })->filter(fn($row) => ((int) $row[2] + (int) $row[3] + (int) $row[4] + (int) $row[5]) > 0)
            ->values();

        return [
            'type' => 'exam_vacancy_based_result',
            'title' => 'Vacancy-Based Exam Result',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Vacancies with Exam Data', 'value' => $rows->count()],
                ['label' => 'Applicants Invited', 'value' => $rows->sum(fn($row) => (int) $row[2])],
                ['label' => 'Applicants Attended', 'value' => $rows->sum(fn($row) => (int) $row[3])],
                ['label' => 'Passed / Failed', 'value' => $rows->sum(fn($row) => (int) $row[4]) . ' / ' . $rows->sum(fn($row) => (int) $row[5])],
            ],
            'charts' => [
                [
                    'title' => 'Invited vs Attended vs Passed vs Failed',
                    'type' => 'bar',
                    'labels' => $rows->map(fn($row) => $this->shortLabel((string) $row[1]))->all(),
                    'datasets' => [
                        [
                            'label' => 'Invited',
                            'data' => $rows->map(fn($row) => (int) $row[2])->all(),
                            'backgroundColor' => '#0EA5E9',
                        ],
                        [
                            'label' => 'Attended',
                            'data' => $rows->map(fn($row) => (int) $row[3])->all(),
                            'backgroundColor' => '#F59E0B',
                        ],
                        [
                            'label' => 'Passed',
                            'data' => $rows->map(fn($row) => (int) $row[4])->all(),
                            'backgroundColor' => '#16A34A',
                        ],
                        [
                            'label' => 'Failed',
                            'data' => $rows->map(fn($row) => (int) $row[5])->all(),
                            'backgroundColor' => '#DC2626',
                        ],
                    ],
                ],
            ],
            'table' => [
                'title' => 'Exam Result by Vacancy',
                'headers' => ['Vacancy ID', 'Vacancy Title', 'Applicants Invited', 'Applicants Attended', 'Passed', 'Failed'],
                'rows' => $rows->all(),
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }

    private function vacanciesForScope(array $filters): Collection
    {
        return JobVacancy::query()
            ->select(['vacancy_id', 'position_title', 'vacancy_type', 'status', 'created_at', 'closing_date'])
            ->when($filters['vacancy_id'] !== '', function ($query) use ($filters) {
                $query->where('vacancy_id', $filters['vacancy_id']);
            })
            ->whereBetween('created_at', [$filters['start'], $filters['end']])
            ->orderBy('position_title')
            ->orderBy('vacancy_id')
            ->get();
    }

    private function applicationsForScope(array $filters): Collection
    {
        return Applications::query()
            ->select([
                'id',
                'user_id',
                'vacancy_id',
                'status',
                'result',
                'qs_result',
                'link_sent_at',
                'exam_token',
                'exam_started_at',
                'exam_submitted_at',
                'created_at',
            ])
            ->whereBetween('created_at', [$filters['start'], $filters['end']])
            ->when($filters['vacancy_id'] !== '', function ($query) use ($filters) {
                $query->where('vacancy_id', $filters['vacancy_id']);
            })
            ->get();
    }

    private function applicantMasterListQuery(array $filters)
    {
        $query = Applications::query()
            ->with(['user:id,name,email', 'vacancy:vacancy_id,position_title,vacancy_type,status'])
            ->whereBetween('created_at', [$filters['start'], $filters['end']])
            ->when($filters['vacancy_id'] !== '', function ($q) use ($filters) {
                $q->where('vacancy_id', $filters['vacancy_id']);
            })
            ->when($filters['qualification'] !== '', function ($q) use ($filters) {
                $q->where('qs_result', $filters['qualification']);
            })
            ->orderByDesc('created_at');

        $status = strtolower($filters['status']);
        if ($status !== '' && !in_array($status, ['reviewed', 'ongoing', 'passed', 'failed', 'withdrawn'], true)) {
            $query->whereRaw('LOWER(status) = ?', [$status]);
        }

        return $query;
    }

    private function isVacancyFilled(Collection $applications): bool
    {
        return $applications->contains(function ($application) {
            return $this->resolveOutcomeForApplication($application) === 'passed';
        });
    }

    private function extractScorePercentage(?string $result): ?float
    {
        $result = trim((string) $result);
        if ($result === '') {
            return null;
        }

        if (!preg_match('/(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)/', $result, $matches)) {
            return null;
        }

        $numerator = (float) $matches[1];
        $denominator = (float) $matches[2];
        if ($denominator <= 0) {
            return null;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    private function resolveOutcomeForApplication($application): ?string
    {
        $status = strtolower(trim((string) data_get($application, 'status', '')));
        if ($status === 'withdrawn') {
            return 'withdrawn';
        }

        $qsResult = strtolower(trim((string) data_get($application, 'qs_result', '')));
        if ($qsResult === 'qualified') {
            return 'passed';
        }
        if ($qsResult === 'not qualified') {
            return 'failed';
        }

        $score = $this->extractScorePercentage((string) data_get($application, 'result', ''));
        if ($score === null) {
            return null;
        }

        return $score >= self::PASSING_PERCENTAGE ? 'passed' : 'failed';
    }

    private function resolveAnalyticsBucket($application): string
    {
        $outcome = $this->resolveOutcomeForApplication($application);
        if ($outcome === 'withdrawn') {
            return 'withdrawn';
        }
        if ($outcome === 'passed') {
            return 'passed';
        }
        if ($outcome === 'failed') {
            return 'failed';
        }

        $status = strtolower(trim((string) data_get($application, 'status', '')));
        if (in_array($status, ['pending', 'incomplete', 'updated', 'submitted', 'in-progress', 'ready', 'compliance'], true)) {
            return 'ongoing';
        }

        return 'reviewed';
    }

    private function countAnalyticsBuckets(Collection $applications): array
    {
        $counts = [
            'reviewed' => 0,
            'ongoing' => 0,
            'passed' => 0,
            'failed' => 0,
            'withdrawn' => 0,
        ];

        foreach ($applications as $application) {
            $bucket = $this->resolveAnalyticsBucket($application);
            if (array_key_exists($bucket, $counts)) {
                $counts[$bucket]++;
            }
        }

        return $counts;
    }

    private function publicFilters(array $filters): array
    {
        return [
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
            'vacancy_id' => $filters['vacancy_id'],
            'status' => $filters['status'],
            'qualification' => $filters['qualification'],
            'passing_percentage' => self::PASSING_PERCENTAGE,
        ];
    }

    private function normalizeVacancyType(string $type): string
    {
        return strtoupper(trim($type)) === 'COS' ? 'COS' : 'Plantilla';
    }

    private function shortLabel(string $text, int $limit = 24): string
    {
        return Str::limit(trim($text), $limit, '...');
    }

    private function exportCsv(string $fileName, array $headers, array $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportExcel(string $fileName, array $headers, array $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Report');

            $col = 1;
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($col, 1, (string) $header);
                $col++;
            }

            $rowIndex = 2;
            foreach ($rows as $row) {
                $colIndex = 1;
                foreach ($row as $value) {
                    $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, is_scalar($value) ? (string) $value : json_encode($value));
                    $colIndex++;
                }
                $rowIndex++;
            }

            $highestColumn = $sheet->getHighestColumn();
            foreach (range('A', $highestColumn) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function exportApplicantMasterListPdf(string $fileName, array $headers, array $rows, string $title)
    {
        $pdf = new \FPDF('L', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, $this->toPdfText($title), 0, 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 6, $this->toPdfText('Generated at: ' . now()->format('F d, Y h:i A')), 0, 1);
        $pdf->Ln(2);

        $contentWidth = 277;
        $columnCount = max(count($headers), 1);
        $baseWidth = (int) floor($contentWidth / $columnCount);
        $widths = array_fill(0, $columnCount, $baseWidth);
        $widths[$columnCount - 1] += $contentWidth - array_sum($widths);

        $pdf->SetFont('Arial', 'B', 8);
        foreach ($headers as $index => $header) {
            $pdf->Cell($widths[$index], 7, $this->toPdfText(Str::limit((string) $header, 24, '.')), 1, 0, 'C');
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        foreach ($rows as $row) {
            if ($pdf->GetY() > 190) {
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 8);
                foreach ($headers as $index => $header) {
                    $pdf->Cell($widths[$index], 7, $this->toPdfText(Str::limit((string) $header, 24, '.')), 1, 0, 'C');
                }
                $pdf->Ln();
                $pdf->SetFont('Arial', '', 7);
            }

            foreach ($headers as $index => $header) {
                $value = $row[$index] ?? '';
                $text = $this->toPdfText(Str::limit((string) $value, 36, '...'));
                $pdf->Cell($widths[$index], 6, $text, 1, 0, 'L');
            }
            $pdf->Ln();
        }

        $content = $pdf->Output('S');
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function toPdfText(string $text): string
    {
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        return $converted === false ? utf8_decode($text) : $converted;
    }
}
