<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Vacancy;
use App\Models\ExamDetail;
use App\Models\JobVacancy;
use App\Models\Applications;
use App\Models\UploadedDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Validator;


use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyApplicationStatus;



class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function manage()
    {
        $admins = Admin::all();
        $users = User::all();

        activity()
            ->causedBy(auth('admin')->user())
            ->event('view')
            ->withProperties(['section' => 'System Users Management'])
            ->log('Viewed admin account management.');

        return view('admin.admin_account_management', compact('admins', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:admins,username'],
            'name' => ['required', 'string', 'max:255'],
            'office' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8'],
            'account_type' => ['required', Rule::in(['admin', 'viewer'])],
        ], [
            'username.unique' => 'The username has already been taken.',
            'email.unique' => 'The email has already been taken.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = $validated['account_type'];
        unset($validated['account_type']);

        Admin::create($validated);

        activity()
            ->causedBy(auth('admin')->user())
            ->performedOn(Admin::where('email', $validated['email'])->first())
            ->event('create')
            ->withProperties(['username' => $validated['username'], 'section' => 'System Users Management'])
            ->log('Created a new admin account.');

        return redirect()->back()->with('success', 'Admin account created successfully!');
    }


    public function deactivate($id)
    {
        $admin = Admin::findOrFail($id);
        $authUser = Auth::guard('admin')->user();

        if (!$authUser) {
            return redirect()->route('admin.login')->with('error', 'You must be logged in.');
        }

        if ($authUser->id == $admin->id) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        $admin->is_active = false;
        $admin->save();

        activity()
            ->causedBy(auth('admin')->user())
            ->performedOn($admin)
            ->event('deactivate')
            ->withProperties(['deactivated_admin_id' => $admin->id, 'section' => 'System Users Management'])
            ->log('Deactivated an admin account.');


        return redirect()->back()->with('success', 'Admin deactivated successfully.');
    }

    public function activate($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->is_active = true;
        $admin->save();

        activity()
            ->causedBy(auth('admin')->user())
            ->performedOn($admin)
            ->event('activate')
            ->withProperties(['activated_admin_id' => $admin->id, 'section' => 'System Users Management'])
            ->log('Activated an admin account.');

        return redirect()->back()->with('success', 'Admin activated successfully.');
    }

    private function getReviewedApplications()
    {
        /*
        activity()
            ->causedBy(auth()->guard('admin')->user())
            ->log('Viewed reviewed applicants list.');
        */

        return Applications::with(['personalInformation', 'vacancy'])
            ->where('status', '!=', 'Pending')
            ->whereHas('personalInformation')
            ->latest()
            ->get();
    }

    public function dashboard(Request $request)
    {
        //info('check');
        $selectedYear = $request->query('year', now()->year);

        $years = DB::table('applications')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        $monthlyApplicants = DB::table('applications')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        //info('check');

        $months = collect(range(1, 12))->map(fn($m) => Carbon::create()->month($m)->format('F'))->toArray();

        $monthCounts = array_fill(1, 12, 0);
        foreach ($monthlyApplicants as $record) {
            $monthCounts[(int) $record->month] = (int) $record->total;
        }

        //info('check');

        $chartLabels = $months;
        $chartData = array_values($monthCounts);

        $openVacancies = Vacancy::where('status', 'OPEN')->get();
        $openVacancyCount = $openVacancies->count();
        $cosVacancyCount = $openVacancies->where('vacancy_type', 'COS')->count();
        $plantillaVacancyCount = $openVacancies->where('vacancy_type', 'Plantilla')->count();

        $onGoingApplications = Applications::with(['personalInformation', 'vacancy'])
            ->whereIn('status', ['Incomplete', 'Pending'])
            ->take(6)
            ->get();

        //info('check');

        $onGoingApplicationsCount = $onGoingApplications->count();
        $reviewedApplications = $this->getReviewedApplications();
        $reviewedApplicationsCount = Applications::where('status', '!=', 'Pending')->count();

        $systemUsers = Admin::where('is_active', 1)->get();
        $systemUsersCount = $systemUsers->count();

        $now = Carbon::now()->toDateTimeString();
        $upcomingExams = ExamDetail::whereRaw("STR_TO_DATE(CONCAT(`date`, ' ', `time`), '%Y-%m-%d %H:%i:%s') > ?", [$now])
            ->orderByRaw("STR_TO_DATE(CONCAT(`date`, ' ', `time`), '%Y-%m-%d %H:%i:%s')")
            ->with('vacancy')
            ->get();

        $upcomingExamsCount = $upcomingExams->count();

        /*
        activity()
            ->causedBy(auth()->guard('admin')->user())
            ->log('Viewed admin dashboard.');
        */

        return view('admin.dashboard_admin', [
            'openVacancies' => $openVacancies,
            'openVacancyCount' => $openVacancyCount,
            'cosVacancyCount' => $cosVacancyCount,
            'plantillaVacancyCount' => $plantillaVacancyCount,
            'onGoingApplications' => $onGoingApplications,
            'onGoingApplicationsCount' => $onGoingApplicationsCount,
            'reviewedApplications' => $reviewedApplications,
            'reviewedApplicationsCount' => $reviewedApplicationsCount,
            'systemUsers' => $systemUsers,
            'systemUsersCount' => $systemUsersCount,
            'upcomingExams' => $upcomingExams,
            'upcomingExamsCount' => $upcomingExamsCount,
            'chartLabels' => json_encode($chartLabels),
            'chartData' => json_encode($chartData),
            'selectedYear' => $selectedYear,
            'years' => $years,
        ]);
        //info('check');

    }

    public function reviewedApplicants()
    {
        $reviewedApplications = $this->getReviewedApplications();
        /*
        activity()
            ->causedBy(auth()->guard('admin')->user())
            ->log('Viewed reviewed applicants list.');
        */
        return view('admin.reviewed_applicants', compact('reviewedApplications'));
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', Rule::unique('admins')->ignore($admin->id)],
            'name' => ['required', 'string', 'max:255'],
            'office' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins')->ignore($admin->id)],
            'account_type' => ['required', Rule::in(['admin', 'viewer'])],
        ]);

        // If validation fails, flash errors as a single string or array
        if ($validator->fails()) {
            return redirect()->back()
                ->with('_editing', $admin->id)
                ->with('error', $validator->errors()->all()) // flash as array of errors
                ->withInput();
        }

        $validated = $validator->validated();

        // Flash '_editing' so mother blade knows which admin was edited
        session()->flash('_editing', $admin->id);

        $admin->update([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'office' => $validated['office'],
            'designation' => $validated['designation'],
            'email' => $validated['email'],
            'role' => $validated['account_type'],
        ]);

        activity()
            ->causedBy(auth('admin')->user())
            ->performedOn($admin)
            ->event('update')
            ->withProperties(['updated_admin_id' => $admin->id, 'section' => 'System Users Management'])
            ->log('Updated an admin account.');

        return redirect()->back()
            ->with('success', 'Admin account updated successfully!');
    }

    public function search(Request $request)
    {
        $search = $request->input('query');

        $admins = Admin::where('username', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->get();
        /*
        activity()
            ->causedBy(auth()->guard('admin')->user())
            ->withProperties(['query' => $request->input('query')])
            ->log('Searched for admins.');
        */
        return view('partials.admin_list', compact('admins'))->render();
    }

    public function viewApplicantStatus($user_id, $vacancy_id)
    {
        $application = Applications::with(['personalInformation', 'vacancy', 'user'])
            ->where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->first();

        if (!$application || !$application->vacancy) {
            abort(404, 'Application or vacancy not found.');
        }

        $pi = $application->personalInformation;
        $vacancy = $application->vacancy;

        // Get name from PDS if available, otherwise fall back to user's name
        $formattedName = $pi
            ? trim(
                $pi->first_name . ' ' .
                ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                $pi->surname . ' ' .
                $pi->name_extension
            )
            : ($application->user ? $application->user->name : 'N/A');

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();
        $adminName = $application->updatedByAdmin?->username ?? null;

        $uploadedDocuments = UploadedDocument::where('user_id', $user_id)->get()->keyBy('document_type');
        $documents = [];

        $labelMap = [
            'application_letter' => 'Application Letter',
            'signed_pds' => 'Signed Personal Data Sheet',
            'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
            'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
            'cert_eligibility' => 'Certificate of Eligibility / Board Rating',
            'ipcr' => 'Certification of Numerical Rating / Performance Rating / IPCR',
            'non_academic' => 'Non-Academic Awards Received',
            'cert_training' => 'Certified/Authenticated Copy of Certificates of Training/Participation',
            'designation_order' => 'List with Certified Photocopy of Duly Confirmed Designation Order/s',
            'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
            'photocopy_diploma' => 'Diploma',
            'grade_masteraldoctorate' => 'Certified Photocopy of Certificate of Grades with Masteral/Doctorate Units Earned',
            'tor_masteraldoctorate' => 'Certified Photocopy of TOR with Masteral/Doctorate Degree',
            'cert_employment' => 'Certificate of Employment (If Any)',
            'other_documents' => 'Other Documents Submitted',
        ];

        foreach (UploadedDocument::DOCUMENTS as $docType) {
            if ($docType === 'isApproved')
                continue;

            $doc = $uploadedDocuments->get($docType);

            if ($docType === 'application_letter') {
                $documents[] = [ // Get from Applications table instead
                    'id' => 'application_letter',
                    'text' => $labelMap['application_letter'],
                    'status' => $application->file_status ?? 'invalid',
                    //'preview' => $application->file_storage_path ? asset('storage/' . $application->file_storage_path) : '',
                    'preview' => $application->file_storage_path
                        ? url('/preview-file/' . base64_encode($application->file_storage_path))
                        : '',
                    'remarks' => $application->file_remarks ?? 'No remarks provided.',
                    'isBold' => true,
                ];
            } else {
                $doc = $uploadedDocuments->get($docType);

                $documents[] = [
                    'id' => $docType,
                    'text' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'status' => $doc ? $doc->status : 'invalid',
                    //'preview' => $doc ? asset('storage/' . $doc->storage_path) : '',
                    'preview' => $doc ? url('/preview-file/' . base64_encode($doc->storage_path)) : '',
                    'remarks' => $doc ? ($doc->remarks ?: $doc->original_name) : 'Document missing.',
                    'isBold' => true,
                ];
            }
            /*
            $documents[] = [
                'id' => $docType,
                'text' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                'status' => $doc ? $doc->status : 'invalid',
                'preview' => $doc ? asset('storage/' . $doc->storage_path) : '',
                'remarks' => $doc ? ($doc->remarks ?: $doc->original_name) : 'Document missing.',
                'isBold' => true
            ];
            */
        }
        //dd($documents);

        activity()
            ->causedBy(auth('admin')->user())
            ->performedOn($application)
            ->event('view')
            ->withProperties(['user_id' => $user_id, 'vacancy_id' => $vacancy_id, 'section' => 'Application List'])
            ->log('Viewed applicant status.');


        return view('admin.applicant_status', [
            'applicant_name' => $formattedName,
            'place_of_assignment' => $vacancy->place_of_assignment,
            'compensation' => $vacancy->monthly_salary,
            'job_applied' => $vacancy->position_title,
            'user_id' => $user_id,
            'vacancy_id' => $vacancy_id,
            'application' => $application,
            'examDetail' => $examDetail,
            'documents' => $documents,
            'admin_name' => $adminName,
        ]);
    }

    public function updateApplicantStatus(Request $request, $user_id, $vacancy_id)
    {
        $request->validate([
            'status' => 'required|string',
            'deadline_date' => 'nullable|date',
            'deadline_time' => 'nullable|date_format:H:i',
            'qs_education' => 'nullable|string',
            'qs_eligibility' => 'nullable|string',
            'qs_experience' => 'nullable|string',
            'qs_training' => 'nullable|string',
            'qs_result' => 'nullable|string',
            'application_remarks' => 'nullable|string',
        ]);

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->firstOrFail();

        $documentStatuses = $request->input('document_statuses', []);
        $documentRemarks = $request->input('document_remarks', []);

        // Track changes
        $changes = [];

        // Compare and store changed application fields
        $fieldsToCheck = [
            'status',
            'deadline_date',
            'deadline_time',
            'qs_education',
            'qs_eligibility',
            'qs_experience',
            'qs_training',
            'qs_result',
            'application_remarks',
        ];


        foreach ($fieldsToCheck as $field) {
            $newValue = $request->input($field);
            $oldValue = $application->$field;

            // Special formatting for time comparison
            if ($field === 'deadline_time') {
                $newValue = $newValue ? date('H:i', strtotime($newValue)) : null;
                $oldValue = $oldValue ? date('H:i', strtotime($oldValue)) : null;
            }

            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
                $application->$field = $newValue;
            }
        }

        // Application letter status and remarks
        $file_status = $documentStatuses['application_letter'] ?? null;
        $file_remarks = $documentRemarks['application_letter'] ?? null;

        if ($application->file_status !== $file_status) {
            $changes['application_letter_status'] = [
                'old' => $application->file_status,
                'new' => $file_status
            ];
            $application->file_status = $file_status;
        }

        if ($application->file_remarks !== $file_remarks) {
            $changes['application_letter_remarks'] = [
                'old' => $application->file_remarks,
                'new' => $file_remarks
            ];
            $application->file_remarks = $file_remarks;
        }

        $application->updated_by_admin_id = Auth::guard('admin')->id();
        $application->save();

        // Update Uploaded Documents and track changes
        foreach ($documentStatuses as $docType => $status) {
            $document = UploadedDocument::where('user_id', $user_id)
                ->where('document_type', $docType)
                ->first();

            if ($document) {
                $doc_changes = [];

                if ($document->status !== $status) {
                    $doc_changes['status'] = [
                        'old' => $document->status,
                        'new' => $status
                    ];
                    $document->status = $status;
                }

                $newRemark = $documentRemarks[$docType] ?? null;
                if ($document->remarks !== $newRemark) {
                    $doc_changes['remarks'] = [
                        'old' => $document->remarks,
                        'new' => $newRemark
                    ];
                    $document->remarks = $newRemark;
                }

                if (!empty($doc_changes)) {
                    $changes["document_$docType"] = $doc_changes;
                    $document->save();
                }
            }
        }

        //dd($changes);

        $userEmail = User::where('id', $user_id)->value('email');
        Mail::to($userEmail)->send(new NotifyApplicationStatus(
            auth('admin')->user()->username,
            $changes,
            $application->status,
            $user_id,
            $vacancy_id
        ));

        // Log only if there are changes
        if (!empty($changes)) {
            activity()
                ->causedBy(auth('admin')->user())
                ->performedOn(User::find($user_id))
                ->event('update')
                ->withProperties([
                    'user_id' => $user_id,
                    'vacancy_id' => $vacancy_id,
                    'changes' => $changes,
                    'section' => 'Application List'
                ])
                ->log('Updated applicant status and documents.');
        }

        return redirect()->back()->with('success', 'Changes updated successfully.');
    }

}
