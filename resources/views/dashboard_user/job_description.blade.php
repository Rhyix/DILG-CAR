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
    @endphp
    <main class="flex-1 min-w-0 space-y-8 font-montserrat">

        <!-- Header Section -->
        <section class="flex-none flex items-center space-x-4 max-w-full">
            <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
                <span class="whitespace-nowrap text-[#0D2B70]">Job Descriptions</span>
            </h1>
        </section>

        @php
            $isClosed = strtolower($vacancy->status) === 'closed';
            $statusColor = $isClosed ? 'bg-red-600' : 'bg-green-600';
            $status = $isClosed ? 'CLOSED' : 'OPEN';
            $borderColor = $isClosed ? 'border-red-600' : 'border-green-600';
        @endphp

        <!-- Main Job Info Card -->
        <section class="bg-white p-4 md:p-6 rounded-lg shadow border-l-4 {{ $borderColor }}">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start md:space-x-6">
                <!-- Left: Title and Details -->
                <div class="space-y-2 w-full md:w-2/3">
                    <h2 class="text-2xl md:text-4xl font-extrabold text-[#002C76] break-words">{{ $vacancy->position_title }}</h2>
                    <p class="text-gray-600 text-base md:text-lg">
                        @if ($vacancy->vacancy_type === 'COS')
                            CONTRACT OF SERVICE
                        @elseif ($vacancy->vacancy_type === 'Plantilla')
                            PLANTILLA ITEM
                        @else
                            {{ $vacancy->vacancy_type }}
                        @endif
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-1 text-sm mt-2">
                        <p><span class="font-semibold">Date Posted:</span> {{ $vacancy->created_at }}</p>
                        <p><span class="font-semibold">Deadline:</span> {{ \Carbon\Carbon::parse($vacancy->closing_date)->subMinute()->format('n/j/Y g:i A') }}</p>
                        <p><span class="font-semibold">Place of Assignment:</span> {{ $vacancy->place_of_assignment }}</p>

                        @if(strtolower($vacancy->vacancy_type) == 'plantilla')
                            <p><span class="font-semibold">Item No.:</span> {{ $vacancy->plantilla_item_no }}</p>
                            <p><span class="font-semibold">Salary Grade:</span> {{ $vacancy->salary_grade }}</p>
                        @endif

                        <p><span class="font-semibold">Compensation:</span> {{ $vacancy->monthly_salary }}</p>
                    </div>
                </div>

                <!-- Right: Status and Buttons -->
                <div class="flex flex-col items-end gap-2 mt-4 md:mt-0 w-full md:w-auto md:ml-auto">
                    <span class="text-black font-bold text-sm flex items-center gap-1 uppercase">
                        {{ $status }}
                        <span class="w-3 h-3 {{ $statusColor }} rounded-full"></span>
                    </span>

                    @if ($hasApplied)
                        <!-- Already Applied Button -->
                        <button disabled
                            class="flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-gray-400 text-white text-sm font-semibold shadow cursor-not-allowed w-full md:w-auto">
                            <i data-feather="check-circle" class="w-4 h-4"></i> ALREADY APPLIED
                        </button>
                    @elseif (!$isClosed)
                    <!-- Apply Button -->
                        <button type="button"
                            onclick="openApplyModal()"
                            class="flex items-center justify-center gap-2 px-6 py-3 rounded-full text-sm font-semibold shadow transition w-full md:w-auto bg-green-600 hover:bg-green-700 text-white">
                            <i data-feather="arrow-right" class="w-4 h-4"></i> APPLY
                        </button>
                    @else
                    <!-- Application Closed Button -->
                        <button disabled
                            class="flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-gray-400 text-white text-sm font-semibold shadow cursor-not-allowed w-full md:w-auto">
                            <i data-feather="x-circle" class="w-4 h-4"></i> APPLICATION CLOSED
                        </button>
                    @endif

                    <!-- PDS Button -->
                    @if($hasIncompletePdsForApply)
                        <button onclick="window.location.href='{{ route('display_c1') }}'"
                            class="use-loader flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow transition w-full md:w-auto">
                            <i data-feather="arrow-right" class="w-4 h-4"></i> COMPLETE PDS
                        </button>
                    @endif

                    <!-- Work Experience Sheet Button - REMOVED -->
                </div>
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
                            // Filter documents for COS positions
                            $filteredDocs = $requiredDocsPreviewForModal;
                            if (strtolower($vacancy->vacancy_type) === 'cos') {
                                $filteredDocs = collect($requiredDocsPreviewForModal)->filter(function($doc) {
                                    $docLabel = is_array($doc) ? ($doc['label'] ?? '') : '';
                                    // Remove Certificate of Employment, Certificate of Training, and Transcript of Record
                                    return !str_contains(strtolower($docLabel), 'certificate of employment') && 
                                        !str_contains(strtolower($docLabel), 'certificate of training') && 
                                        !str_contains(strtolower($docLabel), 'transcript of record');
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

        <!-- QUALIFICATIONS TABLE -->
        <section class="bg-white p-6 rounded-lg shadow border-l-4 border-[#002C76]">
            <h3 class="text-xl font-bold text-[#002C76] mb-4 flex items-center gap-2">
                <i data-feather="clipboard-list" class="w-6 h-6"></i>
                QUALIFICATION STANDARDS
            </h3>
            
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <!-- Education -->
                    <tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors">
                        <td class="py-3 px-4 w-1/4 align-top font-bold text-[#002C76]">Education</td>
                        <td class="py-3 px-4 text-gray-700">{{ $vacancy->qualification_education ?: 'Not specified' }}</td>
                    </tr>
                    
                    <!-- Experience -->
                    <tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors">
                        <td class="py-3 px-4 w-1/4 align-top font-bold text-[#002C76]">Experience</td>
                        <td class="py-3 px-4 text-gray-700">{{ $vacancy->qualification_experience ?: 'Not specified' }}</td>
                    </tr>
                    
                    <!-- Training -->
                    <tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors">
                        <td class="py-3 px-4 w-1/4 align-top font-bold text-[#002C76]">Training</td>
                        <td class="py-3 px-4 text-gray-700">{{ $vacancy->qualification_training ?: 'Not specified' }}</td>
                    </tr>
                    
                    <!-- Eligibility -->
                    <tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors">
                        <td class="py-3 px-4 w-1/4 align-top font-bold text-[#002C76]">Eligibility</td>
                        <td class="py-3 px-4 text-gray-700">{{ $vacancy->qualification_eligibility ?: 'Not specified' }}</td>
                    </tr>
                    
                    <!-- Competencies (Plantilla only) -->
                    @if(strtolower($vacancy->vacancy_type) == 'plantilla' && !empty($vacancy->competencies))
                    <tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors">
                        <td class="py-3 px-4 w-1/4 align-top font-bold text-[#002C76]">Competencies</td>
                        <td class="py-3 px-4 text-gray-700">{!! nl2br(e($vacancy->competencies)) !!}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </section>

        <!-- CSC Form Attachment (Plantilla only) -->
        @if(strtolower($vacancy->vacancy_type) == 'plantilla' && !empty($vacancy->csc_form_path))
            <section class="bg-white p-6 rounded-lg shadow border-l-4 border-[#002C76]">
                <h3 class="text-xl font-bold text-[#002C76] mb-4 flex items-center gap-2">
                    <i data-feather="file" class="w-6 h-6"></i>
                    CSC FORM ATTACHMENT
                </h3>
                <div class="flex items-center gap-4 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#002C76]/10 rounded-lg flex items-center justify-center">
                        <i data-feather="file-text" class="w-5 h-5 text-[#002C76]"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ basename($vacancy->csc_form_path) }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">CSC Form for this position</p>
                    </div>
                    <a href="{{ Storage::url($vacancy->csc_form_path) }}" target="_blank"
                       class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-[#002C76] text-white text-sm font-medium rounded-lg hover:bg-[#001a4d] transition-colors">
                        <i data-feather="eye" class="w-4 h-4"></i>
                        View
                    </a>
                </div>
            </section>
        @endif

        <!-- COS Details -->
        @if(strtolower($vacancy->vacancy_type) == 'cos')
            <section class="bg-white p-6 rounded-lg shadow border-l-4 border-green-600">
                <h3 class="text-xl font-bold text-green-700 mb-4 flex items-center gap-2">
                    <i data-feather="file-text" class="w-6 h-6"></i>
                    COS DETAILS
                </h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <!-- Scope of Work -->
                        <tr class="border-b border-gray-200 hover:bg-green-50 transition-colors">
                            <td class="py-3 px-4 w-1/4 align-top font-bold text-green-700">Scope of Work</td>
                            <td class="py-3 px-4 text-gray-700">{!! nl2br(e($vacancy->scope_of_work)) !!}</td>
                        </tr>
                        
                        <!-- Expected Output -->
                        <tr class="border-b border-gray-200 hover:bg-green-50 transition-colors">
                            <td class="py-3 px-4 w-1/4 align-top font-bold text-green-700">Expected Output</td>
                            <td class="py-3 px-4 text-gray-700">{!! nl2br(e($vacancy->expected_output)) !!}</td>
                        </tr>
                        
                        <!-- Duration of Work -->
                        <tr class="border-b border-gray-200 hover:bg-green-50 transition-colors">
                            <td class="py-3 px-4 w-1/4 align-top font-bold text-green-700">Duration of Work</td>
                            <td class="py-3 px-4 text-gray-700">{!! nl2br(e($vacancy->duration_of_work)) !!}</td>
                        </tr>
                    </table>
                </div>
            </section>
        @endif

        <!-- Application Details -->
        <section class="bg-white p-6 rounded-lg shadow border-l-4 border-[#002C76]">
            <h3 class="text-xl font-bold text-[#002C76] mb-4 flex items-center gap-2">
                <i data-feather="send" class="w-6 h-6"></i>
                APPLICATION
            </h3>
            
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <!-- Application Instructions -->
                    <tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors">
                        <td class="py-3 px-4 w-1/4 align-top font-bold text-[#002C76]">How to Apply</td>
                        <td class="py-3 px-4 text-gray-700">
                            Qualified applicants are advised to apply online through 
                            <a href="https://car.dilg.gov.ph/dilg-car-vacancy/" class="text-blue-600 font-bold underline">this portal</a>.
                        </td>
                    </tr>
                    
                    <!-- Address To -->
                    <tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors">
                        <td class="py-3 px-4 w-1/4 align-top font-bold text-[#002C76]">Address To</td>
                        <td class="py-3 px-4 text-gray-700">
                            <p class="font-bold">{{ $vacancy->to_person }}</p>
                            <p>{{ $vacancy->to_position }}</p>
                            <p>{{ $vacancy->to_office }}</p>
                            <p>{{ $vacancy->to_office_address }}</p>
                        </td>
                    </tr>
                    
                    <!-- Important Notice -->
                    <tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors">
                        <td class="py-3 px-4 w-1/4 align-top font-bold text-[#002C76]">Notice</td>
                        <td class="py-3 px-4">
                            <p class="text-red-600 font-bold">APPLICATIONS WITH INCOMPLETE DOCUMENTS SHALL NOT BE ENTERTAINED.</p>
                        </td>
                    </tr>
                </table>
            </div>
        </section>

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

    // Re-initialize Feather Icons
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

        // Backward compatibility for legacy confirmation modal triggers.
        window.submitApplication = openApplyModal;
        window.confirmApply = openApplyModal;
        window.openApplyConfirmationModal = openApplyModal;
        window.closeApplyConfirmationModal = function () {};
        window.addEventListener('confirm-apply-modal', function () {
            openApplyModal();
        });
    });
</script>
