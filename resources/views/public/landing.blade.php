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
            <nav class="flex items-center justify-between flex-none">
                <div class="flex items-center gap-3">
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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 flex-1 content-center items-center">
                <div class="space-y-6">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-tight">
                        Build Your Career in Public Service
                    </h1>
                    <p class="text-white/90 text-sm sm:text-base">
                        Explore openings, track your application, and take examinations online. Join us in strengthening local governance across the Cordillera.
                    </p>
                    <div class="bg-gradient-to-br w-[100%] from-white to-blue-50 border border-[#0D2B70]/20 rounded-2xl p-6 sm:p-8">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                            <div>
                                <div class="text-xl md:text-2xl font-bold text-[#0D2B70]">Start your application today</div>
                                <div class="text-gray-600 mt-1 text-sm">Sign in or create an account to begin.</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('login.form') }}" class="inline-flex items-center gap-2 bg-[#0D2B70] text-white px-5 py-2.5 rounded-full w-[100%] font-bold shadow hover:shadow-md">
                                    <i data-feather="log-in" class="w-5 h-5"></i>
                                    Sign In
                                </a>
                                <a href="{{ route('register.form') }}" class="inline-flex items-center gap-2 bg-white border border-[#0D2B70] text-[#0D2B70] px-5 py-2.5 rounded-full font-bold hover:bg-blue-50">
                                    <i data-feather="user-plus" class="w-5 h-5"></i>
                                    Create Account
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <div class="relative">
                        <div class="absolute -inset-6 bg-white/10 blur-2xl rounded-3xl"></div>
                        <div class="relative bg-white w-[125%] rounded-3xl shadow-2xl p-6">
                            <div class="flex items-center justify-between">
                                <div class="font-bold text-[#0D2B70]">Featured Openings</div>
                                <a href="{{ route('job_vacancy') }}" class="text-sm text-[#0D2B70] font-semibold hover:underline">View all</a>
                            </div>
                            <div class="mt-4 divide-y divide-gray-200">
                                @forelse(($vacancies ?? []) as $v)
                                    <div class="py-4 hover:bg-blue-50 transition-colors duration-200 rounded-lg cursor-pointer" onclick="showJobDetails({{ json_encode($v) }})">
                                        <div class="flex items-center justify-between px-2">
                                            <div>
                                                <div class="text-[#0D2B70] font-bold hover:underline">{{ $v->position_title }}</div>
                                                <div class="text-gray-600 text-sm">{{ $v->place_of_assignment }}</div>
                                                <div class="text-gray-500 text-xs mt-1">{{ strtoupper($v->vacancy_type) }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-green-700 font-bold">₱{{ number_format($v->monthly_salary, 2) }}</div>
                                                <span class="inline-flex items-center gap-1 mt-2 text-[#0D2B70] font-semibold">
                                                    <i data-feather="eye" class="w-4 h-4"></i>
                                                    View Details
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-10 text-center text-gray-500 font-semibold">No open vacancies</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <footer class="border-t border-gray-200 flex-none bg-[#F3F8FF] relative z-10">
        <div class="max-w-7xl mx-auto px-6 py-6 text-sm text-gray-600 flex flex-col sm:flex-row items-center justify-between gap-3">
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
