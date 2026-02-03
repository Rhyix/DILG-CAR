<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use App\Models\Admin;
use Illuminate\Http\Request;
use Carbon\Carbon;

class activityLogController extends Controller
{
    public function view(Request $request)
    {
        // Get admin names
        $adminNames = Activity::where('causer_type', Admin::class)
            ->whereNotNull('causer_id')
            ->with('causer')
            ->get()
            ->filter(fn($act) => $act->causer && $act->causer->name) // skip missing admins
            ->pluck('causer.name')
            ->unique()
            ->sort()
            ->values();


        // Get sections from properties->section instead of log_name
        $sections = Activity::where('causer_type', Admin::class)
            ->whereNotNull('properties->section')
            ->get()
            ->pluck('properties.section')
            ->unique()
            ->sort()
            ->values();

        // Initial logs shown on load (descending by default)
        $activities = Activity::where('causer_type', Admin::class)
            ->whereNotIn('event', ['login', 'view'])
            ->orderBy('created_at', 'desc')
            ->with('causer', 'subject')
            ->get();

        

        return view('admin.admin_activity_log', compact('activities', 'adminNames', 'sections'));
    }

    public function fetch(Request $request)
    {
        $query = Activity::query()
            ->where('causer_type', Admin::class)
            ->whereNotIn('event', ['login', 'view'])
            ->with('causer', 'subject');

        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%$search%");
        }

        if ($adminName = $request->input('admin_name')) {
            $query->whereHas('causer', function ($q) use ($adminName) {
                $q->where('name', 'like', "%$adminName%");
            });
        }

        if ($section = $request->input('section')) {
            $query->where('properties->section', $section);
        }

        if ($range = $request->input('date_range')) {
            $dates = explode(' to ', $range);
            if (count($dates) === 2) {
                [$start, $end] = $dates;
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay()
                ]);
            }
        }

        // Sorting order: default to DESC
        $sort = $request->input('sort', 'desc');
        $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->get();

        $logs = $activities->map(function ($activity) {
            $target = 'N/A';
            if ($activity->subject) {
                $target = $activity->subject->name ??
                    $activity->subject->position_title ??
                    $activity->subject->title ??
                    'ID: ' . $activity->subject->id ??
                    'N/A';
            }

            if ($target === 'N/A' && isset($activity->properties['position_title'])) {
                $target = $activity->properties['position_title'];
            }

            return [
                'id' => $activity->id,
                'timestamp' => $activity->created_at->format('Y-m-d H:i:s'),
                'admin_name' => optional($activity->causer)->name ?? 'N/A',
                'section' => $activity->properties['section'] ?? 'N/A',
                'description' => $activity->description ?? 'N/A',
                'target' => $target,
                'vacancy_id' => $activity->properties['vacancy_id'] ?? null,
                'subject' => $activity->subject ? $activity->subject : 'N/A',
            ];
        });

        //info($logs);

        return response()->json($logs);
    }
}
