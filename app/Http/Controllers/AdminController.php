<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Vacancy;
use App\Models\ExamDetail;
use App\Models\JobVacancy;
use App\Models\Applications;
use App\Models\Notification;
use App\Models\UploadedDocument;
use App\Models\User;
use App\Models\EducationalBackground;
use App\Models\WorkExperience;
use App\Models\LearningAndDevelopment;
use App\Models\CivilServiceEligibility;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Validator;


use Illuminate\Support\Facades\Mail;
use App\Mail\AdminEventNotification;



use App\Mail\NotifyApplicantOverview;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    private const DOCUMENT_LABELS = [
        'application_letter' => 'Application Letter',
        'signed_pds' => 'Signed Personal Data Sheet',
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
    private const DOCUMENT_TYPE_ALIASES = [
        'cert_eligibility' => ['cert_elegibility'],
        'cert_employment' => ['certificate_employment'],
        'grade_masteraldoctorate' => ['certificate_grades'],
        'tor_masteraldoctorate' => ['certified_tor'],
    ];

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    // List all admin accounts
    public function manage()
    {
        $admins = Admin::all();
        // $users = User::all(); // Removed to prevent fetching participants

        activity()
            ->causedBy(auth('admin')->user())
            ->event('view')
            ->withProperties(['section' => 'System Users Management'])
            ->log('Viewed admin account management.');

        return view('admin.admin_account_management', compact('admins'));
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

        // Get all years with applications, or default to current year
        $years = DB::table('applications')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        // If no years found, add current year as default
        if (empty($years)) {
            $years = [now()->year];
        }

        // Get monthly application counts for selected year
        $monthlyApplicants = DB::table('applications')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        //info('check');

        // Generate month labels (Jan, Feb, Mar, etc.)
        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Initialize all months with 0
        $monthCounts = array_fill(0, 12, 0);

        // Fill in actual counts
        foreach ($monthlyApplicants as $record) {
            $monthIndex = (int) $record->month - 1; // Convert to 0-based index
            $monthCounts[$monthIndex] = (int) $record->total;
        }

        //info('check');

        $chartLabels = $monthLabels;
        $chartData = $monthCounts;

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
        $upcomingExams = ExamDetail::whereRaw("TIMESTAMP(`date`, `time`) > ?", [$now])
            ->orderByRaw("TIMESTAMP(`date`, `time`)")
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
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
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

    private function getApplicantDocuments($user_id, $application)
    {
        $uploadedDocuments = UploadedDocument::where('user_id', $user_id)->get()->keyBy('document_type');
        $documents = [];

        foreach (UploadedDocument::DOCUMENTS as $docType) {
            if ($docType === 'isApproved')
                continue;

            $doc = $uploadedDocuments->get($docType);

            if ($docType === 'application_letter') {
                $status = $application->file_status ?? 'Not Submitted';
                // If status is null/empty for application letter, it might mean not submitted if file is missing,
                // but usually there's a file_storage_path.
                // Let's rely on file existence check in previewDocument, but here we just generate the link.

                $documents[] = [
                    'id' => 'application_letter',
                    'name' => self::DOCUMENT_LABELS['application_letter'],
                    'text' => self::DOCUMENT_LABELS['application_letter'],
                    'status' => $status,
                    'preview' => route('admin.preview_document', ['user_id' => $user_id, 'vacancy_id' => $application->vacancy_id, 'document_type' => 'application_letter']),
                    'remarks' => $application->file_remarks ?? '',
                    'last_modified_by' => $application->file_last_modified_by ?? 'N/A',
                    'isBold' => true,
                ];
            } else {
                $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                $status = $doc ? $doc->status : 'Not Submitted';
                $documents[] = [
                    'id' => $docType,
                    'name' => self::DOCUMENT_LABELS[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'text' => self::DOCUMENT_LABELS[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'status' => $status,
                    'preview' => route('admin.preview_document', ['user_id' => $user_id, 'vacancy_id' => $application->vacancy_id, 'document_type' => $docType]),
                    'remarks' => $doc ? ($doc->remarks ?: '') : '',
                    'original_name' => $doc->original_name ?? '',
                    'last_modified_by' => $doc->last_modified_by ?? 'N/A',
                    'isBold' => true,
                ];
            }
        }
        return $documents;
    }

    private function resolveUploadedDocument($uploadedDocuments, string $docType): ?UploadedDocument
    {
        $doc = $uploadedDocuments->get($docType);
        if ($doc && $doc->storage_path !== 'NOINPUT') {
            return $doc;
        }
        foreach (self::DOCUMENT_TYPE_ALIASES[$docType] ?? [] as $alias) {
            $aliasDoc = $uploadedDocuments->get($alias);
            if ($aliasDoc && $aliasDoc->storage_path !== 'NOINPUT') {
                return $aliasDoc;
            }
        }
        return $doc ?: null;
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

        $documents = $this->getApplicantDocuments($user_id, $application);

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
            'vacancy_type' => $vacancy->vacancy_type, // Needed for Phase 4
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

        $qs = $this->recalculateQualificationStatus($user_id, $vacancy_id);
        foreach (['qs_education', 'qs_eligibility', 'qs_experience', 'qs_training', 'qs_result'] as $field) {
            if ($application->$field !== $qs[$field]) {
                $changes[$field] = [
                    'old' => $application->$field,
                    'new' => $qs[$field],
                ];
                $application->$field = $qs[$field];
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

        // Notify other admins if there are changes
        // Notify other admins if there are changes
        if (!empty($changes)) {
            $admins = Admin::where('id', '!=', Auth::guard('admin')->id())->get();
            $applicantName = User::find($user_id)->name ?? 'Applicant';
            $positionTitle = JobVacancy::where('vacancy_id', $vacancy_id)->value('position_title');

            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\ApplicantRecordModifiedNotification(
                    Auth::guard('admin')->user()->name,
                    $applicantName,
                    $changes,
                    $positionTitle,
                    $user_id,
                    $vacancy_id
                ));
            }
        }

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

    public function updateDocumentStatusAjax(Request $request, $user_id, $vacancy_id)
    {
        $request->validate([
            'document_type' => 'required|string',
            'status' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $documentType = $request->input('document_type');
        $status = $request->input('status');
        $remarks = $request->input('remarks');

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->firstOrFail();

        // Get applicant name
        $applicantName = User::find($user_id)->name ?? 'Applicant';
        $docName = $documentType === 'application_letter' ? 'Application Letter' : ucwords(str_replace('_', ' ', $documentType));

        if ($documentType === 'application_letter') {
            if ($request->has('status'))
                $application->file_status = $status;
            if ($request->has('remarks'))
                $application->file_remarks = $remarks;

            // Update last modified by
            $application->file_last_modified_by = Auth::guard('admin')->user()->name;

            $application->save();

        } else {
            $document = UploadedDocument::where('user_id', $user_id)
                ->where('document_type', $documentType)
                ->first();

            if ($document) {
                if ($request->has('status'))
                    $document->status = $status;

                // Only update remarks if explicitly provided, but force it to empty string if verified
                // or ensure it is not null if it is being updated
                if ($request->has('remarks')) {
                    $document->remarks = $remarks ?? '';
                } elseif ($status === 'Verified') {
                    // When verifying, we often clear remarks, but we must ensure we don't save NULL
                    $document->remarks = '';
                }

                // Update last modified by
                $document->last_modified_by = Auth::guard('admin')->user()->name;

                $document->save();
            } else {
                // If document doesn't exist, create a placeholder record so status/remarks can be saved
                // This handles cases where admin wants to mark a missing document as "Needs Revision" or add remarks
                UploadedDocument::create([
                    'user_id' => $user_id,
                    'document_type' => $documentType,
                    'status' => $status ?? 'Pending',
                    'remarks' => $remarks ?? '',
                    'last_modified_by' => Auth::guard('admin')->user()->name, // Add last modified by
                    'original_name' => '', // Placeholder
                    'stored_name' => '',   // Placeholder
                    'storage_path' => '',  // Placeholder
                    'mime_type' => '',     // Placeholder
                    'file_size_8b' => 0,   // Placeholder
                ]);
            }

        }

        // Notify other admins if status changed
        // Notify other admins if status changed
        if ($request->has('status')) {
            $admins = Admin::where('id', '!=', Auth::guard('admin')->id())->get();
            $positionTitle = JobVacancy::where('vacancy_id', $vacancy_id)->value('position_title');

            $changesOrMessage = [
                "$docName Status" => $status
            ];

            // Should potentially include remarks if changed
            if ($request->has('remarks') && !empty($remarks)) {
                $changesOrMessage["$docName Remarks"] = $remarks;
            }

            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\ApplicantRecordModifiedNotification(
                    Auth::guard('admin')->user()->name,
                    $applicantName,
                    $changesOrMessage,
                    $positionTitle,
                    $user_id,
                    $vacancy_id
                ));
            }
        }

        return response()->json(['success' => true]);
    }

    public function updateApplicationRemarksAjax(Request $request, $user_id, $vacancy_id)
    {
        $request->validate([
            'application_remarks' => 'nullable|string',
        ]);

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->firstOrFail();

        $application->application_remarks = $request->input('application_remarks');
        $application->updated_by_admin_id = Auth::guard('admin')->id();
        $application->save();

        // Notify other admins
        $admins = Admin::where('id', '!=', Auth::guard('admin')->id())->get();
        $applicantName = User::find($user_id)->name ?? 'Applicant';
        $positionTitle = JobVacancy::where('vacancy_id', $vacancy_id)->value('position_title');

        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\ApplicantRecordModifiedNotification(
                Auth::guard('admin')->user()->name,
                $applicantName,
                ['Application Remarks' => $request->input('application_remarks')],
                $positionTitle,
                $user_id,
                $vacancy_id
            ));
        }

        return response()->json(['success' => true]);
    }

    public function notifyApplicant(Request $request, $user_id, $vacancy_id)
    {
        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->firstOrFail();

        // Update Deadline and QS data if provided in request
        $validatedData = $request->validate([
            'deadline_date' => 'nullable|date',
            'deadline_time' => 'nullable',
            'qs_education' => 'nullable|string',
            'qs_eligibility' => 'nullable|string',
            'qs_experience' => 'nullable|string',
            'qs_training' => 'nullable|string',
            'qs_result' => 'nullable|string',
        ]);

        if ($request->has('deadline_date')) {
            $application->deadline_date = $validatedData['deadline_date'];
        }
        if ($request->has('deadline_time')) {
            $application->deadline_time = $validatedData['deadline_time'] ? date('H:i', strtotime($validatedData['deadline_time'])) : null;
        }

        foreach (['qs_education', 'qs_eligibility', 'qs_experience', 'qs_training', 'qs_result'] as $field) {
            if ($request->has($field)) {
                $application->$field = $validatedData[$field];
            }
        }
        $application->save();

        $qs = $this->recalculateQualificationStatus($user_id, $vacancy_id);
        $application->fill($qs)->save();

        $documents = $this->getApplicantDocuments($user_id, $application);
        $userDocumentsSnapshot = $this->buildUserDocumentsSnapshot($user_id, $application);

        // --- Logic Check for Application Status Update ---
        $hasNeedsRevision = false;
        $allVerified = true;
        $submittedCount = 0;

        foreach ($documents as $doc) {
            $status = $doc['status'];

            // Skip documents that are not submitted
            if ($status === 'Not Submitted' || $status === 'Pending' && empty($doc['original_name']) && $doc['id'] !== 'application_letter') {
                // Note: 'Pending' might be default for placeholder docs, but if no file is attached (original_name empty), treat as not submitted?
                // Actually getApplicantDocuments sets status to 'Not Submitted' if $doc is null.
                // If $doc exists but status is 'Pending', it counts as submitted.
            }

            if ($status === 'Not Submitted') {
                continue;
            }

            $submittedCount++;

            if ($status === 'Needs Revision' || $status === 'Disapproved With Deficiency') {
                $hasNeedsRevision = true;
            }

            if ($status !== 'Verified' && $status !== 'Okay/Confirmed') {
                $allVerified = false;
            }
        }

        // Logic:
        // 1. If ANY document needs revision -> Status = Compliance
        // 2. If ALL submitted documents are verified -> Status = Qualified
        // 3. Otherwise -> Status stays as is (e.g. Pending)

        if ($hasNeedsRevision) {
            $application->status = 'Compliance';
        } elseif ($allVerified && $submittedCount > 0) {
            $application->status = 'Qualified';
        }

        $application->save();
        // -------------------------------------------------

        // --- Calculate Progress ---
        $totalDocuments = count($documents);
        $verifiedCount = collect($documents)->whereIn('status', ['Verified', 'Okay/Confirmed'])->count();
        $progressPercentage = $totalDocuments > 0 ? round(($verifiedCount / $totalDocuments) * 100) : 0;
        $progressCount = "$verifiedCount/$totalDocuments";

        // --- Retrieve Job Vacancy Details ---
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->first();
        $placeOfAssignment = $vacancy->place_of_assignment ?? 'N/A';
        $compensation = $vacancy->monthly_salary ?? 0;
        $vacancyType = $vacancy->vacancy_type ?? 'Plantilla';

        // --- Format Deadline ---
        $deadline = null;
        if ($application->deadline_date && $application->deadline_time) {
            try {
                $deadline = \Carbon\Carbon::parse($application->deadline_date . ' ' . $application->deadline_time)->format('F d, Y h:i A');
            } catch (\Exception $e) {
                $deadline = 'No deadline set';
            }
        } else {
            $deadline = 'No deadline set';
        }

        // --- Retrieve Qualification Standards ---
        $qsEducation = $qs['qs_education'] ?? 'no';
        $qsEligibility = $qs['qs_eligibility'] ?? 'no';
        $qsExperience = $qs['qs_experience'] ?? 'no';
        $qsTraining = $qs['qs_training'] ?? 'no';
        $qsResult = $qs['qs_result'] ?? 'Not Qualified';

        $userEmail = User::where('id', $user_id)->value('email');

        if (!$userEmail) {
            return response()->json(['success' => false, 'message' => 'User email not found.'], 404);
        }

        $messageTitle = 'Application Status Update';
        $messageBody = 'Your application has been reviewed. Please see the updated status.';
        $messageLevel = 'info';
        if ($hasNeedsRevision) {
            $messageTitle = 'Documents Need Revision';
            $messageBody = 'Some documents require revision. Please review the updates.';
            $messageLevel = 'warning';
        } elseif ($allVerified && $submittedCount > 0) {
            $messageTitle = 'Documents Verified';
            $messageBody = 'Your documents have been verified.';
            $messageLevel = 'success';
        }

        Notification::create([
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user_id,
            'type' => 'info',
            'data' => [
                'type' => 'application_overview',
                'vacancy_id' => $vacancy_id,
                'title' => $messageTitle,
                'message' => $messageBody,
                'level' => $messageLevel,
                'action_url' => route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id]),
                'documents' => $userDocumentsSnapshot,
                'application_status' => $application->status,
                'application_remarks' => $application->application_remarks,
                'qs_education' => $qsEducation,
                'qs_eligibility' => $qsEligibility,
                'qs_experience' => $qsExperience,
                'qs_training' => $qsTraining,
                'qs_result' => $qsResult,
                'deadline_date' => $application->deadline_date,
                'deadline_time' => $application->deadline_time,
                'last_modified_by' => Auth::guard('admin')->user()->name,
                'notified_at' => now()->toDateTimeString()
            ]
        ]);

        try {
            Mail::to($userEmail)->send(new NotifyApplicantOverview(
                $user_id,
                $vacancy_id,
                $userDocumentsSnapshot,
                $application->application_remarks,
                $placeOfAssignment,
                $compensation,
                $deadline,
                $qsEducation,
                $qsEligibility,
                $qsExperience,
                $qsTraining,
                $qsResult,
                $progressPercentage,
                $progressCount,
                $vacancyType
            ));
        } catch (\Exception $e) {
            \Log::error('Mail sending failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()]);
        }

        // Notify all admins (except actor) about the notification sent to applicant
        $admins = Admin::where('id', '!=', Auth::guard('admin')->id())->get();
        $actorName = Auth::guard('admin')->user()->name;
        $applicantName = User::where('id', $user_id)->value('name');
        $positionTitle = JobVacancy::where('vacancy_id', $vacancy_id)->value('position_title');
        $occurredAt = now();
        foreach ($admins as $admin) {
            Notification::create([
                'notifiable_type' => 'App\Models\Admin',
                'notifiable_id' => $admin->id,
                'type' => 'info',
                'data' => [
                    'title' => 'Applicant Notified',
                    'message' => $actorName . ' notified ' . ($applicantName ?: 'Applicant'),
                    'link' => route('admin.applicant_status', ['user_id' => $user_id, 'vacancy_id' => $vacancy_id]),
                ]
            ]);

            if ($admin->email) {
                try {
                    \Log::info('Attempting admin notify email', [
                        'recipient' => $admin->email,
                        'actor' => $actorName,
                        'applicant' => $applicantName,
                        'vacancy_id' => $vacancy_id,
                        'time' => now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s T')
                    ]);

                    $timezone = config('app.timezone') ?: 'UTC';
                    $timestamp = now()->timezone($timezone)->format('Y-m-d H:i:s T');

                    $verifiedDocs = array_values(array_filter($userDocumentsSnapshot, function ($d) {
                        $status = strtoupper((string)($d['status'] ?? ''));
                        return in_array($status, ['VERIFIED', 'NEEDS REVISION']);
                    }));

                    Mail::to($admin->email)->send(new \App\Mail\AdminNotifyApplicant(
                        $actorName,
                        $applicantName,
                        $vacancy_id,
                        $positionTitle,
                        $verifiedDocs,
                        $timestamp,
                        $timezone
                    ));
                    \Log::info('Admin notify email sent', ['recipient' => $admin->email]);
                } catch (\Throwable $e) {
                    \Log::error('Admin notify email failed', ['error' => $e->getMessage()]);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Applicant notified successfully.']);
    }

    private function buildUserDocumentsSnapshot($user_id, $application): array
    {
        $uploadedDocuments = UploadedDocument::where('user_id', $user_id)->get()->keyBy('document_type');
        $documents = [];

        foreach (UploadedDocument::DOCUMENTS as $docType) {
            if ($docType === 'isApproved')
                continue;

            if ($docType === 'application_letter') {
                $documents[] = [
                    'id' => 'application_letter',
                    'doc_id' => null,
                    'name' => self::DOCUMENT_LABELS['application_letter'],
                    'text' => self::DOCUMENT_LABELS['application_letter'],
                    'status' => $application->file_status ?? 'Not Submitted',
                    'preview' => $application->file_storage_path
                        ? url('/preview-file/' . base64_encode($application->file_storage_path))
                        : '',
                    'remarks' => $application->file_remarks ?? '',
                    'last_modified_by' => $application->file_last_modified_by ?? null,
                    'isBold' => true,
                ];
                continue;
            }

            $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
            $hasFile = $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
            $documents[] = [
                'id' => $docType,
                'doc_id' => $doc->id ?? null,
                'name' => self::DOCUMENT_LABELS[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                'text' => self::DOCUMENT_LABELS[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                'status' => $hasFile ? ($doc->status ?? 'Pending') : 'Not Submitted',
                'preview' => $hasFile ? url('/preview-file/' . base64_encode($doc->storage_path)) : '',
                'remarks' => $doc ? ($doc->remarks ?: '') : '',
                'last_modified_by' => $doc->last_modified_by ?? null,
                'isBold' => true,
            ];
        }

        return $documents;
    }

    private function recalculateQualificationStatus(int $userId, string $vacancyId): array
    {
        $vacancy = JobVacancy::where('vacancy_id', $vacancyId)->first();
        $educationReq = $this->normalizeRequirement($vacancy?->qualification_education ?? null);
        $eligibilityReq = $this->normalizeRequirement($vacancy?->qualification_eligibility ?? null);
        $experienceReq = $this->normalizeRequirement($vacancy?->qualification_experience ?? null);
        $trainingReq = $this->normalizeRequirement($vacancy?->qualification_training ?? null);

        $education = EducationalBackground::where('user_id', $userId)->first();
        $hasCollege = $this->arrayHasValue($education?->college);
        $hasGrad = $this->arrayHasValue($education?->grad);
        $hasVoc = $this->arrayHasValue($education?->vocational);
        $hasAnyEducation = $hasCollege || $hasGrad || $hasVoc || !empty($education?->elem_school) || !empty($education?->jhs_school);

        $educationMet = 'no';
        if (!$educationReq) {
            $educationMet = 'na';
        } else {
            $reqLower = strtolower($educationReq);
            if (str_contains($reqLower, 'master') || str_contains($reqLower, 'doctor')) {
                $educationMet = $hasGrad ? 'yes' : 'no';
            } elseif (str_contains($reqLower, 'bachelor') || str_contains($reqLower, 'college')) {
                $educationMet = ($hasCollege || $hasGrad) ? 'yes' : 'no';
            } elseif (str_contains($reqLower, 'vocational')) {
                $educationMet = $hasVoc ? 'yes' : 'no';
            } else {
                $educationMet = $hasAnyEducation ? 'yes' : 'no';
            }
        }

        $experienceMet = 'no';
        if (!$experienceReq) {
            $experienceMet = 'na';
        } else {
            $requiredMonths = $this->parseRequirementMonths($experienceReq);
            $workExperiences = WorkExperience::where('user_id', $userId)->get();
            $hasExperience = $workExperiences->isNotEmpty();
            $totalMonths = 0;
            foreach ($workExperiences as $work) {
                if (!$work->work_exp_from) {
                    continue;
                }
                $from = Carbon::parse($work->work_exp_from);
                $to = $work->work_exp_to ? Carbon::parse($work->work_exp_to) : Carbon::now();
                if ($to->lessThan($from)) {
                    continue;
                }
                $totalMonths += $from->diffInMonths($to) + 1;
            }
            if ($requiredMonths === null) {
                $experienceMet = $hasExperience ? 'yes' : 'no';
            } else {
                $experienceMet = $totalMonths >= $requiredMonths ? 'yes' : 'no';
            }
        }

        $trainingMet = 'no';
        if (!$trainingReq) {
            $trainingMet = 'na';
        } else {
            $requiredHours = $this->parseRequirementHours($trainingReq);
            $trainingHours = LearningAndDevelopment::where('user_id', $userId)->sum('learning_hours');
            $hasTraining = LearningAndDevelopment::where('user_id', $userId)->exists();
            if ($requiredHours === null) {
                $trainingMet = $hasTraining ? 'yes' : 'no';
            } else {
                $trainingMet = $trainingHours >= $requiredHours ? 'yes' : 'no';
            }
        }

        $eligibilityMet = 'no';
        if (!$eligibilityReq) {
            $eligibilityMet = 'na';
        } else {
            $eligibilityMet = CivilServiceEligibility::where('user_id', $userId)->exists() ? 'yes' : 'no';
        }

        $requiredStatuses = collect([$educationMet, $eligibilityMet, $experienceMet, $trainingMet])
            ->filter(fn($value) => $value !== 'na');
        $qsResult = $requiredStatuses->isEmpty() || $requiredStatuses->every(fn($value) => $value === 'yes')
            ? 'Qualified'
            : 'Not Qualified';

        return [
            'qs_education' => $educationMet,
            'qs_eligibility' => $eligibilityMet,
            'qs_experience' => $experienceMet,
            'qs_training' => $trainingMet,
            'qs_result' => $qsResult,
        ];
    }

    private function normalizeRequirement(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $lower = strtolower($value);
        if (in_array($lower, ['na', 'n/a', 'none', 'not applicable', '-'], true)) {
            return null;
        }
        return $value;
    }

    private function parseRequirementMonths(string $value): ?int
    {
        $lower = strtolower($value);
        if (preg_match('/(\d+(?:\.\d+)?)/', $lower, $matches)) {
            $amount = (float) $matches[1];
            if (str_contains($lower, 'month')) {
                return (int) round($amount);
            }
            if (str_contains($lower, 'year')) {
                return (int) round($amount * 12);
            }
        }
        return null;
    }

    private function parseRequirementHours(string $value): ?int
    {
        $lower = strtolower($value);
        if (preg_match('/(\d+(?:\.\d+)?)/', $lower, $matches)) {
            $amount = (float) $matches[1];
            return (int) round($amount);
        }
        return null;
    }

    private function arrayHasValue($array): bool
    {
        if (!is_array($array)) {
            return false;
        }
        foreach ($array as $value) {
            if (is_array($value)) {
                if ($this->arrayHasValue($value)) {
                    return true;
                }
            } elseif (is_scalar($value) && trim((string) $value) !== '') {
                return true;
            }
        }
        return false;
    }

    public function previewDocument($user_id, $vacancy_id, $document_type)
    {
        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->first();

        if (!$application) {
            abort(404);
        }

        $path = null;

        if ($document_type === 'application_letter') {
            $path = $application->file_storage_path;
        } else {
            $doc = UploadedDocument::where('user_id', $user_id)
                ->where('document_type', $document_type)
                ->first();
            if (!$doc) {
                foreach (self::DOCUMENT_TYPE_ALIASES[$document_type] ?? [] as $alias) {
                    $doc = UploadedDocument::where('user_id', $user_id)
                        ->where('document_type', $alias)
                        ->first();
                    if ($doc) {
                        break;
                    }
                }
            }
            if ($doc) {
                $path = $doc->storage_path;
            }
        }

        // Helper to return "No Document Submitted" view
        $noDocumentView = function () {
            return response('
                <html>
                <body style="display:flex;justify-content:center;align-items:center;height:100%;margin:0;font-family:sans-serif;background-color:#f9fafb;color:#6b7280;">
                    <div style="text-align:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:1rem;display:inline-block;"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                        <p style="font-size:1.125rem;font-weight:500;">No Document Submitted</p>
                    </div>
                </body>
                </html>
            ', 200);
        };

        if (!$path || $path === 'NOINPUT') {
            return $noDocumentView();
        }

        // Check explicit paths
        $possiblePaths = [
            storage_path('app/' . $path),
            storage_path('app/public/' . $path),
            public_path('storage/' . $path)
        ];

        $fullPath = null;
        foreach ($possiblePaths as $p) {
            if (file_exists($p)) {
                $fullPath = $p;
                break;
            }
        }

        if (!$fullPath) {
            // Try Storage facade as fallback
            if (Storage::exists($path)) {
                $file = Storage::get($path);
                $type = Storage::mimeType($path);
                return response($file, 200)->header("Content-Type", $type);
            }
            return $noDocumentView();
        }

        $file = file_get_contents($fullPath);
        $type = mime_content_type($fullPath);

        return response($file, 200)->header("Content-Type", $type);
    }

}
