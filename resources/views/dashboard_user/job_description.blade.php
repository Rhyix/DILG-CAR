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
                'fresh_upload' => 1,
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

        <!-- Confirm Application Modal -->
        <div id="confirmApplyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold text-[#002C76] mb-3">Submit Application</h2>
                <p class="text-sm text-gray-700 mb-6">
                    Your required documents are ready. Do you want to submit your application now?
                </p>

                <form id="applyForm" action="{{ route('application.store', $vacancy->vacancy_id) }}" method="POST">
                    @csrf
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeConfirmApplyModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                        <button type="submit" class="use-loader px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Complete PDS First Modal -->
        <div id="pdsRequiredModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
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
        <div id="requiredDocsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
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
                    Each job application requires a fresh document submission.
                </p>

                <div class="max-h-72 overflow-y-auto border border-gray-200 rounded-xl p-4 mb-6 bg-slate-50">
                    <ul class="space-y-3">
                        @forelse($requiredDocsPreviewForModal as $doc)
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
                        Confirm and Go to Upload PDF
                    </button>
                </div>
                </div>
            </div>
        </div>

        <!-- Document Track Mismatch Modal -->
        <div id="docTrackMismatchModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
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
    function openApplyModal() {
        const hasIncompletePds = @json($hasIncompletePdsForApply);
        if (hasIncompletePds) {
            document.getElementById('pdsRequiredModal').classList.remove('hidden');
            return;
        }

        const hasMissingRequiredDocs = @json($hasMissingRequiredDocsForModal);
        if (hasMissingRequiredDocs) {
            document.getElementById('requiredDocsModal').classList.remove('hidden');
            return;
        }

        const hasDocTrackMismatch = @json($hasDocTrackMismatch);
        if (hasDocTrackMismatch) {
            document.getElementById('docTrackMismatchModal').classList.remove('hidden');
            return;
        }

        document.getElementById('confirmApplyModal').classList.remove('hidden');
    }

    function closeConfirmApplyModal() {
        document.getElementById('confirmApplyModal').classList.add('hidden');
    }

    function closePdsRequiredModal() {
        document.getElementById('pdsRequiredModal').classList.add('hidden');
    }

    function closeRequiredDocsModal() {
        document.getElementById('requiredDocsModal').classList.add('hidden');
    }

    function closeDocTrackMismatchModal() {
        document.getElementById('docTrackMismatchModal').classList.add('hidden');
    }

    // Re-initialize Feather Icons
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        const showPdsRequiredModalOnLoad = @json($showPdsRequiredModalOnLoad);
        if (showPdsRequiredModalOnLoad) {
            document.getElementById('pdsRequiredModal').classList.remove('hidden');
        }

        const showRequiredDocsModalOnLoad = @json($showRequiredDocsModalOnLoad);
        if (showRequiredDocsModalOnLoad) {
            document.getElementById('requiredDocsModal').classList.remove('hidden');
        }

        const showMismatchModalOnLoad = @json($showMismatchModalOnLoad);
        if (showMismatchModalOnLoad) {
            document.getElementById('docTrackMismatchModal').classList.remove('hidden');
        }
    });
</script>
