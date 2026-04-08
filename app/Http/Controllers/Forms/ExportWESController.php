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
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;
use setasign\Fpdi\Fpdi;

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
        $responsiveDocxTemplatePath = public_path('templates/WES_Template.docx');
        if (file_exists($responsiveDocxTemplatePath)) {
            try {
                return $this->buildWesPdfFromResponsiveDocxTemplate($responsiveDocxTemplatePath, $fullName, $experiences);
            } catch (\Throwable $e) {
                Log::warning('Responsive WES DOCX template render failed; falling back to PDF-template overlay.', [
                    'error' => $e->getMessage(),
                    'template_docx' => $responsiveDocxTemplatePath,
                ]);
            }
        }

        $templatePdfPath = $this->resolveWesTemplatePdfPath();
        if ($templatePdfPath !== null) {
            try {
                return $this->buildWesPdfFromTemplate($templatePdfPath, $fullName, $experiences);
            } catch (\Throwable $e) {
                Log::warning('WES template-based export failed; falling back to legacy WES PDF renderer.', [
                    'error' => $e->getMessage(),
                    'template_pdf' => $templatePdfPath,
                ]);
            }
        }

        // Fallback: legacy in-code renderer (kept for resilience when template conversion is unavailable).
        return $this->buildWesPdfLegacy($fullName, $experiences);
    }

    private function buildWesPdfFromResponsiveDocxTemplate(
        string $templateDocxPath,
        string $fullName,
        Collection $experiences
    ): \FPDF {
        $entries = $experiences->values();
        if ($entries->isEmpty()) {
            $entries = collect([(object) [
                'start_date' => null,
                'end_date' => null,
                'position' => 'N/A',
                'office' => 'N/A',
                'supervisor' => 'N/A',
                'agency' => 'N/A',
                'accomplishments' => ['N/A'],
                'duties' => ['N/A'],
            ]]);
        }

        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $tempDir = storage_path('app/temp');
        @mkdir($tempDir, 0777, true);

        foreach ($entries->values() as $index => $exp) {
            $templateProcessor = new TemplateProcessor($templateDocxPath);
            // Keep placeholder empty and place the name via PDF overlay for precise centering on the underline.
            $templateProcessor->setValue('name', '');
            $templateProcessor->setValue('date', now()->format('F d, Y'));

            $from = $this->formatMonthYear($exp->start_date);
            $to = $exp->end_date ? $this->formatMonthYear($exp->end_date) : 'Present';

            $this->setTemplateValueOnce($templateProcessor, 'from', $from !== '' ? $from : 'N/A');
            $this->setTemplateValueOnce($templateProcessor, 'to', $to !== '' ? $to : 'N/A');
            $this->setTemplateValueOnce($templateProcessor, 'position', trim((string) ($exp->position ?? '')) ?: 'N/A');
            $this->setTemplateValueOnce($templateProcessor, 'office', trim((string) ($exp->office ?? '')) ?: 'N/A');
            $this->setTemplateValueOnce($templateProcessor, 'supervisor', trim((string) ($exp->supervisor ?? '')) ?: 'N/A');
            $this->setTemplateValueOnce($templateProcessor, 'agency', trim((string) ($exp->agency ?? '')) ?: 'N/A');
            $this->setTemplateValueOnce(
                $templateProcessor,
                'accomplishments',
                $this->formatTemplateMultilineList($exp->accomplishments ?? [])
            );
            $this->setTemplateValueOnce(
                $templateProcessor,
                'duties',
                $this->formatTemplateMultilineList($exp->duties ?? [])
            );

            // Keep one rendered experience block per generated page and drop marker rows.
            $templateProcessor->setValue('experience', '', 1);
            $templateProcessor->setValue('/experience', '', 1);

            $token = uniqid('wes_runtime_' . $index . '_', true);
            $tempDocxPath = $tempDir . DIRECTORY_SEPARATOR . $token . '.docx';
            $tempPdfPath = $tempDir . DIRECTORY_SEPARATOR . $token . '.pdf';

            $templateProcessor->saveAs($tempDocxPath);
            $converted = $this->convertDocxTemplateToPdf($tempDocxPath, $tempPdfPath);
            if (!$converted || !file_exists($tempPdfPath)) {
                @unlink($tempDocxPath);
                @unlink($tempPdfPath);
                throw new \RuntimeException('DOCX to PDF conversion failed for responsive WES template.');
            }

            $pageCount = $pdf->setSourceFile($tempPdfPath);
            for ($page = 1; $page <= $pageCount; $page++) {
                $templateId = $pdf->importPage($page);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
                $this->overlayWesSignatureName($pdf, $fullName);
            }

            @unlink($tempDocxPath);
            @unlink($tempPdfPath);
        }

        return $pdf;
    }

    private function buildWesPdfFromTemplate(string $templatePdfPath, string $fullName, Collection $experiences): \FPDF
    {
        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->setSourceFile($templatePdfPath);
        $templateId = $pdf->importPage(1);
        $templateSize = $pdf->getTemplateSize($templateId);

        $entries = $experiences->values();
        if ($entries->isEmpty()) {
            $entries = collect([(object) [
                'start_date' => null,
                'end_date' => null,
                'position' => 'N/A',
                'office' => 'N/A',
                'supervisor' => 'N/A',
                'agency' => 'N/A',
                'accomplishments' => ['N/A'],
                'duties' => ['N/A'],
            ]]);
        }

        // Template has two repeated entry blocks; render two entries per page.
        $chunked = $entries->chunk(2);
        foreach ($chunked as $chunk) {
            $pdf->AddPage($templateSize['orientation'], [$templateSize['width'], $templateSize['height']]);
            $pdf->useTemplate($templateId);

            foreach ($chunk->values() as $slotIndex => $exp) {
                // Tuned anchors for the converted CS Form No. 212 WES template.
                // Slot 0 = upper entry box, Slot 1 = lower entry box.
                $baseY = $slotIndex === 0 ? 69.2 : 140.8;
                $this->writeTemplateEntryOverlay($pdf, $exp, $baseY);
            }

            // Fill footer date line in template.
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(162.0, 281.2);
            $pdf->Cell(27, 4.5, $this->toPdfText(now()->format('m/d/Y')), 0, 0, 'C');
            $this->overlayWesSignatureName($pdf, $fullName);
        }

        return $pdf;
    }

    private function writeTemplateEntryOverlay(Fpdi $pdf, $exp, float $baseY): void
    {
        $durationFrom = $this->formatMonthYear($exp->start_date);
        $durationTo = $exp->end_date ? $this->formatMonthYear($exp->end_date) : 'Present';
        $duration = trim(($durationFrom !== '' ? $durationFrom : 'N/A') . ' to ' . ($durationTo !== '' ? $durationTo : 'N/A'));

        $position = trim((string) ($exp->position ?? '')) ?: 'N/A';
        $office = trim((string) ($exp->office ?? '')) ?: 'N/A';
        $supervisor = trim((string) ($exp->supervisor ?? '')) ?: 'N/A';
        $agency = trim((string) ($exp->agency ?? '')) ?: 'N/A';

        // Right-side values aligned to the template's fixed labels.
        $valueX = 94.0;
        $valueWidth = 92.0;
        $lineHeight = 8.35;

        $pdf->SetFont('Arial', '', 8.4);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY($valueX, $baseY);
        $pdf->Cell($valueWidth, 4.6, $this->toPdfText($duration), 0, 0, 'L');

        $pdf->SetXY($valueX, $baseY + $lineHeight);
        $pdf->Cell($valueWidth, 4.6, $this->toPdfText($position), 0, 0, 'L');

        $pdf->SetXY($valueX, $baseY + ($lineHeight * 2));
        $pdf->Cell($valueWidth, 4.6, $this->toPdfText($office), 0, 0, 'L');

        $pdf->SetXY($valueX, $baseY + ($lineHeight * 3));
        $pdf->Cell($valueWidth, 4.6, $this->toPdfText($supervisor), 0, 0, 'L');

        $pdf->SetXY($valueX, $baseY + ($lineHeight * 4));
        $pdf->Cell($valueWidth, 4.6, $this->toPdfText($agency), 0, 0, 'L');

        $accomplishments = array_slice($this->listItemsForPreview($exp->accomplishments ?? []), 0, 3);
        $duties = array_slice($this->listItemsForPreview($exp->duties ?? []), 0, 3);

        $bulletX = 61.5;
        $bulletLineHeight = 4.25;

        $accomplishmentY = $baseY + 40.6;
        foreach ($accomplishments as $index => $item) {
            $pdf->SetXY($bulletX, $accomplishmentY + ($index * $bulletLineHeight));
            $pdf->Cell(123, 4.0, $this->toPdfText('• ' . $item), 0, 0, 'L');
        }

        $dutyY = $baseY + 55.9;
        foreach ($duties as $index => $item) {
            $pdf->SetXY($bulletX, $dutyY + ($index * $bulletLineHeight));
            $pdf->Cell(123, 4.0, $this->toPdfText('• ' . $item), 0, 0, 'L');
        }
    }

    private function buildWesPdfLegacy(string $fullName, Collection $experiences): \FPDF
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

    private function resolveWesTemplatePdfPath(): ?string
    {
        $pdfPath = resource_path('templates/work_experience_template.pdf');
        $docxPath = $this->resolveWesTemplateDocxPath();

        if ($docxPath !== null && (!file_exists($pdfPath) || filemtime($docxPath) > filemtime($pdfPath))) {
            $converted = $this->convertDocxTemplateToPdf($docxPath, $pdfPath);
            if (!$converted && file_exists($pdfPath)) {
                // Keep existing PDF template when conversion fails.
                return $pdfPath;
            }
        }

        return file_exists($pdfPath) ? $pdfPath : null;
    }

    private function resolveWesTemplateDocxPath(): ?string
    {
        $path = resource_path('templates/work_experience_template.docx');
        return file_exists($path) ? $path : null;
    }

    private function convertDocxTemplateToPdf(string $docxPath, string $pdfPath): bool
    {
        $escapedDocx = str_replace("'", "''", $docxPath);
        $escapedPdf = str_replace("'", "''", $pdfPath);

        $script = <<<'PS'
$ErrorActionPreference = 'Stop'
$docxPath = '__DOCX__'
$pdfPath = '__PDF__'
$word = $null
$document = $null
try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $document = $word.Documents.Open($docxPath, $false, $true)
    $wdFormatPDF = 17
    $document.SaveAs([ref]$pdfPath, [ref]$wdFormatPDF)
}
finally {
    if ($document -ne $null) { $document.Close($false) | Out-Null }
    if ($word -ne $null) { $word.Quit() | Out-Null }
    if ($document -ne $null) { [System.Runtime.Interopservices.Marshal]::ReleaseComObject($document) | Out-Null }
    if ($word -ne $null) { [System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null }
    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}
PS;

        $script = str_replace('__DOCX__', $escapedDocx, $script);
        $script = str_replace('__PDF__', $escapedPdf, $script);

        $scriptPath = storage_path('app/temp/wes_docx_to_pdf.ps1');
        @mkdir(dirname($scriptPath), 0777, true);
        file_put_contents($scriptPath, $script);

        $command = 'powershell -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($scriptPath);
        $output = [];
        $exitCode = 1;
        @exec($command, $output, $exitCode);

        @unlink($scriptPath);

        if ($exitCode !== 0) {
            Log::warning('Failed to convert WES DOCX template to PDF.', [
                'docx' => $docxPath,
                'pdf' => $pdfPath,
                'exit_code' => $exitCode,
                'output' => implode("\n", $output),
            ]);
            return false;
        }

        return file_exists($pdfPath);
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

        $pdf->SetFont('Arial', '', 9.5);
        $pdf->Cell(56, 6, $this->toPdfText($label), 0, 0);
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->MultiCell(130, 6, $this->toPdfText($safeValue), 0, 'L');
    }

    private function pdfListBlock(\FPDF $pdf, string $title, array $items): void
    {
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->Cell(0, 6, $this->toPdfText($title . ':'), 0, 1);
        $pdf->SetFont('Arial', '', 9.5);

        foreach ($items as $item) {
            $pdf->Cell(4, 6, $this->toPdfText('•'), 0, 0);
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

    private function formatTemplateMultilineList($value): string
    {
        $items = $this->listItemsForPreview($value);
        if (empty($items)) {
            return '• N/A';
        }

        return implode('  ', array_map(function ($item) {
            return '• ' . trim((string) $item);
        }, $items));
    }

    private function overlayWesSignatureName(\FPDF $pdf, string $fullName): void
    {
        $name = trim($fullName) !== '' ? trim($fullName) : 'N/A';

        // Signature line text area at lower-right of WES template.
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        $signatureLineX = 130.0;
        $signatureLineWidth = 42.0;

        $pdf->SetXY($signatureLineX, 129.0);
        $pdf->Cell($signatureLineWidth, 5.0, $this->toPdfText($name), 0, 0, 'C');
    }

    private function setTemplateValueOnce(TemplateProcessor $templateProcessor, string $key, string $value): void
    {
        $templateProcessor->setValue($key, $this->sanitizeDocxText($value), 1);
    }

    private function sanitizeDocxText(string $value): string
    {
        $cleaned = str_replace(
            ["\r\n", "\r", "\u{00A0}"],
            [" ", " ", ' '],
            trim($value)
        );

        return $cleaned === '' ? 'N/A' : $cleaned;
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
            '…' => '...',
            "\u{00A0}" => ' ',
        ]);

        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $cleaned);

        return $converted === false ? utf8_decode($cleaned) : $converted;
    }
}
