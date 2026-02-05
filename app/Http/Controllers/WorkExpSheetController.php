<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WorkExpSheet;
use Illuminate\Support\Facades\Auth;
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

        $existingEntries = WorkExpSheet::where('user_id', $user_id)->count() > 0;

        WorkExpSheet::where('user_id', $user_id)->delete();

        if (empty($request->end_date)) {
            $entry['end_date'] = null;
        }

        $validated = $request->validate([
            'entries' => 'required|array|min:1',
            'entries.*.start_date' => 'required|date',
            'entries.*.end_date' => 'nullable|date|after_or_equal:entries.*.start_date',
            'entries.*.position' => 'required|string|max:255',
            'entries.*.office' => 'required|string|max:255',
            'entries.*.supervisor' => 'nullable|string|max:255',
            'entries.*.agency' => 'required|string|max:255',
            'entries.*.accomplishments' => 'nullable|array',
            'entries.*.accomplishments.*' => 'nullable|string|max:1000',
            'entries.*.duties' => 'nullable|array',
            'entries.*.duties.*' => 'nullable|string|max:1000',
            'entries.*.isDisplayed' => 'boolean',
        ]);

        foreach($validated['entries'] as $work){
            WorkExpSheet::create([
                'user_id' => $user_id,
                'start_date' => $work['start_date'],
                'end_date' => $work['end_date'],
                'position' => $work['position'],
                'office' => $work['office'],
                'supervisor' => $work['supervisor'],
                'agency' => $work['agency'],
                'accomplishments' => $work['accomplishments'],
                'duties' => $work['duties'],
                'isDisplayed' => $work['isDisplayed'],
            ]);
        }

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

        return redirect()->back()
            ->with('success', 'Work Experience Sheet Saved!')
            ->with('after_action', $request->input('after_action'));
        }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $workEntries = WorkExpSheet::where('user_id', Auth::id())->get();

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
