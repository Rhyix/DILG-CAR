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
            <div class="flex items-center justify-between w-full border-b border-[#0D2B70] py-2">
                <h1 class="text-white text-4xl font-montserrat tracking-wide select-none">
                    <span class="whitespace-nowrap text-[#0D2B70]">Manage Applicants</span>
                </h1>
                <div class="text-right">
                    <p class="text-[#0D2B70] text-xl font-semibold">{{ $positionTitle ?? 'Vacancy ' . $vacancyId }}</p>
                    @if(!empty($vacancyType) || !empty($placeOfAssignment))
                        <p class="text-[#0D2B70]/70 text-sm italic">
                            {{ $vacancyType ?? '' }}{{ !empty($vacancyType) && !empty($placeOfAssignment) ? ' • ' : '' }}{{ $placeOfAssignment ?? '' }}
                        </p>
                    @endif
                </div>
            </div>
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
            <button id="tab-compliance" onclick="switchTab('compliance')"
                class="tab-button px-6 py-3 font-semibold text-[#0D2B70] border-b-4 border-transparent hover:bg-blue-50 transition-all duration-200">
                Compliance
                @if($complianceApplicantsCount > 0)
                    <span class="ml-2 bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $complianceApplicantsCount }}
                    </span>
                @endif
            </button>
            <button id="tab-qualified" onclick="switchTab('qualified')"
                class="tab-button px-6 py-3 font-semibold text-[#0D2B70] border-b-4 border-transparent hover:bg-blue-50 transition-all duration-200">
                Qualified Applicants
                @if($qualifiedApplicantsCount > 0)
                    <span class="ml-2 bg-green-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $qualifiedApplicantsCount }}
                    </span>
                @endif
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
                                <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[20%]">Name</th>
                                <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[25%]">Job Applied</th>
                                <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[25%]">Place of Assignment</th>
                                <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[15%]">Status</th>
                                <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[15%]">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="new-applicants-list" class="divide-y divide-[#0D2B70]">
                            @forelse ($newApplicants as $applicant)
                                <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                    <td class="py-4 px-6 text-left w-[20%]">{{ $applicant['name'] }}</td>
                                    <td class="py-4 px-6 text-left w-[25%]">{{ $applicant['job_applied'] }}</td>
                                    <td class="py-4 px-6 text-left w-[25%]">{{ $applicant['place_of_assignment'] }}</td>
                                    <td class="py-4 px-6 text-left w-[15%]">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                            {{ $applicant['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center w-[15%]">
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

        <!-- Tab Content: Compliance -->
        <div id="content-compliance" class="tab-content hidden flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="flex-none flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                <!-- Search Bar -->
                <form onsubmit="return false;" class="relative w-full max-w-xs">
                    <input id="searchInputCompliance" type="search" placeholder="Search applicants" aria-label="Search"
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
                    <label for="sortOrderCompliance" class="font-semibold text-[#0D2B70] text-sm">Sort By</label>
                    <select aria-label="Sort by date" id="sortOrderCompliance"
                        class="rounded-md text-[#0D2B70] p-2 px-3 font-semibold cursor-pointer border border-[#0D2B70]">
                        <option value="latest">Latest</option>
                        <option value="oldest">Oldest</option>
                    </select>
                </div>
            </div>

            <!-- Table Container -->
            <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                <div class="flex-1 overflow-auto">
                    <table class="w-full text-left border-collapse table-fixed">
                        <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                            <tr>
                                <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[20%]">Name</th>
                                <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[25%]">Job Applied</th>
                                <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[25%]">Place of Assignment</th>
                                <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[15%]">Status</th>
                                <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[15%]">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="compliance-applicants-list" class="divide-y divide-[#0D2B70]">
                            @forelse ($complianceApplicants as $applicant)
                                <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                    <td class="py-4 px-6 text-left w-[20%]">{{ $applicant['name'] }}</td>
                                    <td class="py-4 px-6 text-left w-[25%]">{{ $applicant['job_applied'] }}</td>
                                    <td class="py-4 px-6 text-left w-[25%]">{{ $applicant['place_of_assignment'] }}</td>
                                    <td class="py-4 px-6 text-left w-[15%]">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                            {{ $applicant['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center w-[15%]">
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
                                        No applicants in compliance.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Content: Qualified Applicants -->
        <div id="content-qualified" class="tab-content hidden flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="flex-none flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                <!-- Search Bar -->
                <form onsubmit="return false;" class="relative w-full max-w-xs">
                    <input id="searchInputQualified" type="search" placeholder="Search applicants" aria-label="Search"
                        class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </form>
                <div class="flex flex-col gap-2">
                    <label for="sortOrderQualified" class="font-semibold text-[#0D2B70] text-sm">Sort By</label>
                    <select aria-label="Sort by date" id="sortOrderQualified"
                        class="rounded-md text-[#0D2B70] p-2 px-3 font-semibold cursor-pointer border border-[#0D2B70]">
                        <option value="latest">Latest</option>
                        <option value="oldest">Oldest</option>
                    </select>
                </div>
            </div>

            <!-- Table Container -->
            <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                <div class="flex-1 overflow-auto">
                    <table class="w-full text-left border-collapse table-fixed">
                        <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                            <tr>
                                <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[30%]">Name</th>
                                <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[30%]">Job Applied</th>
                                <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[25%]">Place of Assignment</th>
                                <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[15%]">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="qualified-applicants-list" class="divide-y divide-[#0D2B70]">
                            @forelse ($qualifiedApplicants as $applicant)
                                <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                    <td class="py-4 px-6 text-left w-[30%]">{{ $applicant['name'] }}</td>
                                    <td class="py-4 px-6 text-left w-[30%]">{{ $applicant['job_applied'] }}</td>
                                    <td class="py-4 px-6 text-left w-[25%]">{{ $applicant['place_of_assignment'] }}</td>
                                    <td class="py-4 px-6 text-center w-[15%]">
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
                                    <td colspan="4" class="text-center py-10 text-gray-500 text-xl">
                                        No qualified applicants found.
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
            const tabs = ['new', 'compliance', 'qualified'];
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
        document.addEventListener('DOMContentLoaded', function(){
            const params = new URLSearchParams(window.location.search);
            const initialTab = params.get('tab');
            if (['new','compliance','qualified'].includes(initialTab)) {
                switchTab(initialTab);
            } else {
                switchTab('new');
            }
        });

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

        if(searchInputNew) searchInputNew.addEventListener('input', handleNewApplicantsFilter);
        if(sortOrderNew) sortOrderNew.addEventListener('change', handleNewApplicantsFilter);

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

        // Compliance Applicants - Search and Sort
        const searchInputCompliance = document.getElementById('searchInputCompliance');
        const sortOrderCompliance = document.getElementById('sortOrderCompliance');

        const handleComplianceApplicantsFilter = debounce(function () {
            const search = searchInputCompliance.value.trim();
            const sortOrder = sortOrderCompliance.value;
            fetchComplianceApplicants(search, sortOrder);
        }, 500);

        if(searchInputCompliance) searchInputCompliance.addEventListener('input', handleComplianceApplicantsFilter);
        if(sortOrderCompliance) sortOrderCompliance.addEventListener('change', handleComplianceApplicantsFilter);

        function fetchComplianceApplicants(search = '', sortOrder = 'latest') {
            const params = new URLSearchParams({
                vacancy_id: vacancyId,
                search: search,
                sort_order: sortOrder
            });

            fetch(`/admin/manage_applicants/compliance?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('compliance-applicants-list').innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }

        // Qualified Applicants - Search and Sort
        const searchInputQualified = document.getElementById('searchInputQualified');
        const sortOrderQualified = document.getElementById('sortOrderQualified');

        const handleQualifiedApplicantsFilter = debounce(function () {
            const search = searchInputQualified.value.trim();
            const sortOrder = sortOrderQualified.value;
            fetchQualifiedApplicants(search, sortOrder);
        }, 500);

        if(searchInputQualified) searchInputQualified.addEventListener('input', handleQualifiedApplicantsFilter);
        if(sortOrderQualified) sortOrderQualified.addEventListener('change', handleQualifiedApplicantsFilter);

        function fetchQualifiedApplicants(search = '', sortOrder = 'latest') {
            const params = new URLSearchParams({
                vacancy_id: vacancyId,
                search: search,
                sort_order: sortOrder
            });

            fetch(`/admin/manage_applicants/qualified?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('qualified-applicants-list').innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }
    </script>

@endsection
