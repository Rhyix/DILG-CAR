<?php

namespace App\Http\Controllers;

use App\Models\EligibilityPreset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EligibilityPresetController extends Controller
{
    private function canManageEligibilities(): bool
    {
        $role = Auth::guard('admin')->user()->role ?? null;
        return in_array($role, ['superadmin', 'admin'], true);
    }

    public function index()
    {
        if (!$this->canManageEligibilities()) {
            abort(403);
        }

        $eligibilities = EligibilityPreset::query()
            ->orderBy('name')
            ->get();

        return view('admin.eligibilities.index', compact('eligibilities'));
    }

    public function store(Request $request)
    {
        if (!$this->canManageEligibilities()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:eligibility_presets,name',
            'legal_basis' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
        ]);

        EligibilityPreset::query()->create([
            'name' => trim((string) ($validated['name'] ?? '')),
            'legal_basis' => trim((string) ($validated['legal_basis'] ?? '')),
            'level' => trim((string) ($validated['level'] ?? '')),
        ]);

        return redirect()
            ->route('admin.eligibilities.index')
            ->with('success', 'Eligibility added successfully.');
    }

    public function update(Request $request, int $id)
    {
        if (!$this->canManageEligibilities()) {
            abort(403);
        }

        $eligibility = EligibilityPreset::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:eligibility_presets,name,' . $eligibility->id,
            'legal_basis' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
        ]);

        $eligibility->update([
            'name' => trim((string) ($validated['name'] ?? '')),
            'legal_basis' => trim((string) ($validated['legal_basis'] ?? '')),
            'level' => trim((string) ($validated['level'] ?? '')),
        ]);

        return redirect()
            ->route('admin.eligibilities.index')
            ->with('success', 'Eligibility updated successfully.');
    }

    public function destroy(int $id)
    {
        if (!$this->canManageEligibilities()) {
            abort(403);
        }

        $eligibility = EligibilityPreset::query()->findOrFail($id);
        $eligibility->delete();

        return redirect()
            ->route('admin.eligibilities.index')
            ->with('success', 'Eligibility deleted successfully.');
    }

    public function listJson()
    {
        if (!Auth::guard('admin')->check() && !Auth::guard('web')->check()) {
            abort(403);
        }

        $data = EligibilityPreset::query()
            ->orderBy('name')
            ->get(['id', 'name', 'legal_basis', 'level']);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
