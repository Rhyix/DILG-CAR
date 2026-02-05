@extends('layout.admin')
@section('title', 'DILG - Admin Exam Management')
@section('content')

<main class="w-full space-y-6">

    <section class="flex items-center space-x-4 mb-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Exam Management</span>
        </h1>
    </section>

    <form onsubmit="return false;" class="relative w-full grid grid-cols-2">
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
                <span class="text-[#0D2B70] font-semibold mr-2">Job Type</span>    
                <select id="jobTypeFilter"
                        class="h-10 cursor-pointer px-4 rounded-md border border-[#0D2B70] text-[#0D2B70] font-semibold bg-white
                            focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1">
                    <option value="">All</option>
                    <option value="COS">COS</option>
                    <option value="Plantilla">Plantilla</option>
                </select>
            </div>
        </div>

        <!-- exam library button -->
        <div class="flex justify-end">
            <button class="h-10 hover:scale-105 animate-ease-in-out px-6 border border-[#0D2B70] transition bg-white font-semibold rounded-md flex items-center gap-2 text-sm">
                <span class="text-[#0D2B70] font-bold">Exam Library</span>
            </button>
        </div>
    </form>

    <!-- Table Header -->
    <div class="overflow-hidden rounded-xl border border-[#0D2B70]">
        <table class="w-full text-left border-collapse">
            <thead class="bg-[#0D2B70] text-white">
                <tr>
                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider">Job ID</th>
                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider">Job Title</th>
                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider">Job Type</th>
                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($vacancies as $vacancy)
                <tr class="hover:bg-blue-50 transition-colors duration-200">
                    <td class="py-4 px-6 text-center text-[#0D2B70] font-semibold">
                        {{ $vacancy->vacancy_id }}
                    </td>
                    <td class="py-4 px-6 text-[#0D2B70] font-medium">
                        {{ $vacancy->position_title }}
                    </td>
                    <td class="py-4 px-6 text-center text-[#0D2B70]">
                        {{ $vacancy->vacancy_type }}
                    </td>
                    <td class="py-4 px-6 text-center">
                        <button onclick="window.location.href='{{ route('admin.manage_exam', $vacancy->vacancy_id) }}'" 
                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-2 px-6 rounded-md text-sm
                                transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
                                hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md"
                        >
                            Manage
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        const searchInput = document.getElementById('examIdFilter');
        const jobTypeFilter = document.getElementById('jobTypeFilter');

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

        function fetchVacancies() {
            const query = searchInput.value;
            const jobType = jobTypeFilter.value;

            // Build query parameters
            const params = new URLSearchParams();
            if (query) params.append('search', query);
            if (jobType) params.append('job_type', jobType);

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
                        <td colspan="4" class="py-6 text-center text-gray-500 font-medium">
                            No records found.
                        </td>
                    </tr>
                `;
                return;
            }

            vacancies.forEach(vacancy => {
                container.innerHTML += `
                <tr class="hover:bg-blue-50 transition-colors duration-200">
                    <td class="py-4 px-6 text-center text-[#0D2B70] font-semibold">
                        ${vacancy.vacancy_id}
                    </td>
                    <td class="py-4 px-6 text-[#0D2B70] font-medium">
                        ${vacancy.position_title}
                    </td>
                    <td class="py-4 px-6 text-center text-[#0D2B70]">
                        ${vacancy.vacancy_type}
                    </td>
                    <td class="py-4 px-6 text-center">
                        <button onclick="window.location.href='/admin/exam_management/manage_exam/${vacancy.vacancy_id}'" 
                            class="bg-[#0D2B70] transition-transform duration-200 ease-in-out hover:scale-105 text-white font-bold py-2 px-6 rounded-md text-sm">
                            Manage
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
