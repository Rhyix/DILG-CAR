<?php

namespace App\Http\Controllers;

use App\Models\EligibilityPreset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class EligibilityPresetController extends Controller
{
    private const DEFAULT_PRESETS = [
        ['name' => 'CSC Professional Eligibility', 'legal_basis' => 'CSR 2017/PD 807', 'level' => 'Second Level'],
        ['name' => 'Bar/Board Eligibility', 'legal_basis' => 'RA 1080', 'level' => 'Second Level'],
        ['name' => 'Honor Graduate Eligibility', 'legal_basis' => 'PD 907', 'level' => 'Second Level'],
        ['name' => 'Subprofessional (Sub-Prof) Eligibility', 'legal_basis' => 'CSR 2017/PD 807', 'level' => 'First Level'],
        ['name' => 'Barangay Health Worker Eligibility', 'legal_basis' => 'RA 7883', 'level' => 'First Level'],
        ['name' => 'Barangay Nutrition Scholar Eligibility', 'legal_basis' => 'PD 1569', 'level' => 'First Level'],
        ['name' => 'Barangay Official Eligibility', 'legal_basis' => 'RA 7160', 'level' => 'First Level'],
        ['name' => 'Sanggunian Member Eligibility', 'legal_basis' => 'RA 10156', 'level' => 'First Level'],
        ['name' => 'Skills Eligibility-Category II', 'legal_basis' => 'CSC MC 11, s.1996', 'level' => 'First Level'],
        ['name' => 'Electronic Data Processing Specialist Eligibility', 'legal_basis' => 'CSC Res. 90-083', 'level' => 'Second Level'],
        ['name' => 'Foreign School Honor Graduate Eligibility', 'legal_basis' => 'CSC Res. 1302714', 'level' => 'Second Level'],
        ['name' => 'Scientific and Technological Specialist Eligibility', 'legal_basis' => 'PD 997', 'level' => 'Second Level'],
    ];

    private function canManageEligibilities(): bool
    {
        $role = Auth::guard('admin')->user()->role ?? null;
        return in_array($role, ['superadmin', 'admin'], true);
    }

    private function hasPresetsTable(): bool
    {
        return Schema::hasTable('eligibility_presets');
    }

    public function index()
    {
        if (!$this->canManageEligibilities()) {
            abort(403);
        }

        $eligibilities = $this->hasPresetsTable()
            ? EligibilityPreset::query()->orderBy('name')->get()
            : collect();

        return view('admin.eligibilities.index', compact('eligibilities'));
    }

    public function store(Request $request)
    {
        if (!$this->canManageEligibilities()) {
            abort(403);
        }
        if (!$this->hasPresetsTable()) {
            return redirect()
                ->route('admin.eligibilities.index')
                ->withErrors(['eligibility_presets' => 'Eligibility presets table is missing. Run migrations first.']);
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
        if (!$this->hasPresetsTable()) {
            return redirect()
                ->route('admin.eligibilities.index')
                ->withErrors(['eligibility_presets' => 'Eligibility presets table is missing. Run migrations first.']);
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
        if (!$this->hasPresetsTable()) {
            return redirect()
                ->route('admin.eligibilities.index')
                ->withErrors(['eligibility_presets' => 'Eligibility presets table is missing. Run migrations first.']);
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

        if (!$this->hasPresetsTable()) {
            return response()->json([
                'success' => true,
                'data' => collect(self::DEFAULT_PRESETS)->values(),
            ]);
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
