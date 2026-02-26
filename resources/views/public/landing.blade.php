<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DILG-CAR Careers</title>
    @vite('resources/css/app.css')
    <style>
        body { font-family: sans-serif; }
        .modal-transition {
            transition: opacity 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
    @php
        $vacancyTypeCounts = collect($vacancies)->groupBy(function ($vacancy) {
            return strtolower(trim((string) ($vacancy->vacancy_type ?? '')));
        })->map->count();

        $allCount = $vacancies->count();
        $permanentCount = ($vacancyTypeCounts['permanent'] ?? 0) + ($vacancyTypeCounts['plantilla'] ?? 0);
        $cosCount = ($vacancyTypeCounts['cos'] ?? 0) + ($vacancyTypeCounts['contract of service'] ?? 0);
        $ojtCount = ($vacancyTypeCounts['ojt'] ?? 0) + ($vacancyTypeCounts['on-the-job training'] ?? 0);
        $contractualCount = $vacancyTypeCounts['contractual'] ?? 0;
    @endphp
    <!-- Modal Overlay - Hidden by default -->
    <div id="documentsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden modal-transition p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full mx-4 shadow-2xl transform transition-all max-h-[90vh] flex flex-col overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div>
                    <h3 class="text-2xl font-bold text-blue-900" id="modalJobTitle">Job Position</h3>
                    <p class="text-gray-600 text-sm mt-1" id="modalVacancyType">Vacancy Type</p>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="w-6 h-6 inline-flex items-center justify-center text-xl leading-none">&times;</span>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto flex-1 min-h-0">
                <div class="mb-6">
                    <h4 class="font-bold text-[#0D2B70] mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 inline-flex items-center justify-center">✓</span>
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
                            <span class="text-gray-600" id="modalEligibility"></span>
                        </div>
                        <div class="grid grid-cols-[120px_1fr] gap-2" id="modalCompetencyContainer">
                            <span class="font-semibold text-gray-700">Competency:</span>
                            <span class="text-gray-600" id="modalCompetency"></span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h4 class="font-bold text-[#0D2B70] mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 inline-flex items-center justify-center">📄</span>
                        Required Documents
                    </h4>
                    <div class="bg-blue-50 rounded-xl p-4">
                        <ul id="requiredDocumentsList" class="space-y-3">
                            <!-- Documents will be dynamically inserted here -->
                        </ul>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h4 class="font-bold text-[#0D2B70] mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 inline-flex items-center justify-center">ℹ</span>
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
                        <span class="w-4 h-4 inline-flex items-center justify-center">ℹ</span>
                        You need to be logged in to apply for this position.
                    </p>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
                <button onclick="closeModal()" class="px-4 py-2 text-gray-600 font-semibold hover:bg-gray-100 rounded-lg transition-colors">
                    Close
                </button>
                <a href="{{ route('login.form') }}" id="applyNowBtn" class="inline-flex items-center gap-2 bg-[#0D2B70] text-white px-6 py-2 rounded-lg font-bold hover:bg-[#002C76] transition-colors">
                    <span class="w-4 h-4 inline-flex items-center justify-center">→</span>
                    Login to Apply
                </a>
            </div>
        </div>
    </div>

    <header class="relative overflow-hidden bg-gradient-to-br from-[#003d99] to-[#002966]">
        <div class="absolute -top-24 -left-16 w-64 h-64 rounded-full bg-white/5 blur-2xl"></div>
        <div class="absolute -bottom-28 right-0 w-80 h-80 rounded-full bg-white/5 blur-2xl"></div>

        <div class="relative max-w-7xl mx-auto px-6 py-6">
            <nav class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-8">
                <div class="flex items-start gap-3 min-w-0 lg:pr-6">
                    <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG" class="h-12 w-12 rounded-full border border-white/30 bg-white/10">
                    <span class="text-white text-lg sm:text-xl font-bold tracking-wide leading-tight max-w-4xl">
                        DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT - CORDILLERA ADMINISTRATIVE REGION
                    </span>
                </div>
<<<<<<< Updated upstream
                <div class="flex items-center gap-3">
                    <a href="{{ route('login.form') }}" class="text-white/90 hover:text-white font-semibold">Sign In</a>
                    <a href="{{ route('register.form') }}" class="ml-2 inline-flex items-center gap-2 bg-white text-[#0D2B70] px-4 py-2 rounded-full font-bold shadow hover:shadow-md">
                        <span class="w-4 h-4 inline-flex items-center justify-center">+</span>
=======
                <div class="flex items-center gap-3 sm:gap-4 self-start lg:self-center shrink-0">
                    <a href="{{ route('login.form') }}" class="inline-flex items-center rounded-full border border-white/40 px-4 py-2 text-white/95 hover:text-white hover:bg-white/10 font-semibold transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('register.form') }}" class="inline-flex items-center gap-2 bg-white text-blue-900 px-4 py-2 rounded-full font-bold shadow hover:shadow-md">
                        <i data-feather="user-plus" class="w-4 h-4"></i>
>>>>>>> Stashed changes
                        Create Account
                    </a>
                </div>
            </nav>

            <div class="max-w-3xl pb-10">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-tight">
                    Build Your Career in Public Service
                </h1>
                <p class="text-white/85 text-sm sm:text-base mt-3 max-w-2xl">
                    Explore openings, track your application, and take examinations online. Join us in strengthening local governance across the Cordillera.
                </p>
            </div>
        </div>
    </header>

    <main class="flex-1 -mt-5 relative z-10 bg-gray-50">
        <section class="max-w-7xl mx-auto px-6 pb-8">
            <div class="bg-white rounded-2xl shadow-lg p-5 sm:p-6 md:p-8">
                <div class="flex flex-col gap-5 mb-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Latest Jobs</h2>
                        <span class="hidden sm:inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-900">
                            {{ $allCount }} Open Vacancy{{ $allCount === 1 ? '' : 'ies' }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2" id="filterButtons">
                        <button type="button" class="filter-btn px-4 py-2.5 bg-blue-900 text-white rounded-full font-semibold text-sm shadow-sm active" data-filter="all">All Vacancies ({{ $allCount }})</button>
                        <button type="button" class="filter-btn px-4 py-2.5 text-gray-600 bg-gray-100 rounded-full font-semibold text-sm" data-filter="permanent">Permanent ({{ $permanentCount }})</button>
                        <button type="button" class="filter-btn px-4 py-2.5 text-gray-600 bg-gray-100 rounded-full font-semibold text-sm" data-filter="cos">Contract of Service ({{ $cosCount }})</button>
                        <!-- <button type="button" class="filter-btn px-4 py-2.5 text-gray-600 bg-gray-100 rounded-full font-semibold text-sm" data-filter="ojt">On-the-Job Training ({{ $ojtCount }})</button> -->
                        <!-- <button type="button" class="filter-btn px-4 py-2.5 text-gray-600 bg-gray-100 rounded-full font-semibold text-sm" data-filter="contractual">Contractual ({{ $contractualCount }})</button> -->
                        <div class="relative w-full sm:w-80 sm:ml-auto">
                            <i data-feather="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input
                                id="vacancySearchInput"
                                type="search"
                                placeholder="Search by title, office, type, place..."
                                class="w-full rounded-full border border-gray-300 bg-white py-2.5 pl-9 pr-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400"
                            >
                        </div>
                    </div>
                    <hr class="my-4 border-gray-200 w-full">
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="vacancyGrid">
                    @forelse ($vacancies as $vacancy)
                        @php
                            $typeNormalized = strtolower(trim((string) ($vacancy->vacancy_type ?? '')));
                            $filterType = match($typeNormalized) {
                                'permanent', 'plantilla' => 'permanent',
                                'cos', 'contract of service' => 'cos',
                                default => 'other',
                            };
                            $salaryGradeRaw = trim((string) ($vacancy->salary_grade ?? ''));
                            $salaryGradeDigits = preg_replace('/\D+/', '', $salaryGradeRaw);
                            $salaryGradeDigits = substr($salaryGradeDigits, 0, 2);
                            $salaryGradeDisplay = $salaryGradeDigits !== ''
                                ? ('SG-' . str_pad($salaryGradeDigits, 2, '0', STR_PAD_LEFT))
                                : 'SG-N/A';
                            $salaryDisplay = 'P' . number_format((float) ($vacancy->monthly_salary ?? 0), 0);
                        @endphp
                        <article
                            class="vacancy-card bg-white rounded-xl border border-gray-200 hover:border-blue-400 hover:shadow-md transition-all duration-200 cursor-pointer"
                            data-type="{{ $filterType }}"
                            data-search="{{ strtolower(trim(($vacancy->position_title ?? '') . ' ' . ($vacancy->office_assignment ?? '') . ' ' . ($vacancy->vacancy_type ?? '') . ' ' . ($vacancy->place_of_assignment ?? '') . ' ' . ($vacancy->vacancy_id ?? ''))) }}"
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
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                    <div>
                                        <h3 class="font-bold text-blue-900 text-lg sm:text-xl">{{ $vacancy->position_title }}</h3>
                                        <p class="text-gray-700 text-sm sm:text-base mt-1 font-medium">{{ $vacancy->office_assignment ?? 'DILG - CAR' }}</p>
                                        <p class="text-blue-700 text-sm mt-1 italic">{{ $vacancy->vacancy_type }}</p>
                                    </div>
                                    <span class="text-green-700 font-bold whitespace-nowrap text-base sm:text-lg bg-green-50 px-3 py-1 rounded-lg w-fit">
                                        {{ $salaryGradeDisplay }}, {{ $salaryDisplay }}
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

                                    <span class="text-blue-900 font-semibold hover:underline inline-flex items-center gap-2">
                                        View details
                                        <i data-feather="arrow-right" class="w-4 h-4"></i>
                                    </span>
                                </div>
                            </div>
                        </article>
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

                <div class="mt-8 text-right">
                    <a href="{{ route('job_vacancy') }}" class="inline-flex items-center gap-3 text-base sm:text-lg text-blue-900 font-semibold hover:underline bg-gray-100 border border-gray-300 px-5 py-3 rounded-full hover:bg-gray-200 transition-colors">
                        View all vacancies
                        <i data-feather="arrow-right" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="max-w-7xl mx-auto px-6 py-4 text-sm text-gray-600 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>© {{ date('Y') }} DILG - CAR</div>
            <div class="flex items-center gap-4">
                <a href="{{ route('job_vacancy') }}" class="text-blue-900 font-semibold hover:underline">Vacancies</a>
                <a href="{{ route('about') }}" class="text-blue-900 font-semibold hover:underline">About</a>
            </div>
        </div>
    </footer>

    <script>
        function showJobDetails(job) {
            sessionStorage.setItem('selectedJobId', job.vacancy_id);
            sessionStorage.setItem('selectedJobTitle', job.position_title);
            
            // Set modal title
            document.getElementById('modalJobTitle').textContent = job.position_title;
            document.getElementById('modalVacancyType').textContent = job.vacancy_type + ' Position';
            
            // Set qualification standards
            document.getElementById('modalEducation').textContent = job.qualification_education || 'N/A';
            document.getElementById('modalTraining').textContent = job.qualification_training || 'N/A';
            document.getElementById('modalExperience').textContent = job.qualification_experience || 'N/A';
            let eligibility = job.qualification_eligibility || 'N/A';
            eligibility = eligibility.replace(/PQE/gi, 'PQE(if taken and passed)');
            document.getElementById('modalEligibility').textContent = eligibility;
            
            const competencyContainer = document.getElementById('modalCompetencyContainer');
            if (job.competencies) {
                document.getElementById('modalCompetency').textContent = job.competencies;
                competencyContainer.style.display = 'grid';
            } else {
                competencyContainer.style.display = 'none';
            }
            
            // Set required documents based on vacancy type
            const documentsList = document.getElementById('requiredDocumentsList');
            documentsList.innerHTML = ''; // Clear existing
            
            // Common documents for all positions
            const commonDocs = [
                { icon: 'file-text', name: 'Personal Data Sheet (PDS)' },
                { icon: 'id-card', name: 'Valid Government ID' },
                { icon: 'certificate', name: 'Certificate of Eligibility (if applicable)' }
            ];
            
            // Type-specific documents
            const typeSpecificDocs = job.vacancy_type === 'COS' ? [
                { icon: 'scroll', name: 'Curriculum Vitae (CV)' }
            ] : [
                { icon: 'graduation-cap', name: 'Diploma' },
                { icon: 'clipboard-check', name: 'Service Record (for internal applicants)' },
                { icon: 'certificate', name: 'Civil Service Eligibility' }
            ];
            
            // Combine all documents
            const allDocs = [...commonDocs, ...typeSpecificDocs];
            
            // Add documents to modal
            allDocs.forEach(doc => {
                const li = document.createElement('li');
                li.className = 'flex items-center gap-3 text-gray-700';
                li.innerHTML = `
                    <span class="w-4 h-4 inline-flex items-center justify-center text-[#0D2B70]">•</span>
                    <span>${doc.name}</span>
                `;
                documentsList.appendChild(li);
            });
            
            // Show modal
            const modal = document.getElementById('documentsModal');
            modal.classList.remove('hidden');

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
        
        const vacancySearchInput = document.getElementById('vacancySearchInput');
        const allVacancyCards = document.querySelectorAll('.vacancy-card');
        let activeFilter = 'all';

        function applyVacancyFilters() {
            const searchTerm = (vacancySearchInput?.value || '').trim().toLowerCase();

            allVacancyCards.forEach(card => {
                const cardType = card.getAttribute('data-type');
                const searchableText = (card.getAttribute('data-search') || '').toLowerCase();
                const matchesType = activeFilter === 'all' || cardType === activeFilter;
                const matchesSearch = searchTerm === '' || searchableText.includes(searchTerm);
                const shouldShow = matchesType && matchesSearch;

                if (shouldShow) {
                    card.classList.remove('hidden');
                    card.style.display = '';
                } else {
                    card.classList.add('hidden');
                    card.style.display = 'none';
                }
            });
        }

        // Handle filter button clicks
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                activeFilter = this.getAttribute('data-filter');
                const allButtons = document.querySelectorAll('.filter-btn');

                // Update active button styling
                allButtons.forEach(btn => {
                    btn.classList.remove('bg-blue-900', 'text-white', 'active');
                    btn.classList.add('text-gray-600', 'bg-gray-100');
                });
                this.classList.remove('text-gray-600', 'bg-gray-100');
                this.classList.add('bg-blue-900', 'text-white', 'active');

                applyVacancyFilters();
            });
        });

        // Handle search input
        if (vacancySearchInput) {
            vacancySearchInput.addEventListener('input', applyVacancyFilters);
        }
        applyVacancyFilters();
        
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
        
        document.getElementById('applyNowBtn').addEventListener('click', function(e) {
            const jobId = sessionStorage.getItem('selectedJobId');
            const jobTitle = sessionStorage.getItem('selectedJobTitle');
            // this.href = "{{ route('login.form') }}?redirect=apply/" + jobId;
        });
    </script>
 </body>
 </html>
