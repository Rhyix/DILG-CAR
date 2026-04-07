<?php

namespace App\Http\Controllers;

use App\Models\CoursePreset;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class CoursePresetController extends Controller
{
    private const DEFAULT_COURSES = [
        ['code' => 'BS_ACCOUNTANCY', 'name' => 'BS Accountancy'],
        ['code' => 'BS_INFORMATION_TECHNOLOGY', 'name' => 'BS Information Technology'],
        ['code' => 'BS_COMPUTER_SCIENCE', 'name' => 'BS Computer Science'],
        ['code' => 'BS_INFORMATION_SYSTEMS', 'name' => 'BS Information Systems'],
        ['code' => 'B_PUBLIC_ADMIN', 'name' => 'Bachelor of Public Administration'],
        ['code' => 'BS_PSYCHOLOGY', 'name' => 'BS Psychology'],
    ];

    private function canManageCourses(): bool
    {
        $role = Auth::guard('admin')->user()->role ?? null;
        return in_array($role, ['superadmin', 'admin'], true);
    }

    private function hasCoursesTable(): bool
    {
        return Schema::hasTable('course_presets');
    }

    private function normalizeCode(string $value): string
    {
        $normalized = strtoupper(trim($value));
        $normalized = preg_replace('/[^A-Z0-9]+/', '_', $normalized) ?: '';
        $normalized = trim($normalized, '_');

        if ($normalized === '') {
            return 'COURSE';
        }

        return $normalized;
    }

    private function nextUniqueCode(string $baseCode, ?int $ignoreId = null): string
    {
        $code = $this->normalizeCode($baseCode);
        $counter = 2;

        while (true) {
            $query = CoursePreset::query()->where('course_code', $code);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            if (!$query->exists()) {
                return $code;
            }

            $code = $this->normalizeCode($baseCode) . '_' . $counter;
            $counter++;
        }
    }

    private function sortedCourses(Collection $items): Collection
    {
        return $items
            ->sortBy(fn($item) => strtolower(trim((string) ($item->course_name ?? $item['name'] ?? ''))))
            ->values();
    }

    public function index()
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        $courses = $this->hasCoursesTable()
            ? $this->sortedCourses(CoursePreset::query()->get())
            : collect();

        return view('admin.courses.index', compact('courses'));
    }

    public function store(Request $request)
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        if (!$this->hasCoursesTable()) {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['course_presets' => 'Courses table is missing. Run migrations first.']);
        }

        $validated = $request->validate([
            'course_name' => 'required|string|max:255|unique:course_presets,course_name',
        ]);

        $courseName = trim((string) ($validated['course_name'] ?? ''));
        $courseCode = $this->nextUniqueCode($courseName);

        CoursePreset::query()->create([
            'course_code' => $courseCode,
            'course_name' => $courseName,
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Course added successfully.');
    }

    public function update(Request $request, int $id)
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        if (!$this->hasCoursesTable()) {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['course_presets' => 'Courses table is missing. Run migrations first.']);
        }

        $course = CoursePreset::query()->findOrFail($id);

        $validated = $request->validate([
            'course_name' => 'required|string|max:255|unique:course_presets,course_name,' . $course->id,
        ]);

        $course->update([
            'course_name' => trim((string) ($validated['course_name'] ?? '')),
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(int $id)
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        if (!$this->hasCoursesTable()) {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['course_presets' => 'Courses table is missing. Run migrations first.']);
        }

        $course = CoursePreset::query()->findOrFail($id);
        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    public function listJson()
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }

        if (!$this->hasCoursesTable()) {
            return response()->json([
                'success' => true,
                'data' => self::DEFAULT_COURSES,
            ]);
        }

        $data = $this->sortedCourses(
            CoursePreset::query()->get(['id', 'course_code', 'course_name'])
        )->map(function ($row) {
            return [
                'id' => (int) ($row->id ?? 0),
                'code' => (string) ($row->course_code ?? ''),
                'name' => (string) ($row->course_name ?? ''),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}

