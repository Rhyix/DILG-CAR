@extends('layout.admin')
@section('title', 'DILG - Applications List')
@section('content')

    <main class="w-full h-[calc(100vh-6rem)] flex flex-col space-y-4 overflow-hidden">

        <!-- Header with back arrow and title -->
        <section class="flex-none flex items-center space-x-4 max-w-full">
            <h1
                class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
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

        <!-- Table Container -->
        <div class="flex flex-col border border-[#0D2B70] h-full rounded-2xl overflow-hidden">
            <!-- HEADER - Fixed outside scrollable area -->
            <div class="bg-[#0D2B70] text-white rounded-t-xl flex-none">
                <table class="w-full text-left border-separate table-fixed">
                    <thead>
                        <tr>
                            <th class="py-4 px-6 font-normal w-[15%]">Vacancy ID</th>
                            <th class="py-4 px-6 font-normal w-[45%]">Job Title</th>
                            <th class="py-4 px-6 font-normal text-center w-[15%]">Status</th>
                            <th class="py-4 px-6 font-normal text-center w-[25%]">Manage Applications</th>
                        </tr>
                    </thead>
                </table>
            </div>
<!-- SCROLLABLE BODY CONTAINER -->
    <div class="flex-1 overflow-y-auto min-h-0">
        <table class="w-full text-left border-collapse table-fixed">
            <tbody id="vacancy-list" class="align-items-centerdivide-y divide-[#0D2B70]">
                @forelse ($vacancies as $vacancy)
                    <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                        <td class="py-4 px-6 w-[15%]">{{ $vacancy->vacancy_id }}</td>

                        <td class="py-4 px-6 w-[45%]">
                            <p class="font-medium">{{ $vacancy->position_title }}</p>
                            <p class="text-[#0D2B70]/70 text-[0.8rem] italic mt-0.5">
                                {{ $vacancy->vacancy_type }}
                            </p>
                        </td>

                        <td class="py-4 px-6 text-center w-[15%]">
                            <div class="flex justify-center items-center gap-2 font-normal">
                                @php
                                    $statusColor = match (strtolower($vacancy->status)) {
                                        'open' => 'bg-green-600',
                                        'closed' => 'bg-red-600',
                                        default => 'bg-gray-400'
                                    };
                                @endphp

                                <span class="w-4 h-4 rounded-full inline-block {{ $statusColor }}"></span>
                                <span class="font-semibold uppercase text-sm">
                                    {{ $vacancy->status }}
                                </span>
                            </div>
                        </td>

                        <td class="py-4 px-6 text-center w-[25%]">
                            <div class="flex justify-center items-center gap-2">
                                <a href="{{ route('admin.manage_applicants', ['vacancy_id' => $vacancy->vacancy_id]) }}?tab=new"
                                   class="use-loader py-1 px-3 rounded-md text-sm
                                        transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)]
                                        hover:scale-110">
                                   <span class="font-semibold">New</span>
                                   @if(isset($vacancy->pending_count) && $vacancy->pending_count > 0)
                                       <span class="inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold">
                                           {{ $vacancy->pending_count }}
                                       </span>
                                   @endif
                                </a>
                                <a href="{{ route('admin.manage_applicants', ['vacancy_id' => $vacancy->vacancy_id]) }}?tab=compliance"
                                   class="use-loader py-1 px-3 rounded-md text-sm
                                        transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)]
                                        hover:scale-110">
                                   <span class="font-semibold">Compliance</span>
                                   @if(isset($vacancy->compliance_count) && $vacancy->compliance_count > 0)
                                       <span class="inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1 rounded-full bg-orange-500 text-white text-[10px] font-bold">
                                           {{ $vacancy->compliance_count }}
                                       </span>
                                   @endif
                                </a>
                                <a href="{{ route('admin.manage_applicants', ['vacancy_id' => $vacancy->vacancy_id]) }}?tab=qualified"
                                   class="use-loader py-1 px-3 rounded-md text-sm
                                        transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)]
                                        hover:scale-110">
                                   <span class="font-semibold">Qualified</span>
                                   @if(isset($vacancy->qualified_count) && $vacancy->qualified_count > 0)
                                       <span class="inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1 rounded-full bg-green-600 text-white text-[10px] font-bold">
                                           {{ $vacancy->qualified_count }}
                                       </span>
                                   @endif
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-12 text-gray-500 text-lg">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p>No job vacancies found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            
            <!-- Optional: Add bottom padding for better scrolling experience -->
            <tfoot>
                <tr>
                    <td colspan="4" class="h-4"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>           
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
                    `<span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[9px] font-bold text-white shadow-sm ring-1 ring-white">
                        ${vacancy.pending_count}
                    </span>` : '';

                container.innerHTML += `
                <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                    <td class="py-4 px-6 w-[15%] text-left">${vacancy.vacancy_id}</td>
                    <td class="py-4 px-6 w-[45%] text-left">
                        <p>${vacancy.position_title}</p>
                        <p class="text-[#0D2B70]/70 text-[0.9rem] italic">${vacancy.vacancy_type}</p>
                    </td>
                    <td class="py-4 px-6 text-center w-[15%]">
                        <div class="flex justify-center items-center gap-3 font-normal">
                            <span class="w-5 h-5 rounded-full inline-block ${statusColor}"></span>
                            <span class="text-center font-semibold uppercase">${vacancy.status}</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center w-[25%]">
                        <div class="flex justify-center items-center gap-2">
                            <a href="/admin/manage_applicants/${vacancy.vacancy_id}?tab=new" class="use-loader inline-flex items-center gap-2 text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-3 rounded-full text-xs hover:bg-[#0D2B70] hover:text-white transition">
                                <span>New</span>
                                ${vacancy.pending_count > 0 ? `<span class="inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold">${vacancy.pending_count}</span>` : ''}
                            </a>
                            <a href="/admin/manage_applicants/${vacancy.vacancy_id}?tab=compliance" class="use-loader inline-flex items-center gap-2 text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-3 rounded-full text-xs hover:bg-[#0D2B70] hover:text-white transition">
                                <span>Compliance</span>
                                ${vacancy.compliance_count > 0 ? `<span class="inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1 rounded-full bg-orange-500 text-white text-[10px] font-bold">${vacancy.compliance_count}</span>` : ''}
                            </a>
                            <a href="/admin/manage_applicants/${vacancy.vacancy_id}?tab=qualified" class="use-loader inline-flex items-center gap-2 text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-3 rounded-full text-xs hover:bg-[#0D2B70] hover:text-white transition">
                                <span>Qualified</span>
                                ${vacancy.qualified_count > 0 ? `<span class="inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1 rounded-full bg-green-600 text-white text-[10px] font-bold">${vacancy.qualified_count}</span>` : ''}
                            </a>
                        </div>
                    </td>
                </tr>`;
            });
        }
    </script>
@endsection
