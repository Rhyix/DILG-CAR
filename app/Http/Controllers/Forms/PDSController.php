<?php

namespace App\Http\Controllers\Forms;

use App\Enums\ApplicationStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models;
use App\Models\MiscInfos;

// Models
use App\Models\Applications;
use App\Models\DocumentGalleryItem;
use Illuminate\Http\Request;
use App\Models\VoluntaryWork;
use App\Models\WorkExperience;
use App\Models\OtherInformation;
use App\Models\UploadedDocument;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LearningAndDevelopment;
use Illuminate\Support\Facades\DB;
use App\Models\CivilServiceEligibility;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use App\Services\ApplicationStatusTransitionService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PDSController extends Controller
{
    private const SEPARATOR = '/|/';
    private const PDF_MIME_TYPES = [
        'application/pdf',
        'application/x-pdf',
    ];
    private const IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
    ];
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

    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            $this->syncPdsSessionOwner($request);
            return $next($request);
        });
    }

    private function syncPdsSessionOwner(Request $request): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();
        $ownerKey = implode('|', [
            'uid:' . (string) $user->id,
            'email:' . (string) ($user->email ?? ''),
            'created:' . (string) optional($user->created_at)->timestamp,
        ]);

        $sessionOwner = (string) $request->session()->get('pds_form_owner', '');
        if ($sessionOwner !== $ownerKey) {
            $request->session()->forget([
                'form',
                'data_learning',
                'data_voluntary',
                'data_otherInfo',
                'vacancy_doc_uploads',
            ]);
        }

        $request->session()->put('pds_form_owner', $ownerKey);
    }

    private function normalizeDateForForm(?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
            return $value;
        }

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d', $value)->format('d-m-Y');
            }

            if (preg_match('/^\d{4}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m', $value)->format('01-m-Y');
            }
        } catch (\Throwable $e) {
            return $value;
        }

        return $value;
    }

    private function normalizeDateForDatabase(?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }

        try {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
                return Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
            }
        } catch (\Throwable $e) {
            return $value;
        }

        return $value;
    }

    private function normalizeTelephoneInput(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        if ($value === null || $value === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === null || $digits === '') {
            return $value;
        }

        // Convert international PH prefixes to local 0-prefixed format.
        if (str_starts_with($digits, '63') && strlen($digits) >= 11) {
            $digits = '0' . substr($digits, 2);
        }

        return $digits;
    }

    private function normalizeMobileInput(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        if ($value === null || $value === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === null || $digits === '') {
            return $value;
        }

        // Accept +63 9XX XXX XXXX and normalize to 09XXXXXXXXX.
        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            $digits = '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '9') && strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        return $digits;
    }

    /**
     * Updates the C1 session data based on the database .If there is no data on the database,
     * the function should return an empty array.
     * @return array|null
     */
    private function c1GetFormFromDB()
    {

        $c1_full_info = [];
        $current_user = Auth::user();
        $user_personal_info = $current_user->personalInformation?->attributesToArray();
        if ($user_personal_info != null) {
            $user_personal_info['date_of_birth'] = $this->normalizeDateForForm($user_personal_info['date_of_birth'] ?? null);

            [
                $user_personal_info['res_house_no'],
                $user_personal_info['res_street'],
                $user_personal_info['res_sub_vil'],
                $user_personal_info['res_brgy'],
                $user_personal_info['res_city'],
                $user_personal_info['res_province'],
                $user_personal_info['res_zipcode']
            ] = explode(self::SEPARATOR, $user_personal_info['residential_address']);

            $user_personal_info['res_house_no'] = ($user_personal_info['res_house_no'] != '{*}') ? $user_personal_info['res_house_no'] : null;
            $user_personal_info['res_street'] = ($user_personal_info['res_street'] != '{*}') ? $user_personal_info['res_street'] : null;
            $user_personal_info['res_sub_vil'] = ($user_personal_info['res_sub_vil'] != '{*}') ? $user_personal_info['res_sub_vil'] : null;
            $user_personal_info['res_brgy'] = ($user_personal_info['res_brgy'] != '{*}') ? $user_personal_info['res_brgy'] : null;
            $user_personal_info['res_city'] = ($user_personal_info['res_city'] != '{*}') ? $user_personal_info['res_city'] : null;
            $user_personal_info['res_province'] = ($user_personal_info['res_province'] != '{*}') ? $user_personal_info['res_province'] : null;
            $user_personal_info['res_zipcode'] = ($user_personal_info['res_zipcode'] != '{*}') ? $user_personal_info['res_zipcode'] : null;

            [
                $user_personal_info['per_house_no'],
                $user_personal_info['per_street'],
                $user_personal_info['per_sub_vil'],
                $user_personal_info['per_brgy'],
                $user_personal_info['per_city'],
                $user_personal_info['per_province'],
                $user_personal_info['per_zipcode'],
            ] = explode(self::SEPARATOR, $user_personal_info['permanent_address']);

            $user_personal_info['per_house_no'] = ($user_personal_info['per_house_no'] != '{*}') ? $user_personal_info['per_house_no'] : null;
            $user_personal_info['per_street'] = ($user_personal_info['per_street'] != '{*}') ? $user_personal_info['per_street'] : null;
            $user_personal_info['per_sub_vil'] = ($user_personal_info['per_sub_vil'] != '{*}') ? $user_personal_info['per_sub_vil'] : null;
            $user_personal_info['per_brgy'] = ($user_personal_info['per_brgy'] != '{*}') ? $user_personal_info['per_brgy'] : null;
            $user_personal_info['per_city'] = ($user_personal_info['per_city'] != '{*}') ? $user_personal_info['per_city'] : null;
            $user_personal_info['per_province'] = ($user_personal_info['per_province'] != '{*}') ? $user_personal_info['per_province'] : null;
            $user_personal_info['per_zipcode'] = ($user_personal_info['per_zipcode'] != '{*}') ? $user_personal_info['per_zipcode'] : null;

            $c1_full_info = array_merge($c1_full_info, $user_personal_info);
        }

        $user_family_bg = $current_user->familyBackground?->attributesToArray();
        if ($user_family_bg != null) {

            $c1_full_info['children'] = $user_family_bg['children_info'];
            $c1_full_info = array_merge($c1_full_info, $user_family_bg);
        }

        $user_educational_bg = $current_user->educationalBackground?->attributesToArray();
        if ($user_educational_bg != null) {
            foreach (['elem_from', 'elem_to', 'jhs_from', 'jhs_to'] as $dateField) {
                $user_educational_bg[$dateField] = $this->normalizeDateForForm($user_educational_bg[$dateField] ?? null);
            }

            foreach (['vocational', 'college', 'grad'] as $_key) {
                $c1_full_info[$_key] = $user_educational_bg[$_key];
            }
            $c1_full_info = array_merge($c1_full_info, $user_educational_bg);
        }

        // All 'NOINPUT' fields should be displayed as an empty string.
        if ($c1_full_info != null) {

            foreach ($c1_full_info as $key => $value) {
                if ($c1_full_info[$key] == 'NOINPUT') {
                    $c1_full_info[$key] = null;
                }
            }
        }
        return $c1_full_info;
    }


    /**
     * Display C1 page with all session data.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function c1DisplayForm()
    {

        // If form in session already exists then no need to retireve data from the database.
        if (!session()->has('form.c1')) {
            session(['form.c1' => $this->c1GetFormFromDB()]);
        }
        $vocational_schools = session('form.c1.vocational', []);
        $college_schools = session('form.c1.college', []);
        $grad_schools = session('form.c1.grad', []);
        /*
                activity()
                    ->causedBy(Auth::user())
                    ->log('Viewed C1 form.');
        */
        // dd($vocational_schools);
        return view('pds.pds', compact('vocational_schools', 'college_schools', 'grad_schools'));
    }

    public function importC1Excel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pds_excel' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please upload a valid Excel file (.xlsx or .xls) up to 10MB.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payload = $this->extractC1DataFromExcel($request->file('pds_excel')->getRealPath());

            $existingC1 = session('form.c1', []);
            if (!is_array($existingC1)) {
                $existingC1 = [];
            }

            session([
                'form.c1' => array_merge(
                    $existingC1,
                    $payload['c1']['fields'],
                    [
                        'children' => $payload['c1']['children'],
                        'vocational' => $payload['c1']['vocational'],
                        'college' => $payload['c1']['college'],
                        'grad' => $payload['c1']['grad'],
                    ]
                ),
                'form.c2' => $payload['c2'],
                'data_learning' => $payload['c3']['data_learning'],
                'data_voluntary' => $payload['c3']['data_voluntary'],
                'data_otherInfo' => $payload['c3']['data_otherInfo'],
                'form.c4' => $payload['c4'],
            ]);

            return response()->json([
                'message' => 'Excel file imported for C1-C4. Please review all fields before proceeding.',
                'data' => $payload['c1'],
                'warnings' => $payload['warnings'],
                'missing_report' => $payload['missing_report'],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to import C1 Excel.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Unable to process the uploaded Excel file. Please try again with the official template.',
            ], 500);
        }
    }


    /**
     * Update the C1 session data based on the input fields.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\RedirectResponse
     */
    public function c1UpdateFormSession(Request $request, $go_to)
    {
        //dd($request->all());
        $request->merge([
            'telephone_no' => $this->normalizeTelephoneInput($request->input('telephone_no')),
            'mobile_no' => $this->normalizeMobileInput($request->input('mobile_no')),
        ]);

        // get key-value pairs only for fields that need validation.
        $c1_form_data_valid = $request->validate([
            'surname' => 'required|max:255|string',
            'first_name' => 'required|max:255|string',
            'middle_name' => 'nullable|max:255|string',
            'name_extension' => 'nullable|max:255|string',
            'civil_status' => 'required|string|in:single,married,widowed,separated,other',
            'date_of_birth' => 'required|date_format:d-m-Y',
            'place_of_birth' => 'required|max:255|string',
            'citizenship' => 'required|max:255|in:Filipino,Dual Citizenship',
            'sex' => 'required|in:male,female',
            'blood_type' => 'required|max:255|string',
            'telephone_no' => 'nullable|regex:/^0\d{9,10}$/', // example: 0281234567, 0322123456
            'mobile_no' => 'required|regex:/^09\d{9}$/', // example: +639171234567
            'email_address' => 'required|email:rfc',
            'height' => 'required|numeric|max:999',
            'weight' => 'required|numeric|max:999',
            'res_zipcode' => 'nullable|string|max:4',
            'per_zipcode' => 'nullable|string|max:4',
            'elem_from' => 'required|date_format:d-m-Y',
            'elem_to' => 'required|date_format:d-m-Y',
            'jhs_from' => 'required|date_format:d-m-Y',
            'jhs_to' => 'required|date_format:d-m-Y',

        ], [
            'date_of_birth.date_format' => 'The date of birth field must match the format dd-mm-yyyy.',
            'elem_from.date_format' => 'The elem from field must match the format dd-mm-yyyy.',
            'elem_to.date_format' => 'The elem to field must match the format dd-mm-yyyy.',
            'jhs_from.date_format' => 'The jhs from field must match the format dd-mm-yyyy.',
            'jhs_to.date_format' => 'The jhs to field must match the format dd-mm-yyyy.',
        ]);

        foreach (['date_of_birth', 'elem_from', 'elem_to', 'jhs_from', 'jhs_to'] as $dateField) {
            $c1_form_data_valid[$dateField] = $this->normalizeDateForForm($c1_form_data_valid[$dateField] ?? null);
        }

        // get all key-value pairs for non validated fields.
        $c1_form_data = $request->except([
            '_token',
            'surname',
            'first_name',
            'civil_status',
            'date_of_birth',
            'place_of_birth',
            'citizenship',
            'sex',
            'telephone_no',
            'mobile_no',
            'email_address',
            'height',
            'weight',
            'blood_type',
            'res_zipcode',
            'per_zipcode',
            'elem_from',
            'elem_to',
            'jhs_from',
            'jhs_to',

        ]);

        // join all request data
        $c1_form_data = array_merge($c1_form_data_valid, $c1_form_data);

        // check if there is data for children
        if (!$request->has('children')) {
            $c1_form_data['children'] = null;
        }

        session(['form.c1' => $c1_form_data]);

        // START DB SAVING LOGIC
        $c1_form_data_db = $c1_form_data;
        foreach (['elem_from', 'elem_to', 'jhs_from', 'jhs_to'] as $dateField) {
            $c1_form_data_db[$dateField] = $this->normalizeDateForDatabase($c1_form_data_db[$dateField] ?? null);
        }
        $c1_form_data_db = array_merge([
            'surname' => '',
            'first_name' => '',
            'middle_name' => '',
            'name_extension' => '',
            'sex' => '',
            'civil_status' => '',
            'date_of_birth' => '',
            'place_of_birth' => '',
            'height' => '',
            'weight' => '',
            'blood_type' => '',
            'philhealth_no' => '',
            'tin_no' => '',
            'agency_employee_no' => '',
            'gsis_id_no' => '',
            'pagibig_id_no' => '',
            'sss_id_no' => '',
            'citizenship' => '',
            'telephone_no' => '',
            'mobile_no' => '',
            'email_address' => '',
            'res_house_no' => '',
            'res_street' => '',
            'res_sub_vil' => '',
            'res_brgy' => '',
            'res_city' => '',
            'res_province' => '',
            'res_zipcode' => '',
            'per_house_no' => '',
            'per_street' => '',
            'per_sub_vil' => '',
            'per_brgy' => '',
            'per_city' => '',
            'per_province' => '',
            'per_zipcode' => '',
            'elem_from' => '',
            'elem_to' => '',
            'elem_school' => '',
            'elem_academic_honors' => '',
            'elem_basic' => '',
            'elem_earned' => '',
            'elem_year_graduated' => '',
            'jhs_from' => '',
            'jhs_to' => '',
            'jhs_school' => '',
            'jhs_academic_honors' => '',
            'jhs_basic' => '',
            'jhs_earned' => '',
            'jhs_year_graduated' => '',
        ], $c1_form_data_db);

        // Format residential address
        $house_no_t = ($c1_form_data_db['res_house_no'] != '') ? $c1_form_data_db['res_house_no'] : '{*}';
        $street_t = ($c1_form_data_db['res_street'] != '') ? $c1_form_data_db['res_street'] : '{*}';
        $sub_vil_t = ($c1_form_data_db['res_sub_vil'] != '') ? $c1_form_data_db['res_sub_vil'] : '{*}';
        $brgy_t = ($c1_form_data_db['res_brgy'] != '') ? $c1_form_data_db['res_brgy'] : '{*}';
        $city_t = ($c1_form_data_db['res_city'] != '') ? $c1_form_data_db['res_city'] : '{*}';
        $province_t = ($c1_form_data_db['res_province'] != '') ? $c1_form_data_db['res_province'] : '{*}';
        $zipcode_t = ($c1_form_data_db['res_zipcode'] != '') ? $c1_form_data_db['res_zipcode'] : '{*}';
        $formatted_residential_address = "$house_no_t/|/$street_t/|/$sub_vil_t/|/$brgy_t/|/$city_t/|/$province_t/|/$zipcode_t";

        // Format permanent address
        $house_no_t = ($c1_form_data_db['per_house_no'] != '') ? $c1_form_data_db['per_house_no'] : '{*}';
        $street_t = ($c1_form_data_db['per_street'] != '') ? $c1_form_data_db['per_street'] : '{*}';
        $sub_vil_t = ($c1_form_data_db['per_sub_vil'] != '') ? $c1_form_data_db['per_sub_vil'] : '{*}';
        $brgy_t = ($c1_form_data_db['per_brgy'] != '') ? $c1_form_data_db['per_brgy'] : '{*}';
        $city_t = ($c1_form_data_db['per_city'] != '') ? $c1_form_data_db['per_city'] : '{*}';
        $province_t = ($c1_form_data_db['per_province'] != '') ? $c1_form_data_db['per_province'] : '{*}';
        $zipcode_t = ($c1_form_data_db['per_zipcode'] != '') ? $c1_form_data_db['per_zipcode'] : '{*}';
        $formatted_permanent_address = "$house_no_t/|/$street_t/|/$sub_vil_t/|/$brgy_t/|/$city_t/|/$province_t/|/$zipcode_t";

        $dual_type_t = '';
        if ($c1_form_data_db['citizenship'] === 'Dual Citizenship' || $c1_form_data_db['citizenship'] === 'Dual Citizen') {
            $dual_type_t = $c1_form_data_db['dual_type'] ?? '';
        }

        // Save PersonalInformation
        Models\PersonalInformation::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'surname' => $c1_form_data_db['surname'],
                'first_name' => $c1_form_data_db['first_name'],
                'middle_name' => $c1_form_data_db['middle_name'],
                'name_extension' => $c1_form_data_db['name_extension'],
                'sex' => $c1_form_data_db['sex'],
                'civil_status' => $c1_form_data_db['civil_status'],
                'date_of_birth' => $this->normalizeDateForDatabase($c1_form_data_db['date_of_birth']),
                'place_of_birth' => $c1_form_data_db['place_of_birth'],
                'height' => $c1_form_data_db['height'],
                'weight' => $c1_form_data_db['weight'],
                'blood_type' => $c1_form_data_db['blood_type'],
                'philhealth_no' => $c1_form_data_db['philhealth_no'],
                'tin_no' => $c1_form_data_db['tin_no'],
                'agency_employee_no' => $c1_form_data_db['agency_employee_no'],
                'gsis_id_no' => $c1_form_data_db['gsis_id_no'],
                'pagibig_id_no' => $c1_form_data_db['pagibig_id_no'],
                'sss_id_no' => $c1_form_data_db['sss_id_no'],
                'citizenship' => $c1_form_data_db['citizenship'],
                'dual_type' => $dual_type_t,
                'dual_country' => $c1_form_data_db['dual_country'] ?? null,
                'residential_address' => $formatted_residential_address,
                'permanent_address' => $formatted_permanent_address,
                'telephone_no' => $c1_form_data_db['telephone_no'],
                'mobile_no' => $c1_form_data_db['mobile_no'],
                'email_address' => $c1_form_data_db['email_address']
            ]
        );

        // Save FamilyBackground
        Models\FamilyBackground::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'spouse_surname' => $c1_form_data_db['spouse_surname'] ?? null,
                'spouse_first_name' => $c1_form_data_db['spouse_first_name'] ?? null,
                'spouse_middle_name' => $c1_form_data_db['spouse_middle_name'] ?? null,
                'spouse_name_extension' => $c1_form_data_db['spouse_name_extension'] ?? null,
                'spouse_occupation' => $c1_form_data_db['spouse_occupation'] ?? null,
                'spouse_employer' => $c1_form_data_db['spouse_employer'] ?? null,
                'spouse_business_address' => $c1_form_data_db['spouse_business_address'] ?? null,
                'spouse_telephone' => $c1_form_data_db['spouse_telephone'] ?? null,
                'father_surname' => $c1_form_data_db['father_surname'] ?? null,
                'father_first_name' => $c1_form_data_db['father_first_name'] ?? null,
                'father_middle_name' => $c1_form_data_db['father_middle_name'] ?? null,
                'father_name_extension' => $c1_form_data_db['father_name_extension'] ?? null,
                'mother_maiden_surname' => $c1_form_data_db['mother_maiden_surname'] ?? null,
                'mother_maiden_first_name' => $c1_form_data_db['mother_maiden_first_name'] ?? null,
                'mother_maiden_middle_name' => $c1_form_data_db['mother_maiden_middle_name'] ?? null,
                'children_info' => $c1_form_data_db['children'] ?? null
            ]
        );

        // Save EducationalBackground
        Models\EducationalBackground::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'elem_from' => $c1_form_data_db['elem_from'],
                'elem_to' => $c1_form_data_db['elem_to'],
                'elem_school' => $c1_form_data_db['elem_school'],
                'elem_academic_honors' => $c1_form_data_db['elem_academic_honors'],
                'elem_basic' => $c1_form_data_db['elem_basic'],
                'elem_earned' => $c1_form_data_db['elem_earned'],
                'elem_year_graduated' => $c1_form_data_db['elem_year_graduated'],
                'jhs_from' => $c1_form_data_db['jhs_from'],
                'jhs_to' => $c1_form_data_db['jhs_to'],
                'jhs_school' => $c1_form_data_db['jhs_school'],
                'jhs_academic_honors' => $c1_form_data_db['jhs_academic_honors'],
                'jhs_basic' => $c1_form_data_db['jhs_basic'],
                'jhs_earned' => $c1_form_data_db['jhs_earned'],
                'jhs_year_graduated' => $c1_form_data_db['jhs_year_graduated'],
                'vocational' => $c1_form_data_db['vocational'] ?? null,
                'college' => $c1_form_data_db['college'] ?? null,
                'grad' => $c1_form_data_db['grad'] ?? null,
            ]
        );

        activity()
            ->causedBy(Auth::user())
            ->log('Updated C1 form session and database.');

        \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);
        //dd(session('form.c1'));
        $routeParams = [];
        if ($request->query('simple')) {
            $routeParams['simple'] = 1;
        }
        return redirect()->route($go_to, $routeParams);
    }

    private function extractC1DataFromExcel(string $uploadedPath): array
    {
        $spreadsheet = IOFactory::load($uploadedPath);
        $c1Sheet = $spreadsheet->getSheetByName('C1');
        $c2Sheet = $spreadsheet->getSheetByName('C2');
        $c3Sheet = $spreadsheet->getSheetByName('C3');
        $c4Sheet = $spreadsheet->getSheetByName('C4');

        if (!$c1Sheet || !$c2Sheet || !$c3Sheet || !$c4Sheet) {
            throw new \RuntimeException('Incompatible file: expected sheets C1, C2, C3, and C4 were not found.');
        }

        $markerCells = [
            'A3' => 'PERSONAL DATA SHEET',
            'A9' => 'I. PERSONAL INFORMATION',
            'A35' => 'II.  FAMILY BACKGROUND',
        ];
        foreach ($markerCells as $cell => $expected) {
            $actual = $this->normalizedExcelText((string) $c1Sheet->getCell($cell)->getFormattedValue());
            if ($actual !== $this->normalizedExcelText($expected)) {
                throw new \RuntimeException('Incompatible file: please upload the official CS Form No. 212 Revised 2025 Excel template.');
            }
        }

        $cellToFieldMap = [
            'D10' => 'surname', 'D11' => 'first_name', 'D12' => 'middle_name', 'L11' => 'name_extension',
            'D15' => 'place_of_birth', 'L15' => 'dual_country', 'D22' => 'height', 'D24' => 'weight',
            'D25' => 'blood_type', 'D27' => 'gsis_id_no', 'D29' => 'pagibig_id_no', 'D31' => 'philhealth_no',
            'D32' => 'sss_id_no', 'D33' => 'tin_no', 'D34' => 'agency_employee_no', 'I18' => 'res_house_no',
            'L18' => 'res_street', 'I21' => 'res_sub_vil', 'L21' => 'res_brgy', 'I23' => 'res_city',
            'L23' => 'res_province', 'I24' => 'res_zipcode', 'I26' => 'per_house_no', 'L26' => 'per_street',
            'I28' => 'per_sub_vil', 'L28' => 'per_brgy', 'I30' => 'per_city', 'L30' => 'per_province',
            'I31' => 'per_zipcode', 'I32' => 'telephone_no', 'I33' => 'mobile_no', 'I34' => 'email_address',
            'D36' => 'spouse_surname', 'D37' => 'spouse_first_name', 'G37' => 'spouse_name_extension',
            'D38' => 'spouse_middle_name', 'D39' => 'spouse_occupation', 'D40' => 'spouse_employer',
            'D41' => 'spouse_business_address', 'D42' => 'spouse_telephone', 'D43' => 'father_surname',
            'D44' => 'father_first_name', 'G44' => 'father_name_extension', 'D45' => 'father_middle_name',
            'D47' => 'mother_maiden_surname', 'D48' => 'mother_maiden_first_name', 'D49' => 'mother_maiden_middle_name',
            'D54' => 'elem_school', 'G54' => 'elem_basic', 'L54' => 'elem_earned', 'M54' => 'elem_year_graduated',
            'N54' => 'elem_academic_honors', 'D55' => 'jhs_school', 'G55' => 'jhs_basic', 'L55' => 'jhs_earned',
            'M55' => 'jhs_year_graduated', 'N55' => 'jhs_academic_honors',
        ];

        $fields = [];
        foreach ($cellToFieldMap as $cell => $field) {
            $fields[$field] = $this->readCellText($c1Sheet, $cell);
        }
        $fields['date_of_birth'] = $this->readCellDate($c1Sheet, 'D13', 'd-m-Y');
        $fields['elem_from'] = $this->readCellDate($c1Sheet, 'J54', 'd-m-Y', true);
        $fields['elem_to'] = $this->readCellDate($c1Sheet, 'K54', 'd-m-Y', true);
        $fields['jhs_from'] = $this->readCellDate($c1Sheet, 'J55', 'd-m-Y', true);
        $fields['jhs_to'] = $this->readCellDate($c1Sheet, 'K55', 'd-m-Y', true);
        $fields['sex'] = $this->normalizeSex($this->readCellText($c1Sheet, 'D16'));
        $fields['civil_status'] = $this->normalizeCivilStatus($this->readCellText($c1Sheet, 'D17'));
        $fields['citizenship'] = $this->normalizeCitizenship($this->readCellText($c1Sheet, 'J13'));
        $fields['blood_type'] = strtoupper(trim((string) ($fields['blood_type'] ?? '')));
        $fields['dual_type'] = '';
        if ($fields['citizenship'] !== 'Dual Citizenship') {
            $fields['dual_country'] = '';
        }

        $children = [];
        for ($i = 0; $i < 12; $i++) {
            $row = 37 + $i;
            $name = $this->readCellText($c1Sheet, "I{$row}");
            $dob = $this->readCellDate($c1Sheet, "M{$row}", 'd-m-Y');
            if ($name !== '' || $dob !== '') {
                $children[] = ['name' => $name, 'dob' => $dob];
            }
        }

        $vocationalRow = [
            'from' => $this->readCellDate($c1Sheet, 'J56', 'd-m-Y', true),
            'to' => $this->readCellDate($c1Sheet, 'K56', 'd-m-Y', true),
            'school' => $this->readCellText($c1Sheet, 'D56'),
            'basic' => $this->readCellText($c1Sheet, 'G56'),
            'earned' => $this->readCellText($c1Sheet, 'L56'),
            'year_graduated' => $this->readCellText($c1Sheet, 'M56'),
            'academic_honors' => $this->readCellText($c1Sheet, 'N56'),
        ];
        $collegeRow = [
            'from' => $this->readCellDate($c1Sheet, 'J57', 'd-m-Y', true),
            'to' => $this->readCellDate($c1Sheet, 'K57', 'd-m-Y', true),
            'school' => $this->readCellText($c1Sheet, 'D57'),
            'basic' => $this->readCellText($c1Sheet, 'G57'),
            'earned' => $this->readCellText($c1Sheet, 'L57'),
            'year_graduated' => $this->readCellText($c1Sheet, 'M57'),
            'academic_honors' => $this->readCellText($c1Sheet, 'N57'),
        ];
        $gradRow = [
            'from' => $this->readCellDate($c1Sheet, 'J58', 'd-m-Y', true),
            'to' => $this->readCellDate($c1Sheet, 'K58', 'd-m-Y', true),
            'school' => $this->readCellText($c1Sheet, 'D58'),
            'basic' => $this->readCellText($c1Sheet, 'G58'),
            'earned' => $this->readCellText($c1Sheet, 'L58'),
            'year_graduated' => $this->readCellText($c1Sheet, 'M58'),
            'academic_honors' => $this->readCellText($c1Sheet, 'N58'),
        ];

        $allCivilService = [];
        for ($i = 0; $i < 7; $i++) {
            $row = 5 + $i;
            $entry = [
                'user_id' => Auth::id(),
                'cs_eligibility_career' => $this->readCellText($c2Sheet, "B{$row}"),
                'cs_eligibility_rating' => $this->readCellText($c2Sheet, "F{$row}"),
                'cs_eligibility_date' => $this->readCellDate($c2Sheet, "G{$row}", 'Y-m-d'),
                'cs_eligibility_place' => $this->readCellText($c2Sheet, "I{$row}"),
                'cs_eligibility_license' => $this->readCellText($c2Sheet, "J{$row}"),
                'cs_eligibility_validity' => $this->readCellDate($c2Sheet, "K{$row}", 'Y-m-d'),
            ];
            if ($this->rowHasData($entry, ['user_id'])) {
                $allCivilService[] = $entry;
            }
        }

        $allWorkExp = [];
        for ($i = 0; $i < 28; $i++) {
            $row = 18 + $i;
            $entry = [
                'user_id' => Auth::id(),
                'work_exp_from' => $this->readCellDate($c2Sheet, "A{$row}", 'Y-m-d'),
                'work_exp_to' => $this->readCellDate($c2Sheet, "C{$row}", 'Y-m-d'),
                'work_exp_position' => $this->readCellText($c2Sheet, "D{$row}"),
                'work_exp_department' => $this->readCellText($c2Sheet, "G{$row}"),
                'work_exp_status' => $this->normalizeWorkStatus($this->readCellText($c2Sheet, "J{$row}")),
                'work_exp_govt_service' => $this->normalizeGovServiceFlag($this->readCellText($c2Sheet, "K{$row}")),
            ];
            if ($this->rowHasData($entry, ['user_id'])) {
                $allWorkExp[] = $entry;
            }
        }

        $dataVoluntary = [];
        for ($i = 0; $i < 7; $i++) {
            $row = 6 + $i;
            $entry = [
                'voluntary_org' => $this->readCellText($c3Sheet, "B{$row}"),
                'voluntary_from' => $this->readCellDate($c3Sheet, "E{$row}", 'Y-m-d'),
                'voluntary_to' => $this->readCellDate($c3Sheet, "F{$row}", 'Y-m-d'),
                'voluntary_hours' => $this->readCellText($c3Sheet, "G{$row}"),
                'voluntary_position' => $this->readCellText($c3Sheet, "H{$row}"),
                'user_id' => Auth::id(),
            ];
            if ($this->rowHasData($entry, ['user_id'])) {
                $dataVoluntary[] = $entry;
            }
        }

        $dataLearning = [];
        for ($i = 0; $i < 21; $i++) {
            $row = 18 + $i;
            $entry = [
                'learning_title' => $this->readCellText($c3Sheet, "B{$row}"),
                'learning_from' => $this->readCellDate($c3Sheet, "E{$row}", 'Y-m-d'),
                'learning_to' => $this->readCellDate($c3Sheet, "F{$row}", 'Y-m-d'),
                'learning_hours' => $this->readCellText($c3Sheet, "G{$row}"),
                'learning_type' => $this->readCellText($c3Sheet, "H{$row}"),
                'learning_conducted' => $this->readCellText($c3Sheet, "I{$row}"),
                'user_id' => Auth::id(),
            ];
            if ($this->rowHasData($entry, ['user_id'])) {
                $dataLearning[] = $entry;
            }
        }

        $skills = [];
        $distinctions = [];
        $organizations = [];
        for ($i = 0; $i < 7; $i++) {
            $row = 42 + $i;
            $skill = $this->readCellText($c3Sheet, "B{$row}");
            $dist = $this->readCellText($c3Sheet, "C{$row}");
            $org = $this->readCellText($c3Sheet, "I{$row}");
            if ($skill !== '') {
                $skills[] = $skill;
            }
            if ($dist !== '') {
                $distinctions[] = $dist;
            }
            if ($org !== '') {
                $organizations[] = $org;
            }
        }

        $related34A = $this->readYesNo($c4Sheet, 'I6', 'K6');
        $related34B = $this->readYesNo($c4Sheet, 'I8', 'K8', 'G10');
        $guilty35A = $this->readYesNo($c4Sheet, 'I13', 'K13', 'G14');
        $criminal35B = $this->readYesNo($c4Sheet, 'I18', 'K18');
        $convicted36 = $this->readYesNo($c4Sheet, 'I23', 'K23', 'G24');
        $separated37 = $this->readYesNo($c4Sheet, 'I27', 'K27', 'G28');
        $candidate38 = $this->readYesNo($c4Sheet, 'I31', 'K31', 'G32');
        $resigned38B = $this->readYesNo($c4Sheet, 'I34', 'K34', 'G35');
        $immigrant39 = $this->readYesNo($c4Sheet, 'I37', 'K37', 'G38');
        $indigenous40A = $this->readYesNo($c4Sheet, 'I43', 'K43', 'G44');
        $pwd40B = $this->readYesNo($c4Sheet, 'I45', 'K45', 'G46');
        $soloParent40C = $this->readYesNo($c4Sheet, 'I47', 'K47', 'G48');

        $criminalDate = $this->readCellDate($c4Sheet, 'H20', 'Y-m-d');
        $criminalStatus = $this->readCellText($c4Sheet, 'G21');
        $criminal35BValue = 'no';
        $criminal35BArray = ['date' => '', 'status' => ''];
        if ($criminal35B === 'yes') {
            $criminal35BValue = trim($criminalDate . ',' . $criminalStatus, ',');
            $criminal35BArray = ['date' => $criminalDate, 'status' => $criminalStatus];
        }

        $govtType = $this->normalizeGovtIdType($this->readCellText($c4Sheet, 'B61'));
        $govtPlaceAndDate = $this->readCellText($c4Sheet, 'B64');
        $govtPlaceIssued = '';
        $govtDateIssued = '';
        if ($govtPlaceAndDate !== '') {
            $parts = array_map('trim', explode('|', $govtPlaceAndDate, 2));
            if (count($parts) === 2) {
                $govtPlaceIssued = $parts[0];
                $govtDateIssued = $this->normalizeDateString($parts[1], 'Y-m-d');
            } else {
                $govtPlaceIssued = $govtPlaceAndDate;
            }
        }

        $c4Data = [
            'related_34_a' => $related34A,
            'related_34_b' => $related34B === 'yes' ? $this->readCellText($c4Sheet, 'G10') : 'no',
            'guilty_35_a' => $guilty35A === 'yes' ? $this->readCellText($c4Sheet, 'G14') : 'no',
            'criminal_35_b' => $criminal35BValue,
            'criminal_35_b_array' => $criminal35BArray,
            'convicted_36' => $convicted36 === 'yes' ? $this->readCellText($c4Sheet, 'G24') : 'no',
            'separated_37' => $separated37 === 'yes' ? $this->readCellText($c4Sheet, 'G28') : 'no',
            'candidate_38' => $candidate38 === 'yes' ? $this->readCellText($c4Sheet, 'G32') : 'no',
            'resigned_38_b' => $resigned38B === 'yes' ? $this->readCellText($c4Sheet, 'G35') : 'no',
            'immigrant_39' => $immigrant39 === 'yes' ? $this->readCellText($c4Sheet, 'G38') : 'no',
            'indigenous_40_a' => $indigenous40A === 'yes' ? $this->readCellText($c4Sheet, 'G44') : 'no',
            'pwd_40_b' => $pwd40B === 'yes' ? $this->readCellText($c4Sheet, 'G46') : 'no',
            'solo_parent_40_c' => $soloParent40C === 'yes' ? $this->readCellText($c4Sheet, 'G48') : 'no',
            'ref1_name' => $this->readCellText($c4Sheet, 'A52'),
            'ref1_tel' => $this->readCellText($c4Sheet, 'G52'),
            'ref1_address' => $this->readCellText($c4Sheet, 'F52'),
            'ref2_name' => $this->readCellText($c4Sheet, 'A53'),
            'ref2_tel' => $this->readCellText($c4Sheet, 'G53'),
            'ref2_address' => $this->readCellText($c4Sheet, 'F53'),
            'ref3_name' => $this->readCellText($c4Sheet, 'A54'),
            'ref3_tel' => $this->readCellText($c4Sheet, 'G54'),
            'ref3_address' => $this->readCellText($c4Sheet, 'F54'),
            'govt_id_type' => $govtType['type'],
            'govt_id_other' => $govtType['other'],
            'govt_id_number' => $this->readCellText($c4Sheet, 'B62'),
            'govt_id_date_issued' => $govtDateIssued,
            'govt_id_place_issued' => $govtPlaceIssued,
            'photo_upload' => null,
        ];

        $warnings = [];
        if (($fields['citizenship'] ?? '') === 'Dual Citizenship') {
            $warnings[] = 'Dual citizenship type (By Birth / By Naturalization) is not available in the Excel template and must be selected manually.';
        }
        if (empty($dataLearning) && empty($dataVoluntary) && empty($skills) && empty($distinctions) && empty($organizations)) {
            $warnings[] = 'C3 sheet appears to have no importable entries.';
        }

        return [
            'c1' => [
                'fields' => $fields,
                'children' => $children,
                'vocational' => $this->rowHasData($vocationalRow) ? [$vocationalRow] : [],
                'college' => $this->rowHasData($collegeRow) ? [$collegeRow] : [],
                'grad' => $this->rowHasData($gradRow) ? [$gradRow] : [],
            ],
            'c2' => [
                'all_user_work_exps' => $allWorkExp,
                'all_user_civil_service_eligibility' => $allCivilService,
            ],
            'c3' => [
                'data_learning' => $dataLearning,
                'data_voluntary' => $dataVoluntary,
                'data_otherInfo' => [
                    'skill' => $skills,
                    'distinction' => $distinctions,
                    'organization' => $organizations,
                    'user_id' => Auth::id(),
                ],
            ],
            'c4' => $c4Data,
            'warnings' => $warnings,
            'missing_report' => $this->buildExcelCoverageReport(),
        ];
    }

    private function readCellText($sheet, string $cell): string
    {
        return $this->sanitizeExtractedText(trim((string) $sheet->getCell($cell)->getFormattedValue()));
    }

    private function readCellDate($sheet, string $cell, string $outputFormat = 'd-m-Y', bool $monthYearOnly = false): string
    {
        $uploadedCell = $sheet->getCell($cell);
        $raw = $uploadedCell->getValue();
        $asText = trim((string) $uploadedCell->getFormattedValue());
        if ($asText === '' && ($raw === null || $raw === '')) {
            return '';
        }

        try {
            if (is_numeric($raw)) {
                $date = ExcelDate::excelToDateTimeObject((float) $raw);
                if ($monthYearOnly) {
                    $date->modify('first day of this month');
                }
                return $date->format($outputFormat);
            }

            $candidate = trim((string) $raw);
            if ($candidate === '') {
                return '';
            }

            return $this->normalizeDateString($candidate, $outputFormat, $monthYearOnly);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function normalizeDateString(string $value, string $outputFormat = 'd-m-Y', bool $monthYearOnly = false): string
    {
        $candidate = trim($value);
        if ($candidate === '') {
            return '';
        }

        $formats = ['d/m/Y', 'd-m-Y', 'm/d/Y', 'Y-m-d', 'm/Y', 'm-Y', 'Y-m'];
        foreach ($formats as $format) {
            try {
                $dt = Carbon::createFromFormat($format, $candidate);
                if ($dt !== false) {
                    if ($monthYearOnly || in_array($format, ['m/Y', 'm-Y', 'Y-m'], true)) {
                        $dt->startOfMonth();
                    }
                    return $dt->format($outputFormat);
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            $dt = Carbon::parse($candidate);
            if ($monthYearOnly) {
                $dt->startOfMonth();
            }
            return $dt->format($outputFormat);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function readYesNo($sheet, string $yesCell, string $noCell, ?string $detailCell = null): string
    {
        $yesRaw = $this->normalizedExcelText((string) $sheet->getCell($yesCell)->getFormattedValue());
        $noRaw = $this->normalizedExcelText((string) $sheet->getCell($noCell)->getFormattedValue());

        $yesMarked = $this->isMarkedCellValue($yesRaw);
        $noMarked = $this->isMarkedCellValue($noRaw);

        if ($yesMarked && !$noMarked) {
            return 'yes';
        }
        if ($noMarked && !$yesMarked) {
            return 'no';
        }

        if ($detailCell) {
            $detail = $this->readCellText($sheet, $detailCell);
            if ($detail !== '') {
                return 'yes';
            }
        }

        return 'no';
    }

    private function isMarkedCellValue(string $value): bool
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return false;
        }
        return in_array($trimmed, ['X', '/', 'YES', 'TRUE', '1'], true);
    }

    private function sanitizeExtractedText(string $value): string
    {
        $text = trim($value);
        if ($text === '') {
            return '';
        }

        $placeholders = [
            'House/Block/Lot No.',
            'Street',
            'Subdivision/Village',
            'Barangay',
            'City/Municipality',
            'Province',
            'ZIP Code',
            'Government Issued ID:',
            'ID/License/Passport No.:',
            'Date/Place of Issuance:',
            'Date Filed:',
            'Status of Case/s:',
            'If YES, give details:',
            'If YES, please specify:',
            'If YES, please specify ID No:',
            'If YES, give details (country):',
        ];

        foreach ($placeholders as $placeholder) {
            if (strcasecmp($text, $placeholder) === 0) {
                return '';
            }
        }

        if (preg_match('/^If YES, give details/i', $text)) {
            return '';
        }

        return $text;
    }

    private function normalizeSex(string $value): string
    {
        $normalized = strtolower(trim($value));
        if (str_starts_with($normalized, 'm')) {
            return 'male';
        }
        if (str_starts_with($normalized, 'f')) {
            return 'female';
        }
        return '';
    }

    private function normalizeCivilStatus(string $value): string
    {
        $normalized = strtolower(trim($value));
        return match ($normalized) {
            'single' => 'single',
            'married' => 'married',
            'widowed' => 'widowed',
            'separated', 'seperated' => 'separated',
            'other', 'others', 'other/s' => 'other',
            default => '',
        };
    }

    private function normalizeCitizenship(string $value): string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return '';
        }
        if (str_contains($normalized, 'dual')) {
            return 'Dual Citizenship';
        }
        if (str_contains($normalized, 'filipino')) {
            return 'Filipino';
        }
        return '';
    }

    private function normalizedExcelText(string $value): string
    {
        return preg_replace('/\s+/', ' ', strtoupper(trim($value))) ?? '';
    }

    private function rowHasData(array $row, array $excludeKeys = []): bool
    {
        foreach ($row as $key => $value) {
            if (in_array($key, $excludeKeys, true)) {
                continue;
            }
            if (trim((string) $value) !== '') {
                return true;
            }
        }
        return false;
    }

    private function normalizeWorkStatus(string $value): string
    {
        $normalized = strtolower(trim($value));
        return match ($normalized) {
            'permanent' => 'Permanent',
            'temporary' => 'Temporary',
            'casual' => 'Casual',
            'contractual' => 'Contractual',
            default => '',
        };
    }

    private function normalizeGovServiceFlag(string $value): string
    {
        $normalized = strtolower(trim($value));
        if (in_array($normalized, ['y', 'yes', 'true', '1'], true)) {
            return 'Y';
        }
        if (in_array($normalized, ['n', 'no', 'false', '0'], true)) {
            return 'N';
        }
        return '';
    }

    private function normalizeGovtIdType(string $value): array
    {
        $raw = trim($value);
        $normalized = strtolower($raw);

        $map = [
            'passport' => 'Passport',
            'gsis' => 'GSIS',
            'sss' => 'SSS',
            'philhealth' => 'PhilHealth',
            "driver's license" => "Driver's License",
            'drivers license' => "Driver's License",
            'prc' => 'PRC',
            "voter's id" => "Voter's ID",
            'voters id' => "Voter's ID",
            'philsys/national id' => 'PhilSys/National ID',
            'philsys national id' => 'PhilSys/National ID',
            'national id' => 'PhilSys/National ID',
        ];

        if (isset($map[$normalized])) {
            return ['type' => $map[$normalized], 'other' => ''];
        }

        if ($raw === '') {
            return ['type' => '', 'other' => ''];
        }

        return ['type' => 'other', 'other' => $raw];
    }

    private function buildExcelCoverageReport(): array
    {
        return [
            'mapped_sections' => ['c1', 'c2', 'c3', 'c4'],
            'missing_in_excel_template' => [
                'c1' => [
                    'cs_id_no (CSC use only)',
                    'dual_type (By Birth / By Naturalization)',
                ],
                'c2' => [],
                'c3' => [],
                'c4' => ['photo_upload'],
                'wes' => [
                    'entries[*].start_date',
                    'entries[*].end_date',
                    'entries[*].position',
                    'entries[*].office',
                    'entries[*].supervisor',
                    'entries[*].agency',
                    'entries[*].accomplishments[*]',
                    'entries[*].duties[*]',
                    'entries[*].isDisplayed',
                ],
            ],
            'notes' => [
                'WES is not part of ANNEX H-1 workbook and cannot be auto-populated from this file.',
                'Only first 7 voluntary/skills rows and first 21 L&D rows are supported by the Excel template.',
                'Only first 7 civil service rows and first 28 work experience rows are supported by the Excel template.',
            ],
        ];
    }


    /**
     * Update the C1 session data based on the database. If there is no data on the database,
     * the function should return an empty array.
     *
     * @return array{all_user_civil_service_eligibility: array, all_user_work_exps: array}
     */
    private function c2GetFormFromDB()
    {

        $current_user_id = Auth::id();
        $all_user_work_exps = WorkExperience::where(
            'user_id',
            '=',
            $current_user_id
        )->get()->toArray();

        /**NOTE:
         * $user_work_exps is a multidimensional array with the format:
         *
         * [ [index1 => [user_work_experience_record1]], [index2 => [user_work_experience_record2]], ... ]
         *
         * a user work experience record have all the attributes stated in its migration file
         * @see yyyy_mm_dd_create_work_experiences_table.php
         *
         * ** This is also true for the variable $civil_service_eligibility below....
         */

        $all_user_civil_service_eligibility = CivilServiceEligibility::where(
            'user_id',
            '=',
            $current_user_id
        )->get()->toArray();

        $c2_full_info = [
            'all_user_work_exps' => $all_user_work_exps,
            'all_user_civil_service_eligibility' => $all_user_civil_service_eligibility
        ];

        return $c2_full_info;
    }


    /**
     * displays C2 page with all session data.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function c2DisplayForm()
    {

        // Run if session does not exists. get data from database. if database has no data
        // return an empty array.
        if (!session()->has('form.c2')) {
            session(['form.c2' => $this->c2GetFormFromDB()]);
        }

        // Run if session exists
        $all_user_work_exps = session('form.c2.all_user_work_exps');
        $all_user_civil_service_eligibility = session('form.c2.all_user_civil_service_eligibility');
        /*
                activity()
                    ->causedBy(Auth::user())
                    ->log('Viewed C2 form.');
        */
        return view('pds.c2', compact('all_user_work_exps', 'all_user_civil_service_eligibility'));
    }


    /**
     * Updates C2 session data based on the input fields.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\RedirectResponse
     */
    public function c2UpdateFormSession(Request $request, $go_to)
    {

        //dd($request->except('_token'));
        $c2_form_data = $request->except('_token');

        // ------------------------------
        // WORK EXPERIENCE TABLE
        // ------------------------------
        $work_exp_count = $c2_form_data['work_exp_count'] ?? 0;
        $all_wex_data = [];

        for ($i = 0; $i < $work_exp_count; $i++) {

            $data_work_exp = [
                'user_id' => Auth::id(), // store the id of the current user
                'work_exp_from' => trim(strip_tags($c2_form_data['work_exp_from'][$i])),
                'work_exp_to' => trim(strip_tags($c2_form_data['work_exp_to'][$i])),
                'work_exp_position' => trim(strip_tags($c2_form_data['work_exp_position'][$i])),
                'work_exp_department' => trim(strip_tags($c2_form_data['work_exp_department'][$i])),
                'work_exp_status' => trim(strip_tags($c2_form_data['work_exp_status'][$i])),
                'work_exp_govt_service' => trim(strip_tags($c2_form_data['work_exp_govt_service'][$i]))
            ];


            // Check if the value of id is zero, a zero value means a record
            // does not exist. thus, create a new record
            $wex_id_temp = $c2_form_data['work_exp_id'][$i] ?? null;
            if (!empty($wex_id_temp)) {
                $data_work_exp['id'] = $wex_id_temp;
            } else {
                $data_work_exp['created_at'] = now();
            }
            $data_work_exp['updated_at'] = now();

            if (!empty($data_work_exp['id'])) {
                WorkExperience::where('id', $data_work_exp['id'])->update($data_work_exp);
            } else {
                $newRecord = WorkExperience::create($data_work_exp);
                $data_work_exp['id'] = $newRecord->id;
            }

            $all_wex_data[] = $data_work_exp;
            // WorkExperience::upsert($data_work_exp, 'id');
        }


        // ------------------------------
        // CIVIL SERVICE ELIGIBILITY test
        // ------------------------------
        $civil_service_count = $c2_form_data['civil_service_count'] ?? 0;
        $all_cs_data = [];

        for ($i = 0; $i < $civil_service_count; $i++) {

            $data_cs = [
                'user_id' => Auth::id(), // store the id of the current user
                'cs_eligibility_career' => trim(strip_tags($c2_form_data['cs_eligibility_career'][$i])),
                'cs_eligibility_rating' => trim(strip_tags($c2_form_data['cs_eligibility_rating'][$i])),
                'cs_eligibility_date' => trim(strip_tags($c2_form_data['cs_eligibility_date'][$i])),
                'cs_eligibility_place' => trim(strip_tags($c2_form_data['cs_eligibility_place'][$i])),
                'cs_eligibility_license' => trim(strip_tags($c2_form_data['cs_eligibility_license'][$i])),
                'cs_eligibility_validity' => trim(strip_tags($c2_form_data['cs_eligibility_validity'][$i]))
            ];

            $cs_id_temp = $c2_form_data['cs_eligibility_id'][$i] ?? null;
            if (!empty($cs_id_temp)) {
                $data_cs['id'] = $cs_id_temp;
            } else {
                $data_cs['created_at'] = now();
            }
            $data_cs['updated_at'] = now();
            if (!empty($data_cs['id'])) {
                CivilServiceEligibility::where('id', $data_cs['id'])->update($data_cs);
            } else {
                $newRecord = CivilServiceEligibility::create($data_cs);
                $data_cs['id'] = $newRecord->id;
            }

            $all_cs_data[] = $data_cs;
            // CivilServiceEligibility::upsert($data_cs, 'id');
        }


        $c2_full_info = [
            'all_user_work_exps' => $all_wex_data,
            'all_user_civil_service_eligibility' => $all_cs_data
        ];

        session(['form.c2' => $c2_full_info]);

        activity()
            ->causedBy(Auth::user())
            ->log('Updated C2 form session.');

        \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);
        $routeParams = [];
        if ($request->query('simple')) {
            $routeParams['simple'] = 1;
        }
        return redirect()->route($go_to, $routeParams);

    }

    public function c2DeleteRow($target_row, $id)
    {

        switch ($target_row) {
            case 'work-exp-table':
                WorkExperience::destroy($id);
                break;

            case 'civil-service-table':
                CivilServiceEligibility::destroy($id);
                break;
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties(['target_row' => $target_row, 'id' => $id])
            ->log("Deleted row in C2 form.");

        return response('Delete OK', 200);
    }

    // PDS PAGE 3
    public function c3SubmitForm(Request $request, $go_to)
    {

        // ------------------------------
        // LEARNING AND DEVELOPMENT
        // ------------------------- -----
        $data_learning = $request->all();
        $entryCount = (int) ($data_learning['learning_entry_count'] ?? 0); // get from hidden field
        //$entryCount = 1 + $entryCount;

        // If validation fails the inputted data in Learning and Development (L&D) Interventions table
        // c3.blade the data is already in session to auto populate the input fields.
        $data_learning_arrays = [];
        //dd($request->all());
        for ($i = 1; $i <= $entryCount; $i++) {
            $data_learning_arrays[] = [
                'learning_title' => $data_learning["learning_title_$i"],
                'learning_type' => $data_learning["learning_type_$i"],
                'learning_from' => $data_learning["learning_from_$i"],
                'learning_to' => $data_learning["learning_to_$i"],
                'learning_hours' => $data_learning["learning_hours_$i"],
                'learning_conducted' => $data_learning["learning_conducted_$i"],
            ];
        }
        session(['data_learning' => $data_learning_arrays]);

        // ---------------------------------------------------------------------------
        // VOLUNTARY WORK EXPERIENCE
        // ---------------------------------------------------------------------------
        $data_vol = $request->all();
        $entryCount_vol = (int) ($data_vol['voluntary_work_count'] ?? 0); // get from hidden field

        // If validation fails the inputted data in Voluntary Works table
        // c3.blade the data is already in session to auto populate the input fields.
        $data_voluntary_arrays = [];
        for ($i = 1; $i <= $entryCount_vol; $i++) {
            $data_voluntary_arrays[] = [
                'voluntary_org' => $data_vol["voluntary_org_$i"],
                'voluntary_from' => $data_vol["voluntary_from_$i"],
                'voluntary_to' => $data_vol["voluntary_to_$i"],
                'voluntary_hours' => $data_vol["voluntary_hours_$i"],
                'voluntary_position' => $data_vol["voluntary_position_$i"],
            ];
        }
        session(['data_voluntary' => $data_voluntary_arrays]);

        // ---------------------------------------------------------------------------
        // OTHER INFORMATION
        // ---------------------------------------------------------------------------
        $skills = $request->input('skills', []);
        $distinctions = $request->input('distinctions', []);
        $organizations = $request->input('organizations', []);
        if (!is_array($skills)) {
            $skills = [$skills];
        }
        if (!is_array($distinctions)) {
            $distinctions = [$distinctions];
        }
        if (!is_array($organizations)) {
            $organizations = [$organizations];
        }
        $skills = array_values(array_filter($skills, fn($value) => $value !== null && $value !== ''));
        $distinctions = array_values(array_filter($distinctions, fn($value) => $value !== null && $value !== ''));
        $organizations = array_values(array_filter($organizations, fn($value) => $value !== null && $value !== ''));
        $data_other_arrays = [
            'skill' => $skills,
            'distinction' => $distinctions,
            'organization' => $organizations,
            'user_id' => Auth::id(),
        ];
        session(['data_otherInfo' => $data_other_arrays]);
        //dd($data_other_arrays);

        /*
        $user_other_info = OtherInformation::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $user_other_info->update([
            'user_id'       => Auth::id(),
            'skill'         => $data_other['skills'],
            'distinction'   => $data_other['distinctions'],
            'organization'  => $data_other['organizations'],
        ]);
        */

        // ========================================================================
        // VALIDATION
        // ========================================================================

        // LEARNING AND DEVELOPMENT VALIDATION
        $rules_data_learning = [];
        for ($i = 1; $i <= $entryCount; $i++) {
            $rules_data_learning["learning_title_$i"] = 'required|string|max:255';
            $rules_data_learning["learning_type_$i"] = 'required|string|max:100';
            $rules_data_learning["learning_from_$i"] = 'required|date';
            $rules_data_learning["learning_to_$i"] = "required|date|after_or_equal:learning_from_$i";
            $rules_data_learning["learning_hours_$i"] = 'required|numeric|min:1';
            $rules_data_learning["learning_conducted_$i"] = 'required|string|max:255';
        }
        $validated_data_learning = $request->validate($rules_data_learning);

        // FOR session data in LEARNING AND DEVELOPMENT
        $data_learning_arrays = [];
        for ($i = 1; $i <= $entryCount; $i++) {
            $data_learning_arrays[] = [
                'learning_title' => $validated_data_learning["learning_title_$i"],
                'learning_type' => $validated_data_learning["learning_type_$i"],
                'learning_from' => $validated_data_learning["learning_from_$i"],
                'learning_to' => $validated_data_learning["learning_to_$i"],
                'learning_hours' => $validated_data_learning["learning_hours_$i"],
                'learning_conducted' => $validated_data_learning["learning_conducted_$i"],
                'user_id' => Auth::id(),
            ];
        }
        // SESSION name table of Learning and Development (L&D) Interventions in c3.blade file
        session(['data_learning' => $data_learning_arrays]);
        //dd(session('data_learning'));


        // VOLUNTARY WORKS VALIDATION
        $rules_data_vol = [];
        for ($i = 1; $i <= $entryCount_vol; $i++) {
            $rules_data_vol["voluntary_org_$i"] = 'required|string|max:255';
            $rules_data_vol["voluntary_from_$i"] = 'required|date';
            $rules_data_vol["voluntary_to_$i"] = "required|date|after_or_equal:voluntary_from_$i";
            $rules_data_vol["voluntary_hours_$i"] = 'required|numeric|min:1';
            $rules_data_vol["voluntary_position_$i"] = 'required|string|max:255';
        }
        $validated_data_vol = $request->validate($rules_data_vol);

        // FOR session data in VOLUNTARY WORK
        $data_voluntary_arrays = [];
        for ($i = 1; $i <= $entryCount_vol; $i++) {
            $data_voluntary_arrays[] = [
                'voluntary_org' => $validated_data_vol["voluntary_org_$i"],
                'voluntary_from' => $validated_data_vol["voluntary_from_$i"],
                'voluntary_to' => $validated_data_vol["voluntary_to_$i"],
                'voluntary_hours' => $validated_data_vol["voluntary_hours_$i"],
                'voluntary_position' => $validated_data_vol["voluntary_position_$i"],
                'user_id' => Auth::id(),
            ];
        }
        // SESSION name table of VOLUNTARY WORKS in c3.blade file
        session(['data_voluntary' => $data_voluntary_arrays]);

        // VALIDATION ENDS
        // ========================================================================
/*
        activity()
            ->causedBy(Auth::user())
            ->log('Submitted C3 form data.');
*/
        \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);

        // SAVE C3 to DB
        //LEARNING AND DEVELOPMENT
        $c3_learning_and_development_data = session('data_learning');
        if (!empty($c3_learning_and_development_data)) {
            LearningAndDevelopment::where('user_id', Auth::id())->delete();
            LearningAndDevelopment::upsert(
                $c3_learning_and_development_data,
                ['learning_title', 'learning_from', 'user_id'], // Unique constraint
                ['learning_type', 'learning_hours', 'learning_to', 'learning_conducted'] // Fields to update
            );
        }

        //VOLUNTARY WORK
        $c3_voluntary_data = session('data_voluntary');
        if (!empty($c3_voluntary_data)) {
            VoluntaryWork::where('user_id', Auth::id())->delete();
            VoluntaryWork::upsert(
                $c3_voluntary_data,
                ['voluntary_org', 'voluntary_from', 'user_id'], // Unique constraint
                ['voluntary_to', 'voluntary_hours', 'voluntary_position'] // Fields to update
            );
        }

        //OTHER INFORMATION
        $c3_other_information_data = session('data_otherInfo');
        if (!empty($c3_other_information_data)) {
            Models\OtherInformation::updateOrCreate(
                ['user_id' => Auth::id()],
                [
                    'skill' => $c3_other_information_data['skill'],
                    'distinction' => $c3_other_information_data['distinction'],
                    'organization' => $c3_other_information_data['organization'],
                ]
            );
        }
        $routeParams = [];
        if ($request->query('simple')) {
            $routeParams['simple'] = 1;
        }
        return redirect()->route($go_to, $routeParams);
    }

    public function c3ShowForm()
    {
        if (empty(session('data_learning')) && empty(session('data_voluntary')) && empty(session('data_otherInfo'))) {
            $this->c3GetDatabase();
        }
        $data_learning = session('data_learning', []);
        $data_voluntary = session('data_voluntary', []);
        $data_otherInfo = session('data_otherInfo', []);
        /*
                activity()
                    ->causedBy(Auth::user())
                    ->log('Viewed C3 form.');
        */
        return view('pds.c3', compact('data_learning', 'data_voluntary', 'data_otherInfo'));
    }

    public function c3GetDatabase()
    {
        $current_user_id = Auth::id();
        $all_user_learningAndDevelopment_data = LearningAndDevelopment::where(
            'user_id',
            '=',
            $current_user_id
        )->get()->toArray();

        $all_user_voluntary_work_data = VoluntaryWork::where(
            'user_id',
            '=',
            $current_user_id
        )->get()->toArray();

        $all_user_other_info_data = OtherInformation::where('user_id', $current_user_id)
            ->first(); // Assuming only one record per user
        if ($all_user_other_info_data) {
            $processed_data = [
                'skill' => $all_user_other_info_data->skill ?? [],
                'distinction' => $all_user_other_info_data->distinction ?? [],
                'organization' => $all_user_other_info_data->organization ?? [],
                'user_id' => $all_user_other_info_data->user_id,
            ];
            session(['data_otherInfo' => $processed_data]);
        }

        session(['data_learning' => $all_user_learningAndDevelopment_data]);
        session(['data_voluntary' => $all_user_voluntary_work_data]);
    }


    // ==============================================================================
    // C4 CONTROLLER
    public function c4SubmitForm(Request $request, $go_to)
    {
        /*
        $hasUploadedPhoto = session()->has('form.c4.photo_upload');
        $request->validate([
            'photo_upload' => $hasUploadedPhoto
                ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240'
                : 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);
        $temp_photo_path = session('form.c4.photo_upload'); // default to existing photo
        //Handle the photo upload (store in photo/pds-img inside public disk)
        // Only store new photo if uploaded
        if ($request->hasFile('photo_upload') && $request->file('photo_upload')->isValid()) {
            $photo = $request->file('photo_upload');

            //Store the uploaded photo in: storage/app/public/uploads/pds-photo
            $new_photo_path = $photo->store('uploads/pds-photo', 'public');

            //Delete old photo if it's different and exists
            if (!empty($temp_photo_path) &&
                $temp_photo_path !== $new_photo_path &&
                Storage::disk('public')->exists($temp_photo_path)) {
                Storage::disk('public')->delete($temp_photo_path);
            }

            $temp_photo_path = $new_photo_path; // use the new photo path
        } */
        // get the selected option if "Yes" or "No"
        $temp_34_b = $request->input('related_34_b');
        $temp_35_a = $request->input('guilty_35_a');
        $temp_35_b = $request->input('criminal_35_b');
        $temp_36 = $request->input('convicted_36');
        $temp_37 = $request->input('separated_37');
        $temp_38_a = $request->input('candidate_38_a');
        $temp_38_b = $request->input('resigned_38_b');
        $temp_39 = $request->input('immigrant_39');
        $temp_40_a = $request->input('indigenous_40_a');
        $temp_40_b = $request->input('pwd_40_b');
        $temp_40_c = $request->input('solo_parent_40_c');

        // If "yes" was selected, use the text area instead
        $related_34_b = $this->userSelection($temp_34_b, $request, 'related_34_b_details');
        $guilty_35_a = $this->userSelection($temp_35_a, $request, 'guilty_35_a_details');
        $convicted_36 = $this->userSelection($temp_36, $request, 'convicted_36_details');
        $separated_37 = $this->userSelection($temp_37, $request, 'separated_37_details');
        $candidate_38_a = $this->userSelection($temp_38_a, $request, 'candidate_38_a_details');
        $resigned_38_b = $this->userSelection($temp_38_b, $request, 'resigned_38_b_details');
        $immigrant_39 = $this->userSelection($temp_39, $request, 'immigrant_39_details');
        $indigenous_40_a = $this->userSelection($temp_40_a, $request, 'indigenous_40_a_details');
        $pwd_40_b = $this->userSelection($temp_40_b, $request, 'pwd_40_b_details');
        $solo_parent_40_c = $this->userSelection($temp_40_c, $request, 'solo_parent_40_c_details');

        $criminal_35_b_array = $request->input('criminal_35_b_details');
        // NUMBER 35_b
        if ($temp_35_b === 'yes') {
            $criminal_35_b = implode(',', $criminal_35_b_array);

        } else {
            $criminal_35_b = 'no';
        }

        $govt_id_type = $request->input('govt_id_type');
        if ($govt_id_type === 'other') {
            $govt_id_type = $request->input('govt_id_other');
            //Updated value into the request for validation to work
            $request->merge(['govt_id_type' => $govt_id_type]);
        }

        // TODO get the photo upload
        $misc_data = [
            //'user_id'               => Auth::id(),
            'related_34_a' => $request->input('related_34_a'),
            'related_34_b' => $related_34_b,
            'guilty_35_a' => $guilty_35_a,
            'criminal_35_b' => $criminal_35_b,
            'criminal_35_b_array' => $criminal_35_b_array, // remove if insert into database
            'convicted_36' => $convicted_36,
            'separated_37' => $separated_37,
            'candidate_38' => $candidate_38_a,
            'resigned_38_b' => $resigned_38_b,
            'immigrant_39' => $immigrant_39,
            'indigenous_40_a' => $indigenous_40_a,
            'pwd_40_b' => $pwd_40_b,
            'solo_parent_40_c' => $solo_parent_40_c,

            'ref1_name' => $request->input('ref1_name'),
            'ref1_tel' => $request->input('ref1_tel'),
            'ref1_address' => $request->input('ref1_address'),
            'ref2_name' => $request->input('ref2_name'),
            'ref2_tel' => $request->input('ref2_tel'),
            'ref2_address' => $request->input('ref2_address'),
            'ref3_name' => $request->input('ref3_name'),
            'ref3_tel' => $request->input('ref3_tel'),
            'ref3_address' => $request->input('ref3_address'),

            'govt_id_type' => $govt_id_type,
            'govt_id_other' => $request->input('govt_id_other'),
            'govt_id_number' => $request->input('govt_id_number'),
            'govt_id_date_issued' => $request->input('govt_id_date_issued'),
            'govt_id_place_issued' => $request->input('govt_id_place_issued'),

            'photo_upload' => $temp_photo_path ?? null,
        ];
        session(['form.c4' => $misc_data]);
        //dd(session('form.c4'));
        //dd($request->all());
        //dd(session('form.c4')); // TODO: GETDATABASE

        // Validate the form for the hidden fields (e.g: realated_34_b_details)
        $request->validate([
            'related_34_b_details' => 'required_if:related_34_b,yes|nullable|string|max:255',
            'criminal_35_b_details.date' => 'required_if:criminal_35_b,yes|nullable|date',
            'criminal_35_b_details.status' => 'required_if:criminal_35_b,yes|nullable|string|max:255',
            'convicted_36_details' => 'required_if:convicted_36,yes|nullable|string|max:255',
            'separated_37_details' => 'required_if:separated_37,yes|nullable|string|max:255',
            'candidate_38_a_details' => 'required_if:candidate_38_a,yes|nullable|string|max:255',
            'resigned_38_b_details' => 'required_if:resigned_38_b,yes|nullable|string|max:255',
            'immigrant_39_details' => 'required_if:immigrant_39,yes|nullable|string|max:255',
            'indigenous_40_a_details' => 'required_if:indigenous_40_a,yes|nullable|string|max:255',
            'pwd_40_b_details' => 'required_if:pwd_40_b,yes|nullable|string|max:255',
            'solo_parent_40_c_details' => 'required_if:solo_parent_40_c,yes|nullable|string|max:255',
            'govt_id_other' => 'nullable|required_if:govt_id_type,other|string|max:255',

        ]);

        // Validation for the data to be inserted in session to database
        $validator_misc_data = Validator::make($misc_data, [
            'related_34_a' => 'required|string|max:255',
            'related_34_b' => 'required|string|max:255',
            'guilty_35_a' => 'required|string|max:255',
            'criminal_35_b' => 'required|string|max:255',
            'convicted_36' => 'required|string|max:255',
            'separated_37' => 'required|string|max:255',
            'candidate_38' => 'required|string|max:255',
            'resigned_38_b' => 'required|string|max:255',
            'immigrant_39' => 'required|string|max:255',
            'indigenous_40_a' => 'required|string|max:255',
            'pwd_40_b' => 'required|string|max:255',
            'solo_parent_40_c' => 'required|string|max:255',

            'ref1_name' => 'required|string|max:255',
            'ref1_tel' => 'required|string|max:20',
            'ref1_address' => 'required|string|max:255',
            'ref2_name' => 'required|string|max:255',
            'ref2_tel' => 'required|string|max:20',
            'ref2_address' => 'required|string|max:255',
            'ref3_name' => 'required|string|max:255',
            'ref3_tel' => 'required|string|max:20',
            'ref3_address' => 'required|string|max:255',

            'govt_id_type' => 'required|string|max:255',
            'govt_id_number' => 'required|string|max:50',
            'govt_id_date_issued' => 'required|date',
            'govt_id_place_issued' => 'required|string|max:255',
            'photo_upload' => 'nullable|string',
        ]);

        $validated_misc_data = $validator_misc_data->validated();
        $validated_misc_data['criminal_35_b_array'] = $criminal_35_b_array;
        $validated_misc_data['govt_id_other'] = $request->input('govt_id_other');
        $validated_misc_data['user_id'] = Auth::id();

        session(['form.c4' => $validated_misc_data]);
        /*
                activity()
                    ->causedBy(Auth::user())
                    ->log('Submitted C4 form data.');
        */
        \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);

        // SAVE C4 to DB
        $c4_misc_info_data = session('form.c4');
        if (!empty($c4_misc_info_data)) {
            $dataToSave = $c4_misc_info_data;
            unset($dataToSave['criminal_35_b_array']);

            MiscInfos::updateOrCreate(
                ['user_id' => Auth::id()],
                $dataToSave
            );
        }

        return redirect()->route($go_to);
    }

    public function userSelection($sel, Request $request, string $textArea)
    {
        /*
        FOR THE SELECTION OF YES OR NO, WHERE IF THE SELECTED
        IS "NO", data="no". IF "YES" the data="details"
        */

        if ($sel === 'yes') {
            return $request->input($textArea);
        } else {
            return 'no';
        }
    }

    public function c4ShowForm()
    {
        if (empty(session('form.c4'))) {
            $this->c4GetDatabase();
        }
        $data = session('form.c4', []);

        /*
        if (!empty($data['photo_upload'])) {
                $encodedPath = base64_encode($data['photo_upload']);
                $data['photo_preview_url'] = url('/preview-file/' . $encodedPath);
        } else {
                $data['photo_preview_url'] = null;
        }
        */
        //dd($data);
/*
        activity()
            ->causedBy(Auth::user())
            ->log('Viewed C4 form.');
*/
        return view('pds.c4', compact('data'));
    }

    // TODO: GETINDATABASE
    public function c4GetDatabase()
    {
        $current_user_id = Auth::id();
        $all_user_miscInfo_data = MiscInfos::where('user_id', '=', $current_user_id)->first();
        if ($all_user_miscInfo_data) {
            $data = $all_user_miscInfo_data->toArray();

            // Normalize unknown government ID types
            $valid_id_types = ['passport', 'gsis', 'sss', 'philhealth', 'drivers', 'prc', 'voters'];
            $original_govt_id = strtolower(trim($data['govt_id_type'] ?? ''));

            if (!in_array($original_govt_id, $valid_id_types)) {
                // Store custom/unknown value in govt_id_other
                $data['govt_id_other'] = $data['govt_id_type'];
                $data['govt_id_type'] = 'other';
            } else {
                // Just to ensure it's clean
                $data['govt_id_type'] = $original_govt_id;
            }

            // Handle "criminal_35_b_array" from "criminal_35_b"
            if (!empty($data['criminal_35_b']) && str_contains($data['criminal_35_b'], ',')) {
                [$date, $status] = explode(',', $data['criminal_35_b'], 2);
                $data['criminal_35_b_array'] = [
                    'date' => $date,
                    'status' => $status,
                ];
            } else {
                $data['criminal_35_b_array'] = [
                    'date' => null,
                    'status' => null,
                ];
            }

            /*
            // For Previewing of photo uploaded in live server
            if (!empty($data['photo_upload'])) {
                $encodedPath = base64_encode($data['photo_upload']);
                $data['photo_preview_url'] = url('/preview-file/' . $encodedPath);
            } else {
                $data['photo_preview_url'] = null;
            }
                */

            session(['form.c4' => $data]);

            //dd(session('form.c4'));
        }

        //session(['form.c4' => $all_user_miscInfo_data]);
        //dd($all_user_miscInfo_data);
    }

    private function resolveAutosaveCount($countValue, int $fallback = 0): int
    {
        if (is_array($countValue)) {
            $countValue = end($countValue);
        }

        if (!is_numeric($countValue)) {
            return max(0, $fallback);
        }

        return max(0, (int) $countValue);
    }

    private function hasAutosaveRowData(array $row, array $excludeKeys = []): bool
    {
        foreach ($row as $key => $value) {
            if (in_array($key, $excludeKeys, true)) {
                continue;
            }
            if ($value !== null && $value !== '') {
                return true;
            }
        }
        return false;
    }

    public function autosaveDraft(Request $request, string $section)
    {
        $section = strtolower(trim($section));

        switch ($section) {
            case 'c1': {
                $existing = session('form.c1', []);
                if (!is_array($existing)) {
                    $existing = [];
                }
                $incoming = $request->except('_token');
                if (!is_array($incoming)) {
                    $incoming = [];
                }

                // Prevent transient blank autosave payloads from wiping address fields.
                $addressKeys = [
                    'res_house_no',
                    'res_street',
                    'res_sub_vil',
                    'res_brgy',
                    'res_city',
                    'res_province',
                    'res_zipcode',
                    'per_house_no',
                    'per_street',
                    'per_sub_vil',
                    'per_brgy',
                    'per_city',
                    'per_province',
                    'per_zipcode',
                ];
                foreach ($addressKeys as $key) {
                    if (!array_key_exists($key, $incoming)) {
                        continue;
                    }
                    $incomingValue = $incoming[$key];
                    $existingValue = $existing[$key] ?? null;
                    if (
                        is_string($incomingValue)
                        && trim($incomingValue) === ''
                        && is_string($existingValue)
                        && trim($existingValue) !== ''
                    ) {
                        unset($incoming[$key]);
                    }
                }

                session(['form.c1' => array_merge($existing, $incoming)]);
                break;
            }

            case 'c2': {
                $workIds = (array) $request->input('work_exp_id', []);
                $workFrom = (array) $request->input('work_exp_from', []);
                $workTo = (array) $request->input('work_exp_to', []);
                $workPosition = (array) $request->input('work_exp_position', []);
                $workDepartment = (array) $request->input('work_exp_department', []);
                $workStatus = (array) $request->input('work_exp_status', []);
                $workGov = (array) $request->input('work_exp_govt_service', []);

                $workCount = $this->resolveAutosaveCount(
                    $request->input('work_exp_count'),
                    count($workFrom)
                );

                $allWexData = [];
                for ($i = 0; $i < $workCount; $i++) {
                    $row = [
                        'id' => $workIds[$i] ?? null,
                        'user_id' => Auth::id(),
                        'work_exp_from' => trim(strip_tags((string) ($workFrom[$i] ?? ''))),
                        'work_exp_to' => trim(strip_tags((string) ($workTo[$i] ?? ''))),
                        'work_exp_position' => trim(strip_tags((string) ($workPosition[$i] ?? ''))),
                        'work_exp_department' => trim(strip_tags((string) ($workDepartment[$i] ?? ''))),
                        'work_exp_status' => trim(strip_tags((string) ($workStatus[$i] ?? ''))),
                        'work_exp_govt_service' => trim(strip_tags((string) ($workGov[$i] ?? ''))),
                    ];

                    if ($this->hasAutosaveRowData($row, ['id', 'user_id'])) {
                        $allWexData[] = $row;
                    }
                }

                $csIds = (array) $request->input('cs_eligibility_id', []);
                $csCareer = (array) $request->input('cs_eligibility_career', []);
                $csRating = (array) $request->input('cs_eligibility_rating', []);
                $csDate = (array) $request->input('cs_eligibility_date', []);
                $csPlace = (array) $request->input('cs_eligibility_place', []);
                $csLicense = (array) $request->input('cs_eligibility_license', []);
                $csValidity = (array) $request->input('cs_eligibility_validity', []);

                $civilCount = $this->resolveAutosaveCount(
                    $request->input('civil_service_count'),
                    count($csCareer)
                );

                $allCsData = [];
                for ($i = 0; $i < $civilCount; $i++) {
                    $row = [
                        'id' => $csIds[$i] ?? null,
                        'user_id' => Auth::id(),
                        'cs_eligibility_career' => trim(strip_tags((string) ($csCareer[$i] ?? ''))),
                        'cs_eligibility_rating' => trim(strip_tags((string) ($csRating[$i] ?? ''))),
                        'cs_eligibility_date' => trim(strip_tags((string) ($csDate[$i] ?? ''))),
                        'cs_eligibility_place' => trim(strip_tags((string) ($csPlace[$i] ?? ''))),
                        'cs_eligibility_license' => trim(strip_tags((string) ($csLicense[$i] ?? ''))),
                        'cs_eligibility_validity' => trim(strip_tags((string) ($csValidity[$i] ?? ''))),
                    ];

                    if ($this->hasAutosaveRowData($row, ['id', 'user_id'])) {
                        $allCsData[] = $row;
                    }
                }

                session([
                    'form.c2' => [
                        'all_user_work_exps' => $allWexData,
                        'all_user_civil_service_eligibility' => $allCsData,
                    ],
                ]);
                break;
            }

            case 'c3': {
                $entryCountLearning = $this->resolveAutosaveCount($request->input('learning_entry_count'));
                $entryCountVoluntary = $this->resolveAutosaveCount($request->input('voluntary_work_count'));

                $dataLearning = [];
                for ($i = 1; $i <= $entryCountLearning; $i++) {
                    $row = [
                        'learning_title' => trim((string) $request->input("learning_title_$i", '')),
                        'learning_type' => trim((string) $request->input("learning_type_$i", '')),
                        'learning_from' => trim((string) $request->input("learning_from_$i", '')),
                        'learning_to' => trim((string) $request->input("learning_to_$i", '')),
                        'learning_hours' => trim((string) $request->input("learning_hours_$i", '')),
                        'learning_conducted' => trim((string) $request->input("learning_conducted_$i", '')),
                    ];
                    if ($this->hasAutosaveRowData($row)) {
                        $dataLearning[] = $row;
                    }
                }

                $dataVoluntary = [];
                for ($i = 1; $i <= $entryCountVoluntary; $i++) {
                    $row = [
                        'voluntary_org' => trim((string) $request->input("voluntary_org_$i", '')),
                        'voluntary_from' => trim((string) $request->input("voluntary_from_$i", '')),
                        'voluntary_to' => trim((string) $request->input("voluntary_to_$i", '')),
                        'voluntary_hours' => trim((string) $request->input("voluntary_hours_$i", '')),
                        'voluntary_position' => trim((string) $request->input("voluntary_position_$i", '')),
                    ];
                    if ($this->hasAutosaveRowData($row)) {
                        $dataVoluntary[] = $row;
                    }
                }

                $skills = $request->input('skills', []);
                $distinctions = $request->input('distinctions', []);
                $organizations = $request->input('organizations', []);

                if (!is_array($skills)) {
                    $skills = [$skills];
                }
                if (!is_array($distinctions)) {
                    $distinctions = [$distinctions];
                }
                if (!is_array($organizations)) {
                    $organizations = [$organizations];
                }

                $skills = array_values(array_filter($skills, fn($v) => $v !== null && $v !== ''));
                $distinctions = array_values(array_filter($distinctions, fn($v) => $v !== null && $v !== ''));
                $organizations = array_values(array_filter($organizations, fn($v) => $v !== null && $v !== ''));

                session([
                    'data_learning' => $dataLearning,
                    'data_voluntary' => $dataVoluntary,
                    'data_otherInfo' => [
                        'skill' => $skills,
                        'distinction' => $distinctions,
                        'organization' => $organizations,
                        'user_id' => Auth::id(),
                    ],
                ]);
                break;
            }

            case 'c4': {
                $existing = session('form.c4', []);
                if (!is_array($existing)) {
                    $existing = [];
                }
                $incoming = $request->except('_token');
                if (!is_array($incoming)) {
                    $incoming = [];
                }

                $criminalDetails = $request->input('criminal_35_b_details');
                if (is_array($criminalDetails)) {
                    $incoming['criminal_35_b_array'] = $criminalDetails;
                }

                session(['form.c4' => array_merge($existing, $incoming)]);
                break;
            }

            default:
                return response()->json([
                    'ok' => false,
                    'message' => 'Unsupported autosave section.',
                ], 422);
        }

        \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);

        return response()->json([
            'ok' => true,
            'section' => $section,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    // END C4 CONTROLLER
    // ==============================================================================



    /**
     * Stores all files in a local filesystem (subject to change, probably 💩).
     * Handles auto-deletion of existing files and auto-updating on database
     *
     * @param UploadedFile[] $files
     * @return void
     */
    private function c5StoreFilesToDB(array $files, ?string $vacancyId = null): array
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $supportsRevisionTracking = Schema::hasColumn('uploaded_documents', 'revision_requested_count')
            && Schema::hasColumn('uploaded_documents', 'revision_submitted_at');

        $storedPaths = [];
        foreach ($files as $doc_type => $file) {

            $hashed_name = $file->hashName();
            $store_path = $file->store("uploads/pds-files", 'public');
            if ($supportsVacancyScopedDocs && !empty($vacancyId)) {
                // Reuse legacy null-vacancy row when present to preserve revision history/state.
                $document = UploadedDocument::where('user_id', Auth::id())
                    ->where('document_type', $doc_type)
                    ->where(function ($query) use ($vacancyId) {
                        $query->where('vacancy_id', $vacancyId)->orWhereNull('vacancy_id');
                    })
                    ->orderByRaw("CASE WHEN vacancy_id = ? THEN 0 ELSE 1 END", [$vacancyId])
                    ->orderByDesc('updated_at')
                    ->first();

                if (!$document) {
                    $document = UploadedDocument::create([
                        'user_id' => Auth::id(),
                        'vacancy_id' => $vacancyId,
                        'document_type' => $doc_type,
                    ]);
                }
            } else {
                $match = [
                    'user_id' => Auth::id(),
                    'document_type' => $doc_type
                ];
                if ($supportsVacancyScopedDocs) {
                    $match['vacancy_id'] = $vacancyId;
                }

                $document = UploadedDocument::firstOrCreate($match);
            }

            // Auto-delete if file_paths don't match and if an item.
            if (
                !empty($document->storage_path) &&
                ($document->storage_path !== $store_path) &&
                Storage::disk('public')->exists($document->storage_path)
            ) {
                Storage::disk('public')->delete($document->storage_path);
            }

            $updates = [
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $hashed_name,
                'storage_path' => $store_path,
                'mime_type' => $file->getMimeType(),
                'file_size_8b' => $file->getSize(),
                'status' => 'Pending', // Reset status to Pending on new upload
                'remarks' => ''      // Clear old remarks
            ];
            if (
                $supportsRevisionTracking
                && (int) ($document->revision_requested_count ?? 0) > 0
                && empty($document->revision_submitted_at)
            ) {
                $updates['revision_submitted_at'] = now();
            }
            if ($supportsVacancyScopedDocs) {
                $updates['vacancy_id'] = $vacancyId;
            }

            $document->update($updates);
            $this->syncUploadedDocumentToGallery($document);
            $storedPaths[] = $store_path;

        }
        activity()
            ->causedBy(Auth::user())
            ->log('Store C5 form.');

        return $storedPaths;
    }

    private function syncUploadedDocumentToGallery(UploadedDocument $document): void
    {
        if (!Schema::hasTable('document_gallery_items')) {
            return;
        }

        $docType = trim((string) ($document->document_type ?? ''));
        $storagePath = trim((string) ($document->storage_path ?? ''));
        if ($docType === '' || $storagePath === '' || $storagePath === 'NOINPUT') {
            return;
        }

        DocumentGalleryItem::updateOrCreate(
            [
                'user_id' => (int) $document->user_id,
                'document_type' => $docType,
            ],
            [
                'original_name' => (string) ($document->original_name ?: basename($storagePath)),
                'stored_name' => (string) ($document->stored_name ?: basename($storagePath)),
                'storage_path' => $storagePath,
                'mime_type' => (string) ($document->mime_type ?: 'application/pdf'),
                'file_size_8b' => (int) ($document->file_size_8b ?? 0),
            ]
        );
    }

    private function isRevisionStatus(?string $status): bool
    {
        $normalized = strtolower(trim((string) $status));
        return in_array($normalized, ['needs revision', 'disapproved with deficiency'], true);
    }

    private function hasFinalRevisionDisqualification(Applications $application, string $vacancyId): bool
    {
        if ($this->isRevisionStatus($application->file_status) && (int) ($application->file_revision_requested_count ?? 0) >= 2) {
            return true;
        }

        if (!Schema::hasColumn('uploaded_documents', 'revision_requested_count')) {
            return false;
        }

        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $docsQuery = UploadedDocument::query()
            ->where('user_id', $application->user_id)
            ->where('revision_requested_count', '>=', 2);

        if ($supportsVacancyScopedDocs && $vacancyId !== '') {
            $docsQuery->where(function ($q) use ($vacancyId) {
                $q->where('vacancy_id', $vacancyId)
                    ->orWhereNull('vacancy_id');
            });
        }

        $docs = $docsQuery->get(['status']);
        return $docs->contains(fn($doc) => $this->isRevisionStatus($doc->status));
    }

    private function hasUploadedDocumentForType($documents, string $docType): bool
    {
        $candidates = array_merge([$docType], self::DOCUMENT_TYPE_ALIASES[$docType] ?? []);
        foreach ($candidates as $candidate) {
            $doc = $documents[$candidate] ?? null;
            if ($doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT') {
                return true;
            }
        }
        return false;
    }

    private function resolveUploadedDocument($documents, string $docType): ?UploadedDocument
    {
        $doc = $documents[$docType] ?? null;
        if ($doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT') {
            return $doc;
        }

        foreach (self::DOCUMENT_TYPE_ALIASES[$docType] ?? [] as $alias) {
            $aliasDoc = $documents[$alias] ?? null;
            if ($aliasDoc && !empty($aliasDoc->storage_path) && $aliasDoc->storage_path !== 'NOINPUT') {
                return $aliasDoc;
            }
        }

        return $doc instanceof UploadedDocument ? $doc : null;
    }

    private function loadReusableUploadedDocumentsMap(int $userId, ?string $vacancyId = null)
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');

        $query = UploadedDocument::where('user_id', $userId)
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT');

        if ($supportsVacancyScopedDocs && !empty($vacancyId)) {
            $query->orderByRaw(
                "CASE WHEN vacancy_id = ? THEN 0 WHEN vacancy_id IS NULL THEN 1 ELSE 2 END",
                [(string) $vacancyId]
            );
        } elseif ($supportsVacancyScopedDocs) {
            $query->orderByRaw('CASE WHEN vacancy_id IS NULL THEN 0 ELSE 1 END');
        }

        $documents = $query
            ->orderByDesc('updated_at')
            ->get()
            ->unique('document_type')
            ->keyBy('document_type');

        $documents = $this->mergeGalleryDocumentsIntoReusableMap($documents, $userId, $vacancyId);
        return $this->applyDocumentTypeAliasesToMap($documents);
    }

    private function mergeGalleryDocumentsIntoReusableMap(Collection $documents, int $userId, ?string $vacancyId): Collection
    {
        if (!Schema::hasTable('document_gallery_items')) {
            return $documents;
        }

        $galleryItems = DocumentGalleryItem::where('user_id', $userId)
            ->whereNotNull('document_type')
            ->where('document_type', '!=', '')
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT')
            ->orderByDesc('updated_at')
            ->get();

        foreach ($galleryItems as $item) {
            $docType = trim((string) ($item->document_type ?? ''));
            if ($docType === '' || $documents->has($docType)) {
                continue;
            }

            $documents->put($docType, new UploadedDocument([
                'user_id' => $userId,
                'vacancy_id' => $vacancyId,
                'document_type' => $docType,
                'original_name' => (string) ($item->original_name ?? ''),
                'stored_name' => (string) ($item->stored_name ?? ''),
                'storage_path' => (string) ($item->storage_path ?? ''),
                'mime_type' => (string) ($item->mime_type ?? 'application/pdf'),
                'file_size_8b' => (int) ($item->file_size_8b ?? 0),
                'status' => 'Pending',
                'remarks' => '',
            ]));
        }

        return $documents;
    }

    private function applyDocumentTypeAliasesToMap(Collection $documents): Collection
    {
        foreach (self::DOCUMENT_TYPE_ALIASES as $canonical => $aliases) {
            if ($documents->has($canonical)) {
                continue;
            }

            foreach ($aliases as $alias) {
                $aliasDoc = $documents->get($alias);
                if ($aliasDoc instanceof UploadedDocument && !empty($aliasDoc->storage_path) && $aliasDoc->storage_path !== 'NOINPUT') {
                    $documents->put($canonical, $aliasDoc);
                    break;
                }
            }
        }

        return $documents;
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
            if ($this->hasUploadedDocumentForType($vacancyDocs, (string) $docType)) {
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

    private function hasCompleteRequiredDocsForVacancy(
        int $userId,
        string $vacancyId,
        string $docTrack,
        array $requiredDocsByTrack
    ): bool {
        $requiredDocs = $requiredDocsByTrack[$docTrack] ?? [];
        if (empty($requiredDocs)) {
            return false;
        }

        $documents = $this->loadReusableUploadedDocumentsMap($userId, $vacancyId);

        $hasApplicationLetterInApplications = Applications::where('user_id', $userId)
            ->whereNotNull('file_storage_path')
            ->exists();

        foreach ($requiredDocs as $docType) {
            if ($docType === 'application_letter' && $hasApplicationLetterInApplications) {
                continue;
            }

            if (!$this->hasUploadedDocumentForType($documents, $docType)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Displays the C5 page for PDS.
     * @return \Illuminate\Contracts\View\View
     */
    public function c5DisplayForm()
    {
        $user = Auth::user();

        // ✅ Fix the quote in the view name
        $latestApplication = Applications::where('user_id', $user->id)
            ->with('vacancy')
            ->latest()
            ->first();

        $applicationVacancyId = request('vacancy_id');
        $vacancyForApplication = null;
        if (!empty($applicationVacancyId)) {
            $vacancyForApplication = Models\JobVacancy::where('vacancy_id', $applicationVacancyId)->first();
            if (!$vacancyForApplication) {
                return redirect()->back()->withErrors(['vacancy_id' => 'Selected vacancy was not found.']);
            }
        }
        $hasExistingApplicationLetter = Applications::where('user_id', $user->id)
            ->whereNotNull('file_storage_path')
            ->exists();

        // Reuse previously uploaded files from prior applications.
        $documents = $this->loadReusableUploadedDocumentsMap(
            (int) $user->id,
            !empty($applicationVacancyId) ? (string) $applicationVacancyId : null
        );

        $defaultDocTrack = request('doc_track');
        if ($vacancyForApplication) {
            $defaultDocTrack = strcasecmp((string) $vacancyForApplication->vacancy_type, 'COS') === 0 ? 'COS' : 'Plantilla';
        }
        if (!in_array($defaultDocTrack, ['COS', 'Plantilla'], true)) {
            $defaultDocTrack = $latestApplication?->vacancy?->vacancy_type;
        }
        if (!in_array($defaultDocTrack, ['COS', 'Plantilla'], true)) {
            $defaultDocTrack = 'Plantilla';
        }

        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        $documentLabels = $this->getDocumentLabelMap();
        $isFreshUpload = in_array(request('fresh_upload'), [1, '1', true, 'true'], true) || !empty($applicationVacancyId);
        $hasFreshUploadForVacancy = false;
        if (!empty($applicationVacancyId)) {
            $hasFreshUploadForVacancy = $this->hasCompleteRequiredDocsForVacancy(
                (int) $user->id,
                (string) $applicationVacancyId,
                (string) $defaultDocTrack,
                $requiredDocsByTrack
            );

            Log::info('C5 display with vacancy context', [
                'user_id' => (int) $user->id,
                'vacancy_id' => (string) $applicationVacancyId,
                'doc_track' => $defaultDocTrack,
                'forced_fresh_upload' => $isFreshUpload,
                'has_complete_required_docs' => $hasFreshUploadForVacancy,
            ]);
        }

        return view('pds.c5', compact(
            'documents',
            'defaultDocTrack',
            'requiredDocsByTrack',
            'documentLabels',
            'hasExistingApplicationLetter',
            'applicationVacancyId',
            'isFreshUpload',
            'hasFreshUploadForVacancy'
        ));
    }



    /**
     * The transition functionality for submission of data. This should perform
     * the uploading of files to the filesystem and createing/updating metadata
     * for that specific file for future retrieval.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\RedirectResponse
     */
    public function finalizePDS(Request $request, $go_to)
    {
        $request->validate(
            [
                'declaration' => 'accepted',
                'consent' => 'accepted',
                'confirmation' => 'accepted',
            ],
            [
                'declaration.accepted' => 'Please check the declaration checkbox to continue.',
                'consent.accepted' => 'Please check the consent checkbox to continue.',
                'confirmation.accepted' => 'Please check the confirmation checkbox to continue.',
            ]
        );

        $docTrack = $request->input('doc_track', 'Plantilla');
        if (!in_array($docTrack, ['COS', 'Plantilla'], true)) {
            $docTrack = 'Plantilla';
        }
        $applicationVacancyId = $request->input('vacancy_id');
        $refererVacancyId = $this->extractVacancyIdFromReferer($request->headers->get('referer'));
        if (!empty($refererVacancyId) && $refererVacancyId !== $applicationVacancyId) {
            Log::warning('C5 vacancy_id mismatch detected; using referer vacancy_id', [
                'user_id' => Auth::id(),
                'posted_vacancy_id' => $applicationVacancyId,
                'referer_vacancy_id' => $refererVacancyId,
                'referer' => $request->headers->get('referer'),
            ]);
            $applicationVacancyId = $refererVacancyId;
        }

        if (!empty($applicationVacancyId)) {
            Log::info('C5 finalize vacancy context', [
                'user_id' => Auth::id(),
                'vacancy_id' => $applicationVacancyId,
                'go_to' => $go_to,
                'fresh_upload' => $request->input('fresh_upload'),
            ]);
        }
        $vacancyForApplication = null;
        if (!empty($applicationVacancyId)) {
            $vacancyForApplication = Models\JobVacancy::where('vacancy_id', $applicationVacancyId)->first();
            if (!$vacancyForApplication) {
                return back()->withErrors(['vacancy_id' => 'Selected vacancy was not found.'])->withInput();
            }
            $docTrack = strcasecmp((string) $vacancyForApplication->vacancy_type, 'COS') === 0 ? 'COS' : 'Plantilla';
        }

        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        $requiredDocs = $requiredDocsByTrack[$docTrack];
        $documentLabels = $this->getDocumentLabelMap();
        $existingDocs = $this->loadReusableUploadedDocumentsMap(
            (int) Auth::id(),
            !empty($applicationVacancyId) ? (string) $applicationVacancyId : null
        );

        $existingDocLookup = [];
        foreach ($requiredDocs as $docType) {
            if ($this->hasUploadedDocumentForType($existingDocs, $docType)) {
                $existingDocLookup[$docType] = true;
            }
        }

        // Count previously uploaded application letter from Applications records as existing.
        if (in_array('application_letter', $requiredDocs, true) && !isset($existingDocLookup['application_letter'])) {
            $hasApplicationLetter = Applications::where('user_id', Auth::id())
                ->whereNotNull('file_storage_path')
                ->exists();
            if ($hasApplicationLetter) {
                $existingDocLookup['application_letter'] = true;
            }
        }

        $missingRequiredDocs = [];
        foreach ($requiredDocs as $docType) {
            if (!$request->hasFile("cert_uploads.$docType") && !isset($existingDocLookup[$docType])) {
                $missingRequiredDocs[] = $docType;
            }
        }
        if (!empty($missingRequiredDocs)) {
            Log::warning('C5 missing required documents', [
                'user_id' => Auth::id(),
                'vacancy_id' => $applicationVacancyId,
                'doc_track' => $docTrack,
                'missing_docs' => $missingRequiredDocs,
                'uploaded_keys' => array_keys($request->file('cert_uploads', [])),
            ]);
            $errors = [];
            foreach ($missingRequiredDocs as $docType) {
                $label = $documentLabels[$docType] ?? str_replace('_', ' ', $docType);
                $errors["cert_uploads.$docType"] = "{$label} is required for {$docTrack} applications.";
            }
            return back()->withErrors($errors)->withInput();
        }

        /********************************
         * +++++ Required Documents
         ********************************/
        // User is allowed to not upload any file
        $request->validate([
            'cert_uploads.application_letter' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.pqe_result' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_eligibility' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_elegibility' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.ipcr' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.non_academic' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_training' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.designation_order' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.transcript_records' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.photocopy_diploma' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.grade_masteraldoctorate' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.tor_masteraldoctorate' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_employment' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.other_documents' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.signed_pds' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.signed_work_exp_sheet' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_lgoo_induction' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.passport_photo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240'
        ], [
            'cert_uploads.*.mimes' => 'Only PDF files are allowed.',
            'cert_uploads.*.max' => 'Each file must be 10MB or smaller.',
        ]);

        $normalizedUploads = $request->file('cert_uploads', []);
        if (isset($normalizedUploads['cert_elegibility']) && !isset($normalizedUploads['cert_eligibility'])) {
            $normalizedUploads['cert_eligibility'] = $normalizedUploads['cert_elegibility'];
            unset($normalizedUploads['cert_elegibility']);
        }
        $request->files->set('cert_uploads', $normalizedUploads);

        $uploaded_files = [];
        $files_with_upload_errors = [];
        $upload_errors = [];
        foreach (UploadedDocument::DOCUMENTS as $_access) {

            // Files not present in the request should not be processed.
            if (!$request->hasFile("cert_uploads.$_access")) {
                continue;
            }

            // Check if the requested file has any upload errors.
            $_file = $request->file("cert_uploads.$_access");
            if (!$_file->isValid()) {
                $files_with_upload_errors[] = $_access;
                continue;
            }

            $allowImage = $_access === 'passport_photo';
            [$is_valid, $message] = $this->validateUploadedFile($_file, $allowImage);
            if (!$is_valid) {
                $upload_errors["cert_uploads.$_access"] = $message;
                continue;
            }

            [$scan_ok, $scan_message] = $this->scanUploadedFile($_file);
            if (!$scan_ok) {
                $upload_errors["cert_uploads.$_access"] = $scan_message;
                continue;
            }

            $uploaded_files[$_access] = $_file;
        }

        if (!empty($files_with_upload_errors)) {
            foreach ($files_with_upload_errors as $field) {
                $upload_errors["cert_uploads.$field"] = 'Upload failed. Please try again.';
            }
        }

        if (!empty($upload_errors)) {
            return back()->withErrors($upload_errors);
        }

        $storedPaths = [];
        try {
            return DB::transaction(function () use ($request, $uploaded_files, &$storedPaths, $go_to, $applicationVacancyId, $docTrack, $requiredDocs) {
                $storedPaths = $this->c5StoreFilesToDB($uploaded_files, $applicationVacancyId);
                if (!empty($applicationVacancyId)) {
                    $this->seedVacancyDocumentsFromReusableUploads(
                        (int) Auth::id(),
                        (string) $applicationVacancyId,
                        $requiredDocs
                    );
                }

                // Persist declaration/consent only when DB columns exist.
                if (Schema::hasTable('misc_infos')) {
                    $miscInfoUpdates = [];
                    if (Schema::hasColumn('misc_infos', 'declaration')) {
                        $miscInfoUpdates['declaration'] = $request->boolean('declaration') ? '1' : '0';
                    }
                    if (Schema::hasColumn('misc_infos', 'consent')) {
                        $miscInfoUpdates['consent'] = $request->boolean('consent') ? '1' : '0';
                    }

                    if (!empty($miscInfoUpdates)) {
                        $misc_info = MiscInfos::firstOrCreate(['user_id' => Auth::id()]);
                        $misc_info->update($miscInfoUpdates);
                    }
                }
                
                if (app()->environment('testing') && $request->boolean('simulate_failure')) {
                    throw new \RuntimeException('Simulated failure');
                }

                $hasSessionPayload = static function (string $key): bool {
                    $payload = session($key);
                    if (is_array($payload)) {
                        return !empty($payload);
                    }

                    return !empty($payload);
                };

                $shouldPersistPdsData = $hasSessionPayload('form.c1')
                    || $hasSessionPayload('form.c2')
                    || $hasSessionPayload('data_learning')
                    || $hasSessionPayload('data_voluntary')
                    || $hasSessionPayload('data_otherInfo')
                    || $hasSessionPayload('form.c4');

                if ($shouldPersistPdsData) {
                //********************************
                //* +++++ Personal Information
                //*******************************
                $c1_form_data = array_merge([
                    'surname' => '',
                    'first_name' => '',
                    'middle_name' => '',
                    'name_extension' => '',
                    'civil_status' => '',
                    'date_of_birth' => '',
                    'place_of_birth' => '',
                    'citizenship' => '',
                    'dual_type' => '',
                    'sex' => '',
                    'blood_type' => '',
                    'philhealth_no' => '',
                    'tin_no' => '',
                    'agency_employee_no' => '',
                    'gsis_id_no' => '',
                    'pagibig_id_no' => '',
                    'sss_id_no' => '',
                    'mobile_no' => '',
                    'email_address' => '',
                    'height' => '',
                    'weight' => '',
                    'telephone_no' => '',
                    'dual_country' => '',
                    'spouse_surname' => '',
                    'spouse_first_name' => '',
                    'spouse_middle_name' => '',
                    'spouse_name_extension' => '',
                    'spouse_occupation' => '',
                    'spouse_employer' => '',
                    'spouse_business_address' => '',
                    'spouse_telephone' => '',
                    'father_surname' => '',
                    'father_first_name' => '',
                    'father_middle_name' => '',
                    'father_name_extension' => '',
                    'mother_maiden_surname' => '',
                    'mother_maiden_first_name' => '',
                    'mother_maiden_middle_name' => '',
                    'res_house_no' => '',
                    'res_street' => '',
                    'res_sub_vil' => '',
                    'res_brgy' => '',
                    'res_city' => '',
                    'res_province' => '',
                    'res_zipcode' => '',
                    'per_house_no' => '',
                    'per_street' => '',
                    'per_sub_vil' => '',
                    'per_brgy' => '',
                    'per_city' => '',
                    'per_province' => '',
                    'per_zipcode' => '',
                    'elem_from' => '',
                    'elem_to' => '',
                    'elem_school' => '',
                    'elem_academic_honors' => '',
                    'elem_basic' => '',
                    'elem_earned' => '',
                    'elem_year_graduated' => '',
                    'jhs_from' => '',
                    'jhs_to' => '',
                    'jhs_school' => '',
                    'jhs_academic_honors' => '',
                    'jhs_basic' => '',
                    'jhs_earned' => '',
                    'jhs_year_graduated' => '',
                    'children' => [],
                    'vocational' => [],
                    'college' => [],
                    'grad' => [],
                    'declaration' => $request->input('declaration', '0'),
                    'consent' => $request->input('consent', '0'),
                    'confirmation' => $request->input('confirmation', '0'),
                ], session('form.c1', []));

                $dual_type_t = '';
                if ($c1_form_data['citizenship'] === 'Dual Citizen') {
                    $dual_type_t = $c1_form_data['dual_type'];
                }

                $_haystack = ['children', 'vocational', 'college', 'grad'];
                foreach ($c1_form_data as $_key => $_val) {

                    // Skips the haystack data to be handled later.
                    if (in_array($_key, $_haystack)) {
                        continue;
                    }

                    if (is_array($_val)) {
                        $flattened = [];

                        array_walk_recursive($_val, function ($v) use (&$flattened) {
                            // Only push if it's a string or something castable to string
                            if (is_scalar($v)) {
                                $flattened[] = strip_tags($v);
                            }
                        });

                        $_val = trim(implode(', ', $flattened));
                    } else {
                        $_val = trim(strip_tags($_val));
                    }

                }

                // format residential address for compact database insertion
                $house_no_t = ($c1_form_data['res_house_no'] != '') ? $c1_form_data['res_house_no'] : '{*}';
                $street_t = ($c1_form_data['res_street'] != '') ? $c1_form_data['res_street'] : '{*}';
                $sub_vil_t = ($c1_form_data['res_sub_vil'] != '') ? $c1_form_data['res_sub_vil'] : '{*}';
                $brgy_t = ($c1_form_data['res_brgy'] != '') ? $c1_form_data['res_brgy'] : '{*}';
                $city_t = ($c1_form_data['res_city'] != '') ? $c1_form_data['res_city'] : '{*}';
                $province_t = ($c1_form_data['res_province'] != '') ? $c1_form_data['res_province'] : '{*}';
                $zipcode_t = ($c1_form_data['res_zipcode'] != '') ? $c1_form_data['res_zipcode'] : '{*}';

                $formatted_residential_address = "$house_no_t/|/$street_t/|/$sub_vil_t/|/$brgy_t/|/$city_t/|/$province_t/|/$zipcode_t";

                // format permanent address
                $house_no_t = ($c1_form_data['per_house_no'] != '') ? $c1_form_data['per_house_no'] : '{*}';
                $street_t = ($c1_form_data['per_street'] != '') ? $c1_form_data['per_street'] : '{*}';
                $sub_vil_t = ($c1_form_data['per_sub_vil'] != '') ? $c1_form_data['per_sub_vil'] : '{*}';
                $brgy_t = ($c1_form_data['per_brgy'] != '') ? $c1_form_data['per_brgy'] : '{*}';
                $city_t = ($c1_form_data['per_city'] != '') ? $c1_form_data['per_city'] : '{*}';
                $province_t = ($c1_form_data['per_province'] != '') ? $c1_form_data['per_province'] : '{*}';
                $zipcode_t = ($c1_form_data['per_zipcode'] != '') ? $c1_form_data['per_zipcode'] : '{*}';

                $formatted_permanent_address = "$house_no_t/|/$street_t/|/$sub_vil_t/|/$brgy_t/|/$city_t/|/$province_t/|/$zipcode_t";

                // create a personal information record compact database insertion
                // IF the record does not exist for the current user.
                $user_personal_info = Models\PersonalInformation::firstOrCreate([
                    'user_id' => Auth::id()
                ]);

                $dateOfBirthForUpdate = $this->normalizeDateForDatabase($c1_form_data['date_of_birth']);
                if (empty($dateOfBirthForUpdate)) {
                    $dateOfBirthForUpdate = !empty($user_personal_info->date_of_birth)
                        ? (string) $user_personal_info->date_of_birth
                        : Carbon::now()->toDateString();
                }

                $user_personal_info->update([
                    //'cs_id_no'                  => $c1_form_data['cs_id_no'],
                    'surname' => $c1_form_data['surname'],
                    'name_extension' => $c1_form_data['name_extension'],
                    'first_name' => $c1_form_data['first_name'],
                    'middle_name' => $c1_form_data['middle_name'],
                    'sex' => $c1_form_data['sex'],
                    'civil_status' => $c1_form_data['civil_status'],
                    'date_of_birth' => $dateOfBirthForUpdate,
                    'place_of_birth' => $c1_form_data['place_of_birth'],
                    'height' => $c1_form_data['height'],
                    'weight' => $c1_form_data['weight'],
                    'blood_type' => $c1_form_data['blood_type'],
                    'philhealth_no' => $c1_form_data['philhealth_no'],
                    'tin_no' => $c1_form_data['tin_no'],
                    'agency_employee_no' => $c1_form_data['agency_employee_no'],
                    'gsis_id_no' => $c1_form_data['gsis_id_no'],
                    'pagibig_id_no' => $c1_form_data['pagibig_id_no'],
                    'sss_id_no' => $c1_form_data['sss_id_no'],
                    'citizenship' => $c1_form_data['citizenship'],
                    'dual_type' => $dual_type_t,
                    'dual_country' => $c1_form_data['dual_country'],
                    'residential_address' => $formatted_residential_address,
                    'permanent_address' => $formatted_permanent_address,
                    'telephone_no' => $c1_form_data['telephone_no'],
                    'mobile_no' => $c1_form_data['mobile_no'],
                    'email_address' => $c1_form_data['email_address']
                ]);

                unset($user_personal_info);

                //********************************
                //* +++++ Family Background
                //*******************************

                $user_family_bg = Models\FamilyBackground::firstOrCreate([
                    'user_id' => Auth::id()
                ]);

                $user_family_bg->update([
                    'spouse_surname' => $c1_form_data['spouse_surname'],
                    'spouse_first_name' => $c1_form_data['spouse_first_name'],
                    'spouse_middle_name' => $c1_form_data['spouse_middle_name'],
                    'spouse_name_extension' => $c1_form_data['spouse_name_extension'],
                    'spouse_occupation' => $c1_form_data['spouse_occupation'],
                    'spouse_employer' => $c1_form_data['spouse_employer'],
                    'spouse_business_address' => $c1_form_data['spouse_business_address'],
                    'spouse_telephone' => $c1_form_data['spouse_telephone'],
                    'father_surname' => $c1_form_data['father_surname'],
                    'father_first_name' => $c1_form_data['father_first_name'],
                    'father_middle_name' => $c1_form_data['father_middle_name'],
                    'father_name_extension' => $c1_form_data['father_name_extension'],
                    'mother_maiden_surname' => $c1_form_data['mother_maiden_surname'],
                    'mother_maiden_first_name' => $c1_form_data['mother_maiden_first_name'],
                    'mother_maiden_middle_name' => $c1_form_data['mother_maiden_middle_name'],
                    'children_info' => $c1_form_data['children']
                ]);

                unset($user_family_bg);

                //********************************
                //* +++++ Educational Background
                //********************************

                $user_educational_bg = Models\EducationalBackground::firstOrCreate([
                    'user_id' => Auth::id()
                ]);

                $user_educational_bg->update([
                    'elem_from' => $c1_form_data['elem_from'],
                    'elem_to' => $c1_form_data['elem_to'],
                    'elem_school' => $c1_form_data['elem_school'],
                    'elem_academic_honors' => $c1_form_data['elem_academic_honors'],
                    'elem_basic' => $c1_form_data['elem_basic'],
                    'elem_earned' => $c1_form_data['elem_earned'],
                    'elem_year_graduated' => $c1_form_data['elem_year_graduated'],

                    'jhs_from' => $c1_form_data['jhs_from'],
                    'jhs_to' => $c1_form_data['jhs_to'],
                    'jhs_school' => $c1_form_data['jhs_school'],
                    'jhs_academic_honors' => $c1_form_data['jhs_academic_honors'],
                    'jhs_basic' => $c1_form_data['jhs_basic'],
                    'jhs_earned' => $c1_form_data['jhs_earned'],
                    'jhs_year_graduated' => $c1_form_data['jhs_year_graduated'],

                    /*
                    'shs_from'                  => $c1_form_data['shs_from'],
                    'shs_to'                    => $c1_form_data['shs_to'],
                    'shs_school'                => $c1_form_data['shs_school'],
                    'shs_academic_honors'       => $c1_form_data['shs_academic_honors'],
                    'shs_basic'                 => $c1_form_data['shs_basic'],
                    'shs_earned'                => $c1_form_data['shs_earned'],
                    'shs_year_graduated'        => $c1_form_data['shs_year_graduated'],
                    */

                    'vocational' => $c1_form_data['vocational'] ?? null,
                    'college' => $c1_form_data['college'],
                    'grad' => $c1_form_data['grad'] ?? null,
                ]);

                unset($user_educational_bg);
                // TODO: Fix null new user null required values. Create a middleware so that they cant skip ahead to c5

                // -------------
                // C2 INSERT TO DATABASE
                // ------------
                //********************************
                //* +++++ Work Experience
                //*******************************
                $stripAuditColumns = function (array $row): array {
                    unset($row['id'], $row['created_at'], $row['updated_at'], $row['deleted_at']);
                    return $row;
                };

                $c2_form_data = session('form.c2');
                if (isset($c2_form_data['all_user_work_exps'])) {
                    $user_all_wex_data = $c2_form_data['all_user_work_exps'];

                    WorkExperience::where('user_id', Auth::id())->delete();

                    for ($i = 0; $i < count($user_all_wex_data); $i++) {
                        $workRow = is_array($user_all_wex_data[$i]) ? $stripAuditColumns($user_all_wex_data[$i]) : [];
                        if (empty($workRow)) {
                            continue;
                        }

                        $workRow['user_id'] = Auth::id();
                        $workRow['work_exp_from'] = $this->normalizeDateForDatabase($workRow['work_exp_from'] ?? null);
                        $workRow['work_exp_to'] = $this->normalizeDateForDatabase($workRow['work_exp_to'] ?? null);

                        WorkExperience::upsert($workRow, 'id');
                    }
                }

                //********************************
                //* +++++ Civil Service Eligibility
                //*******************************
                if (isset($c2_form_data['all_user_civil_service_eligibility'])) {
                    $user_all_cs_data = $c2_form_data['all_user_civil_service_eligibility'];

                    CivilServiceEligibility::where('user_id', Auth::id())->delete();
                    for ($i = 0; $i < sizeof($user_all_cs_data); $i++) {
                        $civilServiceRow = is_array($user_all_cs_data[$i]) ? $stripAuditColumns($user_all_cs_data[$i]) : [];
                        if (empty($civilServiceRow)) {
                            continue;
                        }

                        $civilServiceRow['user_id'] = Auth::id();
                        $civilServiceRow['cs_eligibility_date'] = $this->normalizeDateForDatabase($civilServiceRow['cs_eligibility_date'] ?? null);
                        $civilServiceRow['cs_eligibility_validity'] = $this->normalizeDateForDatabase($civilServiceRow['cs_eligibility_validity'] ?? null);

                        CivilServiceEligibility::upsert($civilServiceRow, 'id');
                    }
                }

                // C3 INSERT TO DATABASE
                //LEARNING AND DEVELOPMENT
                $c3_learning_and_development_data = session('data_learning');
                //dd(session('data_learning'));

                if (!empty($c3_learning_and_development_data)) {
                    foreach ($c3_learning_and_development_data as $idx => $row) {
                        if (!is_array($row)) {
                            unset($c3_learning_and_development_data[$idx]);
                            continue;
                        }
                        $row = $stripAuditColumns($row);
                        $row['user_id'] = Auth::id();
                        $row['learning_from'] = $this->normalizeDateForDatabase($row['learning_from'] ?? null);
                        $row['learning_to'] = $this->normalizeDateForDatabase($row['learning_to'] ?? null);
                        $c3_learning_and_development_data[$idx] = $row;
                    }
                    $c3_learning_and_development_data = array_values($c3_learning_and_development_data);

                    LearningAndDevelopment::where('user_id', Auth::id())->delete();
                    LearningAndDevelopment::upsert(
                        $c3_learning_and_development_data,
                        ['learning_title', 'learning_from', 'user_id'], // Unique constraint
                        ['learning_type', 'learning_hours', 'learning_to', 'learning_conducted'] // Fields to update
                    );
                }

                //VOLUNTARY WORK
                $c3_voluntary_data = session('data_voluntary');
                if (!empty($c3_voluntary_data)) {
                    foreach ($c3_voluntary_data as $idx => $row) {
                        if (!is_array($row)) {
                            unset($c3_voluntary_data[$idx]);
                            continue;
                        }
                        $row = $stripAuditColumns($row);
                        $row['user_id'] = Auth::id();
                        $row['voluntary_from'] = $this->normalizeDateForDatabase($row['voluntary_from'] ?? null);
                        $row['voluntary_to'] = $this->normalizeDateForDatabase($row['voluntary_to'] ?? null);
                        $c3_voluntary_data[$idx] = $row;
                    }
                    $c3_voluntary_data = array_values($c3_voluntary_data);

                    VoluntaryWork::where('user_id', Auth::id())->delete();
                    VoluntaryWork::upsert(
                        $c3_voluntary_data,
                        ['voluntary_org', 'voluntary_from', 'user_id'], // Unique constraint
                        ['voluntary_to', 'voluntary_hours', 'voluntary_position'] // Fields to update
                    );
                }
                //OTHER INFORMATION

                $c3_other_information_data = session('data_otherInfo');
                if (!empty($c3_other_information_data)) {
                    $user_other_info = OtherInformation::firstOrCreate([
                        'user_id' => Auth::id()
                    ]);
                    $user_other_info->update([
                        'user_id' => Auth::id(),
                        'skill' => $c3_other_information_data['skill'],
                        'distinction' => $c3_other_information_data['distinction'],
                        'organization' => $c3_other_information_data['organization'],
                    ]);
                }

                //C4 INSERT TO DATABASE
                $c4_misc_info_data = session('form.c4');
                if (!empty($c4_misc_info_data)) {
                    unset($c4_misc_info_data['criminal_35_b_array']); // criminal_35_b_array is not part of the database
                    $misc_info_data = MiscInfos::firstOrCreate([
                        'user_id' => Auth::id()
                    ]);
                    $misc_info_data->update($c4_misc_info_data);
                }
                } else {
                    Log::info('C5 finalize skipped C1-C4 persistence due to empty form session payload', [
                        'user_id' => Auth::id(),
                        'vacancy_id' => $applicationVacancyId,
                    ]);
                }



                // --- NOTIFICATION TRIGGER ---
                // Collect uploaded document types
                $uploadedDocTypes = array_keys($uploaded_files);

                if (!empty($uploadedDocTypes)) {
                    try {
                        $user = Auth::user();
                        // Find latest active application to get context (Vacancy Title)
                        $latestApplication = \App\Models\Applications::where('user_id', $user->id)
                            ->latest()
                            ->with('vacancy')
                            ->first();

                        $vacancyTitle = $latestApplication ? $latestApplication->vacancy->position_title : 'General Update';
                        $vacancyId = $latestApplication ? $latestApplication->vacancy_id : null;

                        $admins = \App\Models\Admin::all();
                        foreach ($admins as $admin) {
                            $admin->notify(new \App\Notifications\DocumentUploadedNotification(
                                $user->name,
                                $uploadedDocTypes,
                                $vacancyTitle,
                                $user->id,
                                $vacancyId
                            ));
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to send document upload notification: ' . $e->getMessage());
                    }
                }

                activity()
                    ->causedBy(Auth::user())
                    ->event('save')
                    ->log('Finalized PDS submission.');

                if (!empty($applicationVacancyId)) {
                    $vacancyUploads = session('vacancy_doc_uploads', []);
                    $vacancyUploads[$applicationVacancyId] = [
                        'user_id' => Auth::id(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                    session(['vacancy_doc_uploads' => $vacancyUploads]);

                    $submissionResult = $this->createOrUpdateApplicationFromVacancyUploads((string) $applicationVacancyId);
                    if (!$submissionResult['ok']) {
                        Log::warning('C5 application submit blocked', [
                            'user_id' => Auth::id(),
                            'vacancy_id' => $applicationVacancyId,
                            'reason' => $submissionResult['message'],
                        ]);
                        return redirect()
                            ->route('display_c5', [
                                'doc_track' => $docTrack,
                                'vacancy_id' => $applicationVacancyId,
                                'simple' => 1,
                            ])
                            ->withErrors(['cert_uploads.application_letter' => $submissionResult['message']]);
                    }

                    return redirect()
                        ->route('my_applications')
                        ->with('success', $submissionResult['created']
                            ? 'Application submitted successfully!'
                            : 'Application updated successfully.');
                }

                if ($go_to === 'job_description') {
                    $redirectVacancyId = $request->input('redirect_vacancy_id', $applicationVacancyId);
                    if (!empty($redirectVacancyId)) {
                        return redirect()
                            ->route('job_description', ['id' => $redirectVacancyId])
                            ->with('success', 'Required documents uploaded. You can now continue your application.');
                    }
                    return redirect()->route('job_vacancy');
                }

                return redirect()->route($go_to);
            });
        } catch (\Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            Log::error('PDS finalize failed', [
                'user_id' => Auth::id(),
                'go_to' => $go_to,
                'vacancy_id' => $request->input('vacancy_id'),
                'doc_track' => $request->input('doc_track'),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->withErrors(['cert_uploads' => 'Upload failed. Please try again.']);
        }
    } // END finalize PDS

    private function createOrUpdateApplicationFromVacancyUploads(string $vacancyId): array
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');

        $applicationLetterDocQuery = UploadedDocument::where('user_id', Auth::id())
            ->where('document_type', 'application_letter')
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT');
        if ($supportsVacancyScopedDocs) {
            $applicationLetterDocQuery->orderByRaw(
                "CASE WHEN vacancy_id = ? THEN 0 WHEN vacancy_id IS NULL THEN 1 ELSE 2 END",
                [$vacancyId]
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
                            'vacancy_id' => $vacancyId,
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
            return [
                'ok' => false,
                'created' => false,
                'message' => 'Application Letter is required before submitting your application.',
            ];
        }

        if (
            $supportsVacancyScopedDocs
            && (string) ($applicationLetterDoc->vacancy_id ?? '') !== $vacancyId
        ) {
            $applicationLetterDoc = $this->upsertVacancyDocumentFromSource(
                $applicationLetterDoc,
                $vacancyId,
                'application_letter'
            );
        }

        $applicationPayload = [
            'file_original_name' => $applicationLetterDoc->original_name,
            'file_stored_name' => $applicationLetterDoc->stored_name,
            'file_storage_path' => $applicationLetterDoc->storage_path,
            'file_status' => 'Submitted',
            'file_remarks' => null,
            'file_size_8b' => $applicationLetterDoc->file_size_8b,
            'is_valid' => true,
        ];

        $application = Applications::where('user_id', Auth::id())
            ->where('vacancy_id', $vacancyId)
            ->first();

        if ($application) {
            if (ApplicationStatus::equals($application->status, ApplicationStatus::COMPLIANCE)) {
                $statusTransitions = app(ApplicationStatusTransitionService::class);
                if ($statusTransitions->canTransition($application->status, ApplicationStatus::UPDATED->value)) {
                    $applicationPayload['status'] = ApplicationStatus::UPDATED->value;
                }
            }

            $application->update($applicationPayload);

            Log::info('C5 application submit updated existing application', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancyId,
                'application_id' => $application->id,
            ]);

            return [
                'ok' => true,
                'created' => false,
                'message' => 'Application updated successfully.',
            ];
        }

        $application = Applications::create(array_merge($applicationPayload, [
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancyId,
            'status' => ApplicationStatus::PENDING->value,
        ]));

        $vacancy = Models\JobVacancy::where('vacancy_id', $vacancyId)->first();
        if ($vacancy) {
            $admins = \App\Models\Admin::all();
            foreach ($admins as $admin) {
                \App\Models\Notification::create([
                    'notifiable_type' => 'App\Models\Admin',
                    'notifiable_id' => $admin->id,
                    'type' => 'warning',
                    'data' => [
                        'title' => 'New Job Application',
                        'message' => Auth::user()->name . ' submitted an application for ' . $vacancy->position_title . '.',
                        'link' => route('admin.applicant_status', ['user_id' => Auth::id(), 'vacancy_id' => $vacancyId], false),
                        'section' => 'Application List',
                        'category' => 'document_verification',
                        'user_id' => Auth::id(),
                        'vacancy_id' => $vacancyId,
                    ],
                    'read_at' => null,
                ]);
            }

            activity()
                ->event('apply job')
                ->causedBy(Auth::user())
                ->performedOn($vacancy)
                ->withProperties(['vacancy_id' => $vacancyId, 'section' => 'Job Vacancy'])
                ->log('Applied to job vacancy.');
        }

        Log::info('C5 application submit created application', [
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancyId,
            'application_id' => $application->id,
        ]);

        return [
            'ok' => true,
            'created' => true,
            'message' => 'Application submitted successfully!',
        ];
    }

    private function validateUploadedFile(UploadedFile $file, bool $allowImage): array
    {
        $path = $file->getRealPath();
        if (!$path || !is_file($path)) {
            return [false, 'Unable to read uploaded file.'];
        }

        if ($file->getSize() === 0) {
            return [false, 'The file appears to be empty.'];
        }

        $mimeType = $this->resolveMimeType($path) ?: $file->getClientMimeType();

        if ($allowImage && $this->isAllowedImageMime($mimeType)) {
            return [true, null];
        }

        if (!$this->isAllowedPdfMime($mimeType)) {
            return [false, 'Only PDF files are allowed.'];
        }

        if (!$this->hasPdfHeader($path)) {
            return [false, 'Invalid PDF file content.'];
        }

        return [true, null];
    }

    private function resolveMimeType(string $path): ?string
    {
        if (!class_exists(\finfo::class)) {
            return null;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->file($path);
        return $type ?: null;
    }

    private function hasPdfHeader(string $path): bool
    {
        $handle = @fopen($path, 'rb');
        if (!$handle) {
            return false;
        }
        $header = fread($handle, 8);
        fclose($handle);
        if (!$header) {
            return false;
        }
        return str_starts_with($header, '%PDF-');
    }

    private function isAllowedPdfMime(?string $mimeType): bool
    {
        if (!$mimeType) {
            return false;
        }
        return in_array(strtolower($mimeType), self::PDF_MIME_TYPES, true);
    }

    private function isAllowedImageMime(?string $mimeType): bool
    {
        if (!$mimeType) {
            return false;
        }
        return in_array(strtolower($mimeType), self::IMAGE_MIME_TYPES, true);
    }

    private function getRequiredDocsByTrack(): array
    {
        $allDocumentTypes = array_values(array_filter(
            UploadedDocument::DOCUMENTS,
            fn($doc) => $doc !== 'isApproved'
        ));

        return [
            'COS' => [
                'passport_photo',
                'signed_pds',
                'signed_work_exp_sheet',
                'photocopy_diploma',
                'application_letter',
                'cert_training',
            ],
            // Requirement requested by user: all required except these 3.
            'Plantilla' => array_values(array_diff(
                $allDocumentTypes,
                ['tor_masteraldoctorate', 'grade_masteraldoctorate', 'cert_lgoo_induction', 'other_documents', 'pqe_result']
            )),
        ];
    }

    private function getDocumentLabelMap(): array
    {
        return [
            'application_letter' => 'Application Letter',
            'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
            'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
            'photocopy_diploma' => 'Diploma',
            'signed_pds' => 'Signed Personal Data Sheet',
            'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
            'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
            'passport_photo' => '2\" x 2\" or Passport Size Picture',
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

    private function extractVacancyIdFromReferer(?string $referer): ?string
    {
        if (empty($referer)) {
            return null;
        }

        $query = parse_url($referer, PHP_URL_QUERY);
        if (empty($query)) {
            return null;
        }

        parse_str($query, $params);
        $vacancyId = $params['vacancy_id'] ?? null;
        if (!is_string($vacancyId)) {
            return null;
        }

        $vacancyId = trim($vacancyId);
        return $vacancyId !== '' ? $vacancyId : null;
    }

    private function scanUploadedFile(UploadedFile $file): array
    {
        $enabled = filter_var(env('CLAMAV_ENABLED', false), FILTER_VALIDATE_BOOL);
        if (!$enabled) {
            return [true, null];
        }

        $path = $file->getRealPath();
        if (!$path || !is_file($path)) {
            return [false, 'Virus scan failed.'];
        }

        if (!class_exists(\Symfony\Component\Process\Process::class)) {
            return [false, 'Virus scanner is not available.'];
        }

        $command = env('CLAMAV_PATH', 'clamscan');
        $process = new \Symfony\Component\Process\Process([$command, '--no-summary', $path]);
        $process->run();

        if ($process->isSuccessful()) {
            return [true, null];
        }

        $output = $process->getOutput() . $process->getErrorOutput();
        if (str_contains($output, 'FOUND')) {
            return [false, 'File failed virus scan.'];
        }

        return [false, 'Virus scan could not be completed.'];
    }


    public function showSubmittedForm()
    {
        // TODO: Get all data from DB

        $user = Auth::user();
        // If user has not submitted PDS, redirect to C1 form
        // if (!$user->has_pds) {
        //     return redirect()->route('display_c1');
        // }

        // Redirect to the correct route, not the view
        return redirect()->route('dashboard_user')->with('pds_submitted', true);

    }



    /*
     * APPLICATION STATUS UPLOADS IN ADMIN
     * using the same flow as C5 and using its function c5StoreFilesToDB();
     */
    public function uploadApplicationDocuments(Request $request, $user_id, $vacancy_id)
    {
        //dd($request->all());
        $request->validate([
            'cert_uploads.*' => 'nullable|file|mimes:pdf|max:10240'
        ], [
            'cert_uploads.*.mimes' => 'Only PDF files are allowed.',
            'cert_uploads.*.max' => 'Each file must be 10MB or smaller.',
        ]);

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->firstOrFail();

        if ($this->hasFinalRevisionDisqualification($application, (string) $vacancy_id)) {
            return redirect()->back()->withErrors([
                'final_revision_block' => 'No further compliance is allowed. Your application is already marked as not qualified after the final revision cycle.',
            ]);
        }

        $uploaded_files = [];
        $upload_errors = [];
        foreach (UploadedDocument::DOCUMENTS as $doc_type) {
            if (!$request->hasFile("cert_uploads.$doc_type")) {
                continue;
            }

            $file = $request->file("cert_uploads.$doc_type");
            if (!$file->isValid()) {
                $upload_errors["cert_uploads.$doc_type"] = 'Upload failed. Please try again.';
                continue;
            }

            [$is_valid, $message] = $this->validateUploadedFile($file, false);
            if (!$is_valid) {
                $upload_errors["cert_uploads.$doc_type"] = $message;
                continue;
            }

            [$scan_ok, $scan_message] = $this->scanUploadedFile($file);
            if (!$scan_ok) {
                $upload_errors["cert_uploads.$doc_type"] = $scan_message;
                continue;
            }

            //If it's application_letter, store in Applications model
            if ($doc_type === 'application_letter') {
                $supportsFileRevisionTracking = Schema::hasColumn('applications', 'file_revision_requested_count')
                    && Schema::hasColumn('applications', 'file_revision_submitted_at');

                // Generate unique stored name
                $originalName = $file->getClientOriginalName();
                $storedName = uniqid() . '_' . $originalName;
                $path = $file->storeAs('uploads/application_letters', $storedName, 'public');

                // Delete old file if exists
                if ($application->file_storage_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($application->file_storage_path);
                }

                //Update the application record
                $applicationUpdates = [
                    'file_original_name' => $originalName,
                    'file_stored_name' => $storedName,
                    'file_storage_path' => $path,
                    'file_status' => 'Pending', // Reset status
                    'file_remarks' => null, // Reset remarks
                    'file_size_8b' => $file->getSize(),
                ];
                if (
                    $supportsFileRevisionTracking
                    && (int) ($application->file_revision_requested_count ?? 0) > 0
                    && empty($application->file_revision_submitted_at)
                ) {
                    $applicationUpdates['file_revision_submitted_at'] = now();
                }

                $application->update($applicationUpdates);

                // *** NEW: Check if application was "Compliance" -> update to "Updated" ***
                if (ApplicationStatus::equals($application->status, ApplicationStatus::COMPLIANCE)) {
                    $statusTransitions = app(ApplicationStatusTransitionService::class);
                    if ($statusTransitions->canTransition($application->status, ApplicationStatus::UPDATED->value)) {
                        $application->update(['status' => ApplicationStatus::UPDATED->value]);
                    }

                    $admins = \App\Models\Admin::all();
                    foreach ($admins as $admin) {
                        \App\Models\Notification::create([
                            'notifiable_type' => 'App\Models\Admin',
                            'notifiable_id' => $admin->id,
                            'type' => 'warning',
                            'data' => [
                                'title' => 'Applicant Updated Documents',
                                'message' => 'Applicant ' . Auth::user()->name . ' has updated their documents for review.',
                                'link' => route('admin.applicant_status', ['user_id' => $user_id, 'vacancy_id' => $vacancy_id], false),
                                'section' => 'Application List',
                                'user_id' => $user_id,
                                'vacancy_id' => $vacancy_id,
                            ],
                            'read_at' => null,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

            } else {
                //For all other documents, add to $uploaded_files for c5StoreFilesToDB
                $uploaded_files[$doc_type] = $file;
            }

            //$uploaded_files[$doc_type] = $file;
        }
        if (!empty($upload_errors)) {
            return redirect()->back()->withErrors($upload_errors);
        }
        if (!empty($uploaded_files)) {
            $this->c5StoreFilesToDB($uploaded_files, $vacancy_id);

            // *** NEW: Check if ANY uploaded file triggers "Compliance" -> "Updated"
            // We need to find the application associated with this user/vacancy if we are in admin context?
            // Actually c5StoreFilesToDB is generic. But here we are in uploadApplicationDocuments
            // which has $user_id and $vacancy_id.
            if (ApplicationStatus::equals($application->status, ApplicationStatus::COMPLIANCE)) {
                $statusTransitions = app(ApplicationStatusTransitionService::class);
                if ($statusTransitions->canTransition($application->status, ApplicationStatus::UPDATED->value)) {
                    $application->update(['status' => ApplicationStatus::UPDATED->value]);
                }

                $admins = \App\Models\Admin::all();
                foreach ($admins as $admin) {
                    \App\Models\Notification::create([
                        'notifiable_type' => 'App\Models\Admin',
                        'notifiable_id' => $admin->id,
                        'type' => 'warning',
                        'data' => [
                            'title' => 'Applicant Updated Documents',
                            'message' => 'Applicant ' . Auth::user()->name . ' has updated their documents for review.',
                            'link' => route('admin.applicant_status', ['user_id' => $user_id, 'vacancy_id' => $vacancy_id], false),
                            'section' => 'Application List',
                            'user_id' => $user_id,
                            'vacancy_id' => $vacancy_id,
                        ],
                        'read_at' => null
                    ]);
                }
            }
        }

        //$this->c5StoreFilesToDB($uploaded_files);

        activity()
            ->causedBy(Auth::user())
            ->event('save')
            ->withProperties(['user_id' => $user_id, 'vacancy_id' => $vacancy_id, 'section' => 'Personal Data Sheet'])
            ->log('Uploaded application documents (Admin).');

        \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);
        return redirect()->back()->with('success', 'Documents uploaded successfully.');
    }

    public function c1DisplayUpdateForm()
    {
        if (!session()->has('form.c1')) {
            session(['form.c1' => $this->c1GetFormFromDB()]);
        }
        $vocational_schools = session('form.c1.vocational', []);
        $college_schools = session('form.c1.college', []);
        $grad_schools = session('form.c1.grad', []);
        $data = session('form.c1');
        return view('pds_update.pds_update', compact('vocational_schools', 'college_schools', 'grad_schools', 'data'));
    }

    public function c2DisplayUpdateForm()
    {
        if (!session()->has('form.c2')) {
            session(['form.c2' => $this->c2GetFormFromDB()]);
        }
        $all_user_work_exps = session('form.c2.all_user_work_exps', []);
        $all_user_civil_service_eligibility = session('form.c2.all_user_civil_service_eligibility', []);
        return view('pds_update.c2_update', compact('all_user_work_exps', 'all_user_civil_service_eligibility'));
    }

    

    public function c3DisplayUpdateForm()
    {
        if (empty(session('data_learning')) && empty(session('data_voluntary')) && empty(session('data_otherInfo'))) {
            $this->c3GetDatabase();
        }
        $data_learning = session('data_learning', []);
        $data_voluntary = session('data_voluntary', []);
        $data_otherInfo = session('data_otherInfo', []);
        return view('pds_update.c3_update', compact('data_learning', 'data_voluntary', 'data_otherInfo'));
    }

    public function c4DisplayUpdateForm()
    {
        if (empty(session('form.c4'))) {
            $this->c4GetDatabase();
        }
        $data = session('form.c4', []);
        return view('pds_update.c4_update', compact('data'));
    }

    public function c5DisplayUpdateForm()
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $documentCollection = UploadedDocument::where('user_id', Auth::id())
            ->when($supportsVacancyScopedDocs, fn($q) => $q->whereNull('vacancy_id'))
            ->orderByDesc('updated_at')
            ->get();
        $documents = $documentCollection
            ->unique('document_type')
            ->keyBy('document_type');
        return view('pds_update.c5_update', compact('documents'));
    }
}
