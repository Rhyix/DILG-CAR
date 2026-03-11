<?php
namespace App\Http\Controllers\Forms;

use App\Models\PersonalInformation;
use App\Models\FamilyBackground;
use App\Models\EducationalBackground;
use App\Models\CivilServiceEligibility;
use App\Models\WorkExperience;
use App\Models\VoluntaryWork;
use App\Models\LearningAndDevelopment;
use App\Models\OtherInformation;
use App\Models\MiscInfos;
use setasign\Fpdi\Fpdi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ExportPDSController
{
    private const LEGACY_TEMPLATE_WIDTH_MM = 215.9;
    private const LEGACY_TEMPLATE_HEIGHT_MM = 330.2;
    private const SHORT_BOND_TEMPLATE_HEIGHT_MM = 279.4;

    private float $xScale = 1.0;
    private float $yScale = 1.0;
    private float $yOffset = 0.0;
    private float $fontScale = 1.0;
    private float $xOffset = 0.0;
    private bool $isShortBondTemplate = false;
    private int $currentTemplatePage = 1;
    private int $clampedCoordinates = 0;
    private array $clampSamples = [];
    private bool $forceAscii = false;

    private function getFooterDateY(): float
    {
        if (!$this->isShortBondTemplate) {
            return 305;
        }

        // Page 2 is handled separately via getPage2FooterDateY().
        // This baseline covers the other template pages.
        if ($this->currentTemplatePage === 1) {
            return 310.190;
        }

        if ($this->currentTemplatePage === 3) {
            return 273.2;
        }

        return 310.190;
    }

    private function getPage2FooterDateX(): float
    {
        // Adjust page-2 footer date X here without affecting other pages.
        return 145;
    }

    private function getPage2FooterDateY(): float
    {
        // Adjust page-2 footer date Y here without affecting other pages.
        return $this->isShortBondTemplate ? 273.2 : 291;
    }

    private function writeFooterDate(Fpdi $pdf): void
    {
        $this->setFont($pdf, 'Arial', '', 8);

        if ($this->currentTemplatePage === 2) {
            $this->setXY($pdf, $this->getPage2FooterDateX(), $this->getPage2FooterDateY());
        } else {
            $this->setXY($pdf, 163.5, $this->getFooterDateY());
        }

        $pdf->Write(0, Carbon::now()->format('m/d/Y'));
    }

    public function exportPDS(Request $request)
    {
        $this->clampedCoordinates = 0;
        $this->clampSamples = [];

        $user = Auth::user(); // Get currently authenticated user
        if (!$user) {
            abort(401);
        }

        $isPreview = $request->boolean('preview');
        $isDownload = $request->boolean('download');
        $isPrint = $request->boolean('print');
        // Keep instructional red text for preview only; print/download should use clean template.
        $useCleanTemplate = $isDownload || $isPrint || !$isPreview;

        // C1
        $personalInfo = PersonalInformation::where('user_id', $user->id)->first(); // changed from firstOrFail()
        $familyBackground = FamilyBackground::where('user_id', $user->id)->first();
        $educationalBackground = EducationalBackground::where('user_id', $user->id)->first();

        // C2
        $civilServiceEligibility = CivilServiceEligibility::where('user_id', $user->id)
            ->orderByDesc('cs_eligibility_date')
            ->get();

        $workExperience = WorkExperience::where('user_id', $user->id)
            ->orderByDesc('work_exp_from')
            ->get();

        // C3
        $voluntaryWork = VoluntaryWork::where('user_id', $user->id)
            ->orderByDesc('voluntary_from')
            ->get();

        $learningAndDev = LearningAndDevelopment::where('user_id', $user->id)
            ->orderByDesc('learning_from')
            ->get();

        $otherInfo = OtherInformation::where('user_id', $user->id)->first();

        // C4
        $OtherInformation = MiscInfos::where('user_id', $user->id)->first();


        // Creating PDF File from template
        $pdf = new Fpdi();
        // Keep absolute-positioned template writing from triggering automatic blank pages.
        $pdf->SetAutoPageBreak(false, 0);

        $templateCandidates = $useCleanTemplate
            ? [
                resource_path('templates/revised pds without red signature text.pdf'),
                resource_path('templates/pds template without red text.pdf'),
                resource_path('templates/PDS_2025_from_xlsx.pdf'),
                resource_path('templates/PDS_fixed_V9.pdf'),
            ]
            : [
                resource_path('templates/PDS_2025_from_xlsx.pdf'),
                resource_path('templates/pds template without red text.pdf'),
                resource_path('templates/PDS_fixed_V9.pdf'),
            ];

        $templatePath = null;
        foreach ($templateCandidates as $candidate) {
            if (file_exists($candidate)) {
                $templatePath = $candidate;
                break;
            }
        }

        if ($templatePath === null) {
            abort(404, 'PDS PDF template was not found.');
        }

        $pageCount = $pdf->setSourceFile($templatePath);

        // Separates Residential and Permanent Address Information
        $SEPARATOR = "/|/";
        $residentialRaw = $personalInfo && $personalInfo->residential_address ? $personalInfo->residential_address : '';
        $residential = array_map(fn($part) => $part != '{*}' ? $part : null, explode($SEPARATOR, $residentialRaw));

        $permanentRaw = $personalInfo && $personalInfo->permanent_address ? $personalInfo->permanent_address : '';
        $permanent = array_map(fn($part) => $part != '{*}' ? $part : null, explode($SEPARATOR, $permanentRaw));

        // Preprocess Children chunks
        $children = [];

        if ($familyBackground && $familyBackground->children_info) {
            $children = $this->normalizeListData($familyBackground->children_info);
        }

        $childrenChunks = array_chunk($children, 12);

        // Preprocess Vocational, College, and Graduate School chunks
        $vocationalData = $educationalBackground?->vocational ?? [];
        $collegeData = $educationalBackground?->college ?? [];
        $gradData = $educationalBackground?->grad ?? [];

        $vocational = $this->normalizeListData($vocationalData);
        $college = $this->normalizeListData($collegeData);
        $grad = $this->normalizeListData($gradData);

        $vocationalChunks = array_chunk($vocational, 1);
        $collegeChunks = array_chunk($college, 1);
        $gradChunks = array_chunk($grad, 1);

        $hasVocOverflow = count($vocationalChunks) > 1;
        $hasCollegeOverflow = count($collegeChunks) > 1;
        $hasGradOverflow = count($gradChunks) > 1;

        // Preprocess Civil Service Eligibility and Work Experience chunks
        $cseChunks = array_chunk($civilServiceEligibility->toArray(), 7);
        $weChunks = array_chunk($workExperience->toArray(), 28);

        $hasCSEOverflow = count($cseChunks) > 1;
        $hasWEOverflow = count($weChunks) > 1;

        // Preprocess Voluntary Works, Learning and Development and other Information Chunks
        $vwChunks = array_chunk($voluntaryWork->toArray(), 7);
        $lndChunks = array_chunk($learningAndDev->toArray(), 21);

        $skills = [];
        $distinctions = [];
        $organizations = [];

        if ($otherInfo) {
            $skills = $this->normalizeListData($otherInfo->skill);
            $skillsChunks = array_chunk($skills, 7);

            $distinctions = $this->normalizeListData($otherInfo->distinction);
            $distinctionsChunks = array_chunk($distinctions, 7);

            $organizations = $this->normalizeListData($otherInfo->organization);
            $organizationsChunks = array_chunk($organizations, 7);
        } else {
            $skillsChunks = [];
            $distinctionsChunks = [];
            $organizationsChunks = [];
        }

        $hasVWOverflow = count($vwChunks) > 1;
        $hasLNDOverflow = count($lndChunks) > 1;

        // Excel-path export currently supports only the first 21 L&D rows.
        // When L&D overflows, force FPDI so continuation pages are generated.
        // Keep Excel export for preview mode only; print/download mode relies on the clean PDF template.
        // Default to FPDI for consistent coordinates/styling across accounts. Excel path is opt-in.
        $forceFpdi = $request->has('force_fpdi') ? $request->boolean('force_fpdi') : true;
        $canUseExcelTemplate = !$useCleanTemplate && !$forceFpdi && !$hasLNDOverflow;
        $excelExport = null;
        if ($canUseExcelTemplate) {
            $excelExport = $this->tryExportViaExcelTemplate(
                $request,
                $personalInfo,
                $familyBackground,
                $educationalBackground,
                $civilServiceEligibility->toArray(),
                $workExperience->toArray(),
                $voluntaryWork->toArray(),
                $learningAndDev->toArray(),
                $otherInfo,
                $OtherInformation,
                $residential,
                $permanent,
                $children,
                $vocational,
                $college,
                $grad,
                $skills,
                $distinctions,
                $organizations
            );
        }

        if ($excelExport !== null) {
            activity()
                ->causedBy($user)
                ->event('export')
                ->withProperties([
                    'exported_file' => $excelExport['filename'],
                    'section' => 'Export'
                ])
                ->log('Exported Personal Data Sheet (PDS) via Excel template.');

            return $this->respondWithGeneratedPdfPath($request, $excelExport['path'], $excelExport['filename']);
        }

        // ----------------------------
        // Page 1: Personal Info, Address, Family, Education
        // ----------------------------
        $templateId = $pdf->importPage(1);
        $this->currentTemplatePage = 1;
        $page1Size = $pdf->getTemplateSize($templateId);
        $this->configureCoordinateScale((float) $page1Size['width'], (float) $page1Size['height']);
        $pdf->AddPage($page1Size['orientation'], [$page1Size['width'], $page1Size['height']]);
        $pdf->useTemplate($templateId);
        $this->setFont($pdf, 'Arial', '', 8);

        $this->writePersonalInfo($pdf, $personalInfo);
        $this->writeAddresses($pdf, $residential, $permanent);
        $this->writeFamilyBackground($pdf, $familyBackground);
        $this->writeEducationalBackground($pdf, $educationalBackground);

        $this->writeCollegeChunk($pdf, $collegeChunks[0] ?? []);
        $this->writeVocationalChunk($pdf, $vocationalChunks[0] ?? []);
        $this->writeGraduateChunk($pdf, $gradChunks[0] ?? []);
        $this->writeChildrenChunk($pdf, $childrenChunks[0] ?? []);

        $this->writeFooterDate($pdf);


        // Determine continuation needs
        $childrenHasOverflow = count($childrenChunks) > 1;
        $schoolHasOverflow = count($collegeChunks) > 1 || count($vocationalChunks) > 1 || count($gradChunks) > 1;

        // Write CONTINUED indicator for children if overflow exists
        if ($childrenHasOverflow) {
            $this->setFont($pdf, 'Arial', 'B', 8);
            $this->setXY($pdf, 122, 242);
            $pdf->Write(0, "CONTINUED");
            $this->setFont($pdf, 'Arial', '', 8);
        }

        // Write CONTINUED indicator for school chunks if overflow exists
        if ($schoolHasOverflow) {
            $this->setFont($pdf, 'Arial', 'B', 8);
            $this->setXY($pdf, 43, 310);
            $pdf->Write(0, "CONTINUED");
            $this->setFont($pdf, 'Arial', '', 8);
        }

        // ----------------------------
        // Overflow Pages: Children, Vocational, College, Graduate overflow
        // ----------------------------

        // Determine the maximum number of overflow chunks needed for C1
        $maxChunks = max(
            count($childrenChunks),
            count($vocationalChunks),
            count($collegeChunks),
            count($gradChunks)
        );

        // Loop through each overflow chunk beyond the first page
        for ($i = 1; $i < $maxChunks; $i++) {
            $templateId = $pdf->importPage(1); // Reuse Page 1 template
            $this->currentTemplatePage = 1;
            $pdf->AddPage($page1Size['orientation'], [$page1Size['width'], $page1Size['height']]);
            $pdf->useTemplate($templateId);
            $this->writeFooterDate($pdf);

            $this->setFont($pdf, 'Arial', '', 8);

            // Write only the name parts for identification on overflow pages.
            $this->writeFittedAt($pdf, $this->valueOrNa($personalInfo?->surname), 41.5, 45.5, 78);
            $this->writeFittedAt($pdf, $this->valueOrNa($personalInfo?->first_name), 41.5, 52, 78);
            $this->writeFittedAt($pdf, $this->valueOrNa($personalInfo?->middle_name), 41.5, 58, 78);
            $this->writeFittedAt($pdf, $this->valueOrNa($personalInfo?->name_extension), 161, 52.5, 40);

            // Write children overflow chunk if exists
            if (isset($childrenChunks[$i])) {
                $this->writeChildrenChunk($pdf, $childrenChunks[$i]);
            }

            // Write vocational overflow chunk if exists
            if (isset($vocationalChunks[$i])) {
                $this->writeVocationalChunk($pdf, $vocationalChunks[$i]);
            }

            // Write college overflow chunk if exists
            if (isset($collegeChunks[$i])) {
                $this->writeCollegeChunk($pdf, $collegeChunks[$i]);
            }

            // Write graduate overflow chunk if exists
            if (isset($gradChunks[$i])) {
                $this->writeGraduateChunk($pdf, $gradChunks[$i]);
            }
        }

        // ----------------------------
        // Page 2: First CSE chunk + First WE chunk
        // ----------------------------

        $templateId = $pdf->importPage(2);
        $this->currentTemplatePage = 2;
        $page2Size = $pdf->getTemplateSize($templateId);
        $this->configureCoordinateScale((float) $page2Size['width'], (float) $page2Size['height']);
        $pdf->AddPage($page2Size['orientation'], [$page2Size['width'], $page2Size['height']]);
        $pdf->useTemplate($templateId);
        // Write first CSE chunk (max 7 rows)
        $this->writeCivilServiceEligibilityChunk($pdf, $cseChunks[0] ?? []);

        // Write first WE chunk (max 27 rows)
        $this->writeWorkExperienceChunk($pdf, $weChunks[0] ?? []);


        // Determine CSE and WE continuation
        $cseHasOverflow = count($cseChunks) > 1;
        $weHasOverflow = count($weChunks) > 1;

        if ($cseHasOverflow) {
            $this->setFont($pdf, 'Arial', 'B', 8);
            $this->setXY($pdf, 8, 80.5); // adjust XY for CSE continued placement
            $pdf->Write(0, "CONTINUED");
            $this->setFont($pdf, 'Arial', '', 8);
        }

        if ($weHasOverflow) {
            $this->setFont($pdf, 'Arial', 'B', 8);
            $this->setXY($pdf, 8, 308.5); // adjust XY for WE continued placement
            $pdf->Write(0, "CONTINUED");
            $this->setFont($pdf, 'Arial', '', 8);
        }

        $this->writeFooterDate($pdf);
        // ----------------------------
        // Overflow Pages: CSE overflow + WE overflow
        // ----------------------------

        // Handle CSE overflow pages
        for ($i = 1; $i < count($cseChunks); $i++) {
            $this->currentTemplatePage = 2;
            $pdf->AddPage($page2Size['orientation'], [$page2Size['width'], $page2Size['height']]);
            $pdf->useTemplate($templateId);
            $this->writeFooterDate($pdf);
            $this->writeCivilServiceEligibilityChunk($pdf, $cseChunks[$i]);

            // If WE overflows, write next WE chunk here
            if ($hasWEOverflow && isset($weChunks[$i])) {
                $this->writeWorkExperienceChunk($pdf, $weChunks[$i]);
                unset($weChunks[$i]); // Mark as written
            }
        }

        // Handle remaining WE overflow pages
        foreach ($weChunks as $index => $chunk) {
            if ($index == 0) continue; // Already written first WE chunk on Page 2
            $this->currentTemplatePage = 2;
            $pdf->AddPage($page2Size['orientation'], [$page2Size['width'], $page2Size['height']]);
            $pdf->useTemplate($templateId);
            $this->writeFooterDate($pdf);
            $this->writeWorkExperienceChunk($pdf, $chunk);
        }

        // ----------------------------
        // Page 3: First VW chunk + First LND chunk + First Others Chunk
        // ----------------------------

        $templateId = $pdf->importPage(3);
        $this->currentTemplatePage = 3;
        $page3Size = $pdf->getTemplateSize($templateId);
        $this->configureCoordinateScale((float) $page3Size['width'], (float) $page3Size['height']);
        $pdf->AddPage($page3Size['orientation'], [$page3Size['width'], $page3Size['height']]);
        $pdf->useTemplate($templateId);
        // Write first Voluntary chunk (max 7 rows)
        $this->writeVoluntaryWorkChunk($pdf, $vwChunks[0] ?? []);

        // Write first LND chunk (max 21 rows)
        $this->writeLearningAndDevelopmentChunk($pdf, $lndChunks[0] ?? []);

        // Write first Other Information (skills, distinctions, organizations)
        $this->writeOtherInformation(
            $pdf,
            $skillsChunks[0] ?? [],
            $distinctionsChunks[0] ?? [],
            $organizationsChunks[0] ?? []
        );

        // Determine continuation for C3
        $vwHasOverflow = count($vwChunks) > 1;
        $lndHasOverflow = count($lndChunks) > 1;
        $skillsHasOverflow = count($skillsChunks) > 1;
        $distinctionsHasOverflow = count($distinctionsChunks) > 1;
        $organizationsHasOverflow = count($organizationsChunks) > 1;

        if ($vwHasOverflow) {
            $this->setFont($pdf, 'Arial', 'B', 8);
            $this->setXY($pdf, 8, 77.5); // adjust as needed for Voluntary Work
            $pdf->Write(0, "CONTINUED");
            $this->setFont($pdf, 'Arial', '', 8);
        }

        if ($skillsHasOverflow || $distinctionsHasOverflow || $organizationsHasOverflow) {
            $this->setFont($pdf, 'Arial', 'B', 8);
            $this->setXY($pdf, 8, 299); // adjust as needed for Other Information
            $pdf->Write(0, "CONTINUED");
            $this->setFont($pdf, 'Arial', '', 8);
        }

        $this->writeFooterDate($pdf);

        // ----------------------------
        // Overflow Pages: Page 3 logic for remaining chunks
        // ----------------------------

        $page3OverflowMax = max(
            count($vwChunks),
            count($skillsChunks),
            count($distinctionsChunks),
            count($organizationsChunks)
        );

        for ($i = 1; $i < $page3OverflowMax; $i++) {
            $this->currentTemplatePage = 3;
            $this->configureCoordinateScale((float) $page3Size['width'], (float) $page3Size['height']);
            $pdf->AddPage($page3Size['orientation'], [$page3Size['width'], $page3Size['height']]);
            $pdf->useTemplate($templateId);
            $this->writeFooterDate($pdf);

            // Write Voluntary Work chunk if exists
            if (isset($vwChunks[$i])) {
                $this->writeVoluntaryWorkChunk($pdf, $vwChunks[$i]);
            }

            // Write Other Information chunk if exists
            $skillsChunk = $skillsChunks[$i] ?? [];
            $distinctionsChunk = $distinctionsChunks[$i] ?? [];
            $organizationsChunk = $organizationsChunks[$i] ?? [];

            // Only call if any of them have data
            if ($skillsChunk || $distinctionsChunk || $organizationsChunk) {
                $this->writeOtherInformation($pdf, $skillsChunk, $distinctionsChunk, $organizationsChunk);
            }
        }

        // ----------------------------
        // Overflow Pages: L&D continuation pages (beyond first 21 rows)
        // ----------------------------
        if ($lndHasOverflow) {
            $lndContinuationTemplatePath = resource_path('templates/LEARNING AND DEVELOPMENT (L&D) INTERVENTIONSTRAINING PROGRAMS ATTENDED.pdf');
            $lndContinuationTemplateId = $templateId;
            $lndContinuationPageSize = $page3Size;
            $usingDedicatedLndTemplate = false;

            if (file_exists($lndContinuationTemplatePath)) {
                $pdf->setSourceFile($lndContinuationTemplatePath);
                $lndContinuationTemplateId = $pdf->importPage(1);
                $lndContinuationPageSize = $pdf->getTemplateSize($lndContinuationTemplateId);
                $usingDedicatedLndTemplate = true;
            }

            for ($i = 1; $i < count($lndChunks); $i++) {
                $this->currentTemplatePage = 3;
                $this->configureCoordinateScale((float) $lndContinuationPageSize['width'], (float) $lndContinuationPageSize['height']);
                $pdf->AddPage($lndContinuationPageSize['orientation'], [$lndContinuationPageSize['width'], $lndContinuationPageSize['height']]);
                $pdf->useTemplate($lndContinuationTemplateId);
                $this->writeLearningAndDevelopmentChunk($pdf, $lndChunks[$i]);
                $this->writeFooterDate($pdf);
            }

            if ($usingDedicatedLndTemplate) {
                // Restore the main PDS source before importing page 4.
                $pdf->setSourceFile($templatePath);
            }
        }

        // ----------------------------
        // Page 4: Other Information
        // ----------------------------

        $templateId = $pdf->importPage(4);
        $this->currentTemplatePage = 4;
        $page4Size = $pdf->getTemplateSize($templateId);
        $this->configureCoordinateScale((float) $page4Size['width'], (float) $page4Size['height']);
        $pdf->AddPage($page4Size['orientation'], [$page4Size['width'], $page4Size['height']]);
        $pdf->useTemplate($templateId);
        // Write first C4 chunk (max 7 rows)
        $this->WriteC4Information($pdf, $user->id);

        $this->setXY($pdf, 113, 274);
        $pdf->Write(0, Carbon::now()->format('m/d/Y'));

        // C4 has no overflow data so no need for overflow page


        // ----------------------------
        // Output PDF
        // ----------------------------

        activity()
            ->causedBy($user)
            ->event('export')
            ->withProperties([
                'exported_file' => 'ExportPDS.pdf',
                //'pages_generated' => $pdf->page, // total pages processed
                'section' => 'Export'
            ])
            ->log('Exported Personal Data Sheet (PDS).');

        $this->logClampDiagnostics();


        $pdf->SetTitle('Personal Data Sheet');

        $timestamp = date('Y-m-d_His');
        $filename = "ExportPDS_{$timestamp}.pdf";

        // Detect if mobile
        $userAgent = (string) ($request->userAgent() ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $isMobile = preg_match('/Android|iPhone|iPad|iPod|webOS|BlackBerry|Windows Phone/i', $userAgent);

        $forceInline = $isPreview || $isPrint;

        if ($isDownload) {
            // Download as attachment
            $pdf->Output($filename, 'D');
            exit;
        } elseif ($isMobile && !$forceInline) {
            // Save the PDF temporarily
            $tempPath = storage_path("app/public/{$filename}");
            $pdf->Output($tempPath, 'F');

            // Optionally store the path in session or flash data for download link
            // Redirect with success message
            return redirect()
                ->route('dashboard_user') // Change to your actual route
                ->with('success', 'PDF generated successfully! You may download it from your dashboard.');
        } else {
            // Preview inline on desktop
            $pdf->Output($filename, 'I');
            exit;
        }

        exit;
    }

    private function configureCoordinateScale(float $templateWidth, float $templateHeight): void
    {
        if ($templateWidth <= 0 || $templateHeight <= 0) {
            $this->xScale = 1.0;
            $this->yScale = 1.0;
            $this->yOffset = 0.0;
            $this->fontScale = 1.0;
            $this->xOffset = 0.0;
            $this->isShortBondTemplate = false;
            return;
        }

        $this->xScale = $templateWidth / self::LEGACY_TEMPLATE_WIDTH_MM;
        $this->yScale = $templateHeight / self::LEGACY_TEMPLATE_HEIGHT_MM;

        if (abs($this->xScale - 1.0) < 0.001) {
            $this->xScale = 1.0;
        }
        if (abs($this->yScale - 1.0) < 0.001) {
            $this->yScale = 1.0;
        }

        // Keep the "short-bond" logic only for true 8.5x11 templates.
        $isShortBondTemplate = abs($templateHeight - self::SHORT_BOND_TEMPLATE_HEIGHT_MM) <= 2.0;
        $this->isShortBondTemplate = $isShortBondTemplate;

        $this->xOffset = 0.0;
        $this->yOffset = 0.0;
        $this->fontScale = max(0.75, min(1.25, min($this->xScale, $this->yScale)));
    }

    private function scaleX(float $x): float
    {
        return round($x * $this->xScale, 3);
    }

    private function scaleY(float $y): float
    {
        return round($y * $this->yScale, 3);
    }

    private function scaleHeight(float $height): float
    {
        return round($height * $this->yScale, 3);
    }

    private function scaleWidth(float $width): float
    {
        return round($width * $this->xScale, 3);
    }

    private function scaleFont(float $size): float
    {
        return max(4.5, round($size * $this->fontScale, 2));
    }

    private function setFont(Fpdi $pdf, string $family, string $style, float $size): void
    {
        $pdf->SetFont($family, $style, $this->scaleFont($size));
    }

    private function setXY(Fpdi $pdf, float $x, float $y): void
    {
        $targetX = $this->scaleX($x) + $this->xOffset;
        $targetY = $this->scaleY($y) + $this->yOffset;

        $pageWidth = $pdf->GetPageWidth();
        $pageHeight = $pdf->GetPageHeight();
        $safeX = max(1.5, min($targetX, $pageWidth - 2.5));
        $safeY = max(1.5, min($targetY, $pageHeight - 2.5));

        if (abs($safeX - $targetX) > 0.001 || abs($safeY - $targetY) > 0.001) {
            $this->clampedCoordinates++;
            if (count($this->clampSamples) < 8) {
                $this->clampSamples[] = [
                    'page' => $this->currentTemplatePage,
                    'target_x' => round($targetX, 3),
                    'target_y' => round($targetY, 3),
                    'safe_x' => round($safeX, 3),
                    'safe_y' => round($safeY, 3),
                    'page_width' => round($pageWidth, 3),
                    'page_height' => round($pageHeight, 3),
                ];
            }
        }

        $pdf->SetXY($safeX, $safeY);
    }

    private function logClampDiagnostics(): void
    {
        if ($this->clampedCoordinates <= 0) {
            return;
        }

        Log::warning('PDS export coordinate clamping detected.', [
            'count' => $this->clampedCoordinates,
            'samples' => $this->clampSamples,
        ]);
    }

    private function normalizeListData($data): array
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($data)) {
            return [];
        }

        if (empty($data)) {
            return [];
        }

        $hasNonNumericKeys = array_keys($data) !== range(0, count($data) - 1);
        if ($hasNonNumericKeys) {
            $data = [$data];
        }

        return array_values(array_filter($data, function ($row) {
            if (is_array($row)) {
                foreach ($row as $value) {
                    if (is_array($value)) {
                        foreach ($value as $nested) {
                            if (trim((string) $nested) !== '') {
                                return true;
                            }
                        }
                    } elseif (trim((string) $value) !== '') {
                        return true;
                    }
                }
                return false;
            }

            return trim((string) $row) !== '';
        }));
    }

    private function valueOrNa($value): string
    {
        if ($value === null) {
            return 'N/A';
        }

        $text = trim((string) $value);
        if ($text === '' || strtolower($text) === 'null') {
            return 'N/A';
        }

        return $text;
    }

    private function hasMeaningfulValue($value): bool
    {
        $text = strtolower($this->normalizeScalarText($value));
        return $text !== '' && $text !== 'null';
    }

    private function hasCivilServiceData(array $rows): bool
    {
        foreach ($rows as $row) {
            if (
                $this->hasMeaningfulValue($row['cs_eligibility_career'] ?? null) ||
                $this->hasMeaningfulValue($row['cs_eligibility_rating'] ?? null) ||
                $this->hasMeaningfulValue($row['cs_eligibility_date'] ?? null) ||
                $this->hasMeaningfulValue($row['cs_eligibility_place'] ?? null) ||
                $this->hasMeaningfulValue($row['cs_eligibility_license'] ?? null) ||
                $this->hasMeaningfulValue($row['cs_eligibility_validity'] ?? null)
            ) {
                return true;
            }
        }

        return false;
    }

    private function hasWorkExperienceData(array $rows): bool
    {
        foreach ($rows as $row) {
            if (
                $this->hasMeaningfulValue($row['work_exp_from'] ?? null) ||
                $this->hasMeaningfulValue($row['work_exp_to'] ?? null) ||
                $this->hasMeaningfulValue($row['work_exp_position'] ?? null) ||
                $this->hasMeaningfulValue($row['work_exp_department'] ?? null) ||
                $this->hasMeaningfulValue($row['work_exp_status'] ?? null) ||
                $this->normalizeGovServiceFlag($row['work_exp_govt_service'] ?? null, '') !== ''
            ) {
                return true;
            }
        }

        return false;
    }

    private function hasAnyRowData(array $rows, array $keys): bool
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            foreach ($keys as $key) {
                if ($this->hasMeaningfulValue($row[$key] ?? null)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasEducationObjectData($education, array $keys): bool
    {
        foreach ($keys as $key) {
            if (is_object($education)) {
                $value = $education->{$key} ?? null;
            } elseif (is_array($education)) {
                $value = $education[$key] ?? null;
            } else {
                $value = null;
            }

            if ($this->hasMeaningfulValue($value)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeScalarText($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value) || is_numeric($value)) {
            return trim((string) $value);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return is_string($encoded) ? trim($encoded) : '';
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return trim((string) $value);
            }
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return is_string($encoded) ? trim($encoded) : '';
        }

        return trim((string) $value);
    }

    private function dateOrNa($value, string $format = 'm/d/Y'): string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '' || strtolower($text) === 'null') {
            return 'N/A';
        }

        try {
            return Carbon::parse($text)->format($format);
        } catch (\Throwable $e) {
            return $text;
        }
    }

    private function normalizedValue($value): string
    {
        return strtolower(trim((string) ($value ?? '')));
    }

    private function valueMatches($value, string ...$candidates): bool
    {
        $needle = $this->normalizedValue($value);
        if ($needle === '') {
            return false;
        }

        foreach ($candidates as $candidate) {
            if ($needle === strtolower(trim($candidate))) {
                return true;
            }
        }

        return false;
    }

    private function normalizeGovServiceFlag($value, string $default = ''): string
    {
        $raw = $this->normalizedValue($value);
        if ($raw === '' || $raw === '~' || $raw === 'null' || $raw === 'n/a' || $raw === 'na') {
            return $default;
        }

        if (in_array($raw, ['y', 'yes', '1', 'true'], true) || str_starts_with($raw, 'y')) {
            return 'Y';
        }

        if (in_array($raw, ['n', 'no', '0', 'false'], true) || str_starts_with($raw, 'n')) {
            return 'N';
        }

        return $default;
    }


    private function writePersonalInfo($pdf, $info)
    {
        // CS ID No (CSC use only): leave blank when missing.
        $csIdNo = trim((string) ($info?->cs_id_no ?? ''));
        if (strtolower($csIdNo) === 'null') {
            $csIdNo = '';
        }
        $this->writeFittedAt($pdf, $csIdNo, 165, 35.5, 35);

        // Names
        $this->writeFittedAt($pdf, $this->valueOrNa($info?->surname), 41.5, 45.5, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->first_name), 41.5, 52, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->middle_name), 41.5, 58, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->name_extension), 161, 52.5, 40);

        // Birth
        $this->writeFittedAt(
            $pdf,
            $this->dateOrNa($info?->date_of_birth),
            41.5,
            66,
            34,
            8.0,
            5.0
        );

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->place_of_birth), 41.5, 75, 65);

        // Sex
        $sex = $this->normalizedValue($info?->sex);
        if ($sex === 'male') {
            $this->markCheckbox($pdf, 43.5, 78.7);
        } elseif ($sex === 'female') {
            $this->markCheckbox($pdf, 72.8, 79);
        }

        // Civil Status
        $civilStatus = $this->normalizedValue($info?->civil_status);
        $civilStatusCoords = [
            'single' => [43.5, 85],
            'married' => [72.8, 85],
            'widowed' => [43.5, 89],
            'separated' => [72.8, 89],
            'other' => [43, 93],
            'other/s' => [43, 93],
            'others' => [43, 93],
        ];
        if ($civilStatus !== '') {
            $coords = $civilStatusCoords[$civilStatus] ?? $civilStatusCoords['other'];
            $this->markCheckbox($pdf, (float) $coords[0], (float) $coords[1]);
        }

        // Physical
        $this->writeFittedAt($pdf, $this->valueOrNa($info?->height), 41.5, 101.5, 30, 8.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->weight), 41.5, 107.5, 30, 8.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->blood_type), 41.5, 114.5, 30, 8.0, 5.0);

        // IDs
        $this->writeFittedAt($pdf, $this->valueOrNa($info?->gsis_id_no), 41.5, 121.5, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->pagibig_id_no), 41.5, 128.5, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->philhealth_no), 41.5, 135, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->sss_id_no), 41.5, 142, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->tin_no), 41.5, 149, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->agency_employee_no), 41.5, 155.5, 78);

        // Citizenship
        if ($this->valueMatches($info?->citizenship, 'Filipino')) {
            $this->markCheckbox($pdf, 139, 63.5);
        } elseif ($this->valueMatches($info?->citizenship, 'Dual Citizenship', 'Dual')) {
            $this->markCheckbox($pdf, 157.5, 63.5);
        }

        if ($this->valueMatches($info?->dual_type, 'By Birth', 'By birth', 'Birth', 'By_Birth')) {
            $this->markCheckbox($pdf, 163, 68);
        } elseif ($this->valueMatches($info?->dual_type, 'By Naturalization', 'By naturalization', 'Naturalization', 'By_Naturalization')) {
            $this->markCheckbox($pdf, 178.5, 68.2);
        }

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->dual_country), 138, 81, 52);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->telephone_no), 122, 142, 74);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->mobile_no), 122.4, 148.5, 74);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->email_address), 122.5, 154.5, 74, 7.5, 5.0);

}

private function writeAddresses($pdf, $residential, $permanent)
{
    // Residential Address
    $this->writeWrappedAt($pdf, $this->valueOrNa($residential[0] ?? null), 136, 86, 48, 8.0, 2.2, 1.0); // House Number
    $this->writeWrappedAt($pdf, $this->valueOrNa($residential[1] ?? null), 179.5, 86, 65, 8.0, 2.2, 1.0); // Street
    $this->writeWrappedAt($pdf, $this->valueOrNa($residential[2] ?? null), 136, 92.5, 23, 8.0, 2.2, 1.0); // Village/Subdivision
    $this->writeWrappedAt($pdf, $this->valueOrNa($residential[3] ?? null), 179.5, 92.5, 48, 8.0, 2.2, 1.0); // Barangay
    $this->writeWrappedAt($pdf, $this->valueOrNa($residential[4] ?? null), 130, 99, 50, 8.0, 2.2, 1.0); // City/Municipality
    $this->writeWrappedAt($pdf, $this->valueOrNa($residential[5] ?? null), 179.5, 99, 100, 8.0, 2.2, 1.0); // Province
    $this->writeWrappedAt($pdf, $this->valueOrNa($residential[6] ?? null), 130, 108, 60, 8.0, 2.2, 1.0); // ZIP Code

    // Permanent Address
    $this->writeWrappedAt($pdf, $this->valueOrNa($permanent[0] ?? null), 136, 112, 35, 8.0, 2.2, 1.0); // House Number
    $this->writeWrappedAt($pdf, $this->valueOrNa($permanent[1] ?? null), 179.5, 112, 65, 8.0, 2.2, 1.0); // Street
    $this->writeWrappedAt($pdf, $this->valueOrNa($permanent[2] ?? null), 136, 119, 23, 8.0, 2.2, 1.0); // Village/Subdivision
    $this->writeWrappedAt($pdf, $this->valueOrNa($permanent[3] ?? null), 179.5, 118.5, 48, 8.0, 2.2, 1.0); // Barangay
    $this->writeWrappedAt($pdf, $this->valueOrNa($permanent[4] ?? null), 130, 125.5, 50, 8.0, 2.2, 1.0); // City/Municipality
    $this->writeWrappedAt($pdf, $this->valueOrNa($permanent[5] ?? null), 179.5, 125.5, 100, 8.0, 2.2, 1.0); // Province
    $this->writeWrappedAt($pdf, $this->valueOrNa($permanent[6] ?? null), 130, 133.5, 60, 8.0, 2.2, 1.0); // ZIP Code

}

private function writeFamilyBackground($pdf, $family)
{
    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_surname), 41.5, 165, 49, 7.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_first_name), 41.5, 170.5, 49, 7.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_middle_name), 41.5, 176.5, 49, 7.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_name_extension), 90, 171, 28, 7.0, 5.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_occupation), 41.5, 182, 79, 7.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_employer), 41.5, 187.5, 79, 7.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_business_address), 41.5, 193, 79, 7.0, 5.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_telephone), 41.5, 199, 79, 7.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->father_surname), 41.5, 204.5, 49, 7.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->father_first_name), 41.5, 210.5, 49, 7.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->father_name_extension), 90, 211, 28, 7.0, 5.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->father_middle_name), 41.5, 216, 79, 7.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->mother_maiden_surname), 41.5, 227, 79, 7.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->mother_maiden_first_name), 41.5, 233.5, 79, 7.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->mother_maiden_middle_name), 41.5, 238.5, 79, 7.0);

}

private function writeChildrenChunk($pdf, $chunk)
{
    $startX_name = 123;
    $startX_birthdate = 182.5;
    $startY = 170.5;
    $lineHeight = 5.9;

    $isEmpty = !$this->hasAnyRowData((array) $chunk, ['name', 'dob']);

    // If all are empty, write N/A in the first-row cells.
    if ($isEmpty) {
        $this->writeFittedAt($pdf, 'N/A', $startX_name, $startY, 58, 7.5, 5.0);
        $this->writeFittedAt($pdf, 'N/A', $startX_birthdate, $startY, 22, 7.0, 5.0);
        return;
    }

    // Otherwise, loop through and write child data
    foreach ($chunk as $index => $child)
    {
        $currentY = $startY + ($index * $lineHeight);

        $this->writeFittedAt($pdf, $this->valueOrNa($child['name'] ?? null), $startX_name, $currentY, 58, 7.5, 5.0);

        $this->writeFittedAt(
            $pdf,
            $this->dateOrNa($child['dob'] ?? null),
            187,
            $currentY,
            22,
            8.0,
            5.0
        );
    }
}

// Educational Background Part

private function writeEducationalBackground($pdf, $education)
{
    // === Elementary Section ===
    $hasElemData = $this->hasEducationObjectData($education, [
        'elem_school',
        'elem_basic',
        'elem_from',
        'elem_to',
        'elem_earned',
        'elem_year_graduated',
        'elem_academic_honors',
    ]);

    if (!$hasElemData) {
        $this->writeFittedAt($pdf, 'N/A', 62, 263, 49, 8.0, 5.0); // School
        $this->writeFittedAt($pdf, 'N/A', 110, 263, 45, 8.0, 4.5); // Basic Education
        $this->writeFittedAt($pdf, 'N/A', 137.5, 263, 27, 8.0, 5.0); // From
        $this->writeFittedAt($pdf, 'N/A', 150.5, 263, 31.5, 8.0, 5.0); // To
        $this->writeFittedAt($pdf, 'N/A', 166, 263, 18, 8.0, 5.0); // Earned
        $this->writeFittedAt($pdf, 'N/A', 183, 263, 12, 8.0, 5.0); // Year Graduated
        $this->writeFittedAt($pdf, 'N/A', 200, 263, 13, 8.0, 5.0); // Academic Honors
    } else {
        $this->writeWrappedAt(
            $pdf,
            $this->valueOrNa($education?->elem_school),
            40.5,
            265,
            48,
            6.5,
            2.0,
            3.0
        );
        $this->writeWrappedAt(
            $pdf,
            $this->valueOrNa($education?->elem_basic),
            90,
            263,
            45,
            6.5,
            2.0,
            1.3
        );
        $this->writeFittedAt($pdf, $this->dateOrNa($education?->elem_from, 'm/Y'), 136, 263, 27, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->dateOrNa($education?->elem_to, 'm/Y'), 149,263, 31.5, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->elem_earned), 166, 263, 18, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->elem_year_graduated), 181, 263, 12, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->elem_academic_honors), 198, 263, 13, 7.0, 5.0);
    }

    // === Junior High Section ===
    $hasJHSData = $this->hasEducationObjectData($education, [
        'jhs_school',
        'jhs_basic',
        'jhs_from',
        'jhs_to',
        'jhs_earned',
        'jhs_year_graduated',
        'jhs_academic_honors',
    ]);

    if (!$hasJHSData) {
        $this->writeFittedAt($pdf, 'N/A', 62, 271, 49, 8.0, 5.0); // School
        $this->writeFittedAt($pdf, 'N/A', 110, 271, 45, 8.0, 4.5); // Basic Education
        $this->writeFittedAt($pdf, 'N/A', 137.5, 271, 27, 8.0, 5.0); // From
        $this->writeFittedAt($pdf, 'N/A', 150.5, 271, 31.5, 8.0, 5.0); // To
        $this->writeFittedAt($pdf, 'N/A', 166, 271, 18, 8.0, 5.0); // Earned
        $this->writeFittedAt($pdf, 'N/A', 183, 271, 12, 8.0, 5.0); // Year Graduated
        $this->writeFittedAt($pdf, 'N/A', 200, 271, 13, 8.0, 5.0); // Academic Honors
    } else {
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->jhs_school), 40.5, 271, 48, 6.5, 4.5);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->jhs_basic), 90, 271, 45, 6.5, 4.5);
        $this->writeFittedAt($pdf, $this->dateOrNa($education?->jhs_from, 'm/Y'), 136, 271, 27, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->dateOrNa($education?->jhs_to, 'm/Y'), 149, 271, 31.5, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->jhs_earned), 166, 271, 18, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->jhs_year_graduated), 182, 271, 12, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->jhs_academic_honors), 199, 271, 13, 7.0, 5.0);
    }
}


private function writeVocationalChunk($pdf, $chunk)
{
    $startX_school = 41.5;
    $startX_basic = 95;
    $startX_from = 139;
    $startX_to = 150;
    $startX_earned = 163.5;
    $startX_year_graduated = 183;
    $startX_honors = 194.2;
    $startY_school_basic = 278.0;
    $startY_other = 279.0;
    $lineHeight = 6;

    $isEmpty = !$this->hasAnyRowData((array) $chunk, ['school', 'basic', 'from', 'to', 'earned', 'year_graduated', 'academic_honors']);

    // If all are empty, write N/A in the first-row cells.
    if ($isEmpty) {
        $this->writeFittedAt($pdf, 'N/A', 62, 278.5, 50, 8.0, 5.0); // School
        $this->writeFittedAt($pdf, 'N/A', 110, 278.5, 45, 8.0, 4.5); // Basic Education
        $this->writeFittedAt($pdf, 'N/A', 137.5, 279.0, 27, 8.0, 5.0); // From
        $this->writeFittedAt($pdf, 'N/A', 150.5, 279.0, 31.5, 8.0, 5.0); // To
        $this->writeFittedAt($pdf, 'N/A', 166, 279.0, 18, 8.0, 5.0); // Earned
        $this->writeFittedAt($pdf, 'N/A', 183, 279.0, 12, 8.0, 5.0); // Year Graduated
        $this->writeFittedAt($pdf, 'N/A', 200, 279.0, 13, 8.0, 5.0); // Academic Honors
        return;
    }

    // Otherwise, loop and write each row
    foreach ($chunk as $index => $voc) {
        $rowOffset = $index * $lineHeight;
        $currentYSchoolBasic = $startY_school_basic + $rowOffset;
        $currentYOther = $startY_other + $rowOffset;

        $this->writeFittedAt($pdf, $this->valueOrNa($voc['school'] ?? null), 40.5, $currentYSchoolBasic, 48, 6.5, 4.5);

        $this->writeFittedAt($pdf, $this->valueOrNa($voc['basic'] ?? null), 90, $currentYSchoolBasic, 45, 6.5, 4.5);

        $this->writeFittedAt($pdf, $this->dateOrNa($voc['from'] ?? null, 'm/Y'), 136, $currentYOther, 27, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->dateOrNa($voc['to'] ?? null, 'm/Y'), 149, $currentYOther, 31.5, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($voc['earned'] ?? null), 166, $currentYOther, 18, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($voc['year_graduated'] ?? null), 182, $currentYOther, 12, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($voc['academic_honors'] ?? null), 199, $currentYOther, 8, 7.0, 5.0);
    }
}


private function writeCollegeChunk($pdf, $chunk)
{
    $startX_school = 41.5;
    $startX_basic = 91;
    $startX_earned = 163.5;
    $startX_year_graduated = 183;
    $startX_honors = 194.2;
    $startY_school_basic = 285.0;
    $startY_other = 286.5;
    $lineHeight = 6;

    $isEmpty = !$this->hasAnyRowData((array) $chunk, ['school', 'basic', 'from', 'to', 'earned', 'year_graduated', 'academic_honors']);

    // If all are empty, write N/A in the first-row cells.
    if ($isEmpty) {
        $this->writeFittedAt($pdf, 'N/A', 62, 286, 50, 8.0, 5.0); // School
        $this->writeFittedAt($pdf, 'N/A', 110, 286, 45, 8.0, 4.5); // Basic Education
        $this->writeFittedAt($pdf, 'N/A', 137.5, 286.5, 27, 8.0, 5.0); // From
        $this->writeFittedAt($pdf, 'N/A', 150.5, 286.5, 31.5, 8.0, 5.0); // To
        $this->writeFittedAt($pdf, 'N/A', 166, 286.5, 18, 8.0, 5.0); // Earned
        $this->writeFittedAt($pdf, 'N/A', 183, 286.5, 12, 8.0, 5.0); // Year Graduated
        $this->writeFittedAt($pdf, 'N/A', 200, 286.5, 13, 8.0, 5.0); // Academic Honors
        return;
    }

    // Otherwise, render the college entries normally
    foreach ($chunk as $index => $college) {
        $rowOffset = $index * $lineHeight;
        $currentYSchoolBasic = $startY_school_basic + $rowOffset;
        $currentYOther = $startY_other + $rowOffset;

        $this->writeFittedAt($pdf, $this->valueOrNa($college['school'] ?? null), 40.5, $currentYSchoolBasic, 48, 6.5, 4.5);

        $this->writeFittedAt($pdf, $this->valueOrNa($college['basic'] ?? null), 90, $currentYSchoolBasic, 45, 6.5, 4.5);

        $this->writeFittedAt($pdf, $this->dateOrNa($college['from'] ?? null, 'm/Y'), 136, $currentYOther, 27, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->dateOrNa($college['to'] ?? null, 'm/Y'), 149, $currentYOther, 31.5, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($college['earned'] ?? null), 166, $currentYOther, 18, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($college['year_graduated'] ?? null), 182, $currentYOther, 12, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($college['academic_honors'] ?? null), 199, $currentYOther, 8, 7.0, 5.0);
    }
}


private function writeGraduateChunk($pdf, $chunk)
{
    $startX_school = 41.5;
    $startX_basic = 91;
    $startX_from = 137;
    $startX_to = 150;
    $startX_earned = 163.5;
    $startX_year_graduated = 183;
    $startX_honors = 194.2;
    $startY_school = 293.0;
    $startY_basic = 292.0;
    $startY_other = 294.0;
    $lineHeight = 6;

    $isEmpty = !$this->hasAnyRowData((array) $chunk, ['school', 'basic', 'from', 'to', 'earned', 'year_graduated', 'academic_honors']);

   
    if ($isEmpty) {
        $this->writeFittedAt($pdf, 'N/A', 62, 295, 50, 8.0, 5.0); // School
        $this->writeFittedAt($pdf, 'N/A', 110, 294.0, 45, 8.0, 4.5); // Basic Education
        $this->writeFittedAt($pdf, 'N/A', 137.5, 294.0, 11, 8.0, 5.0); // From
        $this->writeFittedAt($pdf, 'N/A', 150.5, 294.0, 11, 8.0, 5.0); // To
        $this->writeFittedAt($pdf, 'N/A', 166, 294.0, 18, 8.0, 5.0); // Earned
        $this->writeFittedAt($pdf, 'N/A', 183, 294.0, 12, 8.0, 5.0); // Year Graduated
        $this->writeFittedAt($pdf, 'N/A', 199, 294.0, 10.2, 8.0, 5.0); // Academic Honors
        return;
    }

    // Otherwise, loop through and write each graduate row
    foreach ($chunk as $index => $grad) {
        $rowOffset = $index * $lineHeight;
        $currentYSchool = $startY_school + $rowOffset;
        $currentYBasic = $startY_basic + $rowOffset;
        $currentYOther = $startY_other + $rowOffset;

        $this->writeFittedAt($pdf, $this->valueOrNa($grad['school'] ?? null), 40.5, $currentYSchool, 48, 6.5, 4.5);

        $this->writeFittedAt($pdf, $this->valueOrNa($grad['basic'] ?? null), 90, $currentYBasic, 45, 6.5, 4.5);

        $this->writeFittedAt($pdf, $this->dateOrNa($grad['from'] ?? null, 'm/Y'), 136, $currentYOther, 11, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->dateOrNa($grad['to'] ?? null, 'm/Y'), 149, $currentYOther, 11, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($grad['earned'] ?? null), 166, $currentYOther, 18, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($grad['year_graduated'] ?? null), 182, $currentYOther, 12, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($grad['academic_honors'] ?? null), 199, $currentYOther, 10.2, 5.0, 5.0);
    }
}



private function writeCivilServiceEligibilityChunk($pdf, $chunk)
{
    $startX_career = 8.0;
    $startX_rating = 71.0;
    $startX_date = 94.0;
    $startX_place = 118.0;
    $startX_license = 145.0;
    $startX_validity = 184.0;
    $endX_validity = 201.5;

    // Re-anchor to the 2025 short-bond grid to prevent left/right spillover.
    if ($this->isShortBondTemplate) {
        $startX_career = 20.5;
        $startX_rating = 83.0;
        $startX_date = 104.7;
        $startX_place = 127.8;
        $startX_license = 157.5;
        $startX_validity = 176.4;
        $endX_validity = 195.9;
    }

    $startY = 26;
    $rowHeight = 7;
    $cellInset = 0.5;

    $careerWidth = max(1.0, ($startX_rating - $startX_career) - $cellInset);
    $ratingWidth = max(1.0, ($startX_date - $startX_rating) - $cellInset);
    $dateWidth = max(1.0, ($startX_place - $startX_date) - $cellInset);
    $placeWidth = max(1.0, ($startX_license - $startX_place) - $cellInset);
    $licenseWidth = max(1.0, ($startX_validity - $startX_license) - $cellInset);
    $validityWidth = max(1.0, ($endX_validity - $startX_validity) - $cellInset);

    $isEmpty = !$this->hasCivilServiceData((array) $chunk);

    // If all fields are empty, write N/A in the first row cells.
    if ($isEmpty) {
        $this->writeWrapped($pdf, 'N/A', $careerWidth, 35, 24.5, 24.0, 8.0, 3.0); // Career Eligibility
        $this->writeFittedAt($pdf, 'N/A', 79, 24.5, $ratingWidth, 8.0, 5.0); // Rating
        $this->writeFittedAt($pdf, 'N/A', 99, 24.5, $dateWidth, 8.0, 3.6); // Date
        $this->writeFittedAt($pdf, 'N/A', 126, 24.5, $placeWidth, 8.0, 4.5); // Place
        $this->writeFittedAt($pdf, 'N/A', 153.5, 24.5, $licenseWidth, 8.0, 5.0); // License
        $this->writeFittedAt($pdf, 'N/A', 188, 24.5, $validityWidth, 8.0, 3.6); // Validity
        return;
    }

    foreach ($chunk as $index => $cse) {
        $currentY = $startY + ($index * $rowHeight);

        $this->writeWrapped(
            $pdf,
            $this->valueOrNa($cse['cs_eligibility_career'] ?? null),
            $careerWidth,
            7,
            24.5,
            $currentY - 0.5,
            7.0,
            3.0
        );

        $this->writeFittedAt($pdf, $this->valueOrNa($cse['cs_eligibility_rating'] ?? null), 80, 24.5, $ratingWidth, 8.0, 5.0);
        $this->writeFittedAt($pdf, $this->dateOrNa($cse['cs_eligibility_date'] ?? null), 95, 24.5, $dateWidth, 8.0, 3.6);
        $this->writeFittedAt($pdf, $this->valueOrNa($cse['cs_eligibility_place'] ?? null), 125, 24.5, $placeWidth, 7.0, 4.5);
        $this->writeFittedAt($pdf, $this->valueOrNa($cse['cs_eligibility_license'] ?? null), 153.5, 24.5, $licenseWidth, 8.0, 5.0);
        $this->writeFittedAt($pdf, $this->dateOrNa($cse['cs_eligibility_validity'] ?? null), 183, 24.5, $validityWidth, 8.0, 3.6);
    }
}

private function writeWorkExperienceChunk($pdf, $chunk)
{
    $x_from = 5;
    $x_to = 22;
    $x_position = 40.132;
    $x_agency = 94.488;
    $x_status = 150;
    $x_gov = 187.0;
    $x_gov_end = 199.5;

    if ($this->isShortBondTemplate) {
        $x_from = 19.5;
        $x_to = 36.5;
        $x_position = 53.1;
        $x_agency = 104.7;
        $x_status = 157.5;
        $x_gov = 176.4;
        $x_gov_end = 195.9;
    }

    $startY = 103.5;
    $rowHeight = 6.5;
    $cellInset = 0.5;

    $fromWidth = max(1.0, ($x_to - $x_from) - $cellInset);
    $toWidth = max(1.0, ($x_position - $x_to) - $cellInset);
    $positionWidth = max(1.0, ($x_agency - $x_position) - $cellInset);
    $agencyWidth = max(1.0, ($x_status - $x_agency) - $cellInset);
    $statusWidth = max(1.0, ($x_gov - $x_status) - $cellInset);
    $govWidth = max(1.0, ($x_gov_end - $x_gov) - $cellInset);

    $isEmpty = !$this->hasWorkExperienceData((array) $chunk);

    // If all are empty, write N/A in the first row cells.
    if ($isEmpty) {
        $this->writeFittedAt($pdf, 'N/A', 9, 102, $fromWidth, 8.0, 3.6); // From
        $this->writeFittedAt($pdf, 'N/A', 27, 102, $toWidth, 8.0, 3.6); // To
        $this->writeFittedAt($pdf, 'N/A', 63, 102, $positionWidth, 8.0, 5.0); // Position
        $this->writeWrapped($pdf, 'N/A', $agencyWidth, 115, 102, 100.5, 6.0, 2.0); // Agency
        $this->writeFittedAt($pdf, 'N/A', 153, 102, $statusWidth, 8.0, 4.5); // Status
        $this->writeFittedAt($pdf, 'N/A', 189.5, 102, $govWidth, 8.0, 5.0); // Government Service
        return;
    }

    foreach ($chunk as $index => $we) {
        $currentY = $startY + ($index * $rowHeight);

        $this->writeFittedAt($pdf, $this->dateOrNa($we['work_exp_from'] ?? null), 5, 102, $fromWidth, 8.0, 3.6);
        $this->writeFittedAt($pdf, $this->dateOrNa($we['work_exp_to'] ?? null), $x_to, 102, $toWidth, 8.0, 3.6);
        $this->writeFittedAt($pdf, $this->valueOrNa($we['work_exp_position'] ?? null), $x_position, 102, $positionWidth, 7.0, 5.0);

        $this->writeWrapped(
            $pdf,
            $this->valueOrNa($we['work_exp_department'] ?? null),
            $agencyWidth,
            $x_agency,
            102,
            $currentY - 1.5,
            6.0,
            2.0
        );

        $this->writeFittedAt($pdf, $this->valueOrNa($we['work_exp_status'] ?? null), 147.5, 102, $statusWidth, 8.0, 4.5);
        $this->writeFittedAt($pdf, $this->normalizeGovServiceFlag($we['work_exp_govt_service'] ?? null, 'N/A'), 189.5, 102 , $govWidth, 8.0, 5.0);
    }
}

private function writeVoluntaryWorkChunk($pdf, $chunk)
{
    // Column X positions
    $x_org = 7.5;
    $x_from = 95;
    $x_to = 110.5;
    $x_hours = 130;
    $x_position = 143;

    // Starting Y coordinate
    $startY = 30;
    $rowHeight = 7.25;

    $isEmpty = !$this->hasAnyRowData((array) $chunk, ['voluntary_org', 'voluntary_from', 'voluntary_to', 'voluntary_hours', 'voluntary_position']);

    // If all fields are empty, write N/A in the first-row cells.
    if ($isEmpty) {
        $this->writeWrapped($pdf, 'N/A', 115, 6, $startY, $startY - 1.0, 6, 2); // Organization
        $this->writeFittedAt($pdf, 'N/A', 94.5, $startY, 15.0, 8.0, 5.0); // From
        $this->writeFittedAt($pdf, 'N/A', 110.5, $startY, 19.0, 8.0, 5.0); // To
        $this->writeFittedAt($pdf, 'N/A', 129, $startY, 12.0, 7.0, 5.0); // Hours
        $this->writeWrapped($pdf, 'N/A', 60, 142, $startY, $startY - 1.0, 7, 3); // Position
        return;
    }
    // Render each voluntary work row
    foreach ($chunk as $index => $vw) {
        $currentY = $startY + ($index * $rowHeight);
        $multiLineY = $currentY - 1.0;

        $this->writeWrapped($pdf, $this->valueOrNa($vw['voluntary_org'] ?? null), 115, 6, $currentY, $multiLineY, 6, 2);

        $this->writeFittedAt($pdf, $this->dateOrNa($vw['voluntary_from'] ?? null), 94.5, $currentY, 15.0, 8.0, 5.0);

        $this->writeFittedAt($pdf, $this->dateOrNa($vw['voluntary_to'] ?? null), 110.5, $currentY, 19.0, 8.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($vw['voluntary_hours'] ?? null), 129, $currentY, 12.0, 7.0, 5.0);

        $this->writeWrapped($pdf, $this->valueOrNa($vw['voluntary_position'] ?? null), 60, 145, $currentY, $multiLineY, 7, 3);
    }
}

private function writeLearningAndDevelopmentChunk($pdf, $chunk)
{
    // Column X positions
    $x_title = 7;
    $x_from = 95;
    $x_to = 110.5;
    $x_hours = 130;
    $x_type = 142;
    $x_conducted = 161;

    $startY = 103;
    $rowHeight = 6.4;

    $isEmpty = !$this->hasAnyRowData((array) $chunk, ['learning_title', 'learning_type', 'learning_from', 'learning_to', 'learning_hours', 'learning_conducted']);

    // If all fields are empty, write N/A in the first-row cells.
    if ($isEmpty) {
        $this->writeWrapped($pdf, 'N/A', 105, 6, $startY, $startY - 1.0, 7.0, 2);
        $this->writeFittedAt($pdf, 'N/A', 94.5, $startY, 15.0, 8.0, 5.0);
        $this->writeFittedAt($pdf, 'N/A', 110.5, $startY, 19.0, 8.0, 5.0);
        $this->writeFittedAt($pdf, 'N/A', $x_hours, $startY, 12.0, 7.0, 5.0);
        $this->writeFittedAt($pdf, 'N/A', $x_type, $startY, 18.0, 7.0, 5.0);
        $this->writeWrapped($pdf, 'N/A', 47.3, $x_conducted - 2, $startY, $startY - 1.0, 7.0, 2);
        return;
    }

    // Otherwise, render the actual data
    foreach ($chunk as $index => $lnd) {
        $currentY = $startY + ($index * $rowHeight);
        $multiLineY = $currentY - 1.0;

        $this->writeWrapped($pdf, $this->valueOrNa($lnd['learning_title'] ?? null), 105, 6, 102.5, $multiLineY, 7.0, 2);

        $this->writeFittedAt($pdf, $this->valueOrNa($lnd['learning_type'] ?? null), 146, 102.5, 18.0, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->dateOrNa($lnd['learning_from'] ?? null), $x_from - 0.5, 102.5, 15.0, 8.0, 5.0);

        $this->writeFittedAt($pdf, $this->dateOrNa($lnd['learning_to'] ?? null), $x_to, 102.5, 19.0, 8.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($lnd['learning_hours'] ?? null), 129, 102.5, 12.0, 8.0, 5.0);

        $this->writeWrapped($pdf, $this->valueOrNa($lnd['learning_conducted'] ?? null), 47.3, 160,  102.5, $currentY, $multiLineY, 7.0, 2);
    }
}



private function writeOtherInformation($pdf, $skills, $distinctions, $organizations)
{
    // Column anchors (short-bond 2025 layout)
    $wSkill = 50.0;
    $wDistinction = 98.0;
    $wOrg = 41.5;
    $startY = 256.0;
    $rowHeight = 6.15;

    $skills = array_values(array_filter(array_map(fn($v) => trim((string) $v), (array) $skills), fn($v) => $v !== ''));
    $distinctions = array_values(array_filter(array_map(fn($v) => trim((string) $v), (array) $distinctions), fn($v) => $v !== ''));
    $organizations = array_values(array_filter(array_map(fn($v) => trim((string) $v), (array) $organizations), fn($v) => $v !== ''));

    if (empty($skills) && empty($distinctions) && empty($organizations)) {
        $this->writeCenteredFitted($pdf, 'N/A', 7, 256, $wSkill);
        $this->writeCenteredFitted($pdf, 'N/A', 60, 256, $wDistinction);
        $this->writeCenteredFitted($pdf, 'N/A', 165, 256, $wOrg);
        return;
    }

    for ($i = 0; $i < 7; $i++) {
        $currentY = $startY + ($i * $rowHeight);
        $this->writeFittedAt($pdf, $skills[$i] ?? '', 6, $currentY, $wSkill, 7.0, 5.0);
        $this->writeFittedAt($pdf, $distinctions[$i] ?? '', 60, $currentY, $wDistinction, 7.0, 5.0);
        $this->writeFittedAt($pdf, $organizations[$i] ?? '', 160, $currentY, $wOrg, 6.5, 5.0);
    }
}



private function WriteC4Information($pdf, $userId)
{
    $misc = MiscInfos::where('user_id', $userId)->first();

    if ($misc && $misc->photo_upload) {
        $photoPath = storage_path('app/public/' . $misc->photo_upload);
        if (file_exists($photoPath)) {
            $pdf->Image($photoPath, 169.32, 194.15, 33.6, 37.32);
        }
    }

    // Leave photo box blank when no uploaded photo is available.

    if (!$misc) return;

    $criminalDetailsRaw = $misc->criminal_35_b ?? '';
    $criminalDetails = explode(',', $criminalDetailsRaw);

    $dateFiledRaw = trim($criminalDetails[0] ?? '');
    $caseStatusRaw = isset($criminalDetails[1]) ? implode(',', array_slice($criminalDetails, 1)) : ''; // In case the status contains commas

    $dateFiled = '';
    try {
        if ($dateFiledRaw && strtolower($dateFiledRaw) !== 'no') {
            $dateFiled = Carbon::parse($dateFiledRaw)->format('m/d/Y');
        }
    } catch (\Exception $e) {
        $dateFiled = '';
    }


        if ($dateFiledRaw && strtolower($dateFiledRaw) !== 'no') {
            try {
                $dateFiled = Carbon::parse($dateFiledRaw)->format('m/d/Y');
            } catch (\Exception $e) {
                $dateFiled = ''; // or log the error if needed
            }
        }

        $info = [
            'third_degree'         => $misc->related_34_a,
            'fourth_degree'        => strtolower($misc->related_34_b) === 'no' ? ['No', ''] : ['Yes', $misc->related_34_b],
            'guilty'               => strtolower($misc->guilty_35_a) === 'no' ? ['No', ''] : ['Yes', $misc->guilty_35_a],
            'charged' => strtolower($criminalDetailsRaw) === 'no'
                                ? ['No', '', '']
                                : ['Yes', $dateFiled, $caseStatusRaw],
            'convicted'            => strtolower($misc->convicted_36) === 'no' ? ['No', ''] : ['Yes', $misc->convicted_36],
            'separated'            => strtolower($misc->separated_37) === 'no' ? ['No', ''] : ['Yes', $misc->separated_37],
            'candidate'            => strtolower($misc->candidate_38) === 'no' ? ['No', ''] : ['Yes', $misc->candidate_38],
            'resigned'             => strtolower($misc->resigned_38_b) === 'no' ? ['No', ''] : ['Yes', $misc->resigned_38_b],
            'immigrant'            => strtolower($misc->immigrant_39) === 'no' ? ['No', ''] : ['Yes', $misc->immigrant_39],
            'indigenous'           => strtolower($misc->indigenous_40_a) === 'no' ? ['No', ''] : ['Yes', $misc->indigenous_40_a],
            'disability'           => strtolower($misc->pwd_40_b) === 'no' ? ['No', ''] : ['Yes', $misc->pwd_40_b],
            'solo_parent'          => strtolower($misc->solo_parent_40_c) === 'no' ? ['No', ''] : ['Yes', $misc->solo_parent_40_c],

            // References
            'references' => [
                ['name' => $misc->ref1_name, 'address' => $misc->ref1_address, 'tel' => $misc->ref1_tel],
                ['name' => $misc->ref2_name, 'address' => $misc->ref2_address, 'tel' => $misc->ref2_tel],
                ['name' => $misc->ref3_name, 'address' => $misc->ref3_address, 'tel' => $misc->ref3_tel],
            ],

            // IDs
            'govt_id'    => $misc->govt_id_type ?? '',
            'other_id'   => $misc->govt_id_number ?? '',
            'issue_place'=> $misc->govt_id_place_issued ?? '',
            'issue_date' => $misc->govt_id_date_issued ?? '',
        ];

        $checkboxes = [
            'third_degree'  => ['yes' => [139.5, 24.4], 'no' => [161, 24.7]],
            'fourth_degree' => ['yes' => [139.5, 30], 'no' => [160.8, 30]],
            'guilty'        => ['yes' => [138.7, 46],   'no' => [161.5, 46]],
            'convicted'     => ['yes' => [138.7, 83.5], 'no' => [164, 83]],
            'charged'       => ['yes' => [138.7, 62.5], 'no' => [162.5, 62.6]],
            'separated'     => ['yes' => [138.7, 99.5],'no' => [163.5, 99.3]],
            'candidate'     => ['yes' => [138.7, 113],    'no' => [165.5, 113]],
            'resigned'      => ['yes' => [139.5, 123],'no' => [166.3, 123.3]],
            'immigrant'     => ['yes' => [138.7, 135],    'no' => [165.5, 135]],
            'indigenous'    => ['yes' => [138.7, 163],  'no' => [166, 163]],
            'disability'    => ['yes' => [138.7, 171],  'no' => [166, 171.5]],
            'solo_parent'   => ['yes' => [138.7, 180.4],  'no' => [166, 180.5]],
        ];

    foreach ($checkboxes as $key => $coord) {
        $answer = ($checkboxStates[$key] ?? false) ? 'yes' : 'no';
        if (!isset($coord[$answer])) {
            continue;
        }
        [$x, $y] = $coord[$answer];
        $this->markCheckbox($pdf, (float) $x, (float) $y);
    }

    $this->setFont($pdf, 'Arial', '', 8);

        // Detail fields
        $this->writeFittedAt($pdf, (string) ($info['fourth_degree'][1] ?? ''), 141.224, 40, 56, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['guilty'][1] ?? ''), 141.224, 56, 56, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['charged'][1] ?? ''), 163, 73, 40, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['charged'][2] ?? ''), 163, 77, 40, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['convicted'][1] ?? ''), 141.224, 93, 56, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['separated'][1] ?? ''), 141.224, 107, 56, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['candidate'][1] ?? ''), 165, 118, 40, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['resigned'][1] ?? ''), 163.5, 129, 40, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['immigrant'][1] ?? ''), 141.224, 144, 62, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['indigenous'][1] ?? ''), 177, 168, 26, 6.5, 4.5);
        $this->writeFittedAt($pdf, (string) ($info['disability'][1] ?? ''), 177, 177, 26, 6.5, 4.5);
        $this->writeFittedAt($pdf, (string) ($info['solo_parent'][1] ?? ''), 177, 185, 26, 6.5, 4.5);

        // Reference table
        $x_name = 8.0;
        $x_address = 87.0;
        $x_telno = 134.0;
        $y_refs = [206, 213, 220];

        foreach ($info['references'] as $i => $ref) {
            if ($i >= count($y_refs)) break;
            $y = $y_refs[$i];
            $this->writeFittedAt($pdf, $this->valueOrNa($ref['name'] ?? null), $x_name, $y, 58, 7.5, 5.0);
            $this->writeFittedAt($pdf, $this->valueOrNa($ref['address'] ?? null), $x_address, $y, 58, 7.5, 5.0);
            $this->writeFittedAt($pdf, $this->valueOrNa($ref['tel'] ?? null), $x_telno, $y, 20, 7.5, 5.0);
        }

        // ID Section
        $this->writeFittedAt($pdf, $this->valueOrNa($info['govt_id'] ?? null), 31, 262.5, 58, 7.5, 5.0); // Govt ID type
        $this->writeFittedAt($pdf, $this->valueOrNa($info['other_id'] ?? null), 32, 269, 58, 7.5, 5.0); // Govt ID number

        $issuedText = $this->formatGovtIssuePlaceAndDate(
            $info['issue_place'] ?? '',
            $info['issue_date'] ?? ''
        );
        $this->writeFittedAt($pdf, $issuedText, 32, 276, 86, 7.5, 4.6);

    // Leave oath/signature/thumbmark placeholders blank when data is unavailable.
}

private function hasAffirmativeSelection($value): bool
{
    $normalized = $this->normalizedValue($value);
    if ($normalized === '') {
        return false;
    }

    return !in_array($normalized, ['no', 'n', '0', 'false', 'null', 'n/a', 'na'], true);
}

private function affirmativeDetail($value): string
{
    if (!$this->hasAffirmativeSelection($value)) {
        return '';
    }

    return $this->normalizeScalarText($value);
}

private function parseCriminal35B($misc): array
{
    $raw = $this->normalizeScalarText($misc->criminal_35_b ?? '');
    if (!$this->hasAffirmativeSelection($raw)) {
        return [
            'has_case' => false,
            'date' => '',
            'status' => '',
        ];
    }

    $parts = explode(',', $raw);
    $dateRaw = trim((string) ($parts[0] ?? ''));
    $statusRaw = isset($parts[1]) ? trim((string) implode(',', array_slice($parts, 1))) : '';

    $dateText = '';
    if ($dateRaw !== '' && !$this->valueMatches($dateRaw, 'no', 'n', 'n/a', 'na', 'null')) {
        try {
            $dateText = Carbon::parse($dateRaw)->format('m/d/Y');
        } catch (\Throwable $e) {
            if ($statusRaw === '') {
                $statusRaw = $dateRaw;
            } else {
                $dateText = $dateRaw;
            }
        }
    }

    return [
        'has_case' => true,
        'date' => $dateText,
        'status' => $statusRaw,
    ];
}


// Move writeCentered to class method for cleaner passing
private function getWriteCentered()
{
    return function ($pdf, $text, $x, $y, $width)
    {
        $this->writeCenteredFitted($pdf, (string) $text, (float) $x, (float) $y, (float) $width);
    };
}

private function markCheckbox($pdf, float $x, float $y): void
{
    // Use a vector-drawn mark so checkboxes stay centered regardless of font/renderer.
    $isPage4 = $this->currentTemplatePage === 4;
    $insetX = $isPage4 ? -0.05 : 0.30;
    $insetY = $isPage4 ? 0.45 : 0.22;
    $size = $isPage4 ? 1.05 : 1.00;

    $this->setXY($pdf, $x + $insetX, $y + $insetY);
    $x1 = $pdf->GetX();
    $y1 = $pdf->GetY();

    $this->setXY($pdf, $x + $insetX + $size, $y + $insetY + $size);
    $x2 = $pdf->GetX();
    $y2 = $pdf->GetY();

    $this->setXY($pdf, $x + $insetX + $size, $y + $insetY);
    $x3 = $pdf->GetX();
    $y3 = $pdf->GetY();

    $this->setXY($pdf, $x + $insetX, $y + $insetY + $size);
    $x4 = $pdf->GetX();
    $y4 = $pdf->GetY();

    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.25);
    $pdf->Line($x1, $y1, $x2, $y2);
    $pdf->Line($x3, $y3, $x4, $y4);
}

private function getPageXScale(): float
{
    return $this->xScale;
}

private function getEffectiveMaxWidth(float $maxWidth): float
{
    return max(1.0, $maxWidth * $this->getPageXScale());
}

private function fitTextToWidth($pdf, string $text, float $maxWidth, float $baseSize = 8.0, float $minSize = 5.0): array
{
    $effectiveMaxWidth = $this->getEffectiveMaxWidth($maxWidth);
    $display = trim($text);
    if ($display === '') {
        return ['', $baseSize, 0.0];
    }

    for ($size = $baseSize; $size >= $minSize; $size -= 0.5) {
        $this->setFont($pdf, 'Arial', '', $size);
        $width = $pdf->GetStringWidth($display);
        if ($width <= $effectiveMaxWidth) {
            return [$display, $size, $width];
        }
    }

    $this->setFont($pdf, 'Arial', '', $minSize);
    $display = $this->truncateToWidth($pdf, $display, $effectiveMaxWidth);
    return [$display, $minSize, $pdf->GetStringWidth($display)];
}

private function fitTextToLines(
    $pdf,
    string $text,
    float $maxWidth,
    float $baseSize = 8.0,
    float $minSize = 5.0,
    int $maxLines = 2
): array {
    $display = trim($text);
    if ($display === '') {
        return [[], max($baseSize, $minSize), $this->getEffectiveMaxWidth($maxWidth)];
    }

    $effectiveMaxWidth = $this->getEffectiveMaxWidth($maxWidth);
    $startSize = max((float) $baseSize, (float) $minSize);
    $endSize = max(4.5, (float) $minSize);
    $chosenLines = [$display];
    $chosenSize = $startSize;

    for ($size = $startSize; $size >= $endSize; $size -= 0.5) {
        $this->setFont($pdf, 'Arial', '', $size);
        $lines = $this->splitTextByWidth($pdf, $display, $effectiveMaxWidth);
        $chosenLines = $lines;
        $chosenSize = $size;

        if (count($lines) <= $maxLines) {
            break;
        }
    }

    if (count($chosenLines) > $maxLines) {
        $chosenLines = array_slice($chosenLines, 0, $maxLines);
        $chosenLines[$maxLines - 1] = $this->appendEllipsisToFit(
            $pdf,
            $chosenLines[$maxLines - 1],
            $effectiveMaxWidth
        );
    }

    return [$chosenLines, $chosenSize, $effectiveMaxWidth];
}

private function truncateToWidth($pdf, string $text, float $maxWidth): string
{
    $candidate = trim($text);
    if ($candidate === '') {
        return '';
    }
    if ($pdf->GetStringWidth($candidate) <= $maxWidth) {
        return $candidate;
    }

    $ellipsis = '...';
    while (mb_strlen($candidate) > 1) {
        $candidate = rtrim(mb_substr($candidate, 0, mb_strlen($candidate) - 1));
        $trial = $candidate . $ellipsis;
        if ($pdf->GetStringWidth($trial) <= $maxWidth) {
            return $trial;
        }
    }

    return $ellipsis;
}

private function appendEllipsisToFit($pdf, string $text, float $maxWidth): string
{
    $candidate = rtrim($text);
    $ellipsis = '...';

    if ($candidate === '') {
        return $ellipsis;
    }

    while (mb_strlen($candidate) > 0) {
        $trial = rtrim($candidate) . $ellipsis;
        if ($pdf->GetStringWidth($trial) <= $maxWidth) {
            return $trial;
        }
        $candidate = mb_substr($candidate, 0, mb_strlen($candidate) - 1);
    }

    return $ellipsis;
}

private function writeCenteredFitted($pdf, string $text, float $x, float $y, float $width): void
{
    $this->writeCenteredFittedSized($pdf, $text, $x, $y, $width, 8.0, 5.0);
}

private function writeCenteredFittedSized($pdf, string $text, float $x, float $y, float $width, float $baseSize, float $minSize): void
{
    $text = mb_strtoupper($text);
    $maxWidth = max(1.0, $width - 0.5);
    [$lines, $size, $effectiveWidth] = $this->fitTextToLines($pdf, $text, $maxWidth, $baseSize, $minSize, 2);
    if (empty($lines)) {
        $this->setFont($pdf, 'Arial', '', 8);
        return;
    }

    $this->setFont($pdf, 'Arial', '', $size);
    $lineHeight = count($lines) > 1 ? max(1.8, $size * 0.32) : 0.0;
    $currentY = $y - ((count($lines) - 1) * $lineHeight * 0.45);
    $pageXScale = $this->getPageXScale();

    foreach ($lines as $line) {
        $lineWidth = $pdf->GetStringWidth($line);
        $leftPadding = max(0.0, (($effectiveWidth - $lineWidth) / 2) / $pageXScale);
        $centerX = $x + $leftPadding;
        $this->setXY($pdf, $centerX, $currentY);
        $pdf->Cell($lineWidth, 0, $line, 0, 0, 'L');
        $currentY += $lineHeight;
    }

    $this->setFont($pdf, 'Arial', '', 8);
}

private function writeFittedAt($pdf, string $text, float $x, float $y, float $maxWidth, float $baseSize = 8.0, float $minSize = 5.0): void
{
    $text = mb_strtoupper($text);
    [$lines, $size, $effectiveWidth] = $this->fitTextToLines($pdf, $text, $maxWidth, $baseSize, $minSize, 2);
    if (empty($lines)) {
        $this->setFont($pdf, 'Arial', '', 8);
        return;
    }

    $this->setFont($pdf, 'Arial', '', $size);
    $lineHeight = count($lines) > 1 ? max(1.8, $size * 0.32) : 0.0;
    $currentY = $y - ((count($lines) - 1) * $lineHeight * 0.45);
    foreach ($lines as $line) {
        $this->setXY($pdf, $x, $currentY);
        // Keep each rendered line inside the target cell width.
        $pdf->Cell($effectiveWidth, 0, $line, 0, 0, 'L');
        $currentY += $lineHeight;
    }
    $this->setFont($pdf, 'Arial', '', 8);
}

private function writeAt($pdf, string $text, float $x, float $y, ?float $maxWidth = null): void
{
    $text = mb_strtoupper($text);
    if ($maxWidth !== null) {
        $this->writeFittedAt($pdf, $text, $x, $y, $maxWidth);
        return;
    }

    $this->setXY($pdf, $x, $y);
    $pdf->Write(0, $text);
}


private function writeWrapped($pdf, $text, $maxWidth, $x, $ySingle, $yMultiple, $font_size, $lineHeight)
{
    $text = mb_strtoupper(trim((string) $text));
    if ($text === '') {
        return;
    }
    $maxWidth = $this->getEffectiveMaxWidth((float) $maxWidth);

    $minFont = 5.0;
    $targetLines = 3;

    // Try full single-line size first.
    $this->setFont($pdf, 'Arial', '', 8);
    if ($pdf->GetStringWidth($text) <= $maxWidth) {
        $this->setXY($pdf, $x, $ySingle);
        $pdf->Write(0, $text);
        $this->setFont($pdf, 'Arial', '', 8);
        return;
    }

    $chosenLines = [];
    $chosenSize = max($minFont, (float) $font_size);
    for ($size = max($minFont, (float) $font_size); $size >= $minFont; $size -= 0.5) {
        $this->setFont($pdf, 'Arial', '', $size);
        $lines = $this->splitTextByWidth($pdf, $text, $maxWidth);
        if (count($lines) <= $targetLines) {
            $chosenLines = $lines;
            $chosenSize = $size;
            break;
        }
        $chosenLines = $lines;
        $chosenSize = $size;
    }

    if (count($chosenLines) > $targetLines) {
        $chosenLines = array_slice($chosenLines, 0, $targetLines);
        $last = rtrim((string) end($chosenLines));
        $last = preg_replace('/[\\s\\.]+$/', '', $last);
        $chosenLines[$targetLines - 1] = $last . '...';
    }

    $this->setFont($pdf, 'Arial', '', $chosenSize);
    $currentY = count($chosenLines) === 1 ? $ySingle : $yMultiple;
    $effectiveLineHeight = count($chosenLines) > 2 ? ($lineHeight * 0.9) : $lineHeight;

    foreach ($chosenLines as $line) {
        $this->setXY($pdf, $x, $currentY);
        $pdf->Write(0, $line);
        $currentY += $effectiveLineHeight;
    }

    $this->setFont($pdf, 'Arial', '', 8);
}

private function writeWrappedAt(
    $pdf,
    $text,
    float $x,
    float $y,
    float $maxWidth,
    float $fontSize,
    float $lineHeight,
    float $multiLineYOffset = 1.5
): void {
    $this->writeWrapped(
        $pdf,
        $text,
        $maxWidth,
        $x,
        $y,
        $y - $multiLineYOffset,
        $fontSize,
        $lineHeight
    );
}

private function splitTextByWidth($pdf, string $text, float $maxWidth): array
{
    $words = preg_split('/\\s+/', trim($text)) ?: [];
    $lines = [];
    $currentLine = '';

    foreach ($words as $word) {
        $candidate = $currentLine === '' ? $word : ($currentLine . ' ' . $word);
        if ($pdf->GetStringWidth($candidate) <= $maxWidth) {
            $currentLine = $candidate;
            continue;
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
            $currentLine = '';
        }

        // Hard-wrap oversized single tokens.
        if ($pdf->GetStringWidth($word) > $maxWidth) {
            $buffer = '';
            foreach (str_split($word) as $char) {
                $next = $buffer . $char;
                if ($pdf->GetStringWidth($next) <= $maxWidth) {
                    $buffer = $next;
                } else {
                    if ($buffer !== '') {
                        $lines[] = $buffer;
                    }
                    $buffer = $char;
                }
            }
            $currentLine = $buffer;
        } else {
            $currentLine = $word;
        }
    }

    if ($currentLine !== '') {
        $lines[] = $currentLine;
    }

    return $lines;
}

private function tryExportViaExcelTemplate(
    Request $request,
    $personalInfo,
    $familyBackground,
    $educationalBackground,
    array $civilServiceRows,
    array $workExperienceRows,
    array $voluntaryRows,
    array $lndRows,
    $otherInfo,
    $miscInfo,
    array $residential,
    array $permanent,
    array $children,
    array $vocational,
    array $college,
    array $grad,
    array $skills,
    array $distinctions,
    array $organizations
): ?array {
    $excelTemplate = base_path('ANNEX H-1 - CS Form No. 212 Revised 2025 - Personal Data Sheet.xlsx');
    if (!file_exists($excelTemplate)) {
        return null;
    }

    try {
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $token = bin2hex(random_bytes(8));
        $jsonPath = $tempDir . DIRECTORY_SEPARATOR . "pds_excel_map_{$token}.json";

        $cellMap = $this->buildExcelCellMap(
            $personalInfo,
            $familyBackground,
            $educationalBackground,
            $civilServiceRows,
            $workExperienceRows,
            $voluntaryRows,
            $lndRows,
            $miscInfo,
            $residential,
            $permanent,
            $children,
            $vocational,
            $college,
            $grad,
            $skills,
            $distinctions,
            $organizations
        );
        file_put_contents($jsonPath, json_encode($cellMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $timestamp = date('Y-m-d_His');
        $filename = "ExportPDS_{$timestamp}.pdf";
        $outputPdf = storage_path("app/public/{$filename}");

        $ok = $this->runExcelPdfExport($excelTemplate, $jsonPath, $outputPdf);
        @unlink($jsonPath);
        if (!$ok || !file_exists($outputPdf)) {
            return null;
        }

        return [
            'path' => $outputPdf,
            'filename' => $filename,
        ];
    } catch (\Throwable $e) {
        Log::warning('Excel template PDS export failed; falling back to FPDI.', [
            'error' => $e->getMessage(),
        ]);
        return null;
    }
}

private function runExcelPdfExport(string $templateXlsx, string $jsonPath, string $outputPdf): bool
{
    $powershell = 'C:\\WINDOWS\\System32\\WindowsPowerShell\\v1.0\\powershell.exe';
    $scriptPath = base_path('scripts/export_excel_to_pdf.ps1');
    if (!file_exists($powershell) || !file_exists($scriptPath) || !file_exists($templateXlsx) || !file_exists($jsonPath)) {
        return false;
    }

    try {
        $process = new Process([
            $powershell,
            '-NoProfile',
            '-ExecutionPolicy',
            'Bypass',
            '-File',
            $scriptPath,
            '-TemplateXlsx',
            $templateXlsx,
            '-DataJson',
            $jsonPath,
            '-OutputPdf',
            $outputPdf,
        ]);
        $process->setTimeout(180);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::warning('Excel PDF export process failed.', [
                'exit_code' => $process->getExitCode(),
                'error_output' => $process->getErrorOutput(),
                'output' => $process->getOutput(),
            ]);
            return false;
        }

        return file_exists($outputPdf) && filesize($outputPdf) > 0;
    } catch (\Throwable $e) {
        Log::warning('Excel PDF export invocation error.', [
            'error' => $e->getMessage(),
        ]);
        return false;
    }
}

private function respondWithGeneratedPdfPath(Request $request, string $path, string $filename)
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $isMobile = preg_match('/Android|iPhone|iPad|iPod|webOS|BlackBerry|Windows Phone/i', $userAgent);
    $isDownload = $request->boolean('download');
    $isPrint = $request->boolean('print');
    $forceInline = $request->boolean('preview') || $isPrint;

    if ($isDownload) {
        return response()->download($path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    if ($isMobile && !$forceInline) {
        return redirect()
            ->route('dashboard_user')
            ->with('success', 'PDF generated successfully! You may download it from your dashboard.');
    }

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . $filename . '"',
    ]);
}

private function buildExcelCellMap(
    $personalInfo,
    $familyBackground,
    $educationalBackground,
    array $civilServiceRows,
    array $workExperienceRows,
    array $voluntaryRows,
    array $lndRows,
    $misc,
    array $residential,
    array $permanent,
    array $children,
    array $vocational,
    array $college,
    array $grad,
    array $skills,
    array $distinctions,
    array $organizations
): array {
    $map = [
        'C1' => [],
        'C2' => [],
        'C3' => [],
        'C4' => [],
    ];

    // C1
    $this->mapSet($map, 'C1', 'D10', $this->excelValue($personalInfo?->surname));
    $this->mapSet($map, 'C1', 'D11', $this->excelValue($personalInfo?->first_name));
    $this->mapSet($map, 'C1', 'D12', $this->excelValue($personalInfo?->middle_name));
    $this->mapSet($map, 'C1', 'L11', $this->excelValue($personalInfo?->name_extension));
    $this->mapSet($map, 'C1', 'D13', $this->excelDate($personalInfo?->date_of_birth));
    $this->mapSet($map, 'C1', 'D15', $this->excelValue($personalInfo?->place_of_birth));
    $this->mapSet($map, 'C1', 'D16', $this->excelTitle($personalInfo?->sex));
    $this->mapSet($map, 'C1', 'D17', $this->excelTitle($personalInfo?->civil_status));
    $this->mapSet($map, 'C1', 'J13', $this->excelValue($personalInfo?->citizenship));
    $this->mapSet($map, 'C1', 'L15', $this->excelValue($personalInfo?->dual_country));

    $this->mapSet($map, 'C1', 'D22', $this->excelValue($personalInfo?->height));
    $this->mapSet($map, 'C1', 'D24', $this->excelValue($personalInfo?->weight));
    $this->mapSet($map, 'C1', 'D25', $this->excelValue($personalInfo?->blood_type));
    $this->mapSet($map, 'C1', 'D27', $this->excelValue($personalInfo?->gsis_id_no));
    $this->mapSet($map, 'C1', 'D29', $this->excelValue($personalInfo?->pagibig_id_no));
    $this->mapSet($map, 'C1', 'D31', $this->excelValue($personalInfo?->philhealth_no));
    $this->mapSet($map, 'C1', 'D32', $this->excelValue($personalInfo?->sss_id_no));
    $this->mapSet($map, 'C1', 'D33', $this->excelValue($personalInfo?->tin_no));
    $this->mapSet($map, 'C1', 'D34', $this->excelValue($personalInfo?->agency_employee_no));

    $this->mapSet($map, 'C1', 'I18', $this->excelValue($residential[0] ?? ''));
    $this->mapSet($map, 'C1', 'L18', $this->excelValue($residential[1] ?? ''));
    $this->mapSet($map, 'C1', 'I21', $this->excelValue($residential[2] ?? ''));
    $this->mapSet($map, 'C1', 'L21', $this->excelValue($residential[3] ?? ''));
    $this->mapSet($map, 'C1', 'I23', $this->excelValue($residential[4] ?? ''));
    $this->mapSet($map, 'C1', 'L23', $this->excelValue($residential[5] ?? ''));
    $this->mapSet($map, 'C1', 'I24', $this->excelValue($residential[6] ?? ''));

    $this->mapSet($map, 'C1', 'I26', $this->excelValue($permanent[0] ?? ''));
    $this->mapSet($map, 'C1', 'L26', $this->excelValue($permanent[1] ?? ''));
    $this->mapSet($map, 'C1', 'I28', $this->excelValue($permanent[2] ?? ''));
    $this->mapSet($map, 'C1', 'L28', $this->excelValue($permanent[3] ?? ''));
    $this->mapSet($map, 'C1', 'I30', $this->excelValue($permanent[4] ?? ''));
    $this->mapSet($map, 'C1', 'L30', $this->excelValue($permanent[5] ?? ''));
    $this->mapSet($map, 'C1', 'I31', $this->excelValue($permanent[6] ?? ''));
    $this->mapSet($map, 'C1', 'I32', $this->excelValue($personalInfo?->telephone_no));
    $this->mapSet($map, 'C1', 'I33', $this->excelValue($personalInfo?->mobile_no));
    $this->mapSet($map, 'C1', 'I34', $this->excelValue($personalInfo?->email_address));

    $this->mapSet($map, 'C1', 'D36', $this->excelValue($familyBackground?->spouse_surname));
    $this->mapSet($map, 'C1', 'D37', $this->excelValue($familyBackground?->spouse_first_name));
    $this->mapSet($map, 'C1', 'G37', $this->excelValue($familyBackground?->spouse_name_extension));
    $this->mapSet($map, 'C1', 'D38', $this->excelValue($familyBackground?->spouse_middle_name));
    $this->mapSet($map, 'C1', 'D39', $this->excelValue($familyBackground?->spouse_occupation));
    $this->mapSet($map, 'C1', 'D40', $this->excelValue($familyBackground?->spouse_employer));
    $this->mapSet($map, 'C1', 'D41', $this->excelValue($familyBackground?->spouse_business_address));
    $this->mapSet($map, 'C1', 'D42', $this->excelValue($familyBackground?->spouse_telephone));
    $this->mapSet($map, 'C1', 'D43', $this->excelValue($familyBackground?->father_surname));
    $this->mapSet($map, 'C1', 'D44', $this->excelValue($familyBackground?->father_first_name));
    $this->mapSet($map, 'C1', 'G44', $this->excelValue($familyBackground?->father_name_extension));
    $this->mapSet($map, 'C1', 'D45', $this->excelValue($familyBackground?->father_middle_name));
    $this->mapSet($map, 'C1', 'D47', $this->excelValue($familyBackground?->mother_maiden_surname));
    $this->mapSet($map, 'C1', 'D48', $this->excelValue($familyBackground?->mother_maiden_first_name));
    $this->mapSet($map, 'C1', 'D49', $this->excelValue($familyBackground?->mother_maiden_middle_name));

    for ($i = 0; $i < 12; $i++) {
        $row = 37 + $i;
        $child = $children[$i] ?? [];
        $this->mapSet($map, 'C1', "I{$row}", $this->excelValue($child['name'] ?? ''));
        $this->mapSet($map, 'C1', "M{$row}", $this->excelDate($child['dob'] ?? null));
    }
    if (!$this->hasAnyRowData($children, ['name', 'dob'])) {
        $this->mapSet($map, 'C1', 'I37', 'N/A');
        $this->mapSet($map, 'C1', 'M37', 'N/A');
    }

    $voc = $vocational[0] ?? [];
    $col = $college[0] ?? [];
    $grd = $grad[0] ?? [];
    $this->mapSet($map, 'C1', 'D54', $this->excelValue($educationalBackground?->elem_school));
    $this->mapSet($map, 'C1', 'G54', $this->excelValue($educationalBackground?->elem_basic));
    $this->mapSet($map, 'C1', 'J54', $this->excelDateMonthYear($educationalBackground?->elem_from));
    $this->mapSet($map, 'C1', 'K54', $this->excelDateMonthYear($educationalBackground?->elem_to));
    $this->mapSet($map, 'C1', 'L54', $this->excelValue($educationalBackground?->elem_earned));
    $this->mapSet($map, 'C1', 'M54', $this->excelValue($educationalBackground?->elem_year_graduated));
    $this->mapSet($map, 'C1', 'N54', $this->excelValue($educationalBackground?->elem_academic_honors));
    if (!$this->hasEducationObjectData($educationalBackground, ['elem_school', 'elem_basic', 'elem_from', 'elem_to', 'elem_earned', 'elem_year_graduated', 'elem_academic_honors'])) {
        $this->mapSet($map, 'C1', 'D54', 'N/A');
        $this->mapSet($map, 'C1', 'G54', 'N/A');
        $this->mapSet($map, 'C1', 'J54', 'N/A');
        $this->mapSet($map, 'C1', 'K54', 'N/A');
        $this->mapSet($map, 'C1', 'L54', 'N/A');
        $this->mapSet($map, 'C1', 'M54', 'N/A');
        $this->mapSet($map, 'C1', 'N54', 'N/A');
    }

    $this->mapSet($map, 'C1', 'D55', $this->excelValue($educationalBackground?->jhs_school));
    $this->mapSet($map, 'C1', 'G55', $this->excelValue($educationalBackground?->jhs_basic));
    $this->mapSet($map, 'C1', 'J55', $this->excelDateMonthYear($educationalBackground?->jhs_from));
    $this->mapSet($map, 'C1', 'K55', $this->excelDateMonthYear($educationalBackground?->jhs_to));
    $this->mapSet($map, 'C1', 'L55', $this->excelValue($educationalBackground?->jhs_earned));
    $this->mapSet($map, 'C1', 'M55', $this->excelValue($educationalBackground?->jhs_year_graduated));
    $this->mapSet($map, 'C1', 'N55', $this->excelValue($educationalBackground?->jhs_academic_honors));
    if (!$this->hasEducationObjectData($educationalBackground, ['jhs_school', 'jhs_basic', 'jhs_from', 'jhs_to', 'jhs_earned', 'jhs_year_graduated', 'jhs_academic_honors'])) {
        $this->mapSet($map, 'C1', 'D55', 'N/A');
        $this->mapSet($map, 'C1', 'G55', 'N/A');
        $this->mapSet($map, 'C1', 'J55', 'N/A');
        $this->mapSet($map, 'C1', 'K55', 'N/A');
        $this->mapSet($map, 'C1', 'L55', 'N/A');
        $this->mapSet($map, 'C1', 'M55', 'N/A');
        $this->mapSet($map, 'C1', 'N55', 'N/A');
    }

    $this->mapSet($map, 'C1', 'D56', $this->excelValue($voc['school'] ?? ''));
    $this->mapSet($map, 'C1', 'G56', $this->excelValue($voc['basic'] ?? ''));
    $this->mapSet($map, 'C1', 'J56', $this->excelDateMonthYear($voc['from'] ?? null));
    $this->mapSet($map, 'C1', 'K56', $this->excelDateMonthYear($voc['to'] ?? null));
    $this->mapSet($map, 'C1', 'L56', $this->excelValue($voc['earned'] ?? ''));
    $this->mapSet($map, 'C1', 'M56', $this->excelValue($voc['year_graduated'] ?? ''));
    $this->mapSet($map, 'C1', 'N56', $this->excelValue($voc['academic_honors'] ?? ''));
    if (!$this->hasAnyRowData([$voc], ['school', 'basic', 'from', 'to', 'earned', 'year_graduated', 'academic_honors'])) {
        $this->mapSet($map, 'C1', 'D56', 'N/A');
        $this->mapSet($map, 'C1', 'G56', 'N/A');
        $this->mapSet($map, 'C1', 'J56', 'N/A');
        $this->mapSet($map, 'C1', 'K56', 'N/A');
        $this->mapSet($map, 'C1', 'L56', 'N/A');
        $this->mapSet($map, 'C1', 'M56', 'N/A');
        $this->mapSet($map, 'C1', 'N56', 'N/A');
    }

    $this->mapSet($map, 'C1', 'D57', $this->excelValue($col['school'] ?? ''));
    $this->mapSet($map, 'C1', 'G57', $this->excelValue($col['basic'] ?? ''));
    $this->mapSet($map, 'C1', 'J57', $this->excelDateMonthYear($col['from'] ?? null));
    $this->mapSet($map, 'C1', 'K57', $this->excelDateMonthYear($col['to'] ?? null));
    $this->mapSet($map, 'C1', 'L57', $this->excelValue($col['earned'] ?? ''));
    $this->mapSet($map, 'C1', 'M57', $this->excelValue($col['year_graduated'] ?? ''));
    $this->mapSet($map, 'C1', 'N57', $this->excelValue($col['academic_honors'] ?? ''));
    if (!$this->hasAnyRowData([$col], ['school', 'basic', 'from', 'to', 'earned', 'year_graduated', 'academic_honors'])) {
        $this->mapSet($map, 'C1', 'D57', 'N/A');
        $this->mapSet($map, 'C1', 'G57', 'N/A');
        $this->mapSet($map, 'C1', 'J57', 'N/A');
        $this->mapSet($map, 'C1', 'K57', 'N/A');
        $this->mapSet($map, 'C1', 'L57', 'N/A');
        $this->mapSet($map, 'C1', 'M57', 'N/A');
        $this->mapSet($map, 'C1', 'N57', 'N/A');
    }

    $this->mapSet($map, 'C1', 'D58', $this->excelValue($grd['school'] ?? ''));
    $this->mapSet($map, 'C1', 'G58', $this->excelValue($grd['basic'] ?? ''));
    $this->mapSet($map, 'C1', 'J58', $this->excelDateMonthYear($grd['from'] ?? null));
    $this->mapSet($map, 'C1', 'K58', $this->excelDateMonthYear($grd['to'] ?? null));
    $this->mapSet($map, 'C1', 'L58', $this->excelValue($grd['earned'] ?? ''));
    $this->mapSet($map, 'C1', 'M58', $this->excelValue($grd['year_graduated'] ?? ''));
    $this->mapSet($map, 'C1', 'N58', $this->excelValue($grd['academic_honors'] ?? ''));
    if (!$this->hasAnyRowData([$grd], ['school', 'basic', 'from', 'to', 'earned', 'year_graduated', 'academic_honors'])) {
        $this->mapSet($map, 'C1', 'D58', 'N/A');
        $this->mapSet($map, 'C1', 'G58', 'N/A');
        $this->mapSet($map, 'C1', 'J58', 'N/A');
        $this->mapSet($map, 'C1', 'K58', 'N/A');
        $this->mapSet($map, 'C1', 'L58', 'N/A');
        $this->mapSet($map, 'C1', 'M58', 'N/A');
        $this->mapSet($map, 'C1', 'N58', 'N/A');
    }
    $this->mapSet($map, 'C1', 'J60', Carbon::now()->format('m/d/Y'));

    // C2
    $cse = array_slice($civilServiceRows, 0, 7);
    foreach ($cse as $i => $row) {
        $excelRow = 5 + $i;
        $this->mapSet($map, 'C2', "B{$excelRow}", $this->excelValue($row['cs_eligibility_career'] ?? ''));
        $this->mapSet($map, 'C2', "F{$excelRow}", $this->excelValue($row['cs_eligibility_rating'] ?? ''));
        $this->mapSet($map, 'C2', "G{$excelRow}", $this->excelDate($row['cs_eligibility_date'] ?? null));
        $this->mapSet($map, 'C2', "I{$excelRow}", $this->excelValue($row['cs_eligibility_place'] ?? ''));
        $this->mapSet($map, 'C2', "J{$excelRow}", $this->excelValue($row['cs_eligibility_license'] ?? ''));
        $this->mapSet($map, 'C2', "K{$excelRow}", $this->excelDate($row['cs_eligibility_validity'] ?? null));
    }
    if (!$this->hasCivilServiceData($cse)) {
        $this->mapSet($map, 'C2', 'B5', 'N/A');
        $this->mapSet($map, 'C2', 'F5', 'N/A');
        $this->mapSet($map, 'C2', 'G5', 'N/A');
        $this->mapSet($map, 'C2', 'I5', 'N/A');
        $this->mapSet($map, 'C2', 'J5', 'N/A');
        $this->mapSet($map, 'C2', 'K5', 'N/A');
    }

    $we = array_slice($workExperienceRows, 0, 28);
    foreach ($we as $i => $row) {
        $excelRow = 18 + $i;
        $this->mapSet($map, 'C2', "A{$excelRow}", $this->excelDate($row['work_exp_from'] ?? null));
        $this->mapSet($map, 'C2', "C{$excelRow}", $this->excelDate($row['work_exp_to'] ?? null));
        $this->mapSet($map, 'C2', "D{$excelRow}", $this->excelValue($row['work_exp_position'] ?? ''));
        $this->mapSet($map, 'C2', "G{$excelRow}", $this->excelValue($row['work_exp_department'] ?? ''));
        $this->mapSet($map, 'C2', "J{$excelRow}", $this->excelValue($row['work_exp_status'] ?? ''));
        $this->mapSet($map, 'C2', "K{$excelRow}", $this->normalizeGovServiceFlag($row['work_exp_govt_service'] ?? null));
    }
    if (!$this->hasWorkExperienceData($we)) {
        $this->mapSet($map, 'C2', 'A18', 'N/A');
        $this->mapSet($map, 'C2', 'C18', 'N/A');
        $this->mapSet($map, 'C2', 'D18', 'N/A');
        $this->mapSet($map, 'C2', 'G18', 'N/A');
        $this->mapSet($map, 'C2', 'J18', 'N/A');
        $this->mapSet($map, 'C2', 'K18', 'N/A');
    }
    $this->mapSet($map, 'C2', 'I47', Carbon::now()->format('m/d/Y'));

    // C3
    $vw = array_slice($voluntaryRows, 0, 7);
    foreach ($vw as $i => $row) {
        $excelRow = 6 + $i;
        $this->mapSet($map, 'C3', "B{$excelRow}", $this->excelValue($row['voluntary_org'] ?? ''));
        $this->mapSet($map, 'C3', "E{$excelRow}", $this->excelDate($row['voluntary_from'] ?? null));
        $this->mapSet($map, 'C3', "F{$excelRow}", $this->excelDate($row['voluntary_to'] ?? null));
        $this->mapSet($map, 'C3', "G{$excelRow}", $this->excelValue($row['voluntary_hours'] ?? ''));
        $this->mapSet($map, 'C3', "H{$excelRow}", $this->excelValue($row['voluntary_position'] ?? ''));
    }
    if (!$this->hasAnyRowData($vw, ['voluntary_org', 'voluntary_from', 'voluntary_to', 'voluntary_hours', 'voluntary_position'])) {
        $this->mapSet($map, 'C3', 'B6', 'N/A');
        $this->mapSet($map, 'C3', 'E6', 'N/A');
        $this->mapSet($map, 'C3', 'F6', 'N/A');
        $this->mapSet($map, 'C3', 'G6', 'N/A');
        $this->mapSet($map, 'C3', 'H6', 'N/A');
    }

    $lnd = array_slice($lndRows, 0, 21);
    foreach ($lnd as $i => $row) {
        $excelRow = 18 + $i;
        $this->mapSet($map, 'C3', "B{$excelRow}", $this->excelValue($row['learning_title'] ?? ''));
        $this->mapSet($map, 'C3', "E{$excelRow}", $this->excelDate($row['learning_from'] ?? null));
        $this->mapSet($map, 'C3', "F{$excelRow}", $this->excelDate($row['learning_to'] ?? null));
        $this->mapSet($map, 'C3', "G{$excelRow}", $this->excelValue($row['learning_hours'] ?? ''));
        $this->mapSet($map, 'C3', "H{$excelRow}", $this->excelValue($row['learning_type'] ?? ''));
        $this->mapSet($map, 'C3', "I{$excelRow}", $this->excelValue($row['learning_conducted'] ?? ''));
    }
    if (!$this->hasAnyRowData($lnd, ['learning_title', 'learning_from', 'learning_to', 'learning_hours', 'learning_type', 'learning_conducted'])) {
        $this->mapSet($map, 'C3', 'B18', 'N/A');
        $this->mapSet($map, 'C3', 'E18', 'N/A');
        $this->mapSet($map, 'C3', 'F18', 'N/A');
        $this->mapSet($map, 'C3', 'G18', 'N/A');
        $this->mapSet($map, 'C3', 'H18', 'N/A');
        $this->mapSet($map, 'C3', 'I18', 'N/A');
    }

    for ($i = 0; $i < 7; $i++) {
        $row = 42 + $i;
        $this->mapSet($map, 'C3', "B{$row}", $this->excelValue($skills[$i] ?? ''));
        $this->mapSet($map, 'C3', "C{$row}", $this->excelValue($distinctions[$i] ?? ''));
        $this->mapSet($map, 'C3', "I{$row}", $this->excelValue($organizations[$i] ?? ''));
    }
    if (empty($skills) && empty($distinctions) && empty($organizations)) {
        $this->mapSet($map, 'C3', 'B42', 'N/A');
        $this->mapSet($map, 'C3', 'C42', 'N/A');
        $this->mapSet($map, 'C3', 'I42', 'N/A');
    }
    $this->mapSet($map, 'C3', 'G50', Carbon::now()->format('m/d/Y'));

    // C4
    if ($misc) {
        $criminalDetailsRaw = (string) ($misc->criminal_35_b ?? '');
        $criminalDetails = explode(',', $criminalDetailsRaw);
        $dateFiledRaw = trim((string) ($criminalDetails[0] ?? ''));
        $caseStatusRaw = isset($criminalDetails[1]) ? trim((string) implode(',', array_slice($criminalDetails, 1))) : '';

        $this->setExcelQuestionMap($map, 'C4', 'I6', 'K6', strtolower((string) ($misc->related_34_a ?? 'no')) !== 'no');
        $this->setExcelQuestionMap($map, 'C4', 'I8', 'K8', strtolower((string) ($misc->related_34_b ?? 'no')) !== 'no');
        $this->setExcelQuestionMap($map, 'C4', 'I13', 'K13', strtolower((string) ($misc->guilty_35_a ?? 'no')) !== 'no');
        $this->setExcelQuestionMap($map, 'C4', 'I18', 'K18', strtolower($criminalDetailsRaw) !== 'no' && trim($criminalDetailsRaw) !== '');
        $this->setExcelQuestionMap($map, 'C4', 'I23', 'K23', strtolower((string) ($misc->convicted_36 ?? 'no')) !== 'no');
        $this->setExcelQuestionMap($map, 'C4', 'I27', 'K27', strtolower((string) ($misc->separated_37 ?? 'no')) !== 'no');
        $this->setExcelQuestionMap($map, 'C4', 'I31', 'K31', strtolower((string) ($misc->candidate_38 ?? 'no')) !== 'no');
        $this->setExcelQuestionMap($map, 'C4', 'I34', 'K34', strtolower((string) ($misc->resigned_38_b ?? 'no')) !== 'no');
        $this->setExcelQuestionMap($map, 'C4', 'I37', 'K37', strtolower((string) ($misc->immigrant_39 ?? 'no')) !== 'no');
        $this->setExcelQuestionMap($map, 'C4', 'I43', 'K43', strtolower((string) ($misc->indigenous_40_a ?? 'no')) !== 'no');
        $this->setExcelQuestionMap($map, 'C4', 'I45', 'K45', strtolower((string) ($misc->pwd_40_b ?? 'no')) !== 'no');
        $this->setExcelQuestionMap($map, 'C4', 'I47', 'K47', strtolower((string) ($misc->solo_parent_40_c ?? 'no')) !== 'no');

        $this->mapSet($map, 'C4', 'G10', $this->excelDetail($misc->related_34_b));
        $this->mapSet($map, 'C4', 'G14', $this->excelDetail($misc->guilty_35_a));
        $this->mapSet($map, 'C4', 'H20', $this->excelDate($dateFiledRaw));
        $this->mapSet($map, 'C4', 'G21', $this->excelValue($caseStatusRaw));
        $this->mapSet($map, 'C4', 'G24', $this->excelDetail($misc->convicted_36));
        $this->mapSet($map, 'C4', 'G28', $this->excelDetail($misc->separated_37));
        $this->mapSet($map, 'C4', 'G32', $this->excelDetail($misc->candidate_38));
        $this->mapSet($map, 'C4', 'G35', $this->excelDetail($misc->resigned_38_b));
        $this->mapSet($map, 'C4', 'G38', $this->excelDetail($misc->immigrant_39));
        $this->mapSet($map, 'C4', 'G44', $this->excelDetail($misc->indigenous_40_a));
        $this->mapSet($map, 'C4', 'G46', $this->excelDetail($misc->pwd_40_b));
        $this->mapSet($map, 'C4', 'G48', $this->excelDetail($misc->solo_parent_40_c));

        $this->mapSet($map, 'C4', 'A52', $this->excelValue($misc->ref1_name));
        $this->mapSet($map, 'C4', 'F52', $this->excelValue($misc->ref1_address));
        $this->mapSet($map, 'C4', 'G52', $this->excelValue($misc->ref1_tel));
        $this->mapSet($map, 'C4', 'A53', $this->excelValue($misc->ref2_name));
        $this->mapSet($map, 'C4', 'F53', $this->excelValue($misc->ref2_address));
        $this->mapSet($map, 'C4', 'G53', $this->excelValue($misc->ref2_tel));
        $this->mapSet($map, 'C4', 'A54', $this->excelValue($misc->ref3_name));
        $this->mapSet($map, 'C4', 'F54', $this->excelValue($misc->ref3_address));
        $this->mapSet($map, 'C4', 'G54', $this->excelValue($misc->ref3_tel));

        $this->mapSet($map, 'C4', 'B61', $this->excelValue($misc->govt_id_type));
        $this->mapSet($map, 'C4', 'B62', $this->excelValue($misc->govt_id_number));
        $this->mapSet($map, 'C4', 'B64', $this->formatGovtIssuePlaceAndDate($misc->govt_id_place_issued, $misc->govt_id_date_issued));
        $this->mapSet($map, 'C4', 'F65', Carbon::now()->format('m/d/Y'));
    }

    return $map;
}

private function mapSet(array &$map, string $sheet, string $cell, string $value): void
{
    if ($value === '') {
        return;
    }
    $map[$sheet][$cell] = $value;
}

private function setExcelQuestionMap(array &$map, string $sheet, string $yesCell, string $noCell, bool $yes): void
{
    $map[$sheet][$yesCell] = $yes ? 'X' : '';
    $map[$sheet][$noCell] = $yes ? '' : 'X';
}

private function fillExcelC1(
    $sheet,
    $personalInfo,
    $familyBackground,
    $educationalBackground,
    array $residential,
    array $permanent,
    array $children,
    array $vocational,
    array $college,
    array $grad
): void {
    $sheet->setCellValue('D10', $this->excelValue($personalInfo?->surname));
    $sheet->setCellValue('D11', $this->excelValue($personalInfo?->first_name));
    $sheet->setCellValue('D12', $this->excelValue($personalInfo?->middle_name));
    $sheet->setCellValue('L11', $this->excelValue($personalInfo?->name_extension));
    $sheet->setCellValue('D13', $this->excelDate($personalInfo?->date_of_birth));
    $sheet->setCellValue('D15', $this->excelValue($personalInfo?->place_of_birth));
    $sheet->setCellValue('D16', $this->excelTitle($personalInfo?->sex));
    $sheet->setCellValue('D17', $this->excelTitle($personalInfo?->civil_status));
    $sheet->setCellValue('J13', $this->excelValue($personalInfo?->citizenship));
    $sheet->setCellValue('L15', $this->excelValue($personalInfo?->dual_country));

    $sheet->setCellValue('D22', $this->excelValue($personalInfo?->height));
    $sheet->setCellValue('D24', $this->excelValue($personalInfo?->weight));
    $sheet->setCellValue('D25', $this->excelValue($personalInfo?->blood_type));
    $sheet->setCellValue('D27', $this->excelValue($personalInfo?->gsis_id_no));
    $sheet->setCellValue('D29', $this->excelValue($personalInfo?->pagibig_id_no));
    $sheet->setCellValue('D31', $this->excelValue($personalInfo?->philhealth_no));
    $sheet->setCellValue('D32', $this->excelValue($personalInfo?->sss_id_no));
    $sheet->setCellValue('D33', $this->excelValue($personalInfo?->tin_no));
    $sheet->setCellValue('D34', $this->excelValue($personalInfo?->agency_employee_no));

    $sheet->setCellValue('I18', $this->excelValue($residential[0] ?? ''));
    $sheet->setCellValue('L18', $this->excelValue($residential[1] ?? ''));
    $sheet->setCellValue('I21', $this->excelValue($residential[2] ?? ''));
    $sheet->setCellValue('L21', $this->excelValue($residential[3] ?? ''));
    $sheet->setCellValue('I23', $this->excelValue($residential[4] ?? ''));
    $sheet->setCellValue('L23', $this->excelValue($residential[5] ?? ''));
    $sheet->setCellValue('I24', $this->excelValue($residential[6] ?? ''));

    $sheet->setCellValue('I26', $this->excelValue($permanent[0] ?? ''));
    $sheet->setCellValue('L26', $this->excelValue($permanent[1] ?? ''));
    $sheet->setCellValue('I28', $this->excelValue($permanent[2] ?? ''));
    $sheet->setCellValue('L28', $this->excelValue($permanent[3] ?? ''));
    $sheet->setCellValue('I30', $this->excelValue($permanent[4] ?? ''));
    $sheet->setCellValue('L30', $this->excelValue($permanent[5] ?? ''));
    $sheet->setCellValue('I31', $this->excelValue($permanent[6] ?? ''));
    $sheet->setCellValue('I32', $this->excelValue($personalInfo?->telephone_no));
    $sheet->setCellValue('I33', $this->excelValue($personalInfo?->mobile_no));
    $sheet->setCellValue('I34', $this->excelValue($personalInfo?->email_address));

    $sheet->setCellValue('D36', $this->excelValue($familyBackground?->spouse_surname));
    $sheet->setCellValue('D37', $this->excelValue($familyBackground?->spouse_first_name));
    $sheet->setCellValue('G37', $this->excelValue($familyBackground?->spouse_name_extension));
    $sheet->setCellValue('D38', $this->excelValue($familyBackground?->spouse_middle_name));
    $sheet->setCellValue('D39', $this->excelValue($familyBackground?->spouse_occupation));
    $sheet->setCellValue('D40', $this->excelValue($familyBackground?->spouse_employer));
    $sheet->setCellValue('D41', $this->excelValue($familyBackground?->spouse_business_address));
    $sheet->setCellValue('D42', $this->excelValue($familyBackground?->spouse_telephone));
    $sheet->setCellValue('D43', $this->excelValue($familyBackground?->father_surname));
    $sheet->setCellValue('D44', $this->excelValue($familyBackground?->father_first_name));
    $sheet->setCellValue('G44', $this->excelValue($familyBackground?->father_name_extension));
    $sheet->setCellValue('D45', $this->excelValue($familyBackground?->father_middle_name));
    $sheet->setCellValue('D47', $this->excelValue($familyBackground?->mother_maiden_surname));
    $sheet->setCellValue('D48', $this->excelValue($familyBackground?->mother_maiden_first_name));
    $sheet->setCellValue('D49', $this->excelValue($familyBackground?->mother_maiden_middle_name));

    for ($i = 0; $i < 12; $i++) {
        $row = 37 + $i;
        $child = $children[$i] ?? [];
        $sheet->setCellValue("I{$row}", $this->excelValue($child['name'] ?? ''));
        $sheet->setCellValue("M{$row}", $this->excelDate($child['dob'] ?? null));
    }
    if (!$this->hasAnyRowData($children, ['name', 'dob'])) {
        $sheet->setCellValue('I37', 'N/A');
        $sheet->setCellValue('M37', 'N/A');
    }

    $voc = $vocational[0] ?? [];
    $col = $college[0] ?? [];
    $grd = $grad[0] ?? [];

    $sheet->setCellValue('D54', $this->excelValue($educationalBackground?->elem_school));
    $sheet->setCellValue('G54', $this->excelValue($educationalBackground?->elem_basic));
    $sheet->setCellValue('J54', $this->excelDateMonthYear($educationalBackground?->elem_from));
    $sheet->setCellValue('K54', $this->excelDateMonthYear($educationalBackground?->elem_to));
    $sheet->setCellValue('L54', $this->excelValue($educationalBackground?->elem_earned));
    $sheet->setCellValue('M54', $this->excelValue($educationalBackground?->elem_year_graduated));
    $sheet->setCellValue('N54', $this->excelValue($educationalBackground?->elem_academic_honors));
    if (!$this->hasEducationObjectData($educationalBackground, ['elem_school', 'elem_basic', 'elem_from', 'elem_to', 'elem_earned', 'elem_year_graduated', 'elem_academic_honors'])) {
        $sheet->setCellValue('D54', 'N/A');
        $sheet->setCellValue('G54', 'N/A');
        $sheet->setCellValue('J54', 'N/A');
        $sheet->setCellValue('K54', 'N/A');
        $sheet->setCellValue('L54', 'N/A');
        $sheet->setCellValue('M54', 'N/A');
        $sheet->setCellValue('N54', 'N/A');
    }

    $sheet->setCellValue('D55', $this->excelValue($educationalBackground?->jhs_school));
    $sheet->setCellValue('G55', $this->excelValue($educationalBackground?->jhs_basic));
    $sheet->setCellValue('J55', $this->excelDateMonthYear($educationalBackground?->jhs_from));
    $sheet->setCellValue('K55', $this->excelDateMonthYear($educationalBackground?->jhs_to));
    $sheet->setCellValue('L55', $this->excelValue($educationalBackground?->jhs_earned));
    $sheet->setCellValue('M55', $this->excelValue($educationalBackground?->jhs_year_graduated));
    $sheet->setCellValue('N55', $this->excelValue($educationalBackground?->jhs_academic_honors));
    if (!$this->hasEducationObjectData($educationalBackground, ['jhs_school', 'jhs_basic', 'jhs_from', 'jhs_to', 'jhs_earned', 'jhs_year_graduated', 'jhs_academic_honors'])) {
        $sheet->setCellValue('D55', 'N/A');
        $sheet->setCellValue('G55', 'N/A');
        $sheet->setCellValue('J55', 'N/A');
        $sheet->setCellValue('K55', 'N/A');
        $sheet->setCellValue('L55', 'N/A');
        $sheet->setCellValue('M55', 'N/A');
        $sheet->setCellValue('N55', 'N/A');
    }

    $sheet->setCellValue('D56', $this->excelValue($voc['school'] ?? ''));
    $sheet->setCellValue('G56', $this->excelValue($voc['basic'] ?? ''));
    $sheet->setCellValue('J56', $this->excelDateMonthYear($voc['from'] ?? null));
    $sheet->setCellValue('K56', $this->excelDateMonthYear($voc['to'] ?? null));
    $sheet->setCellValue('L56', $this->excelValue($voc['earned'] ?? ''));
    $sheet->setCellValue('M56', $this->excelValue($voc['year_graduated'] ?? ''));
    $sheet->setCellValue('N56', $this->excelValue($voc['academic_honors'] ?? ''));
    if (!$this->hasAnyRowData([$voc], ['school', 'basic', 'from', 'to', 'earned', 'year_graduated', 'academic_honors'])) {
        $sheet->setCellValue('D56', 'N/A');
        $sheet->setCellValue('G56', 'N/A');
        $sheet->setCellValue('J56', 'N/A');
        $sheet->setCellValue('K56', 'N/A');
        $sheet->setCellValue('L56', 'N/A');
        $sheet->setCellValue('M56', 'N/A');
        $sheet->setCellValue('N56', 'N/A');
    }

    $sheet->setCellValue('D57', $this->excelValue($col['school'] ?? ''));
    $sheet->setCellValue('G57', $this->excelValue($col['basic'] ?? ''));
    $sheet->setCellValue('J57', $this->excelDateMonthYear($col['from'] ?? null));
    $sheet->setCellValue('K57', $this->excelDateMonthYear($col['to'] ?? null));
    $sheet->setCellValue('L57', $this->excelValue($col['earned'] ?? ''));
    $sheet->setCellValue('M57', $this->excelValue($col['year_graduated'] ?? ''));
    $sheet->setCellValue('N57', $this->excelValue($col['academic_honors'] ?? ''));
    if (!$this->hasAnyRowData([$col], ['school', 'basic', 'from', 'to', 'earned', 'year_graduated', 'academic_honors'])) {
        $sheet->setCellValue('D57', 'N/A');
        $sheet->setCellValue('G57', 'N/A');
        $sheet->setCellValue('J57', 'N/A');
        $sheet->setCellValue('K57', 'N/A');
        $sheet->setCellValue('L57', 'N/A');
        $sheet->setCellValue('M57', 'N/A');
        $sheet->setCellValue('N57', 'N/A');
    }

    $sheet->setCellValue('D58', $this->excelValue($grd['school'] ?? ''));
    $sheet->setCellValue('G58', $this->excelValue($grd['basic'] ?? ''));
    $sheet->setCellValue('J58', $this->excelDateMonthYear($grd['from'] ?? null));
    $sheet->setCellValue('K58', $this->excelDateMonthYear($grd['to'] ?? null));
    $sheet->setCellValue('L58', $this->excelValue($grd['earned'] ?? ''));
    $sheet->setCellValue('M58', $this->excelValue($grd['year_graduated'] ?? ''));
    $sheet->setCellValue('N58', $this->excelValue($grd['academic_honors'] ?? ''));
    if (!$this->hasAnyRowData([$grd], ['school', 'basic', 'from', 'to', 'earned', 'year_graduated', 'academic_honors'])) {
        $sheet->setCellValue('D58', 'N/A');
        $sheet->setCellValue('G58', 'N/A');
        $sheet->setCellValue('J58', 'N/A');
        $sheet->setCellValue('K58', 'N/A');
        $sheet->setCellValue('L58', 'N/A');
        $sheet->setCellValue('M58', 'N/A');
        $sheet->setCellValue('N58', 'N/A');
    }

    $sheet->setCellValue('J60', Carbon::now()->format('m/d/Y'));
}

private function fillExcelC2($sheet, array $civilServiceRows, array $workExperienceRows): void
{
    $cse = array_slice($civilServiceRows, 0, 7);
    foreach ($cse as $i => $row) {
        $excelRow = 5 + $i;
        $sheet->setCellValue("B{$excelRow}", $this->excelValue($row['cs_eligibility_career'] ?? ''));
        $sheet->setCellValue("F{$excelRow}", $this->excelValue($row['cs_eligibility_rating'] ?? ''));
        $sheet->setCellValue("G{$excelRow}", $this->excelDate($row['cs_eligibility_date'] ?? null));
        $sheet->setCellValue("I{$excelRow}", $this->excelValue($row['cs_eligibility_place'] ?? ''));
        $sheet->setCellValue("J{$excelRow}", $this->excelValue($row['cs_eligibility_license'] ?? ''));
        $sheet->setCellValue("K{$excelRow}", $this->excelDate($row['cs_eligibility_validity'] ?? null));
    }
    if (!$this->hasCivilServiceData($cse)) {
        $sheet->setCellValue('B5', 'N/A');
        $sheet->setCellValue('F5', 'N/A');
        $sheet->setCellValue('G5', 'N/A');
        $sheet->setCellValue('I5', 'N/A');
        $sheet->setCellValue('J5', 'N/A');
        $sheet->setCellValue('K5', 'N/A');
    }

    $we = array_slice($workExperienceRows, 0, 28);
    foreach ($we as $i => $row) {
        $excelRow = 18 + $i;
        $sheet->setCellValue("A{$excelRow}", $this->excelDate($row['work_exp_from'] ?? null));
        $sheet->setCellValue("C{$excelRow}", $this->excelDate($row['work_exp_to'] ?? null));
        $sheet->setCellValue("D{$excelRow}", $this->excelValue($row['work_exp_position'] ?? ''));
        $sheet->setCellValue("G{$excelRow}", $this->excelValue($row['work_exp_department'] ?? ''));
        $sheet->setCellValue("J{$excelRow}", $this->excelValue($row['work_exp_status'] ?? ''));
        $sheet->setCellValue("K{$excelRow}", isset($row['work_exp_govt_service']) ? ($row['work_exp_govt_service'] ? 'Y' : 'N') : '');
    }
    if (!$this->hasWorkExperienceData($we)) {
        $sheet->setCellValue('A18', 'N/A');
        $sheet->setCellValue('C18', 'N/A');
        $sheet->setCellValue('D18', 'N/A');
        $sheet->setCellValue('G18', 'N/A');
        $sheet->setCellValue('J18', 'N/A');
        $sheet->setCellValue('K18', 'N/A');
    }

    $sheet->setCellValue('I47', Carbon::now()->format('m/d/Y'));
}

private function fillExcelC3($sheet, array $voluntaryRows, array $lndRows, array $skills, array $distinctions, array $organizations): void
{
    $vw = array_slice($voluntaryRows, 0, 7);
    foreach ($vw as $i => $row) {
        $excelRow = 6 + $i;
        $sheet->setCellValue("B{$excelRow}", $this->excelValue($row['voluntary_org'] ?? ''));
        $sheet->setCellValue("E{$excelRow}", $this->excelDate($row['voluntary_from'] ?? null));
        $sheet->setCellValue("F{$excelRow}", $this->excelDate($row['voluntary_to'] ?? null));
        $sheet->setCellValue("G{$excelRow}", $this->excelValue($row['voluntary_hours'] ?? ''));
        $sheet->setCellValue("H{$excelRow}", $this->excelValue($row['voluntary_position'] ?? ''));
    }
    if (!$this->hasAnyRowData($vw, ['voluntary_org', 'voluntary_from', 'voluntary_to', 'voluntary_hours', 'voluntary_position'])) {
        $sheet->setCellValue('B6', 'N/A');
        $sheet->setCellValue('E6', 'N/A');
        $sheet->setCellValue('F6', 'N/A');
        $sheet->setCellValue('G6', 'N/A');
        $sheet->setCellValue('H6', 'N/A');
    }

    $lnd = array_slice($lndRows, 0, 21);
    foreach ($lnd as $i => $row) {
        $excelRow = 18 + $i;
        $sheet->setCellValue("B{$excelRow}", $this->excelValue($row['learning_title'] ?? ''));
        $sheet->setCellValue("E{$excelRow}", $this->excelDate($row['learning_from'] ?? null));
        $sheet->setCellValue("F{$excelRow}", $this->excelDate($row['learning_to'] ?? null));
        $sheet->setCellValue("G{$excelRow}", $this->excelValue($row['learning_hours'] ?? ''));
        $sheet->setCellValue("H{$excelRow}", $this->excelValue($row['learning_type'] ?? ''));
        $sheet->setCellValue("I{$excelRow}", $this->excelValue($row['learning_conducted'] ?? ''));
    }
    if (!$this->hasAnyRowData($lnd, ['learning_title', 'learning_from', 'learning_to', 'learning_hours', 'learning_type', 'learning_conducted'])) {
        $sheet->setCellValue('B18', 'N/A');
        $sheet->setCellValue('E18', 'N/A');
        $sheet->setCellValue('F18', 'N/A');
        $sheet->setCellValue('G18', 'N/A');
        $sheet->setCellValue('H18', 'N/A');
        $sheet->setCellValue('I18', 'N/A');
    }

    for ($i = 0; $i < 7; $i++) {
        $row = 42 + $i;
        $sheet->setCellValue("B{$row}", $this->excelValue($skills[$i] ?? ''));
        $sheet->setCellValue("C{$row}", $this->excelValue($distinctions[$i] ?? ''));
        $sheet->setCellValue("I{$row}", $this->excelValue($organizations[$i] ?? ''));
    }
    if (empty($skills) && empty($distinctions) && empty($organizations)) {
        $sheet->setCellValue('B42', 'N/A');
        $sheet->setCellValue('C42', 'N/A');
        $sheet->setCellValue('I42', 'N/A');
    }

    $sheet->setCellValue('G50', Carbon::now()->format('m/d/Y'));
}

private function fillExcelC4($sheet, $misc): void
{
    if (!$misc) {
        return;
    }

    $criminalCase = $this->parseCriminal35B($misc);

    $this->setExcelQuestion($sheet, 'I6', 'K6', $this->hasAffirmativeSelection($misc->related_34_a));
    $this->setExcelQuestion($sheet, 'I8', 'K8', $this->hasAffirmativeSelection($misc->related_34_b));
    $this->setExcelQuestion($sheet, 'I13', 'K13', $this->hasAffirmativeSelection($misc->guilty_35_a));
    $this->setExcelQuestion($sheet, 'I18', 'K18', $criminalCase['has_case']);
    $this->setExcelQuestion($sheet, 'I23', 'K23', $this->hasAffirmativeSelection($misc->convicted_36));
    $this->setExcelQuestion($sheet, 'I27', 'K27', $this->hasAffirmativeSelection($misc->separated_37));
    $this->setExcelQuestion($sheet, 'I31', 'K31', $this->hasAffirmativeSelection($misc->candidate_38));
    $this->setExcelQuestion($sheet, 'I34', 'K34', $this->hasAffirmativeSelection($misc->resigned_38_b));
    $this->setExcelQuestion($sheet, 'I37', 'K37', $this->hasAffirmativeSelection($misc->immigrant_39));
    $this->setExcelQuestion($sheet, 'I43', 'K43', $this->hasAffirmativeSelection($misc->indigenous_40_a));
    $this->setExcelQuestion($sheet, 'I45', 'K45', $this->hasAffirmativeSelection($misc->pwd_40_b));
    $this->setExcelQuestion($sheet, 'I47', 'K47', $this->hasAffirmativeSelection($misc->solo_parent_40_c));

    $sheet->setCellValue('G10', $this->excelValue($misc->related_34_b));
    $sheet->setCellValue('G14', $this->excelValue($misc->guilty_35_a));
    $sheet->setCellValue('H20', $this->excelValue($criminalCase['date']));
    $sheet->setCellValue('G21', $this->excelValue($criminalCase['status']));
    $sheet->setCellValue('G24', $this->excelValue($misc->convicted_36));
    $sheet->setCellValue('G28', $this->excelValue($misc->separated_37));
    $sheet->setCellValue('G32', $this->excelValue($misc->candidate_38));
    $sheet->setCellValue('G35', $this->excelValue($misc->resigned_38_b));
    $sheet->setCellValue('G38', $this->excelValue($misc->immigrant_39));
    $sheet->setCellValue('G44', $this->excelValue($misc->indigenous_40_a));
    $sheet->setCellValue('G46', $this->excelValue($misc->pwd_40_b));
    $sheet->setCellValue('G48', $this->excelValue($misc->solo_parent_40_c));

    $sheet->setCellValue('A52', $this->excelValue($misc->ref1_name));
    $sheet->setCellValue('F52', $this->excelValue($misc->ref1_address));
    $sheet->setCellValue('G52', $this->excelValue($misc->ref1_tel));
    $sheet->setCellValue('A53', $this->excelValue($misc->ref2_name));
    $sheet->setCellValue('F53', $this->excelValue($misc->ref2_address));
    $sheet->setCellValue('G53', $this->excelValue($misc->ref2_tel));
    $sheet->setCellValue('A54', $this->excelValue($misc->ref3_name));
    $sheet->setCellValue('F54', $this->excelValue($misc->ref3_address));
    $sheet->setCellValue('G54', $this->excelValue($misc->ref3_tel));

    $sheet->setCellValue('B61', $this->excelValue($misc->govt_id_type));
    $sheet->setCellValue('B62', $this->excelValue($misc->govt_id_number));
    $sheet->setCellValue('B64', $this->formatGovtIssuePlaceAndDate($misc->govt_id_place_issued, $misc->govt_id_date_issued));
    $sheet->setCellValue('F65', Carbon::now()->format('m/d/Y'));
}

private function setExcelQuestion($sheet, string $yesCell, string $noCell, bool $yes): void
{
    $sheet->setCellValue($yesCell, $yes ? 'X' : '');
    $sheet->setCellValue($noCell, $yes ? '' : 'X');
}

private function excelValue($value): string
{
    $text = $this->normalizeScalarText($value);
    if ($text === '' || strtolower($text) === 'null') {
        return '';
    }

    return $text;
}

private function excelDetail($value): string
{
    $text = $this->excelValue($value);
    if ($text === '') {
        return '';
    }

    $lower = strtolower($text);
    if ($lower === 'no' || $lower === 'n/a' || $lower === 'na') {
        return '';
    }

    return $text;
}

private function excelTitle($value): string
{
    $text = strtolower($this->excelValue($value));
    if ($text === '') {
        return '';
    }
    return ucwords(str_replace('_', ' ', $text));
}

private function excelDate($value): string
{
    $raw = $this->excelValue($value);
    if ($raw === '') {
        return '';
    }
    try {
        return Carbon::parse($raw)->format('m/d/Y');
    } catch (\Throwable $e) {
        return $raw;
    }
}

private function excelDateMonthYear($value): string
{
    $raw = $this->excelValue($value);
    if ($raw === '') {
        return '';
    }
    try {
        return Carbon::parse($raw)->format('m/Y');
    } catch (\Throwable $e) {
        return $raw;
    }
}

private function formatGovtIssuePlaceAndDate($place, $date): string
{
    $issuePlaceRaw = $this->excelValue($place);
    $issueDateRaw = $this->excelDate($date);
    $issuePlace = strtoupper(trim($issuePlaceRaw)) === 'N/A' ? '' : $issuePlaceRaw;
    $issueDate = strtoupper(trim($issueDateRaw)) === 'N/A' ? '' : $issueDateRaw;

    return implode(' | ', array_values(array_filter([$issueDate, $issuePlace], fn ($value) => $value !== '')));
}

}
