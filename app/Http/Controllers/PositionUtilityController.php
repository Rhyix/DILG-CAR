<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PositionUtilityController extends Controller
{
    /**
     * Build the base query used by the Positions utility and dropdown consumers.
     */
    private function positionsBaseQuery()
    {
        return JobVacancy::query()
            ->select([
                'id',
                'vacancy_id',
                'position_title',
                'vacancy_type',
                'status',
                'pcn_no',
                'plantilla_item_no',
                'closing_date',
                'salary_grade',
                'monthly_salary',
                'place_of_assignment',
                'qualification_education',
                'qualification_training',
                'qualification_experience',
                'qualification_eligibility',
                'competencies',
                'expected_output',
                'scope_of_work',
                'duration_of_work',
                'to_person',
                'to_position',
                'to_office',
                'to_office_address',
                'updated_at',
            ]);
    }

    public function index(Request $request)
    {
        $role = Auth::guard('admin')->user()->role ?? null;
        if (!in_array($role, ['superadmin', 'admin'], true)) {
            abort(403);
        }

        $search = trim((string) $request->query('search', ''));

        $query = $this->positionsBaseQuery()
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('vacancy_id', 'like', '%' . $search . '%')
                    ->orWhere('position_title', 'like', '%' . $search . '%')
                    ->orWhere('vacancy_type', 'like', '%' . $search . '%')
                    ->orWhere('place_of_assignment', 'like', '%' . $search . '%');
            });
        }

        /** @var Collection<int, JobVacancy> $positions */
        $positions = $query->get()
            ->unique(function ($row) {
                $title = strtolower(trim((string) $row->position_title));
                $type = strtoupper(trim((string) $row->vacancy_type));
                return $title . '|' . $type;
            })
            ->values();

        return view('admin.positions.index', compact('positions', 'search'));
    }

    public function listJson(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }

        $type = strtoupper(trim((string) $request->query('vacancy_type', '')));
        $allowedTypes = ['COS', 'PLANTILLA'];
        $filterType = in_array($type, $allowedTypes, true) ? $type : null;

        /** @var Collection<int, JobVacancy> $positions */
        $query = $this->positionsBaseQuery();
        if ($filterType) {
            $query->whereRaw('UPPER(TRIM(COALESCE(vacancy_type, ""))) = ?', [$filterType]);
        }

        $positions = $query
            ->orderBy('position_title')
            ->orderBy('vacancy_type')
            ->orderByDesc('updated_at')
            ->get()
            ->unique(function ($row) {
                $title = strtolower(trim((string) $row->position_title));
                $type = strtoupper(trim((string) $row->vacancy_type));
                return $title . '|' . $type;
            })
            ->values();

        $data = $positions->map(function ($row) {
            return [
                'vacancy_id' => $row->vacancy_id,
                'position_title' => $row->position_title,
                'vacancy_type' => strtoupper((string) $row->vacancy_type),
                'salary_grade' => $row->salary_grade,
                'monthly_salary' => $row->monthly_salary,
                'pcn_no' => $row->pcn_no,
                'plantilla_item_no' => $row->plantilla_item_no,
                'closing_date' => !empty($row->closing_date)
                    ? \Carbon\Carbon::parse($row->closing_date)->format('Y-m-d')
                    : '',
                'place_of_assignment' => $row->place_of_assignment,
                'qualification_education' => $row->qualification_education,
                'qualification_training' => $row->qualification_training,
                'qualification_experience' => $row->qualification_experience,
                'qualification_eligibility' => $row->qualification_eligibility,
                'competencies' => $row->competencies,
                'expected_output' => $row->expected_output,
                'scope_of_work' => $row->scope_of_work,
                'duration_of_work' => $row->duration_of_work,
                'to_person' => $row->to_person,
                'to_position' => $row->to_position,
                'to_office' => $row->to_office,
                'to_office_address' => $row->to_office_address,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
