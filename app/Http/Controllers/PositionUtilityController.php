<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PositionUtilityController extends Controller
{
    public function index(Request $request)
    {
        $role = Auth::guard('admin')->user()->role ?? null;
        if (!in_array($role, ['superadmin', 'admin'], true)) {
            abort(403);
        }

        $search = trim((string) $request->query('search', ''));

        $query = JobVacancy::query()
            ->select([
                'vacancy_id',
                'position_title',
                'vacancy_type',
                'status',
                'salary_grade',
                'monthly_salary',
                'place_of_assignment',
                'updated_at',
            ])
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
}
