<!-- resources/views/dashboard_user/job_vacancy.blade.php -->

@extends('layout.app')

@section('title', 'Job Vacancies')

@section('content')
<!-- Updated HTML with mobile classes -->
        <!-- Header Section -->
            <div class="flex-none flex items-center mb-10 space-x-4 max-w-full">
                <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
                    <span class="whitespace-nowrap text-[#0D2B70]">Browse Job Vacancies</span>
                </h1>
            </div>

<!-- Sorting & Filtering -->
<section class="flex flex-wrap gap-3 sm:gap-4 filters-mobile">
    <select id="sortFilter" class="border-2 border-[#0D2B70] rounded-lg px-6 sm:px-4 py-2 text-sm font-montserrat">
        <option value="latest">LATEST</option>
        <option value="oldest">OLDEST</option>
    </select>
    <select id="statusFilter" class="border-2 border-[#0D2B70] rounded-lg px-6 sm:px-4 py-2 text-sm font-montserrat">
        <option value="">ALL</option>
        <option value="OPEN" selected>OPEN</option>
        <option value="CLOSED">CLOSED</option>
    </select>
    <select id="typeFilter" class="border-2 border-[#0D2B70] rounded-lg px-4 sm:px-4 py-2 text-sm font-montserrat">
        <option value="">Vacancy Type</option>
        <option value="COS">Job Order/Contract of Service</option>
        <option value="Plantilla">Plantilla Item</option>
    </select>
    <select id="salaryFilter" class="border-2 border-[#0D2B70] rounded-lg px-12 sm:px-4 py-2 text-sm font-montserrat text-left">
        <option value="">Monthly Salary</option>
        <option value="10-20">₱10,000 - ₱20,000</option>
        <option value="20-30">₱20,001 - ₱30,000</option>
        <option value="30-40">₱30,001 - ₱40,000</option>
        <option value="40-50">₱40,001 - ₱50,000</option>
        <option value="50-60">₱50,001 - ₱60,000</option>
        <option value="60-70">₱60,001 - ₱70,000</option>
        <option value="70-80">₱70,001 - ₱80,000</option>
        <option value="80-90">₱80,001 - ₱90,000</option>
        <option value="90-100">₱90,001 - ₱100,000</option>
        <option value="100">₱100,000+</option>
        <option value="0">Not Specified</option>
    </select>
    <select id="placeFilter" class="border-2 border-[#0D2B70] rounded-lg px-2 sm:px-4 py-2 text-sm font-montserrat">
        <option value="">Place of Assignment</option>
        <option value="DILG-CAR Regional Office">DILG-CAR Regional Office</option>
        <option value="Apayao Provincial Office">Apayao Provincial Office</option>
        <option value="Abra Provincial Office">Abra Provincial Office</option>
        <option value="Mountain Province Provincial Office">Mountain Province Provincial Office</option>
        <option value="Ifugao Provincial Office">Ifugao Provincial Office</option>
        <option value="Kalinga Provincial Office">Kalinga Provincial Office</option>
        <option value="Benguet Provincial Office">Benguet Provincial Office</option>
        <option value="Baguio City Office">Baguio City Office</option>
    </select>
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
