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
    private const LEGACY_TEMPLATE_HEIGHT_MM = 330.2;
    private const SHORT_BOND_TEMPLATE_HEIGHT_MM = 279.4;

    private float $yScale = 1.0;
    private float $yOffset = 0.0;
    private float $fontScale = 1.0;
    private float $xOffset = 0.0;
    private bool $isShortBondTemplate = false;
    private int $currentTemplatePage = 1;
    private int $clampedCoordinates = 0;
    private array $clampSamples = [];

    private function getFooterDateY(): float
    {
        if (!$this->isShortBondTemplate) {
            return 310.190;
        }

        return $this->currentTemplatePage === 1 ? 310.190 : 273.2;
    }

    public function exportPDS(Request $request)
    {
        $this->clampedCoordinates = 0;
        $this->clampSamples = [];

        $user = Auth::user(); // Get currently authenticated user
        if (!$user) {
            abort(401);
        }

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
        $templatePath = resource_path('templates/PDS_2025_from_xlsx.pdf');
        if (!file_exists($templatePath)) {
            $templatePath = resource_path('templates/PDS_fixed_V9.pdf');
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
        $hasLNDOverflow = count($lndChunks) > 1;;

        $excelExport = null;
        if (!$request->boolean('force_fpdi')) {
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
        $size = $pdf->getTemplateSize($templateId);
        $this->configureCoordinateScale((float) $size['height']);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
        $this->clearLegacyHeaderNote($pdf);
        $this->setFont($pdf, 'Arial', '', 8);

        $this->writePersonalInfo($pdf, $personalInfo);
        $this->writeAddresses($pdf, $residential, $permanent, $this->getWriteCentered());
        $this->writeFamilyBackground($pdf, $familyBackground);
        $this->writeEducationalBackground($pdf, $educationalBackground);

        $this->writeCollegeChunk($pdf, $collegeChunks[0] ?? []);
        $this->writeVocationalChunk($pdf, $vocationalChunks[0] ?? []);
        $this->writeGraduateChunk($pdf, $gradChunks[0] ?? []);
        $this->writeChildrenChunk($pdf, $childrenChunks[0] ?? []);

        $this->setXY($pdf, 163.5, $this->getFooterDateY());
        $pdf->Write(0, Carbon::now()->format('m/d/Y'));


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
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            $this->clearLegacyHeaderNote($pdf);

            $this->setXY($pdf, 163.5, $this->getFooterDateY());
            $pdf->Write(0, Carbon::now()->format('m/d/Y'));

            $this->setFont($pdf, 'Arial', '', 8);

            // Write only the name parts for identification on overflow pages
            $this->setXY($pdf, 41.5, 45.5);
            $pdf->Write(0, $personalInfo->surname);

            $this->setXY($pdf, 41.5, 52);
            $pdf->Write(0, $personalInfo->first_name);

            $this->setXY($pdf, 41.5, 58);
            $pdf->Write(0, $personalInfo->middle_name);

            $this->setXY($pdf, 165.2, 53);
            $pdf->Write(0, $personalInfo->name_extension);

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
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
        $this->clearLegacyHeaderNote($pdf);

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

        $this->setXY($pdf, 149.352, $this->getFooterDateY());
        $pdf->Write(0, Carbon::now()->format('m/d/Y'));
        // ----------------------------
        // Overflow Pages: CSE overflow + WE overflow
        // ----------------------------

        // Handle CSE overflow pages
        for ($i = 1; $i < count($cseChunks); $i++) {
            $this->currentTemplatePage = 2;
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            $this->clearLegacyHeaderNote($pdf);

            $this->setXY($pdf, 149.352, $this->getFooterDateY());
            $pdf->Write(0, Carbon::now()->format('m/d/Y'));
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
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            $this->clearLegacyHeaderNote($pdf);

            $this->setXY($pdf, 149.352, $this->getFooterDateY());
            $pdf->Write(0, Carbon::now()->format('m/d/Y'));
            $this->writeWorkExperienceChunk($pdf, $chunk);
        }

        // ----------------------------
        // Page 3: First VW chunk + First LND chunk + First Others Chunk
        // ----------------------------

        $templateId = $pdf->importPage(3);
        $this->currentTemplatePage = 3;
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
        $this->clearLegacyHeaderNote($pdf);

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

        if ($lndHasOverflow) {
            $this->setFont($pdf, 'Arial', 'B', 8);
            $this->setXY($pdf, 8, 235); // adjust as needed for L&D
            $pdf->Write(0, "CONTINUED");
            $this->setFont($pdf, 'Arial', '', 8);
        }

        if ($skillsHasOverflow || $distinctionsHasOverflow || $organizationsHasOverflow) {
            $this->setFont($pdf, 'Arial', 'B', 8);
            $this->setXY($pdf, 8, 299); // adjust as needed for Other Information
            $pdf->Write(0, "CONTINUED");
            $this->setFont($pdf, 'Arial', '', 8);
        }

        // ----------------------------
        // Overflow Pages: Page 3 logic for remaining chunks
        // ----------------------------

        for ($i = 1; $i < max(
            count($vwChunks),
            count($lndChunks),
            count($skillsChunks),
            count($distinctionsChunks),
            count($organizationsChunks)
        ); $i++) {
            $this->currentTemplatePage = 3;
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            $this->clearLegacyHeaderNote($pdf);
            $this->setXY($pdf, 161, $this->isShortBondTemplate ? 273.2 : 305);
            $pdf->Write(0, Carbon::now()->format('m/d/Y'));

            // Write Voluntary Work chunk if exists
            if (isset($vwChunks[$i])) {
                $this->writeVoluntaryWorkChunk($pdf, $vwChunks[$i]);
            }

            // Write Learning and Development chunk if exists
            if (isset($lndChunks[$i])) {
                $this->writeLearningAndDevelopmentChunk($pdf, $lndChunks[$i]);
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
        // Page 4: Other Information
        // ----------------------------

        $templateId = $pdf->importPage(4);
        $this->currentTemplatePage = 4;
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
        $this->clearLegacyHeaderNote($pdf);

        // Write first C4 chunk (max 7 rows)
        $this->WriteC4Information($pdf, $user->id);

        $this->setXY($pdf, 113, 270);
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

        $forceInline = $request->boolean('preview');

        if ($isMobile && !$forceInline) {
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

    private function configureCoordinateScale(float $templateHeight): void
    {
        if ($templateHeight <= 0) {
            $this->yScale = 1.0;
            $this->yOffset = 0.0;
            $this->fontScale = 1.0;
            $this->xOffset = 0.0;
            $this->isShortBondTemplate = false;
            return;
        }

        $this->yScale = $templateHeight / self::LEGACY_TEMPLATE_HEIGHT_MM;
        $isShortBondTemplate = $templateHeight < 300;
        $this->isShortBondTemplate = $isShortBondTemplate;

        $this->xOffset = 0.0;
        $this->yOffset = 0.0;
        $this->fontScale = 1.0;
    }

    private function scaleY(float $y): float
    {
        return round($y * $this->yScale, 3);
    }

    private function scaleHeight(float $height): float
    {
        return round($height * $this->yScale, 3);
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
        $targetX = $x + $this->xOffset;

        if ($this->isShortBondTemplate) {
            if ($this->currentTemplatePage === 1) {
                if ($x < 120.0) {
                    $targetX += 7.0;
                }
                $targetY = ($y * 0.87) + 1.2 + $this->yOffset;
            } else {
                $pageXScale = $this->getPageXScale();
                $targetX = ($targetX * $pageXScale) + 12.8;
                $targetY = $this->scaleY($y) + $this->yOffset;
            }
        } else {
            $targetY = $this->scaleY($y) + $this->yOffset;
        }

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
        // CS ID No
        $this->writeFittedAt($pdf, $this->valueOrNa($info?->cs_id_no), 165, 35.5, 35);

        // Names
        $this->writeFittedAt($pdf, $this->valueOrNa($info?->surname), 41.5, 45.5, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->first_name), 41.5, 52, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->middle_name), 41.5, 58, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->name_extension), 165.2, 53, 40);

        // Birth
        $this->writeFittedAt(
            $pdf,
            $this->dateOrNa($info?->date_of_birth),
            41.5,
            62.2,
            34,
            8.0,
            5.0
        );

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->place_of_birth), 41.5, 75, 78);

        // Sex
        $sex = $this->normalizedValue($info?->sex);
        if ($sex === 'male') {
            $this->markCheckbox($pdf, 44, 80);
        } elseif ($sex === 'female') {
            $this->markCheckbox($pdf, 73, 81);
        }

        // Civil Status
        $civilStatus = $this->normalizedValue($info?->civil_status);
        $civilStatusCoords = [
            'single' => [44, 86],
            'married' => [73, 87],
            'widowed' => [43, 90.7],
            'separated' => [73, 90.7],
            'other' => [43, 94],
            'other/s' => [43, 94],
            'others' => [43, 94],
        ];
        if ($civilStatus !== '') {
            $coords = $civilStatusCoords[$civilStatus] ?? $civilStatusCoords['other'];
            $this->markCheckbox($pdf, (float) $coords[0], (float) $coords[1]);
        }

        // Physical
        $this->writeFittedAt($pdf, $this->valueOrNa($info?->height), 41.5, 101.5, 30, 8.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->weight), 41.5, 108.5, 30, 8.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->blood_type), 41.5, 115, 30, 8.0, 5.0);

        // IDs
        $this->writeFittedAt($pdf, $this->valueOrNa($info?->gsis_id_no), 41.5, 122, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->pagibig_id_no), 41.5, 129, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->philhealth_no), 41.5, 136, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->sss_id_no), 41.5, 143, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->tin_no), 41.5, 150, 78);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->agency_employee_no), 41.5, 156.5, 78);

        // Citizenship
        if ($this->valueMatches($info?->citizenship, 'Filipino')) {
            $this->markCheckbox($pdf, 135, 64.5);
        } elseif ($this->valueMatches($info?->citizenship, 'Dual Citizenship', 'Dual')) {
            $this->markCheckbox($pdf, 159, 65);
        }

        if ($this->valueMatches($info?->dual_type, 'By Birth', 'By birth', 'Birth', 'By_Birth')) {
            $this->markCheckbox($pdf, 164.5, 68.5);
        } elseif ($this->valueMatches($info?->dual_type, 'By Naturalization', 'By naturalization', 'Naturalization', 'By_Naturalization')) {
            $this->markCheckbox($pdf, 180.5, 68.5);
        }

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->dual_country), 140.5, 81, 52);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->telephone_no), 123, 143, 74);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->mobile_no), 123, 150, 74);

        $this->writeFittedAt($pdf, $this->valueOrNa($info?->email_address), 123, 156.5, 74, 7.5, 5.0);

}

private function writeAddresses($pdf, $residential, $permanent, $writeCentered)
{
    // Residential Address
    $writeCentered($pdf, $this->valueOrNa($residential[0] ?? null), 133, 149, 87); // House Number
    $writeCentered($pdf, $this->valueOrNa($residential[1] ?? null), 185, 190, 87); // Street
    $writeCentered($pdf, $this->valueOrNa($residential[2] ?? null), 133, 149, 94); // Village/Subdivision
    $writeCentered($pdf, $this->valueOrNa($residential[3] ?? null), 185, 190, 94); // Barangay
    $writeCentered($pdf, $this->valueOrNa($residential[4] ?? null), 133, 149, 100.5); // City/Municipality
    $writeCentered($pdf, $this->valueOrNa($residential[5] ?? null), 185, 190, 100.5); // Province
    $writeCentered($pdf, $this->valueOrNa($residential[6] ?? null), 133, 190, 108); // ZIP Code

    // Permanent Address
    $writeCentered($pdf, $this->valueOrNa($permanent[0] ?? null), 133, 149, 114); // House Number
    $writeCentered($pdf, $this->valueOrNa($permanent[1] ?? null), 185, 190, 114); // Street
    $writeCentered($pdf, $this->valueOrNa($permanent[2] ?? null), 133, 149, 120.5); // Village/Subdivision
    $writeCentered($pdf, $this->valueOrNa($permanent[3] ?? null), 185, 190, 120.5); // Barangay
    $writeCentered($pdf, $this->valueOrNa($permanent[4] ?? null), 133, 149, 127.5); // City/Municipality
    $writeCentered($pdf, $this->valueOrNa($permanent[5] ?? null), 185, 190, 127.5); // Province
    $writeCentered($pdf, $this->valueOrNa($permanent[6] ?? null), 133, 190, 135.5); // ZIP Code

}

private function writeFamilyBackground($pdf, $family)
{
    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_surname), 41.5, 167.5, 49);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_first_name), 41.5, 173.5, 49);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_middle_name), 41.5, 179.5, 49);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_name_extension), 93, 174.2, 28, 7.0, 5.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_occupation), 41.5, 185, 79);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_employer), 41.5, 190.2, 79);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_business_address), 41.5, 196.2, 79, 7.0, 5.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->spouse_telephone), 41.5, 202.2, 79);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->father_surname), 41.5, 208.2, 49);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->father_first_name), 41.5, 213.8, 49);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->father_name_extension), 93, 215, 28, 7.0, 5.0);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->father_middle_name), 41.5, 219.9, 79);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->mother_maiden_surname), 41.5, 231.2, 79);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->mother_maiden_first_name), 41.5, 237.2, 79);

    $this->writeFittedAt($pdf, $this->valueOrNa($family?->mother_maiden_middle_name), 41.5, 243.2, 79);

}

private function writeChildrenChunk($pdf, $chunk)
{
    $startX_name = 123;
    $startX_birthdate = 182.5;
    $startY = 173.3;
    $lineHeight = 5.9;

    // Check if all children entries are empty
    $isEmpty = true;
    foreach ($chunk as $child) {
        if (!empty($child['name']) || !empty($child['dob'])) {
            $isEmpty = false;
            break;
        }
    }

    // If all are empty, write N/A in the name column only
    if ($isEmpty) {
        $this->writeFittedAt($pdf, 'N/A', $startX_name, $startY, 58, 7.5, 5.0);
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
            $startX_birthdate,
            $currentY,
            22,
            7.0,
            5.0
        );
    }
}

private function writeEducationalBackground($pdf, $education)
{
    // === Elementary Section ===
    $hasElemData = !empty($education?->elem_school) ||
                   !empty($education?->elem_basic) ||
                   !empty($education?->elem_from) ||
                   !empty($education?->elem_to) ||
                   !empty($education?->elem_earned) ||
                   !empty($education?->elem_year_graduated) ||
                   !empty($education?->elem_academic_honors);

    if (!$hasElemData) {
        $this->writeFittedAt($pdf, 'N/A', 41.5, 267, 49, 8.0, 5.0);
    } else {
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->elem_school), 41.5, 267, 48, 6.5, 4.5);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->elem_basic), 91, 267, 45, 6.5, 4.5);
        $this->writeCenteredFitted($pdf, $this->dateOrNa($education?->elem_from, 'm/Y'), 138, 150, 267);
        $this->writeCenteredFitted($pdf, $this->dateOrNa($education?->elem_to, 'm/Y'), 150, 163.5, 267);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->elem_earned), 163.5, 267, 18, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->elem_year_graduated), 183, 267, 12, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->elem_academic_honors), 194.2, 267, 10.2, 4.8, 3.8);
    }

    // === Junior High Section ===
    $hasJHSData = !empty($education?->jhs_school) ||
                  !empty($education?->jhs_basic) ||
                  !empty($education?->jhs_from) ||
                  !empty($education?->jhs_to) ||
                  !empty($education?->jhs_earned) ||
                  !empty($education?->jhs_year_graduated) ||
                  !empty($education?->jhs_academic_honors);

    if (!$hasJHSData) {
        $this->writeFittedAt($pdf, 'N/A', 41.5, 276, 49, 8.0, 5.0);
    } else {
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->jhs_school), 41.5, 275, 48, 6.5, 4.5);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->jhs_basic), 91, 275, 45, 6.5, 4.5);
        $this->writeCenteredFitted($pdf, $this->dateOrNa($education?->jhs_from, 'm/Y'), 138, 150, 275);
        $this->writeCenteredFitted($pdf, $this->dateOrNa($education?->jhs_to, 'm/Y'), 150, 163.5, 275);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->jhs_earned), 163.5, 275, 18, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->jhs_year_graduated), 183, 275, 12, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->valueOrNa($education?->jhs_academic_honors), 194.2, 275, 10.2, 4.8, 3.8);
    }
}


private function writeVocationalChunk($pdf, $chunk)
{
    $startX_school = 41.5;
    $startX_basic = 91;
    $startX_from = 137;
    $startX_to = 150;
    $startX_earned = 163.5;
    $startX_year_graduated = 183;
    $startX_honors = 194.2;
    $startY = 281.5;
    $lineHeight = 6;

    // Check if all vocational inputs are empty
    $isEmpty = true;
    foreach ($chunk as $voc) {
        if (
            !empty($voc['school']) ||
            !empty($voc['basic']) ||
            !empty($voc['from']) ||
            !empty($voc['to']) ||
            !empty($voc['earned']) ||
            !empty($voc['year_graduated']) ||
            !empty($voc['academic_honors'])
        ) {
            $isEmpty = false;
            break;
        }
    }

    // If all are empty, write N/A once in the school column
    if ($isEmpty) {
        $this->writeFittedAt($pdf, 'N/A', $startX_school, $startY, 50, 8.0, 5.0);
        return;
    }

    // Otherwise, loop and write each row
    foreach ($chunk as $index => $voc)
    {
        $currentY = $startY + ($index * $lineHeight);

        $this->writeFittedAt($pdf, $this->valueOrNa($voc['school'] ?? null), $startX_school, $currentY, 48, 6.5, 4.5);

        $this->writeFittedAt($pdf, $this->valueOrNa($voc['basic'] ?? null), $startX_basic, $currentY, 45, 6.5, 4.5);

        $this->writeCenteredFitted($pdf, $this->dateOrNa($voc['from'] ?? null, 'm/Y'), $startX_from, $startX_to, $currentY);
        $this->writeCenteredFitted($pdf, $this->dateOrNa($voc['to'] ?? null, 'm/Y'), $startX_to, $startX_earned, $currentY);

        $this->writeFittedAt($pdf, $this->valueOrNa($voc['earned'] ?? null), $startX_earned, $currentY, 18, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($voc['year_graduated'] ?? null), $startX_year_graduated, $currentY, 12, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($voc['academic_honors'] ?? null), $startX_honors, $currentY - 0.6, 10.2, 4.8, 3.8);
    }
}


private function writeCollegeChunk($pdf, $chunk)
{
    $startX_school = 41.5;
    $startX_basic = 91;
    $startX_from = 138;
    $startX_to = 150;
    $startX_earned = 163.5;
    $startX_year_graduated = 183;
    $startX_honors = 194.2;
    $startY = 288.7;
    $lineHeight = 6;

    // Check if all college entries are empty
    $isEmpty = true;
    foreach ($chunk as $college) {
        if (
            !empty($college['school']) ||
            !empty($college['basic']) ||
            !empty($college['from']) ||
            !empty($college['to']) ||
            !empty($college['earned']) ||
            !empty($college['year_graduated']) ||
            !empty($college['academic_honors'])
        ) {
            $isEmpty = false;
            break;
        }
    }

    // If all are empty, write N/A in school column only
    if ($isEmpty) {
        $this->writeFittedAt($pdf, 'N/A', $startX_school, $startY, 50, 8.0, 5.0);
        return;
    }

    // Otherwise, render the college entries normally
    foreach ($chunk as $index => $college)
    {
        $currentY = $startY + ((int)$index * $lineHeight);

        $this->writeFittedAt($pdf, $this->valueOrNa($college['school'] ?? null), $startX_school, $currentY, 48, 6.5, 4.5);

        $this->writeFittedAt($pdf, $this->valueOrNa($college['basic'] ?? null), $startX_basic, $currentY, 45, 6.5, 4.5);

        $this->writeCenteredFitted($pdf, $this->dateOrNa($college['from'] ?? null, 'm/Y'), $startX_from, $startX_to, $currentY);
        $this->writeCenteredFitted($pdf, $this->dateOrNa($college['to'] ?? null, 'm/Y'), $startX_to, $startX_earned, $currentY);

        $this->writeFittedAt($pdf, $this->valueOrNa($college['earned'] ?? null), $startX_earned, $currentY, 18, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($college['year_graduated'] ?? null), $startX_year_graduated, $currentY, 12, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($college['academic_honors'] ?? null), $startX_honors, $currentY - 0.6, 10.2, 4.8, 3.8);

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
    $startY = 296.0;
    $lineHeight = 6;

    // Check if all grad entries are empty
    $isEmpty = true;
    foreach ($chunk as $grad) {
        if (
            !empty($grad['school']) ||
            !empty($grad['basic']) ||
            !empty($grad['from']) ||
            !empty($grad['to']) ||
            !empty($grad['earned']) ||
            !empty($grad['year_graduated']) ||
            !empty($grad['academic_honors'])
        ) {
            $isEmpty = false;
            break;
        }
    }

    // If all are empty, write N/A in school column only
    if ($isEmpty) {
        $this->writeFittedAt($pdf, 'N/A', $startX_school, $startY, 50, 8.0, 5.0);
        return;
    }

    // Otherwise, loop through and write each graduate row
    foreach ($chunk as $index => $grad)
    {
        $currentY = $startY + ($index * $lineHeight);

        $this->writeFittedAt($pdf, $this->valueOrNa($grad['school'] ?? null), $startX_school, $currentY, 48, 6.5, 4.5);

        $this->writeFittedAt($pdf, $this->valueOrNa($grad['basic'] ?? null), $startX_basic, $currentY, 45, 6.5, 4.5);

        $this->writeCenteredFitted($pdf, $this->dateOrNa($grad['from'] ?? null, 'm/Y'), $startX_from, $startX_to, $currentY);
        $this->writeCenteredFitted($pdf, $this->dateOrNa($grad['to'] ?? null, 'm/Y'), $startX_to, $startX_earned, $currentY);

        $this->writeFittedAt($pdf, $this->valueOrNa($grad['earned'] ?? null), $startX_earned, $currentY, 18, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($grad['year_graduated'] ?? null), $startX_year_graduated, $currentY, 12, 7.0, 5.0);

        $this->writeFittedAt($pdf, $this->valueOrNa($grad['academic_honors'] ?? null), $startX_honors, $currentY - 0.6, 10.2, 4.8, 3.8);
    }
}



private function writeCivilServiceEligibilityChunk($pdf, $chunk)
{
    // Layout setup
    $startX_career = 9;
    $startX_rating = 71;
    $startX_date = 94;
    $startX_place = 118;
    $startX_license = 173.0;
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

    $startY = 25.5;
    $rowHeight = 8;

    // Check if all Civil Service entries are empty
    $isEmpty = true;
    foreach ($chunk as $cse) {
        if (
            !empty($cse['cs_eligibility_career']) ||
            !empty($cse['cs_eligibility_rating']) ||
            !empty($cse['cs_eligibility_date']) ||
            !empty($cse['cs_eligibility_place']) ||
            !empty($cse['cs_eligibility_license']) ||
            !empty($cse['cs_eligibility_validity'])
        ) {
            $isEmpty = false;
            break;
        }
    }

    // If all fields are empty, write N/A in career field only
    if ($isEmpty) {
        $this->setXY($pdf, $startX_career, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Render each entry
    foreach ($chunk as $index => $cse) {
        $currentY = $startY + ($index * $rowHeight);

        // Career service
        $this->writeWrapped($pdf, $this->valueOrNa($cse['cs_eligibility_career'] ?? null), 60, $startX_career, $currentY, $currentY - 0.5, 8, 3);

        // Rating
        $this->writeCenteredFitted($pdf, $this->valueOrNa($cse['cs_eligibility_rating'] ?? null), $startX_rating, $startX_date, $currentY);

        // Date of examination
        $this->writeCenteredFittedSized(
            $pdf,
            $this->dateOrNa($cse['cs_eligibility_date'] ?? null),
            $startX_date,
            $startX_place,
            $currentY,
            6.0,
            3.6
        );

        // Place of examination
        $this->writeFittedAt($pdf, $this->valueOrNa($cse['cs_eligibility_place'] ?? null), $startX_place, $currentY, 59, 7.0, 4.5);

        // License number
        $this->writeCenteredFitted($pdf, $this->valueOrNa($cse['cs_eligibility_license'] ?? null), $startX_license, $startX_validity, $currentY);

        // Validity date
        $this->writeCenteredFittedSized($pdf, $this->dateOrNa($cse['cs_eligibility_validity'] ?? null), $startX_validity, $endX_validity, $currentY, 6.0, 3.6);

        // Reset font size
        $this->setFont($pdf, 'Arial', '', 8);
    }
}

private function writeWorkExperienceChunk($pdf, $chunk)
{
    $usesLegacyWorkExpColumns = !$this->isShortBondTemplate;

    // Column X positions
    $x_from = 7.0;
    $x_to = 21.59;
    $x_position = 40.132;
    $x_agency = 94.488;
    $x_salary = 149.352; // Legacy template (Revised 2017) only
    $x_grade = 166.624;  // Legacy template (Revised 2017) only
    $x_status = 177.65;
    $x_gov = 187.0;
    $x_gov_end = 199.5;
    $statusWidth = 20.5;

    if ($this->isShortBondTemplate) {
        $x_from = 20.5;
        $x_to = 36.5;
        $x_position = 53.1;
        $x_agency = 104.7;
        $x_status = 157.5;
        $x_gov = 176.4;
        $x_gov_end = 195.9;
        $statusWidth = 18.0;
    }

    // Starting Y coordinate for the WE table
    $startY = 110;
    $rowHeight = 7.2;

    // Check if all work experience entries are empty
    $isEmpty = true;
    foreach ($chunk as $we) {
        if (
            !empty($we['work_exp_from']) ||
            !empty($we['work_exp_to']) ||
            !empty($we['work_exp_position']) ||
            !empty($we['work_exp_department']) ||
            !empty($we['work_exp_status']) ||
            !empty($we['work_exp_govt_service']) ||
            ($usesLegacyWorkExpColumns && (!empty($we['work_exp_salary']) || !empty($we['work_exp_grade'])))
        ) {
            $isEmpty = false;
            break;
        }
    }

    // If all are empty, write N/A in the position field only
    if ($isEmpty) {
        $this->setXY($pdf, $x_position, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Otherwise, render each row normally
    foreach ($chunk as $index => $we) {
        $currentY = $startY + ($index * $rowHeight);

        $this->writeCenteredFittedSized($pdf, $this->dateOrNa($we['work_exp_from'] ?? null), $x_from, $x_to, $currentY, 6.0, 3.6);
        $this->writeCenteredFittedSized($pdf, $this->dateOrNa($we['work_exp_to'] ?? null), $x_to, $x_position, $currentY, 6.0, 3.6);

        $this->writeFittedAt($pdf, $this->valueOrNa($we['work_exp_position'] ?? null), $x_position, $currentY, 53, 7.0, 5.0);

        $this->writeWrapped($pdf, $this->valueOrNa($we['work_exp_department'] ?? null), 55, $x_agency, $currentY, $currentY - 1.5, 6, 2);

        if ($usesLegacyWorkExpColumns) {
            $this->writeFittedAt($pdf, $this->valueOrNa($we['work_exp_salary'] ?? null), $x_salary, $currentY, 16.8, 7.0, 4.5);
            $this->writeFittedAt($pdf, $this->valueOrNa($we['work_exp_grade'] ?? null), $x_grade, $currentY, 10.5, 6.5, 4.5);
        }

        $this->writeFittedAt($pdf, $this->valueOrNa($we['work_exp_status'] ?? null), $x_status, $currentY, $statusWidth, 6.5, 4.5);

        $this->writeCenteredFitted($pdf, $this->normalizeGovServiceFlag($we['work_exp_govt_service'] ?? null, 'N/A'), $x_gov, $x_gov_end, $currentY);
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

    // Check if all voluntary work entries are empty
    $isEmpty = true;
    foreach ($chunk as $vw) {
        if (
            !empty($vw['voluntary_org']) ||
            !empty($vw['voluntary_from']) ||
            !empty($vw['voluntary_to']) ||
            !empty($vw['voluntary_hours']) ||
            !empty($vw['voluntary_position'])
        ) {
            $isEmpty = false;
            break;
        }
    }

    // If all fields are empty, write "N/A" in organization column only
    if ($isEmpty) {
        $this->setXY($pdf, $x_org, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Render each voluntary work row
    foreach ($chunk as $index => $vw) {
        $currentY = $startY + ($index * $rowHeight);

        $this->writeWrapped($pdf, $this->valueOrNa($vw['voluntary_org'] ?? null), 115, $x_org, $currentY, $currentY - 1, 6, 2);

        $this->setXY($pdf, $x_from, $currentY);
        $pdf->Write(0, $this->dateOrNa($vw['voluntary_from'] ?? null));

        $this->setXY($pdf, $x_to, $currentY);
        $pdf->Write(0, $this->dateOrNa($vw['voluntary_to'] ?? null));

        $this->setXY($pdf, $x_hours, $currentY);
        $pdf->Write(0, $this->valueOrNa($vw['voluntary_hours'] ?? null));

        $this->writeWrapped($pdf, $this->valueOrNa($vw['voluntary_position'] ?? null), 60, $x_position, $currentY, $currentY - 1, 7, 3);
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

    // Check if all learning/development fields are empty
    $isEmpty = true;
    foreach ($chunk as $lnd) {
        if (
            !empty($lnd['learning_title']) ||
            !empty($lnd['learning_type']) ||
            !empty($lnd['learning_from']) ||
            !empty($lnd['learning_to']) ||
            !empty($lnd['learning_hours']) ||
            !empty($lnd['learning_conducted'])
        ) {
            $isEmpty = false;
            break;
        }
    }

    // If all fields are empty, write N/A in the learning_title column only
    if ($isEmpty) {
        $this->setXY($pdf, $x_title, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Otherwise, render the actual data
    foreach ($chunk as $index => $lnd) {
        $currentY = $startY + ($index * $rowHeight);

        $this->writeWrapped($pdf, $this->valueOrNa($lnd['learning_title'] ?? null), 105, $x_title, $currentY, $currentY - 1, 6, 2);

        $this->setXY($pdf, $x_type, $currentY);
        $pdf->Write(0, $this->valueOrNa($lnd['learning_type'] ?? null));

        $this->setXY($pdf, $x_from, $currentY);
        $pdf->Write(0, $this->dateOrNa($lnd['learning_from'] ?? null));

        $this->setXY($pdf, $x_to, $currentY);
        $pdf->Write(0, $this->dateOrNa($lnd['learning_to'] ?? null));

        $this->setXY($pdf, $x_hours, $currentY);
        $pdf->Write(0, $this->valueOrNa($lnd['learning_hours'] ?? null));

        $this->writeWrapped($pdf, $this->valueOrNa($lnd['learning_conducted'] ?? null), 47.3, $x_conducted, $currentY, $currentY - 1, 6, 2);
    }
}



private function writeOtherInformation($pdf, $skills, $distinctions, $organizations)
{
    // Column anchors (short-bond 2025 layout)
    $xSkill = 7.5;
    $xDistinction = 61.5;
    $xOrg = 162.0;
    $wSkill = 50.0;
    $wDistinction = 98.0;
    $wOrg = 41.5;
    $startY = 268.0;
    $rowHeight = 6.15;

    $skills = array_values(array_filter(array_map(fn($v) => trim((string) $v), (array) $skills), fn($v) => $v !== ''));
    $distinctions = array_values(array_filter(array_map(fn($v) => trim((string) $v), (array) $distinctions), fn($v) => $v !== ''));
    $organizations = array_values(array_filter(array_map(fn($v) => trim((string) $v), (array) $organizations), fn($v) => $v !== ''));

    if (empty($skills) && empty($distinctions) && empty($organizations)) {
        $this->writeCenteredFitted($pdf, 'N/A', $xSkill, $xSkill + $wSkill, $startY);
        $this->writeCenteredFitted($pdf, 'N/A', $xDistinction, $xDistinction + $wDistinction, $startY);
        $this->writeCenteredFitted($pdf, 'N/A', $xOrg, $xOrg + $wOrg, $startY);
        return;
    }

    for ($i = 0; $i < 7; $i++) {
        $y = $startY + ($i * $rowHeight);
        $this->writeFittedAt($pdf, $this->valueOrNa($skills[$i] ?? null), $xSkill, $y, $wSkill, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->valueOrNa($distinctions[$i] ?? null), $xDistinction, $y, $wDistinction, 7.0, 5.0);
        $this->writeFittedAt($pdf, $this->valueOrNa($organizations[$i] ?? null), $xOrg, $y, $wOrg, 6.5, 5.0);
    }
}



private function WriteC4Information($pdf, $userId)
{
    $misc = MiscInfos::where('user_id', $userId)->first();

    if ($misc && $misc->photo_upload) {
    $photoPath = storage_path('app/public/' . $misc->photo_upload);

    if (file_exists($photoPath)) {
        $pdf->Image($photoPath, 169.32, 194.15, 33.6, 37.32); // You can fine-tune position and size
    }
}

    if (!$misc || !$misc->photo_upload || !isset($photoPath) || !file_exists($photoPath)) {
        $this->writeCenteredFitted($pdf, 'N/A', 169.3, 202.9, 226.0);
    }

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
            'third_degree'  => ['yes' => [138, 25.3], 'no' => [159.7, 25.3]],
            'fourth_degree' => ['yes' => [138, 30.7], 'no' => [159.7, 30.7]],
            'guilty'        => ['yes' => [137.5, 46.9],   'no' => [159.8, 47]],
            'charged'       => ['yes' => [137.3, 63.8], 'no' => [160.8, 63.8]],
            'convicted'     => ['yes' => [137, 84.7], 'no' => [161.8, 84.7]],
            'separated'     => ['yes' => [137, 101.1],'no' => [162.3, 101.1]],
            'candidate'     => ['yes' => [137.4, 115],    'no' => [164.3, 115]],
            'resigned'      => ['yes' => [137.5, 125.3],'no' => [164.4, 125.3]],
            'immigrant'     => ['yes' => [137.2, 137],    'no' => [164.3, 137]],
            'indigenous'    => ['yes' => [137.2, 161.5],  'no' => [164.8, 161.5]],
            'disability'    => ['yes' => [137.2, 169.8],  'no' => [164.8, 169.8]],
            'solo_parent'   => ['yes' => [137.2, 178.8],  'no' => [164.8, 178.8]],
        ];

        foreach ($checkboxes as $key => $coord) {
            if ($key === 'third_degree') {
                $pos = $coord[strtolower($info[$key])];
            } else {
                $answer = $info[$key][0];
                $pos = $coord[strtolower($answer)];
            }
            $this->markCheckbox($pdf, $pos[0], $pos[1]);
        }

        $this->setFont($pdf, 'Arial', '', 8);

        // Detail fields
        $this->writeFittedAt($pdf, (string) ($info['fourth_degree'][1] ?? ''), 141.224, 40.3, 56, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['guilty'][1] ?? ''), 141.224, 56, 56, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['charged'][1] ?? ''), 163, 73, 40, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['charged'][2] ?? ''), 163, 77.5, 40, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['convicted'][1] ?? ''), 141.224, 94, 56, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['separated'][1] ?? ''), 141.224, 108, 56, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['candidate'][1] ?? ''), 163, 119, 40, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['resigned'][1] ?? ''), 163, 130, 40, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['immigrant'][1] ?? ''), 141.224, 145.5, 62, 7.0, 5.0);
        $this->writeFittedAt($pdf, (string) ($info['indigenous'][1] ?? ''), 177, 165.5, 26, 6.5, 4.5);
        $this->writeFittedAt($pdf, (string) ($info['disability'][1] ?? ''), 177, 174, 26, 6.5, 4.5);
        $this->writeFittedAt($pdf, (string) ($info['solo_parent'][1] ?? ''), 177, 182, 26, 6.5, 4.5);

        // Reference table
        $x_name = 8.0;
        $x_address = 87.0;
        $x_telno = 132.0;
        $y_refs = [209.0, 218.5, 228.0];

        foreach ($info['references'] as $i => $ref) {
            if ($i >= count($y_refs)) break;
            $y = $y_refs[$i];
            $this->writeFittedAt($pdf, $this->valueOrNa($ref['name'] ?? null), $x_name, $y, 74, 7.0, 5.0);
            $this->writeFittedAt($pdf, $this->valueOrNa($ref['address'] ?? null), $x_address, $y, 49, 7.0, 5.0);
            $this->writeFittedAt($pdf, $this->valueOrNa($ref['tel'] ?? null), $x_telno, $y, 14, 6.5, 5.0);
        }

        // ID Section
        $this->writeFittedAt($pdf, $this->valueOrNa($info['govt_id'] ?? null), 31, 268.0, 58, 7.0, 5.0); // Govt ID type
        $this->writeFittedAt($pdf, $this->valueOrNa($info['other_id'] ?? null), 31, 273.5, 58, 7.0, 5.0); // Govt ID number

        $issuePlace = trim((string) ($info['issue_place'] ?? ''));
        $issuedDate = $this->dateOrNa($info['issue_date'] ?? null);
        $issuedText = $issuePlace === ''
            ? $issuedDate
            : trim($issuePlace . ' | ' . ($issuedDate === 'N/A' ? '' : $issuedDate), " |\t\n\r\0\x0B");
        $this->writeFittedAt($pdf, $this->valueOrNa($issuedText), 31, 278.8, 86, 6.0, 4.6);

        // Fields not yet collected in the current PDS flow are explicitly marked N/A.
        $this->writeFittedAt($pdf, 'N/A', 72.0, 287.8, 22, 7.0, 5.0);   // "Subscribed and sworn ... this ____"
        $this->writeFittedAt($pdf, 'N/A', 128.0, 287.8, 22, 7.0, 5.0);  // Affiant line before "exhibiting ..."
        $this->writeCenteredFitted($pdf, 'N/A', 168.8, 202.8, 273.6);   // Right thumbmark box
        $this->writeCenteredFitted($pdf, 'N/A', 77.5, 149.3, 300.8);    // Person administering oath box
    }


// Move writeCentered to class method for cleaner passing
private function getWriteCentered()
{
    return function ($pdf, $text, $startX, $endX, $y)
    {
        $this->writeCenteredFitted($pdf, (string) $text, (float) $startX, (float) $endX, (float) $y);
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

private function clearLegacyHeaderNote(Fpdi $pdf): void
{
    // Disabled: user requested no white cover on the "CS Form No. 212 Revised 2025" label.
}

private function getPageXScale(): float
{
    return ($this->isShortBondTemplate && $this->currentTemplatePage >= 2) ? 0.895 : 1.0;
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

private function writeCenteredFitted($pdf, string $text, float $startX, float $endX, float $y): void
{
    $this->writeCenteredFittedSized($pdf, $text, $startX, $endX, $y, 8.0, 5.0);
}

private function writeCenteredFittedSized($pdf, string $text, float $startX, float $endX, float $y, float $baseSize, float $minSize): void
{
    $maxWidth = max(1.0, ($endX - $startX) - 0.5);
    [$display, $size, $width] = $this->fitTextToWidth($pdf, $text, $maxWidth, $baseSize, $minSize);
    $pageXScale = $this->getPageXScale();
    $usableWidth = $this->getEffectiveMaxWidth($endX - $startX);
    $leftPadding = max(0.0, (($usableWidth - $width) / 2) / $pageXScale);
    $centerX = $startX + $leftPadding;
    $this->setXY($pdf, $centerX, $y);
    $pdf->Write(0, $display);
    $this->setFont($pdf, 'Arial', '', 8);
}

private function writeFittedAt($pdf, string $text, float $x, float $y, float $maxWidth, float $baseSize = 8.0, float $minSize = 5.0): void
{
    [$display, $size, $_width] = $this->fitTextToWidth($pdf, $text, $maxWidth, $baseSize, $minSize);
    $this->setFont($pdf, 'Arial', '', $size);
    $this->setXY($pdf, $x, $y);
    $pdf->Write(0, $display);
    $this->setFont($pdf, 'Arial', '', 8);
}


private function writeWrapped($pdf, $text, $maxWidth, $x, $ySingle, $yMultiple, $font_size, $lineHeight)
{
    $text = trim((string) $text);
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
    $forceInline = $request->boolean('preview');

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

    $this->mapSet($map, 'C1', 'D55', $this->excelValue($educationalBackground?->jhs_school));
    $this->mapSet($map, 'C1', 'G55', $this->excelValue($educationalBackground?->jhs_basic));
    $this->mapSet($map, 'C1', 'J55', $this->excelDateMonthYear($educationalBackground?->jhs_from));
    $this->mapSet($map, 'C1', 'K55', $this->excelDateMonthYear($educationalBackground?->jhs_to));
    $this->mapSet($map, 'C1', 'L55', $this->excelValue($educationalBackground?->jhs_earned));
    $this->mapSet($map, 'C1', 'M55', $this->excelValue($educationalBackground?->jhs_year_graduated));
    $this->mapSet($map, 'C1', 'N55', $this->excelValue($educationalBackground?->jhs_academic_honors));

    $this->mapSet($map, 'C1', 'D56', $this->excelValue($voc['school'] ?? ''));
    $this->mapSet($map, 'C1', 'G56', $this->excelValue($voc['basic'] ?? ''));
    $this->mapSet($map, 'C1', 'J56', $this->excelDateMonthYear($voc['from'] ?? null));
    $this->mapSet($map, 'C1', 'K56', $this->excelDateMonthYear($voc['to'] ?? null));
    $this->mapSet($map, 'C1', 'L56', $this->excelValue($voc['earned'] ?? ''));
    $this->mapSet($map, 'C1', 'M56', $this->excelValue($voc['year_graduated'] ?? ''));
    $this->mapSet($map, 'C1', 'N56', $this->excelValue($voc['academic_honors'] ?? ''));

    $this->mapSet($map, 'C1', 'D57', $this->excelValue($col['school'] ?? ''));
    $this->mapSet($map, 'C1', 'G57', $this->excelValue($col['basic'] ?? ''));
    $this->mapSet($map, 'C1', 'J57', $this->excelDateMonthYear($col['from'] ?? null));
    $this->mapSet($map, 'C1', 'K57', $this->excelDateMonthYear($col['to'] ?? null));
    $this->mapSet($map, 'C1', 'L57', $this->excelValue($col['earned'] ?? ''));
    $this->mapSet($map, 'C1', 'M57', $this->excelValue($col['year_graduated'] ?? ''));
    $this->mapSet($map, 'C1', 'N57', $this->excelValue($col['academic_honors'] ?? ''));

    $this->mapSet($map, 'C1', 'D58', $this->excelValue($grd['school'] ?? ''));
    $this->mapSet($map, 'C1', 'G58', $this->excelValue($grd['basic'] ?? ''));
    $this->mapSet($map, 'C1', 'J58', $this->excelDateMonthYear($grd['from'] ?? null));
    $this->mapSet($map, 'C1', 'K58', $this->excelDateMonthYear($grd['to'] ?? null));
    $this->mapSet($map, 'C1', 'L58', $this->excelValue($grd['earned'] ?? ''));
    $this->mapSet($map, 'C1', 'M58', $this->excelValue($grd['year_graduated'] ?? ''));
    $this->mapSet($map, 'C1', 'N58', $this->excelValue($grd['academic_honors'] ?? ''));
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
    if (empty($cse)) {
        $this->mapSet($map, 'C2', 'B5', 'N/A');
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
    if (empty($we)) {
        $this->mapSet($map, 'C2', 'D18', 'N/A');
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
    if (empty($vw)) {
        $this->mapSet($map, 'C3', 'B6', 'N/A');
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
    if (empty($lnd)) {
        $this->mapSet($map, 'C3', 'B18', 'N/A');
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
        $this->mapSet($map, 'C4', 'B64', trim($this->excelValue($misc->govt_id_place_issued) . ' | ' . $this->excelDate($misc->govt_id_date_issued)));
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

    $sheet->setCellValue('D55', $this->excelValue($educationalBackground?->jhs_school));
    $sheet->setCellValue('G55', $this->excelValue($educationalBackground?->jhs_basic));
    $sheet->setCellValue('J55', $this->excelDateMonthYear($educationalBackground?->jhs_from));
    $sheet->setCellValue('K55', $this->excelDateMonthYear($educationalBackground?->jhs_to));
    $sheet->setCellValue('L55', $this->excelValue($educationalBackground?->jhs_earned));
    $sheet->setCellValue('M55', $this->excelValue($educationalBackground?->jhs_year_graduated));
    $sheet->setCellValue('N55', $this->excelValue($educationalBackground?->jhs_academic_honors));

    $sheet->setCellValue('D56', $this->excelValue($voc['school'] ?? ''));
    $sheet->setCellValue('G56', $this->excelValue($voc['basic'] ?? ''));
    $sheet->setCellValue('J56', $this->excelDateMonthYear($voc['from'] ?? null));
    $sheet->setCellValue('K56', $this->excelDateMonthYear($voc['to'] ?? null));
    $sheet->setCellValue('L56', $this->excelValue($voc['earned'] ?? ''));
    $sheet->setCellValue('M56', $this->excelValue($voc['year_graduated'] ?? ''));
    $sheet->setCellValue('N56', $this->excelValue($voc['academic_honors'] ?? ''));

    $sheet->setCellValue('D57', $this->excelValue($col['school'] ?? ''));
    $sheet->setCellValue('G57', $this->excelValue($col['basic'] ?? ''));
    $sheet->setCellValue('J57', $this->excelDateMonthYear($col['from'] ?? null));
    $sheet->setCellValue('K57', $this->excelDateMonthYear($col['to'] ?? null));
    $sheet->setCellValue('L57', $this->excelValue($col['earned'] ?? ''));
    $sheet->setCellValue('M57', $this->excelValue($col['year_graduated'] ?? ''));
    $sheet->setCellValue('N57', $this->excelValue($col['academic_honors'] ?? ''));

    $sheet->setCellValue('D58', $this->excelValue($grd['school'] ?? ''));
    $sheet->setCellValue('G58', $this->excelValue($grd['basic'] ?? ''));
    $sheet->setCellValue('J58', $this->excelDateMonthYear($grd['from'] ?? null));
    $sheet->setCellValue('K58', $this->excelDateMonthYear($grd['to'] ?? null));
    $sheet->setCellValue('L58', $this->excelValue($grd['earned'] ?? ''));
    $sheet->setCellValue('M58', $this->excelValue($grd['year_graduated'] ?? ''));
    $sheet->setCellValue('N58', $this->excelValue($grd['academic_honors'] ?? ''));

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
    if (empty($cse)) {
        $sheet->setCellValue('B5', 'N/A');
    }

    $we = array_slice($workExperienceRows, 0, 28);
    foreach ($we as $i => $row) {
        $excelRow = 18 + $i;
        $sheet->setCellValue("A{$excelRow}", $this->excelDate($row['work_exp_from'] ?? null));
        $sheet->setCellValue("C{$excelRow}", $this->excelDate($row['work_exp_to'] ?? null));
        $sheet->setCellValue("D{$excelRow}", $this->excelValue($row['work_exp_position'] ?? ''));
        $sheet->setCellValue("G{$excelRow}", $this->excelValue($row['work_exp_department'] ?? ''));
        $sheet->setCellValue("J{$excelRow}", $this->excelValue($row['work_exp_status'] ?? ''));
        $sheet->setCellValue("K{$excelRow}", $this->normalizeGovServiceFlag($row['work_exp_govt_service'] ?? null));
    }
    if (empty($we)) {
        $sheet->setCellValue('D18', 'N/A');
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
    if (empty($vw)) {
        $sheet->setCellValue('B6', 'N/A');
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
    if (empty($lnd)) {
        $sheet->setCellValue('B18', 'N/A');
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

    $criminalDetailsRaw = (string) ($misc->criminal_35_b ?? '');
    $criminalDetails = explode(',', $criminalDetailsRaw);
    $dateFiledRaw = trim((string) ($criminalDetails[0] ?? ''));
    $caseStatusRaw = isset($criminalDetails[1]) ? trim((string) implode(',', array_slice($criminalDetails, 1))) : '';

    $this->setExcelQuestion($sheet, 'I6', 'K6', strtolower((string) ($misc->related_34_a ?? 'no')) !== 'no');
    $this->setExcelQuestion($sheet, 'I8', 'K8', strtolower((string) ($misc->related_34_b ?? 'no')) !== 'no');
    $this->setExcelQuestion($sheet, 'I13', 'K13', strtolower((string) ($misc->guilty_35_a ?? 'no')) !== 'no');
    $this->setExcelQuestion($sheet, 'I18', 'K18', strtolower($criminalDetailsRaw) !== 'no' && trim($criminalDetailsRaw) !== '');
    $this->setExcelQuestion($sheet, 'I23', 'K23', strtolower((string) ($misc->convicted_36 ?? 'no')) !== 'no');
    $this->setExcelQuestion($sheet, 'I27', 'K27', strtolower((string) ($misc->separated_37 ?? 'no')) !== 'no');
    $this->setExcelQuestion($sheet, 'I31', 'K31', strtolower((string) ($misc->candidate_38 ?? 'no')) !== 'no');
    $this->setExcelQuestion($sheet, 'I34', 'K34', strtolower((string) ($misc->resigned_38_b ?? 'no')) !== 'no');
    $this->setExcelQuestion($sheet, 'I37', 'K37', strtolower((string) ($misc->immigrant_39 ?? 'no')) !== 'no');
    $this->setExcelQuestion($sheet, 'I43', 'K43', strtolower((string) ($misc->indigenous_40_a ?? 'no')) !== 'no');
    $this->setExcelQuestion($sheet, 'I45', 'K45', strtolower((string) ($misc->pwd_40_b ?? 'no')) !== 'no');
    $this->setExcelQuestion($sheet, 'I47', 'K47', strtolower((string) ($misc->solo_parent_40_c ?? 'no')) !== 'no');

    $sheet->setCellValue('G10', $this->excelValue($misc->related_34_b));
    $sheet->setCellValue('G14', $this->excelValue($misc->guilty_35_a));
    $sheet->setCellValue('H20', $this->excelDate($dateFiledRaw));
    $sheet->setCellValue('G21', $this->excelValue($caseStatusRaw));
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
    $sheet->setCellValue('B64', trim($this->excelValue($misc->govt_id_place_issued) . ' | ' . $this->excelDate($misc->govt_id_date_issued)));
    $sheet->setCellValue('F65', Carbon::now()->format('m/d/Y'));
}

private function setExcelQuestion($sheet, string $yesCell, string $noCell, bool $yes): void
{
    $sheet->setCellValue($yesCell, $yes ? 'X' : '');
    $sheet->setCellValue($noCell, $yes ? '' : 'X');
}

private function excelValue($value): string
{
    if ($value === null) {
        return '';
    }
    $text = trim((string) $value);
    return $text === '' ? '' : $text;
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

}
