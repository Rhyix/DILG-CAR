@extends('layout.admin')
@section('title', 'DILG - Applications List')
@section('content')

<main class="w-full h-[calc(100vh-6rem)] flex flex-col space-y-4 overflow-hidden">

    <!-- Header with back arrow and title -->
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Applications List</span>
        </h1>
    </section>

    {{-- Search & Sort Section --}}
    <div class="flex-none flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Search Bar -->
        <form onsubmit="return false;" class="relative w-full max-w-xs">
            <input id="searchInput" type="search" placeholder="Search" aria-label="Search"
                class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1" />
            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

    <!-- Table Container -->
    <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
        <div class="flex-1 overflow-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
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
                                <span class="w-5 h-5 rounded-full inline-block {{ $statusColor }}"></span>
                                <span class="text-center font-semibold uppercase">{{ $vacancy->status }}</span>
                            </div>
                        </td>

                        <!-- Reviewed Applicants -->
                        <td class="py-4 px-6 text-center">
                            <div class="flex justify-center items-center">
                                <button onclick="window.location.href='{{ route('admin.manage', ['vacancy_id' => $vacancy->vacancy_id]) }}'" 
                                    class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm
                                    transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
                                    hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                    <span>Manage</span>
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
                    <td colspan="5" class="text-center py-10 text-gray-500 text-xl">
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
                        <button onclick="window.location.href='/admin/reviewed/${vacancy.vacancy_id}'"
                            class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>View Reviewed</span>
                        </button>
                    </div>
                </td>
                <td class="py-4 px-6 text-center">
                    <div class="flex justify-center items-center relative">
                        <button onclick="window.location.href='/admin/applicants/${vacancy.vacancy_id}'"
                            class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 relative">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            <span>New Applicants</span>
                            ${pendingBadge}
                        </button>
                    </div>
                </td>
            </tr>`;
        });
    }
</script>

@endsection