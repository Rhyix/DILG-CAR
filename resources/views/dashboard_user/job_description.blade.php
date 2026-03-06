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
        /* Keep a single scroll container (page shell) on this view */
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

        $isClosed = strtolower((string) $vacancy->status) === 'closed';
        $status = $isClosed ? 'CLOSED' : 'OPEN';
        $typeIsPlantilla = strcasecmp(trim((string) $vacancy->vacancy_type), 'plantilla') === 0;
        $typeIsCos = strcasecmp(trim((string) $vacancy->vacancy_type), 'cos') === 0;
        $typeLabel = $typeIsCos
            ? 'Contract of Service Position'
            : ($typeIsPlantilla ? 'Plantilla Position' : (string) $vacancy->vacancy_type);
    @endphp

    <main class="flex-1 min-w-0 space-y-5 font-montserrat mr-4">
        <section class="flex items-center max-w-full">
            <h1 class="w-full border-b border-[#0D2B70] py-2 text-2xl sm:text-3xl text-[#0D2B70] tracking-wide">
                Job Description
            </h1>
        </section>

        <section class="rounded-2xl border border-[#0D2B70]/20 bg-white/70 p-4 sm:p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#0D2B70]/30 pb-3">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#0D2B70] break-words">{{ $vacancy->position_title }}</h2>
                <span class="inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold {{ $isClosed ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-emerald-100 text-emerald-700 border border-emerald-300' }}">
                    {{ $isClosed ? 'Closed' : 'Open' }}
                </span>
            </div>

            <div class="mt-4 grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-4">
                <aside class="rounded-xl border border-[#0D2B70]/25 bg-white p-3">
                    <h3 class="text-xl font-bold text-[#0D2B70] border-b border-[#0D2B70]/40 pb-1">{{ $typeLabel }}</h3>
                    <div class="mt-2 space-y-1.5 text-sm text-[#0D2B70]">
                        <p><span class="font-bold">Compensation:</span> {{ $vacancy->monthly_salary }}</p>
                        <p><span class="font-bold">Date Posted:</span> {{ $vacancy->created_at }}</p>
                        <p><span class="font-bold">Deadline:</span> {{ \Carbon\Carbon::parse($vacancy->closing_date)->subMinute()->format('n/j/Y g:i A') }}</p>
                        <p><span class="font-bold">Place of Assignment:</span> {{ $vacancy->place_of_assignment }}</p>

                        @if($typeIsPlantilla)
                            <p><span class="font-bold">Salary Grade:</span> {{ $vacancy->salary_grade }}</p>
                            <p><span class="font-bold">Plantilla Item Number:</span> {{ $vacancy->plantilla_item_no }}</p>
                        @endif
                    </div>

                    @if($typeIsPlantilla)
                        <div class="mt-3">
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

                    <!-- Application Buttons -->
                    <div class="mt-3 flex flex-col gap-2 justify-center">
                        @if ($hasApplied)
                            <button disabled
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-gray-400 text-white text-sm font-semibold cursor-not-allowed">
                                <i data-feather="check-circle" class="w-4 h-4"></i> ALREADY APPLIED
                            </button>
                        @elseif (!$isClosed)
                            <button type="button" onclick="openApplyModal()"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
                                <i data-feather="arrow-right" class="w-4 h-4"></i> APPLY
                            </button>
                        @else
                            <button disabled
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-gray-400 text-white text-sm font-semibold cursor-not-allowed">
                                <i data-feather="x-circle" class="w-4 h-4"></i> APPLICATION CLOSED
                            </button>
                        @endif

                        @if($hasIncompletePdsForApply)
                            <button onclick="window.location.href='{{ route('display_c1') }}'"
                                class="use-loader inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                                <i data-feather="file-text" class="w-4 h-4"></i> COMPLETE PDS
                            </button>
                        @endif
                    </div>
                </aside>

                <section class="rounded-xl border border-[#0D2B70]/25 bg-white p-4 text-sm text-[#0D2B70] space-y-5">
                    <div>
                        <h3 class="text-3xl font-semibold text-[#0D2B70] mb-2">Qualification Standards</h3>
                        <ul class="space-y-2">
                            <li>
                                <p class="font-bold">Education</p>
                                <p class="text-slate-700">{{ $vacancy->qualification_education ?: 'Not specified' }}</p>
                            </li>
                            <li>
                                <p class="font-bold">Experience</p>
                                <p class="text-slate-700">{{ $vacancy->qualification_experience ?: 'Not specified' }}</p>
                            </li>
                            <li>
                                <p class="font-bold">Training</p>
                                <p class="text-slate-700">{{ $vacancy->qualification_training ?: 'Not specified' }}</p>
                            </li>
                            <li>
                                <p class="font-bold">Eligibility</p>
                                <p class="text-slate-700">{{ $vacancy->qualification_eligibility ?: 'Not specified' }}</p>
                            </li>
                            @if($typeIsPlantilla && !empty($vacancy->competencies))
                                <li>
                                    <p class="font-bold">Competencies</p>
                                    <p class="text-slate-700">{!! nl2br(e($vacancy->competencies)) !!}</p>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <hr>


                    @if($typeIsCos)
                        <div>
                            <h3 class="text-3xl font-semibold text-[#0D2B70] mb-2">COS Details</h3>
                            <ul class="space-y-2">
                                <li>
                                    <p class="font-bold">Scope of Work</p>
                                    <p class="text-slate-700">{!! nl2br(e($vacancy->scope_of_work)) !!}</p>
                                </li>
                                <li>
                                    <p class="font-bold">Expected Output</p>
                                    <p class="text-slate-700">{!! nl2br(e($vacancy->expected_output)) !!}</p>
                                </li>
                                <li>
                                    <p class="font-bold">Duration of Work</p>
                                    <p class="text-slate-700">{!! nl2br(e($vacancy->duration_of_work)) !!}</p>
                                </li>
                            </ul>
                        </div>

                        <hr>
                    @endif

                    <div>
                        <h3 class="text-3xl font-semibold text-[#0D2B70] mb-2">Application</h3>
                        <ul class="space-y-2">
                            <li>
                                <p class="font-bold">How to Apply</p>
                                <p class="text-slate-700">
                                    Qualified applicants are advised to apply online through
                                    <a target="_blank" href="https://car.dilg.gov.ph/dilg-car-vacancy/" class="text-[#0D2B70] font-bold underline">this portal</a>.
                                </p>
                            </li>
                            <li>
                                <p class="font-bold">Address To</p>
                                <p class="text-slate-700">
                                    {{ $vacancy->to_person }}, {{ $vacancy->to_position }}, {{ $vacancy->to_office }}, {{ $vacancy->to_office_address }}
                                </p>
                            </li>
                            <li>
                                <p class="font-bold">Notice</p>
                                <p class="text-red-600 font-semibold">APPLICATIONS WITH INCOMPLETE DOCUMENTS SHALL NOT BE ENTERTAINED.</p>
                            </li>
                        </ul>
                    </div>
                </section>
            </div>
        </section>

        <!-- Complete PDS First Modal -->
        <div id="pdsRequiredModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/55 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold text-[#002C76] mb-4">Complete Personal Data Sheet First</h2>
                <p class="text-sm text-gray-700 mb-6">
                    You need to complete your Personal Data Sheet from Personal Information up to Work Experience Sheet before applying for a job.
                </p>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closePdsRequiredModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                        Close
                    </button>
                    <button type="button" onclick="window.location.href='{{ route('display_c1') }}'" class="use-loader px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Go to PDS
                    </button>
                </div>
            </div>
        </div>

        <!-- Required Documents Preview Modal -->
        <div id="requiredDocsModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/60 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-0 overflow-hidden">
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

        <!-- Document Track Mismatch Modal -->
        <div id="docTrackMismatchModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/60 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold text-[#002C76] mb-4">Required Documents Mismatch</h2>
                <p class="text-sm text-gray-700 mb-6">
                    @if($submittedTrackForModal)
                        You submitted <span class="font-semibold">{{ $submittedTrackForModal }}</span> documents,
                        but this vacancy is <span class="font-semibold">{{ $vacancyTrackForModal }}</span>.
                    @else
                        This vacancy requires <span class="font-semibold">{{ $vacancyTrackForModal }}</span> documents.
                    @endif
                    Please upload the required {{ $vacancyTrackForModal }} documents first.
                </p>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeDocTrackMismatchModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                        Close
                    </button>
                    <button type="button" onclick="window.location.href='{{ $docUploadRedirectUrlForModal }}'" class="use-loader px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
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
