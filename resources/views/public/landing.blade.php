<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DILG-CAR Careers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .modal-transition {
            transition: opacity 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-blue-100 text-gray-900 min-h-screen flex flex-col">
    @php
        // Get the items on the current page
        $pageItems = $vacancies->items();
        
        // Count by vacancy type for current page (excluding closed vacancies)
        $vacancyTypeCounts = [];
        $allCount = 0;
        
        foreach ($pageItems as $vacancy) {
            $closingDate = \Carbon\Carbon::parse($vacancy->closing_date)->setTime(17, 0, 0);
            $now = \Carbon\Carbon::now();
            $isClosed = $now->greaterThan($closingDate);
            
            // Only count if not closed
            if (!$isClosed) {
                $type = strtolower(trim((string) ($vacancy->vacancy_type ?? '')));
                $vacancyTypeCounts[$type] = ($vacancyTypeCounts[$type] ?? 0) + 1;
                $allCount++;
            }
        }

        $plantillaCount = ($vacancyTypeCounts['permanent'] ?? 0) + ($vacancyTypeCounts['plantilla'] ?? 0);
        $cosCount = ($vacancyTypeCounts['cos'] ?? 0) + ($vacancyTypeCounts['contract of service'] ?? 0) + ($vacancyTypeCounts['contract'] ?? 0);
        $ojtCount = ($vacancyTypeCounts['ojt'] ?? 0) + ($vacancyTypeCounts['on-the-job training'] ?? 0);
        $contractualCount = $vacancyTypeCounts['contractual'] ?? 0;

        // Keep labels aligned with applicant document upload (resources/views/pds/c5.blade.php).
        $documentMetaForLanding = [
            'application_letter' => 'Application Letter',
            'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
            'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
            'photocopy_diploma' => 'Diploma',
            'signed_pds' => 'Signed and Subscribed Personal Data Sheet',
            'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
            'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
            'passport_photo' => '2" x 2" or Passport Size Picture',
            'cert_eligibility' => 'Certificate of Eligibility/Board Rating',
            'ipcr' => 'Certification of Numerical Rating/Performance Rating/IPCR (If Any)',
            'non_academic' => 'Non-Academic Awards Received (If Any)',
            'cert_training' => 'Certificates of Training/Participation Relevant to the Position',
            'designation_order' => 'Confirmed Designation Order/s (If Any)',
            'grade_masteraldoctorate' => 'Certificate of Grades with Masteral/Doctorate Units Earned',
            'tor_masteraldoctorate' => 'TOR with Masteral/Doctorate Degree',
            'cert_employment' => 'Certificate of Employment (If Any)',
            'other_documents' => 'Other Documents Submitted',
        ];

        $allDocumentTypesForLanding = array_keys($documentMetaForLanding);
        $requiredDocsByTrackForLanding = [
            'COS' => [
                'passport_photo',
                'signed_pds',
                'signed_work_exp_sheet',
                'photocopy_diploma',
                'application_letter',
                'cert_training',
            ],
            'Plantilla' => array_values(array_diff(
                $allDocumentTypesForLanding,
                [
                    'tor_masteraldoctorate',
                    'grade_masteraldoctorate',
                    'cert_lgoo_induction',
                    'other_documents',
                    'pqe_result',
                    'ipcr',
                    'non_academic',
                    'designation_order',
                    'cert_employment',
                ]
            )),
        ];
    @endphp
    <!-- Modal Overlay - Hidden by default -->
    <div id="documentsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden modal-transition overflow-y-auto py-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full mx-4 shadow-2xl transform transition-all flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="flex-shrink-0 flex items-center justify-between p-6 border-b border-gray-200 bg-white rounded-t-3xl">
                <div>
                    <h3 class="text-2xl font-bold text-[#0D2B70]" id="modalJobTitle">Job Position</h3>
                    <p class="text-gray-600 text-sm mt-1" id="modalVacancyType">Vacancy Type</p>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <!-- Modal Body - Scrollable -->
            <div class="flex-1 overflow-y-auto p-6 bg-red">
                <div class="mb-6">
                    <h4 class="font-bold text-[#0D2B70] mb-3 flex items-center gap-2">
                        <i data-feather="check-circle" class="w-5 h-5"></i>
                        Qualification Standards
                    </h4>
                    <div class="bg-gray-50 rounded-xl p-4 space-y-3 text-sm">
                        <div class="grid grid-cols-[120px_1fr] gap-2">
                            <span class="font-semibold text-gray-700">Education:</span>
                            <span class="text-gray-600" id="modalEducation"></span>
                        </div>
                        <div class="grid grid-cols-[120px_1fr] gap-2">
                            <span class="font-semibold text-gray-700">Training:</span>
                            <span class="text-gray-600" id="modalTraining"></span>
                        </div>
                        <div class="grid grid-cols-[120px_1fr] gap-2">
                            <span class="font-semibold text-gray-700">Experience:</span>
                            <span class="text-gray-600" id="modalExperience"></span>
                        </div>
                        <div class="grid grid-cols-[120px_1fr] gap-2">
                            <span class="font-semibold text-gray-700">Eligibility:</span>
                            <div class="text-gray-600 break-words" id="modalEligibility"></div>
                        </div>
                        <div class="grid grid-cols-[120px_1fr] gap-2" id="modalCompetencyContainer">
                            <span class="font-semibold text-gray-700">Competency:</span>
                            <span class="text-gray-600" id="modalCompetency"></span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h4 class="font-bold text-[#0D2B70] mb-3 flex items-center gap-2">
                        <i data-feather="file-text" class="w-5 h-5"></i>
                        Required Documents
                    </h4>
                    <div class="bg-blue-50 rounded-xl p-4">
                        <p id="requiredDocumentsHint" class="text-xs font-semibold text-gray-500 mb-3"></p>
                        <ul id="requiredDocumentsList" class="space-y-3">
                            <!-- Documents will be dynamically inserted here -->
                        </ul>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h4 class="font-bold text-[#0D2B70] mb-3 flex items-center gap-2">
                        <i data-feather="info" class="w-5 h-5"></i>
                        Additional Information
                    </h4>
                    <div class="text-sm text-gray-600 space-y-2" id="additionalInfo">
                        <p>• Ensure all documents are clear and legible</p>
                        <p>• Upload in PDF or image format (max 2MB per file)</p>
                        <p>• Incomplete requirements may delay application processing</p>
                    </div>
                </div>
                
                <!-- Login Prompt for Guest Users -->
                <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-sm text-yellow-800 flex items-center gap-2">
                        <i data-feather="info" class="w-4 h-4"></i>
                        You need to be logged in to apply for this position.
                    </p>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex-shrink-0 flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-white rounded-b-3xl">
                <button onclick="closeModal()" class="px-4 py-2 text-gray-600 font-semibold hover:bg-gray-100 rounded-lg transition-colors">
                    Close
                </button>
                <a href="{{ route('login.form') }}" id="applyNowBtn" class="inline-flex items-center gap-2 bg-[#0D2B70] text-white px-6 py-2 rounded-lg font-bold hover:bg-[#002C76] transition-colors">
                    <i data-feather="log-in" class="w-4 h-4"></i>
                    Login to Apply
                </a>
            </div>
        </div>
    </div>

    <header class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-[#0D2B70] via-[#17439e] to-[#002C76]"></div>
        <div class="absolute -top-40 -left-20 w-80 h-80 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute -bottom-40 right-0 w-96 h-96 rounded-full bg-white/5 blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto px-6 py-8">
            <!-- Top Navigation -->
            <nav class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-12 pb-6 border-b border-white/20">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG" class="h-16 w-16 rounded-full border-2 border-white/40 bg-white/10">
                    <div class="flex flex-col w-[800px] rounded-lg px-4 py-2">
                        <span class="text-white text-l font-bold ">REPUBLIC OF THE PHILIPPINES</span>
                        <hr>
                        <span class="text-white text-xl font-semibold ">DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT - CORDILLERA ADMINISTRATIVE REGION</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('login.form') }}" class="text-white/90 hover:text-white font-semibold text-sm transition-colors">Sign In</a>
                    <a href="{{ route('register.form') }}" class="inline-flex items-center gap-2 bg-white text-[#0D2B70] px-6 py-2.5 rounded-full font-bold shadow-lg hover:shadow-xl hover:bg-gray-50 transition-all">
                        <i data-feather="user-plus" class="w-4 h-4"></i>
                        Create Account
                    </a>
                </div>
            </nav>

            <!-- Main Heading -->
            <div class="max-w-4xl">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight mb-4">
                    Ang DILG ay Matino, Mahusay at Maaasahan
                </h1>
            </div>
        </div>
    </header>

    <main class="flex-1 bg-gradient-to-b from-blue-50 via-indigo-100 to-blue-50">
        <section class="max-w-7xl mx-auto px-6 py-8">
            <div class="bg-white rounded-2xl shadow-lg p-5 sm:p-6 md:p-8">
                <div class="flex flex-col gap-5 mb-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Latest Jobs</h2>
                        <span class="hidden sm:inline-flex items-center rounded-full bg-[#0D2B70]/10 px-3 py-1 text-sm font-semibold text-[#0D2B70]">
                            {{ $allCount }} Open {{ $allCount === 1 ? 'Vacancy' : 'Vacancies' }}
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row border-b border-[#0D2B70] flex-wrap items-start sm:items-center gap-4 sm:gap-2">
                        <div class="flex flex-wrap items-center gap-2 flex-1" id="filterButtons">
                            <button type="button" class="filter-btn px-4 py-2.5 bg-[#0D2B70] text-white rounded-full font-semibold text-sm shadow-sm active" data-filter="all">All Vacancies ({{ $allCount }})</button>
                            <button type="button" class="filter-btn px-4 py-2.5 text-gray-600 bg-gray-100 rounded-full font-semibold text-sm" data-filter="plantilla">Plantilla ({{ $plantillaCount }})</button>
                            <button type="button" class="filter-btn px-4 py-2.5 text-gray-600 bg-gray-100 rounded-full font-semibold text-sm" data-filter="cos">Contract of Service ({{ $cosCount }})</button>
                            <!-- <button type="button" class="filter-btn px-4 py-2.5 text-gray-600 bg-gray-100 rounded-full font-semibold text-sm" data-filter="ojt">On-the-Job Training ({{ $ojtCount }})</button> -->
                            <!-- <button type="button" class="filter-btn px-4 py-2.5 text-gray-600 bg-gray-100 rounded-full font-semibold text-sm" data-filter="contractual">Contractual ({{ $contractualCount }})</button> -->
                        </div>
                        <div class="relative w-full sm:w-auto">
                            <input 
                                type="text" 
                                id="searchInput" 
                                placeholder="Search job titles..." 
                                class="w-full px-6 py-3 pr-14 border-2 border-gray-200 rounded-full font-semibold text-sm placeholder-gray-400 bg-white focus:outline-none focus:border-[#0D2B70] focus:shadow-md transition-all"
                            />
                            <button type="button" id="searchBtn" class="absolute right-1 top-1/2 transform -translate-y-1/2 p-2 text-[#0D2B70] hover:bg-blue-50 rounded-full transition-all active:scale-95">
                                <i data-feather="search" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="vacancyGrid">
                    @forelse ($vacancies as $vacancy)
                        @php
                            $closingDate = \Carbon\Carbon::parse($vacancy->closing_date)->setTime(17, 0, 0); // Set closing time to 5:00 PM
                            $now = \Carbon\Carbon::now();
                            $isClosed = $now->greaterThan($closingDate);
                            
                            $typeNormalized = strtolower(trim((string) ($vacancy->vacancy_type ?? '')));
                            $filterType = match($typeNormalized) {
                                'permanent', 'plantilla' => 'plantilla',
                                'cos', 'contract of service', 'contract' => 'cos',
                                default => 'other',
                            };
                            
                            // Expand vacancy type to full name
                            $vacancyTypeDisplay = match($typeNormalized) {
                                'cos', 'contract of service', 'contract' => 'Contract of Service Position',
                                'plantilla' => 'Plantilla Position',
                                'permanent' => 'Plantilla Position',
                                default => strtoupper($vacancy->vacancy_type ?? '') . ' Position',
                            };
                        @endphp
                        @if(!$isClosed)
                        <article
                            class="vacancy-card bg-white rounded-xl border border-gray-200 hover:border-[#0D2B70]/40 hover:shadow-md transition-all duration-200 cursor-pointer"
                            data-type="{{ $filterType }}"
                            data-vacancy="{{ base64_encode(json_encode([
                                'vacancy_id' => $vacancy->vacancy_id,
                                'position_title' => $vacancy->position_title,
                                'vacancy_type' => $vacancy->vacancy_type,
                                'qualification_education' => $vacancy->qualification_education,
                                'qualification_training' => $vacancy->qualification_training,
                                'qualification_experience' => $vacancy->qualification_experience,
                                'qualification_eligibility' => $vacancy->qualification_eligibility,
                                'competencies' => $vacancy->competencies,
                            ])) }}"
                        >
                            <div class="p-5 sm:p-6">
                                @php 
                                    $closingSoonStart = $closingDate->copy()->subDay()->startOfDay(); 
                                    $isDeadlineSoon = $now->greaterThanOrEqualTo($closingSoonStart) && !$isClosed;
                                @endphp
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                    <div>
                                        <h3 class="font-bold text-[#0D2B70] text-lg sm:text-xl">{{ $vacancy->position_title }}</h3>
                                        <p class="text-gray-700 text-sm sm:text-base mt-1 font-medium">{{ $vacancy->office_assignment ?? 'DILG - CAR' }}</p>
                                        <p class="text-[#0D2B70]/75 text-sm mt-1 italic">{{ $vacancyTypeDisplay }}</p>
                                    </div>
                                    <span class="text-[#0D2B70] font-bold text-base sm:text-lg bg-blue-50 px-4 py-2 rounded-lg w-fit whitespace-nowrap">
                                        @if($vacancy->salary_grade)
                                            @php
                                                $gradeNum = preg_replace('/[^0-9]/', '', $vacancy->salary_grade);
                                                if (empty($gradeNum)) $gradeNum = $vacancy->salary_grade;
                                            @endphp
                                            SG {{ $gradeNum }} - ₱{{ number_format((float) ($vacancy->monthly_salary ?? 2), 2) }}
                                        @else
                                            ₱{{ number_format((float) ($vacancy->monthly_salary ?? 0), 2) }}
                                        @endif
                                    </span>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-5 pt-4 border-t border-gray-200">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 text-gray-600 text-sm sm:text-base">
                                            <i data-feather="map-pin" class="w-4 h-4"></i>
                                            <span>{{ $vacancy->place_of_assignment ?? 'DILG-CAR' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-gray-500 text-sm sm:text-base">
                                            <i data-feather="calendar" class="w-4 h-4"></i>
                                            <span>Deadline: {{ \Carbon\Carbon::parse($vacancy->closing_date)->format('F d, Y') }}</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3">
                                        @if($isClosed)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                <span class="w-2 h-2 bg-red-500 rounded-full mr-1.5"></span>
                                                Closed
                                            </span>
                                        @elseif($isDeadlineSoon)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                                <span class="w-2 h-2 bg-orange-500 rounded-full mr-1.5"></span>
                                                Closing Soon
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span>
                                                Open
                                            </span>
                                        @endif
                                        <span class="text-[#0D2B70] font-semibold hover:underline inline-flex items-center gap-2">
                                            View details
                                            <i data-feather="arrow-right" class="w-4 h-4"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </article>
                        @endif
                    @empty
                        <div class="text-center py-12 text-gray-500 flex flex-col items-center justify-center">
                            <div class="bg-gray-100 p-4 rounded-full mb-3">
                                <i data-feather="inbox" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <span class="font-semibold text-lg">No Job Vacancy Found</span>
                            <p class="text-sm text-gray-400 mt-1">Please check back soon for new openings.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($vacancies->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $vacancies->links('pagination::tailwind') }}
                </div>
                @endif
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-300 bg-blue-50">
        <div class="max-w-7xl mx-auto px-6 py-4 text-sm text-gray-600 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>© {{ date('Y') }} DILG - CAR</div>
            <div class="flex items-center gap-4">
                <a href="{{ route('job_vacancy') }}" class="text-[#0D2B70] font-semibold hover:underline">Vacancies</a>
                <a href="{{ route('about') }}" class="text-[#0D2B70] font-semibold hover:underline">About</a>
            </div>
        </div>
    </footer>

    <script>
        const documentMetaForLanding = @json($documentMetaForLanding);
        const requiredDocsByTrackForLanding = @json($requiredDocsByTrackForLanding);

        function normalizeVacancyTrack(vacancyType) {
            const type = String(vacancyType || '').trim().toLowerCase();
            return (type === 'cos' || type === 'contract of service') ? 'COS' : 'Plantilla';
        }

        function getRequiredDocumentsForTrack(track) {
            const required = new Set(requiredDocsByTrackForLanding[track] || []);
            return Object.keys(documentMetaForLanding)
                .filter((docType) => required.has(docType))
                .map((docType) => documentMetaForLanding[docType]);
        }

        function formatEligibilityPhrase(value) {
            return String(value || '')
                .replace(/PQE/gi, 'PQE (if taken and passed)')
                .trim();
        }

        function parseEligibilityEntries(rawValue) {
            const raw = String(rawValue || '').trim();
            if (!raw) {
                return [];
            }

            const entries = [];
            const addEntry = (nameValue, legalBasisValue = '', levelValue = '') => {
                const name = formatEligibilityPhrase(nameValue);
                if (!name) return;
                const legalBasis = formatEligibilityPhrase(legalBasisValue);
                const level = formatEligibilityPhrase(levelValue);
                const detailParts = [legalBasis, level].filter(Boolean);
                entries.push(detailParts.length ? `${name} (${detailParts.join(' | ')})` : name);
            };

            try {
                const parsed = JSON.parse(raw);
                const source = Array.isArray(parsed) ? parsed : [parsed];
                source.forEach((item) => {
                    if (item && typeof item === 'object') {
                        addEntry(item.name, item.legalBasis, item.level);
                    } else {
                        addEntry(item);
                    }
                });
            } catch (_) {
                raw
                    .split(/\r?\n|;/)
                    .map(token => token.trim())
                    .filter(Boolean)
                    .forEach(token => addEntry(token));
            }

            const deduped = [];
            const seen = new Set();
            entries.forEach((entry) => {
                const key = entry.toLowerCase();
                if (!seen.has(key)) {
                    seen.add(key);
                    deduped.push(entry);
                }
            });
            return deduped;
        }

        function renderEligibility(container, rawValue) {
            if (!container) return;
            container.replaceChildren();

            const entries = parseEligibilityEntries(rawValue);
            if (!entries.length) {
                container.textContent = 'N/A';
                return;
            }

            if (entries.length === 1) {
                container.textContent = entries[0];
                return;
            }

            const list = document.createElement('ul');
            list.className = 'list-disc pl-5 space-y-1';
            entries.forEach((entry) => {
                const item = document.createElement('li');
                item.textContent = entry;
                list.appendChild(item);
            });
            container.appendChild(list);
        }

        function showJobDetails(job) {
            // Store job ID in session storage or data attribute for later use
            sessionStorage.setItem('selectedJobId', job.vacancy_id);
            sessionStorage.setItem('selectedJobTitle', job.position_title);
            
            // Set modal title
            document.getElementById('modalJobTitle').textContent = job.position_title;
            document.getElementById('modalVacancyType').textContent = job.vacancy_type + ' Position';
            
            // Set qualification standards
            document.getElementById('modalEducation').textContent = job.qualification_education || 'N/A';
            document.getElementById('modalTraining').textContent = job.qualification_training || 'N/A';
            document.getElementById('modalExperience').textContent = job.qualification_experience || 'N/A';
            renderEligibility(document.getElementById('modalEligibility'), job.qualification_eligibility);
            
            const competencyContainer = document.getElementById('modalCompetencyContainer');
            if (job.competencies) {
                document.getElementById('modalCompetency').textContent = job.competencies;
                competencyContainer.style.display = 'grid';
            } else {
                competencyContainer.style.display = 'none';
            }
            
            // Set required documents based on the same COS/Plantilla rules as applicant upload.
            const normalizedTrack = normalizeVacancyTrack(job.vacancy_type);
            const requiredDocLabels = getRequiredDocumentsForTrack(normalizedTrack);
            const documentsList = document.getElementById('requiredDocumentsList');
            const documentsHint = document.getElementById('requiredDocumentsHint');
            documentsList.innerHTML = ''; // Clear existing
            if (documentsHint) {
                documentsHint.textContent = `* Required for ${normalizedTrack} vacancy`;
            }

            requiredDocLabels.forEach((label) => {
                const li = document.createElement('li');
                li.className = 'flex items-start gap-3 text-gray-700';
                li.innerHTML = `
                    <i data-feather="check" class="w-4 h-4 text-green-600 mt-0.5"></i>
                    <span>${label} <span class="text-red-600">*</span></span>
                `;
                documentsList.appendChild(li);
            });

            if (requiredDocLabels.length === 0) {
                const li = document.createElement('li');
                li.className = 'text-sm text-gray-500';
                li.textContent = 'No required documents configured.';
                documentsList.appendChild(li);
            }
            
            // Show modal
            const modal = document.getElementById('documentsModal');
            modal.classList.remove('hidden');
            
            // Re-initialize feather icons
            feather.replace();
            
            // Prevent body scrolling
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            const modal = document.getElementById('documentsModal');
            modal.classList.add('hidden');
            
            // Restore body scrolling
            document.body.style.overflow = '';
        }
        
        // Handle vacancy card clicks
        document.querySelectorAll('.vacancy-card').forEach(card => {
            card.addEventListener('click', function() {
                const encodedData = this.getAttribute('data-vacancy');
                const decodedData = JSON.parse(atob(encodedData));
                showJobDetails(decodedData);
            });
        });
        
        // Handle filter button clicks
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                const filterType = this.getAttribute('data-filter');
                const allButtons = document.querySelectorAll('.filter-btn');
                
                // Update active button styling
                allButtons.forEach(btn => {
                    btn.classList.remove('bg-[#0D2B70]', 'text-white', 'active');
                    btn.classList.add('text-gray-600', 'bg-gray-100');
                });
                this.classList.remove('text-gray-600', 'bg-gray-100');
                this.classList.add('bg-[#0D2B70]', 'text-white', 'active');
                
                // Trigger search to update results with new filter
                performSearch();
            });
        });

        // Handle search functionality
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const allCards = document.querySelectorAll('.vacancy-card');
            const activeFilterBtn = document.querySelector('.filter-btn.active');
            const currentFilter = activeFilterBtn ? activeFilterBtn.getAttribute('data-filter') : 'all';
            
            allCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const office = card.querySelector('p:nth-of-type(1)').textContent.toLowerCase();
                const cardType = card.getAttribute('data-type');
                
                // Check if card matches the search term
                const matchesSearch = searchTerm === '' || title.includes(searchTerm) || office.includes(searchTerm);
                
                // Check if card matches the current filter
                const matchesFilter = currentFilter === 'all' || cardType === currentFilter;
                
                // Show card only if it matches both search and filter
                if (matchesSearch && matchesFilter) {
                    card.classList.remove('hidden');
                    card.style.display = '';
                } else {
                    card.classList.add('hidden');
                    card.style.display = 'none';
                }
            });
        }
        
        searchBtn.addEventListener('click', performSearch);
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
        
        searchInput.addEventListener('input', performSearch);
        
        // Close modal when clicking outside
        document.getElementById('documentsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Optional: Store the job ID in the login link to redirect back after login
        document.getElementById('applyNowBtn').addEventListener('click', function(e) {
            const jobId = sessionStorage.getItem('selectedJobId');
            const jobTitle = sessionStorage.getItem('selectedJobTitle');
            // this.href = "{{ route('login.form') }}?redirect=apply/" + jobId;
        });
        
        feather.replace();
    </script>
 </body>
 </html>
