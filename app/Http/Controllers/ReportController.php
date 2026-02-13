<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobVacancy;
use App\Models\Applications;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function getData(Request $request)
    {
        $type = $request->input('type');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfYear();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfDay();

        switch ($type) {
            case 'recruitment_performance':
                return $this->getRecruitmentPerformance($startDate, $endDate);
            case 'applicant_demographics':
                return $this->getApplicantDemographics($startDate, $endDate);
            case 'financial_summary':
                return $this->getFinancialSummary();
            case 'user_activity':
                return $this->getUserActivity($startDate, $endDate);
            case 'inventory':
                return $this->getInventoryReport();
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }
    }

    private function getRecruitmentPerformance($start, $end)
    {
        // Applications over time
        $applicationsOverTime = Applications::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Vacancy Status
        $vacancyStatus = JobVacancy::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json([
            'applications_over_time' => $applicationsOverTime,
            'vacancy_status' => $vacancyStatus,
            'total_applications' => Applications::whereBetween('created_at', [$start, $end])->count(),
            'total_vacancies' => JobVacancy::count(),
        ]);
    }

    private function getApplicantDemographics($start, $end)
    {
        // Gender distribution (Need to join with PersonalInformation if exists, but assuming User or PDS data)
        // Since we don't have direct access to PDS table structure in memory, let's use what we have.
        // We will assume Applications -> User -> PersonalInformation (if relation exists)
        // Or just count total applicants for now.
        
        // Let's look at Applications status breakdown
        $statusBreakdown = Applications::select('status', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('status')
            ->get();

        return response()->json([
            'status_breakdown' => $statusBreakdown
        ]);
    }

    private function getFinancialSummary()
    {
        // Projected Monthly Salaries of Open Vacancies
        $salaryData = JobVacancy::where('status', '!=', 'CLOSED') // Assuming 'OPEN' or other active statuses
            ->select('position_title', 'monthly_salary', 'vacancy_type')
            ->orderBy('monthly_salary', 'desc')
            ->limit(10)
            ->get();

        $totalProjectedCost = JobVacancy::where('status', '!=', 'CLOSED')->sum('monthly_salary');
        $costByType = JobVacancy::where('status', '!=', 'CLOSED')
            ->select('vacancy_type', DB::raw('sum(monthly_salary) as total'))
            ->groupBy('vacancy_type')
            ->get();

        return response()->json([
            'top_salaries' => $salaryData,
            'total_projected_cost' => $totalProjectedCost,
            'cost_by_type' => $costByType
        ]);
    }

    private function getUserActivity($start, $end)
    {
        // Top Active Users
        $topUsers = Activity::select('causer_id', DB::raw('count(*) as count'))
            ->whereNotNull('causer_id')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('causer_id')
            ->orderByDesc('count')
            ->limit(5)
            ->with('causer') // Assuming relation exists
            ->get()
            ->map(function ($log) {
                return [
                    'name' => $log->causer ? $log->causer->name : 'Unknown',
                    'count' => $log->count
                ];
            });

        // Recent Logs
        $recentLogs = Activity::with('causer')
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'top_users' => $topUsers,
            'recent_logs' => $recentLogs
        ]);
    }

    private function getInventoryReport()
    {
        // Talent Pool Inventory
        // Count unique applicants
        $totalCandidates = Applications::distinct('user_id')->count();
        
        // Candidates by Eligibility (if available in applications table or linked)
        // We have qs_eligibility in applications table
        $eligibilityBreakdown = Applications::select('qs_eligibility', DB::raw('count(*) as count'))
            ->whereNotNull('qs_eligibility')
            ->groupBy('qs_eligibility')
            ->limit(10)
            ->get();

        return response()->json([
            'total_candidates' => $totalCandidates,
            'eligibility_breakdown' => $eligibilityBreakdown
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->input('type');
        $fileName = 'report_' . $type . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($type, $request) {
            $file = fopen('php://output', 'w');
            
            // Logic to fetch data and write to CSV based on type
            // Reuse logic from getData but flattened
            
            // Example for Financial
            if ($type === 'financial_summary') {
                fputcsv($file, ['Position', 'Type', 'Monthly Salary', 'Status']);
                $vacancies = JobVacancy::all();
                foreach ($vacancies as $v) {
                    fputcsv($file, [$v->position_title, $v->vacancy_type, $v->monthly_salary, $v->status]);
                }
            }
            // Example for Activity
            elseif ($type === 'user_activity') {
                fputcsv($file, ['Date', 'User', 'Description', 'Event']);
                $logs = Activity::with('causer')->limit(1000)->latest()->get();
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->created_at, 
                        $log->causer ? $log->causer->name : 'System', 
                        $log->description,
                        $log->event
                    ]);
                }
            }
             // Example for Recruitment
             elseif ($type === 'recruitment_performance') {
                fputcsv($file, ['Vacancy ID', 'Position', 'Applicants Count', 'Status']);
                $vacancies = JobVacancy::withCount('applications')->get();
                foreach ($vacancies as $v) {
                    fputcsv($file, [
                        $v->vacancy_id,
                        $v->position_title,
                        $v->applications_count,
                        $v->status
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
