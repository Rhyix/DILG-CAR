<?php

namespace App\Http\Controllers\Forms;

use Illuminate\Support\Facades\Storage;
use App\Models;
use App\Models\MiscInfos;

// Models
use App\Models\Applications;
use Illuminate\Http\Request;
use App\Models\VoluntaryWork;
use App\Models\WorkExperience;
use App\Models\OtherInformation;
use App\Models\UploadedDocument;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LearningAndDevelopment;
use App\Models\CivilServiceEligibility;
use Illuminate\Support\Facades\Validator;

class PDSController extends Controller
{
    private const SEPARATOR = '/|/';

    /**
     * Updates the C1 session data based on the database .If there is no data on the database,
     * the function should return an empty array.
     * @return array|null
     */
    private function c1GetFormFromDB() {

        $c1_full_info = [];
        $current_user = Auth::user();
        $user_personal_info = $current_user->personalInformation?->attributesToArray();
        if ($user_personal_info != null) {

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
            $user_personal_info['res_street']   = ($user_personal_info['res_street']   != '{*}') ? $user_personal_info['res_street']   : null;
            $user_personal_info['res_sub_vil']  = ($user_personal_info['res_sub_vil']  != '{*}') ? $user_personal_info['res_sub_vil']  : null;
            $user_personal_info['res_brgy']     = ($user_personal_info['res_brgy']     != '{*}') ? $user_personal_info['res_brgy']     : null;
            $user_personal_info['res_city']     = ($user_personal_info['res_city']     != '{*}') ? $user_personal_info['res_city']     : null;
            $user_personal_info['res_province'] = ($user_personal_info['res_province'] != '{*}') ? $user_personal_info['res_province'] : null;
            $user_personal_info['res_zipcode']  = ($user_personal_info['res_zipcode']  != '{*}') ? $user_personal_info['res_zipcode']  : null;

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
            $user_personal_info['per_street']   = ($user_personal_info['per_street']   != '{*}') ? $user_personal_info['per_street']   : null;
            $user_personal_info['per_sub_vil']  = ($user_personal_info['per_sub_vil']  != '{*}') ? $user_personal_info['per_sub_vil']  : null;
            $user_personal_info['per_brgy']     = ($user_personal_info['per_brgy']     != '{*}') ? $user_personal_info['per_brgy']     : null;
            $user_personal_info['per_city']     = ($user_personal_info['per_city']     != '{*}') ? $user_personal_info['per_city']     : null;
            $user_personal_info['per_province'] = ($user_personal_info['per_province'] != '{*}') ? $user_personal_info['per_province'] : null;
            $user_personal_info['per_zipcode']  = ($user_personal_info['per_zipcode']  != '{*}') ? $user_personal_info['per_zipcode']  : null;

            $c1_full_info = array_merge($c1_full_info, $user_personal_info);
        }

        $user_family_bg = $current_user->familyBackground?->attributesToArray();
        if ($user_family_bg != null) {

            $c1_full_info['children'] = $user_family_bg['children_info'];
            $c1_full_info = array_merge($c1_full_info, $user_family_bg);
        }

        $user_educational_bg = $current_user->educationalBackground?->attributesToArray();
        if ($user_educational_bg != null) {

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
    public function c1DisplayForm() {

        // If form in session already exists then no need to retireve data from the database.
        if (!session()->has('form.c1')) {
            session(['form.c1' => $this->c1GetFormFromDB()]);
        }
        $vocational_schools = session('form.c1.vocational', []);
        $college_schools    = session('form.c1.college', []);
        $grad_schools       = session('form.c1.grad', []);
/*
        activity()
            ->causedBy(Auth::user())
            ->log('Viewed C1 form.');
*/
        // dd($vocational_schools);
        return view('pds.pds', compact('vocational_schools', 'college_schools', 'grad_schools'));
    }


    /**
     * Update the C1 session data based on the input fields.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\RedirectResponse
     */
    public function c1UpdateFormSession(Request $request, $go_to) {
        //dd($request->all());
        // get key-value pairs only for fields that need validation.
        $c1_form_data_valid = $request->validate([
            'surname'        => 'required|max:255|string',
            'first_name'     => 'required|max:255|string',
            'middle_name'    => 'nullable|max:255|string',
            'name_extension' => 'nullable|max:255|string',
            'civil_status'   => 'required|string|in:single,married,widowed,separated,other',
            'date_of_birth'  => 'required|date_format:Y-m-d',
            'place_of_birth' => 'required|max:255|string',
            'citizenship'    => 'required|max:255|in:Filipino,Dual Citizenship',
            'sex'            => 'required|in:male,female',
            'blood_type'     => 'required|max:255|string',
            'telephone_no'   => 'nullable|regex:/^0\d{9,10}$/', // example: 0281234567, 0322123456
            'mobile_no'      => 'required|regex:/^09\d{9}$/', // example: +639171234567
            'email_address'  => 'required|email:rfc',
            'height'         => 'required|integer|max:999',
            'weight'         => 'required|integer|max:999',
            'elem_from'      => 'required|date_format:Y-m-d',
            'elem_to'        => 'required|date_format:Y-m-d',
            'jhs_from'       => 'required|date_format:Y-m-d',
            'jhs_to'         => 'required|date_format:Y-m-d',

        ]);

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

        activity()
            ->causedBy(Auth::user())
            ->log('Updated C1 form session.');

        \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);
        //dd(session('form.c1'));
        $routeParams = [];
        if ($request->query('simple')) {
            $routeParams['simple'] = 1;
        }
        return redirect()->route($go_to, $routeParams);
    }


    /**
     * Update the C1 session data based on the database. If there is no data on the database,
     * the function should return an empty array.
     *
     * @return array{all_user_civil_service_eligibility: array, all_user_work_exps: array}
     */
    private function c2GetFormFromDB() {

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
    public function c2DisplayForm() {

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
    public function c2UpdateFormSession(Request $request, $go_to){

        //dd($request->except('_token'));
        $c2_form_data = $request->except('_token');

        // ------------------------------
        // WORK EXPERIENCE TABLE
        // ------------------------------
        $work_exp_count = $c2_form_data['work_exp_count']  ?? 0;
        $all_wex_data = [];

            for ($i = 0; $i < $work_exp_count; $i++) {

            $data_work_exp = [
                'user_id'               => Auth::id(), // store the id of the current user
                'work_exp_from'         => trim(strip_tags($c2_form_data['work_exp_from'][$i])),
                'work_exp_to'           => trim(strip_tags($c2_form_data['work_exp_to'][$i])),
                'work_exp_position'     => trim(strip_tags($c2_form_data['work_exp_position'][$i])),
                'work_exp_department'   => trim(strip_tags($c2_form_data['work_exp_department'][$i])),
                'work_exp_salary'       => trim(strip_tags($c2_form_data['work_exp_salary'][$i])),
                'work_exp_grade'        => trim(strip_tags($c2_form_data['work_exp_grade'][$i])),
                'work_exp_status'       => trim(strip_tags($c2_form_data['work_exp_status'][$i])),
                'work_exp_govt_service' => trim(strip_tags($c2_form_data['work_exp_govt_service'][$i]))
            ];


            // Check if the value of id is zero, a zero value means a record
            // does not exist. thus, create a new record
            $wex_id_temp = $c2_form_data['work_exp_id'][$i] ?? null;
            if (!empty($wex_id_temp)) {
                $data_work_exp['id'] = $wex_id_temp;
            }
            else {
                $data_work_exp['created_at'] = now();
            }
            $data_work_exp['updated_at'] = now();

                $all_wex_data[] = $data_work_exp;
                // WorkExperience::upsert($data_work_exp, 'id');
            }


        // ------------------------------
        // CIVIL SERVICE ELIGIBILITY test
        // ------------------------------
        $civil_service_count = $c2_form_data['civil_service_count']  ?? 0;
        $all_cs_data = [];

            for ($i = 0; $i < $civil_service_count; $i++) {

                $data_cs = [
                    'user_id'                   => Auth::id(), // store the id of the current user
                    'cs_eligibility_career'     => trim(strip_tags($c2_form_data['cs_eligibility_career'][$i])),
                    'cs_eligibility_rating'     => trim(strip_tags($c2_form_data['cs_eligibility_rating'][$i])),
                    'cs_eligibility_date'       => trim(strip_tags($c2_form_data['cs_eligibility_date'][$i])),
                    'cs_eligibility_place'      => trim(strip_tags($c2_form_data['cs_eligibility_place'][$i])),
                    'cs_eligibility_license'    => trim(strip_tags($c2_form_data['cs_eligibility_license'][$i])),
                    'cs_eligibility_validity'   => trim(strip_tags($c2_form_data['cs_eligibility_validity'][$i]))
                ];

                $cs_id_temp = $c2_form_data['cs_eligibility_id'][$i] ?? null;
                if (!empty($cs_id_temp)) {
                    $data_cs['id'] = $cs_id_temp;
                }
                else {
                    $data_cs['created_at'] = now();
                }
                $data_cs['updated_at'] = now();
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

    public function c2DeleteRow($target_row, $id) {

        switch($target_row) {
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

        return response('Delete OK',200);
    }

    // PDS PAGE 3
    public function c3SubmitForm(Request $request, $go_to){

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
                'learning_title'     => $data_learning["learning_title_$i"],
                'learning_type'      => $data_learning["learning_type_$i"],
                'learning_from'      => $data_learning["learning_from_$i"],
                'learning_to'        => $data_learning["learning_to_$i"],
                'learning_hours'     => $data_learning["learning_hours_$i"],
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
                'voluntary_org'         => $data_vol["voluntary_org_$i"],
                'voluntary_from'        => $data_vol["voluntary_from_$i"],
                'voluntary_to'          => $data_vol["voluntary_to_$i"],
                'voluntary_hours'       => $data_vol["voluntary_hours_$i"],
                'voluntary_position'    => $data_vol["voluntary_position_$i"],
            ];
        }
        session(['data_voluntary' => $data_voluntary_arrays]);

        // ---------------------------------------------------------------------------
        // OTHER INFORMATION
        // ---------------------------------------------------------------------------
        $data_other = $request->all();
        $data_other_arrays = [
            'skill'         => $data_other['skills'],
            'distinction'   => $data_other['distinctions'],
            'organization'  => $data_other['organizations'],
            'user_id'       => Auth::id(),
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
            $rules_data_learning["learning_title_$i"]     = 'required|string|max:255';
            $rules_data_learning["learning_type_$i"]      = 'required|string|max:100';
            $rules_data_learning["learning_from_$i"]      = 'required|date';
            $rules_data_learning["learning_to_$i"]        = "required|date|after_or_equal:learning_from_$i";
            $rules_data_learning["learning_hours_$i"]     = 'required|numeric|min:1';
            $rules_data_learning["learning_conducted_$i"] = 'required|string|max:255';
        }
        $validated_data_learning = $request->validate($rules_data_learning);

        // FOR session data in LEARNING AND DEVELOPMENT
        $data_learning_arrays = [];
        for ($i = 1; $i <= $entryCount; $i++) {
            $data_learning_arrays[] = [
                'learning_title'     => $validated_data_learning["learning_title_$i"],
                'learning_type'      => $validated_data_learning["learning_type_$i"],
                'learning_from'      => $validated_data_learning["learning_from_$i"],
                'learning_to'        => $validated_data_learning["learning_to_$i"],
                'learning_hours'     => $validated_data_learning["learning_hours_$i"],
                'learning_conducted' => $validated_data_learning["learning_conducted_$i"],
                'user_id'            => Auth::id(),
            ];
        }
        // SESSION name table of Learning and Development (L&D) Interventions in c3.blade file
        session(['data_learning' => $data_learning_arrays]);
        //dd(session('data_learning'));


        // VOLUNTARY WORKS VALIDATION
        $rules_data_vol = [];
        for ($i = 1; $i <= $entryCount_vol; $i++) {
            $rules_data_vol["voluntary_org_$i"]      = 'required|string|max:255';
            $rules_data_vol["voluntary_from_$i"]     = 'required|date';
            $rules_data_vol["voluntary_to_$i"]       = "required|date|after_or_equal:voluntary_from_$i";
            $rules_data_vol["voluntary_hours_$i"]    = 'required|numeric|min:1';
            $rules_data_vol["voluntary_position_$i"] = 'required|string|max:255';
        }
        $validated_data_vol = $request->validate($rules_data_vol);

        // FOR session data in VOLUNTARY WORK
        $data_voluntary_arrays = [];
        for ($i = 1; $i <= $entryCount_vol; $i++) {
            $data_voluntary_arrays[] = [
                'voluntary_org'         => $validated_data_vol["voluntary_org_$i"],
                'voluntary_from'        => $validated_data_vol["voluntary_from_$i"],
                'voluntary_to'          => $validated_data_vol["voluntary_to_$i"],
                'voluntary_hours'       => $validated_data_vol["voluntary_hours_$i"],
                'voluntary_position'    => $validated_data_vol["voluntary_position_$i"],
                'user_id'               => Auth::id(),
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
        $routeParams = [];
        if ($request->query('simple')) {
            $routeParams['simple'] = 1;
        }
        return redirect()->route($go_to, $routeParams);
    }

    public function c3ShowForm(){
        if (empty(session('data_learning')) && empty(session('data_voluntary')) && empty(session('data_otherInfo'))){
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

    public function c3GetDatabase(){
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
                'skill' => json_decode($all_user_other_info_data->skill, true) ?? [],
                'distinction' => json_decode($all_user_other_info_data->distinction, true) ?? [],
                'organization' => json_decode($all_user_other_info_data->organization, true) ?? [],
                'user_id' => $all_user_other_info_data->user_id,
            ];
            session(['data_otherInfo' => $processed_data]);
        }

        session(['data_learning' => $all_user_learningAndDevelopment_data]);
        session(['data_voluntary' => $all_user_voluntary_work_data]);
    }


    // ==============================================================================
    // C4 CONTROLLER
    public function c4SubmitForm(Request $request, $go_to){
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
        $temp_34_b  = $request->input('related_34_b');
        $temp_35_a  = $request->input('guilty_35_a');
        $temp_35_b  = $request->input('criminal_35_b');
        $temp_36    = $request->input('convicted_36');
        $temp_37    = $request->input('separated_37');
        $temp_38_a  = $request->input('candidate_38_a');
        $temp_38_b  = $request->input('resigned_38_b');
        $temp_39    = $request->input('immigrant_39');
        $temp_40_a  = $request->input('indigenous_40_a');
        $temp_40_b  = $request->input('pwd_40_b');
        $temp_40_c  = $request->input('solo_parent_40_c');

        // If "yes" was selected, use the text area instead
        $related_34_b       = $this->userSelection($temp_34_b, $request,'related_34_b_details');
        $guilty_35_a        = $this->userSelection($temp_35_a, $request,'guilty_35_a_details');
        $convicted_36       = $this->userSelection($temp_36, $request,'convicted_36_details');
        $separated_37       = $this->userSelection($temp_37, $request,'separated_37_details');
        $candidate_38_a     = $this->userSelection($temp_38_a, $request,'candidate_38_a_details');
        $resigned_38_b      = $this->userSelection($temp_38_b, $request,'resigned_38_b_details');
        $immigrant_39       = $this->userSelection($temp_39, $request,'immigrant_39_details');
        $indigenous_40_a    = $this->userSelection($temp_40_a, $request,'indigenous_40_a_details');
        $pwd_40_b           = $this->userSelection($temp_40_b, $request,'pwd_40_b_details');
        $solo_parent_40_c   = $this->userSelection($temp_40_c, $request,'solo_parent_40_c_details');

        $criminal_35_b_array = $request->input('criminal_35_b_details');
        // NUMBER 35_b
        if ($temp_35_b === 'yes') {
            $criminal_35_b= implode(',', $criminal_35_b_array);

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
            'related_34_a'          => $request->input('related_34_a'),
            'related_34_b'          => $related_34_b,
            'guilty_35_a'           => $guilty_35_a,
            'criminal_35_b'         => $criminal_35_b,
            'criminal_35_b_array'   => $criminal_35_b_array, // remove if insert into database
            'convicted_36'          => $convicted_36,
            'separated_37'          => $separated_37,
            'candidate_38'          => $candidate_38_a,
            'resigned_38_b'         => $resigned_38_b,
            'immigrant_39'          => $immigrant_39,
            'indigenous_40_a'       => $indigenous_40_a,
            'pwd_40_b'              => $pwd_40_b,
            'solo_parent_40_c'      => $solo_parent_40_c,

            'ref1_name'             => $request->input('ref1_name'),
            'ref1_tel'              => $request->input('ref1_tel'),
            'ref1_address'          => $request->input('ref1_address'),
            'ref2_name'             => $request->input('ref2_name'),
            'ref2_tel'              => $request->input('ref2_tel'),
            'ref2_address'          => $request->input('ref2_address'),
            'ref3_name'             => $request->input('ref3_name'),
            'ref3_tel'              => $request->input('ref3_tel'),
            'ref3_address'          => $request->input('ref3_address'),

            'govt_id_type'          => $govt_id_type,
            'govt_id_other'         => $request->input('govt_id_other'),
            'govt_id_number'        => $request->input('govt_id_number'),
            'govt_id_date_issued'   => $request->input('govt_id_date_issued'),
            'govt_id_place_issued'  => $request->input('govt_id_place_issued'),

            'photo_upload'          => $temp_photo_path ?? null,
        ];
        session(['form.c4' => $misc_data]);
        //dd(session('form.c4'));
        //dd($request->all());
        //dd(session('form.c4')); // TODO: GETDATABASE

        // Validate the form for the hidden fields (e.g: realated_34_b_details)
        $request->validate([
            'related_34_b_details'          => 'required_if:related_34_b,yes|nullable|string|max:255',
            'criminal_35_b_details.date'    => 'required_if:criminal_35_b,yes|nullable|date',
            'criminal_35_b_details.status'  => 'required_if:criminal_35_b,yes|nullable|string|max:255',
            'convicted_36_details'          => 'required_if:convicted_36,yes|nullable|string|max:255',
            'separated_37_details'          => 'required_if:separated_37,yes|nullable|string|max:255',
            'candidate_38_a_details'        => 'required_if:candidate_38_a,yes|nullable|string|max:255',
            'resigned_38_b_details'         => 'required_if:resigned_38_b,yes|nullable|string|max:255',
            'immigrant_39_details'          => 'required_if:immigrant_39,yes|nullable|string|max:255',
            'indigenous_40_a_details'       => 'required_if:indigenous_40_a,yes|nullable|string|max:255',
            'pwd_40_b_details'              => 'required_if:pwd_40_b,yes|nullable|string|max:255',
            'solo_parent_40_c_details'      => 'required_if:solo_parent_40_c,yes|nullable|string|max:255',
            'govt_id_other'                 => 'nullable|required_if:govt_id_type,other|string|max:255',

        ]);

        // Validation for the data to be inserted in session to database
        $validator_misc_data = Validator::make($misc_data, [
        'related_34_a'        => 'required|string|max:255',
        'related_34_b'        => 'required|string|max:255',
        'guilty_35_a'         => 'required|string|max:255',
        'criminal_35_b'       => 'required|string|max:255',
        'convicted_36'        => 'required|string|max:255',
        'separated_37'        => 'required|string|max:255',
        'candidate_38'        => 'required|string|max:255',
        'resigned_38_b'       => 'required|string|max:255',
        'immigrant_39'        => 'required|string|max:255',
        'indigenous_40_a'     => 'required|string|max:255',
        'pwd_40_b'            => 'required|string|max:255',
        'solo_parent_40_c'    => 'required|string|max:255',

        'ref1_name'           => 'required|string|max:255',
        'ref1_tel'            => 'required|string|max:20',
        'ref1_address'        => 'required|string|max:255',
        'ref2_name'           => 'required|string|max:255',
        'ref2_tel'            => 'required|string|max:20',
        'ref2_address'        => 'required|string|max:255',
        'ref3_name'           => 'required|string|max:255',
        'ref3_tel'            => 'required|string|max:20',
        'ref3_address'        => 'required|string|max:255',

        'govt_id_type'        => 'required|string|max:255',
        'govt_id_number'      => 'required|string|max:50',
        'govt_id_date_issued' => 'required|date',
        'govt_id_place_issued'=> 'required|string|max:255',
        'photo_upload'        => 'nullable|string',
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
        return redirect()->route($go_to);
    }

    public function userSelection($sel, Request $request, string $textArea){
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

    public function c4ShowForm(){
        if (empty(session('form.c4')) ){
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
    public function c4GetDatabase(){
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

    // END C4 CONTROLLER
    // ==============================================================================



    /**
     * Stores all files in a local filesystem (subject to change, probably 💩).
     * Handles auto-deletion of existing files and auto-updating on database
     *
     * @param UploadedFile[] $files
     * @return void
     */
    private function c5StoreFilesToDB(array $files) {

        foreach ($files as $doc_type => $file) {

            $hashed_name = $file->hashName();
            $store_path = $file->store("uploads/pds-files", 'public');

            $document = UploadedDocument::firstOrCreate([
                'user_id' => Auth::id(),
                'document_type' => $doc_type
            ]);

            // Auto-delete if file_paths don't match and if an item.
            if (!empty($document->storage_path) &&
                ($document->storage_path !== $store_path) &&
            Storage::disk('public')->exists($document->storage_path)) {
                Storage::disk('public')->delete($document->storage_path);
            }

            $document->update([
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $hashed_name,
                'storage_path' => $store_path,
                'mime_type' => $file->getClientMimeType(),
                'file_size_8b' => $file->getSize()
            ]);

        }
        activity()
    ->causedBy(Auth::user())
    ->log('Store C5 form.');

    }


    /**
     * Displays the C5 page for PDS.
     * @return \Illuminate\Contracts\View\View
     */
    public function c5DisplayForm()
{
    $user = Auth::user();

    // Fetch documents for this user
    $documentCollection = UploadedDocument::where('user_id', $user->id)->get();

    // Restructure collection into associative array: ['document_type' => UploadedDocument]
    $documents = $documentCollection->keyBy('document_type');

    // ✅ Fix the quote in the view name
    return view('pds.c5', compact('documents'));
}



    /**
     * The transition functionality for submission of data. This should perform
     * the uploading of files to the filesystem and createing/updating metadata
     * for that specific file for future retrieval.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\RedirectResponse
     */
    public function finalizePDS(Request $request, $go_to) {

        /********************************
         * +++++ Required Documents
         ********************************/
        // User is allowed to not upload any file
        $request->validate([
            'cert_uploads.application_letter'      => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.pqe_result'              => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_elegibility'        => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.ipcr'                    => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.non_academic'            => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_training'           => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.designation_order'       => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.transcript_records'      => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.photocopy_diploma'       => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.grade_masteraldoctorate' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.tor_masteraldoctorate'   => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_employment'         => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.other_documents'         => 'nullable|file|mimes:pdf|max:10240'
        ]);

        $uploaded_files = [];
        $files_with_upload_errors = [];
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

            $uploaded_files[$_access] = $_file;
        }
        $this->c5StoreFilesToDB($uploaded_files);

        //********************************
        //* +++++ Personal Information
        //*******************************
        $c1_form_data = session('form.c1');

        $dual_type_t = '';
        if ($c1_form_data['citizenship']==='Dual Citizen') {
            $dual_type_t = $c1_form_data['dual_type'];
        }

        $_haystack = ['children', 'vocational', 'college', 'grad'];
        foreach($c1_form_data as $_key => $_val) {

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
        $house_no_t = ($c1_form_data['res_house_no']!='') ? $c1_form_data['res_house_no'] : '{*}';
        $street_t   = ($c1_form_data['res_street']!='')   ? $c1_form_data['res_street']   : '{*}';
        $sub_vil_t  = ($c1_form_data['res_sub_vil']!='')  ? $c1_form_data['res_sub_vil']  : '{*}';
        $brgy_t     = ($c1_form_data['res_brgy']!='')     ? $c1_form_data['res_brgy']     : '{*}';
        $city_t     = ($c1_form_data['res_city']!='')     ? $c1_form_data['res_city']     : '{*}';
        $province_t = ($c1_form_data['res_province']!='') ? $c1_form_data['res_province'] : '{*}';
        $zipcode_t  = ($c1_form_data['res_zipcode']!='')  ? $c1_form_data['res_zipcode']  : '{*}';

        $formatted_residential_address = "$house_no_t/|/$street_t/|/$sub_vil_t/|/$brgy_t/|/$city_t/|/$province_t/|/$zipcode_t";

        // format permanent address
        $house_no_t = ($c1_form_data['per_house_no']!='') ? $c1_form_data['per_house_no'] : '{*}';
        $street_t   = ($c1_form_data['per_street']!='')   ? $c1_form_data['per_street']   : '{*}';
        $sub_vil_t  = ($c1_form_data['per_sub_vil']!='')  ? $c1_form_data['per_sub_vil']  : '{*}';
        $brgy_t     = ($c1_form_data['per_brgy']!='')     ? $c1_form_data['per_brgy']     : '{*}';
        $city_t     = ($c1_form_data['per_city']!='')     ? $c1_form_data['per_city']     : '{*}';
        $province_t = ($c1_form_data['per_province']!='') ? $c1_form_data['per_province'] : '{*}';
        $zipcode_t  = ($c1_form_data['per_zipcode']!='')  ? $c1_form_data['per_zipcode']  : '{*}';

        $formatted_permanent_address  = "$house_no_t/|/$street_t/|/$sub_vil_t/|/$brgy_t/|/$city_t/|/$province_t/|/$zipcode_t";

        // create a personal information record compact database insertion
        // IF the record does not exist for the current user.
        $user_personal_info = Models\PersonalInformation::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $user_personal_info->update([
            //'cs_id_no'                  => $c1_form_data['cs_id_no'],
            'surname'                   => $c1_form_data['surname'],
            'name_extension'            => $c1_form_data['name_extension'],
            'first_name'                => $c1_form_data['first_name'],
            'middle_name'               => $c1_form_data['middle_name'],
            'sex'                       => $c1_form_data['sex'],
            'civil_status'              => $c1_form_data['civil_status'],
            'date_of_birth'             => $c1_form_data['date_of_birth'],
            'place_of_birth'            => $c1_form_data['place_of_birth'],
            'height'                    => $c1_form_data['height'],
            'weight'                    => $c1_form_data['weight'],
            'blood_type'                => $c1_form_data['blood_type'],
            'philhealth_no'             => $c1_form_data['philhealth_no'],
            'tin_no'                    => $c1_form_data['tin_no'],
            'agency_employee_no'        => $c1_form_data['agency_employee_no'],
            'gsis_id_no'                => $c1_form_data['gsis_id_no'],
            'pagibig_id_no'             => $c1_form_data['pagibig_id_no'],
            'sss_id_no'                 => $c1_form_data['sss_id_no'],
            'citizenship'               => $c1_form_data['citizenship'],
            'dual_type'                 => $dual_type_t,
            'dual_country'              => $c1_form_data['dual_country'],
            'residential_address'       => $formatted_residential_address,
            'permanent_address'         => $formatted_permanent_address,
            'telephone_no'              => $c1_form_data['telephone_no'],
            'mobile_no'                 => $c1_form_data['mobile_no'],
            'email_address'             => $c1_form_data['email_address']
        ]);

        unset($user_personal_info);

        //********************************
        //* +++++ Family Background
        //*******************************

        $user_family_bg = Models\FamilyBackground::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $user_family_bg->update([
            'spouse_surname'            => $c1_form_data['spouse_surname'],
            'spouse_first_name'         => $c1_form_data['spouse_first_name'],
            'spouse_middle_name'        => $c1_form_data['spouse_middle_name'],
            'spouse_name_extension'     => $c1_form_data['spouse_name_extension'],
            'spouse_occupation'         => $c1_form_data['spouse_occupation'],
            'spouse_employer'           => $c1_form_data['spouse_employer'],
            'spouse_business_address'   => $c1_form_data['spouse_business_address'],
            'spouse_telephone'          => $c1_form_data['spouse_telephone'],
            'father_surname'            => $c1_form_data['father_surname'],
            'father_first_name'         => $c1_form_data['father_first_name'],
            'father_middle_name'        => $c1_form_data['father_middle_name'],
            'father_name_extension'     => $c1_form_data['father_name_extension'],
            'mother_maiden_surname'     => $c1_form_data['mother_maiden_surname'],
            'mother_maiden_first_name'  => $c1_form_data['mother_maiden_first_name'],
            'mother_maiden_middle_name' => $c1_form_data['mother_maiden_middle_name'],
            'children_info'             => $c1_form_data['children']
        ]);

        unset($user_family_bg);

        //********************************
        //* +++++ Educational Background
        //********************************

        $user_educational_bg = Models\EducationalBackground::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $user_educational_bg->update([
            'elem_from'                 => $c1_form_data['elem_from'],
            'elem_to'                   => $c1_form_data['elem_to'],
            'elem_school'               => $c1_form_data['elem_school'],
            'elem_academic_honors'      => $c1_form_data['elem_academic_honors'],
            'elem_basic'                => $c1_form_data['elem_basic'],
            //'elem_earned'               => $c1_form_data['elem_earned'],
            'elem_year_graduated'       => $c1_form_data['elem_year_graduated'],

            'jhs_from'                  => $c1_form_data['jhs_from'],
            'jhs_to'                    => $c1_form_data['jhs_to'],
            'jhs_school'                => $c1_form_data['jhs_school'],
            'jhs_academic_honors'       => $c1_form_data['jhs_academic_honors'],
            'jhs_basic'                 => $c1_form_data['jhs_basic'],
            //'jhs_earned'                => $c1_form_data['jhs_earned'],
            'jhs_year_graduated'        => $c1_form_data['jhs_year_graduated'],

            /*
            'shs_from'                  => $c1_form_data['shs_from'],
            'shs_to'                    => $c1_form_data['shs_to'],
            'shs_school'                => $c1_form_data['shs_school'],
            'shs_academic_honors'       => $c1_form_data['shs_academic_honors'],
            'shs_basic'                 => $c1_form_data['shs_basic'],
            'shs_earned'                => $c1_form_data['shs_earned'],
            'shs_year_graduated'        => $c1_form_data['shs_year_graduated'],
            */

            'vocational'                => $c1_form_data['vocational'] ?? null,
            'college'                   => $c1_form_data['college'],
            'grad'                      => $c1_form_data['grad'] ?? null,
        ]);

        unset($user_educational_bg);
        // TODO: Fix null new user null required values. Create a middleware so that they cant skip ahead to c5

        // -------------
        // C2 INSERT TO DATABASE
        // ------------
        //********************************
        //* +++++ Work Experience
        //*******************************
        $c2_form_data = session('form.c2');
        if(isset($c2_form_data['all_user_work_exps'])){
            $user_all_wex_data = $c2_form_data['all_user_work_exps'];

            WorkExperience::where('user_id', Auth::id())->delete();

            for ($i = 0; $i < count($user_all_wex_data); $i++) {
                // if(array_key_exists('id'))
                //$user_all_wex_data[$i]['id'] = ($user_all_wex_data[$i]['id'] == "null") ? null : $user_all_wex_data[$i]['id'];
                //$user_all_wex_data[$i]['id'] = $user_all_wex_data[$i]['id'] ?? null;
                //dd($user_all_wex_data['id']);
                unset($user_all_wex_data[$i]['id']); // Remove 'id' key
                WorkExperience::upsert($user_all_wex_data[$i], 'id');
            }
        }

        //********************************
        //* +++++ Civil Service Eligibility
        //*******************************
        if(isset($c2_form_data['all_user_civil_service_eligibility'])){
            $user_all_cs_data  = $c2_form_data['all_user_civil_service_eligibility'];

            CivilServiceEligibility::where('user_id', Auth::id())->delete();
            for ($i = 0; $i < sizeof($user_all_cs_data); $i++) {
                unset($user_all_cs_data[$i]['id']); // Remove 'id' key
                CivilServiceEligibility::upsert($user_all_cs_data[$i], 'id');
            }
        }

        // C3 INSERT TO DATABASE
        //LEARNING AND DEVELOPMENT
        $c3_learning_and_development_data = session('data_learning');
        //dd(session('data_learning'));

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
        if(!empty($c3_other_information_data)){
            $user_other_info = OtherInformation::firstOrCreate([
                'user_id' => Auth::id()
            ]);
            $user_other_info->update([
                'user_id'       => Auth::id(),
                'skill'         => $c3_other_information_data['skill'],
                'distinction'   => $c3_other_information_data['distinction'],
                'organization'  => $c3_other_information_data['organization'],
            ]);
        }

        //C4 INSERT TO DATABASE
        $c4_misc_info_data = session('form.c4');
        if(!empty($c4_misc_info_data)){
            unset($c4_misc_info_data['criminal_35_b_array']); // criminal_35_b_array is not part of the database
            $misc_info_data = MiscInfos::firstOrCreate([
                'user_id' => Auth::id()
            ]);
            $misc_info_data->update($c4_misc_info_data);
        }

        activity()
            ->causedBy(Auth::user())
            ->event('save')
            ->log('Finalized PDS submission.');

        return redirect()->route($go_to);
    } // END finalize PDS


    public function showSubmittedForm() {
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
    public function uploadApplicationDocuments(Request $request, $user_id, $vacancy_id){
        //dd($request->all());
        $request->validate([
            'documents.*' => 'nullable|file|mimes:pdf|max:10240'
        ]);

        $uploaded_files = [];
        foreach (UploadedDocument::DOCUMENTS as $doc_type) {
            if (!$request->hasFile("documents.$doc_type")) {
                continue;
            }

            $file = $request->file("documents.$doc_type");
            if (!$file->isValid()) {
                continue;
            }

            //If it's application_letter, store in Applications model
            if ($doc_type === 'application_letter') {
                $application = Applications::where('user_id', $user_id)
                    ->where('vacancy_id', $vacancy_id)
                    ->firstOrFail();

                // Generate unique stored name
                $originalName = $file->getClientOriginalName();
                $storedName = uniqid() . '_' . $originalName;
                $path = $file->storeAs('uploads/application_letters', $storedName, 'public');

                // Delete old file if exists
                if ($application->file_storage_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($application->file_storage_path);
                }

                //Update the application record
                $application->update([
                    'file_original_name' => $originalName,
                    'file_stored_name' => $storedName,
                    'file_storage_path' => $path,
                    'file_status' => 'Pending', // Reset status
                    'file_remarks' => null, // Reset remarks
                    'file_size_8b' => $file->getSize(),
                ]);

            } else {
                //For all other documents, add to $uploaded_files for c5StoreFilesToDB
                $uploaded_files[$doc_type] = $file;
            }

            //$uploaded_files[$doc_type] = $file;
        }
        if (!empty($uploaded_files)) {
            $this->c5StoreFilesToDB($uploaded_files);
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


}
