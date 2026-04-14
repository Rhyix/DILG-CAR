<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WorkExpSheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;



class WorkExpSheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //($request->all());
        $user_id = Auth::id();

        $existingEntries = WorkExpSheet::where('user_id', $user_id)->exists();

        $validated = $request->validate([
            'entries' => 'required|array|min:1',
            'entries.*.start_date' => 'required|date',
            'entries.*.end_date' => 'nullable|date|after_or_equal:entries.*.start_date',
            'entries.*.present' => 'nullable|boolean',
            'entries.*.position' => 'required|string|max:255',
            'entries.*.office' => 'required|string|max:255',
            'entries.*.supervisor' => 'required|string|max:255',
            'entries.*.agency' => 'required|string|max:255',
            'entries.*.accomplishments' => 'nullable|array',
            'entries.*.accomplishments.*' => 'nullable|string|max:1000',
            'entries.*.duties' => 'nullable|array',
            'entries.*.duties.*' => 'nullable|string|max:1000',
            'entries.*.isDisplayed' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $user_id) {
            WorkExpSheet::where('user_id', $user_id)->delete();

            foreach ($validated['entries'] as $work) {
                $isPresent = (bool) ($work['present'] ?? false);
                $endDate = $isPresent ? null : ($work['end_date'] ?? null);

                WorkExpSheet::create([
                    'user_id' => $user_id,
                    'start_date' => $work['start_date'],
                    'end_date' => $endDate,
                    'position' => trim((string) ($work['position'] ?? '')),
                    'office' => trim((string) ($work['office'] ?? '')),
                    'supervisor' => trim((string) ($work['supervisor'] ?? '')),
                    'agency' => trim((string) ($work['agency'] ?? '')),
                    'accomplishments' => $work['accomplishments'] ?? [],
                    'duties' => $work['duties'] ?? [],
                    'isDisplayed' => $work['isDisplayed'] ?? true,
                ]);
            }
        });

        // Explicit save should supersede any transient autosave draft for WES.
        $request->session()->forget('form.wes');

        $action = $existingEntries ? 'Update' : 'Create';

        activity()
            ->causedBy(Auth::user())
            ->event($action)
            ->withProperties([
                'entries_count' => count($validated['entries']),
                'action_type' => $action,
                'section' => 'Work Experience Sheet',
            ])
            ->log($action . 'd Work Experience Sheet');

        if ($request->boolean('simple_mode') || $request->input('after_action') === 'dashboard') {
            return redirect()
                ->route('dashboard_user')
                ->with('success', 'Work Experience Sheet Saved!');
        }

        if ($request->input('after_action') === 'next') {
            return redirect()->route('display_c5');
        }

        return redirect()->back()
            ->with('success', 'Work Experience Sheet Saved!')
            ->with('after_action', $request->input('after_action'));
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $sessionEntries = session('form.wes.entries', []);
        if (is_array($sessionEntries) && count($sessionEntries) > 0) {
            $workEntries = collect($sessionEntries)->map(function ($entry) {
                return [
                    'start_date' => $entry['start_date'] ?? null,
                    'end_date' => $entry['end_date'] ?? null,
                    'position' => $entry['position'] ?? '',
                    'office' => $entry['office'] ?? '',
                    'supervisor' => $entry['supervisor'] ?? '',
                    'agency' => $entry['agency'] ?? '',
                    'accomplishments' => is_array($entry['accomplishments'] ?? null)
                        ? $entry['accomplishments']
                        : [''],
                    'duties' => is_array($entry['duties'] ?? null)
                        ? $entry['duties']
                        : [''],
                    'isDisplayed' => (bool) ($entry['isDisplayed'] ?? true),
                ];
            });
        } else {
            $workEntries = WorkExpSheet::where('user_id', Auth::id())->get();
        }

        //info($workEntries);
        return view('pds.wes', ['workEntries' => $workEntries]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
