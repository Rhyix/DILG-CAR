@extends('layout.admin')
@section('title', 'DILG - Admin Exam Management')
@section('content')

<main class="w-full h-[calc(98vh-6rem)] flex flex-col space-y-6 overflow-hidden">
    @php
        $isViewerMode = (bool) ($isViewer ?? ((Auth::guard('admin')->user()->role ?? null) === 'viewer'));
    @endphp

    <section class="flex items-center space-x-4 mb-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Exam Management</span>
        </h1>
    </section>

    <form onsubmit="return false;" class="relative w-full">
        <!-- search bar and from type dropdown -->
        <div class="flex flex-row items-center">
            <!-- search bar -->
            <div class="relative flex items-center mr-4 w-full max-w-md">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"
                    />
                </svg>

                <input
                    id="examIdFilter"
                    type="text"
                    placeholder="Search by Job Title or ID"
                    aria-label="Search by Job Title or ID"
                    class="h-10 w-full pl-10 pr-4 rounded-md border border-[#0D2B70]
                        text-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold
                        focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1"
                />
            </div>

            <!-- form type dropdown -->
            <div class="flex flex-row items-center ml-4">
                <span class="text-[#0D2B70] font-semibold mr-2 flex flex-row">Job Type</span>    
                <select id="jobTypeFilter"
                        {{ $isViewerMode ? 'disabled' : '' }}
                        class="h-10 cursor-pointer px-4 rounded-md border border-[#0D2B70] text-[#0D2B70] font-semibold bg-white
                            focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1">
                    @if($isViewerMode)
                        <option value="" selected>All</option>
                    @else
                        <option value="">All</option>
                        <option value="COS">COS</option>
                        <option value="Plantilla">Plantilla</option>
                    @endif
                </select>
            </div>

            <!-- exam status dropdown (New) -->
            <div class="flex flex-row items-center ml-4">
                <span class="text-[#0D2B70] font-semibold mr-2">Status</span>    
                <select id="examStatusFilter"
                        {{ $isViewerMode ? 'disabled' : '' }}
                        class="h-10 cursor-pointer px-4 rounded-md border border-[#0D2B70] text-[#0D2B70] font-semibold bg-white
                            focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1">
                    @if($isViewerMode)
                        <option value="Ongoing" selected>Ongoing</option>
                    @else
                        <option value="">All</option>
                        <option value="Unscheduled">Unscheduled</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                    @endif
                </select>
            </div>
            <!-- exam library button -->
            @if(!$isViewerMode)
                <div class="flex justify-end ml-auto">
                    <button onclick="window.location.href='{{ route('admin.exam_library') }}'" 
                        class="h-10 hover:scale-105 animate-ease-in-out px-6 border border-[#0D2B70] transition bg-white font-semibold rounded-md flex items-center gap-2 text-sm">
                        <span class="text-[#0D2B70] font-bold">Exam Library</span>
                    </button>
                </div>
            @endif
        </div>
    </form>

    <!-- TABLE -->
    <div class="flex-1 flex flex-col min-h-0 overflow-hidden rounded-xl border border-[#0D2B70]">
        <div class="flex-none bg-[#0D2B70] text-white">
            <table class="w-full text-left border-collapse table-fixed">
                <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                <tr>
                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[15%]">Vacancy ID</th>
                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[35%]">Job Title</th>
                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[20%]">Job Type</th>
                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[15%]">Status</th>
                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[15%]">Action</th>
                </tr>
            </thead>
            </table>
        </div>
        <div class="flex-1 overflow-auto">
            <table class="w-full text-left border-collapse table-fixed">
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($vacancies as $vacancy)
                    <tr class="hover:bg-blue-50 transition-colors duration-200">
                        <td class="py-4 px-6 text-center text-[#0D2B70] font-semibold w-[15%]">
                            {{ $vacancy->vacancy_id }}
                        </td>
                        <td class="py-4 px-6 text-center text-[#0D2B70] font-medium w-[35%]">
                            {{ $vacancy->position_title }}
                        </td>
                        <td class="py-4 px-6 text-center text-[#0D2B70] w-[20%]">
                            {{ $vacancy->vacancy_type }}
                        </td>
                        <td class="py-4 px-6 text-center w-[15%]">
                            @php
                                $statusClass = 'bg-gray-100 text-gray-800 border border-gray-400';
                                $statusText  = 'Not Scheduled';
                                $isOngoing   = false;

                                if ($vacancy->exam_status === 'Scheduled') {
                                    $statusClass = 'bg-blue-100 text-blue-800 border border-blue-400';
                                    $statusText  = 'Exam Scheduled';
                                } elseif ($vacancy->exam_status === 'Ongoing') {
                                    $statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-400';
                                    $statusText  = 'Exam in Progress';
                                    $isOngoing   = true;
                                } elseif ($vacancy->exam_status === 'Completed') {
                                    $statusClass = 'bg-green-100 text-green-800 border border-green-400';
                                    $statusText  = 'Exam Completed';
                                }
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                @if($isOngoing)
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                                    </span>
                                @endif
                                <span>{{ $statusText }}</span>
                            </span>
                        </td>
                        <td class="py-4 px-6 text-center w-[15%]">
                            <button onclick="window.location.href='{{ route('admin.manage_exam', $vacancy->vacancy_id) }}'" 
                                    class="text-[#0D2B70] border border-[#0D2B70] font-bold py-2 px-6 rounded-md text-sm
                                    transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
                                    hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md"
                            >
                                {{ $isViewerMode ? 'Monitor' : 'Manage' }}
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const isViewerMode = @json($isViewerMode);
        const searchInput = document.getElementById('examIdFilter');
        const jobTypeFilter = document.getElementById('jobTypeFilter');
        const examStatusFilter = document.getElementById('examStatusFilter');

        // DEBOUNCE FUNCTION WAG IDELETE PLS
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        // Debounced Search Handler (500ms delay)
        const handleSearch = debounce(function() {
            fetchVacancies();
        }, 500);

        // Listener for Search Input
        searchInput.addEventListener('input', handleSearch);

        // Listener for Job Type Dropdown
        jobTypeFilter.addEventListener('change', function() {
            fetchVacancies();
        });

        // Listener for Exam Status Dropdown
        examStatusFilter.addEventListener('change', function() {
            fetchVacancies();
        });

        function fetchVacancies() {
            const query = searchInput.value;
            const jobType = isViewerMode ? '' : jobTypeFilter.value;
            const examStatus = isViewerMode ? 'Ongoing' : examStatusFilter.value;

            // Build query parameters
            const params = new URLSearchParams();
            if (query) params.append('search', query);
            if (jobType) params.append('job_type', jobType);
            if (examStatus) params.append('exam_status', examStatus);

            fetch(`/admin/exam_management?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                renderVacancies(data);
            })
            .catch(error => console.error('Error:', error));
        }

        function renderVacancies(vacancies) {
            const container = document.querySelector('tbody');
            container.innerHTML = '';

            if (vacancies.length === 0) {
                container.innerHTML = `
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-500 font-medium">
                            No records found.
                        </td>
                    </tr>
                `;
                return;
            }

            vacancies.forEach(vacancy => {
                let statusClass = 'bg-gray-100 text-gray-800 border border-gray-400';
                let statusText  = 'Not Scheduled';
                let pingHtml    = '';

                if (vacancy.exam_status === 'Scheduled') {
                    statusClass = 'bg-blue-100 text-blue-800 border border-blue-400';
                    statusText  = 'Exam Scheduled';
                } else if (vacancy.exam_status === 'Ongoing') {
                    statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-400';
                    statusText  = 'Exam in Progress';
                    pingHtml = `
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                        </span>`;
                } else if (vacancy.exam_status === 'Completed') {
                    statusClass = 'bg-green-100 text-green-800 border border-green-400';
                    statusText  = 'Exam Completed';
                }

                container.innerHTML += `
                <tr class="hover:bg-blue-50 transition-colors duration-200">
                    <td class="py-4 px-6 text-center text-[#0D2B70] font-semibold w-[15%]">
                        ${vacancy.vacancy_id}
                    </td>
                    <td class="py-4 px-6 text-center text-[#0D2B70] font-medium w-[35%]">
                        ${vacancy.position_title}
                    </td>
                    <td class="py-4 px-6 text-center text-[#0D2B70] w-[20%]">
                        ${vacancy.vacancy_type}
                    </td>
                    <td class="py-4 px-6 text-center w-[15%]">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold ${statusClass}">
                            ${pingHtml}${statusText}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-center w-[15%]">
                        <button onclick="window.location.href='/admin/exam_management/${encodeURIComponent(vacancy.vacancy_id)}/manage'" 
                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-2 px-6 rounded-md text-sm
                                transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
                                hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md">
                            ${isViewerMode ? 'Monitor' : 'Manage'}
                        </button>
                    </td>
                </tr>
                `;
            });
        }
    </script>
    @include('partials.loader')
</main>

@endsection
