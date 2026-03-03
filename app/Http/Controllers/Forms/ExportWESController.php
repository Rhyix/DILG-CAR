<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use App\Models\WorkExpSheet;
use App\Models\WorkExperience;
use App\Models\PersonalInformation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;


class ExportWESController extends Controller
{
    public function exportWES(Request $request)
    {
        $user = Auth::user();

        // Get full name for signature
        $personalInfo = PersonalInformation::where('user_id', $user->id)->first();
        $firstName = $personalInfo->first_name ?? ($user->first_name ?? '');
        $middleName = $personalInfo->middle_name ?? ($user->middle_name ?? ($user->middle_initial ?? ''));
        $surname = $personalInfo->surname ?? ($user->last_name ?? '');
        $extension = $personalInfo->name_extension ?? ($user->name_extension ?? '');

        // Get middle initial with dot (e.g., 'J.')
        $middleInitial = $middleName ? strtoupper(mb_substr($middleName, 0, 1)) . '.' : '';

        // Compose full name (uppercase)
        $fullName = strtoupper(trim($firstName . ' ' . $middleInitial . ' ' . $surname));
        if (!empty($extension)) {
            $fullName .= ', ' . strtoupper($extension);
        }
        if (trim($fullName) === '') {
            $fullName = strtoupper($user->name ?? 'N/A');
        }

        // Load Word template
        $templatePath = public_path('templates/WES_Template.docx');
        $templateProcessor = new TemplateProcessor($templatePath);

        // Insert into placeholder ${name} in the Word template
        $templateProcessor->setValue('name', $fullName);
        $templateProcessor->setValue('date', now()->format('F d, Y'));


        // Work experience entries ordered most recent first
        $experiences = WorkExpSheet::where('user_id', $user->id)
            ->where('isDisplayed', true)
            ->orderByDesc('start_date')
            ->get();

        if ($experiences->isEmpty()) {
            $experiences = WorkExperience::where('user_id', $user->id)
                ->orderByDesc('work_exp_from')
                ->get()
                ->map(function ($row) {
                    return (object) [
                        'start_date' => $row->work_exp_from,
                        'end_date' => $row->work_exp_to,
                        'position' => $row->work_exp_position,
                        'office' => $row->work_exp_department,
                        'supervisor' => '',
                        'agency' => $row->work_exp_department,
                        'accomplishments' => ['None specified'],
                        'duties' => ['None specified'],
                    ];
                });
        }

        if ($experiences->isEmpty()) {
            $experiences = collect([
                (object) [
                    'start_date' => null,
                    'end_date' => null,
                    'position' => 'N/A',
                    'office' => 'N/A',
                    'supervisor' => 'N/A',
                    'agency' => 'N/A',
                    'accomplishments' => ['N/A'],
                    'duties' => ['N/A'],
                ],
            ]);
        }

        $templateProcessor->cloneBlock('experience', $experiences->count(), true, true);

        foreach ($experiences as $i => $exp) {
            $idx = $i + 1;
            $from = $this->formatMonthYear($exp->start_date);
            $to = $exp->end_date ? $this->formatMonthYear($exp->end_date) : 'Present';

            $templateProcessor->setValue("from#{$idx}", $from);
            $templateProcessor->setValue("to#{$idx}", $to);
            $templateProcessor->setValue("position#{$idx}", $exp->position ?? '');
            $templateProcessor->setValue("office#{$idx}", $exp->office ?? '');
            $templateProcessor->setValue("supervisor#{$idx}", $exp->supervisor ?? '');
            $templateProcessor->setValue("agency#{$idx}", $exp->agency ?? '');
            $templateProcessor->setValue("accomplishments#{$idx}", $this->formatList($exp->accomplishments));
            $templateProcessor->setValue("duties#{$idx}", $this->formatList($exp->duties));
        }

        // Save and return the document with timestamped filename
        $timestamp = now()->format('Ymd_His');
        $outputFilename = "WorkExperienceSheet_{$timestamp}.docx";
        $outputPath = storage_path("app/public/{$outputFilename}");
        $templateProcessor->saveAs($outputPath);

        activity()
            ->causedBy($user)
            ->event('export')
            ->withProperties([
                'exported_file' => $outputFilename,
                'entries_count' => $experiences->count(),
                'section' => 'Export'
            ])
            ->log('Exported Work Experience Sheet.');

        if ($request->boolean('preview')) {
            return response()->file($outputPath, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'inline; filename="' . $outputFilename . '"',
            ])->deleteFileAfterSend(true);
        }

        return response()->download($outputPath, $outputFilename)->deleteFileAfterSend(true);

    }

    private function formatList($value)
    {
        if (empty($value)) return '○ None';
        $items = is_string($value) ? explode('|', $value) : (array) $value;
        return '○ ' . implode("\n○ ", array_map('trim', $items));
    }

    private function formatMonthYear($date)
    {
        try {
            return Carbon::parse($date)->format('M Y');
        } catch (\Exception $e) {
            return '';
        }
    }
}
