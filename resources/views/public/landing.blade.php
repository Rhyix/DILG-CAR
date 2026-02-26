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
<body class="bg-[#F3F8FF] text-gray-900 h-screen overflow-hidden flex flex-col">
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

    <header class="relative overflow-hidden flex-1">
        <div class="absolute inset-0 bg-gradient-to-br from-[#0D2B70] via-[#17439e] to-[#002C76] opacity-95"></div>
        <div class="relative max-w-7xl mx-auto px-6 py-6 h-full flex flex-col">
            <nav class="flex items-center justify-between flex-none mb-4">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG" class="h-12 w-12 rounded-full border border-white/30">
                    <span class="text-white text-2xl font-bold tracking-wide">DILG - CAR</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login.form') }}" class="text-white/90 hover:text-white font-semibold">Sign In</a>
                    <a href="{{ route('register.form') }}" class="ml-2 inline-flex items-center gap-2 bg-white text-[#0D2B70] px-4 py-2 rounded-full font-bold shadow hover:shadow-md">
                        <i data-feather="user-plus" class="w-4 h-4"></i>
                        Create Account
                    </a>
                </div>
            </nav>
            
            <!-- Hero Text - Repositioned at top left with minimal style -->
            <!-- <div class="mb-6 max-w-2xl">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white leading-tight">
                    Build Your Career in Public Service
                </h1>
                <p class="text-white/80 text-sm mt-2 max-w-xl">
                    Explore openings, track your application, and take examinations online. Join us in strengthening local governance across the Cordillera.
                </p>
            </div> -->
            <!-- cards dapat -->
            <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-8">
    <!-- Filter/Tabs Section - Now as separate element -->
    <div class="max-w-7xl mx-auto mb-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Latest Jobs</h2>
        <div class="flex flex-wrap items-center gap-2">
            <button class="px-6 py-2.5 bg-[#0D2B70] text-white rounded-full font-semibold text-sm shadow-md hover:shadow-lg transition-all">All Vacancies</button>
            <button class="px-6 py-2.5 text-gray-600 bg-white rounded-full font-semibold text-sm shadow-sm hover:shadow-md hover:bg-gray-50 transition-all">Permanent</button>
            <button class="px-6 py-2.5 text-gray-600 bg-white rounded-full font-semibold text-sm shadow-sm hover:shadow-md hover:bg-gray-50 transition-all">Contract of Service</button>
            <button class="px-6 py-2.5 text-gray-600 bg-white rounded-full font-semibold text-sm shadow-sm hover:shadow-md hover:bg-gray-50 transition-all">On-the-Job Training</button>
            <button class="px-6 py-2.5 text-gray-600 bg-white rounded-full font-semibold text-sm shadow-sm hover:shadow-md hover:bg-gray-50 transition-all">Contractual</button>
        </div>
    </div>

    <!-- Jobs Cards - Individual cards outside container -->
    <div class="max-w-7xl mx-auto">
        <!-- Card 1 -->
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 cursor-pointer mb-6 border-l-4 border-[#0D2B70]">
            <div class="p-6">
                <!-- Job Title and Salary -->
                <div class="flex items-start justify-between gap-4">
                    <h3 class="font-bold text-[#0D2B70] text-xl">Local Government Operations Officer II</h3>
                    <span class="text-green-700 font-bold whitespace-nowrap text-lg bg-green-50 px-3 py-1 rounded-lg">₱36,125.00</span>
                </div>
                
                <!-- Department/Office -->
                <p class="text-gray-700 text-base mt-2 font-medium">NATIONAL BARANGAY OPERATIONS OFFICE</p>
                
                <!-- Location -->
                <div class="flex items-center gap-2 mt-4 text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-base">📍 DILG-CENTRAL OFFICE</span>
                </div>
                
                <!-- Deadline and Action -->
                <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-base text-gray-500">Deadline: March 05, 2026</span>
                    </div>
                    <span class="text-base text-[#0D2B70] font-semibold hover:underline flex items-center gap-2">
                        View details
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 cursor-pointer mb-6 border-l-4 border-[#0D2B70]">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <h3 class="font-bold text-[#0D2B70] text-xl">Administrative Aide VI</h3>
                    <span class="text-green-700 font-bold whitespace-nowrap text-lg bg-green-50 px-3 py-1 rounded-lg">₱19,716.00</span>
                </div>
                
                <p class="text-gray-700 text-base mt-2 font-medium">INFORMATION SYSTEMS AND TECHNOLOGY MANAGEMENT SERVICE</p>
                
                <div class="flex items-center gap-2 mt-4 text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-base">📍 DILG-CENTRAL OFFICE</span>
                </div>
                
                <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-base text-gray-500">Deadline: March 05, 2026</span>
                    </div>
                    <span class="text-base text-[#0D2B70] font-semibold hover:underline flex items-center gap-2">
                        View details
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 cursor-pointer mb-6 border-l-4 border-[#0D2B70]">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <h3 class="font-bold text-[#0D2B70] text-xl">Local Government Operations Officer III</h3>
                    <span class="text-green-700 font-bold whitespace-nowrap text-lg bg-green-50 px-3 py-1 rounded-lg">₱45,000.00</span>
                </div>
                
                <p class="text-gray-700 text-base mt-2 font-medium">BUREAU OF LOCAL GOVERNMENT SUPERVISION</p>
                
                <div class="flex items-center gap-2 mt-4 text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-base">📍 DILG-CENTRAL OFFICE</span>
                </div>
                
                <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-base text-gray-500">Deadline: March 05, 2026</span>
                    </div>
                    <span class="text-base text-[#0D2B70] font-semibold hover:underline flex items-center gap-2">
                        View details
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 4 - Additional card to show variety -->
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 cursor-pointer mb-6 border-l-4 border-[#0D2B70]">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <h3 class="font-bold text-[#0D2B70] text-xl">Project Development Officer III</h3>
                    <span class="text-green-700 font-bold whitespace-nowrap text-lg bg-green-50 px-3 py-1 rounded-lg">₱51,000.00</span>
                </div>
                
                <p class="text-gray-700 text-base mt-2 font-medium">PLANNING AND PROJECT DEVELOPMENT DIVISION</p>
                
                <div class="flex items-center gap-2 mt-4 text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-base">📍 DILG-CENTRAL OFFICE</span>
                </div>
                
                <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-base text-gray-500">Deadline: March 12, 2026</span>
                    </div>
                    <span class="text-base text-[#0D2B70] font-semibold hover:underline flex items-center gap-2">
                        View details
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- View All Link - Separate element -->
    <div class="max-w-7xl mx-auto mt-8 text-right">
        <a href="#" class="inline-flex items-center gap-3 text-lg text-[#0D2B70] font-semibold hover:underline bg-white px-6 py-3 rounded-full shadow-md hover:shadow-lg transition-all">
            View all vacancies
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>
</div>
            </div>
        </div>
    </header>
    
    <footer class="border-t border-gray-200 flex-none bg-[#F3F8FF] relative z-10">
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
            document.getElementById('modalEligibility').textContent = job.qualification_eligibility || 'N/A';
            
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
                { icon: 'scroll', name: 'Curriculum Vitae (CV)' },
                { icon: 'award', name: 'Transcript of Records' }
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