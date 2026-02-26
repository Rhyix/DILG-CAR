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

class ExportPDSController
{
    public function exportPDS(Request $request)
    {
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
        $templatePath = resource_path('templates/PDS_fixed_V9.pdf');
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

        // ----------------------------
        // Page 1: Personal Info, Address, Family, Education
        // ----------------------------
        $templateId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
        $pdf->SetFont('Arial', '', 8);

        $this->writePersonalInfo($pdf, $personalInfo);
        $this->writeAddresses($pdf, $residential, $permanent, $this->getWriteCentered());
        $this->writeFamilyBackground($pdf, $familyBackground);
        $this->writeEducationalBackground($pdf, $educationalBackground);
        $this->stampRevisedHeader($pdf);

        $this->writeCollegeChunk($pdf, $collegeChunks[0] ?? []);
        $this->writeVocationalChunk($pdf, $vocationalChunks[0] ?? []);
        $this->writeGraduateChunk($pdf, $gradChunks[0] ?? []);
        $this->writeChildrenChunk($pdf, $childrenChunks[0] ?? []);

        $pdf->SetXY(163.5, 310.190);
        $pdf->Write(0, Carbon::now()->format('m/d/Y'));


        // Determine continuation needs
        $childrenHasOverflow = count($childrenChunks) > 1;
        $schoolHasOverflow = count($collegeChunks) > 1 || count($vocationalChunks) > 1 || count($gradChunks) > 1;

        // Write CONTINUED indicator for children if overflow exists
        if ($childrenHasOverflow) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(122, 242);
            $pdf->Write(0, "CONTINUED");
            $pdf->SetFont('Arial', '', 8);
        }

        // Write CONTINUED indicator for school chunks if overflow exists
        if ($schoolHasOverflow) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(43, 310);
            $pdf->Write(0, "CONTINUED");
            $pdf->SetFont('Arial', '', 8);
        }
        $this->stampRevisedFooter($pdf);

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
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            $pdf->SetXY(163.5, 310.190);
            $pdf->Write(0, Carbon::now()->format('m/d/Y'));

            $pdf->SetFont('Arial', '', 8);

            // Write only the name parts for identification on overflow pages
            $pdf->SetXY(41.5, 45.5);
            $pdf->Write(0, $personalInfo->surname);

            $pdf->SetXY(41.5, 52);
            $pdf->Write(0, $personalInfo->first_name);

            $pdf->SetXY(41.5, 58);
            $pdf->Write(0, $personalInfo->middle_name);

            $pdf->SetXY(165.2, 53);
            $pdf->Write(0, $personalInfo->name_extension);
            $this->stampRevisedHeader($pdf);

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
            $this->stampRevisedFooter($pdf);
        }

        // ----------------------------
        // Page 2: First CSE chunk + First WE chunk
        // ----------------------------

        $templateId = $pdf->importPage(2);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);

        // Write first CSE chunk (max 7 rows)
        $this->writeCivilServiceEligibilityChunk($pdf, $cseChunks[0] ?? []);

        // Write first WE chunk (max 27 rows)
        $this->writeWorkExperienceChunk($pdf, $weChunks[0] ?? []);


        // Determine CSE and WE continuation
        $cseHasOverflow = count($cseChunks) > 1;
        $weHasOverflow = count($weChunks) > 1;

        if ($cseHasOverflow) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(8, 80.5); // adjust XY for CSE continued placement
            $pdf->Write(0, "CONTINUED");
            $pdf->SetFont('Arial', '', 8);
        }

        if ($weHasOverflow) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(8, 308.5); // adjust XY for WE continued placement
            $pdf->Write(0, "CONTINUED");
            $pdf->SetFont('Arial', '', 8);
        }

        $pdf->SetXY(149.352, 310.190);
        $pdf->Write(0, Carbon::now()->format('m/d/Y'));
        $this->stampRevisedFooter($pdf);
        // ----------------------------
        // Overflow Pages: CSE overflow + WE overflow
        // ----------------------------

        // Handle CSE overflow pages
        for ($i = 1; $i < count($cseChunks); $i++) {
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            $pdf->SetXY(149.352, 310.190);
            $pdf->Write(0, Carbon::now()->format('m/d/Y'));
            $this->writeCivilServiceEligibilityChunk($pdf, $cseChunks[$i]);

            // If WE overflows, write next WE chunk here
            if ($hasWEOverflow && isset($weChunks[$i])) {
                $this->writeWorkExperienceChunk($pdf, $weChunks[$i]);
                unset($weChunks[$i]); // Mark as written
            }
            $this->stampRevisedFooter($pdf);
        }

        // Handle remaining WE overflow pages
        foreach ($weChunks as $index => $chunk) {
            if ($index == 0) continue; // Already written first WE chunk on Page 2
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            $pdf->SetXY(149.352, 310.190);
            $pdf->Write(0, Carbon::now()->format('m/d/Y'));
            $this->writeWorkExperienceChunk($pdf, $chunk);
            $this->stampRevisedFooter($pdf);
        }

        // ----------------------------
        // Page 3: First VW chunk + First LND chunk + First Others Chunk
        // ----------------------------

        $templateId = $pdf->importPage(3);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
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
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(8, 77.5); // adjust as needed for Voluntary Work
            $pdf->Write(0, "CONTINUED");
            $pdf->SetFont('Arial', '', 8);
        }

        if ($lndHasOverflow) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(8, 235); // adjust as needed for L&D
            $pdf->Write(0, "CONTINUED");
            $pdf->SetFont('Arial', '', 8);
        }

        if ($skillsHasOverflow || $distinctionsHasOverflow || $organizationsHasOverflow) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(8, 299); // adjust as needed for Other Information
            $pdf->Write(0, "CONTINUED");
            $pdf->SetFont('Arial', '', 8);
        }
        $this->stampRevisedFooter($pdf);

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
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            $pdf->SetXY(161, 305);
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
            $this->stampRevisedFooter($pdf);
        }

        $pdf->SetXY(161, 305);
        $pdf->Write(0, Carbon::now()->format('m/d/Y'));
        $this->stampRevisedFooter($pdf);

        // ----------------------------
        // Page 4: Other Information
        // ----------------------------

        $templateId = $pdf->importPage(4);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);

        // Write first C4 chunk (max 7 rows)
        $this->WriteC4Information($pdf, $user->id);

        $pdf->SetXY(113, 270);
        $pdf->Write(0, Carbon::now()->format('m/d/Y'));
        $this->stampRevisedFooter($pdf);

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


        $pdf->SetTitle('Personal Data Sheet');

        $timestamp = date('Y-m-d_His');
        $filename = "ExportPDS_{$timestamp}.pdf";

        // Detect if mobile
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $isMobile = preg_match('/Android|iPhone|iPad|iPod|webOS|BlackBerry|Windows Phone/i', $userAgent);

        $forceInline = $request->boolean('preview');

        if ($isMobile && !$forceInline) {
            // Save the PDF temporarily
            $tempPath = storage_path("app/public/{$filename}");
            $pdf->Output($tempPath, 'F');

            // Optionally store the path in session or flash data for download link
            // Redirect with success message
            return redirect()
                ->route('dashboard') // Change to your actual route
                ->with('success', 'PDF generated successfully! You may download it from your dashboard.');
        } else {
            // Preview inline on desktop
            $pdf->Output($filename, 'I');
            exit;
        }

        exit;
    }

    private function stampRevisedFooter(Fpdi $pdf): void
    {
        // Mask template footer revision text and enforce current revision label.
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(5, 318.0, 95, 6.0, 'F');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(7, 321.5);
        $pdf->Write(0, 'CS FORM 212 (Revised 2025)');
    }

    private function stampRevisedHeader(Fpdi $pdf): void
    {
        // Replace the top-left revision label on page 1 template.
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(5, 8.5, 32, 8.5, 'F');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY(7, 11.5);
        $pdf->Write(0, 'CS Form No. 212');
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetXY(7, 15.3);
        $pdf->Write(0, 'Revised 2025');
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


    private function writePersonalInfo($pdf, $info)
    {
        // CS ID No
        $pdf->SetXY(165, 35.5);
        $pdf->Write(0, $info?->cs_id_no ?? '');

        // Names
        $pdf->SetXY(41.5, 45.5);
        $pdf->Write(0, $info?->surname ?? 'N/A');

        $pdf->SetXY(41.5, 52);
        $pdf->Write(0, $info?->first_name ?? 'N/A');

        $pdf->SetXY(41.5, 58);
        $pdf->Write(0, $info?->middle_name ?? 'N/A');

        $pdf->SetXY(165.2, 53);
        $pdf->Write(0, $info?->name_extension ?? 'N/A');

        // Birth
        $pdf->SetXY(41.5, 66);
        $pdf->Write(0, !empty($info?->date_of_birth) ? Carbon::parse($info->date_of_birth)->format('m/d/Y') : 'N/A');

        $pdf->SetXY(41.5, 75);
        $pdf->Write(0, $info?->place_of_birth ?? 'N/A');

        // Sex
        if ($info?->sex == 'male')
        {
            $pdf->SetXY(43, 81);
            $pdf->Write(0, '/' ?? '');
        }
        elseif ($info?->sex == 'female')
        {
            $pdf->SetXY(73, 81);
            $pdf->Write(0, '/' ?? '');
        }

        // Civil Status
        switch ($info?->civil_status ?? '')
        {
            case 'single' : $pdf->SetXY(43, 87);
                break;
            case 'married' : $pdf->SetXY(73, 87);
                break;
            case 'widowed' : $pdf->SetXY(43, 90.7);
                break;
            case 'separated' : $pdf->SetXY(73, 90.7);
                break;
            default:
                $pdf->SetXY(43, 94);
            break;
            }
        $pdf->Write(0, '/');

        // Physical
        $pdf->SetXY(41.5, 101.5);
        $pdf->Write(0, $info?->height ?? 'N/A');

        $pdf->SetXY(41.5, 108.5);
        $pdf->Write(0, $info?->weight ?? 'N/A');

        $pdf->SetXY(41.5, 115);
        $pdf->Write(0, $info?->blood_type ?? 'N/A');

        // IDs
        $pdf->SetXY(41.5, 122);
        $pdf->Write(0, $info?->gsis_id_no ?? 'N/A');

        $pdf->SetXY(41.5, 129);
        $pdf->Write(0, $info?->pagibig_id_no ?? 'N/A');

        $pdf->SetXY(41.5, 136);
        $pdf->Write(0, $info?->philhealth_no ?? 'N/A');

        $pdf->SetXY(41.5, 143);
        $pdf->Write(0, $info?->sss_id_no ?? 'N/A');

        $pdf->SetXY(41.5, 150);
        $pdf->Write(0, $info?->tin_no ?? 'N/A');

        $pdf->SetXY(41.5, 156.5);
        $pdf->Write(0, $info?->agency_employee_no ?? 'N/A');

        // Citizenship
        if ($info?->citizenship == 'Filipino')
        {
            $pdf->SetXY(140.5, 65);
            $pdf->Write(0, '/' ?? '');
        }
        elseif ($info?->citizenship == 'Dual Citizenship')
        {
            $pdf->SetXY(159, 65);
            $pdf->Write(0, '/' ?? '');
        }

        if ($info?->dual_type == 'By birth')
        {
            $pdf->SetXY(164.5, 68.5);
            $pdf->Write(0, '/' ?? '');
        }
        elseif ($info?->dual_type == 'By naturalization')
        {
            $pdf->SetXY(180.5, 68.5);
            $pdf->Write(0, '/' ?? '');
        }

        $pdf->SetXY(140.5, 81);
        $pdf->Write(0, $info?->dual_country ?? '');

        $pdf->SetXY(123, 143);
        $pdf->Write(0, $info?->telephone_no ?? 'N/A');

        $pdf->SetXY(123, 150);
        $pdf->Write(0, $info?->mobile_no ?? 'N/A');

        $pdf->SetXY(123, 156.5);
        $pdf->Write(0, $info?->email_address ?? 'N/A');

}

private function writeAddresses($pdf, $residential, $permanent, $writeCentered)
{
    // Residential Address
    $writeCentered($pdf, $residential[0] ?? 'N/A', 133, 149, 87); // House Number
    $writeCentered($pdf, $residential[1] ?? 'N/A', 185, 190, 87); // Street
    $writeCentered($pdf, $residential[2] ?? 'N/A', 133, 149, 94); // Village/Subdivision
    $writeCentered($pdf, $residential[3] ?? 'N/A', 185, 190, 94); // Barangay
    $writeCentered($pdf, $residential[4] ?? 'N/A', 133, 149, 100.5); // City/Municipality
    $writeCentered($pdf, $residential[5] ?? 'N/A', 185, 190, 100.5); // Province
    $writeCentered($pdf, $residential[6] ?? 'N/A', 133, 190, 108); // ZIP Code

    // Permanent Address
    $writeCentered($pdf, $permanent[0] ?? 'N/A', 133, 149, 114); // House Number
    $writeCentered($pdf, $permanent[1] ?? 'N/A', 185, 190, 114); // Street
    $writeCentered($pdf, $permanent[2] ?? 'N/A', 133, 149, 120.5); // Village/Subdivision
    $writeCentered($pdf, $permanent[3] ?? 'N/A', 185, 190, 120.5); // Barangay
    $writeCentered($pdf, $permanent[4] ?? 'N/A', 133, 149, 127.5); // City/Municipality
    $writeCentered($pdf, $permanent[5] ?? 'N/A', 185, 190, 127.5); // Province
    $writeCentered($pdf, $permanent[6] ?? 'N/A', 133, 190, 135.5); // ZIP Code

}

private function writeFamilyBackground($pdf, $family)
{
    $pdf->SetXY(41.5, 167.5);
    $pdf->Write(0, $family?->spouse_surname ?? 'N/A');

    $pdf->SetXY(41.5, 173.5);
    $pdf->Write(0, $family?->spouse_first_name ?? 'N/A');

    $pdf->SetXY(41.5, 179.5);
    $pdf->Write(0, $family?->spouse_middle_name ?? 'N/A');

    $pdf->SetXY(93, 174.2);
    $pdf->Write(0, $family?->spouse_name_extension ?? 'N/A');

    $pdf->SetXY(41.5, 185);
    $pdf->Write(0, $family?->spouse_occupation ?? 'N/A');

    $pdf->SetXY(41.5, 190.2);
    $pdf->Write(0, $family?->spouse_employer ?? 'N/A');

    $pdf->SetXY(41.5, 196.2);
    $pdf->Write(0, $family?->spouse_business_address ?? 'N/A');

    $pdf->SetXY(41.5, 202.2);
    $pdf->Write(0, $family?->spouse_telephone ?? 'N/A');

    $pdf->SetXY(41.5, 208.2);
    $pdf->Write(0, $family?->father_surname ?? 'N/A');

    $pdf->SetXY(41.5, 213.8);
    $pdf->Write(0, $family?->father_first_name ?? 'N/A');

    $pdf->SetXY(93, 215);
    $pdf->Write(0, $family?->father_name_extension ?? 'N/A');

    $pdf->SetXY(41.5, 219.9);
    $pdf->Write(0, $family?->father_middle_name ?? 'N/A');

    $pdf->SetXY(41.5, 231.2);
    $pdf->Write(0, $family?->mother_maiden_surname ?? 'N/A');

    $pdf->SetXY(41.5, 237.2);
    $pdf->Write(0, $family?->mother_maiden_first_name ?? 'N/A');

    $pdf->SetXY(41.5, 243.2);
    $pdf->Write(0, $family?->mother_maiden_middle_name ?? 'N/A');

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
        $pdf->SetXY($startX_name, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Otherwise, loop through and write child data
    foreach ($chunk as $index => $child)
    {
        $currentY = $startY + ($index * $lineHeight);

        $pdf->SetXY($startX_name, $currentY);
        $pdf->Write(0, $child['name'] ?? '');

        $pdf->SetXY($startX_birthdate, $currentY);
        $pdf->Write(0, !empty($child['dob']) ? Carbon::parse($child['dob'])->format('m/d/Y') : '');
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
        $pdf->SetXY(41.5, 267);
        $pdf->Write(0, 'N/A');
    } else {
        $this->writeWrapped($pdf, $education?->elem_school ?? '', 49, 41.5, 267, 265, 6, 3);
        $this->writeWrapped($pdf, $education?->elem_basic ?? '', 45, 91, 267, 264, 6, 2);
        $this->writeWrapped($pdf, !empty($education?->elem_from) ? Carbon::parse($education?->elem_from)->format('m/Y') : '', 20, 138, 267, 264, 8, 2);
        $this->writeWrapped($pdf, !empty($education?->elem_to) ? Carbon::parse($education?->elem_to)->format('m/Y') : '', 20, 150, 267, 264, 8, 2);
        $pdf->SetXY(163.5, 267);
        $pdf->Write(0, $education?->elem_earned ?? 'N/A');
        $pdf->SetXY(183, 267);
        $pdf->Write(0, $education?->elem_year_graduated ?? 'N/A');

        $pdf->SetFont('Arial', '', 4);

        // Set parameters
        $text =  $education?->jhs_academic_honors ?? 'N/A';
        $maxWidth = 7; // Width to fit the text in
        $fontSize = 4;
        $lineHeight = 2;

        // Calculate the string width
        $stringWidth = $pdf->GetStringWidth($text);

        // Check if it fits in one line
        if ($stringWidth <= $maxWidth) {
            // It fits in one line, write directly
            $pdf->SetFont('Arial', '', $fontSize);
            $pdf->SetXY(197, 267);
            $pdf->Cell($maxWidth, $lineHeight, $text, 0, 0);
        } else {
            // It doesn't fit, wrap text
            $this->writeWrapped($pdf, $text, $maxWidth, 197, 275, 265.2, $fontSize, $lineHeight);
        }

        // Reset font to 8pt
        $pdf->SetFont('Arial', '', 8);
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
        $pdf->SetXY(41.5, 276);
        $pdf->Write(0, 'N/A');
    } else {
        $this->writeWrapped($pdf, $education?->jhs_school ?? '', 50, 41.5, 275, 273, 6, 3);
        $this->writeWrapped($pdf, $education?->jhs_basic ?? '', 45, 91, 275, 271.7, 6, 2);
        $this->writeWrapped($pdf, !empty($education?->jhs_from) ? Carbon::parse($education?->jhs_from)->format('m/Y') : '', 20, 138, 275, 271.2, 8, 2);
        $this->writeWrapped($pdf, !empty($education?->jhs_to) ? Carbon::parse($education?->jhs_to)->format('m/Y') : '', 20, 150, 275, 271.2, 8, 2);
        $pdf->SetXY(163.5, 275);
        $pdf->Write(0, $education?->jhs_earned ?? 'N/A');
        $pdf->SetXY(183, 275);
        $pdf->Write(0, $education?->jhs_year_graduated ?? 'N/A');
        
        $pdf->SetFont('Arial', '', 4);

        // Set parameters
        $text =  $education?->jhs_academic_honors ?? 'N/A';
        $maxWidth = 7; // Width to fit the text in
        $fontSize = 4;
        $lineHeight = 2;

        // Calculate the string width
        $stringWidth = $pdf->GetStringWidth($text);

        // Check if it fits in one line
        if ($stringWidth <= $maxWidth) {
            // It fits in one line, write directly
            $pdf->SetFont('Arial', '', $fontSize);
            $pdf->SetXY(197, 275);
            $pdf->Cell($maxWidth, $lineHeight, $text, 0, 0);
        } else {
            // It doesn't fit, wrap text
            $this->writeWrapped($pdf, $text, $maxWidth, 197, 275, 273.2, $fontSize, $lineHeight);
        }

        // Reset font to 8pt
        $pdf->SetFont('Arial', '', 8);
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
    $startX_honors = 195;
    $startY = 282;
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
        $pdf->SetXY($startX_school, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Otherwise, loop and write each row
    foreach ($chunk as $index => $voc)
    {
        $currentY = $startY + ($index * $lineHeight);

        $pdf->SetXY($startX_school, $currentY);
        $this->writeWrapped($pdf, $voc['school'] ?? '', 50, $startX_school, $currentY, $currentY-1, 6, 3);

        $pdf->SetXY($startX_basic, $currentY);
        $this->writeWrapped($pdf, $voc['basic'] ?? '', 50, $startX_basic, $currentY, $currentY-1, 6, 3);

        $this->writeWrapped($pdf, !empty($voc['from'] ?? '') ? Carbon::parse($voc['from'])->format('m/Y') : '', 20, $startX_from, $currentY, $currentY-1, 8, 2);
        $this->writeWrapped($pdf, !empty($voc['to'] ?? '') ? Carbon::parse($voc['to'])->format('m/Y') : '', 20, $startX_to, $currentY, $currentY-1, 8, 2);

        $pdf->SetXY($startX_earned, $currentY);
        $pdf->Write(0, $voc['earned'] ?? 'N/A');

        $pdf->SetXY($startX_year_graduated, $currentY);
        $pdf->Write(0, $voc['year_graduated'] ?? 'N/A');

        
        $pdf->SetFont('Arial', '', 4);

        // Set parameters
        $text = $voc['academic_honors'] ?? 'N/A';
        $maxWidth =7; // Width to fit the text in
        $fontSize = 4;
        $lineHeight = 2;

        // Calculate the string width
        $stringWidth = $pdf->GetStringWidth($text);

        // Check if it fits in one line
        if ($stringWidth <= $maxWidth) {
            // It fits in one line, write directly
            $pdf->SetFont('Arial', '', $fontSize);
            $pdf->SetXY($startX_honors, $currentY-1);
            $pdf->Cell($maxWidth, $lineHeight, $text, 0, 0);
        } else {
            // It doesn't fit, wrap text
            $this->writeWrapped($pdf, $text, $maxWidth, $startX_honors, $currentY, $currentY - 1.5, $fontSize, $lineHeight);
        }

        // Reset font to 8pt
        $pdf->SetFont('Arial', '', 8);
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
    $startX_honors = 197;
    $startY = 291;
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
        $pdf->SetXY($startX_school, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Otherwise, render the college entries normally
    foreach ($chunk as $index => $college)
    {
        $currentY = $startY + ((int)$index * $lineHeight);

        $pdf->SetXY($startX_school, $currentY);
        $this->writeWrapped($pdf, $college['school'] ?? '', 50, $startX_school, $currentY, $currentY-1, 6, 3);

        $pdf->SetXY($startX_basic, $currentY);
        $this->writeWrapped($pdf, $college['basic'] ?? '', 50, $startX_basic, $currentY, $currentY-1, 6, 3);

        $this->writeWrapped($pdf, !empty($college['from'] ?? '') ? Carbon::parse($college['from'])->format('m/Y') : '', 20, $startX_from, $currentY, $currentY-1, 8, 2);
        $this->writeWrapped($pdf, !empty($college['to'] ?? '') ? Carbon::parse($college['to'])->format('m/Y') : '', 20, $startX_to, $currentY, $currentY-1, 8, 2);

        $pdf->SetXY($startX_earned, $currentY);
        $pdf->Write(0, $college['earned'] ?? 'N/A');

        $pdf->SetXY($startX_year_graduated, $currentY);
        $pdf->Write(0, $college['year_graduated'] ?? 'N/A');

        $pdf->SetFont('Arial', '', 4);

        // Set parameters
        $text = $college['academic_honors'] ?? 'N/A';
        $maxWidth = 7; // Width to fit the text in
        $fontSize = 4;
        $lineHeight = 2;

        // Calculate the string width
        $stringWidth = $pdf->GetStringWidth($text);

        // Check if it fits in one line
        if ($stringWidth <= $maxWidth) {
            // It fits in one line, write directly
            $pdf->SetFont('Arial', '', $fontSize);
            $pdf->SetXY($startX_honors, $currentY-1);
            $pdf->Cell($maxWidth, $lineHeight, $text, 0, 0);
        } else {
            // It doesn't fit, wrap text
            $this->writeWrapped($pdf, $text, $maxWidth, $startX_honors, $currentY, $currentY - 1.5, $fontSize, $lineHeight);
        }

        // Reset font to 8pt
        $pdf->SetFont('Arial', '', 8);

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
    $startX_honors = 195;
    $startY = 298;
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
        $pdf->SetXY($startX_school, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Otherwise, loop through and write each graduate row
    foreach ($chunk as $index => $grad)
    {
        $currentY = $startY + ($index * $lineHeight);

        $pdf->SetXY($startX_school, $currentY);
        $this->writeWrapped($pdf, $grad['school'] ?? '', 50, $startX_school, $currentY, $currentY-1, 6, 3);

        $pdf->SetXY($startX_basic, $currentY);
        $this->writeWrapped($pdf, $grad['basic'] ?? '', 50, $startX_basic, $currentY, $currentY-1, 6, 3);

        $this->writeWrapped($pdf, !empty($grad['from'] ?? '') ? Carbon::parse($grad['from'])->format('m/Y') : '', 20, $startX_from, $currentY, $currentY-1, 8, 2);
        $this->writeWrapped($pdf, !empty($grad['to'] ?? '') ? Carbon::parse($grad['to'])->format('m/Y') : '', 20, $startX_to, $currentY, $currentY-1, 8, 2);

        $pdf->SetXY($startX_earned, $currentY);
        $pdf->Write(0, $grad['earned'] ?? 'N/A');

        $pdf->SetXY($startX_year_graduated, $currentY);
        $pdf->Write(0, $grad['year_graduated'] ?? 'N/A');


        $pdf->SetFont('Arial', '', 4);

        // Set parameters
        $text = $grad['academic_honors'] ?? 'N/A';
        $maxWidth = 7; // Width to fit the text in
        $fontSize = 4;
        $lineHeight = 2;

        // Calculate the string width
        $stringWidth = $pdf->GetStringWidth($text);

        // Check if it fits in one line
        if ($stringWidth <= $maxWidth) {
            // It fits in one line, write directly
            $pdf->SetFont('Arial', '', $fontSize);
            $pdf->SetXY($startX_honors, $currentY-1);
            $pdf->Cell($maxWidth, $lineHeight, $text, 0, 0);
        } else {
            // It doesn't fit, wrap text
            $this->writeWrapped($pdf, $text, $maxWidth, $startX_honors, $currentY, $currentY - 1.5, $fontSize, $lineHeight);
        }

        // Reset font to 8pt
        $pdf->SetFont('Arial', '', 8);
    }
}



private function writeCivilServiceEligibilityChunk($pdf, $chunk)
{
    // Layout setup
    $startX_career = 6;
    $startX_rating = 71;
    $startX_date = 94;
    $startX_place = 118;
    $startX_license = 178.2;
    $startX_validity = 192.16;

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
        $pdf->SetXY($startX_career, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Render each entry
    foreach ($chunk as $index => $cse) {
        $currentY = $startY + ($index * $rowHeight);

        // Career service
        $this->writeWrapped($pdf, $cse['cs_eligibility_career'] ?? '', 60, $startX_career, $currentY, $currentY - 0.5, 8, 3);

        // Rating
        $pdf->SetXY($startX_rating, $currentY);
        $pdf->Write(0, $cse['cs_eligibility_rating'] ?? '');

        // Date of examination
        $pdf->SetXY($startX_date, $currentY);
        $pdf->Write(0, !empty($cse['cs_eligibility_date']) ? Carbon::parse($cse['cs_eligibility_date'])->format('m-d-Y') : '');

        // Place of examination
        $pdf->SetXY($startX_place, $currentY);
        $pdf->Write(0, $cse['cs_eligibility_place'] ?? '');

        // License number
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->SetXY($startX_license, $currentY);
        $pdf->Write(0, $cse['cs_eligibility_license'] ?? '');

        // Validity date
        $pdf->SetXY($startX_validity, $currentY);
        $pdf->Write(0, !empty($cse['cs_eligibility_validity']) ? Carbon::parse($cse['cs_eligibility_validity'])->format('m-d-Y') : '');

        // Reset font size
        $pdf->SetFont('Arial', '', 8);
    }
}

private function writeWorkExperienceChunk($pdf, $chunk)
{
    // Column X positions
    $x_from = 4.064;
    $x_to = 21.59;
    $x_position = 40.132;
    $x_agency = 94.488;
    $x_salary = 149.352;
    $x_grade = 166.624;
    $x_status = 177.65;
    $x_gov = 199.136;

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
            !empty($we['work_exp_salary']) ||
            !empty($we['work_exp_grade']) ||
            !empty($we['work_exp_status']) ||
            !empty($we['work_exp_govt_service'])
        ) {
            $isEmpty = false;
            break;
        }
    }

    // If all are empty, write N/A in the position field only
    if ($isEmpty) {
        $pdf->SetXY($x_position, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Otherwise, render each row normally
    foreach ($chunk as $index => $we) {
        $currentY = $startY + ($index * $rowHeight);

        $pdf->SetXY($x_from, $currentY);
        $pdf->Write(0, !empty($we['work_exp_from']) ? Carbon::parse($we['work_exp_from'])->format('m/d/Y') : '');

        $pdf->SetXY($x_to, $currentY);
        $pdf->Write(0, !empty($we['work_exp_to']) ? Carbon::parse($we['work_exp_to'])->format('m/d/Y') : '');

        $pdf->SetXY($x_position, $currentY);
        $pdf->Write(0, $we['work_exp_position'] ?? '');

        $this->writeWrapped($pdf, $we['work_exp_department'] ?? '', 55, $x_agency, $currentY, $currentY - 1.5, 6, 2);

        $pdf->SetXY($x_salary, $currentY);
        $pdf->Write(0, $we['work_exp_salary'] ?? '');

        $pdf->SetXY($x_grade, $currentY);
        $pdf->Write(0, $we['work_exp_grade'] ?? '');

        $this->writeWrapped($pdf, $we['work_exp_status'] ?? '', 20, $x_status, $currentY, $currentY - 1.5, 6, 2);

        $pdf->SetXY($x_gov, $currentY);
        $pdf->Write(0, isset($we['work_exp_govt_service']) ? ($we['work_exp_govt_service'] ? 'Y' : 'N') : '');
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
        $pdf->SetXY($x_org, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Render each voluntary work row
    foreach ($chunk as $index => $vw) {
        $currentY = $startY + ($index * $rowHeight);

        $this->writeWrapped($pdf, $vw['voluntary_org'] ?? '', 115, $x_org, $currentY, $currentY - 1, 6, 2);

        $pdf->SetXY($x_from, $currentY);
        $pdf->Write(0, !empty($vw['voluntary_from']) ? Carbon::parse($vw['voluntary_from'])->format('m/d/Y') : '');

        $pdf->SetXY($x_to, $currentY);
        $pdf->Write(0, !empty($vw['voluntary_to']) ? Carbon::parse($vw['voluntary_to'])->format('m/d/Y') : '');

        $pdf->SetXY($x_hours, $currentY);
        $pdf->Write(0, $vw['voluntary_hours'] ?? '');

        $this->writeWrapped($pdf, $vw['voluntary_position'] ?? '', 60, $x_position, $currentY, $currentY - 1, 7, 3);
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
        $pdf->SetXY($x_title, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Otherwise, render the actual data
    foreach ($chunk as $index => $lnd) {
        $currentY = $startY + ($index * $rowHeight);

        $this->writeWrapped($pdf, $lnd['learning_title'] ?? '', 105, $x_title, $currentY, $currentY - 1, 6, 2);

        $pdf->SetXY($x_type, $currentY);
        $pdf->Write(0, $lnd['learning_type'] ?? '');

        $pdf->SetXY($x_from, $currentY);
        $pdf->Write(0, !empty($lnd['learning_from']) ? Carbon::parse($lnd['learning_from'])->format('m/d/Y') : '');

        $pdf->SetXY($x_to, $currentY);
        $pdf->Write(0, !empty($lnd['learning_to']) ? Carbon::parse($lnd['learning_to'])->format('m/d/Y') : '');

        $pdf->SetXY($x_hours, $currentY);
        $pdf->Write(0, $lnd['learning_hours'] ?? '');

        $this->writeWrapped($pdf, $lnd['learning_conducted'] ?? '', 47.3, $x_conducted, $currentY, $currentY - 1, 6, 2);
    }
}



private function writeOtherInformation($pdf, $skills, $distinctions, $organizations)
{
    // Define x-coordinates for each column
    $x_skill = 7;
    $x_distinction = 60;
    $x_org = 161;

    // Define starting Y and row height
    $startY = 256;
    $rowHeight = 6.5;

    // Check if all arrays are empty
    if (
        empty(array_filter($skills)) &&
        empty(array_filter($distinctions)) &&
        empty(array_filter($organizations))
    ) {
        $pdf->SetXY($x_skill, $startY);
        $pdf->Write(0, 'N/A');
        $pdf->SetXY($x_distinction, $startY);
        $pdf->Write(0, 'N/A');
        $pdf->SetXY($x_org, $startY);
        $pdf->Write(0, 'N/A');
        return;
    }

    // Track Y position for each column separately
    $y_skill = $startY;
    $y_distinction = $startY;
    $y_org = $startY;

    // Write non-empty skills
    foreach ($skills as $skill) {
        if (trim($skill) !== '') {
            $pdf->SetXY($x_skill, $y_skill);
            $this->writeWrapped($pdf, $skill, 30, $x_skill, $y_skill, $y_skill - 1, 6, 3);
            $y_skill += $rowHeight;
        }
    }

    // Write non-empty distinctions
    foreach ($distinctions as $distinction) {
        if (trim($distinction) !== '') {
            $pdf->SetXY($x_distinction, $y_distinction);
            $this->writeWrapped($pdf, $distinction, 30, $x_distinction, $y_distinction, $y_distinction - 1, 6, 3);
            $y_distinction += $rowHeight;
        }
    }

    // Write non-empty organizations
    foreach ($organizations as $organization) {
        if (trim($organization) !== '') {
            $pdf->SetXY($x_org, $y_org);
            $this->writeWrapped($pdf, $organization, 55, $x_org, $y_org, $y_org - 1, 6, 2);
            $y_org += $rowHeight;
        }
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
            $pdf->SetXY($pos[0], $pos[1]);
            $pdf->Write(0, '/');
        }

        $pdf->SetFont('Arial', '', 8);

        // Detail fields
        $pdf->SetXY(141.224, 40.3);  $pdf->Write(0, $info['fourth_degree'][1]  ?? '');
        $pdf->SetXY(141.224, 56);    $pdf->Write(0, $info['guilty'][1] ?? '');
        $pdf->SetXY(163, 73);        $pdf->Write(0, $info['charged'][1] ?? '');
        $pdf->SetXY(163, 77.5);      $pdf->Write(0, $info['charged'][2] ?? '');
        $pdf->SetXY(141.224, 94);    $pdf->Write(0, $info['convicted'][1] ?? '');
        $pdf->SetXY(141.224, 108);   $pdf->Write(0, $info['separated'][1] ?? '');
        $pdf->SetXY(163, 119);       $pdf->Write(0, $info['candidate'][1] ?? '');
        $pdf->SetXY(163, 130);       $pdf->Write(0, $info['resigned'][1] ?? '');
        $pdf->SetXY(141.224, 145.5); $pdf->Write(0, $info['immigrant'][1] ?? '');
        $pdf->SetXY(177, 165.5);     $pdf->Write(0, $info['indigenous'][1] ?? '');
        $pdf->SetXY(177, 174);       $pdf->Write(0, $info['disability'][1] ?? '');
        $pdf->SetXY(177, 182);       $pdf->Write(0, $info['solo_parent'][1] ?? '');

        // Reference table
        $x_name = 5.145;
        $x_address = 84;
        $x_telno = 134;
        $y_refs = [203, 210, 218];

        foreach ($info['references'] as $i => $ref) {
            if ($i >= count($y_refs)) break;
            $y = $y_refs[$i];
            $pdf->SetXY($x_name, $y);    $pdf->Write(0, $ref['name'] ?? '');
            $this->writeWrapped($pdf, $ref['address'] ?? '', 50, $x_address, $y, $y - 1.5, 6, 2);
            $pdf->SetXY($x_telno, $y);   $pdf->Write(0, $ref['tel'] ?? '');
        }

        // ID Section
        $pdf->SetXY(31, 258.3);
        $pdf->Write(0, $info['govt_id'] ?? ''); // Only govt ID type

        $pdf->SetXY(31, 265);
        $pdf->Write(0, $info['other_id'] ?? ''); // Now shows govt_id_number only

        $formattedIssuedDate = $info['issue_date'] ? Carbon::parse($info['issue_date'])->format('m/d/Y') : '';
        $pdf->SetXY(31, 271.9);
        $pdf->Write(0, $info['issue_place'] . ' | ' . $formattedIssuedDate ?? '');
    }


// Move writeCentered to class method for cleaner passing
private function getWriteCentered()
{
    return function ($pdf, $text, $startX, $endX, $y)
    {
        $textWidth = $pdf->GetStringWidth($text);
        $centerX = $startX + (($endX - $startX) - $textWidth) / 2;
        $pdf->SetXY($centerX, $y);
        $pdf->Write(0, $text);
    };
}


private function writeWrapped($pdf, $text, $maxWidth, $x, $ySingle, $yMultiple, $font_size, $lineHeight)
{
    $words = explode(' ', $text);
    $currentLine = '';
    $lines = [];

    foreach ($words as $word) {
        $testLine = trim($currentLine . ' ' . $word);
        $testWidth = $pdf->GetStringWidth($testLine);

        if ($testWidth > $maxWidth && $currentLine !== '') {
            $lines[] = $currentLine;
            $currentLine = $word;
        } else {
            $currentLine = $testLine;
        }
    }

    // Add the last line
    if ($currentLine !== '') {
        $lines[] = $currentLine;
    }

    // Determine font size based on number of lines
    if (count($lines) == 1) {
        $pdf->SetFont('Arial', '', 8);
        $currentY = $ySingle;
    } else {
        $pdf->SetFont('Arial', '', $font_size);
        $currentY = $yMultiple;
    }

    // Write lines
    foreach ($lines as $line) {
        $pdf->SetXY($x, $currentY);
        $pdf->Write(0, $line);
        $currentY += $lineHeight;
    }
    $pdf->SetFont('Arial', '', 8);
}

}
