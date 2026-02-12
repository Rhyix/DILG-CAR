<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Signatory;

class SignatoryController extends Controller
{
    /**
     * Display the signatories list.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        
        if ($search) {
            $signatories = Signatory::where('first_name', 'like', "%{$search}%")
                ->orWhere('middle_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('designation', 'like', "%{$search}%")
                ->orWhere('office', 'like', "%{$search}%")
                ->orWhere('office_address', 'like', "%{$search}%")
                ->get();
        } else {
            $signatories = Signatory::all();
        }
        
        return view('admin.signatories.index', compact('signatories', 'search'));
    }

    /**
     * Show the form for creating a new signatory.
     */
    public function create()
    {
        return view('admin.signatories.create');
    }

    /**
     * Store a newly created signatory in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'office_address' => 'required|string|max:500',
        ]);

        Signatory::create($validated);

        return redirect()->route('signatories.index')
            ->with('success', 'Signatory created successfully.');
    }

    /**
     * Display the specified signatory.
     */
    public function show($id)
    {
        $signatory = Signatory::findOrFail($id);
        return view('admin.signatories.show', compact('signatory'));
    }

    /**
     * Show the form for editing the specified signatory.
     */
    public function edit($id)
    {
        $signatory = Signatory::findOrFail($id);
        return view('admin.signatories.edit', compact('signatory'));
    }

    /**
     * Update the specified signatory in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'office_address' => 'required|string|max:500',
        ]);

        $signatory = Signatory::findOrFail($id);
        $signatory->update($validated);

        return redirect()->route('signatories.index')
            ->with('success', 'Signatory updated successfully.');
    }

    /**
     * Remove the specified signatory from storage.
     */
    public function destroy($id)
    {
        $signatory = Signatory::findOrFail($id);
        $signatory->delete();

        return redirect()->route('signatories.index')
            ->with('success', 'Signatory removed successfully.');
    }
}
