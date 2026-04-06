<?php


namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\JobVacancy;
use App\Models\ExamDetail;
use App\Models\ExamItems;
use App\Models\Applications;
use App\Models\AdminVacancyAccess;
use App\Models\VacancyTitle;
use Illuminate\Http\Request;
use App\Models\Vacancy;
use App\Models\UploadedDocument;
use App\Models\PersonalInformation;
use App\Models\WorkExperience;
use App\Models\CivilServiceEligibility;
use App\Models\LearningAndDevelopment;
use App\Models\VoluntaryWork;
use App\Models\OtherInformation;
use App\Models\FamilyBackground;
use App\Models\EducationalBackground;
use App\Models\MiscInfos;
use App\Support\ApplicantOnboarding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use App\Models\WorkExpSheet;

class JobVacancyController extends Controller
{
    private const DOCUMENT_TYPE_ALIASES = [
        'cert_eligibility' => ['cert_elegibility'],
        'cert_employment' => ['certificate_employment'],
        'grade_masteraldoctorate' => ['certificate_grades'],
        'tor_masteraldoctorate' => ['certified_tor'],
        'ipcr' => ['performance_rating'],
        'non_academic' => ['non_academic_awards'],
        'cert_training' => ['certificates_participation'],
        'designation_order' => ['designation_orders'],
        'transcript_records' => ['transcript'],
        'photocopy_diploma' => ['diploma'],
    ];

    private const COS_REQUIRED_DOCUMENTS = [
        'passport_photo',
        'signed_pds',
        'signed_work_exp_sheet',
        'photocopy_diploma',
        'application_letter',
        'cert_training',
    ];

    private const EDUCATION_RULE_PARSER_VERSION = 2;

    private const ELIGIBILITY_CANONICAL_LABELS = [
        'csc_professional' => 'CSC Professional Eligibility',
        'csc_subprofessional' => 'Subprofessional (Sub-Prof) Eligibility',
        'bar_board' => 'Bar/Board Eligibility',
        'honor_graduate' => 'Honor Graduate Eligibility',
        'foreign_honor_graduate' => 'Foreign School Honor Graduate Eligibility',
        'barangay_health_worker' => 'Barangay Health Worker Eligibility',
        'barangay_nutrition_scholar' => 'Barangay Nutrition Scholar Eligibility',
        'barangay_official' => 'Barangay Official Eligibility',
        'sanggunian_member' => 'Sanggunian Member Eligibility',
        'skills_category_ii' => 'Skills Eligibility-Category II',
        'edp_specialist' => 'Electronic Data Processing Specialist Eligibility',
        'scientific_technological_specialist' => 'Scientific and Technological Specialist Eligibility',
    ];

    private function currentAdmin()
    {
        return Auth::guard('admin')->user();
    }

    private function isHrDivisionAdmin(): bool
    {
        return (($this->currentAdmin()->role ?? null) === 'hr_division');
    }

    private function supportsVacancyCreatorColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        try {
            $hasColumn = Schema::hasColumn('job_vacancies', 'created_by_admin_id');
        } catch (\Throwable $e) {
            $hasColumn = false;
            Log::warning('Unable to detect job_vacancies.created_by_admin_id column.', [
                'error' => $e->getMessage(),
            ]);
        }

        return $hasColumn;
    }

    private function hrDivisionGrantedVacancyIds(int $adminId): array
    {
        if ($adminId <= 0 || !Schema::hasTable('admin_vacancy_accesses')) {
            return [];
        }

        return AdminVacancyAccess::query()
            ->where('admin_id', $adminId)
            ->pluck('vacancy_id')
            ->map(fn($value) => trim((string) $value))
            ->filter(fn($value) => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function grantHrDivisionAccessToVacancy(string $vacancyId, ?int $adminId = null): void
    {
        if (!$this->isHrDivisionAdmin()) {
            return;
        }

        $adminId = $adminId ?? (int) ($this->currentAdmin()->id ?? 0);
        $vacancyId = trim($vacancyId);

        if ($adminId <= 0 || $vacancyId === '' || !Schema::hasTable('admin_vacancy_accesses')) {
            return;
        }

        try {
            AdminVacancyAccess::query()->firstOrCreate([
                'admin_id' => $adminId,
                'vacancy_id' => $vacancyId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Unable to auto-grant HR Division vacancy access.', [
                'admin_id' => $adminId,
                'vacancy_id' => $vacancyId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function applyHrDivisionManagedVacancyScope($query, ?int $adminId = null): void
    {
        $adminId = $adminId ?? (int) ($this->currentAdmin()->id ?? 0);
        $grantedVacancyIds = $this->hrDivisionGrantedVacancyIds($adminId);
        $supportsCreatorColumn = $this->supportsVacancyCreatorColumn();

        $query->whereRaw('UPPER(vacancy_type) = ?', ['COS'])
            ->where(function ($subQuery) use ($adminId, $grantedVacancyIds, $supportsCreatorColumn) {
                $hasScope = false;

                if ($supportsCreatorColumn && $adminId > 0) {
                    $subQuery->where('created_by_admin_id', $adminId);
                    $hasScope = true;
                }

                if (!empty($grantedVacancyIds)) {
                    if ($hasScope) {
                        $subQuery->orWhereIn('vacancy_id', $grantedVacancyIds);
                    } else {
                        $subQuery->whereIn('vacancy_id', $grantedVacancyIds);
                    }
                    $hasScope = true;
                }

                if (!$hasScope) {
                    $subQuery->whereRaw('1 = 0');
                }
            });
    }

    private function hrDivisionCanManageVacancy(JobVacancy $vacancy): bool
    {
        if (!$this->isHrDivisionAdmin()) {
            return true;
        }

        if (strcasecmp((string) ($vacancy->vacancy_type ?? ''), 'COS') !== 0) {
            return false;
        }

        $adminId = (int) ($this->currentAdmin()->id ?? 0);
        if ($this->supportsVacancyCreatorColumn() && $adminId > 0 && (int) ($vacancy->created_by_admin_id ?? 0) === $adminId) {
            return true;
        }

        if (!Schema::hasTable('admin_vacancy_accesses') || $adminId <= 0) {
            return false;
        }

        return AdminVacancyAccess::query()
            ->where('admin_id', $adminId)
            ->where('vacancy_id', (string) $vacancy->vacancy_id)
            ->exists();
    }

    private function denyHrDivisionVacancyAccess(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        return redirect()->route('vacancies_management')->with('error', $message);
    }

    private function syncVacancyTitleCompensationFromVacancyData(array $vacancyData): void
    {
        if (!Schema::hasTable('vacancy_titles')) {
            return;
        }

        $positionTitle = trim((string) ($vacancyData['position_title'] ?? ''));
        if ($positionTitle === '') {
            return;
        }

        $salaryGrade = $this->normalizeSalaryGrade((string) ($vacancyData['salary_grade'] ?? ''));
        $monthlySalary = (float) ($vacancyData['monthly_salary'] ?? 0);

        $updates = [
            'salary_grade' => $salaryGrade !== '' ? $salaryGrade : null,
            'monthly_salary' => $monthlySalary,
        ];

        $optionalColumns = [
            'vacancy_type',
            'pcn_no',
            'plantilla_item_no',
            'closing_date',
            'place_of_assignment',
            'qualification_education',
            'education_rule_compiled',
            'education_rule_parser_version',
            'education_rule_compiled_at',
            'qualification_training',
            'qualification_experience',
            'qualification_eligibility',
            'competencies',
            'expected_output',
            'scope_of_work',
            'duration_of_work',
            'to_person',
            'to_position',
            'to_office',
            'to_office_address',
            'csc_form_path',
        ];

        foreach ($optionalColumns as $column) {
            if (array_key_exists($column, $vacancyData) && Schema::hasColumn('vacancy_titles', $column)) {
                $updates[$column] = $vacancyData[$column];
            }
        }

        VacancyTitle::query()->updateOrCreate(['position_title' => $positionTitle], $updates);
    }

    private function normalizeSalaryGrade(string $value): string
    {
        $raw = strtoupper(trim($value));
        if (preg_match('/^(?:SG-)?(\d{1,2})$/', $raw, $matches) !== 1) {
            return $raw;
        }

        return 'SG-' . str_pad((string) ((int) $matches[1]), 2, '0', STR_PAD_LEFT);
    }

    private function strictEducationRequirementValidationRules(): array
    {
        return [
            'required',
            'string',
            'max:1000',
            function ($attribute, $value, $fail): void {
                $error = $this->educationRequirementValidationError($value);
                if ($error !== null) {
                    $fail($error);
                }
            },
        ];
    }

    private function educationRequirementValidationError($value): ?string
    {
        $requirement = $this->normalizeQualificationRequirement((string) $value);
        if ($requirement === null) {
            return 'Education requirement is required.';
        }

        $rule = $this->buildCompiledEducationRule($requirement);
        if (!is_array($rule)) {
            return 'Education requirement could not be parsed. Please use a clear template.';
        }

        $ruleCode = strtolower(trim((string) ($rule['rule_code'] ?? 'unknown_text')));
        $confidence = strtolower(trim((string) ($rule['confidence'] ?? 'low')));
        if ($ruleCode !== '' && $ruleCode !== 'unknown_text' && $confidence === 'high') {
            return null;
        }

        return 'Education requirement text is ambiguous. Use a clear format like: '
            . '"Bachelor\'s Degree (any field)", '
            . '"Bachelor\'s Degree in Statistics or related field", '
            . '"Completion of 2 years of studies in college", '
            . '"72 units in college", '
            . '"Masteral Degree", or '
            . '"Bachelor of Laws".';
    }

    private function normalizeEducationRequirementFieldList($value): array
    {
        $items = is_array($value) ? $value : preg_split('/[\r\n,;\/]+/', (string) $value);
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(function ($item) {
            $text = trim((string) $item);
            if ($text === '') {
                return null;
            }

            $text = preg_replace('/\s+/', ' ', $text) ?: $text;
            return strtolower($text);
        }, $items))));
    }

    private function extractEducationFieldHints(string $requirement): array
    {
        $requirement = trim($requirement);
        if ($requirement === '') {
            return [];
        }

        $candidate = '';
        if (preg_match('/\bin\s+(.+?)(?:\.\s*|,\s*required|\s+required|\s+is required|\s+or related|\s+related field|\s+preferably|$)/i', $requirement, $matches) === 1) {
            $candidate = trim((string) ($matches[1] ?? ''));
        }

        if ($candidate === '') {
            return [];
        }

        $candidate = preg_replace('/\b(degree|bachelor(?:\'s)?|master(?:\'s)?|course|field|fields)\b/i', '', $candidate) ?: $candidate;
        $candidate = preg_replace('/\s+/', ' ', $candidate) ?: $candidate;
        return $this->normalizeEducationRequirementFieldList($candidate);
    }

    private function buildCompiledEducationRule(?string $rawRequirement): ?array
    {
        $requirement = $this->normalizeQualificationRequirement($rawRequirement);
        if ($requirement === null) {
            return null;
        }

        $normalizedText = strtolower(trim((string) preg_replace('/\s+/', ' ', $requirement)));
        $rule = [
            'parser_version' => self::EDUCATION_RULE_PARSER_VERSION,
            'source_text' => $requirement,
            'normalized_text' => $normalizedText,
            'rule_code' => 'unknown_text',
            'required' => true,
            'advisory_only' => false,
            'min_college_years' => null,
            'min_college_units' => null,
            'required_fields' => [],
            'strict_fields' => false,
            'accept_higher_degree' => true,
            'confidence' => 'low',
        ];

        if (
            $this->textContainsAny($normalizedText, ['no education required', 'no education requirement', 'any educational background']) ||
            ($this->textContainsAny($normalizedText, ['none']) && str_contains($normalizedText, 'education'))
        ) {
            $rule['rule_code'] = 'none';
            $rule['required'] = false;
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['bachelor of laws', 'llb', 'juris doctor', 'attorney'])) {
            $rule['rule_code'] = 'law_degree';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['doctoral', 'doctorate', 'doctor of philosophy', 'phd', 'ph.d'])) {
            $rule['rule_code'] = 'doctorate';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['master', 'masteral', "master's"])) {
            $rule['rule_code'] = 'masters';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['graduate studies', 'post graduate', 'postgraduate'])) {
            $rule['rule_code'] = 'graduate_studies';
            $rule['confidence'] = 'high';
            return $rule;
        }

        $yearsMatches = [];
        $mentionsCollege = str_contains($normalizedText, 'college');
        $hasYearPattern = preg_match('/\b(\d{1,2})\s*(?:years?|yrs?)\b/i', $normalizedText, $yearsMatches) === 1
            || preg_match('/\bat least\s+(\d{1,2})\s*(?:years?|yrs?)\b/i', $normalizedText, $yearsMatches) === 1;
        if ($mentionsCollege && $hasYearPattern) {
            $rule['rule_code'] = 'college_years';
            $rule['min_college_years'] = max(1, (int) ($yearsMatches[1] ?? 0));
            $rule['confidence'] = 'high';
            return $rule;
        }

        if (preg_match('/\b(\d{1,3})\s*(?:units?|unit)\s*(?:in\s*)?college\b/i', $normalizedText, $unitMatches) === 1) {
            $rule['rule_code'] = 'college_years';
            $rule['min_college_units'] = max(1, (int) ($unitMatches[1] ?? 0));
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['college graduate', 'college degree'])) {
            $rule['rule_code'] = 'college_degree';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['bachelor', "bachelor's", 'baccalaureate'])) {
            $rule['rule_code'] = 'bachelor_any';
            $fields = $this->extractEducationFieldHints($requirement);
            $isRelevantWording = $this->textContainsAny($normalizedText, ['related field', 'relevant']);

            if ($isRelevantWording) {
                // "Relevant to the job" still requires a bachelor's degree; relevance is verified by admin.
                $rule['rule_code'] = 'bachelor_relevant_admin_review';
                $rule['required_fields'] = $fields;
                $rule['required'] = true;
                $rule['advisory_only'] = false;
                $rule['confidence'] = 'high';
            } elseif (!empty($fields)) {
                $rule['rule_code'] = 'bachelor_specific';
                $rule['required_fields'] = $fields;
                $rule['strict_fields'] = $this->textContainsAny($normalizedText, ['strict', 'exact']);
                $rule['confidence'] = 'high';
            } else {
                $rule['confidence'] = 'high';
            }
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['senior high', 'grade 12', 'shs'])) {
            $rule['rule_code'] = 'senior_high';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['high school'])) {
            $rule['rule_code'] = 'high_school';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['elementary'])) {
            $rule['rule_code'] = 'elementary';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['college', 'education'])) {
            $rule['rule_code'] = 'any_education';
            $rule['confidence'] = 'medium';
            return $rule;
        }

        return $rule;
    }

    private function buildCompiledEducationStoragePayload(?string $rawRequirement): array
    {
        $rule = $this->buildCompiledEducationRule($rawRequirement);
        if ($rule === null) {
            return [
                'education_rule_compiled' => null,
                'education_rule_parser_version' => null,
                'education_rule_compiled_at' => null,
            ];
        }

        $encoded = json_encode($rule, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if (!is_string($encoded) || $encoded === '') {
            $encoded = null;
        }

        return [
            'education_rule_compiled' => $encoded,
            'education_rule_parser_version' => (int) ($rule['parser_version'] ?? self::EDUCATION_RULE_PARSER_VERSION),
            'education_rule_compiled_at' => now(),
        ];
    }

    private function decodeCompiledEducationRule($raw): ?array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function resolveCompiledEducationRuleForVacancy(JobVacancy $vacancy, ?string $normalizedRequirement = null): ?array
    {
        $normalizedRequirement = $normalizedRequirement ?? $this->normalizeQualificationRequirement($vacancy->qualification_education ?? null);
        if ($normalizedRequirement === null) {
            return null;
        }

        $storedRule = $this->decodeCompiledEducationRule($vacancy->education_rule_compiled ?? null);
        if (is_array($storedRule)) {
            $sourceText = $this->normalizeQualificationRequirement((string) ($storedRule['source_text'] ?? ''));
            $parserVersion = (int) ($storedRule['parser_version'] ?? 0);
            if (
                $sourceText !== null
                && $sourceText === $normalizedRequirement
                && $parserVersion === self::EDUCATION_RULE_PARSER_VERSION
            ) {
                return $storedRule;
            }
        }

        return $this->buildCompiledEducationRule($normalizedRequirement);
    }

    private function educationProfileMatchesFieldHints(array $profile, array $requiredFields, bool $strict = false): bool
    {
        if (empty($requiredFields)) {
            return true;
        }

        $haystack = strtolower(trim((string) ($profile['educationKeywordHaystack'] ?? '')));
        if ($haystack === '') {
            return false;
        }

        foreach ($requiredFields as $field) {
            $needle = strtolower(trim((string) $field));
            if ($needle === '') {
                continue;
            }

            if (str_contains($haystack, $needle)) {
                return true;
            }

            if ($strict) {
                continue;
            }

            $tokens = preg_split('/\s+/', preg_replace('/[^a-z0-9\s]+/i', ' ', $needle) ?? '') ?: [];
            $tokens = array_values(array_filter($tokens, fn($token) => strlen((string) $token) >= 4));
            if (empty($tokens)) {
                continue;
            }

            $matches = 0;
            foreach ($tokens as $token) {
                if (str_contains($haystack, $token)) {
                    $matches++;
                }
            }

            if ($matches >= max(1, min(2, count($tokens)))) {
                return true;
            }
        }

        return false;
    }

    private function evaluateCompiledEducationRule(array $profile, array $rule): ?bool
    {
        $ruleCode = strtolower(trim((string) ($rule['rule_code'] ?? '')));
        if ($ruleCode === '' || $ruleCode === 'unknown_text') {
            return null;
        }

        return match ($ruleCode) {
            'none' => true,
            'any_education' => (bool) ($profile['hasAnyEducation'] ?? false),
            'elementary' => (bool) ($profile['hasElementaryOrHigher'] ?? false),
            'high_school' => (bool) ($profile['hasHighSchoolOrHigher'] ?? false),
            'senior_high' => (bool) ($profile['hasSeniorHighOrHigher'] ?? false),
            'bachelor_relevant_admin_review' => (bool) ($profile['hasBachelorOrHigher'] ?? false),
            'college_years' => (($rule['min_college_units'] ?? null) !== null)
                ? (int) ($profile['estimatedCollegeUnits'] ?? 0) >= (int) ($rule['min_college_units'] ?? 0)
                : (int) ($profile['collegeYearsCompleted'] ?? 0) >= max(1, (int) ($rule['min_college_years'] ?? 2)),
            'college_degree' => (bool) ($profile['hasCollegeDegreeOrHigher'] ?? false),
            'bachelor_any' => (bool) ($profile['hasBachelorOrHigher'] ?? false),
            'bachelor_specific' => (bool) ($profile['hasBachelorOrHigher'] ?? false)
                && $this->educationProfileMatchesFieldHints(
                    $profile,
                    (array) ($rule['required_fields'] ?? []),
                    (bool) ($rule['strict_fields'] ?? false)
                ),
            'law_degree' => (bool) ($profile['hasLawDegree'] ?? false),
            'graduate_studies' => (bool) ($profile['hasGrad'] ?? false),
            'masters' => (bool) ($profile['hasMasters'] ?? false)
                || ((bool) ($rule['accept_higher_degree'] ?? true) && (bool) ($profile['hasDoctorate'] ?? false)),
            'doctorate' => (bool) ($profile['hasDoctorate'] ?? false),
            default => null,
        };
    }

    private function evaluateLegacyEducationRequirementByText(array $profile, string $requirement): bool
    {
        $requirementLower = strtolower($requirement);
        $mentionsSeniorHighAlternative = $this->textContainsAny($requirementLower, ['senior high', 'grade 12']);
        $mentionsHighSchoolAlternative = str_contains($requirementLower, 'high school');

        if ($this->textContainsAny($requirementLower, [
            'master',
            'masteral',
            'graduate studies',
            'post graduate',
            'postgraduate',
            'doctoral',
            'doctor',
            'phd',
        ])) {
            return (bool) ($profile['hasGrad'] ?? false);
        }
        if ($this->textContainsAny($requirementLower, ['bachelor of laws', 'llb', 'juris doctor', 'attorney'])) {
            return (bool) ($profile['hasLawDegree'] ?? false);
        }
        if ($this->textContainsAny($requirementLower, ['bachelor', "bachelor's", 'baccalaureate'])) {
            return (bool) ($profile['hasBachelorOrHigher'] ?? false);
        }
        if (str_contains($requirementLower, '2 years') && str_contains($requirementLower, 'college')) {
            return (bool) ($profile['hasAtLeastTwoYearsCollege'] ?? false)
                || ($mentionsSeniorHighAlternative && (bool) ($profile['hasSeniorHighOrHigher'] ?? false))
                || (!$mentionsSeniorHighAlternative && $mentionsHighSchoolAlternative && (bool) ($profile['hasHighSchoolOrHigher'] ?? false));
        }
        if ($this->textContainsAny($requirementLower, ['college graduate', 'college degree'])) {
            return (bool) ($profile['hasCollegeDegreeOrHigher'] ?? false);
        }
        if ($this->textContainsAny($requirementLower, ['bachelor', 'college'])) {
            return (bool) ($profile['hasCollegeEntryOrHigher'] ?? false);
        }
        if ($mentionsSeniorHighAlternative) {
            return (bool) ($profile['hasSeniorHighOrHigher'] ?? false);
        }
        if ($mentionsHighSchoolAlternative) {
            return (bool) ($profile['hasHighSchoolOrHigher'] ?? false);
        }
        if (str_contains($requirementLower, 'elementary')) {
            return (bool) ($profile['hasElementaryOrHigher'] ?? false);
        }

        return (bool) ($profile['hasAnyEducation'] ?? false);
    }

    private function upsertPositionTemplate(array $validated, ?string $cscFormPath = null, ?int $positionTitleId = null): void
    {
        if (!Schema::hasTable('vacancy_titles')) {
            return;
        }

        $data = [
            'position_title' => trim((string) ($validated['position_title'] ?? '')),
            'salary_grade' => $this->normalizeSalaryGrade((string) ($validated['salary_grade'] ?? '')),
            'monthly_salary' => (float) ($validated['monthly_salary'] ?? 0),
        ];

        $optionalPayload = [
            'vacancy_type',
            'pcn_no',
            'plantilla_item_no',
            'closing_date',
            'place_of_assignment',
            'qualification_education',
            'education_rule_compiled',
            'education_rule_parser_version',
            'education_rule_compiled_at',
            'qualification_training',
            'qualification_experience',
            'qualification_eligibility',
            'competencies',
            'expected_output',
            'scope_of_work',
            'duration_of_work',
            'to_person',
            'to_position',
            'to_office',
            'to_office_address',
        ];

        foreach ($optionalPayload as $column) {
            if (Schema::hasColumn('vacancy_titles', $column)) {
                $data[$column] = $validated[$column] ?? null;
            }
        }

        if ($cscFormPath !== null && Schema::hasColumn('vacancy_titles', 'csc_form_path')) {
            $data['csc_form_path'] = $cscFormPath;
        }

        if ($positionTitleId && $positionTitleId > 0) {
            $existing = VacancyTitle::query()->find($positionTitleId);
            if ($existing) {
                $existing->update($data);
                return;
            }
        }

        VacancyTitle::query()->updateOrCreate(
            ['position_title' => $data['position_title']],
            $data
        );
    }

    public function jobVacancy()
    {
        if (Auth::check() && ApplicantOnboarding::shouldRequire(Auth::user())) {
            return redirect()
                ->route('dashboard_user')
                ->with('open_onboarding_modal', true)
                ->with('status', 'Please complete onboarding before accessing job vacancies.');
        }

        $jobVacancies = JobVacancy::select('job_vacancies.*')
            ->leftJoin('exam_details', function ($join) {
                $join->whereRaw('job_vacancies.vacancy_id COLLATE utf8mb4_unicode_ci = exam_details.vacancy_id COLLATE utf8mb4_unicode_ci');
            })
            ->with('examDetail')
            ->orderByRaw("CASE 
                WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NOT NULL AND exam_details.date >= CURDATE() THEN 1 
                WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NULL THEN 2 
                WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NOT NULL AND exam_details.date < CURDATE() THEN 3 
                ELSE 4 
            END")
            ->orderBy('job_vacancies.closing_date', 'asc')
            ->get();

        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Viewed job vacancy list.');
        */

        return view('dashboard_user.job_vacancy', ['vacancies' => $jobVacancies]);
    }

    public function jobVacancyManagement()
    {
        $jobVacanciesQuery = JobVacancy::query();

        if ($this->isHrDivisionAdmin()) {
            $this->applyHrDivisionManagedVacancyScope($jobVacanciesQuery);
        }

        $jobVacancies = $jobVacanciesQuery
            ->orderByRaw("CASE WHEN status = 'OPEN' THEN 1 ELSE 2 END")
            ->orderBy('closing_date', 'asc')
            ->get();

        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Accessed job vacancy management page.');
        */

        return view('admin.vacancies_management', [
            'vacancies' => $jobVacancies,
            'isHrDivisionUser' => $this->isHrDivisionAdmin(),
        ]);
    }

    public function edit(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();
        if (!$this->hrDivisionCanManageVacancy($vacancy)) {
            return $this->denyHrDivisionVacancyAccess(
                $request,
                'Access denied. You can only manage your own or assigned COS vacancies.'
            );
        }

        $signatories = \App\Models\Signatory::query()->orderBy('id')->get();
        $vacancyType = (string) ($vacancy->vacancy_type ?? '');
        $view = strcasecmp(trim($vacancyType), 'Plantilla') === 0
            ? 'admin.vacancy_add_plantilla'
            : 'admin.vacancy_add_cos';

        activity()
            ->event('view')
            ->causedBy(auth('admin')->user())
            ->performedOn($vacancy)
            ->withProperties(['vacancy_id' => $vacancy->vacancy_id, 'section' => 'Job Vacancy'])
            ->log('Editing job vacancy.');

        return view($view, ['vacancy' => $vacancy, 'signatories' => $signatories]);
    }

    public function update(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();
        $requiresCscFormUpload = strtoupper((string) $request->input('vacancy_type')) === 'PLANTILLA'
            && (!$this->hasJobVacancyCscFormPathColumn() || empty($vacancy->csc_form_path));

        $validated = $request->validate([
            'vacancy_type' => 'required|in:Plantilla,COS',
            'position_title' => 'required|string|max:255',
            'monthly_salary' => 'required|numeric',
            'place_of_assignment' => 'required|string',
            //'vacancies' => 'required|integer|min:1',
            'closing_date' => 'required|date',
            'qualification_education' => $this->strictEducationRequirementValidationRules(),
            'qualification_experience' => 'required|string',
            'qualification_training' => 'required|string',
            'qualification_eligibility' => 'nullable|string|required_if:vacancy_type,Plantilla',

            // Plantilla-only
            'competencies' => 'nullable|string',

            // COS only
            'scope_of_work' => 'nullable|string|required_if:vacancy_type,COS',
            'expected_output' => 'nullable|string|required_if:vacancy_type,COS',
            'duration_of_work' => 'nullable|string|required_if:vacancy_type,COS',

            'to_person' => 'required|string',
            'to_position' => 'required|string',
            'to_office' => 'required|string',
            'to_office_address' => 'required|string',

            'salary_grade' => ['required', 'regex:/^SG-\\d{2}$/'],
            'pcn_no' => 'nullable|string',
            'plantilla_item_no' => 'nullable|string',
            'csc_form' => [Rule::requiredIf($requiresCscFormUpload), 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        if (!$this->hrDivisionCanManageVacancy($vacancy)) {
            return $this->denyHrDivisionVacancyAccess(
                $request,
                'Access denied. You can only manage your own or assigned COS vacancies.'
            );
        }

        if ($this->isHrDivisionAdmin() && strcasecmp((string) ($validated['vacancy_type'] ?? ''), 'COS') !== 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'HR Division can only update COS vacancies.');
        }

        $compiledEducationStorage = $this->buildCompiledEducationStoragePayload($validated['qualification_education'] ?? null);

        $changes = [];
        foreach ($validated as $key => $value) {
            if ($vacancy->$key != $value) {
                $changes[$key] = [
                    'old' => $vacancy->$key,
                    'new' => $value
                ];
            }
        }


        $closingDate = Carbon::parse($validated['closing_date']);
        $today = Carbon::today();

        $status = 'OPEN';

        $vacancyUpdateData = [
            'vacancy_type' => $validated['vacancy_type'],
            'position_title' => $validated['position_title'],
            'monthly_salary' => $validated['monthly_salary'],
            'place_of_assignment' => $validated['place_of_assignment'],
            //'vacancies' => $validated['vacancies'],
            'closing_date' => $validated['closing_date'],
            'status' => $status,

            'qualification_education' => $validated['qualification_education'],
            'qualification_experience' => $validated['qualification_experience'],
            'qualification_training' => $validated['qualification_training'],
            'qualification_eligibility' => $validated['qualification_eligibility'] ?? '',

            // Plantilla only
            'competencies' => $validated['competencies'] ?? null,

            // COS-only
            'expected_output' => $validated['expected_output'] ?? null,
            'scope_of_work' => $validated['scope_of_work'] ?? null,
            'duration_of_work' => $validated['duration_of_work'] ?? null,

            'to_person' => $validated['to_person'],
            'to_position' => $validated['to_position'],
            'to_office' => $validated['to_office'],
            'to_office_address' => $validated['to_office_address'],

            'salary_grade' => $validated['salary_grade'] ?? null,
            'pcn_no' => $validated['pcn_no'] ?? null,
            'plantilla_item_no' => $validated['plantilla_item_no'] ?? null,

            'last_modified_by' => Auth::guard('admin')->user()?->name ?? 'System',
        ];

        if (Schema::hasColumn('job_vacancies', 'education_rule_compiled')) {
            $vacancyUpdateData['education_rule_compiled'] = $compiledEducationStorage['education_rule_compiled'];
        }
        if (Schema::hasColumn('job_vacancies', 'education_rule_parser_version')) {
            $vacancyUpdateData['education_rule_parser_version'] = $compiledEducationStorage['education_rule_parser_version'];
        }
        if (Schema::hasColumn('job_vacancies', 'education_rule_compiled_at')) {
            $vacancyUpdateData['education_rule_compiled_at'] = $compiledEducationStorage['education_rule_compiled_at'];
        }

        if ($this->hasJobVacancyLastModifiedAtColumn()) {
            $vacancyUpdateData['last_modified_at'] = now();
        }

        $vacancy->update($vacancyUpdateData);
        $this->syncVacancyTitleCompensationFromVacancyData($vacancyUpdateData);

        // Handle CSC Form file upload only when the column exists in this database.
        if ($this->hasJobVacancyCscFormPathColumn() && request()->hasFile('csc_form')) {
            if ($vacancy->csc_form_path) {
                Storage::disk('public')->delete($vacancy->csc_form_path);
            }
            $vacancy->update([
                'csc_form_path' => request()->file('csc_form')->store('csc_forms', 'public'),
            ]);
        }

        if (!empty($changes)) {
            activity()
                ->event('edit')
                ->causedBy(auth('admin')->user())
                ->performedOn($vacancy)
                ->withProperties(['changes' => $changes, 'section' => 'Job Vacancy'])
                ->log('Updated job vacancy fields.');
        }


        return redirect()->route('vacancies_management')->with('success', 'Job vacancy updated successfully.');
    }

    public function storeVacancy(Request $request)
    {
        //try {
        $positionMode = $request->boolean('position_mode');
        if (!$positionMode) {
            $referer = (string) $request->headers->get('referer', '');
            if (
                $referer !== ''
                && (
                    str_contains($referer, '/admin/vacancies_management/add/cos')
                    || str_contains($referer, '/admin/vacancies_management/add/plantilla')
                )
            ) {
                $positionMode = true;
            }
        }
        $positionTitleId = (int) $request->input('position_title_id', 0);
        $templateCscFormPath = '';
        if (
            !$positionMode
            && strtoupper((string) $request->input('vacancy_type')) === 'PLANTILLA'
            && Schema::hasTable('vacancy_titles')
            && Schema::hasColumn('vacancy_titles', 'csc_form_path')
        ) {
            $positionTitle = trim((string) $request->input('position_title', ''));
            if ($positionTitle !== '') {
                $templateQuery = VacancyTitle::query()
                    ->where('position_title', $positionTitle)
                    ->whereNotNull('csc_form_path')
                    ->whereRaw("TRIM(COALESCE(csc_form_path, '')) != ''");

                if (Schema::hasColumn('vacancy_titles', 'vacancy_type')) {
                    $templateQuery->where(function ($q) {
                        $q->whereRaw("UPPER(TRIM(COALESCE(vacancy_type, ''))) = 'PLANTILLA'")
                            ->orWhereNull('vacancy_type')
                            ->orWhereRaw("TRIM(COALESCE(vacancy_type, '')) = ''");
                    });
                }

                $templateTitle = $templateQuery
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->first();

                $templateCscFormPath = trim((string) ($templateTitle?->csc_form_path ?? ''));
            }
        }

        if ($templateCscFormPath === '' && !$positionMode && strtoupper((string) $request->input('vacancy_type')) === 'PLANTILLA' && $this->hasJobVacancyCscFormPathColumn()) {
            $positionTitle = trim((string) $request->input('position_title', ''));
            if ($positionTitle !== '') {
                $templateVacancy = JobVacancy::query()
                    ->where('position_title', $positionTitle)
                    ->whereRaw("UPPER(TRIM(COALESCE(vacancy_type, ''))) = 'PLANTILLA'")
                    ->whereNotNull('csc_form_path')
                    ->whereRaw("TRIM(COALESCE(csc_form_path, '')) != ''")
                    ->orderByDesc('updated_at')
                    ->first();
                $templateCscFormPath = trim((string) ($templateVacancy?->csc_form_path ?? ''));
            }
        }

        if ($templateCscFormPath !== '' && !Storage::disk('public')->exists((string) $templateCscFormPath)) {
            $templateCscFormPath = '';
        }

        $requiresCscFormUpload = !$positionMode
            && strtoupper((string) $request->input('vacancy_type')) === 'PLANTILLA'
            && $templateCscFormPath === '';
        $existingPositionTemplate = null;
        if ($positionMode && $positionTitleId > 0 && Schema::hasTable('vacancy_titles')) {
            $existingPositionTemplate = VacancyTitle::query()->find($positionTitleId);
        }
        $requiresPositionModeCscFormUpload = $positionMode
            && strtoupper((string) $request->input('vacancy_type')) === 'PLANTILLA'
            && (
                !Schema::hasColumn('vacancy_titles', 'csc_form_path')
                || !$existingPositionTemplate
                || empty($existingPositionTemplate->csc_form_path)
            );

        if ($positionMode) {
            if (!Schema::hasTable('vacancy_titles')) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Positions table is missing. Run migrations first.');
            }

            $validated = $request->validate([
                'position_title' => 'required|string|max:255',
                'vacancy_type' => 'required|in:COS,Plantilla',
                'pcn_no' => 'nullable|string',
                'plantilla_item_no' => 'nullable|string',
                'closing_date' => 'required|date',
                'monthly_salary' => 'required|numeric|min:0',
                'salary_grade' => ['required', 'regex:/^SG-\d{2}$/'],
                'place_of_assignment' => 'required|string',
                'qualification_education' => $this->strictEducationRequirementValidationRules(),
                'qualification_training' => 'required|string',
                'qualification_experience' => 'required|string',
                'qualification_eligibility' => 'nullable|string|required_if:vacancy_type,Plantilla',
                'competencies' => 'nullable|string',
                'expected_output' => 'nullable|string|required_if:vacancy_type,COS',
                'scope_of_work' => 'nullable|string|required_if:vacancy_type,COS',
                'duration_of_work' => 'nullable|string|required_if:vacancy_type,COS',
                'to_person' => 'nullable|string',
                'to_position' => 'nullable|string',
                'to_office' => 'nullable|string',
                'to_office_address' => 'nullable|string',
                'csc_form' => [Rule::requiredIf($requiresPositionModeCscFormUpload), 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            ]);

            if ($this->isHrDivisionAdmin() && strcasecmp((string) ($validated['vacancy_type'] ?? ''), 'COS') !== 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'HR Division can only create COS positions.');
            }

            $compiledEducationStorage = $this->buildCompiledEducationStoragePayload($validated['qualification_education'] ?? null);
            $validated = array_merge($validated, $compiledEducationStorage);

            $cscFormPath = null;
            if ($request->hasFile('csc_form') && Schema::hasColumn('vacancy_titles', 'csc_form_path')) {
                $cscFormPath = $request->file('csc_form')->store('csc_forms', 'public');
            }

            $this->upsertPositionTemplate($validated, $cscFormPath, $positionTitleId > 0 ? $positionTitleId : null);

            activity()
                ->event('create')
                ->causedBy(auth('admin')->user())
                ->withProperties([
                    'position_title' => $validated['position_title'],
                    'vacancy_type' => $validated['vacancy_type'],
                    'section' => 'Positions',
                ])
                ->log('Created or updated position template.');

            return redirect()->route('admin.positions.index')->with('success', 'Position saved successfully.');
        }

        $validated = $request->validate([
            'position_title' => 'required|string|max:255',
            'vacancy_type' => 'required|in:COS,Plantilla',
            'pcn_no' => 'nullable|string',
            'plantilla_item_no' => 'nullable|string',
            'closing_date' => 'required|date|after_or_equal:today',
            // 'status' => 'nullable|in:OPEN,CLOSED', // Status is auto-set to OPEN
            'monthly_salary' => 'required|numeric',
            'salary_grade' => ['required', 'regex:/^SG-\\d{2}$/'],
            'place_of_assignment' => 'required|string',

            // Qualification standards
            'qualification_education' => $this->strictEducationRequirementValidationRules(),
            'qualification_training' => 'required|string',
            'qualification_experience' => 'required|string',
            'qualification_eligibility' => 'nullable|string|required_if:vacancy_type,Plantilla',

            // Plantilla-only
            'competencies' => 'nullable|string',

            // COS-only
            'expected_output' => 'nullable|string|required_if:vacancy_type,COS',
            'scope_of_work' => 'nullable|string|required_if:vacancy_type,COS',
            'duration_of_work' => 'nullable|string|required_if:vacancy_type,COS',

            // Application submission
            'to_person' => 'required|string',
            'to_position' => 'required|string',
            'to_office' => 'required|string',
            'to_office_address' => 'required|string',

            // CSC Form
            'csc_form' => [Rule::requiredIf($requiresCscFormUpload), 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        if ($this->isHrDivisionAdmin() && strcasecmp((string) ($validated['vacancy_type'] ?? ''), 'COS') !== 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'HR Division can only create COS vacancies.');
        }

        $compiledEducationStorage = $this->buildCompiledEducationStoragePayload($validated['qualification_education'] ?? null);

        // 🔷 Generate vacancy_id
        /*
        $positionTitle = $validated['position_title'];
        $words = preg_split('/\s+/', $positionTitle);
        $ranks = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII'];
        $filteredWords = array_values(array_filter($words, fn($word) => !in_array(strtoupper($word), $ranks)));

        if(count($filteredWords) == 1){
            $letters = strtoupper(substr($filteredWords[0], 0, 3));
        } else {
            $letters = '';
            for($i = 0; $i < min(3, count($filteredWords)); $i++){
                $letters .= strtoupper(substr($filteredWords[$i], 0, 1));
            }
        }

        $latestVacancy = JobVacancy::where('vacancy_id', 'like', $letters . '-%')->latest('vacancy_id')->first();
        $num = $latestVacancy ? intval(substr($latestVacancy->vacancy_id, strpos($latestVacancy->vacancy_id, '-') + 1)) + 1 : 1;
        $vacancy_id = $letters . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        */

        $closingDate = Carbon::parse($validated['closing_date']);
        $today = Carbon::today();

        $status = 'OPEN'; // Default status for new vacancies

        $hasCscFormPathColumn = $this->hasJobVacancyCscFormPathColumn();

        // 🔷 Create vacancy
        $vacancyData = [
            //'vacancy_id' => $vacancy_id,
            'position_title' => $validated['position_title'],
            'vacancy_type' => $validated['vacancy_type'],
            'pcn_no' => $validated['pcn_no'] ?? null,
            'plantilla_item_no' => $validated['plantilla_item_no'] ?? null,
            'closing_date' => $validated['closing_date'],

            'status' => $status,
            'monthly_salary' => $validated['monthly_salary'],
            'salary_grade' => $validated['salary_grade'] ?? null,
            'place_of_assignment' => $validated['place_of_assignment'],

            // Qualification standards
            'qualification_education' => $validated['qualification_education'],
            'qualification_training' => $validated['qualification_training'],
            'qualification_experience' => $validated['qualification_experience'],
            'qualification_eligibility' => $validated['qualification_eligibility'] ?? '',

            // Plantilla only
            'competencies' => $validated['competencies'] ?? null,

            // COS only
            'expected_output' => $validated['expected_output'] ?? null,
            'scope_of_work' => $validated['scope_of_work'] ?? null,
            'duration_of_work' => $validated['duration_of_work'] ?? null,


            // Application submission
            'to_person' => $validated['to_person'],
            'to_position' => $validated['to_position'],
            'to_office' => $validated['to_office'],
            'to_office_address' => $validated['to_office_address'],
        ];

        if (Schema::hasColumn('job_vacancies', 'education_rule_compiled')) {
            $vacancyData['education_rule_compiled'] = $compiledEducationStorage['education_rule_compiled'];
        }
        if (Schema::hasColumn('job_vacancies', 'education_rule_parser_version')) {
            $vacancyData['education_rule_parser_version'] = $compiledEducationStorage['education_rule_parser_version'];
        }
        if (Schema::hasColumn('job_vacancies', 'education_rule_compiled_at')) {
            $vacancyData['education_rule_compiled_at'] = $compiledEducationStorage['education_rule_compiled_at'];
        }

        if ($this->supportsVacancyCreatorColumn()) {
            $vacancyData['created_by_admin_id'] = Auth::guard('admin')->id();
        }

        // Some environments may not yet have the csc_form_path column.
        if ($hasCscFormPathColumn) {
            $vacancyData['csc_form_path'] = $request->hasFile('csc_form')
                ? $request->file('csc_form')->store('csc_forms', 'public')
                : ($templateCscFormPath !== '' ? $templateCscFormPath : null);
        }

        $vacancy = JobVacancy::create($vacancyData);
        $vacancy->refresh();
        $this->grantHrDivisionAccessToVacancy((string) ($vacancy->vacancy_id ?? ''));
        $this->syncVacancyTitleCompensationFromVacancyData($vacancyData);


        ExamDetail::create(['vacancy_id' => $vacancy->vacancy_id]);
        Log::info('Competencies field debug:', ['competencies' => $validated['competencies'] ?? 'NOT SET']);

        activity()
            ->event('create')
            ->causedBy(auth('admin')->user())
            ->performedOn($vacancy)
            ->withProperties(['vacancy_id' => $vacancy->vacancy_id, 'section' => 'Job Vacancy'])
            ->log('Created new job vacancy.');


        return redirect()->route('vacancies_management')->with('success', 'Vacancy created successfully.');
        /*} catch (\Exception $e) {
            Log::error('Vacancy Store Error: '.$e->getMessage());
            Log::error('Request Data: ' . json_encode($request->all()));
            return back()->with('error', 'Error: '.$e->getMessage());
        }*/

    }


    public function delete(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();
        if (!$this->hrDivisionCanManageVacancy($vacancy)) {
            return $this->denyHrDivisionVacancyAccess(
                $request,
                'Access denied. You can only manage your own or assigned COS vacancies.'
            );
        }

        ExamDetail::where('vacancy_id', $vacancy_id)->delete();
        ExamItems::where('vacancy_id', $vacancy_id)->delete();
        Applications::where('vacancy_id', $vacancy_id)->delete();

        $vacancy->delete();

        activity()
            ->event('delete')
            ->causedBy(auth('admin')->user())
            ->performedOn($vacancy)
            ->withProperties(['position_title' => $vacancy->position_title, 'section' => 'Job Vacancy'])
            ->log('Deleted job vacancy.');


        return redirect()->route('vacancies_management')->with('success', 'Vacancy deleted successfully.');
    }

    public function jobDescription(Request $request, $vacancy_id)
    {
        if (Auth::check() && ApplicantOnboarding::shouldRequire(Auth::user())) {
            return redirect()
                ->route('dashboard_user')
                ->with('open_onboarding_modal', true)
                ->with('onboarding_prefill_vacancy_id', $vacancy_id)
                ->with('status', 'Please complete onboarding before viewing position details.');
        }

        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();
        $requiredEligibilityItems = $this->extractVacancyEligibilityItems((string) ($vacancy->qualification_eligibility ?? ''));
        $qualificationEligibilityDisplay = !empty($requiredEligibilityItems)
            ? $this->formatVacancyEligibilityDisplay($requiredEligibilityItems)
            : (trim((string) ($vacancy->qualification_eligibility ?? '')) ?: 'Not specified');

        $hasPDS = PersonalInformation::where('user_id', Auth::id())->exists();
        $hasCompletedPdsForApply = Auth::check()
            ? $this->hasCompletedPdsForApply((int) Auth::id())
            : false;

        $hasApplied = Applications::where('user_id', Auth::id())
            ->where('vacancy_id', $vacancy_id)
            ->exists();

        $normalizedVacancyTrack = $this->normalizeTrack($vacancy->vacancy_type);
        $docTrackMismatchState = [
            'hasMismatch' => false,
            'submittedTrack' => null,
            'vacancyTrack' => $normalizedVacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $normalizedVacancyTrack,
                'vacancy_id' => $vacancy->vacancy_id,
            ]),
        ];
        $requiredDocsModalState = [
            'hasMissing' => false,
            'previewDocs' => [],
            'vacancyTrack' => $normalizedVacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $normalizedVacancyTrack,
                'vacancy_id' => $vacancy->vacancy_id,
            ]),
        ];
        $qualificationGateState = [
            'isQualified' => true,
            'message' => null,
            'checks' => [],
        ];

        if (Auth::check()) {
            $docTrackMismatchState = $this->getDocumentTrackMismatchState((int) Auth::id(), (string) $vacancy->vacancy_type, (string) $vacancy->vacancy_id);
            $requiredDocsModalState = $this->getRequiredDocsModalState((int) Auth::id(), (string) $vacancy->vacancy_type, (string) $vacancy->vacancy_id);
            $qualificationGateState = $this->evaluateApplicantQualificationGateForVacancy((int) Auth::id(), $vacancy);
        }

        $missingQualificationLabels = $this->collectMissingQualificationLabels((array) ($qualificationGateState['checks'] ?? []));
        $isQualificationQualified = empty($missingQualificationLabels);
        $qualificationMismatchMessage = $qualificationGateState['message'] ?? null;
        if (!$isQualificationQualified && empty($qualificationMismatchMessage)) {
            $qualificationMismatchMessage = 'You are not yet qualified to apply for this position. '
                . 'Please review the missing requirements and update your PDS.';
        }

        return view('dashboard_user.job_description', [
            'vacancy' => $vacancy,
            'qualificationEligibilityDisplay' => $qualificationEligibilityDisplay,
            'hasPDS' => $hasPDS,
            'hasCompletedPdsForApply' => $hasCompletedPdsForApply,
            'hasApplied' => $hasApplied,
            'docTrackMismatch' => $docTrackMismatchState['hasMismatch'],
            'mismatchSubmittedTrack' => $docTrackMismatchState['submittedTrack'],
            'vacancyTrack' => $requiredDocsModalState['vacancyTrack'],
            'docUploadRedirectUrl' => $requiredDocsModalState['redirectUrl'],
            'hasMissingRequiredDocs' => $requiredDocsModalState['hasMissing'],
            'requiredDocsPreview' => $requiredDocsModalState['previewDocs'],
            'isEligibilityQualified' => $isQualificationQualified,
            'eligibilityMismatchMessage' => $qualificationMismatchMessage,
            'qualificationChecks' => $qualificationGateState['checks'],
            'missingQualificationLabels' => $missingQualificationLabels,
        ]);


    }

    public function adminFilterVacancy(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        $job = $request->get('job');
        $place = $request->get('place');
        $isHrDivisionUser = $this->isHrDivisionAdmin();

        $vacanciesQuery = JobVacancy::query();

        if ($isHrDivisionUser) {
            $this->applyHrDivisionManagedVacancyScope($vacanciesQuery);
        }

        $vacancies = $vacanciesQuery
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($job, function ($query) use ($job) {
                $query->where('vacancy_type', $job);
            })
            ->when($place, function ($query) use ($place) {
                $query->where('place_of_assignment', $place);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q
                        ->orWhere('vacancy_id', 'like', "%{$search}%")
                        ->orWhere('position_title', 'like', "%{$search}%")
                        ->orWhere('vacancy_type', 'like', "%{$search}%")
                        ->orWhere('monthly_salary', 'like', "%{$search}%")
                        ->orWhere('place_of_assignment', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('closing_date', 'like', "%{$search}%")
                        ->orWhere('qualification_education', 'like', "%{$search}%")
                        ->orWhere('qualification_training', 'like', "%{$search}%")
                        ->orWhere('qualification_experience', 'like', "%{$search}%")
                        ->orWhere('qualification_eligibility', 'like', "%{$search}%")
                        ->orWhere('scope_of_work', 'like', "%{$search}%")
                        ->orWhere('expected_output', 'like', "%{$search}%")
                        ->orWhere('duration_of_work', 'like', "%{$search}%")
                        ->orWhere('to_person', 'like', "%{$search}%")
                        ->orWhere('to_position', 'like', "%{$search}%")
                        ->orWhere('to_office', 'like', "%{$search}%")
                        ->orWhere('to_office_address', 'like', "%{$search}%")
                        ->orWhere('created_at', 'like', "%{$search}%")
                        ->orWhere('updated_at', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE WHEN status = 'OPEN' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

        session(['vacancyFilterSearch' => $search]);
        session(['vacancyFilterJob' => $job]);
        session(['vacancyFilterStatus' => $status]);
        session(['vacancyFilterPlace' => $place]);

        /*activity()
            ->causedBy(auth()->user())
            ->log('Filtered job vacancies (admin).');
        */

        return view('partials.admin_vacancy_list', [
            'vacancies' => $vacancies,
            'isHrDivisionUser' => $isHrDivisionUser,
        ])->render();
    }


    public function filterVacancy(Request $request)
    {
        $vacancies = JobVacancy::select('job_vacancies.*')
            ->leftJoin('exam_details', function ($join) {
                $join->whereRaw('job_vacancies.vacancy_id COLLATE utf8mb4_unicode_ci = exam_details.vacancy_id COLLATE utf8mb4_unicode_ci');
            })
            ->with('examDetail');

        if ($request->search) {
            $s = trim($request->search);
            $vacancies->where(function ($q) use ($s) {
                $q->where('position_title', 'like', "%{$s}%")
                    ->orWhere('place_of_assignment', 'like', "%{$s}%")
                    ->orWhere('job_vacancies.vacancy_id', 'like', "%{$s}%")
                    ->orWhere('vacancy_type', 'like', "%{$s}%");
            });
        }
        $status = trim((string) $request->input('status', ''));
        if ($status !== '') {
            $vacancies->whereRaw(
                "UPPER(TRIM(COALESCE(job_vacancies.status, ''))) = ?",
                [strtoupper($status)]
            );
        }

        $type = trim((string) $request->input('type', ''));
        if ($type !== '') {
            $vacancies->whereRaw(
                "LOWER(TRIM(COALESCE(vacancy_type, ''))) = ?",
                [strtolower($type)]
            );
        }

        if ($request->place) {
            $vacancies->where('place_of_assignment', $request->place);
        }

        if ($request->salary) {
            [$min, $max] = explode('-', $request->salary);
            $vacancies->whereBetween('monthly_salary', [$min * 1000, $max * 1000]);
        }

        // Priority sorting: Scheduled (future exam) → Open (unscheduled) → Completed (past exam) → Closed
        $vacancies->orderByRaw("CASE 
            WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NOT NULL AND exam_details.date >= CURDATE() THEN 1 
            WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NULL THEN 2 
            WHEN job_vacancies.status = 'OPEN' AND exam_details.date IS NOT NULL AND exam_details.date < CURDATE() THEN 3 
            ELSE 4 
        END");

        if ($request->sort == 'latest') {
            $vacancies->orderBy('job_vacancies.created_at', 'desc');
        } elseif ($request->sort == 'oldest') {
            $vacancies->orderBy('job_vacancies.created_at', 'asc');
        }

        $vacancies = $vacancies->get();

        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Filtered job vacancies (user).');

        */

        return view('partials.vacancy_list', compact('vacancies'))->render();
    }

    public function getOpenVacanciesForDashboard()
    {
        $userId = Auth::id();

        $vacancies = collect();
        $openVacanciesQuery = JobVacancy::query()->where('status', 'OPEN');
        $openVacancyCount = (clone $openVacanciesQuery)->count();
        $cosVacancyCount = (clone $openVacanciesQuery)
            ->whereRaw('UPPER(vacancy_type) = ?', ['COS'])
            ->count();
        $plantillaVacancyCount = max($openVacancyCount - $cosVacancyCount, 0);

        $applications = \App\Models\Applications::query()
            ->select([
                'id',
                'user_id',
                'vacancy_id',
                'status',
                'qs_result',
                'deadline_date',
                'deadline_time',
                'created_at',
            ])
            ->where('user_id', $userId)
            ->with(['vacancy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdsProgress = (int) round($this->calculatePdsProgress(Auth::id()));
        $hasPDS = PersonalInformation::where('user_id', Auth::id())->exists();
        $hasWES = WorkExpSheet::where('user_id', Auth::id())->exists();

        // Application Status Summary
        $statusSummary = $applications->groupBy('status')->map->count();

        // Upcoming exams for user's applied vacancies
        $vacancyIds = $applications->pluck('vacancy_id')->filter()->unique()->values();
        $now = Carbon::now()->toDateTimeString();
        $upcomingExamsCount = ExamDetail::whereIn('vacancy_id', $vacancyIds)
            ->whereRaw("STR_TO_DATE(CONCAT(`date`, ' ', `time`), '%Y-%m-%d %H:%i:%s') > ?", [$now])
            ->count();
        $upcomingExams = collect();

        // Required Documents Status
        $uploadedDocuments = UploadedDocument::where('user_id', $userId)->get()->keyBy('document_type');
        $documentStatusSummary = [];
        foreach (UploadedDocument::DOCUMENTS as $docType) {
            if ($docType === 'isApproved')
                continue;
            $doc = $uploadedDocuments->get($docType);
            $documentStatusSummary[] = [
                'type' => $docType,
                'status' => $doc ? ($doc->status ?? 'PENDING') : 'Not Submitted',
            ];
        }
        // Include quick flags for PDS/WES completion
        $documentStatusSummary[] = ['type' => 'pds', 'status' => $hasPDS ? 'Completed' : 'Incomplete'];
        $documentStatusSummary[] = ['type' => 'wes', 'status' => $hasWES ? 'Completed' : 'Incomplete'];

        // Recently closed positions among user's applications
        $recentlyClosedApplications = $applications->filter(function ($app) {
            return $app->vacancy && $app->vacancy->status === 'CLOSED';
        })->values();

        // Deadline countdown per active application
        $now = Carbon::now();
        $deadlineCountdown = $applications
            ->filter(function ($app) {
                if (!$app->deadline_date || !$app->deadline_time) {
                    return false;
                }

                $applicationStatus = strtolower(trim((string) ($app->status ?? '')));
                $qsResult = strtolower(trim((string) ($app->qs_result ?? '')));
                $isTerminalStatus = in_array($applicationStatus, ['closed', 'qualified'], true);
                $isVacancyClosed = $app->vacancy && strtolower((string) ($app->vacancy->status ?? '')) === 'closed';

                return !$isTerminalStatus && !$isVacancyClosed && $qsResult !== 'qualified';
            })
            ->map(function ($app) use ($now) {
                $deadline = Carbon::parse($app->deadline_date . ' ' . $app->deadline_time);
                $secondsRemaining = $now->diffInSeconds($deadline, false);
                if ($secondsRemaining <= 0) {
                    return null;
                }

                return [
                    'vacancy_id' => $app->vacancy_id,
                    'position_title' => $app->vacancy->position_title ?? '',
                    'deadline' => $deadline->toDateTimeString(),
                    'days_remaining' => (int) ceil($secondsRemaining / 86400),
                ];
            })
            ->filter()
            ->sortBy('days_remaining')
            ->values();

        // Notifications/Alerts (latest 5, and unread count)
        $recentNotifications = Auth::user()?->notifications()->orderBy('created_at', 'desc')->take(5)->get() ?? collect();
        $unreadNotificationsCount = Auth::user()?->unreadNotifications()->count() ?? 0;

        $authUser = Auth::user();
        $savedOnboarding = ApplicantOnboarding::payload($authUser);
        $requiresApplicantOnboarding = ApplicantOnboarding::shouldRequire($authUser);
        $openOnboardingModal = $requiresApplicantOnboarding || (bool) session('open_onboarding_modal', false);

        $onboardingVacancies = JobVacancy::query()
            ->where('status', 'OPEN')
            ->whereRaw('DATE(closing_date) >= DATE(NOW())')
            ->orderBy('closing_date')
            ->get([
                'vacancy_id',
                'position_title',
                'vacancy_type',
                'place_of_assignment',
                'qualification_education',
                'qualification_experience',
                'qualification_training',
                'qualification_eligibility',
            ]);

        $onboardingVacancyOptions = $onboardingVacancies->map(function (JobVacancy $vacancy) {
            return [
                'vacancy_id' => (string) $vacancy->vacancy_id,
                'position_title' => (string) $vacancy->position_title,
                'vacancy_type' => (string) ($vacancy->vacancy_type ?? ''),
                'place_of_assignment' => (string) ($vacancy->place_of_assignment ?? ''),
                'requirements' => [
                    'education' => $this->onboardingRequirementText($vacancy->qualification_education),
                    'experience' => $this->onboardingRequirementText($vacancy->qualification_experience),
                    'training' => $this->onboardingRequirementText($vacancy->qualification_training),
                    'eligibility' => $this->onboardingEligibilityText($vacancy->qualification_eligibility),
                ],
            ];
        })->values();

        $prefillOnboardingVacancyId = trim((string) session('onboarding_prefill_vacancy_id', ''));
        $selectedOnboardingVacancyId = $prefillOnboardingVacancyId !== ''
            ? $prefillOnboardingVacancyId
            : trim((string) ($savedOnboarding['preferred_vacancy_id'] ?? ''));
        $hasSelectedOnboardingVacancy = $selectedOnboardingVacancyId !== ''
            && $onboardingVacancyOptions->contains(
                fn (array $item) => (string) ($item['vacancy_id'] ?? '') === $selectedOnboardingVacancyId
            );
        if (!$hasSelectedOnboardingVacancy) {
            $selectedOnboardingVacancyId = (string) ($onboardingVacancyOptions->first()['vacancy_id'] ?? '');
        }

        return view('dashboard_user.dashboard_user', [
            'vacancies' => $vacancies,
            'openVacancyCount' => $openVacancyCount,
            'applications' => $applications,
            'pdsProgress' => $pdsProgress,
            'hasPDS' => $hasPDS,
            'hasWES' => $hasWES,
            'statusSummary' => $statusSummary,
            'cosVacancyCount' => $cosVacancyCount,
            'plantillaVacancyCount' => $plantillaVacancyCount,
            'upcomingExams' => $upcomingExams,
            'upcomingExamsCount' => $upcomingExamsCount,
            'documentStatusSummary' => $documentStatusSummary,
            'recentlyClosedApplications' => $recentlyClosedApplications,
            'deadlineCountdown' => $deadlineCountdown,
            'recentNotifications' => $recentNotifications,
            'unreadNotificationsCount' => $unreadNotificationsCount,
            'requiresApplicantOnboarding' => $requiresApplicantOnboarding,
            'openOnboardingModal' => $openOnboardingModal,
            'onboardingVacancyOptions' => $onboardingVacancyOptions,
            'selectedOnboardingVacancyId' => $selectedOnboardingVacancyId,
            'savedApplicantOnboarding' => $savedOnboarding,
        ]);

    }

    private function onboardingRequirementText(?string $raw): string
    {
        $text = trim((string) $raw);
        if ($text === '') {
            return 'Not specified';
        }

        return preg_replace('/\s+/', ' ', $text) ?: $text;
    }

    private function onboardingEligibilityText(?string $raw): string
    {
        $items = $this->extractVacancyEligibilityItems((string) ($raw ?? ''));
        if (!empty($items)) {
            return $this->formatVacancyEligibilityDisplay($items);
        }

        return $this->onboardingRequirementText($raw);
    }


    public function apply(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();
        Log::info('Apply request received', [
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancy_id,
        ]);

        if (ApplicantOnboarding::shouldRequire(Auth::user())) {
            Log::info('Apply blocked: applicant onboarding not completed', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
            ]);
            return redirect()
                ->route('dashboard_user')
                ->with('open_onboarding_modal', true)
                ->with('onboarding_prefill_vacancy_id', $vacancy->vacancy_id)
                ->with('status', 'Please complete onboarding before you apply.');
        }

        if (!$this->hasCompletedPdsForApply((int) Auth::id())) {
            Log::info('Apply blocked: incomplete PDS', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('pds_required_prompt', true);
        }

        // Check if user already applied
        $existing = \App\Models\Applications::where('user_id', Auth::id())
            ->where('vacancy_id', $vacancy->vacancy_id)
            ->first();

        if ($existing) {
            Log::info('Apply skipped: already applied', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
                'application_id' => $existing->id,
            ]);
            return redirect()
                ->route('my_applications')
                ->with('success', 'Application already exists for this vacancy.');
        }

        $requiredDocsModalState = $this->getRequiredDocsModalState((int) Auth::id(), (string) $vacancy->vacancy_type, (string) $vacancy->vacancy_id);
        if ($requiredDocsModalState['hasMissing']) {
            Log::info('Apply blocked: required docs missing', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('required_docs_prompt', [
                    'vacancy_id' => $vacancy->vacancy_id,
                    'vacancy_track' => $requiredDocsModalState['vacancyTrack'],
                    'redirect_url' => $requiredDocsModalState['redirectUrl'],
                    'preview_docs' => $requiredDocsModalState['previewDocs'],
                ]);
        }

        $docTrackMismatchState = $this->getDocumentTrackMismatchState((int) Auth::id(), (string) $vacancy->vacancy_type, (string) $vacancy->vacancy_id);
        if ($docTrackMismatchState['hasMismatch']) {
            Log::info('Apply blocked: doc track mismatch', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
                'submitted_track' => $docTrackMismatchState['submittedTrack'],
                'vacancy_track' => $docTrackMismatchState['vacancyTrack'],
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('doc_track_mismatch', [
                    'vacancy_id' => $vacancy->vacancy_id,
                    'submitted_track' => $docTrackMismatchState['submittedTrack'],
                    'vacancy_track' => $docTrackMismatchState['vacancyTrack'],
                    'redirect_url' => $docTrackMismatchState['redirectUrl'],
                ]);
        }

        $qualificationGate = $this->evaluateApplicantQualificationGateForVacancy((int) Auth::id(), $vacancy);
        $missingQualificationLabels = $this->collectMissingQualificationLabels((array) ($qualificationGate['checks'] ?? []));
        if (!empty($missingQualificationLabels)) {
            Log::info('Apply blocked: qualification requirements not met', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
                'qualification_checks' => $qualificationGate['checks'],
            ]);

            $qualificationMismatchMessage = $qualificationGate['message'] ?? (
                'You are not yet qualified to apply for this position. '
                . 'Please review the missing requirements and update your PDS.'
            );

            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('error', $qualificationMismatchMessage);
        }

        $requiredDocumentIds = $this->getRequiredDocumentIdsForVacancyType((string) $vacancy->vacancy_type);
        $this->seedVacancyDocumentsFromReusableUploads(
            (int) Auth::id(),
            (string) $vacancy->vacancy_id,
            $requiredDocumentIds
        );

        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $applicationLetterDocQuery = UploadedDocument::where('user_id', Auth::id())
            ->where('document_type', 'application_letter')
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT');
        if ($supportsVacancyScopedDocs) {
            $applicationLetterDocQuery->orderByRaw(
                "CASE WHEN vacancy_id = ? THEN 0 WHEN vacancy_id IS NULL THEN 1 ELSE 2 END",
                [(string) $vacancy->vacancy_id]
            );
        }
        $applicationLetterDoc = $applicationLetterDocQuery
            ->latest('updated_at')
            ->first();

        if (!$applicationLetterDoc) {
            $latestApplicationLetter = Applications::where('user_id', Auth::id())
                ->whereNotNull('file_storage_path')
                ->latest('updated_at')
                ->first();

            if ($latestApplicationLetter) {
                $applicationLetterDoc = UploadedDocument::updateOrCreate(
                    $supportsVacancyScopedDocs
                        ? [
                            'user_id' => Auth::id(),
                            'vacancy_id' => (string) $vacancy->vacancy_id,
                            'document_type' => 'application_letter',
                        ]
                        : [
                            'user_id' => Auth::id(),
                            'document_type' => 'application_letter',
                        ],
                    [
                        'original_name' => (string) ($latestApplicationLetter->file_original_name
                            ?: basename((string) $latestApplicationLetter->file_storage_path)),
                        'stored_name' => (string) ($latestApplicationLetter->file_stored_name
                            ?: basename((string) $latestApplicationLetter->file_storage_path)),
                        'storage_path' => (string) $latestApplicationLetter->file_storage_path,
                        'mime_type' => 'application/pdf',
                        'file_size_8b' => (int) ($latestApplicationLetter->file_size_8b ?? 0),
                        'status' => 'Pending',
                        'remarks' => '',
                        'last_modified_by' => Auth::user()?->name ?? 'System',
                    ]
                );
            }
        }

        if (!$applicationLetterDoc) {
            Log::info('Apply blocked: application letter not found in UploadedDocument', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('required_docs_prompt', [
                    'vacancy_track' => $requiredDocsModalState['vacancyTrack'],
                    'redirect_url' => $requiredDocsModalState['redirectUrl'],
                    'preview_docs' => $requiredDocsModalState['previewDocs'],
                ]);
        }

        if (
            $supportsVacancyScopedDocs
            && (string) ($applicationLetterDoc->vacancy_id ?? '') !== (string) $vacancy->vacancy_id
        ) {
            $applicationLetterDoc = $this->upsertVacancyDocumentFromSource(
                $applicationLetterDoc,
                (string) $vacancy->vacancy_id,
                'application_letter'
            );
        }


        // Create application
        $educationRequirementSnapshot = $this->normalizeQualificationRequirement($vacancy->qualification_education ?? null);
        $educationRuleSnapshot = $this->resolveCompiledEducationRuleForVacancy($vacancy, $educationRequirementSnapshot);

        $applicationPayload = [
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => ApplicationStatus::PENDING->value,
            'is_valid' => true,

            'file_original_name' => $applicationLetterDoc->original_name,
            'file_stored_name' => $applicationLetterDoc->stored_name,
            'file_storage_path' => $applicationLetterDoc->storage_path,
            'file_status' => 'Submitted',
            'file_remarks' => null,
            'file_size_8b' => $applicationLetterDoc->file_size_8b,
        ];

        if (Schema::hasColumn('applications', 'education_requirement_snapshot')) {
            $applicationPayload['education_requirement_snapshot'] = $educationRequirementSnapshot;
        }
        if (Schema::hasColumn('applications', 'education_rule_snapshot')) {
            $applicationPayload['education_rule_snapshot'] = $educationRuleSnapshot;
        }
        if (Schema::hasColumn('applications', 'education_rule_snapshot_version')) {
            $applicationPayload['education_rule_snapshot_version'] = is_array($educationRuleSnapshot)
                ? (int) ($educationRuleSnapshot['parser_version'] ?? self::EDUCATION_RULE_PARSER_VERSION)
                : null;
        }

        $application = \App\Models\Applications::create($applicationPayload);
        Log::info('Apply success: application created', [
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancy_id,
            'application_id' => $application->id,
        ]);

        // Consume fresh-upload marker for this vacancy after successful application submit.
        $vacancyUploads = session('vacancy_doc_uploads', []);
        if (is_array($vacancyUploads) && array_key_exists((string) $vacancy->vacancy_id, $vacancyUploads)) {
            unset($vacancyUploads[(string) $vacancy->vacancy_id]);
            session(['vacancy_doc_uploads' => $vacancyUploads]);
        }

        // Keep apply response fast: store lightweight DB notifications directly.
        $admins = \App\Models\Admin::all();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'notifiable_type' => 'App\Models\Admin',
                'notifiable_id' => $admin->id,
                'type' => 'warning',
                'data' => [
                    'title' => 'New Job Application',
                    'message' => Auth::user()->name . ' submitted an application for ' . $vacancy->position_title . '.',
                    'link' => route('admin.applicant_status', ['user_id' => Auth::id(), 'vacancy_id' => $vacancy->vacancy_id], false),
                    'section' => 'Application List',
                    'category' => 'document_verification',
                    'user_id' => Auth::id(),
                    'vacancy_id' => $vacancy->vacancy_id,
                ],
                'read_at' => null,
            ]);
        }

        activity()
            ->event('apply job')
            ->causedBy(Auth::user())
            ->performedOn($vacancy)
            ->withProperties(['vacancy_id' => $vacancy->vacancy_id, 'section' => 'Job Vacancy'])
            ->log('Applied to job vacancy.');

        return redirect()->route('my_applications')->with('success', 'Application submitted successfully!');
    }

    public function myApplications()
    {
        if (Auth::check() && ApplicantOnboarding::shouldRequire(Auth::user())) {
            return redirect()
                ->route('dashboard_user')
                ->with('open_onboarding_modal', true)
                ->with('status', 'Please complete onboarding before viewing applications.');
        }

        $applications = $this->buildMyApplicationsQuery(request())->get();
        $filterOptions = $this->getMyApplicationFilterOptions();
        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Viewed my applications.');
        */

        return view('dashboard_user.my_applications', [
            'applications' => $applications,
            'filterOptions' => $filterOptions,
        ]);
    }

    // USEREND application status
    public function applicationStatus($user_id, $vacancy_id)
    {
        if (Auth::check() && ApplicantOnboarding::shouldRequire(Auth::user())) {
            return redirect()
                ->route('dashboard_user')
                ->with('open_onboarding_modal', true)
                ->with('status', 'Please complete onboarding before viewing application status.');
        }

        if ((int) Auth::id() !== (int) $user_id) {
            abort(403, 'Unauthorized access to this application.');
        }

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->with(['personalInformation', 'vacancy'])
            ->firstOrFail();

        if (strcasecmp(trim((string) ($application->status ?? '')), 'Not Qualified') === 0) {
            return redirect()->route('my_applications')
                ->with('error', 'This application is already marked as Not Qualified and can no longer be opened.');
        }

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();

        $snapshotNotification = \App\Models\Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user_id)
            ->where('data->type', 'application_overview')
            ->where('data->vacancy_id', $vacancy_id)
            ->latest()
            ->first();
        $snapshotData = $snapshotNotification?->data ?? null;
        $snapshotDocumentsById = collect($snapshotData['documents'] ?? [])->keyBy('id');

        $adminName = $snapshotData['last_modified_by'] ?? null;
        $lastModifiedAt = $snapshotData['notified_at'] ?? null;

        $uploadedDocuments = $this->loadUploadedDocumentsMap((int) $user_id, (string) $vacancy_id);
        $isFinalRevisionDisqualified = $this->hasFinalRevisionDisqualification($application, $uploadedDocuments);
        $documents = [];

        $labelMap = [
            'application_letter' => 'Application Letter',
            'signed_pds' => 'Signed and Subscribed Personal Data Sheet',
            'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
            'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
            'cert_eligibility' => 'Certificate of Eligibility / Board Rating',
            'ipcr' => 'Performance Rating/IPCR in the last period (if applicable)',
            'non_academic' => 'Non-Academic Awards Received',
            'cert_training' => 'Certificate/s of Training Attended/Participated relevant to the position being applied',
            'designation_order' => 'List with Certified Photocopy of Duly Confirmed Designation Order/s',
            'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
            'photocopy_diploma' => 'Diploma',
            'grade_masteraldoctorate' => 'Certified Photocopy of Certificate of Grades with Masteral/Doctorate Units Earned',
            'tor_masteraldoctorate' => 'Certified Photocopy of TOR with Masteral/Doctorate Degree',
            'cert_employment' => 'Certificate of Employment (If Any)',
            'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
            'passport_photo' => '2" x 2" or Passport Size Picture',
            'other_documents' => 'Other Documents Submitted',
        ];

        foreach (UploadedDocument::DOCUMENTS as $docType) {
            // Skip "isApproved" since it's not a document
            if ($docType === 'isApproved')
                continue;

            if ($docType === 'application_letter') {
                // Always get from Applications table for live data
                $documents[] = [ // Get from Applications table instead
                    'id' => 'application_letter',
                    'name' => $labelMap['application_letter'],
                    'text' => $labelMap['application_letter'],
                    'status' => $application->file_status ?? ($application->file_storage_path ? 'Pending' : 'Not Submitted'),
                    'preview' => $application->file_storage_path
                        ? url('/preview-file/' . base64_encode($application->file_storage_path))
                        : '',
                    'remarks' => $application->file_remarks ?? '',
                    'last_modified_by' => $application->file_last_modified_by ?? null,
                    'isBold' => true,
                ];
            } else {
                // Always prioritize live data over snapshot
                $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                $hasFile = $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';

                $status = 'Not Submitted';
                if ($doc) {
                    if (!empty($doc->status)) {
                        $status = $doc->status;
                    } elseif ($hasFile) {
                        $status = 'Pending';
                    }
                }

                $documents[] = [
                    'id' => $docType,
                    'name' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'text' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'status' => $status,
                    'preview' => ($doc && !empty($doc->storage_path)) ? url('/preview-file/' . base64_encode($doc->storage_path)) : '',
                    'remarks' => $doc?->remarks ?? '',
                    'last_modified_by' => $doc?->last_modified_by,
                    'isBold' => true,
                ];
            }
        }

        $requiredDocumentIds = $this->getRequiredDocumentIdsForVacancyType($application->vacancy?->vacancy_type);
        $documents = $this->sortDocumentsForRequiredPriority($documents, $requiredDocumentIds);

        $displayApplicationStatus = $application->status ?? 'Pending';
        // Show only manually saved QS values from admin review.
        $displayQsEducation = $application->qs_education ?? 'no';
        $displayQsEligibility = $application->qs_eligibility ?? 'no';
        $displayQsExperience = $application->qs_experience ?? 'no';
        $displayQsTraining = $application->qs_training ?? 'no';
        $displayQsResult = $application->qs_result ?? 'Not Qualified';
        $displayDeadlineDate = $application->deadline_date ?? null;
        $displayDeadlineTime = $application->deadline_time ?? null;
        $displayApplicationRemarks = $application->application_remarks ?? '';

        /*
        activity()
            ->causedBy(auth()->user())
            ->performedOn($application)
            ->withProperties(['vacancy_id' => $application->vacancy_id])
            ->log('Viewed application status.');
        */

        return view('dashboard_user.application_status', compact(
            'application',
            'examDetail',
            'documents',
            'requiredDocumentIds',
            'adminName',
            'lastModifiedAt',
            'displayApplicationStatus',
            'displayQsEducation',
            'displayQsEligibility',
            'displayQsExperience',
            'displayQsTraining',
            'displayQsResult',
            'displayDeadlineDate',
            'displayDeadlineTime',
            'displayApplicationRemarks',
            'isFinalRevisionDisqualified',
            'user_id',
            'vacancy_id'
        ));
    }

    /**
     * Get updated documents for AJAX refresh (user endpoint)
     */
    public function getUpdatedDocumentsUser(Request $request, $user_id, $vacancy_id)
    {
        if ((int) Auth::id() !== (int) $user_id) {
            return response()->json(['error' => 'Unauthorized access to this application.'], 403);
        }

        // Debug logging
        \Log::info("getUpdatedDocumentsUser called", [
            'user_id' => $user_id,
            'vacancy_id' => $vacancy_id,
            'auth_user_id' => Auth::id(),
            'method' => $request->method()
        ]);

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->with(['personalInformation', 'vacancy'])
            ->first();

        if (!$application) {
            \Log::error("Application not found", ['user_id' => $user_id, 'vacancy_id' => $vacancy_id]);
            return response()->json(['error' => 'Application not found'], 404);
        }

        if (strcasecmp(trim((string) ($application->status ?? '')), 'Not Qualified') === 0) {
            return response()->json([
                'error' => 'This application is already marked as Not Qualified and can no longer be opened.'
            ], 403);
        }

        // Use the same logic as applicationStatus method
        $snapshotNotification = \App\Models\Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user_id)
            ->where('data->type', 'application_overview')
            ->where('data->vacancy_id', $vacancy_id)
            ->latest()
            ->first();
        $snapshotData = $snapshotNotification?->data ?? null;
        $snapshotDocumentsById = collect($snapshotData['documents'] ?? [])->keyBy('id');

        $uploadedDocuments = $this->loadUploadedDocumentsMap((int) $user_id, (string) $vacancy_id);
        $isFinalRevisionDisqualified = $this->hasFinalRevisionDisqualification($application, $uploadedDocuments);
        $documents = [];

        // Debug: Log uploaded documents count
        \Log::info("Uploaded documents found", ['count' => $uploadedDocuments->count()]);

        $labelMap = [
            'application_letter' => 'Application Letter',
            'signed_pds' => 'Signed and Subscribed Personal Data Sheet',
            'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
            'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
            'cert_eligibility' => 'Certificate of Eligibility / Board Rating',
            'ipcr' => 'Performance Rating/IPCR in the last period (if applicable)',
            'non_academic' => 'Non-Academic Awards Received',
            'cert_training' => 'Certificate/s of Training Attended/Participated relevant to the position being applied',
            'designation_order' => 'List with Certified Photocopy of Duly Confirmed Designation Order/s',
            'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
            'photocopy_diploma' => 'Diploma',
            'grade_masteraldoctorate' => 'Certified Photocopy of Certificate of Grades with Masteral/Doctorate Units Earned',
            'tor_masteraldoctorate' => 'Certified Photocopy of TOR with Masteral/Doctorate Degree',
            'cert_employment' => 'Certificate of Employment (If Any)',
            'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
            'passport_photo' => '2" x 2" or Passport Size Picture',
            'other_documents' => 'Other Documents Submitted',
        ];

        foreach (UploadedDocument::DOCUMENTS as $docType) {
            // Skip "isApproved" since it's not a document
            if ($docType === 'isApproved')
                continue;

            if ($docType === 'application_letter') {
                // Always get from Applications table for live data
                $documents[] = [ // Get from Applications table instead
                    'id' => 'application_letter',
                    'name' => $labelMap['application_letter'],
                    'text' => $labelMap['application_letter'],
                    'status' => $application->file_status ?? ($application->file_storage_path ? 'Pending' : 'Not Submitted'),
                    'preview' => $application->file_storage_path
                        ? url('/preview-file/' . base64_encode($application->file_storage_path))
                        : '',
                    'remarks' => $application->file_remarks ?? '',
                    'last_modified_by' => $application->file_last_modified_by ?? null,
                    'isBold' => true,
                ];
            } else {
                // Always prioritize live data over snapshot
                $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                $hasFile = $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';

                // Debug: Log document details
                \Log::info("Document check for {$docType} in getUpdatedDocumentsUser", [
                    'doc_found' => $doc ? true : false,
                    'has_file' => $hasFile,
                    'storage_path' => $doc?->storage_path,
                    'status' => $doc?->status,
                    'last_modified_by' => $doc?->last_modified_by
                ]);

                // Use actual status from database if document exists
                $status = 'Not Submitted';
                if ($doc) {
                    if (!empty($doc->status)) {
                        $status = $doc->status;
                    } elseif ($hasFile) {
                        $status = 'Pending';
                    }
                }

                $documents[] = [
                    'id' => $docType,
                    'name' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'text' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'status' => $status,
                    'preview' => ($doc && !empty($doc->storage_path)) ? url('/preview-file/' . base64_encode($doc->storage_path)) : '',
                    'remarks' => $doc?->remarks ?? '',
                    'last_modified_by' => $doc?->last_modified_by,
                    'isBold' => true,
                ];
            }
        }

        $requiredDocumentIds = $this->getRequiredDocumentIdsForVacancyType($application->vacancy?->vacancy_type);
        $documents = $this->sortDocumentsForRequiredPriority($documents, $requiredDocumentIds);

        \Log::info("Final documents array in getUpdatedDocumentsUser", ['count' => count($documents)]);

        return response()->json([
            'documents' => $documents,
            'requiredDocumentIds' => $requiredDocumentIds,
            'application' => [
                'status' => $application->status ?? 'Pending',
                'qs_result' => $application->qs_result ?? null,
                'file_last_modified_by' => $application->file_last_modified_by ?? null,
                'deadline_date' => $application->deadline_date ?? null,
                'deadline_time' => $application->deadline_time ?? null,
                'is_past_deadline' => $this->hasRevisionDeadlinePassed($application),
                'final_revision_disqualified' => $isFinalRevisionDisqualified,
            ]
        ]);
    }

    private function isRevisionStatus(?string $status): bool
    {
        $normalized = strtolower(trim((string) $status));
        return in_array($normalized, ['needs revision', 'disapproved with deficiency'], true);
    }

    private function hasSatisfiedLatestRevisionRequest(?string $requestedAt, ?string $submittedAt): bool
    {
        if (empty($submittedAt)) {
            return false;
        }

        if (empty($requestedAt)) {
            return true;
        }

        try {
            return Carbon::parse($submittedAt)->greaterThanOrEqualTo(Carbon::parse($requestedAt));
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function isRevisionComplianceLocked(int $requestedCount, ?string $requestedAt, ?string $submittedAt): bool
    {
        return $requestedCount >= 2 && $this->hasSatisfiedLatestRevisionRequest($requestedAt, $submittedAt);
    }

    private function hasRevisionDeadlinePassed(?Applications $application): bool
    {
        if (!$application || empty($application->deadline_date) || empty($application->deadline_time)) {
            return false;
        }

        try {
            $timezone = (string) config('app.timezone', 'Asia/Manila');
            $deadline = Carbon::parse(
                $application->deadline_date . ' ' . $application->deadline_time,
                $timezone
            );
            return Carbon::now($timezone)->greaterThan($deadline);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function hasFinalRevisionDisqualification(Applications $application, $uploadedDocuments): bool
    {
        if ($this->isRevisionComplianceLocked(
            (int) ($application->file_revision_requested_count ?? 0),
            $application->file_revision_requested_at ?? null,
            $application->file_revision_submitted_at ?? null
        )) {
            return true;
        }

        foreach ($uploadedDocuments as $doc) {
            if ($this->isRevisionComplianceLocked(
                (int) ($doc->revision_requested_count ?? 0),
                $doc->revision_requested_at ?? null,
                $doc->revision_submitted_at ?? null
            )) {
                return true;
            }
        }

        return false;
    }

    private function resolveUploadedDocument($uploadedDocuments, string $docType): ?UploadedDocument
    {
        $doc = $uploadedDocuments->get($docType);
        if ($doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT') {
            return $doc;
        }
        foreach (self::DOCUMENT_TYPE_ALIASES[$docType] ?? [] as $alias) {
            $aliasDoc = $uploadedDocuments->get($alias);
            if ($aliasDoc && !empty($aliasDoc->storage_path) && $aliasDoc->storage_path !== 'NOINPUT') {
                return $aliasDoc;
            }
        }
        return $doc ?: null;
    }

    private function loadUploadedDocumentsMap(int $userId, ?string $vacancyId = null)
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $docsQuery = UploadedDocument::where('user_id', $userId);
        if ($supportsVacancyScopedDocs) {
            if (!empty($vacancyId)) {
                $docsQuery->where('vacancy_id', $vacancyId);
            } else {
                $docsQuery->whereNull('vacancy_id');
            }
        }

        $docs = $docsQuery
            ->orderByDesc('updated_at')
            ->get();

        return $docs
            ->unique('document_type')
            ->keyBy('document_type');
    }

    private function loadReusableUploadedDocumentsMap(int $userId, ?string $vacancyId = null)
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');

        $docsQuery = UploadedDocument::where('user_id', $userId)
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT');

        if ($supportsVacancyScopedDocs && !empty($vacancyId)) {
            $docsQuery->orderByRaw(
                "CASE WHEN vacancy_id = ? THEN 0 WHEN vacancy_id IS NULL THEN 1 ELSE 2 END",
                [(string) $vacancyId]
            );
        } elseif ($supportsVacancyScopedDocs) {
            $docsQuery->orderByRaw('CASE WHEN vacancy_id IS NULL THEN 0 ELSE 1 END');
        }

        $docs = $docsQuery
            ->orderByDesc('updated_at')
            ->get();

        return $docs
            ->unique('document_type')
            ->keyBy('document_type');
    }

    private function hasStoredUploadedDocument($uploadedDocuments, string $docType): bool
    {
        $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
        return $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
    }

    private function seedVacancyDocumentsFromReusableUploads(int $userId, string $vacancyId, array $requiredDocs): void
    {
        if (
            empty($vacancyId)
            || empty($requiredDocs)
            || !Schema::hasColumn('uploaded_documents', 'vacancy_id')
        ) {
            return;
        }

        $vacancyDocs = UploadedDocument::where('user_id', $userId)
            ->where('vacancy_id', $vacancyId)
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT')
            ->orderByDesc('updated_at')
            ->get()
            ->unique('document_type')
            ->keyBy('document_type');

        $reusableDocs = $this->loadReusableUploadedDocumentsMap($userId, $vacancyId);

        foreach ($requiredDocs as $docType) {
            if ($this->hasStoredUploadedDocument($vacancyDocs, (string) $docType)) {
                continue;
            }

            $sourceDoc = $this->resolveUploadedDocument($reusableDocs, (string) $docType);
            if (!$sourceDoc || empty($sourceDoc->storage_path) || $sourceDoc->storage_path === 'NOINPUT') {
                continue;
            }

            $seeded = $this->upsertVacancyDocumentFromSource($sourceDoc, $vacancyId, (string) $docType);
            $vacancyDocs->put((string) $docType, $seeded);
        }
    }

    private function upsertVacancyDocumentFromSource(
        UploadedDocument $source,
        string $vacancyId,
        string $targetDocType
    ): UploadedDocument {
        $destination = UploadedDocument::where('user_id', (int) $source->user_id)
            ->where('vacancy_id', $vacancyId)
            ->where('document_type', $targetDocType)
            ->orderByDesc('updated_at')
            ->first();

        $payload = [
            'original_name' => $source->original_name,
            'stored_name' => $source->stored_name,
            'storage_path' => $source->storage_path,
            'mime_type' => $source->mime_type,
            'file_size_8b' => $source->file_size_8b,
            'status' => 'Pending',
            'remarks' => '',
            'last_modified_by' => Auth::user()?->name ?? 'System',
        ];

        if ($destination) {
            $destination->update($payload);
            return $destination;
        }

        return UploadedDocument::create(array_merge($payload, [
            'user_id' => (int) $source->user_id,
            'vacancy_id' => $vacancyId,
            'document_type' => $targetDocType,
        ]));
    }

    private function getRequiredDocumentIdsForVacancyType(?string $vacancyType): array
    {
        $vacancyTrack = $this->normalizeTrack($vacancyType);
        $requiredDocumentIds = $this->getRequiredDocsByTrack()[$vacancyTrack] ?? [];

        usort($requiredDocumentIds, function ($a, $b) {
            $labelA = strtolower($this->getDocumentLabelMap()[$a] ?? $a);
            $labelB = strtolower($this->getDocumentLabelMap()[$b] ?? $b);
            return $labelA <=> $labelB;
        });

        return $requiredDocumentIds;
    }

    private function sortDocumentsForRequiredPriority(array $documents, array $requiredDocumentIds): array
    {
        $requiredLookup = array_fill_keys($requiredDocumentIds, true);

        usort($documents, function ($a, $b) use ($requiredLookup) {
            $requiredA = isset($requiredLookup[$a['id'] ?? '']) ? 0 : 1;
            $requiredB = isset($requiredLookup[$b['id'] ?? '']) ? 0 : 1;

            if ($requiredA !== $requiredB) {
                return $requiredA - $requiredB;
            }

            $labelA = strtolower((string) ($a['text'] ?? $a['name'] ?? $a['id'] ?? ''));
            $labelB = strtolower((string) ($b['text'] ?? $b['name'] ?? $b['id'] ?? ''));
            return $labelA <=> $labelB;
        });

        return $documents;
    }

    private function getDocumentLabelMap(): array
    {
        return [
            'application_letter' => 'Application Letter',
            'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
            'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
            'photocopy_diploma' => 'Diploma',
            'signed_pds' => 'Signed and Subscribed Personal Data Sheet',
            'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
            'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
            'passport_photo' => '2" x 2" or Passport Size Picture',
            'cert_eligibility' => 'Certificate of Eligibility/Board Rating',
            'ipcr' => 'Certification of Numerical Rating/Performance Rating/IPCR',
            'non_academic' => 'Non-Academic Awards Received',
            'cert_training' => 'Certificates of Training/Participation',
            'designation_order' => 'Confirmed Designation Order/s',
            'grade_masteraldoctorate' => 'Certificate of Grades with Masteral/Doctorate Units Earned',
            'tor_masteraldoctorate' => 'TOR with Masteral/Doctorate Degree',
            'cert_employment' => 'Certificate of Employment',
            'other_documents' => 'Other Documents Submitted',
        ];
    }

    private function getRequiredDocsByTrack(): array
    {
        $allDocumentTypes = array_values(array_filter(
            UploadedDocument::DOCUMENTS,
            fn($doc) => $doc !== 'isApproved'
        ));

        return [
            'COS' => self::COS_REQUIRED_DOCUMENTS,
            'Plantilla' => array_values(array_diff(
                $allDocumentTypes,
                ['tor_masteraldoctorate', 'grade_masteraldoctorate', 'cert_lgoo_induction', 'other_documents', 'pqe_result']
            )),
        ];
    }

    private function normalizeTrack(?string $track): string
    {
        return strcasecmp((string) $track, 'COS') === 0 ? 'COS' : 'Plantilla';
    }

    private function getRequiredDocsModalState(int $userId, ?string $vacancyType, ?string $vacancyId = null): array
    {
        $vacancyTrack = $this->normalizeTrack($vacancyType);
        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        $requiredDocs = $requiredDocsByTrack[$vacancyTrack] ?? [];
        $documentLabels = $this->getDocumentLabelMap();

        $previewDocs = array_map(function (string $docType) use ($documentLabels) {
            return [
                'key' => $docType,
                'label' => $documentLabels[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
            ];
        }, $requiredDocs);

        $uploadedDocuments = $this->loadReusableUploadedDocumentsMap($userId, $vacancyId);
        $hasApplicationLetterInApplications = Applications::where('user_id', $userId)
            ->whereNotNull('file_storage_path')
            ->exists();

        $hasMissing = collect($requiredDocs)->contains(function (string $docType) use ($uploadedDocuments, $hasApplicationLetterInApplications) {
            if ($docType === 'application_letter' && $hasApplicationLetterInApplications) {
                return false;
            }

            return !$this->hasStoredUploadedDocument($uploadedDocuments, $docType);
        });

        return [
            'hasMissing' => $hasMissing,
            'previewDocs' => $previewDocs,
            'vacancyTrack' => $vacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $vacancyTrack,
                'vacancy_id' => $vacancyId,
            ]),
        ];
    }

    private function getTrackCompletenessByUser(int $userId): array
    {
        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        $uploadedDocuments = UploadedDocument::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get()
            ->unique('document_type')
            ->keyBy('document_type');
        $hasApplicationLetter = Applications::where('user_id', $userId)
            ->whereNotNull('file_storage_path')
            ->exists();

        $isComplete = [];
        foreach ($requiredDocsByTrack as $track => $requiredDocs) {
            $isComplete[$track] = collect($requiredDocs)->every(function (string $docType) use ($uploadedDocuments, $hasApplicationLetter) {
                if ($docType === 'application_letter') {
                    if ($hasApplicationLetter) {
                        return true;
                    }
                    $appLetterDoc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                    return $appLetterDoc && !empty($appLetterDoc->storage_path) && $appLetterDoc->storage_path !== 'NOINPUT';
                }

                $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                return $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
            });
        }

        return $isComplete;
    }

    private function getDocumentTrackMismatchState(int $userId, ?string $vacancyType, ?string $vacancyId = null): array
    {
        $vacancyTrack = $this->normalizeTrack($vacancyType);
        $otherTrack = $vacancyTrack === 'COS' ? 'Plantilla' : 'COS';
        $trackCompleteness = $this->getTrackCompletenessByUser($userId);

        $hasMismatch = ($trackCompleteness[$otherTrack] ?? false) && !($trackCompleteness[$vacancyTrack] ?? false);

        return [
            'hasMismatch' => $hasMismatch,
            'submittedTrack' => $hasMismatch ? $otherTrack : null,
            'vacancyTrack' => $vacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $vacancyTrack,
                'vacancy_id' => $vacancyId,
            ]),
        ];
    }

    private function hasCompletedPdsForApply(int $userId): bool
    {
        $personalInfo = PersonalInformation::where('user_id', $userId)->first();
        $familyBackground = FamilyBackground::where('user_id', $userId)->first();
        $educationBackground = EducationalBackground::where('user_id', $userId)->first();
        $miscInfo = MiscInfos::where('user_id', $userId)->first();
        $hasWes = WorkExpSheet::where('user_id', $userId)->exists();

        if (!$personalInfo || !$familyBackground || !$educationBackground || !$miscInfo || !$hasWes) {
            return false;
        }

        return $this->hasMeaningfulValue($personalInfo->surname)
            && $this->hasMeaningfulValue($personalInfo->first_name)
            && $this->hasMeaningfulValue($personalInfo->mobile_no)
            && $this->hasMeaningfulValue($personalInfo->email_address)
            && $this->hasMeaningfulValue($familyBackground->mother_maiden_surname)
            && $this->hasMeaningfulValue($familyBackground->mother_maiden_first_name)
            && $this->hasMeaningfulValue($educationBackground->elem_school)
            && $this->hasMeaningfulValue($educationBackground->jhs_school)
            && $this->hasMeaningfulValue($miscInfo->govt_id_type)
            && $this->hasMeaningfulValue($miscInfo->govt_id_number);
    }

    public function hasCompletedPdsForApplicant(int $userId): bool
    {
        return $this->hasCompletedPdsForApply($userId);
    }

    private function hasMeaningfulValue($value): bool
    {
        if (is_array($value)) {
            return !empty(array_filter($value, fn($item) => $this->hasMeaningfulValue($item)));
        }

        $normalized = trim((string) $value);
        return $normalized !== '' && strtoupper($normalized) !== 'NOINPUT';
    }

    private function evaluateApplicantQualificationGateForVacancy(int $userId, JobVacancy $vacancy): array
    {
        $educationCheck = $this->evaluateEducationRequirementForApplicant($userId, $vacancy);
        $experienceCheck = $this->evaluateExperienceRequirementForApplicant($userId, $vacancy->qualification_experience ?? null);
        $trainingCheck = $this->evaluateTrainingRequirementForApplicant($userId, $vacancy->qualification_training ?? null);
        $eligibilityState = $this->evaluateApplicantEligibilityForVacancy($userId, $vacancy);

        $eligibilityRequired = !empty($eligibilityState['requiredEligibilities'] ?? []);
        $eligibilityCheck = [
            'required' => $eligibilityRequired,
            'met' => !$eligibilityRequired || (bool) ($eligibilityState['isEligible'] ?? false),
            'status' => !$eligibilityRequired
                ? 'na'
                : ((bool) ($eligibilityState['isEligible'] ?? false) ? 'yes' : 'no'),
            'requirement' => $eligibilityRequired
                ? implode(', ', (array) ($eligibilityState['requiredEligibilities'] ?? []))
                : null,
            'message' => $eligibilityState['message'] ?? null,
            'required_values' => array_values((array) ($eligibilityState['requiredEligibilities'] ?? [])),
            'applicant_values' => array_values((array) ($eligibilityState['applicantEligibilities'] ?? [])),
        ];

        $checks = [
            'education' => $educationCheck,
            'training' => $trainingCheck,
            'experience' => $experienceCheck,
            'eligibility' => $eligibilityCheck,
        ];

        $missingLabels = $this->collectMissingQualificationLabels($checks);

        $isQualified = empty($missingLabels);
        $message = null;
        if (!$isQualified) {
            $message = 'You are not yet qualified to apply for this position. '
                . 'Please review the missing requirements and update your PDS.';
        }

        return [
            'isQualified' => $isQualified,
            'message' => $message,
            'checks' => $checks,
        ];
    }

    public function evaluateQualificationGateForApplicant(int $userId, JobVacancy $vacancy): array
    {
        $result = $this->evaluateApplicantQualificationGateForVacancy($userId, $vacancy);
        $result['missing_labels'] = $this->collectMissingQualificationLabels((array) ($result['checks'] ?? []));
        return $result;
    }

    private function collectMissingQualificationLabels(array $checks): array
    {
        $missingLabels = [];
        foreach ($checks as $field => $check) {
            if (($check['required'] ?? false) && !($check['met'] ?? false)) {
                $label = ucfirst((string) $field);
                $requirement = trim((string) ($check['requirement'] ?? ''));
                $missingLabels[] = $requirement !== ''
                    ? "{$label} ({$requirement})"
                    : $label;
            }
        }

        return $missingLabels;
    }

    private function evaluateEducationRequirementForApplicant(int $userId, JobVacancy $vacancy): array
    {
        $profile = $this->buildApplicantEducationProfile($userId);
        $requirement = $this->normalizeQualificationRequirement($vacancy->qualification_education ?? null);
        if ($requirement === null) {
            return [
                'required' => false,
                'met' => true,
                'status' => 'na',
                'requirement' => null,
            ];
        }

        $compiledRule = $this->resolveCompiledEducationRuleForVacancy($vacancy, $requirement);
        $met = is_array($compiledRule) ? $this->evaluateCompiledEducationRule($profile, $compiledRule) : null;
        $usedFallback = false;
        if (!is_bool($met)) {
            $met = $this->evaluateLegacyEducationRequirementByText($profile, $requirement);
            $usedFallback = true;
        }
        $isAdvisoryOnly = is_array($compiledRule) && (bool) ($compiledRule['advisory_only'] ?? false) && !$usedFallback;
        if ($isAdvisoryOnly) {
            $met = true;
        }

        $ruleCode = is_array($compiledRule) ? (string) ($compiledRule['rule_code'] ?? '') : '';
        if ($ruleCode === '') {
            $ruleCode = $usedFallback ? 'legacy_text_fallback' : null;
        } elseif ($usedFallback) {
            $ruleCode = 'legacy_text_fallback';
        }
        $isRelevantBachelorRule = $ruleCode === 'bachelor_relevant_admin_review' && !$usedFallback;

        return [
            'required' => !$isAdvisoryOnly,
            'met' => $met,
            'status' => $isAdvisoryOnly ? 'na' : ($met ? 'yes' : 'no'),
            'requirement' => $requirement,
            'rule_code' => $ruleCode,
            'explanation' => $isRelevantBachelorRule
                ? 'This vacancy accepts any bachelor\'s degree for initial screening. HR will verify relevance to the position during review.'
                : ($isAdvisoryOnly
                    ? 'Education requirement uses relevant-field wording; final verification is for admin review.'
                    : ($usedFallback
                        ? 'Requirement text was ambiguous, so legacy text matching was used.'
                        : null)),
            'compiled_rule' => is_array($compiledRule) ? $compiledRule : null,
            'applicant_profile' => $profile,
        ];
    }

    private function evaluateExperienceRequirementForApplicant(int $userId, ?string $rawRequirement): array
    {
        $requirement = $this->normalizeQualificationRequirement($rawRequirement);
        if ($requirement === null) {
            return [
                'required' => false,
                'met' => true,
                'status' => 'na',
                'requirement' => null,
                'actual_months' => 0,
                'required_months' => null,
            ];
        }

        $workExperiences = WorkExperience::query()
            ->where('user_id', $userId)
            ->get();

        $totalMonths = $this->sumApplicantExperienceMonths($workExperiences);
        $requirementLower = strtolower($requirement);
        $topicKeywords = $this->resolveExperienceTopicKeywords($requirementLower);
        $requiresTopicMatch = !empty($topicKeywords);
        $matchedTopicMonths = $requiresTopicMatch
            ? $this->sumApplicantExperienceMonths($workExperiences, $topicKeywords)
            : 0;
        $hasTopicMatch = !$requiresTopicMatch || $matchedTopicMonths > 0;
        $requiredMonths = $this->parseQualificationRequirementMonths($requirement);
        if ($requiredMonths !== null && $requiresTopicMatch) {
            // Topic-specific experience duration: only matching records count.
            $met = $matchedTopicMonths >= $requiredMonths;
        } elseif ($requiredMonths !== null) {
            // Duration without explicit topic: use overall declared experience.
            $met = $totalMonths >= $requiredMonths;
        } elseif ($requiresTopicMatch) {
            $met = $hasTopicMatch;
        } else {
            $met = $workExperiences->isNotEmpty();
        }

        return [
            'required' => true,
            'met' => $met,
            'status' => $met ? 'yes' : 'no',
            'requirement' => $requirement,
            'actual_months' => $totalMonths,
            'required_months' => $requiredMonths,
            'matched_topic_months' => $requiresTopicMatch ? $matchedTopicMonths : null,
            'requires_topic_match' => $requiresTopicMatch,
            'topic_match_met' => $hasTopicMatch,
        ];
    }

    private function resolveExperienceTopicKeywords(string $requirementLower): ?array
    {
        if ($this->textContainsAny($requirementLower, [
            'management and supervision',
            'management',
            'managerial',
            'supervision',
            'supervisory',
        ])) {
            return [
                'management',
                'managerial',
                'supervision',
                'supervisory',
            ];
        }

        if ($this->textContainsAny($requirementLower, [
            'lgoo',
            'local governance',
            'governance operations',
            'community development',
            'strategic thinking',
            'planning',
        ])) {
            return [
                'lgoo',
                'local governance',
                'governance operations',
                'community development',
                'strategic thinking',
                'planning',
            ];
        }

        return null;
    }

    private function sumApplicantExperienceMonths($records, ?array $keywords = null): int
    {
        $totalMonths = 0;
        foreach ($records as $work) {
            if (!empty($keywords) && !$this->experienceRecordMatchesKeywords($work, $keywords)) {
                continue;
            }

            $fromRaw = trim((string) ($work->work_exp_from ?? ''));
            if ($fromRaw === '') {
                continue;
            }

            try {
                $from = Carbon::parse($fromRaw);
            } catch (\Throwable $e) {
                continue;
            }

            $toRaw = trim((string) ($work->work_exp_to ?? ''));
            if ($toRaw === '' || strtolower($toRaw) === 'present') {
                $to = Carbon::now();
            } else {
                try {
                    $to = Carbon::parse($toRaw);
                } catch (\Throwable $e) {
                    continue;
                }
            }

            if ($to->lessThan($from)) {
                continue;
            }

            $totalMonths += $from->diffInMonths($to) + 1;
        }

        return $totalMonths;
    }

    private function experienceRecordMatchesKeywords($work, array $keywords): bool
    {
        $haystack = strtolower(trim(implode(' ', [
            (string) ($work->work_exp_position ?? ''),
            (string) ($work->work_exp_department ?? ''),
            (string) ($work->work_exp_status ?? ''),
        ])));

        return $this->textContainsAny($haystack, $keywords);
    }

    private function evaluateTrainingRequirementForApplicant(int $userId, ?string $rawRequirement): array
    {
        $requirement = $this->normalizeQualificationRequirement($rawRequirement);
        if ($requirement === null) {
            return [
                'required' => false,
                'met' => true,
                'status' => 'na',
                'requirement' => null,
                'actual_hours' => 0,
                'required_hours' => null,
            ];
        }

        $records = LearningAndDevelopment::query()
            ->where('user_id', $userId)
            ->get();

        $totalHours = $this->sumApplicantTrainingHours($records);
        $requiredHours = $this->parseQualificationRequirementHours($requirement);
        $requirementLower = strtolower($requirement);
        $topicKeywords = $this->resolveTrainingTopicKeywords($requirementLower);
        $requiresTopicMatch = !empty($topicKeywords);
        $matchedTopicHours = $requiresTopicMatch
            ? $this->sumApplicantTrainingHours($records, $topicKeywords)
            : 0;
        $hasTopicMatch = !$requiresTopicMatch || $matchedTopicHours > 0;

        if ($requiredHours !== null && $requiresTopicMatch) {
            // Topic-specific hours requirement: only count hours from matching training records.
            $met = $matchedTopicHours >= $requiredHours;
        } elseif ($requiredHours !== null) {
            $met = $totalHours >= $requiredHours;
        } elseif ($requiresTopicMatch) {
            $met = $hasTopicMatch;
        } else {
            $met = $records->isNotEmpty();
        }

        return [
            'required' => true,
            'met' => $met,
            'status' => $met ? 'yes' : 'no',
            'requirement' => $requirement,
            'actual_hours' => $totalHours,
            'required_hours' => $requiredHours,
            'matched_topic_hours' => $requiresTopicMatch ? $matchedTopicHours : null,
            'requires_topic_match' => $requiresTopicMatch,
            'topic_match_met' => $hasTopicMatch,
        ];
    }

    private function resolveTrainingTopicKeywords(string $requirementLower): ?array
    {
        if ($this->textContainsAny($requirementLower, [
            'lgoo',
            'local governance',
            'governance operations',
            'community development',
            'strategic thinking',
        ])) {
            return [
                'lgoo',
                'local governance',
                'governance operations',
                'community development',
                'strategic thinking',
            ];
        }

        if ($this->textContainsAny($requirementLower, [
            'management and supervision',
            'management',
            'managerial',
            'supervision',
            'supervisory',
        ])) {
            return [
                'management',
                'managerial',
                'supervision',
                'supervisory',
            ];
        }

        return null;
    }

    private function sumApplicantTrainingHours($records, ?array $keywords = null): int
    {
        $hours = (float) $records->sum(function ($row) use ($keywords) {
            if (!empty($keywords) && !$this->trainingRecordMatchesKeywords($row, $keywords)) {
                return 0;
            }

            $value = $row->learning_hours ?? 0;
            return is_numeric($value) ? (float) $value : 0;
        });

        return (int) round($hours);
    }

    private function trainingRecordMatchesKeywords($row, array $keywords): bool
    {
        $haystack = strtolower(trim(implode(' ', [
            (string) ($row->learning_title ?? ''),
            (string) ($row->learning_type ?? ''),
            (string) ($row->learning_conducted ?? ''),
        ])));

        return $this->textContainsAny($haystack, $keywords);
    }

    private function buildApplicantEducationProfile(int $userId): array
    {
        $defaultProfile = [
            'hasElementaryOrHigher' => false,
            'hasSeniorHighOrHigher' => false,
            'hasHighSchoolOrHigher' => false,
            'hasCollegeEntryOrHigher' => false,
            'hasCollegeDegreeOrHigher' => false,
            'hasBachelorOrHigher' => false,
            'hasVocational' => false,
            'hasAtLeastTwoYearsCollege' => false,
            'collegeYearsCompleted' => 0,
            'estimatedCollegeUnits' => 0,
            'estimatedCollegeSemesters' => 0,
            'hasGrad' => false,
            'hasMasters' => false,
            'hasDoctorate' => false,
            'hasLawDegree' => false,
            'hasAnyEducation' => false,
            'educationKeywordHaystack' => '',
        ];

        $education = EducationalBackground::query()->where('user_id', $userId)->first();
        if (!$education) {
            return $defaultProfile;
        }

        $hasElementary = $this->hasMeaningfulValue($education->elem_school)
            || $this->hasMeaningfulValue($education->elem_year_graduated)
            || $this->hasMeaningfulValue($education->elem_earned);

        $secondaryBasic = strtolower(trim((string) ($education->jhs_basic ?? '')));
        $mentionsShsTrack = $this->textContainsAny($secondaryBasic, ['senior high', 'grade 12', 'shs']);
        $hasSecondaryRecord = $this->hasMeaningfulValue($education->jhs_school)
            || $this->hasMeaningfulValue($education->jhs_year_graduated)
            || $this->hasMeaningfulValue($education->jhs_earned)
            || $mentionsShsTrack;

        $hasShsCompleted = $mentionsShsTrack
            && (
                $this->hasMeaningfulValue($education->jhs_year_graduated)
                || $this->textContainsAny(strtolower((string) ($education->jhs_earned ?? '')), ['grade 12', 'completed', 'graduate', 'graduated'])
            );

        // Legacy SHS columns are retained for compatibility with previously saved data.
        if (!$hasShsCompleted) {
            $hasShsCompleted = ($this->hasMeaningfulValue($education->shs_year_graduated) || $this->textContainsAny(strtolower((string) ($education->shs_earned ?? '')), ['grade 12', 'completed', 'graduate', 'graduated']))
                && ($this->hasMeaningfulValue($education->shs_school) || $this->hasMeaningfulValue($education->shs_basic) || $this->hasMeaningfulValue($education->shs_earned));
        }

        $hasLegacyHighSchoolFromSecondary = $hasSecondaryRecord
            && !$mentionsShsTrack
            && $this->secondaryBasicIsLegacyHighSchool((string) ($education->jhs_basic ?? ''));
        $hasVocational = $this->hasMeaningfulValue($education->vocational);
        $hasCollege = $this->hasMeaningfulValue($education->college);

        // Treat graduate-level entries as valid even when users accidentally place them in College.
        $graduateKeywords = [
            'master',
            'masteral',
            "master's",
            'post graduate',
            'postgraduate',
            'doctoral',
            'doctorate',
            'doctor of philosophy',
            'phd',
            'ph.d',
            'llm',
            'm.a',
            'm.s',
            'msc',
            'mba',
            'mpa',
        ];
        $hasGrad = $this->hasMeaningfulValue($education->grad)
            || $this->educationEntriesContainKeywords($education->grad, $graduateKeywords)
            || $this->educationEntriesContainKeywords($education->college, $graduateKeywords);
        $hasMasters = $this->educationEntriesContainKeywords(
            [$education->grad, $education->college],
            ['master', 'masteral', "master's", 'mba', 'mpa', 'msc', 'm.s', 'm.a']
        );
        $hasDoctorate = $this->educationEntriesContainKeywords(
            [$education->grad, $education->college],
            ['doctorate', 'doctoral', 'doctor of philosophy', 'phd', 'ph.d']
        );
        $hasCollegeDegree = $this->hasCollegeDegree($education->college);
        $collegeYearsCompleted = $this->estimateHighestCollegeYearsCompleted($education->college);
        if ($hasGrad || $hasCollegeDegree) {
            // Graduate studies imply college completion.
            $collegeYearsCompleted = max($collegeYearsCompleted, 4);
        }
        $estimatedCollegeUnits = max(
            $collegeYearsCompleted * 36,
            $this->estimateHighestCollegeUnitsCompleted($education->college)
        );
        $estimatedCollegeSemesters = max(
            $collegeYearsCompleted * 2,
            $this->estimateHighestCollegeSemestersCompleted($education->college)
        );
        $hasAtLeastTwoYearsCollege = $collegeYearsCompleted >= 2;
        $hasLawDegree = $this->valueContainsAnyKeyword(
            [
                $education->college,
                $education->grad,
                $education->elem_earned,
                $education->jhs_earned,
                $education->jhs_basic,
                $education->shs_earned,
            ],
            ['bachelor of laws', 'llb', 'juris doctor', 'attorney', 'law']
        );

        $hasSeniorHighOrHigher = $hasShsCompleted || $hasVocational || $hasCollege || $hasGrad;
        $hasHighSchoolOrHigher = $hasLegacyHighSchoolFromSecondary || $hasSeniorHighOrHigher;
        $hasCollegeEntryOrHigher = $hasCollege || $hasGrad;
        $hasCollegeDegreeOrHigher = $hasCollegeDegree || $hasGrad;
        $hasBachelorOrHigher = $hasCollegeDegreeOrHigher || $hasLawDegree;
        $hasElementaryOrHigher = $hasElementary || $hasHighSchoolOrHigher;
        $hasAnyEducation = $hasElementary || $hasSecondaryRecord || $hasVocational || $hasCollege || $hasGrad;
        $educationKeywordHaystack = strtolower(trim(implode(' ', array_filter([
            $this->flattenEducationValueToText($education->college),
            $this->flattenEducationValueToText($education->grad),
            $this->flattenEducationValueToText($education->vocational),
            (string) ($education->elem_earned ?? ''),
            (string) ($education->jhs_earned ?? ''),
            (string) ($education->shs_earned ?? ''),
            (string) ($education->jhs_basic ?? ''),
            (string) ($education->shs_basic ?? ''),
        ]))));

        return [
            'hasElementaryOrHigher' => $hasElementaryOrHigher,
            'hasSeniorHighOrHigher' => $hasSeniorHighOrHigher,
            'hasHighSchoolOrHigher' => $hasHighSchoolOrHigher,
            'hasCollegeEntryOrHigher' => $hasCollegeEntryOrHigher,
            'hasCollegeDegreeOrHigher' => $hasCollegeDegreeOrHigher,
            'hasBachelorOrHigher' => $hasBachelorOrHigher,
            'hasVocational' => $hasVocational,
            'hasAtLeastTwoYearsCollege' => $hasAtLeastTwoYearsCollege,
            'collegeYearsCompleted' => $collegeYearsCompleted,
            'estimatedCollegeUnits' => $estimatedCollegeUnits,
            'estimatedCollegeSemesters' => $estimatedCollegeSemesters,
            'hasGrad' => $hasGrad,
            'hasMasters' => $hasMasters,
            'hasDoctorate' => $hasDoctorate,
            'hasLawDegree' => $hasLawDegree,
            'hasAnyEducation' => $hasAnyEducation,
            'educationKeywordHaystack' => $educationKeywordHaystack,
        ];
    }

    private function hasCollegeDegree($entries): bool
    {
        if (!is_array($entries)) {
            return false;
        }

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            if ($this->hasMeaningfulValue($entry['year_graduated'] ?? null)) {
                return true;
            }

            $earned = strtolower(trim((string) ($entry['earned'] ?? '')));
            if ($earned !== '' && $this->textContainsAny($earned, [
                'graduate',
                'degree',
                'baccalaureate',
                'bachelor',
            ])) {
                return true;
            }
        }

        return false;
    }

    private function educationEntriesContainKeywords($entries, array $keywords): bool
    {
        if (!is_array($entries)) {
            $text = strtolower(trim((string) $entries));
            return $text !== '' && $this->textContainsAny($text, $keywords);
        }

        foreach ($entries as $entry) {
            if (is_array($entry)) {
                $basic = strtolower(trim((string) ($entry['basic'] ?? '')));
                $earned = strtolower(trim((string) ($entry['earned'] ?? '')));
                $haystack = trim($basic . ' ' . $earned);
                if ($haystack !== '' && $this->textContainsAny($haystack, $keywords)) {
                    return true;
                }

                if ($this->educationEntriesContainKeywords($entry, $keywords)) {
                    return true;
                }

                continue;
            }

            $text = strtolower(trim((string) $entry));
            if ($text !== '' && $this->textContainsAny($text, $keywords)) {
                return true;
            }
        }

        return false;
    }

    private function secondaryBasicIsSeniorHigh(string $value): bool
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return false;
        }

        return str_contains($normalized, 'senior high') || str_contains($normalized, 'grade 12');
    }

    private function secondaryBasicIsLegacyHighSchool(string $value): bool
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            // Legacy rows may have empty basic text but were historically "High School".
            return true;
        }

        if ($this->secondaryBasicIsSeniorHigh($normalized)) {
            return false;
        }

        return str_contains($normalized, 'high school') && !str_contains($normalized, 'junior');
    }

    private function estimateHighestCollegeYearsCompleted($entries): int
    {
        if (!is_array($entries)) {
            return 0;
        }

        $maxYears = 0;
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $hasAnyEntryValue = $this->hasMeaningfulValue($entry['school'] ?? null)
                || $this->hasMeaningfulValue($entry['basic'] ?? null)
                || $this->hasMeaningfulValue($entry['from'] ?? null)
                || $this->hasMeaningfulValue($entry['to'] ?? null)
                || $this->hasMeaningfulValue($entry['earned'] ?? null)
                || $this->hasMeaningfulValue($entry['year_graduated'] ?? null);

            if (!$hasAnyEntryValue) {
                continue;
            }

            $yearsFromEarned = $this->extractYearsFromEducationLevelText((string) ($entry['earned'] ?? ''));
            $yearsFromDates = $this->estimateYearsFromEducationDateRange(
                (string) ($entry['from'] ?? ''),
                (string) ($entry['to'] ?? '')
            );

            $isGraduated = $this->hasMeaningfulValue($entry['year_graduated'] ?? null)
                || str_contains(strtolower((string) ($entry['earned'] ?? '')), 'graduate');

            $entryMax = max($yearsFromEarned, $yearsFromDates);
            if ($isGraduated) {
                $entryMax = max($entryMax, 4);
            }

            $maxYears = max($maxYears, $entryMax);
        }

        return $maxYears;
    }

    private function estimateHighestCollegeUnitsCompleted($entries): int
    {
        if (!is_array($entries)) {
            return 0;
        }

        $maxUnits = 0;
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $yearsFromDates = $this->estimateYearsFromEducationDateRange(
                (string) ($entry['from'] ?? ''),
                (string) ($entry['to'] ?? '')
            );
            $unitsFromYears = $yearsFromDates > 0 ? $yearsFromDates * 36 : 0;
            $unitsFromEarned = $this->extractUnitsFromEducationLevelText((string) ($entry['earned'] ?? ''));
            $maxUnits = max($maxUnits, $unitsFromYears, $unitsFromEarned);
        }

        return $maxUnits;
    }

    private function estimateHighestCollegeSemestersCompleted($entries): int
    {
        if (!is_array($entries)) {
            return 0;
        }

        $maxSemesters = 0;
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $yearsFromDates = $this->estimateYearsFromEducationDateRange(
                (string) ($entry['from'] ?? ''),
                (string) ($entry['to'] ?? '')
            );
            $semestersFromYears = $yearsFromDates > 0 ? $yearsFromDates * 2 : 0;
            $semestersFromEarned = $this->extractSemestersFromEducationLevelText((string) ($entry['earned'] ?? ''));
            $maxSemesters = max($maxSemesters, $semestersFromYears, $semestersFromEarned);
        }

        return $maxSemesters;
    }

    private function extractYearsFromEducationLevelText(string $value): int
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return 0;
        }

        if (preg_match('/\b([1-9]|10)(?:st|nd|rd|th)?\s*year\b/i', $normalized, $matches) === 1) {
            return (int) $matches[1];
        }

        $wordToYear = [
            'first' => 1,
            'second' => 2,
            'third' => 3,
            'fourth' => 4,
            'fifth' => 5,
        ];

        foreach ($wordToYear as $word => $year) {
            if (str_contains($normalized, $word . ' year')) {
                return $year;
            }
        }

        $units = $this->extractUnitsFromEducationLevelText($normalized);
        if ($units > 0) {
            return max(0, (int) floor($units / 36));
        }

        $semesters = $this->extractSemestersFromEducationLevelText($normalized);
        if ($semesters > 0) {
            return max(0, (int) floor($semesters / 2));
        }

        return 0;
    }

    private function extractUnitsFromEducationLevelText(string $value): int
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return 0;
        }

        if (preg_match('/\b(\d+(?:\.\d+)?)\s*units?\b/i', $normalized, $unitMatches) === 1) {
            $units = (float) $unitMatches[1];
            return $units > 0 ? (int) floor($units) : 0;
        }

        return 0;
    }

    private function extractSemestersFromEducationLevelText(string $value): int
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return 0;
        }

        if (preg_match('/\b(\d+)\s*semesters?\b/i', $normalized, $semesterMatches) === 1) {
            $semesters = (int) $semesterMatches[1];
            return $semesters > 0 ? $semesters : 0;
        }

        return 0;
    }

    private function estimateYearsFromEducationDateRange(string $from, string $to): int
    {
        $fromDate = $this->parseEducationDateValue($from);
        if (!$fromDate) {
            return 0;
        }

        $toDate = $this->parseEducationDateValue($to) ?? Carbon::now();
        if ($toDate->lessThan($fromDate)) {
            return 0;
        }

        $months = $fromDate->diffInMonths($toDate) + 1;
        return (int) max(1, (int) ceil($months / 12));
    }

    private function parseEducationDateValue(string $value): ?Carbon
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        $formats = ['Y-m-d', 'd-m-Y', 'm/d/Y', 'Y/m/d'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $normalized);
            } catch (\Throwable $e) {
                // Try the next supported format.
            }
        }

        try {
            return Carbon::parse($normalized);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function valueContainsAnyKeyword($value, array $keywords): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->valueContainsAnyKeyword($item, $keywords)) {
                    return true;
                }
            }
            return false;
        }

        $text = strtolower(trim((string) $value));
        if ($text === '') {
            return false;
        }

        return $this->textContainsAny($text, $keywords);
    }

    private function flattenEducationValueToText($value): string
    {
        if (is_array($value)) {
            $chunks = [];
            foreach ($value as $item) {
                $chunk = $this->flattenEducationValueToText($item);
                if ($chunk !== '') {
                    $chunks[] = $chunk;
                }
            }
            return trim(implode(' ', $chunks));
        }

        return trim((string) $value);
    }

    private function textContainsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, strtolower((string) $keyword))) {
                return true;
            }
        }

        return false;
    }

    private function normalizeQualificationRequirement(?string $value): ?string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        $lower = strtolower($normalized);

        if (in_array($lower, ['na', 'n/a', 'none', 'not applicable', 'nil', '-'], true)) {
            return null;
        }

        if (str_contains($lower, 'none required') || str_contains($lower, 'not required')) {
            return null;
        }

        return $normalized;
    }

    private function parseQualificationRequirementMonths(string $value): ?int
    {
        $lower = strtolower($value);
        if (preg_match('/(\d+(?:\.\d+)?)/', $lower, $matches) === 1) {
            $amount = (float) $matches[1];
            if (str_contains($lower, 'month')) {
                return (int) round($amount);
            }
            if (str_contains($lower, 'year') || str_contains($lower, 'yr')) {
                return (int) round($amount * 12);
            }
        }

        return null;
    }

    private function parseQualificationRequirementHours(string $value): ?int
    {
        $lower = strtolower($value);
        if (preg_match('/\bhours?\b|\bhrs?\b/', $lower) !== 1) {
            return null;
        }

        if (preg_match('/(\d+(?:\.\d+)?)/', $lower, $matches) === 1) {
            return (int) round((float) $matches[1]);
        }

        return null;
    }

    private function evaluateApplicantEligibilityForVacancy(int $userId, JobVacancy $vacancy): array
    {
        $requiredEligibilities = $this->extractVacancyEligibilityNames((string) ($vacancy->qualification_eligibility ?? ''));

        // Vacancy has no explicit eligibility requirement.
        if (empty($requiredEligibilities)) {
            return [
                'isEligible' => true,
                'message' => null,
                'requiredEligibilities' => [],
                'applicantEligibilities' => [],
            ];
        }

        $applicantEligibilities = $this->extractApplicantEligibilityNames($userId);

        if (empty($applicantEligibilities)) {
            return [
                'isEligible' => false,
                'message' => 'This vacancy requires civil service eligibility (' . implode(', ', $requiredEligibilities) . '). Please update your PDS Civil Service Eligibility (C2) before applying.',
                'requiredEligibilities' => $requiredEligibilities,
                'applicantEligibilities' => [],
            ];
        }

        $requiredByKey = [];
        foreach ($requiredEligibilities as $name) {
            $key = $this->normalizeEligibilityKey($name);
            if ($key !== '' && !array_key_exists($key, $requiredByKey)) {
                $requiredByKey[$key] = $name;
            }
        }

        $applicantByKey = [];
        foreach ($applicantEligibilities as $name) {
            $key = $this->normalizeEligibilityKey($name);
            if ($key !== '' && !array_key_exists($key, $applicantByKey)) {
                $applicantByKey[$key] = $name;
            }
        }

        $matchedKeys = array_intersect(array_keys($requiredByKey), array_keys($applicantByKey));
        if (empty($matchedKeys)) {
            foreach ($requiredByKey as $requiredKey => $requiredName) {
                foreach ($applicantByKey as $applicantName) {
                    if ($this->eligibilityNamesMatch((string) $requiredName, (string) $applicantName)) {
                        $matchedKeys[] = $requiredKey;
                        break;
                    }
                }
            }
            $matchedKeys = array_values(array_unique($matchedKeys));
        }

        if (!empty($matchedKeys)) {
            return [
                'isEligible' => true,
                'message' => null,
                'requiredEligibilities' => array_values($requiredByKey),
                'applicantEligibilities' => array_values($applicantByKey),
            ];
        }

        return [
            'isEligible' => false,
            'message' => 'Your declared civil service eligibility does not match this vacancy requirement. Required: '
                . implode(', ', array_values($requiredByKey))
                . '. Your current entries: '
                . implode(', ', array_values($applicantByKey))
                . '.',
            'requiredEligibilities' => array_values($requiredByKey),
            'applicantEligibilities' => array_values($applicantByKey),
        ];
    }

    private function extractApplicantEligibilityNames(int $userId): array
    {
        $records = CivilServiceEligibility::query()
            ->where('user_id', $userId)
            ->pluck('cs_eligibility_career')
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter()
            ->values()
            ->all();

        return $this->uniqueEligibilityNames($records);
    }

    private function extractVacancyEligibilityNames(string $rawEligibility): array
    {
        $items = $this->extractVacancyEligibilityItems($rawEligibility);
        if (empty($items)) {
            return [];
        }

        $names = array_map(static function (array $item) {
            return trim((string) ($item['name'] ?? ''));
        }, $items);

        return $this->uniqueEligibilityNames($names);
    }

    private function extractVacancyEligibilityItems(string $rawEligibility): array
    {
        $normalizedRequirement = $this->normalizeQualificationRequirement($rawEligibility);
        if ($normalizedRequirement === null) {
            return [];
        }

        $rawEligibility = $normalizedRequirement;
        $items = [];
        $parsed = json_decode($rawEligibility, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                $source = array_key_exists('name', $parsed) ? [$parsed] : $parsed;
                foreach ($source as $entry) {
                    if (is_string($entry)) {
                        $name = $this->canonicalEligibilityLabelFromName((string) $entry);
                        if ($name === '') {
                            continue;
                        }
                        $items[] = [
                            'name' => $name,
                        'legal_basis' => '',
                        'level' => '',
                    ];
                    continue;
                }

                if (!is_array($entry)) {
                    continue;
                }

                $name = $this->canonicalEligibilityLabelFromName((string) ($entry['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $items[] = [
                    'name' => $name,
                    'legal_basis' => trim((string) ($entry['legalBasis'] ?? $entry['legal_basis'] ?? '')),
                    'level' => trim((string) ($entry['level'] ?? '')),
                ];
            }

            $deduped = [];
            $seen = [];
            foreach ($items as $item) {
                $key = $this->normalizeEligibilityKey((string) ($item['name'] ?? ''));
                if ($key === '' || array_key_exists($key, $seen)) {
                    continue;
                }
                $seen[$key] = true;
                $deduped[] = $item;
            }

            if (!empty($deduped)) {
                return $deduped;
            }
        }

        $legacyTokens = preg_split('/[\r\n;,]+/', $rawEligibility) ?: [];
        $legacyTokens = array_map(static function ($token) {
            return trim((string) $token);
        }, $legacyTokens);
        $legacyTokens = $this->uniqueEligibilityNames($legacyTokens);

        return array_map(static function ($name) {
            return [
                'name' => $name,
                'legal_basis' => '',
                'level' => '',
            ];
        }, $legacyTokens);
    }

    private function formatVacancyEligibilityDisplay(array $items): string
    {
        $lines = [];

        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $parts = [];
            $legalBasis = trim((string) ($item['legal_basis'] ?? ''));
            $level = trim((string) ($item['level'] ?? ''));

            if ($legalBasis !== '') {
                $parts[] = 'Legal Basis: ' . $legalBasis;
            }
            if ($level !== '') {
                $parts[] = 'Level: ' . $level;
            }

            $lines[] = empty($parts)
                ? $name
                : $name . ' (' . implode(' | ', $parts) . ')';
        }

        return empty($lines) ? 'Not specified' : implode("\n", $lines);
    }

    private function uniqueEligibilityNames(array $names): array
    {
        $deduped = [];
        $seen = [];

        foreach ($names as $name) {
            $label = $this->canonicalEligibilityLabelFromName((string) $name);
            $key = $this->normalizeEligibilityKey($label);
            if ($label === '' || $key === '' || array_key_exists($key, $seen)) {
                continue;
            }

            $seen[$key] = true;
            $deduped[] = $label;
        }

        return $deduped;
    }

    private function canonicalEligibilityLabelFromName(string $value): string
    {
        $label = trim($value);
        if ($label === '') {
            return '';
        }

        $group = $this->canonicalEligibilityGroup($this->normalizeEligibilityKey($label));
        if ($group !== '' && array_key_exists($group, self::ELIGIBILITY_CANONICAL_LABELS)) {
            return self::ELIGIBILITY_CANONICAL_LABELS[$group];
        }

        return $label;
    }

    private function normalizeEligibilityKey(?string $value): string
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return '';
        }

        return preg_replace('/[^a-z0-9]+/', '', $normalized) ?? '';
    }

    private function eligibilityNamesMatch(string $requiredName, string $applicantName): bool
    {
        $requiredKey = $this->normalizeEligibilityKey($requiredName);
        $applicantKey = $this->normalizeEligibilityKey($applicantName);

        if ($requiredKey === '' || $applicantKey === '') {
            return false;
        }

        if ($requiredKey === $applicantKey) {
            return true;
        }

        $requiredGroup = $this->canonicalEligibilityGroup($requiredKey);
        $applicantGroup = $this->canonicalEligibilityGroup($applicantKey);
        if ($requiredGroup !== '' && $requiredGroup === $applicantGroup) {
            return true;
        }
        if ($requiredGroup !== '' && $applicantGroup !== '' && $this->eligibilityGroupSatisfiesRequirement($requiredGroup, $applicantGroup)) {
            return true;
        }

        // Allow minor wording differences for manually entered "Others" values.
        $minLen = min(strlen($requiredKey), strlen($applicantKey));
        if ($minLen >= 8 && (str_contains($requiredKey, $applicantKey) || str_contains($applicantKey, $requiredKey))) {
            return true;
        }

        return false;
    }

    private function eligibilityGroupSatisfiesRequirement(string $requiredGroup, string $applicantGroup): bool
    {
        if ($requiredGroup === $applicantGroup) {
            return true;
        }

        // Hierarchy: CSC Professional is higher than CSC Subprofessional.
        if ($requiredGroup === 'csc_subprofessional' && $applicantGroup === 'csc_professional') {
            return true;
        }

        return false;
    }

    private function canonicalEligibilityGroup(string $normalizedKey): string
    {
        if ($normalizedKey === '') {
            return '';
        }

        $contains = static function (string $needle) use ($normalizedKey): bool {
            return str_contains($normalizedKey, $needle);
        };

        if ($contains('ra1080') || ($contains('bar') && $contains('board'))) {
            return 'bar_board';
        }
        if ($contains('subprofessional') || $contains('subprof')) {
            return 'csc_subprofessional';
        }
        $isCsFamily = str_starts_with($normalizedKey, 'csc')
            || str_starts_with($normalizedKey, 'cse')
            || str_starts_with($normalizedKey, 'cs')
            || $contains('civilservice')
            || $contains('careerservice');
        if ($isCsFamily && ($contains('professional') || $contains('prof'))) {
            return 'csc_professional';
        }
        if ($contains('foreign') && $contains('honor') && $contains('graduate')) {
            return 'foreign_honor_graduate';
        }
        if ($contains('honor') && $contains('graduate')) {
            return 'honor_graduate';
        }
        if ($contains('barangay') && $contains('health') && $contains('worker')) {
            return 'barangay_health_worker';
        }
        if ($contains('barangay') && $contains('nutrition') && $contains('scholar')) {
            return 'barangay_nutrition_scholar';
        }
        if ($contains('barangay') && $contains('official')) {
            return 'barangay_official';
        }
        if ($contains('sanggunian') && $contains('member')) {
            return 'sanggunian_member';
        }
        if ($contains('skills') && ($contains('categoryii') || $contains('category2'))) {
            return 'skills_category_ii';
        }
        if ($contains('electronic') && $contains('dataprocessing') && $contains('specialist')) {
            return 'edp_specialist';
        }
        if ($contains('scientific') && $contains('technological') && $contains('specialist')) {
            return 'scientific_technological_specialist';
        }

        return '';
    }

    public function calculatePdsProgress($userId)
    {
        $userId = (int) $userId;

        // Determine required docs from tracks the user has applied for.
        $applicationTracks = Applications::where('user_id', $userId)
            ->with('vacancy:vacancy_id,vacancy_type')
            ->get()
            ->map(fn($app) => $this->normalizeTrack($app->vacancy?->vacancy_type))
            ->filter()
            ->unique()
            ->values();

        if ($applicationTracks->isEmpty()) {
            $applicationTracks = collect(['Plantilla']);
        }

        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        $requiredDocumentIds = $applicationTracks
            ->flatMap(fn($track) => $requiredDocsByTrack[$track] ?? [])
            ->unique()
            ->values();

        $totalRequiredDocs = $requiredDocumentIds->count();
        if ($totalRequiredDocs === 0) {
            return 0;
        }

        $uploadedDocuments = UploadedDocument::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get()
            ->unique('document_type')
            ->keyBy('document_type');

        $hasApplicationLetterInApplications = Applications::where('user_id', $userId)
            ->whereNotNull('file_storage_path')
            ->exists();

        $completedRequiredDocs = $requiredDocumentIds->filter(function (string $docType) use ($uploadedDocuments, $hasApplicationLetterInApplications) {
            if ($docType === 'application_letter') {
                if ($hasApplicationLetterInApplications) {
                    return true;
                }

                $applicationLetterDoc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                return $applicationLetterDoc && !empty($applicationLetterDoc->storage_path) && $applicationLetterDoc->storage_path !== 'NOINPUT';
            }

            $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
            return $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
        })->count();

        return (int) round(($completedRequiredDocs / $totalRequiredDocs) * 100);
    }

    private function hasJobVacancyCscFormPathColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        try {
            $hasColumn = Schema::hasColumn('job_vacancies', 'csc_form_path');
        } catch (\Throwable $e) {
            $hasColumn = false;
            Log::warning('Unable to detect job_vacancies.csc_form_path column.', [
                'error' => $e->getMessage(),
            ]);
        }

        return $hasColumn;
    }

    private function hasJobVacancyLastModifiedAtColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        try {
            $hasColumn = Schema::hasColumn('job_vacancies', 'last_modified_at');
        } catch (\Throwable $e) {
            $hasColumn = false;
            Log::warning('Unable to detect job_vacancies.last_modified_at column.', [
                'error' => $e->getMessage(),
            ]);
        }

        return $hasColumn;
    }

    public function sortMyApplications(Request $request)
    {
        $applications = $this->buildMyApplicationsQuery($request)->get();
        $hasActiveFilters = $this->requestHasMyApplicationFilters($request);

        return view('partials.application_list_container', [
            'applications' => $applications,
            'hasActiveFilters' => $hasActiveFilters,
        ])->render();
    }

    private function buildMyApplicationsQuery(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $place = trim((string) $request->query('place', ''));
        $vacancyType = trim((string) $request->query('vacancy_type', ''));
        $status = trim((string) $request->query('status', ''));
        $sortOrder = strtolower(trim((string) $request->query('sort_order', 'latest')));

        $query = Applications::query()
            ->where('user_id', Auth::id())
            ->with('vacancy');

        if ($search !== '') {
            $query->where(function ($applicationQuery) use ($search) {
                $applicationQuery
                    ->where('vacancy_id', 'like', '%' . $search . '%')
                    ->orWhereHas('vacancy', function ($vacancyQuery) use ($search) {
                        $vacancyQuery->where('position_title', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($place !== '') {
            $query->whereHas('vacancy', function ($vacancyQuery) use ($place) {
                $vacancyQuery->where('place_of_assignment', $place);
            });
        }

        if ($vacancyType !== '') {
            $query->whereHas('vacancy', function ($vacancyQuery) use ($vacancyType) {
                $vacancyQuery->whereRaw("LOWER(TRIM(COALESCE(vacancy_type, ''))) = ?", [strtolower($vacancyType)]);
            });
        }

        if ($status !== '') {
            $query->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", [strtolower($status)]);
        }

        $query->orderByRaw("CASE WHEN LOWER(TRIM(COALESCE(status, ''))) = 'not qualified' THEN 1 ELSE 0 END");
        $query->orderBy('created_at', $sortOrder === 'oldest' ? 'asc' : 'desc');

        return $query;
    }

    private function getMyApplicationFilterOptions(): array
    {
        $userId = Auth::id();

        $statuses = Applications::query()
            ->where('user_id', $userId)
            ->whereNotNull('status')
            ->pluck('status')
            ->map(fn($status) => trim((string) $status))
            ->filter()
            ->unique(fn($status) => strtolower($status))
            ->sortBy(fn($status) => strtolower($status))
            ->values();

        return [
            'vacancyTypes' => collect(['COS', 'Plantilla']),
            'statuses' => $statuses,
        ];
    }

    private function requestHasMyApplicationFilters(Request $request): bool
    {
        foreach (['search', 'place', 'vacancy_type', 'status'] as $key) {
            if (trim((string) $request->query($key, '')) !== '') {
                return true;
            }
        }

        return false;
    }
}
