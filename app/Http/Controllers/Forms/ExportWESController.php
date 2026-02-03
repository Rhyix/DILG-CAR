<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use App\Models\WorkExpSheet;
use App\Models\PersonalInformation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class ExportWESController extends Controller
{
    public function exportWES()
    {
        $user = Auth::user();

        // Get full name for signature
        $personalInfo = PersonalInformation::where('user_id', $user->id)->first();
        $firstName = $personalInfo->first_name ?? '';
        $middleName = $personalInfo->middle_name ?? '';
        $surname = $personalInfo->surname ?? '';
        $extension = $personalInfo->name_extension ?? '';

        // Get middle initial with dot (e.g., 'J.')
        $middleInitial = $middleName ? strtoupper(mb_substr($middleName, 0, 1)) . '.' : '';

        // Compose full name (uppercase)
        $fullName = strtoupper(trim($firstName . ' ' . $middleInitial . ' ' . $surname));
        if (!empty($extension)) {
            $fullName .= ', ' . strtoupper($extension);
        }

        // Load Word template
        $templatePath = resource_path('templates/work_experience_template.docx');
        $templateProcessor = new TemplateProcessor($templatePath);

        // Insert into placeholder ${name} in the Word template
        $templateProcessor->setValue('name', $fullName);
        $templateProcessor->setValue('date', now()->format('F d, Y'));


        // Work experience entries ordered most recent first
        $experiences = WorkExpSheet::where('user_id', $user->id)
            ->where('isDisplayed', true)
            ->orderByDesc('start_date')
            ->get();

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

        // Save and return the document
        $outputPath = storage_path("app/public/WorkExperienceSheet.docx");
        $templateProcessor->saveAs($outputPath);

        activity()
            ->causedBy($user)
            ->event('export')
            ->withProperties([
                'exported_file' => 'WorkExperienceSheet.docx',
                'entries_count' => $experiences->count(),
                'section' => 'Export'
            ])
            ->log('Exported Work Experience Sheet.');

        return response()->download($outputPath)->deleteFileAfterSend(true);
        return redirect()->back()->with('success', 'Saved')->with('after_action', $request->input('after_action'));

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
