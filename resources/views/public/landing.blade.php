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
<body class="bg-[#F3F8FF] text-gray-900 min-h-screen flex flex-col">
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
    <div id="documentsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden modal-transition">
        <div class="bg-white rounded-2xl max-w-2xl w-full mx-4 shadow-2xl transform transition-all">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div>
                    <h3 class="text-2xl font-bold text-[#0D2B70]" id="modalJobTitle">Job Position</h3>
                    <p class="text-gray-600 text-sm mt-1" id="modalVacancyType">Vacancy Type</p>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6">
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
                        <i data-feather="file-text" class="w-5 h-5"></i>
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
            <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
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
        <div class="absolute inset-0 bg-gradient-to-br from-[#0D2B70] via-[#17439e] to-[#002C76] opacity-95"></div>
        <div class="absolute -top-24 -left-16 w-64 h-64 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -bottom-28 right-0 w-80 h-80 rounded-full bg-white/10 blur-2xl"></div>

        <div class="relative max-w-7xl mx-auto px-6 py-6">
            <nav class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG" class="h-12 w-12 rounded-full border border-white/30 bg-white/10">
                    <span class="text-white text-2xl font-bold tracking-wide">DILG - CAR</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login.form') }}" class="text-white/90 hover:text-white font-semibold">Sign In</a>
                    <a href="{{ route('register.form') }}" class="inline-flex items-center gap-2 bg-white text-[#0D2B70] px-4 py-2 rounded-full font-bold shadow hover:shadow-md">
                        <i data-feather="user-plus" class="w-4 h-4"></i>
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

    <main class="flex-1 -mt-5 relative z-10 inset-0 bg-gradient-to-br from-[#0D2B70] via-[#17439e] to-[#002C76] opacity-95">
        <section class="max-w-7xl mx-auto px-6 pb-8">
            <div class="bg-white rounded-2xl shadow-lg p-5 sm:p-6 md:p-8">
                <div class="flex flex-col gap-5 mb-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Latest Jobs</h2>
                        <span class="hidden sm:inline-flex items-center rounded-full bg-[#0D2B70]/10 px-3 py-1 text-sm font-semibold text-[#0D2B70]">
                            {{ $allCount }} Open Vacancy{{ $allCount === 1 ? '' : 'ies' }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2" id="filterButtons">
                        <button type="button" class="filter-btn px-4 py-2.5 bg-[#0D2B70] text-white rounded-full font-semibold text-sm shadow-sm active" data-filter="all">All Vacancies ({{ $allCount }})</button>
                        <button type="button" class="filter-btn px-4 py-2.5 text-gray-600 bg-gray-100 rounded-full font-semibold text-sm" data-filter="permanent">Permanent ({{ $permanentCount }})</button>
                        <button type="button" class="filter-btn px-4 py-2.5 text-gray-600 bg-gray-100 rounded-full font-semibold text-sm" data-filter="cos">Contract of Service ({{ $cosCount }})</button>
                        <!-- <button type="button" class="filter-btn px-4 py-2.5 text-gray-600 bg-gray-100 rounded-full font-semibold text-sm" data-filter="ojt">On-the-Job Training ({{ $ojtCount }})</button> -->
                        <!-- <button type="button" class="filter-btn px-4 py-2.5 text-gray-600 bg-gray-100 rounded-full font-semibold text-sm" data-filter="contractual">Contractual ({{ $contractualCount }})</button> -->
                    </div>
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
                        @endphp
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
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                    <div>
                                        <h3 class="font-bold text-[#0D2B70] text-lg sm:text-xl">{{ $vacancy->position_title }}</h3>
                                        <p class="text-gray-700 text-sm sm:text-base mt-1 font-medium">{{ $vacancy->office_assignment ?? 'DILG - CAR' }}</p>
                                        <p class="text-[#0D2B70]/75 text-sm mt-1 italic">{{ $vacancy->vacancy_type }}</p>
                                    </div>
                                    <span class="text-green-700 font-bold whitespace-nowrap text-base sm:text-lg bg-green-50 px-3 py-1 rounded-lg w-fit">
                                        ₱{{ number_format((float) ($vacancy->monthly_salary ?? 0), 2) }}
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

                                    <span class="text-[#0D2B70] font-semibold hover:underline inline-flex items-center gap-2">
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
                    <a href="{{ route('job_vacancy') }}" class="inline-flex items-center gap-3 text-base sm:text-lg text-[#0D2B70] font-semibold hover:underline bg-gray-50 border border-gray-200 px-5 py-3 rounded-full hover:bg-gray-100 transition-colors">
                        View all vacancies
                        <i data-feather="arrow-right" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-200 bg-[#F3F8FF]">
        <div class="max-w-7xl mx-auto px-6 py-4 text-sm text-gray-600 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>© {{ date('Y') }} DILG - CAR</div>
            <div class="flex items-center gap-4">
                <a href="{{ route('job_vacancy') }}" class="text-[#0D2B70] font-semibold hover:underline">Vacancies</a>
                <a href="{{ route('about') }}" class="text-[#0D2B70] font-semibold hover:underline">About</a>
            </div>
        </div>
    </footer>

    <script>
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
                    <i data-feather="${doc.icon}" class="w-4 h-4 text-[#0D2B70]"></i>
                    <span>${doc.name}</span>
                `;
                documentsList.appendChild(li);
            });
            
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
                const allCards = document.querySelectorAll('.vacancy-card');
                
                // Update active button styling
                allButtons.forEach(btn => {
                    btn.classList.remove('bg-[#0D2B70]', 'text-white', 'active');
                    btn.classList.add('text-gray-600', 'bg-gray-100');
                });
                this.classList.remove('text-gray-600', 'bg-gray-100');
                this.classList.add('bg-[#0D2B70]', 'text-white', 'active');
                
                // Show/hide vacancy cards based on filter
                allCards.forEach(card => {
                    const cardType = card.getAttribute('data-type');
                    if (filterType === 'all' || cardType === filterType) {
                        card.classList.remove('hidden');
                        card.style.display = '';
                    } else {
                        card.classList.add('hidden');
                        card.style.display = 'none';
                    }
                });
            });
        });
        
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