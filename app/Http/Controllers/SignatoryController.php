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
        $search = trim((string) $request->query('search', ''));
        $office = trim((string) $request->query('office', ''));
        $sort = trim((string) $request->query('sort', 'latest'));
        $allowedSort = ['latest', 'oldest', 'name_asc', 'name_desc'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'latest';
        }

        $query = Signatory::query();
        if ($search !== '') {
            $term = $search;
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('middle_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('designation', 'like', "%{$term}%")
                    ->orWhere('office', 'like', "%{$term}%")
                    ->orWhere('office_address', 'like', "%{$term}%");
            });
        }

        if ($office !== '') {
            $query->where('office', $office);
        }

        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name_asc':
                $query->orderBy('last_name')->orderBy('first_name')->orderBy('middle_name');
                break;
            case 'name_desc':
                $query->orderBy('last_name', 'desc')->orderBy('first_name', 'desc')->orderBy('middle_name', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $offices = Signatory::query()
            ->whereNotNull('office')
            ->where('office', '!=', '')
            ->distinct()
            ->orderBy('office')
            ->pluck('office');

        $signatories = $query->get();

        return view('admin.signatories.index', compact('signatories', 'search', 'office', 'sort', 'offices'));
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
