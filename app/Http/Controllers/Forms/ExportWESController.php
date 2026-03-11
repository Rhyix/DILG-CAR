<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Models\PersonalInformation;
use App\Models\WorkExperience;
use App\Models\WorkExpSheet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ExportWESController extends Controller
{
    public function exportWES(Request $request)
    {
        $user = Auth::user();
        $prepared = $this->prepareWesData($user->id, $user);
        $fullName = $prepared['full_name'];
        $experiences = $prepared['experiences'];

        $pdf = $this->buildWesPdf($fullName, $experiences);

        $timestamp = now()->format('Ymd_His');
        $filename = "WorkExperienceSheet_{$timestamp}.pdf";

        $forceInline = $request->boolean('preview') || $request->boolean('print');
        if ($request->boolean('download')) {
            $forceInline = false;
        }

        activity()
            ->causedBy($user)
            ->event('export')
            ->withProperties([
                'exported_file' => $filename,
                'entries_count' => $experiences->count(),
                'section' => 'Export',
                'format' => 'pdf',
            ])
            ->log('Exported Work Experience Sheet.');

        $content = $pdf->Output('S');

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($forceInline ? 'inline' : 'attachment') . '; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function previewWES()
    {
        return view('pds.wes_preview');
    }

    private function prepareWesData(int $userId, $user): array
    {
        $personalInfo = PersonalInformation::where('user_id', $userId)->first();
        $firstName = $personalInfo->first_name ?? ($user->first_name ?? '');
        $middleName = $personalInfo->middle_name ?? ($user->middle_name ?? '');
        $surname = $personalInfo->surname ?? ($user->last_name ?? '');
        $extension = $personalInfo->name_extension ?? ($user->name_extension ?? '');

        $middleInitial = $middleName ? strtoupper(mb_substr($middleName, 0, 1)) . '.' : '';
        $fullName = strtoupper(trim($firstName . ' ' . $middleInitial . ' ' . $surname));
        if (!empty($extension)) {
            $fullName .= ', ' . strtoupper($extension);
        }
        if (trim($fullName) === '') {
            $fullName = strtoupper($user->name ?? 'N/A');
        }

        $experiences = WorkExpSheet::where('user_id', $userId)
            ->where('isDisplayed', true)
            ->orderByDesc('start_date')
            ->get();

        if ($experiences->isEmpty()) {
            $experiences = WorkExperience::where('user_id', $userId)
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

        return [
            'full_name' => $fullName,
            'experiences' => $experiences,
        ];
    }

    private function buildWesPdf(string $fullName, Collection $experiences): \FPDF
    {
        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(0, 5, $this->toPdfText('Attachment to CS Form No. 212'), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 8, $this->toPdfText('WORK EXPERIENCE SHEET'), 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 6, $this->toPdfText('Name:'), 0, 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(120, 6, $this->toPdfText($fullName), 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(12, 6, $this->toPdfText('Date:'), 0, 0);
        $pdf->Cell(0, 6, $this->toPdfText(now()->format('F d, Y')), 0, 1);
        $pdf->Ln(2);
        $pdf->Line(12, $pdf->GetY(), 198, $pdf->GetY());
        $pdf->Ln(4);

        foreach ($experiences as $index => $exp) {
            $this->ensurePdfSpace($pdf, 42);
            $entryNo = $index + 1;
            $from = $this->formatMonthYear($exp->start_date);
            $to = $exp->end_date ? $this->formatMonthYear($exp->end_date) : 'Present';
            $duration = trim(($from !== '' ? $from : 'N/A') . ' to ' . ($to !== '' ? $to : 'N/A'));

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(0, 7, $this->toPdfText("Entry {$entryNo}"), 0, 1);
            $pdf->SetFont('Arial', '', 10);

            $this->pdfLabelValue($pdf, 'Duration', $duration);
            $this->pdfLabelValue($pdf, 'Position', (string) ($exp->position ?? 'N/A'));
            $this->pdfLabelValue($pdf, 'Name of Office/Unit', (string) ($exp->office ?? 'N/A'));
            $this->pdfLabelValue($pdf, 'Immediate Supervisor', (string) ($exp->supervisor ?? 'N/A'));
            $this->pdfLabelValue($pdf, 'Agency/Organization and Location', (string) ($exp->agency ?? 'N/A'));

            $this->pdfListBlock($pdf, 'Accomplishments and Contributions', $this->listItemsForPreview($exp->accomplishments ?? []));
            $this->pdfListBlock($pdf, 'Summary of Actual Duties', $this->listItemsForPreview($exp->duties ?? []));

            $pdf->Ln(2);
            $pdf->SetDrawColor(220, 220, 220);
            $pdf->Line(12, $pdf->GetY(), 198, $pdf->GetY());
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->Ln(3);
        }

        $this->ensurePdfSpace($pdf, 24);
        $pdf->Ln(6);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(120, 6, '', 0, 0);
        $pdf->Cell(64, 6, '_______________________________', 0, 1, 'C');
        $pdf->Cell(120, 5, '', 0, 0);
        $pdf->Cell(64, 5, $this->toPdfText('(Signature over Printed Name of Employee/Applicant)'), 0, 1, 'C');
        $pdf->Ln(6);
        $pdf->Cell(0, 6, $this->toPdfText('Date: ____________________'), 0, 1, 'R');

        return $pdf;
    }

    private function ensurePdfSpace(\FPDF $pdf, float $heightNeeded): void
    {
        if ($pdf->GetY() + $heightNeeded <= 285) {
            return;
        }

        $pdf->AddPage();
    }

    private function pdfLabelValue(\FPDF $pdf, string $label, string $value): void
    {
        $label = rtrim($label, ':') . ':';
        $safeValue = trim($value) !== '' ? trim($value) : 'N/A';

        $pdf->SetFont('Arial', 'B', 9.5);
        $pdf->Cell(56, 6, $this->toPdfText($label), 0, 0);
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->MultiCell(130, 6, $this->toPdfText($safeValue), 0, 'L');
    }

    private function pdfListBlock(\FPDF $pdf, string $title, array $items): void
    {
        $pdf->SetFont('Arial', 'B', 9.5);
        $pdf->Cell(0, 6, $this->toPdfText($title . ':'), 0, 1);
        $pdf->SetFont('Arial', '', 9.5);

        foreach ($items as $item) {
            $pdf->Cell(4, 6, '-', 0, 0);
            $pdf->MultiCell(178, 6, $this->toPdfText($item), 0, 'L');
        }
    }

    private function listItemsForPreview($value): array
    {
        if (empty($value)) {
            return ['N/A'];
        }

        $items = is_string($value) ? explode('|', $value) : (array) $value;
        $items = array_values(array_filter(array_map(function ($item) {
            return trim((string) $item);
        }, $items), function ($item) {
            return $item !== '';
        }));

        return empty($items) ? ['N/A'] : $items;
    }

    private function formatMonthYear($date): string
    {
        try {
            return Carbon::parse($date)->format('M Y');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function toPdfText(string $text): string
    {
        $cleaned = strtr($text, [
            '’' => "'",
            '‘' => "'",
            '“' => '"',
            '”' => '"',
            '–' => '-',
            '—' => '-',
            '•' => '-',
            '…' => '...',
            "\u{00A0}" => ' ',
        ]);

        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $cleaned);

        return $converted === false ? utf8_decode($cleaned) : $converted;
    }
}
