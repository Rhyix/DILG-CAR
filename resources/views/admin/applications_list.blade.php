@extends('layout.admin')
@section('title', 'DILG - Applications List')
@section('content')

    <main class="w-full h-[calc(100vh-6rem)] flex flex-col space-y-4 overflow-hidden">

<<<<<<< Updated upstream
        <!-- Header with back arrow and title -->
        <section class="flex-none flex items-center space-x-4 max-w-full">
            <h1
                class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
                <span class="whitespace-nowrap text-[#0D2B70]">Applications List</span>
            </h1>
        </section>
=======
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Applications List</span>
        </h1>
    </section>
>>>>>>> Stashed changes

        {{-- Search & Sort Section --}}
        <div class="flex-none flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Search Bar -->
            <form onsubmit="return false;" class="relative w-full max-w-xs">
                <input id="searchInput" type="search" placeholder="Search" aria-label="Search"
                    class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1" />
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
            </form>

            <!-- Sort Dropdown with Custom Design -->
            <div class="flex flex-wrap gap-2 items-center">
                <div class="flex flex-col gap-2">
                    <label for="statusFilter" class="font-semibold text-[#0D2B70] text-sm">Sort By Status</label>
                    <select aria-label="Filter by Status" id="statusFilter"
                        class="rounded-md text-[#0D2B70] p-2 px-3 font-semibold cursor-pointer border border-[#0D2B70]">
                        <option value="">All</option>
                        <option value="open">OPEN</option>
                        <option value="closed">CLOSED</option>
                    </select>
                </div>
            </div>
        </div>

<<<<<<< Updated upstream
       <!-- Table Container -->
            <div class="flex flex-col border border-[#0D2B70] rounded-xl h-[500px]">
                <div class="overflow-auto flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-[#0D2B70] text-white sticky top-0 z-20">
                            <tr>
                                <th class="py-4 px-6 font-normal">Vacancy ID</th>
                                <th class="py-4 px-6 font-normal">Job Title</th>
                                <th class="py-4 px-6 font-normal text-center">Status</th>
                                <th class="py-4 px-6 font-normal text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="vacancy-list" class="divide-y divide-[#0D2B70]">
                            @forelse ($vacancies as $vacancy)
                                <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                    <td class="py-4 px-6">{{ $vacancy->vacancy_id }}</td>
                                    <td class="py-4 px-6">
                                        <p>{{ $vacancy->position_title }}</p>
                                        <p class="text-[#0D2B70]/70 text-[0.9rem] italic">{{ $vacancy->vacancy_type }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex justify-center items-center gap-3 font-normal">
                                            @php
                                                $statusColor = match (strtolower($vacancy->status)) {
                                                    'open' => 'bg-green-600',
                                                    'closed' => 'bg-red-600',
                                                    default => 'bg-gray-400'
                                                };
                                            @endphp
                                            <span class="w-5 h-5 rounded-full inline-block {{ $statusColor }}"></span>
                                            <span class="text-center font-semibold uppercase">{{ $vacancy->status }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex justify-center items-center">
                                            <button onclick="window.location.href='{{ route('admin.manage_applicants', ['vacancy_id' => $vacancy->vacancy_id]) }}'" 
                                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm
                                                transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
                                                hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 relative">
                                                <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                                                <span>Manage</span>
                                                @if($vacancy->pending_count > 0)
                                                <span class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full leading-none z-10 shadow-sm border border-white">
                                                    {{ $vacancy->pending_count }}
                                                </span>
                                                @endif
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-10 text-gray-500 text-xl">
                                        No applications found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
=======
    <!-- Table Container -->
    <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
        <div class="flex-1 overflow-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                    <tr>
                        <th class="py-4 px-6 font-normal">Vacancy ID</th>
                        <th class="py-4 px-6 font-normal">Job Title</th>
                        <th class="py-4 px-6 font-normal text-center">Status</th>
                        <th class="py-4 px-6 font-normal text-center">Reviewed Applicants</th>
                        <th class="py-4 px-6 font-normal text-center">New Applicants</th>
                    </tr>
                </thead>
                <tbody id="vacancy-list" class="divide-y divide-[#0D2B70]">
                    @forelse ($vacancies as $vacancy)
                    <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                        <!-- Vacancy ID -->
                        <td class="py-4 px-6">{{ $vacancy->vacancy_id }}</td>

                        <!-- Job Title and Type -->
                        <td class="py-4 px-6">
                            <p>{{ $vacancy->position_title }}</p>
                            <p class="text-[#0D2B70]/70 text-[0.9rem] italic">{{ $vacancy->vacancy_type }}</p>
                        </td>

                        <!-- Status -->
                        <td class="py-4 px-6 text-center">
                            <div class="flex justify-center items-center gap-3 font-normal">
                                @php
                                    $statusColor = match(strtolower($vacancy->status)) {
                                        'open' => 'bg-green-600',
                                        'closed' => 'bg-red-600',
                                        default => 'bg-gray-400'
                                    };
                                @endphp
                                <span class="w-3 h-3 rounded-full inline-block {{ $statusColor }}"></span>
                                <span class="text-center font-semibold uppercase">{{ $vacancy->status }}</span>
                            </div>
                        </td>

                        <!-- Reviewed Applicants -->
                        <td class="py-4 px-6 text-center">
                            <div class="flex justify-center items-center">
                                <button onclick="window.location.href='{{ route('admin.reviewed', ['vacancy_id' => $vacancy->vacancy_id]) }}'" 
                                    class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm
                                    transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
                                    hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                    <span>View Reviewed</span>
                                </button>
                            </div>
                        </td>

                        <!-- New Applicants -->
                        <td class="py-4 px-6 text-center">
                            <div class="flex justify-center items-center relative">
                                <button onclick="window.location.href='{{ route('admin.applicants', ['vacancy_id' => $vacancy->vacancy_id]) }}'" 
                                    class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm
                                    transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
                                    hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 relative">
                                    <x-heroicon-o-user-group class="w-4 h-4" />
                                    <span>New Applicants</span>
                                    @if($vacancy->pending_count > 0)
                                    <span class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full leading-none z-10 shadow-sm border border-white">
                                        {{ $vacancy->pending_count }}
                                    </span>
                                    @endif
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-500 text-xl">
                            No applications found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('partials.loader')
</main>

<script>
    // Debounce Function to prevent traffic overload
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');

    function getSearchAndStatus() {
        return {
            search: searchInput.value.trim(),
            status: statusFilter.value.trim()
        };
    }

    // Debounced Search Handler (500ms delay)
    const handleSearch = debounce(function() {
        const { search, status } = getSearchAndStatus();
        fetchVacancies(search, status);
    }, 500);

    searchInput.addEventListener('input', handleSearch);

    statusFilter.addEventListener('change', function () {
        const { search, status } = getSearchAndStatus();
        fetchVacancies(search, status);
    });
>>>>>>> Stashed changes


        @include('partials.loader')
    </main>

    <script>
        // Debounce Function to prevent traffic overload
        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');

        function getSearchAndStatus() {
            return {
                search: searchInput.value.trim(),
                status: statusFilter.value.trim()
            };
        }

        // Debounced Search Handler (500ms delay)
        const handleSearch = debounce(function () {
            const { search, status } = getSearchAndStatus();
            fetchVacancies(search, status);
        }, 500);

        searchInput.addEventListener('input', handleSearch);

        statusFilter.addEventListener('change', function () {
            const { search, status } = getSearchAndStatus();
            fetchVacancies(search, status);
        });


        function fetchVacancies(search = '', status = '') {
            const params = new URLSearchParams({
                search: search,
                status: status
            });

            fetch(`/admin/applications_list?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => renderVacancies(data))
                .catch(error => console.error('Error:', error));
        }

        function renderVacancies(vacancies) {
            const container = document.getElementById('vacancy-list');
            container.innerHTML = '';

            if (vacancies.length === 0) {
                container.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-10 text-gray-500 text-xl">
                            No applications found.
                        </td>
                    </tr>
                `;
                return;
            }

            vacancies.forEach(vacancy => {
                const statusColor = {
                    'open': 'bg-green-600',
                    'closed': 'bg-red-600'
                }[vacancy.status?.toLowerCase()] ?? 'bg-gray-400';

                const pendingBadge = vacancy.pending_count > 0 ?
                    `<span class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full leading-none z-10 shadow-sm border border-white">
                        ${vacancy.pending_count}
                    </span>` : '';

                container.innerHTML += `
                <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                    <td class="py-4 px-6">${vacancy.vacancy_id}</td>
                    <td class="py-4 px-6">
                        <p>${vacancy.position_title}</p>
                        <p class="text-[#0D2B70]/70 text-[0.9rem] italic">${vacancy.vacancy_type}</p>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <div class="flex justify-center items-center gap-3 font-normal">
                            <span class="w-5 h-5 rounded-full inline-block ${statusColor}"></span>
                            <span class="text-center font-semibold uppercase">${vacancy.status}</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <div class="flex justify-center items-center">
                            <button onclick="window.location.href='/admin/manage_applicants/${vacancy.vacancy_id}'"
                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 relative">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Manage</span>
                                ${pendingBadge}
                            </button>
                        </div>
                    </td>
                </tr>`;
            });
        }
    </script>

@endsection