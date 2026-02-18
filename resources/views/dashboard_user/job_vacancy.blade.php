<!-- resources/views/dashboard_user/job_vacancy.blade.php -->

@extends('layout.app')

@section('title', 'Job Vacancies')

@section('content')
<!-- Updated HTML with mobile classes -->
        <!-- Header Section -->
            <div class="flex-none flex items-center mb-10 pace-x-4 max-w-full">
                <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
                    <span class="whitespace-nowrap text-[#0D2B70]">Browse Job Vacancies</span>
                </h1>
            </div>

<!-- Sorting & Filtering -->
<section class="flex flex-wrap gap-3 sm:gap-4 filters-mobile">
    <!-- Sorting Latest to Oldest -->
    <div x-data="{ open: false }" class="relative">
        <button
            @click="open = !open"
            class="font-semibold flex items-center px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70] transition whitespace-nowrap hover:text-white hover:shadow-md border border-[#0D2B70]"
        >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
            </svg>
            Sort By: Latest
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div
            x-show="open"
            x-cloak
            @click.away="open = false"
            x-transition
            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-50"
        >
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold" @click="open = false">Latest</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold" @click="open = false">Oldest</a>
        </div>
    </div>
    <!-- Filtering by Status -->
    <div x-data="{ open: false }" class="relative">
        <button
            @click="open = !open"
            class="font-semibold flex items-center px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70] transition whitespace-nowrap hover:text-white hover:shadow-md border border-[#0D2B70]"
        >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" fill="none"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
            </svg>
            Status: OPEN
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div
            x-show="open"
            x-cloak
            @click.away="open = false"
            x-transition
            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-50"
        >
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold" @click="open = false">ALL</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold" @click="open = false">OPEN</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold" @click="open = false">CLOSED</a>
        </div>
    </div>
    <!-- Filtering by Vacancy Type -->
    <div x-data="{ open: false }" class="relative">
        <button
            @click="open = !open"
            class="font-semibold flex items-center px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70] transition whitespace-nowrap hover:text-white hover:shadow-md border border-[#0D2B70]"
        >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="2" y="7" width="20" height="14" rx="2" stroke="currentColor" fill="none"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 3L12 7 8 3"/>
            </svg>
            Vacancy Type
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div
            x-show="open"
            x-cloak
            @click.away="open = false"
            x-transition
            class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-xl shadow-lg z-50"
        >
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold" @click="open = false">All Types</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold" @click="open = false">Job Order/Contract of Service</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold" @click="open = false">Plantilla Item</a>
        </div>
    </div>
    <!-- Filtering by Monthly Salary -->
    <div x-data="{ open: false }" class="relative">
        <button
            @click="open = !open"
            class="font-semibold flex items-center px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70] transition whitespace-nowrap hover:text-white hover:shadow-md border border-[#0D2B70]"
        >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Monthly Salary
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div
            x-show="open"
            x-cloak
            @click.away="open = false"
            x-transition
            class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-xl shadow-lg z-50 max-h-80 overflow-y-auto"
        >
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold" @click="open = false">All Salaries</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">₱10,000 - ₱20,000</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">₱20,001 - ₱30,000</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">₱30,001 - ₱40,000</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">₱40,001 - ₱50,000</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">₱50,001 - ₱60,000</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">₱60,001 - ₱70,000</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">₱70,001 - ₱80,000</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">₱80,001 - ₱90,000</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">₱90,001 - ₱100,000</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">₱100,000+</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">Not Specified</a>
        </div>
    </div>
    <!-- Filtering by Place of Assignment -->
    <div x-data="{ open: false }" class="relative">
        <button
            @click="open = !open"
            class="font-semibold flex items-center px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70] transition whitespace-nowrap hover:text-white hover:shadow-md border border-[#0D2B70]"
        >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Place of Assignment
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div
            x-show="open"
            x-cloak
            @click.away="open = false"
            x-transition
            class="absolute right-0 mt-2 w-72 bg-white border border-gray-200 rounded-xl shadow-lg z-50 max-h-80 overflow-y-auto"
        >
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold" @click="open = false">All Places</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">DILG-CAR Regional Office</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">Apayao Provincial Office</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">Abra Provincial Office</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">Mountain Province Provincial Office</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">Ifugao Provincial Office</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">Kalinga Provincial Office</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">Benguet Provincial Office</a>
            <a href="#" class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100" @click="open = false">Baguio City Office</a>
        </div>
    </div>
</section>

<!-- Job Vacancies Table -->
<div class="rounded-xl border border-[#0D2B70] mt-6 h-[60vh] flex flex-col overflow-hidden">
    <div class="flex-none bg-[#0D2B70] text-white">
        <table class="w-full text-left border-collapse table-fixed">
            <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                <tr>
                    <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[35%]">Job Title</th>
                    <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[15%]">Monthly Salary</th>
                    <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[25%]">Place of Assignment</th>
                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[10%]">Status</th>
                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[15%]">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="flex-1 overflow-y-auto min-h-0">
        <table class="w-full text-left border-collapse table-fixed">
            <tbody id="vacancy-list" class="divide-y divide-[#0D2B70]">
                @include('partials.vacancy_list', ['vacancies' => $vacancies])
            </tbody>
        </table>
    </div>
</div>

@include('partials.loader')

        <script>
            function fetchVacancies() {
                const status = document.getElementById('statusFilter').value;
                const sort = document.getElementById('sortFilter').value;
                const type = document.getElementById('typeFilter').value;
                const salary = document.getElementById('salaryFilter').value;
                const place = document.getElementById('placeFilter').value;
                const loader = document.getElementById('loader');
                loader?.classList.remove('hidden');

                // Build query parameters
                const params = new URLSearchParams({
                    status: status,
                    sort: sort,
                    type: type,
                    salary: salary,
                    place: place
                });

                fetch(`/job-vacancies/filter?${params.toString()}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('vacancy-list').innerHTML = html;
                        feather.replace();
                        loader?.classList.add('hidden');
                    })
                    .catch(() => {
                        alert('Failed to load vacancies.');
                        loader?.classList.add('hidden');
                    });
            }

            // Attach change event listeners to all filters
            document.getElementById('statusFilter').addEventListener('change', fetchVacancies);
            document.getElementById('sortFilter').addEventListener('change', fetchVacancies);
            document.getElementById('typeFilter').addEventListener('change', fetchVacancies);
            document.getElementById('salaryFilter').addEventListener('change', fetchVacancies);
            document.getElementById('placeFilter').addEventListener('change', fetchVacancies);

            window.addEventListener('DOMContentLoaded', () => {
                fetchVacancies();
            });
        </script>

@endsection
