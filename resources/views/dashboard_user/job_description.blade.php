@if(session('success'))
    @include('partials.alerts_template', [
        'showTrigger' => false,
        'title' => 'Success',
        'message' => session('success'),
        'okText' => 'OK',
        'showCancel' => false
    ])
@endif

@if(session('error'))
    @include('partials.alerts_template', [
        'showTrigger' => false,
        'title' => 'Not allowed',
        'message' => session('error'),
        'okText' => 'OK',
        'showCancel' => false
    ])
@endif

@extends('layout.app')
@section('title', 'DILG - Job Description')

@push('styles')
    <style>
        body {
            overflow: hidden;
        }
    </style>
@endpush

@section('content')
    @php
        $requiredDocsPayload = session('required_docs_prompt');
        $showRequiredDocsModalOnLoad = is_array($requiredDocsPayload)
            && (($requiredDocsPayload['vacancy_id'] ?? $vacancy->vacancy_id) === $vacancy->vacancy_id);
        $showPdsRequiredModalOnLoad = (bool) session('pds_required_prompt', false);

        $mismatchPayload = session('doc_track_mismatch');
        $showMismatchModalOnLoad = is_array($mismatchPayload)
            && (($mismatchPayload['vacancy_id'] ?? $vacancy->vacancy_id) === $vacancy->vacancy_id);
        $hasDocTrackMismatch = (bool) ($docTrackMismatch ?? false);
        $submittedTrackForModal = $mismatchSubmittedTrack ?? ($mismatchPayload['submitted_track'] ?? null);
        $vacancyTrackForModal = $vacancyTrack ?? ($mismatchPayload['vacancy_track'] ?? 'Plantilla');
        $docUploadRedirectUrlForModal = $docUploadRedirectUrl
            ?? route('display_c5', [
                'doc_track' => $vacancyTrackForModal,
                'vacancy_id' => $vacancy->vacancy_id,
            ]);
        $requiredDocsTrackForModal = $vacancyTrackForModal;
        $requiredDocsRedirectUrlForModal = $docUploadRedirectUrlForModal;
        $requiredDocsPreviewForModal = $requiredDocsPreview ?? [];
        $hasMissingRequiredDocsForModal = (bool) ($hasMissingRequiredDocs ?? false);
        $hasIncompletePdsForApply = !($hasCompletedPdsForApply ?? false);

        $statusRaw = strtolower(trim((string) $vacancy->status));
        $isClosed = in_array($statusRaw, ['closed', 'no', '0', 'inactive'], true);
        $status = $isClosed ? 'Closed' : 'Open';
        $typeIsPlantilla = strcasecmp(trim((string) $vacancy->vacancy_type), 'plantilla') === 0;
        $typeIsCos = strcasecmp(trim((string) $vacancy->vacancy_type), 'cos') === 0;
        $typeLabel = $typeIsCos
            ? 'Contract of Service Position'
            : ($typeIsPlantilla ? 'Plantilla Position' : (string) $vacancy->vacancy_type);

        $datePostedDisplay = optional($vacancy->created_at)->format('M d, Y') ?? 'Not specified';
        $deadlineDisplay = \Carbon\Carbon::parse($vacancy->closing_date)->subMinute()->format('M d, Y g:i A');
        $salaryValue = $vacancy->monthly_salary;
        $monthlySalaryDisplay = is_numeric($salaryValue)
            ? 'PHP ' . number_format((float) $salaryValue, 2)
            : ((string) ($salaryValue ?: 'Not specified'));
        $qualificationChecksForPanel = is_array($qualificationChecks ?? null) ? $qualificationChecks : [];
        $qualificationLabelMap = [
            'education' => 'Education',
            'training' => 'Training',
            'experience' => 'Experience',
            'eligibility' => 'Eligibility',
        ];
    @endphp

    <main class="flex-1 min-w-0 font-montserrat mr-4 space-y-5 pb-6">
        <section class="relative overflow-hidden rounded-3xl border border-[#0D2B70]/15 bg-gradient-to-br from-white via-[#F4F8FF] to-[#E8F0FF] p-5 sm:p-8 shadow-sm">
            <div class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-[#0D2B70]/10 blur-2xl"></div>
            <div class="absolute -left-12 -bottom-12 h-36 w-36 rounded-full bg-[#1D4ED8]/10 blur-2xl"></div>

            <div class="relative flex flex-col gap-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="space-y-2">
                        <p class="text-xs uppercase tracking-[0.2em] text-[#0D2B70]/70 font-semibold">Career Opportunity</p>
                        <h1 class="text-2xl sm:text-4xl font-extrabold text-[#0D2B70] leading-tight break-words">
                            {{ $vacancy->position_title }}
                        </h1>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1 rounded-full border border-[#0D2B70]/20 bg-white px-3 py-1 text-xs font-semibold text-[#0D2B70]">
                            <i data-feather="briefcase" class="h-3.5 w-3.5"></i>
                            {{ $typeLabel }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $isClosed ? 'bg-red-100 text-red-700 border-red-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200' }}">
                            {{ $status }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs sm:text-sm">
                    <div class="rounded-xl border border-[#0D2B70]/15 bg-white/90 px-3 py-2">
                        <p class="text-[#0D2B70]/70">Date Posted</p>
                        <p class="font-bold text-[#0D2B70]">{{ $datePostedDisplay }}</p>
                    </div>
                    <div class="rounded-xl border border-[#0D2B70]/15 bg-white/90 px-3 py-2">
                        <p class="text-[#0D2B70]/70">Deadline</p>
                        <p class="font-bold text-[#0D2B70]">{{ $deadlineDisplay }}</p>
                    </div>
                    <div class="rounded-xl border border-[#0D2B70]/15 bg-white/90 px-3 py-2">
                        <p class="text-[#0D2B70]/70">Place of Assignment</p>
                        <p class="font-bold text-[#0D2B70]">{{ $vacancy->place_of_assignment ?: 'Not specified' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.7fr)_360px] gap-5 items-start">
            <div class="space-y-5">
                <article class="rounded-2xl border border-[#0D2B70]/15 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="h-8 w-8 rounded-lg bg-[#0D2B70]/10 text-[#0D2B70] flex items-center justify-center">
                            <i data-feather="award" class="h-4 w-4"></i>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-[#0D2B70]">Qualification Standards</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Education</p>
                            <p class="text-sm text-slate-700 mt-1">{{ $vacancy->qualification_education ?: 'Not specified' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Experience</p>
                            <p class="text-sm text-slate-700 mt-1">{{ $vacancy->qualification_experience ?: 'Not specified' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Training</p>
                            <p class="text-sm text-slate-700 mt-1">{{ $vacancy->qualification_training ?: 'Not specified' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Eligibility</p>
                            <p class="text-sm text-slate-700 mt-1 leading-6">{!! nl2br(e($qualificationEligibilityDisplay ?? ($vacancy->qualification_eligibility ?: 'Not specified'))) !!}</p>
                        </div>
                    </div>

                    @if($typeIsPlantilla && !empty($vacancy->competencies))
                        <div class="mt-3 rounded-xl border border-slate-200 p-3 bg-white">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Competencies</p>
                            <p class="text-sm text-slate-700 mt-1 leading-6">{!! nl2br(e($vacancy->competencies)) !!}</p>
                        </div>
                    @endif
                </article>

                @if($typeIsCos)
                    <article class="rounded-2xl border border-[#0D2B70]/15 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="h-8 w-8 rounded-lg bg-[#0D2B70]/10 text-[#0D2B70] flex items-center justify-center">
                                <i data-feather="layers" class="h-4 w-4"></i>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold text-[#0D2B70]">COS Engagement Details</h2>
                        </div>

                        <div class="space-y-3">
                            <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Scope of Work</p>
                                <p class="text-sm text-slate-700 mt-1 leading-6">{!! nl2br(e($vacancy->scope_of_work)) !!}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Expected Output</p>
                                <p class="text-sm text-slate-700 mt-1 leading-6">{!! nl2br(e($vacancy->expected_output)) !!}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Duration of Work</p>
                                <p class="text-sm text-slate-700 mt-1 leading-6">{!! nl2br(e($vacancy->duration_of_work)) !!}</p>
                            </div>
                        </div>
                    </article>
                @endif

                <article class="rounded-2xl border border-[#0D2B70]/15 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="h-8 w-8 rounded-lg bg-[#0D2B70]/10 text-[#0D2B70] flex items-center justify-center">
                            <i data-feather="send" class="h-4 w-4"></i>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-[#0D2B70]">Application Instructions</h2>
                    </div>

                    <div class="space-y-3 text-sm text-slate-700">
                        <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                            <p class="font-semibold text-[#0D2B70]">How to Apply</p>
                            <p class="mt-1">
                                Qualified applicants are advised to apply online through
                                <a target="_blank" href="https://car.dilg.gov.ph/dilg-car-vacancy/" class="text-[#0D2B70] font-bold underline">this portal</a>.
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                            <p class="font-semibold text-[#0D2B70]">Address To</p>
                            <p class="mt-1">
                                {{ $vacancy->to_person }}, {{ $vacancy->to_position }}, {{ $vacancy->to_office }}, {{ $vacancy->to_office_address }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                            <p class="font-semibold text-red-700">Notice</p>
                            <p class="mt-1 text-red-700 font-medium">APPLICATIONS WITH INCOMPLETE DOCUMENTS SHALL NOT BE ENTERTAINED.</p>
                        </div>
                    </div>
                </article>
            </div>

            <aside class="space-y-5 xl:sticky xl:top-6">
                <div class="rounded-2xl border border-[#0D2B70]/20 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-bold text-[#0D2B70]">Application Panel</h3>
                    <p class="text-xs text-slate-500 mt-1">Check readiness, then submit your application.</p>

                    <div class="mt-4 rounded-xl border border-[#0D2B70]/15 bg-[#F8FAFF] p-3">
                        <p class="text-xs text-slate-500">Monthly Compensation</p>
                        <p class="text-xl font-extrabold text-[#0D2B70]">{{ $monthlySalaryDisplay }}</p>
                    </div>

                    <div class="mt-4 flex flex-col gap-2 justify-center">
                        @if ($hasApplied)
                            <button disabled
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gray-400 text-white text-sm font-semibold cursor-not-allowed">
                                <i data-feather="check-circle" class="w-4 h-4"></i> ALREADY APPLIED
                            </button>
                        @elseif (!$isClosed && !($isEligibilityQualified ?? true))
                            <button disabled
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-red-500 text-white text-sm font-semibold cursor-not-allowed">
                                <i data-feather="x-circle" class="w-4 h-4"></i> NOT ELIGIBLE
                            </button>
                        @elseif (!$isClosed)
                            <button type="button" onclick="openApplyModal()"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
                                <i data-feather="arrow-right" class="w-4 h-4"></i> APPLY FOR THIS POSITION
                            </button>
                        @else
                            <button disabled
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gray-400 text-white text-sm font-semibold cursor-not-allowed">
                                <i data-feather="x-circle" class="w-4 h-4"></i> APPLICATION CLOSED
                            </button>
                        @endif

                        @if($hasIncompletePdsForApply)
                            <button onclick="window.location.href='{{ route('display_c1') }}'"
                                class="use-loader inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                                <i data-feather="file-text" class="w-4 h-4"></i> COMPLETE PDS
                            </button>
                        @endif

                        @if(!$isClosed && !$hasApplied && !($isEligibilityQualified ?? true))
                            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                                {{ $eligibilityMismatchMessage ?: 'You are missing one or more required qualifications for this position.' }}
                            </div>
                        @endif
                    </div>
                </div>

                @if(!$isClosed && !$hasApplied && !empty($qualificationChecksForPanel))
                    <div class="rounded-2xl border border-[#0D2B70]/20 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-bold text-[#0D2B70]">Qualification Check</h3>
                        <div class="mt-3 space-y-2">
                            @foreach($qualificationLabelMap as $field => $label)
                                @php
                                    $check = $qualificationChecksForPanel[$field] ?? null;
                                    $checkStatus = is_array($check) ? ($check['status'] ?? 'na') : 'na';
                                    $required = is_array($check) ? (bool) ($check['required'] ?? false) : false;
                                    $met = is_array($check) ? (bool) ($check['met'] ?? false) : true;
                                    $requirementText = trim((string) (($check['requirement'] ?? '') ?: ''));
                                @endphp
                                <div class="rounded-lg border border-slate-200 px-3 py-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-slate-700">{{ $label }}</p>
                                        @if(!$required || $checkStatus === 'na')
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600">
                                                Not Required
                                            </span>
                                        @elseif($met)
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                Met
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700">
                                                Missing
                                            </span>
                                        @endif
                                    </div>
                                    @if($requirementText !== '')
                                        <p class="mt-1 text-xs text-slate-500">Required: {{ $requirementText }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="rounded-2xl border border-[#0D2B70]/20 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-bold text-[#0D2B70]">Vacancy Snapshot</h3>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Vacancy Type</dt>
                            <dd class="font-semibold text-slate-700 text-right">{{ $vacancy->vacancy_type ?: 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Status</dt>
                            <dd class="font-semibold {{ $isClosed ? 'text-red-600' : 'text-emerald-600' }}">{{ $status }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Posted</dt>
                            <dd class="font-semibold text-slate-700 text-right">{{ $datePostedDisplay }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Deadline</dt>
                            <dd class="font-semibold text-slate-700 text-right">{{ $deadlineDisplay }}</dd>
                        </div>
                        @if($typeIsPlantilla)
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500">Salary Grade</dt>
                                <dd class="font-semibold text-slate-700 text-right">{{ $vacancy->salary_grade ?: 'N/A' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500">Plantilla Item No.</dt>
                                <dd class="font-semibold text-slate-700 text-right">{{ $vacancy->plantilla_item_no ?: 'N/A' }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if($typeIsPlantilla)
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            @if(!empty($vacancy->csc_form_path))
                                <a href="{{ Storage::url($vacancy->csc_form_path) }}" target="_blank"
                                    class="inline-flex items-center justify-center w-full px-3 py-2 text-xs font-semibold rounded-md border border-[#0D2B70] text-[#0D2B70] hover:bg-[#0D2B70] hover:text-white transition">
                                    View CSC Form Attachment
                                </a>
                            @else
                                <div class="text-xs text-slate-500 border border-slate-200 bg-slate-50 rounded-md p-2">
                                    No CSC form attachment uploaded.
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </aside>
        </section>

        <div id="pdsRequiredModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/55 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-[#0D2B70]/10">
                <h2 class="text-lg font-semibold text-[#002C76] mb-3">Complete Personal Data Sheet First</h2>
                <p class="text-sm text-gray-700 mb-6">
                    You need to complete your Personal Data Sheet from Personal Information up to Work Experience Sheet before applying for a job.
                </p>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closePdsRequiredModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Close
                    </button>
                    <button type="button" onclick="window.location.href='{{ route('display_c1') }}'" class="use-loader px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Go to PDS
                    </button>
                </div>
            </div>
        </div>

        <div id="requiredDocsModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/60 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-0 overflow-hidden border border-[#0D2B70]/10">
                <div class="bg-[#0D2B70] px-6 py-4">
                    <h2 class="text-lg font-semibold text-white">
                        Required Documents ({{ $requiredDocsTrackForModal }})
                    </h2>
                    <p class="text-sm text-blue-100 mt-1">
                        Upload these documents to continue your application.
                    </p>
                </div>

                <div class="p-6">
                    <p class="text-sm text-gray-700 mb-4">
                        Previously uploaded required documents are reused automatically for your next applications.
                        Upload only the required documents that are still missing for this vacancy.
                    </p>

                    <p class="text-sm text-red-700 mb-4 flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-700 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span>
                            <span class="font-medium">Note:</span>
                            Make sure all required documents are available before applying.
                            Upload any missing required document now to continue your application.
                        </span>
                    </p>

                    <p class="text-sm text-red-700 mb-4 flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-700 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span>
                            <span class="font-medium">Warning:</span>
                            Reliability in your dishonesty about the required documents may affect your application negatively.
                        </span>
                    </p>

                    <div class="max-h-72 overflow-y-auto border border-gray-200 rounded-xl p-4 mb-6 bg-slate-50">
                        <ul class="space-y-3">
                            @php
                                $filteredDocs = $requiredDocsPreviewForModal;
                                if (strtolower($vacancy->vacancy_type) === 'cos') {
                                    $filteredDocs = collect($requiredDocsPreviewForModal)->filter(function($doc) {
                                        $docLabel = is_array($doc) ? ($doc['label'] ?? '') : '';
                                        return !str_contains(strtolower($docLabel), 'certificate of employment')
                                            && !str_contains(strtolower($docLabel), 'certificate of training')
                                            && !str_contains(strtolower($docLabel), 'transcript of record');
                                    })->values()->toArray();
                                }
                            @endphp

                            @forelse($filteredDocs as $doc)
                                @php
                                    $docLabel = is_array($doc) ? ($doc['label'] ?? 'Document') : 'Document';
                                @endphp
                                <li class="flex items-start gap-3 rounded-lg bg-white border border-slate-200 p-3">
                                    <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#0D2B70] text-[11px] font-bold text-white">
                                        {{ $loop->iteration }}
                                    </span>
                                    <span class="text-sm text-slate-800 leading-5">{{ $docLabel }}</span>
                                </li>
                            @empty
                                <li class="text-sm text-gray-500">No required documents found.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeRequiredDocsModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="button" onclick="window.location.href='{{ $requiredDocsRedirectUrlForModal }}'" class="use-loader px-4 py-2 bg-[#0D2B70] text-white rounded-lg hover:bg-[#0A245D]">
                            Go to Upload Documents
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="docTrackMismatchModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/60 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-[#0D2B70]/10">
                <h2 class="text-lg font-semibold text-[#002C76] mb-3">Required Documents Mismatch</h2>
                <p class="text-sm text-gray-700 mb-6">
                    @if($submittedTrackForModal)
                        You submitted <span class="font-semibold">{{ $submittedTrackForModal }}</span>
                        documents, but this vacancy is <span class="font-semibold">{{ $vacancyTrackForModal }}</span>.
                    @else
                        This vacancy requires <span class="font-semibold">{{ $vacancyTrackForModal }}</span> documents.
                    @endif
                    Please upload the required {{ $vacancyTrackForModal }} documents first.
                </p>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeDocTrackMismatchModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Close
                    </button>
                    <button type="button" onclick="window.location.href='{{ $docUploadRedirectUrlForModal }}'" class="use-loader px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Go to Upload PDF ({{ $vacancyTrackForModal }})
                    </button>
                </div>
            </div>
        </div>

        @include('partials.loader')
    </main>
@endsection

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function openApplyModal() {
        const hasIncompletePds = @json($hasIncompletePdsForApply);
        if (hasIncompletePds) { openModal('pdsRequiredModal'); return; }

        const hasDocTrackMismatch = @json($hasDocTrackMismatch);
        if (hasDocTrackMismatch) { openModal('docTrackMismatchModal'); return; }

        openModal('requiredDocsModal');
    }

    function closePdsRequiredModal()       { closeModal('pdsRequiredModal'); }
    function closeRequiredDocsModal()      { closeModal('requiredDocsModal'); }
    function closeDocTrackMismatchModal()  { closeModal('docTrackMismatchModal'); }

    document.addEventListener('DOMContentLoaded', function() {
        const modalIds = ['pdsRequiredModal', 'requiredDocsModal', 'docTrackMismatchModal'];
        modalIds.forEach((id) => {
            const modal = document.getElementById(id);
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            if (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeModal(id);
                    }
                });
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                modalIds.forEach((id) => {
                    const modal = document.getElementById(id);
                    if (modal && !modal.classList.contains('hidden')) {
                        closeModal(id);
                    }
                });
            }
        });

        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        const showPdsRequiredModalOnLoad = @json($showPdsRequiredModalOnLoad);
        if (showPdsRequiredModalOnLoad) {
            openModal('pdsRequiredModal');
        }

        const showRequiredDocsModalOnLoad = @json($showRequiredDocsModalOnLoad);
        if (showRequiredDocsModalOnLoad) {
            openModal('requiredDocsModal');
        }

        const showMismatchModalOnLoad = @json($showMismatchModalOnLoad);
        if (showMismatchModalOnLoad) {
            openModal('docTrackMismatchModal');
        }

        window.submitApplication = openApplyModal;
        window.confirmApply = openApplyModal;
        window.openApplyConfirmationModal = openApplyModal;
        window.closeApplyConfirmationModal = function () {};
        window.addEventListener('confirm-apply-modal', function () {
            openApplyModal();
        });
    });
</script>
