<!-- resources/views/dashboard_user/job_vacancy.blade.php -->

@extends('layout.app')

@section('title', 'Job Vacancies')

@section('content')
    <div class="px-4 pb-8 sm:px-8">
<!-- Updated HTML with mobile classes -->
        <!-- Header Section -->
            <div class="flex-none flex items-center mb-6 sm:mb-10 pace-x-4 max-w-full">
                <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-2xl sm:text-4xl font-montserrat py-2 tracking-wide select-none">
                    <span class="whitespace-nowrap text-[#0D2B70]">Browse Job Vacancies</span>
                </h1>
            </div>

<!-- Sorting & Filtering -->
<section class="flex flex-col lg:flex-row flex-wrap gap-3 sm:gap-4 filters-mobile mb-6">
    <form class="relative flex-1 min-w-[240px]" onsubmit="return false;">
        <input id="searchInput" type="search" placeholder="Search job title, place, type" aria-label="Search"
            class="pl-10 pr-4 py-2 rounded-md w-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1" />
        <svg xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
        </svg>
    </form>
    <select id="sortFilter" class="hidden">
        <option value="latest" selected>Latest</option>
        <option value="oldest">Oldest</option>
    </select>
    <select id="statusFilter" class="hidden">
        <option value="">ALL</option>
        <option value="OPEN" selected>OPEN</option>
        <option value="CLOSED">CLOSED</option>
    </select>
    <select id="typeFilter" class="hidden">
        <option value="">All Types</option>
        <option value="COS">COS</option>
        <option value="Plantilla">Plantilla</option>
    </select>
    <select id="salaryFilter" class="hidden">
        <option value="">All Salaries</option>
        <option value="10-20">10-20</option>
        <option value="20-30">20-30</option>
        <option value="30-40">30-40</option>
        <option value="40-50">40-50</option>
        <option value="50-60">50-60</option>
        <option value="60-70">60-70</option>
        <option value="70-80">70-80</option>
        <option value="80-90">80-90</option>
        <option value="90-100">90-100</option>
        <option value="100-1000">100-1000</option>
    </select>
    <select id="placeFilter" class="hidden">
        <option value="">All Places</option>
        <option value="DILG-CAR Regional Office">DILG-CAR Regional Office</option>
        <option value="Apayao Provincial Office">Apayao Provincial Office</option>
        <option value="Abra Provincial Office">Abra Provincial Office</option>
        <option value="Mountain Province Provincial Office">Mountain Province Provincial Office</option>
        <option value="Ifugao Provincial Office">Ifugao Provincial Office</option>
        <option value="Kalinga Provincial Office">Kalinga Provincial Office</option>
        <option value="Benguet Provincial Office">Benguet Provincial Office</option>
        <option value="Baguio City Office">Baguio City Office</option>
    </select>
    <!-- Sorting Latest to Oldest -->
    <div x-data="{ open: false }" class="relative w-full sm:w-auto">
        <button
            @click="open = !open"
            class="font-semibold flex items-center justify-between sm:justify-start w-full sm:w-auto px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70] transition whitespace-nowrap hover:text-white hover:shadow-md border border-[#0D2B70]"
        >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
            </svg>
            Sort By:
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
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold"
                onclick="document.getElementById('sortFilter').value='latest'; document.getElementById('sortFilter').dispatchEvent(new Event('change'));"
                @click="open=false">Latest</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold"
                onclick="document.getElementById('sortFilter').value='oldest'; document.getElementById('sortFilter').dispatchEvent(new Event('change'));"
                @click="open=false">Oldest</button>
        </div>
    </div>
    <!-- Filtering by Status -->
    <div x-data="{ open: false }" class="relative w-full sm:w-auto">
        <button
            @click="open = !open"
            class="font-semibold flex items-center justify-between sm:justify-start w-full sm:w-auto px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70] transition whitespace-nowrap hover:text-white hover:shadow-md border border-[#0D2B70]"
        >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" fill="none"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
            </svg>
            Status:
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
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold"
                onclick="document.getElementById('statusFilter').value=''; document.getElementById('statusFilter').dispatchEvent(new Event('change'));"
                @click="open=false">ALL</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold"
                onclick="document.getElementById('statusFilter').value='OPEN'; document.getElementById('statusFilter').dispatchEvent(new Event('change'));"
                @click="open=false">OPEN</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold"
                onclick="document.getElementById('statusFilter').value='CLOSED'; document.getElementById('statusFilter').dispatchEvent(new Event('change'));"
                @click="open=false">CLOSED</button>
        </div>
    </div>
    <!-- Filtering by Vacancy Type -->
    <div x-data="{ open: false }" class="relative w-full sm:w-auto">
        <button
            @click="open = !open"
            class="font-semibold flex items-center justify-between sm:justify-start w-full sm:w-auto px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70] transition whitespace-nowrap hover:text-white hover:shadow-md border border-[#0D2B70]"
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
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold"
                onclick="document.getElementById('typeFilter').value=''; document.getElementById('typeFilter').dispatchEvent(new Event('change'));"
                @click="open=false">All Types</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold"
                onclick="document.getElementById('typeFilter').value='COS'; document.getElementById('typeFilter').dispatchEvent(new Event('change'));"
                @click="open=false">Job Order/Contract of Service</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold"
                onclick="document.getElementById('typeFilter').value='Plantilla'; document.getElementById('typeFilter').dispatchEvent(new Event('change'));"
                @click="open=false">Plantilla Item</button>
        </div>
    </div>
    <!-- Filtering by Monthly Salary -->
    <div x-data="{ open: false }" class="relative w-full sm:w-auto">
        <button
            @click="open = !open"
            class="font-semibold flex items-center justify-between sm:justify-start w-full sm:w-auto px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70] transition whitespace-nowrap hover:text-white hover:shadow-md border border-[#0D2B70]"
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
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold"
                onclick="document.getElementById('salaryFilter').value=''; document.getElementById('salaryFilter').dispatchEvent(new Event('change'));"
                @click="open=false">All Salaries</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('salaryFilter').value='10-20'; document.getElementById('salaryFilter').dispatchEvent(new Event('change'));"
                @click="open=false">₱10,000 - ₱20,000</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('salaryFilter').value='20-30'; document.getElementById('salaryFilter').dispatchEvent(new Event('change'));"
                @click="open=false">₱20,001 - ₱30,000</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('salaryFilter').value='30-40'; document.getElementById('salaryFilter').dispatchEvent(new Event('change'));"
                @click="open=false">₱30,001 - ₱40,000</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('salaryFilter').value='40-50'; document.getElementById('salaryFilter').dispatchEvent(new Event('change'));"
                @click="open=false">₱40,001 - ₱50,000</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('salaryFilter').value='50-60'; document.getElementById('salaryFilter').dispatchEvent(new Event('change'));"
                @click="open=false">₱50,001 - ₱60,000</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('salaryFilter').value='60-70'; document.getElementById('salaryFilter').dispatchEvent(new Event('change'));"
                @click="open=false">₱60,001 - ₱70,000</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('salaryFilter').value='70-80'; document.getElementById('salaryFilter').dispatchEvent(new Event('change'));"
                @click="open=false">₱70,001 - ₱80,000</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('salaryFilter').value='80-90'; document.getElementById('salaryFilter').dispatchEvent(new Event('change'));"
                @click="open=false">₱80,001 - ₱90,000</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('salaryFilter').value='90-100'; document.getElementById('salaryFilter').dispatchEvent(new Event('change'));"
                @click="open=false">₱90,001 - ₱100,000</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('salaryFilter').value='100-1000'; document.getElementById('salaryFilter').dispatchEvent(new Event('change'));"
                @click="open=false">₱100,000+</button>
        </div>
    </div>
    <!-- Filtering by Place of Assignment -->
    <div x-data="{ open: false }" class="relative w-full sm:w-auto">
        <button
            @click="open = !open"
            class="font-semibold flex items-center justify-between sm:justify-start w-full sm:w-auto px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70] transition whitespace-nowrap hover:text-white hover:shadow-md border border-[#0D2B70]"
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
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold"
                onclick="document.getElementById('placeFilter').value=''; document.getElementById('placeFilter').dispatchEvent(new Event('change'));"
                @click="open=false">All Places</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('placeFilter').value='DILG-CAR Regional Office'; document.getElementById('placeFilter').dispatchEvent(new Event('change'));"
                @click="open=false">DILG-CAR Regional Office</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('placeFilter').value='Apayao Provincial Office'; document.getElementById('placeFilter').dispatchEvent(new Event('change'));"
                @click="open=false">Apayao Provincial Office</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('placeFilter').value='Abra Provincial Office'; document.getElementById('placeFilter').dispatchEvent(new Event('change'));"
                @click="open=false">Abra Provincial Office</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('placeFilter').value='Mountain Province Provincial Office'; document.getElementById('placeFilter').dispatchEvent(new Event('change'));"
                @click="open=false">Mountain Province Provincial Office</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('placeFilter').value='Ifugao Provincial Office'; document.getElementById('placeFilter').dispatchEvent(new Event('change'));"
                @click="open=false">Ifugao Provincial Office</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('placeFilter').value='Kalinga Provincial Office'; document.getElementById('placeFilter').dispatchEvent(new Event('change'));"
                @click="open=false">Kalinga Provincial Office</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('placeFilter').value='Benguet Provincial Office'; document.getElementById('placeFilter').dispatchEvent(new Event('change'));"
                @click="open=false">Benguet Provincial Office</button>
            <button class="block w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100"
                onclick="document.getElementById('placeFilter').value='Baguio City Office'; document.getElementById('placeFilter').dispatchEvent(new Event('change'));"
                @click="open=false">Baguio City Office</button>
        </div>
    </div>
</section>

<!-- Job Vacancies List -->
<div class="bg-white rounded-xl border border-[#0D2B70] shadow-sm overflow-hidden flex flex-col h-[75vh] lg:h-[65vh]">
    <!-- Desktop Header -->
    <div class="hidden lg:flex flex-none bg-[#0D2B70] text-white text-sm font-bold uppercase tracking-wider sticky top-0 z-10">
        <div class="py-4 px-6 w-[25%]">Job Title</div>
        <div class="py-4 px-6 w-[12%]">Monthly Salary</div>
        <div class="py-4 px-6 w-[18%]">Place of Assignment</div>
        <div class="py-4 px-6 w-[15%] text-center">Deadline</div>
        <div class="py-4 px-6 w-[10%] text-center">Status</div>
        <div class="py-4 px-6 w-[10%] text-center">Exam</div>
        <div class="py-4 px-6 w-[10%] text-center">Actions</div>
    </div>

    <!-- List Container -->
    <div id="vacancy-list" class="flex-1 overflow-y-auto divide-y divide-gray-200 lg:divide-blue-100">
        @include('partials.vacancy_list', ['vacancies' => $vacancies])
    </div>
</div>
</div>

@include('partials.loader')

        <script>
            function debounce(fn, ms) { let t; return function(){ clearTimeout(t); const a=arguments, self=this; t=setTimeout(function(){ fn.apply(self,a); }, ms); }; }
            function fetchVacancies() {
                const status = document.getElementById('statusFilter').value;
                const sort = document.getElementById('sortFilter').value;
                const type = document.getElementById('typeFilter').value;
                const salary = document.getElementById('salaryFilter').value;
                const place = document.getElementById('placeFilter').value;
                const search = document.getElementById('searchInput').value.trim();
                const loader = document.getElementById('loader');
                loader?.classList.remove('hidden');

                // Build query parameters
                const params = new URLSearchParams({
                    status: status,
                    sort: sort,
                    type: type,
                    salary: salary,
                    place: place,
                    search: search
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
            const fetchVacanciesDebounced = debounce(fetchVacancies, 300);

            // Attach change event listeners to all filters
            document.getElementById('statusFilter').addEventListener('change', fetchVacancies);
            document.getElementById('sortFilter').addEventListener('change', fetchVacancies);
            document.getElementById('typeFilter').addEventListener('change', fetchVacancies);
            document.getElementById('salaryFilter').addEventListener('change', fetchVacancies);
            document.getElementById('placeFilter').addEventListener('change', fetchVacancies);
            document.getElementById('searchInput').addEventListener('input', fetchVacanciesDebounced);

            window.addEventListener('DOMContentLoaded', () => {
                fetchVacancies();
            });
        </script>

@endsection
