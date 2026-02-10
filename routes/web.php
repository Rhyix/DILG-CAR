<?php

use Illuminate\Support\Facades\{Route, Auth};
use App\Http\Controllers\Forms;
use App\Http\Controllers\Auth\{
    RegisterController,
    LoginController,
    ForgotPasswordController,
    GoogleController,
    AdminAuthController
};
use App\Http\Controllers\{
    activityLogController,
    VacancyController,
    JobVacancyController,
    ExamController,
    ShowApplicantsProfile,
    AdminController,
    GeminiChatController,
    WorkExpSheetController,
    ExportController,
    ImportController,
};
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\ViewerAccess;
use App\Http\Middleware\BlockIfAdmin;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;

use App\Events\PackageSent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

// ==================================================================================================
// HOME ROUTE - Smart redirect based on authentication
// ==================================================================================================
Route::get('/', function () {
    if (Auth::guard('admin')->check()) {
        $user = Auth::guard('admin')->user();
        return $user->role === 'viewer'
            ? redirect()->route('viewer')
            : redirect()->route('dashboard_admin');
    } elseif (Auth::check()) {
        return redirect()->route('dashboard_user');
    } else {
        return redirect()->route('login.form');
    }
})->name('dashboard');

// ==================================================================================================
// PUBLIC ROUTES (No authentication required)
// ==================================================================================================

// ==================================================================================================
// Registration, OTP, Login and Logout
// ==================================================================================================
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::get('/otp', [RegisterController::class, 'OTPForm'])->name('otp');
Route::post('/otp', [RegisterController::class, 'OTPCheck'])->name('otp_check');

Route::get('/exam/confirm/{token}', [ExamController::class, 'confirmNotification'])->name('exam.confirm_notification');
Route::post('/otp/resend', [RegisterController::class, 'resendOTP'])->name('otp_resend');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login')->middleware('throttle:5,1');

// ==================================================================================================
// Reset Password
// ==================================================================================================
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('forgot.password.form');
Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('forgot.password.send.otp');
Route::get('/forgot-password/otp', [ForgotPasswordController::class, 'showOtpForm'])->name('forgot.password.otp.form');
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('forgot.password.verify.otp');
Route::get('/forgot-password/otp/resend', [ForgotPasswordController::class, 'resendOtp'])->name('forgot.password.otp.resend');
Route::get('/forgot-password/reset/{email}', [ForgotPasswordController::class, 'showResetForm'])->name('forgot.password.reset.form');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('forgot.password.reset');

Route::get('/', fn() => redirect()->route('dashboard'))->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard_user', [JobVacancyController::class, 'getOpenVacanciesForDashboard'])->name('dashboard')->middleware(\App\Http\Middleware\RunDailyTask::class);
});

// ==================================================================================================
// USER LOGOUT
// ==================================================================================================
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// ==================================================================================================
// PDS ROUTES
// ==================================================================================================
/*
Route::get('/pds/c1', [Forms\PDSController::class, 'c1DisplayForm'])->name('display_c1')->middleware('auth');
Route::post('/pds/submit_c1', [Forms\PDSController::class, 'c1UpdateFormSession'])->name('submit_c1')->middleware('auth');

Route::get('/pds/c2', [Forms\PDSController::class, 'c2DisplayForm'])->name('display_c2')->middleware('auth');
Route::post('/pds/submit_c2', [Forms\PDSController::class, 'c2UpdateFormSession'])->name('submit_c2')->middleware('auth');
Route::delete('/c2/d/{target_row}/{id}', [Forms\PDSController::class, 'c2DeleteRow']);

Route::get('/pds/c5', [Forms\PDSController::class, 'c5DisplayForm'])->name('display_c5')->middleware('auth');
Route::post('/pds/finalize', [Forms\PDSController::class, 'finalizePDS'])->name('finalize_pds')->middleware('auth');

Route::get('/pds/submit', [Forms\PDSController::class, 'showSubmittedForm'])->name('display_final_pds')->middleware('auth');

// ==================================================================================================
// PDS ROUTES
// GOOGLE AUTH ROUTES
// ==================================================================================================

// Exporting PDF (WIP)
Route::get('/export-pds/{id}', [Forms\ExportPDSController::class, 'exportPDS'])->name('export.pds');

// ---------------------------------------
// PDS C3 ROUTES
// ---------------------------------------
Route::post('/c3_submit', [Forms\PDSController::class, 'c3SubmitForm'])->name('c3_submit');
Route::get('/c3_submit', [Forms\PDSController::class, 'c3ShowForm'])->name('c3_show');

// ---------------------------------------
// PDS C4 ROUTES
// ---------------------------------------
Route::post('/c4_submit', [Forms\PDSController::class, 'c4SubmitForm'])->name('c4_submit');
Route::get('/c4_submit', [Forms\PDSController::class, 'c4ShowForm'])->name('c4_show');


// Function call below is reimplemented. Do not uncomment. 💩
// Route::post('/c5_submit', [Forms\C5controller::class, 'c5SubmitForm'])->name('c5_submit');

Route::view('/pds_update', 'pds_update.pds_update')->name('pds_update');
Route::view('/c2_update', 'pds_update.c2_update')->name('c2_update');
Route::view('/c3_update', 'pds_update.c3_update')->name('c3_update');
Route::view('/c4_update', 'pds_update.c4_update')->name('c4_update');
Route::view('/c5_update', 'pds_update.c5_update')->name('c5_update');
Route::view('/submit_update', 'pds_update.submit_update')->name('submit_update');
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// ==================================================================================================
// USER ROUTES
// ADMIN AUTH ROUTES (accessible when not authenticated as admin)
// ==================================================================================================
/*
Route::view('/dashboard', 'dashboard_user.dashboard_user')->name('dashboard_user');
Route::get('/job-vacancies', [JobVacancyController::class, 'jobVacancy'])->name('job_vacancy');
Route::get('/{id}/job_description', [JobVacancyController::class, 'jobDescription'])->name('job_description');
Route::get('/job-vacancies/filter', [JobVacancyController::class, 'filterVacancy'])->name('vacancies.filter');
Route::view('/my_applications', 'dashboard_user.my_applications')->name('my_applications');
Route::view('/application_status/{}', 'dashboard_user.application_status')->name('application_status');
Route::view('/about', 'dashboard_user.about')->name('about');
Route::get('/pds_print', fn() => view('dashboard_user.pds_print'))->name('pds_print');
*/

//Route::middleware(['auth', BlockIfAdmin::class])   // 👈 here!
//->group(function () {

Route::get('/dashboard', [JobVacancyController::class, 'getOpenVacanciesForDashboard'])->name('dashboard_user');

Route::view('/about', 'dashboard_user.about')
    ->name('about');

Route::middleware(['auth'])->group(function () {
    Route::get('/my_applications', [JobVacancyController::class, 'myApplications'])->name('my_applications');
});
Route::get('/my-applications/sort', [JobVacancyController::class, 'sortMyApplications'])->name('my_applications.sort');
// User application status get route
Route::get('/application_status/{user}/{vacancy}', [JobVacancyController::class, 'applicationStatus'])->name('application_status');

Route::get('/job-vacancies', [JobVacancyController::class, 'jobVacancy'])
    ->name('job_vacancy');

Route::get('/{id}/job_description', [JobVacancyController::class, 'jobDescription'])
    ->name('job_description');

Route::get('/job-vacancies/filter', [JobVacancyController::class, 'filterVacancy'])
    ->name('vacancies.filter');

Route::get('/pds_print', fn() => view('dashboard_user.pds_print'))
    ->name('pds_print');

//Route::middleware('guest:admin')->group(function () {
//    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
//    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
//});

// ==================================================================================================
// PDS ROUTES
// ADMIN LOGOUT (accessible when authenticated as admin)
// ==================================================================================================
Route::get('/pds/c1', [Forms\PDSController::class, 'c1DisplayForm'])->name('display_c1')->middleware('auth');
Route::post('/pds/submit_c1/{go_to}', [Forms\PDSController::class, 'c1UpdateFormSession'])->name('submit_c1')->middleware('auth');

Route::get('/pds/c2', [Forms\PDSController::class, 'c2DisplayForm'])->name('display_c2')->middleware('auth');
Route::post('/pds/submit_c2/{go_to}', [Forms\PDSController::class, 'c2UpdateFormSession'])->name('submit_c2')->middleware('auth');
Route::delete('/c2/d/{target_row}/{id}', [Forms\PDSController::class, 'c2DeleteRow']);

Route::get('/pds/c3', [Forms\PDSController::class, 'c3ShowForm'])->name('display_c3')->middleware('auth');
Route::post('/pds/submit_c3/{go_to}', [Forms\PDSController::class, 'c3SubmitForm'])->name('submit_c3')->middleware('auth');

Route::get('/pds/c4', [Forms\PDSController::class, 'c4ShowForm'])->name('display_c4')->middleware('auth');
Route::post('/pds/submit_c4/{go_to}', [Forms\PDSController::class, 'c4SubmitForm'])->name('submit_c4')->middleware('auth');

Route::get('/pds/c5', [Forms\PDSController::class, 'c5DisplayForm'])->name('display_c5')->middleware('auth');
Route::post('/pds/finalize/{go_to}', [Forms\PDSController::class, 'finalizePDS'])->name('finalize_pds')->middleware('auth');

Route::get('/pds/submit', [Forms\PDSController::class, 'showSubmittedForm'])->name('display_final_pds')->middleware('auth');

//});
Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// PDS and WES Export
Route::get('/export-pds', [Forms\ExportPDSController::class, 'exportPDS'])->name('export.pds');
Route::get('/export-wes', [Forms\ExportWESController::class, 'exportWES'])->name('export.wes');

// ==================================================================================================
// ADMIN AUTH ROUTES
// ==================================================================================================
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit')->middleware('throttle:5,1');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
// USER ROUTES (blocked if admin is logged in)
// ==================================================================================================
Route::middleware(['auth', BlockIfAdmin::class])->group(function () {
    Route::get('/dashboard', [JobVacancyController::class, 'getOpenVacanciesForDashboard'])->name('dashboard_user')->middleware(\App\Http\Middleware\RunDailyTask::class);

    Route::get('/about', fn() => view('dashboard_user.about'))->name('about');

    Route::get('/my_applications', [JobVacancyController::class, 'myApplications'])->name('my_applications');

    Route::get('/work_experience', [WorkExpSheetController::class, 'show'])->name('work_experience');
    Route::post('/work_experience', [WorkExpSheetController::class, 'store'])->name('work_experience_store');

    Route::get('/application_status/{user}/{vacancy}', [JobVacancyController::class, 'applicationStatus'])->name('application_status');

    Route::get('/job-vacancies', [JobVacancyController::class, 'jobVacancy'])->name('job_vacancy');

    Route::get('/{id}/job_description', [JobVacancyController::class, 'jobDescription'])->name('job_description');

    Route::get('/job-vacancies/filter', [JobVacancyController::class, 'filterVacancy'])->name('vacancies.filter');

    Route::get('/pds_print', fn() => view('dashboard_user.pds_print'))->name('pds_print');

    // ==================================================================================================
    // PDS ROUTES
    // ==================================================================================================
    Route::get('/pds/c1', [Forms\PDSController::class, 'c1DisplayForm'])->name('display_c1');
    Route::post('/pds/submit_c1', [Forms\PDSController::class, 'c1UpdateFormSession'])->name('submit_c1');

    Route::get('/pds/c2', [Forms\PDSController::class, 'c2DisplayForm'])->name('display_c2');
    Route::post('/pds/submit_c2', [Forms\PDSController::class, 'c2UpdateFormSession'])->name('submit_c2');
    Route::delete('/c2/d/{target_row}/{id}', [Forms\PDSController::class, 'c2DeleteRow']);

    Route::get('/pds/c3', [Forms\PDSController::class, 'c3ShowForm'])->name('display_c3');
    Route::post('/pds/submit_c3', [Forms\PDSController::class, 'c3SubmitForm'])->name('submit_c3');

    Route::get('/pds/c4', [Forms\PDSController::class, 'c4ShowForm'])->name('display_c4');
    Route::post('/pds/submit_c4', [Forms\PDSController::class, 'c4SubmitForm'])->name('submit_c4');

    Route::get('/pds/wes', [WorkExpSheetController::class, 'show'])->name('display_wes');

    Route::get('/pds/c5', [Forms\PDSController::class, 'c5DisplayForm'])->name('display_c5');
    Route::post('/pds/finalize', [Forms\PDSController::class, 'finalizePDS'])->name('finalize_pds');
    Route::post('/application-status/{user_id}/{vacancy_id}/upload', [Forms\PDSController::class, 'uploadApplicationDocuments'])->name('application_status.upload');

    Route::get('/pds/submit', [Forms\PDSController::class, 'showSubmittedForm'])->name('display_final_pds');

    // Exporting PDF (WIP)
    // Route::get('/export-pds/{id}', [Forms\ExportPDSController::class, 'exportPDS'])->name('export.pds');

    // PDS Update Routes
    Route::view('/pds_update', 'pds_update.pds_update')->name('pds_update');
    Route::view('/c2_update', 'pds_update.c2_update')->name('c2_update');
    Route::view('/c3_update', 'pds_update.c3_update')->name('c3_update');
    //Sample
    Route::view('/c4_sample', 'pds.c4-sample')->name('c4-sample');
    Route::view('/c5_update', 'pds_update.c5_update')->name('c5_update');
    //Route::view('/submit_update', 'pds_update.submit_update')->name('submit_update');

    // APPLICATION ROUTE
    Route::post('/apply/{vacancy_id}', [JobVacancyController::class, 'apply'])->name('application.store');

    // =========================
    // Notifications
    // =========================
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');
    Route::get('/notifications/count', [NotificationController::class, 'unreadCount'])->name('notifications.count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all', [NotificationController::class, 'markAll'])->name('notifications.mark_all');
    Route::post('/notifications/cleanup', [NotificationController::class, 'cleanup'])->name('notifications.cleanup');

    // =========================
    // Profile
    // =========================
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::get('/profile/password', fn() => view('profile.password'))->name('profile.password.form');
    Route::post('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

// ==================================================================================================
// ADMIN PROTECTED ROUTES
// ADMIN ROUTES (only accessible to authenticated admins with admin role)
// ==================================================================================================
//Route::middleware(RedirectIfNotAdmin::class)->group(function () {

Route::middleware([RedirectIfNotAdmin::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('home_admin');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('dashboard_admin')->middleware(\App\Http\Middleware\RunDailyTask::class);
    Route::get('/admin/admin_account_management', [AdminController::class, 'manage'])->name('admin_account_management');
    Route::post('/admin/store', [AdminController::class, 'store'])->name('admin.store');
    Route::post('/admin/{id}/deactivate', [AdminController::class, 'deactivate'])->name('admin.deactivate');
    Route::post('/admin/{id}/activate', [AdminController::class, 'activate'])->name('admin.activate');
    Route::put('/admin/{id}/update', [AdminController::class, 'update'])->name('admin.update');
    Route::get('/admin/search', [AdminController::class, 'search'])->name('admin.search');

    Route::get('/admin/vacancies_management/add/plantilla', function () {
        return view('admin.vacancy_add_plantilla');
    })->name('addplantilla');
    Route::get('/admin/vacancies_management/add/cos', function () {
        return view('admin.vacancy_add_cos');
    })->name('addcos');

    Route::post('/admin/vacancies_management/add/cos/store', [JobVacancyController::class, 'storeVacancy'])->name('vacancies.store');
    // Route::put('/admin/vacancies_management/cos/{vacancy}/update', [JobVacancyController::class, 'update'])->name('vacancy.update');
    //Route::get('/admin/vacancies_management/add', fn() => view('admin.vacancy_add'))->name('add_job_vacancy_form');
    //Route::post('/admin/vacancies_management/add', [JobVacancyController::class, 'storeVacancy'])->name('add_job_vacancy');
    Route::post('/admin/vacancies_management/add/plantilla/store', [JobVacancyController::class, 'storeVacancy'])->name('plantilla.store');
    Route::put('/admin/vacancies/plantilla/{vacancy_id}/edit', [JobVacancyController::class, 'update'])->name('plantilla.update');
    Route::get('/admin/vacancies_management', [JobVacancyController::class, 'jobVacancyManagement'])->name('vacancies_management');
    Route::get('/admin/vacancies_management/filter', [JobVacancyController::class, 'adminFilterVacancy'])->name('admin.vacancies.filter');
    Route::get('/admin/vacancies/{vacancy_id}/edit', [JobVacancyController::class, 'edit'])->name('vacancies.edit');
    Route::put('/admin/vacancies/cos/{vacancy_id}/edit', [JobVacancyController::class, 'update'])->name('vacancies.update');
    Route::delete('/admin/vacancies/{vacancy_id}/delete', [JobVacancyController::class, 'delete'])->name('vacancies.delete');
    Route::get('/admin/applicant_status/{user_id}/{vacancy_id}', [AdminController::class, 'viewApplicantStatus'])->name('admin.applicant_status');
    Route::post('/admin/applicant_status/{user_id}/{vacancy_id}', [AdminController::class, 'updateApplicantStatus'])->name('admin.applicant_status.update');

    Route::get("/admin/activity_log", [activityLogController::class, 'view'])->name('admin_activity_log');
    Route::get('/admin/activity-log/data', [activityLogController::class, 'fetch'])->name('admin.activity_log.fetch');

});

// Routes accessible by both admin and viewer
// ==================================================================================================
// VIEWER ROUTES (accessible to both admin and viewer roles)
// ==================================================================================================
Route::middleware([ViewerAccess::class])->group(function () {
    // Viewer routes
    Route::get('/viewer', fn() => view('viewer.viewer_dashboard'))->name('viewer');
    Route::get('/viewer/exam_management', fn() => view('viewer.viewer_exam_management'))->name('viewer.exam_management');
    Route::get('/viewer/exam_management/view_exam', fn() => view('viewer.viewer_answer_view'))->name('viewer.view_exam');


    // Exam management routes (accessible by both admin and viewer)
    Route::get('/admin/exam_management', [ExamController::class, 'examManagement'])->name('admin_exam_management');
    Route::get('/admin/exam_management/{vacancy_id}/view_exam/{user_id}', [ExamController::class, 'viewExam'])->name('admin.view_exam');
    Route::post('/admin/exam_management/{vacancy_id}/view_exam/{user_id}', [ExamController::class, 'saveResult'])->name('admin.save_result');
    Route::get('/admin/exam_management/{vacancy_id}/manage', [ExamController::class, 'manageExam'])->name('admin.manage_exam');
    Route::get('/admin/exam_management/{vacancy_id}/qualified', [ExamController::class, 'getQualifiedApplicants'])->name('admin.exam.qualified');
    Route::post('/admin/exam_management/{vacancy_id}/notify', [ExamController::class, 'notifyApplicants'])->name('admin.exam_notify');
    //Route::get('/admin/exam_management/{vacancy_id}/notify', [ExamController::class, 'notifyApplicants'])->name('admin.exam_notify');
    Route::post('/admin/exam_management/{vacancy_id}/details/save', [ExamController::class, 'saveExamDetails']);
    Route::post('/admin/exam_management/{vacancy_id}/start', [ExamController::class, 'startExam'])->name('admin.exam_start');
    Route::get('/admin/exam_management/{vacancy_id}/lobby-data', [ExamController::class, 'getLobbyData'])->name('admin.exam.lobby_data');
    Route::post('/admin/exam_management/{vacancy_id}/notify-selected', [ExamController::class, 'notifySelectedApplicants'])->name('admin.exam.notify_selected');


<<<<<<< Updated upstream
    // Exam Library Routes
    Route::get('/admin/exam-library', [App\Http\Controllers\ExamLibraryController::class, 'index'])->name('admin.exam_library');
    Route::post('/admin/exam-library/series', [App\Http\Controllers\ExamLibraryController::class, 'storeSeries'])->name('admin.exam_library.series.store');
    Route::put('/admin/exam-library/series/{id}', [App\Http\Controllers\ExamLibraryController::class, 'updateSeries'])->name('admin.exam_library.series.update');
    Route::delete('/admin/exam-library/series/{id}', [App\Http\Controllers\ExamLibraryController::class, 'deleteSeries'])->name('admin.exam_library.series.delete');
    Route::get('/admin/exam-library/series/{id}', [App\Http\Controllers\ExamLibraryController::class, 'getSeriesQuestions'])->name('admin.exam_library.series.show');
    Route::get('/admin/exam-library/series/{id}/questions', [App\Http\Controllers\ExamLibraryController::class, 'getSeriesQuestions'])->name('admin.exam_library.series.questions');
    Route::post('/admin/exam-library/series/{id}/questions', [App\Http\Controllers\ExamLibraryController::class, 'storeQuestion'])->name('admin.exam_library.questions.store');
    Route::put('/admin/exam-library/questions/{id}', [App\Http\Controllers\ExamLibraryController::class, 'updateQuestion'])->name('admin.exam_library.questions.update');
    Route::delete('/admin/exam-library/questions/{id}', [App\Http\Controllers\ExamLibraryController::class, 'deleteQuestion'])->name('admin.exam_library.questions.delete');
    Route::get('/admin/exam-library/questions/selection', [App\Http\Controllers\ExamLibraryController::class, 'getQuestionsForSelection'])->name('admin.exam_library.questions.selection');

=======
    Route::get('/admin/exam_library', fn() => view('admin.exam_library'))->name('admin.exam_library');
>>>>>>> Stashed changes
    Route::get('/admin/exam_management/{vacancy_id}/edit', [ExamController::class, 'editExam'])->name('admin.exam.edit');
    Route::post('/admin/exam_management/{vacancy_id}/edit', [ExamController::class, 'updateExam'])->name('admin.exam.update');

    //Export
    Route::get('/export-job-vacancies-cos', [ExportController::class, 'exportCOS'])->name('exportJobVacancyCOS');
    Route::get('/export-job-vacancies-plantilla', [ExportController::class, 'exportPlantilla'])->name('exportJobVacancyPlantilla');
    Route::get('/export-job-vacancies-all', [ExportController::class, 'exportAllVacancies'])->name('exportJobVacancyAll');
    Route::get('/export-activities-all', [ExportController::class, 'exportActivities'])->name('exportActivities');
    Route::get('/export/reviewed-applications/{vacancy_id}', [ExportController::class, 'exportReviewedApplications'])->name('exportReviewed');
    Route::get('/export/not-reviewed-applications/{vacancy_id}', [ExportController::class, 'exportNotReviewedApplications'])->name('exportNotReviewed');


    //Import
    Route::post('/import-job-vacancy-cos', [ImportController::class, 'importCOS'])->name('importJobVacancyCOS');
    Route::post('/import-job-vacancy-plantilla', [ImportController::class, 'importPlantilla'])->name('importJobVacancyPlantilla');
    Route::get('/download-cos-template', [ImportController::class, 'downloadCOSTemplate'])->name('downloadCOSTemplate');
    Route::get('/download-plantilla-template', [ImportController::class, 'downloadPlantillaTemplate'])->name('downloadPlantillaTemplate');


});

// ==================================================================================================
// EXAM ROUTES
// EXAM ROUTES (for users taking exams)

// ==================================================================================================
Route::get('/exam/{vacancy_id}/questions', [ExamController::class, 'examQuestion'])->name('user.exam_question_page');
Route::get('/exam/{vacancy_id}/lobby', [ExamController::class, 'submit'])->name('user.exam_lobby');
Route::post('/exam/{vacancy_id}/submit', [ExamController::class, 'submit'])->name('exam.submit');
Route::get('/exam/{vacancy_id}/thankyou', fn() => view('exam_user.exam_thankyou'))->name('user.exam_thankyou');
Route::post('/log-switch', [ExamController::class, 'logSwitch']);

// ==================================================================================================
// VIEWER ROUTES
// ==================================================================================================
//Route::get('/viewer', fn() => view('viewer.viewer_dashboard'))->name('viewer');
//Route::get('/viewer/exam_management', fn() => view('viewer.viewer_exam_management'))->name('viewer.exam_management');
//Route::get('/viewer/exam_management/view_exam', fn() => view('viewer.viewer_answer_view'))->name('viewer.view_exam');

// ==================================================================================================
// GOOGLE AUTH ROUTES
// ==================================================================================================
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// ==================================================================================================
// APPLICANT PROFILE LIST
// ==================================================================================================
Route::get('/admin/applicants-profile', [ShowApplicantsProfile::class, 'index'])->name('applicants_profile');
Route::get('/admin/reviewed-applicants', [ShowApplicantsProfile::class, 'reviewedIndex'])->name('reviewed_applicants');
Route::get('/admin/reviewed-applicants/sort', [ShowApplicantsProfile::class, 'ajaxSort'])->name('reviewed_applicants.sort');
Route::get('/admin/applications_list', [ShowApplicantsProfile::class, 'applicationsList'])->name('applications_list');
Route::get('/admin/reviewed/{vacancy_id}', [ShowApplicantsProfile::class, 'reviewedIndex'])->name('admin.reviewed');
Route::get('/admin/applicants/{vacancy_id}', [ShowApplicantsProfile::class, 'index'])->name('admin.applicants');
Route::get('/admin/applicants-profile/sort', [ShowApplicantsProfile::class, 'ajaxSortApplicants'])->name('admin.applicants.sort');
Route::get('/admin/all-applicants/{vacancy_id}', [ShowApplicantsProfile::class, 'allApplicants'])->name('applicants_profile.all');

// Manage Applicants Routes (New)
Route::get('/admin/manage_applicants/{vacancy_id}', [ShowApplicantsProfile::class, 'manageApplicants'])->name('admin.manage_applicants');
Route::get('/admin/manage_applicants/new', [ShowApplicantsProfile::class, 'ajaxFilterNewApplicants'])->name('admin.manage_applicants.new');
Route::get('/admin/manage_applicants/reviewed', [ShowApplicantsProfile::class, 'ajaxFilterReviewedApplicants'])->name('admin.manage_applicants.reviewed');
// ==================================================================================================
// APPLICATION ROUTE
// CHAT-BOT ROUTES
// ==================================================================================================
Route::post('/apply/{vacancy_id}', [JobVacancyController::class, 'apply'])
    ->middleware('auth')
    ->name('application.store');



// ADMIN ROLE
/*
Route::middleware(['web', 'auth:admin', 'admin.role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('dashboard_admin');
});

Route::middleware(['web', 'auth:admin', 'admin.role:viewer'])->group(function () {
    Route::get('/viewer', fn() => view('viewer.viewer_dashboard'))->name('viewer');
});
*/

//Chat-Bot
Route::post('/chat', [GeminiChatController::class, 'chat']);



// ==================================================================================================
// TEST ROUTES
// ==================================================================================================
Route::get('/test-event', function () {
    broadcast(new PackageSent('test data', 'test'));
    return 'Event broadcasted';
});
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard_admin');
});
Route::get('/admin/reviewed_applicants', [AdminController::class, 'reviewedApplicants'])->name('reviewed_applicants');

Route::get('/dashboard/admin', [AdminController::class, 'dashboard'])->name('dashboard_admin');

//Route::get('/dashboard-progress', [JobVacancyController::class, 'pdsAndWesProgress'])->name('dashboard.progress');
//Route::get('/dashboard', [JobVacancyController::class, 'pdsAndWesProgress'])->name('dashboard_user');
//Route::get('/dashboard', [JobVacancyController::class, 'pdsAndWesProgress'])->name('dashboarduser.dashboard_user');


//Chat-Bot
Route::post('/chat', [GeminiChatController::class, 'chat']);

//error mobile
Route::get('/mobile-locked', function () {
    return response()->view('errors.mobile');
})->name('mobile.locked');


// LIVE SERVER ROUTES
Route::get('storage/{filename}', function ($filename) {
    $path = storage_path('app/public/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    $file = file_get_contents($path);
    $type = mime_content_type($path);

    return response($file, 200)->header("Content-Type", $type);
})->where('filename', '.*');
