@extends('layout.pds_layout')
@section('title','Upload PDF')
@section('content')
@php
    $documentMeta = [
        'application_letter' => ['label' => 'Application Letter', 'accept' => 'application/pdf'],
        'pqe_result' => ['label' => 'Pre-Qualifying Exam (PQE) Result', 'accept' => 'application/pdf'],
        'transcript_records' => ['label' => 'Transcript of Records (Baccalaureate Degree)', 'accept' => 'application/pdf'],
        'photocopy_diploma' => ['label' => 'Diploma', 'accept' => 'application/pdf'],
        'signed_pds' => ['label' => 'Signed Personal Data Sheet', 'accept' => 'application/pdf'],
        'signed_work_exp_sheet' => ['label' => 'Signed Work Experience Sheet', 'accept' => 'application/pdf'],
        'cert_lgoo_induction' => ['label' => 'Certificate of Completion of LGOO Induction Training', 'accept' => 'application/pdf'],
        'passport_photo' => ['label' => '2" x 2" or Passport Size Picture', 'accept' => 'application/pdf,image/*'],
        'cert_eligibility' => ['label' => 'Certificate of Eligibility/Board Rating', 'accept' => 'application/pdf'],
        'ipcr' => ['label' => 'Certification of Numerical Rating/Performance Rating/IPCR', 'accept' => 'application/pdf'],
        'non_academic' => ['label' => 'Non-Academic Awards Received', 'accept' => 'application/pdf'],
        'cert_training' => ['label' => 'Certificates of Training/Participation Relevant to the Position', 'accept' => 'application/pdf'],
        'designation_order' => ['label' => 'Confirmed Designation Order/s', 'accept' => 'application/pdf'],
        'grade_masteraldoctorate' => ['label' => 'Certificate of Grades with Masteral/Doctorate Units Earned', 'accept' => 'application/pdf'],
        'tor_masteraldoctorate' => ['label' => 'TOR with Masteral/Doctorate Degree', 'accept' => 'application/pdf'],
        'cert_employment' => ['label' => 'Certificate of Employment', 'accept' => 'application/pdf'],
        'other_documents' => ['label' => 'Other Documents Submitted', 'accept' => 'application/pdf'],
    ];

    $requiredDocsByTrack = $requiredDocsByTrack ?? ['COS' => [], 'Plantilla' => []];
    $activeTrack = old('doc_track', $defaultDocTrack ?? 'Plantilla');
    if (!in_array($activeTrack, ['COS', 'Plantilla'], true)) {
        $activeTrack = 'Plantilla';
    }
@endphp
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if ($errors->any())
            <div class="mb-6 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow text-sm font-semibold">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="myForm" method="POST" action="/pds/finalize/display_final_pds" enctype="multipart/form-data" data-upload-retry="1">
            @csrf
            <input type="hidden" name="doc_track" id="doc-track-input" value="{{ $activeTrack }}">

            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Supporting Documents</h2>
                </div>
                <p class="text-base font-semibold text-gray-900 mb-6">
                    Reminder: If you need to upload multiple files for a single document, please combine them into one file.
                </p>

                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex gap-6">
                        <button
                            id="tab-cos"
                            type="button"
                            onclick="switchDocTrack('COS')"
                            class="tab-button pb-2 font-bold text-sm uppercase tracking-wide transition-all duration-200"
                        >
                            COS
                        </button>
                        <button
                            id="tab-plantilla"
                            type="button"
                            onclick="switchDocTrack('Plantilla')"
                            class="tab-button pb-2 font-bold text-sm uppercase tracking-wide transition-all duration-200"
                        >
                            Plantilla
                        </button>
                    </nav>
                </div>

                <p id="doc-track-hint" class="mb-6 text-sm text-slate-600"></p>

                <div id="documents-container">
                @foreach ($documentMeta as $docType => $meta)
                    @php
                        $doc = $documents[$docType] ?? null;
                        $status = trim((string) ($doc->status ?? ''));
                        $isApproved = strcasecmp($status, 'Okay/Confirmed') === 0;
                        $hasExisting = !empty($doc?->storage_path)
                            || ($docType === 'application_letter' && !empty($hasExistingApplicationLetter));
                        $requiredCos = in_array($docType, $requiredDocsByTrack['COS'] ?? [], true);
                        $requiredPlantilla = in_array($docType, $requiredDocsByTrack['Plantilla'] ?? [], true);
                        $requiredNow = $activeTrack === 'COS' ? $requiredCos : $requiredPlantilla;
                        $inputId = 'cert-upload-' . str_replace('_', '-', $docType);
                    @endphp
                    <div
                        class="doc-row w-full mb-6 border-b border-dashed border-gray-300 pb-4"
                        data-required-cos="{{ $requiredCos ? 1 : 0 }}"
                        data-required-plantilla="{{ $requiredPlantilla ? 1 : 0 }}"
                        data-order="{{ $loop->index }}"
                    >
                        <div class="flex items-center justify-between w-full gap-4">
                            <h3 class="text-gray-700 font-medium">
                                {{ $meta['label'] }}
                                <span
                                    class="doc-required-badge text-sm font-semibold {{ $requiredNow ? 'text-red-600' : 'text-blue-500' }}"
                                    data-required-cos="{{ $requiredCos ? 1 : 0 }}"
                                    data-required-plantilla="{{ $requiredPlantilla ? 1 : 0 }}"
                                >
                                    {{ $requiredNow ? '(required)' : '(optional)' }}
                                </span>
                            </h3>

                            @if ($isApproved)
                                <div class="text-green-600 text-sm font-semibold">
                                    This document is already approved.
                                </div>
                            @else
                                <label
                                    for="{{ $inputId }}"
                                    class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer"
                                >
                                    <span class="material-icons text-5xl {{ $hasExisting ? 'text-green-500' : 'text-blue-400' }}">
                                        cloud_upload
                                    </span>
                                </label>
                                <input
                                    type="file"
                                    id="{{ $inputId }}"
                                    name="cert_uploads[{{ $docType }}]"
                                    accept="{{ $meta['accept'] }}"
                                    class="doc-upload-input absolute opacity-0 w-px h-px"
                                    data-has-existing="{{ $hasExisting ? 1 : 0 }}"
                                    data-required-cos="{{ $requiredCos ? 1 : 0 }}"
                                    data-required-plantilla="{{ $requiredPlantilla ? 1 : 0 }}"
                                    {{ ($requiredNow && !$hasExisting) ? 'required' : '' }}
                                >
                            @endif
                        </div>
                    </div>
                @endforeach
                </div>
            </section>

            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in mt-8">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">verified_user</span>
                    <h2 class="text-2xl font-bold text-gray-900">Declaration</h2>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6">
                    <div class="flex">
                        <div class="flex-1">
                            <p class="text-sm text-yellow-800 leading-relaxed">
                                42. I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, correct and complete statement pursuant to the provisions of pertinent laws, rules and regulations of the Republic of the Philippines. I authorize the agency head/authorized representative to verify/validate the contents stated herein.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="flex items-start cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors">
                        <input type="checkbox" name="declaration" class="mt-1 mr-3" required>
                        <span class="text-gray-700">
                            I certify that all information provided in this form is true and correct to the best of my knowledge.
                        </span>
                    </label>

                    <label class="flex items-start cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors">
                        <input type="checkbox" name="consent" class="mt-1 mr-3" required>
                        <span class="text-gray-700">
                            I consent to the collection and processing of my personal data in accordance with the Data Privacy Act of 2012.
                        </span>
                    </label>

                    <label class="flex items-start cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors">
                        <input type="checkbox" name="confirmation" class="mt-1 mr-3" required>
                        <span class="text-gray-700">
                            I confirm that all uploaded documents are correct, complete, and accurately represent the required information.
                        </span>
                    </label>
                </div>
            </section>

            <div class="flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
                <button type="button" onclick="window.location.href='{{ route('display_wes') }}'" class="use-loader w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
                    <span class="material-icons mr-2">arrow_back</span>
                    Previous
                </button>
                <button id="save-work-exp" type="submit" class="w-full sm:w-auto px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors duration-200 flex items-center justify-center">
                    <span class="material-icons mr-2">check_circle</span>
                    Submit Application
                </button>
            </div>
        </form>

        <footer class="mt-12 text-center text-sm text-gray-600">
            <p class="mb-2">
                <strong>WARNING:</strong> Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filing of administrative/criminal case/s against the person concerned.
            </p>
            <p>CS Form No. 212 (Revised 2025)</p>
        </footer>
    </main>
@endsection

<script>
    function reorderDocumentRows(track) {
        const container = document.getElementById('documents-container');
        if (!container) return;

        const rows = Array.from(container.querySelectorAll('.doc-row'));
        rows.sort((a, b) => {
            const reqA = track === 'COS'
                ? a.dataset.requiredCos === '1'
                : a.dataset.requiredPlantilla === '1';
            const reqB = track === 'COS'
                ? b.dataset.requiredCos === '1'
                : b.dataset.requiredPlantilla === '1';

            if (reqA !== reqB) {
                return reqB - reqA; // required first
            }

            const orderA = Number(a.dataset.order || 0);
            const orderB = Number(b.dataset.order || 0);
            return orderA - orderB;
        });

        rows.forEach((row) => container.appendChild(row));
    }

    function switchDocTrack(track) {
        const normalized = track === 'COS' ? 'COS' : 'Plantilla';
        const hiddenInput = document.getElementById('doc-track-input');
        if (hiddenInput) hiddenInput.value = normalized;

        const cosBtn = document.getElementById('tab-cos');
        const plantillaBtn = document.getElementById('tab-plantilla');
        const activate = (btn) => {
            btn.classList.add('text-[#0D2B70]', 'border-b-2', 'border-[#0D2B70]');
            btn.classList.remove('text-gray-400', 'border-transparent');
        };
        const deactivate = (btn) => {
            btn.classList.remove('text-[#0D2B70]', 'border-b-2', 'border-[#0D2B70]');
            btn.classList.add('text-gray-400', 'border-b-2', 'border-transparent');
        };

        if (normalized === 'COS') {
            activate(cosBtn);
            deactivate(plantillaBtn);
        } else {
            activate(plantillaBtn);
            deactivate(cosBtn);
        }

        const hint = document.getElementById('doc-track-hint');
        if (hint) {
            hint.textContent = normalized === 'COS'
                ? 'COS requirements are active. Required documents are based on COS vacancy rules.'
                : 'Plantilla requirements are active. All documents are required except TOR with masteral/doctorate, Certificate of Grades with masteral/doctorate, and LGOO induction certificate.';
        }

        document.querySelectorAll('.doc-required-badge').forEach((badge) => {
            const required = normalized === 'COS'
                ? badge.dataset.requiredCos === '1'
                : badge.dataset.requiredPlantilla === '1';
            badge.textContent = required ? '(required)' : '(optional)';
            badge.classList.toggle('text-red-600', required);
            badge.classList.toggle('text-blue-500', !required);
        });

        document.querySelectorAll('.doc-upload-input').forEach((input) => {
            const required = normalized === 'COS'
                ? input.dataset.requiredCos === '1'
                : input.dataset.requiredPlantilla === '1';
            const hasExisting = input.dataset.hasExisting === '1';
            if (required && !hasExisting) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        });

        reorderDocumentRows(normalized);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const initialTrack = document.getElementById('doc-track-input')?.value || 'Plantilla';
        switchDocTrack(initialTrack);

        document.querySelectorAll('.doc-upload-input').forEach((input) => {
            input.addEventListener('change', function () {
                const label = input.previousElementSibling;
                if (!label) return;
                const icon = label.querySelector('.material-icons');
                if (!icon) return;

                if (input.files.length > 0) {
                    label.classList.add('bg-green-100', 'border-green-400');
                    icon.classList.remove('text-blue-400');
                    icon.classList.add('text-green-500');
                } else {
                    label.classList.remove('bg-green-100', 'border-green-400');
                    icon.classList.remove('text-green-500');
                    icon.classList.add('text-blue-400');
                }
            });
        });
    });

    function submit(location){
        const form = document.querySelector('#myForm');
        form.action = `/pds/finalize/${location}`;
        form.requestSubmit();
    }
</script>
