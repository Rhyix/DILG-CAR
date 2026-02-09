@extends('layout.admin')
@section('title', 'DILG - Manage Applicants')
@section('content')

    <main class="w-full h-[calc(100vh-6rem)] flex flex-col space-y-4 overflow-hidden">

        <!-- Header with back arrow and title -->
        <section class="flex-none flex items-center space-x-4 max-w-full">
            <button aria-label="Back" onclick="window.location.href='{{ route('applications_list') }}'" class="group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h1
                class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
                <span class="whitespace-nowrap text-[#0D2B70]">Manage Applicants</span>
            </h1>
        </section>

        <!-- Tab Navigation -->
        <div class="flex-none flex gap-2 border-b border-[#0D2B70]">
            <button id="tab-new" onclick="switchTab('new')"
                class="tab-button px-6 py-3 font-semibold text-[#0D2B70] border-b-4 border-[#0D2B70] bg-blue-50 transition-all duration-200">
                New Applicants
                @if($newApplicantsCount > 0)
                    <span class="ml-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $newApplicantsCount }}
                    </span>
                @endif
            </button>
            <button id="tab-reviewed" onclick="switchTab('reviewed')"
                class="tab-button px-6 py-3 font-semibold text-[#0D2B70] border-b-4 border-transparent hover:bg-blue-50 transition-all duration-200">
                Reviewed Applicants
            </button>
        </div>

        <!-- Tab Content: New Applicants -->
        <div id="content-new" class="tab-content flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="flex-none flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                <!-- Search Bar -->
                <form onsubmit="return false;" class="relative w-full max-w-xs">
                    <input id="searchInputNew" type="search" placeholder="Search applicants" aria-label="Search"
                        class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </form>

                <!-- Sort Dropdown -->
                <div class="flex flex-col gap-2">
                    <label for="sortOrderNew" class="font-semibold text-[#0D2B70] text-sm">Sort By</label>
                    <select aria-label="Sort by date" id="sortOrderNew"
                        class="rounded-md text-[#0D2B70] p-2 px-3 font-semibold cursor-pointer border border-[#0D2B70]">
                        <option value="latest">Latest</option>
                        <option value="oldest">Oldest</option>
                    </select>
                </div>
            </div>

            <!-- Table Container -->
            <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                <div class="flex-1 overflow-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                            <tr>
                                <th class="py-4 px-6 font-normal">Name</th>
                                <th class="py-4 px-6 font-normal">Job Applied</th>
                                <th class="py-4 px-6 font-normal">Place of Assignment</th>
                                <th class="py-4 px-6 font-normal text-center">Status</th>
                                <th class="py-4 px-6 font-normal text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="new-applicants-list" class="divide-y divide-[#0D2B70]">
                            @forelse ($newApplicants as $applicant)
                                <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                    <td class="py-4 px-6">{{ $applicant['name'] }}</td>
                                    <td class="py-4 px-6">{{ $applicant['job_applied'] }}</td>
                                    <td class="py-4 px-6">{{ $applicant['place_of_assignment'] }}</td>
                                    <td class="py-4 px-6 text-center">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                            {{ $applicant['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <button
                                            onclick="window.location.href='{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}'"
                                            class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                            <span>View</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-gray-500 text-xl">
                                        No new applicants found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Content: Reviewed Applicants -->
        <div id="content-reviewed" class="tab-content hidden flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="flex-none flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                <!-- Search Bar -->
                <form onsubmit="return false;" class="relative w-full max-w-xs">
                    <input id="searchInputReviewed" type="search" placeholder="Search applicants" aria-label="Search"
                        class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </form>

                <!-- Status Filter -->
                <div class="flex flex-col gap-2">
                    <label for="statusFilterReviewed" class="font-semibold text-[#0D2B70] text-sm">Filter by Status</label>
                    <select aria-label="Filter by Status" id="statusFilterReviewed"
                        class="rounded-md text-[#0D2B70] p-2 px-3 font-semibold cursor-pointer border border-[#0D2B70]">
                        <option value="">All</option>
                        <option value="Incomplete">Incomplete</option>
                        <option value="Complete">Complete</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
            </div>

            <!-- Table Container -->
            <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                <div class="flex-1 overflow-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                            <tr>
                                <th class="py-4 px-6 font-normal">Name</th>
                                <th class="py-4 px-6 font-normal">Job Applied</th>
                                <th class="py-4 px-6 font-normal">Place of Assignment</th>
                                <th class="py-4 px-6 font-normal text-center">Status</th>
                                <th class="py-4 px-6 font-normal text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reviewed-applicants-list" class="divide-y divide-[#0D2B70]">
                            @forelse ($reviewedApplicants as $applicant)
                                <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                    <td class="py-4 px-6">{{ $applicant['name'] }}</td>
                                    <td class="py-4 px-6">{{ $applicant['job_applied'] }}</td>
                                    <td class="py-4 px-6">{{ $applicant['place_of_assignment'] }}</td>
                                    <td class="py-4 px-6 text-center">
                                        @php
                                            $statusClass = match ($applicant['status']) {
                                                'Complete' => 'bg-green-100 text-green-800',
                                                'Incomplete' => 'bg-yellow-100 text-yellow-800',
                                                'Closed' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                            {{ $applicant['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <button
                                            onclick="window.location.href='{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}'"
                                            class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                            <span>View</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-gray-500 text-xl">
                                        No reviewed applicants found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @include('partials.loader')
    </main>

    <script>
        const vacancyId = {{ $vacancyId }};

        // Tab switching
        function switchTab(tab) {
            const tabs = ['new', 'reviewed'];
            tabs.forEach(t => {
                const tabBtn = document.getElementById(`tab-${t}`);
                const content = document.getElementById(`content-${t}`);

                if (t === tab) {
                    tabBtn.classList.add('border-[#0D2B70]', 'bg-blue-50');
                    tabBtn.classList.remove('border-transparent');
                    content.classList.remove('hidden');
                } else {
                    tabBtn.classList.remove('border-[#0D2B70]', 'bg-blue-50');
                    tabBtn.classList.add('border-transparent');
                    content.classList.add('hidden');
                }
            });
        }

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        // New Applicants - Search and Sort
        const searchInputNew = document.getElementById('searchInputNew');
        const sortOrderNew = document.getElementById('sortOrderNew');

        const handleNewApplicantsFilter = debounce(function () {
            const search = searchInputNew.value.trim();
            const sortOrder = sortOrderNew.value;
            fetchNewApplicants(search, sortOrder);
        }, 500);

        searchInputNew.addEventListener('input', handleNewApplicantsFilter);
        sortOrderNew.addEventListener('change', handleNewApplicantsFilter);

        function fetchNewApplicants(search = '', sortOrder = 'latest') {
            const params = new URLSearchParams({
                vacancy_id: vacancyId,
                search: search,
                sort_order: sortOrder
            });

            fetch(`/admin/manage_applicants/new?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('new-applicants-list').innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }

        // Reviewed Applicants - Search and Filter
        const searchInputReviewed = document.getElementById('searchInputReviewed');
        const statusFilterReviewed = document.getElementById('statusFilterReviewed');

        const handleReviewedApplicantsFilter = debounce(function () {
            const search = searchInputReviewed.value.trim();
            const status = statusFilterReviewed.value;
            fetchReviewedApplicants(search, status);
        }, 500);

        searchInputReviewed.addEventListener('input', handleReviewedApplicantsFilter);
        statusFilterReviewed.addEventListener('change', handleReviewedApplicantsFilter);

        function fetchReviewedApplicants(search = '', status = '') {
            const params = new URLSearchParams({
                vacancy_id: vacancyId,
                search: search,
                status: status
            });

            fetch(`/admin/manage_applicants/reviewed?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('reviewed-applicants-list').innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }
    </script>

@endsection