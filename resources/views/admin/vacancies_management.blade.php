@extends('layout.admin')
@section('title', 'DILG - Job Vacancies Management')
@section('content')
@include('partials.loader')
<!--max-w-7xl -->
<style>
  [x-cloak] { display: none !important; }
</style>
<main class="w-full space-y-6">

    <!-- Header with back arrow and title -->
    <section class="flex items-center space-x-4 mb-4">
        <h1 class="w-full flex items-center bg-[#0D2B70] text-white rounded-xl text-2xl font-extrabold font-montserrat px-8 py-3 tracking-wide select-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-3" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 110-4h6a2 2 0 110 4M9 5v2m6-2v2" />
            </svg>
            VACANCIES MANAGEMENT
        </h1>
    </section>

    <!-- Search and Add New Vacancy button -->
    <section class="flex justify-between items-center">
        <form class="relative w-full max-w-xs" onsubmit="return false;">
            <input id="searchInput" type="search" placeholder="Search" aria-label="Search" value="{{ session('vacancyFilterSearch') }}"
                class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1"
                oninput="fetchVacanciesDebounced()" />
            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
            </svg>
        </form>
        <div class="flex items-center space-x-3">
    <!-- Export Dropdown -->
        <div x-data="{ open: false, alertExportCOS: false }" class="relative">
            <button
                @click="open = !open"
                class="font-semibold flex items-center px-4 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-800 transition"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                </svg>
                Export
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
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
            <!-- COS Export --> 
                @include('partials.alerts_template', [
                    'id' => 'exportCOS',
                    'showTrigger' => true,
                    'triggerText' => 'Export COS vacancies',
                    'triggerClass' => 'block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold text-left',
                    'title' => 'Export Confirmation',
                    'message' => 'Are you sure you want to export COS vacancies?',
                    'showCancel' => true,
                    'cancelText' => 'No, Cancel',
                    'okText' => 'Yes, Export',
                    'okAction' => "window.location.href='" . route('exportJobVacancyCOS') . "'",
                ])
                @include('partials.alerts_template', [
                    'id' => 'exportPlantilla',
                    'showTrigger' => true,
                    'triggerText' => 'Export Plantilla vacancies',
                    'triggerClass' => 'block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold text-left',
                    'title' => 'Export Confirmation',
                    'message' => 'Are you sure you want to export Plantilla vacancies?',
                    'showCancel' => true,
                    'cancelText' => 'No, Cancel',
                    'okText' => 'Yes, Export',
                    'okAction' => "window.location.href='" . route('exportJobVacancyPlantilla') . "'",
                ])
                @include('partials.alerts_template', [
                    'id' => 'exportAll',
                    'showTrigger' => true,
                    'triggerText' => 'Export All vacancies',
                    'triggerClass' => 'block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold text-left',
                    'title' => 'Export Confirmation',
                    'message' => 'Are you sure you want to export All vacancies?',
                    'showCancel' => true,
                    'cancelText' => 'No, Cancel',
                    'okText' => 'Yes, Export',
                    'okAction' => "window.location.href='" . route('exportJobVacancyAll') . "'",
                ])
            </div>
        </div>
        <!-- Import Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button
                @click="open = !open"
                class="font-semibold flex items-center px-4 py-2 bg-green-600 text-white rounded-full hover:bg-green-700 transition"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                </svg>
                Import
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
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
            {{-- Import COS Vacancies --}}
            @include('partials.alerts_template', [
                'id' => 'importCOS',
                'showTrigger' => true,
                'triggerText' => 'Import COS vacancies',
                'triggerClass' => 'block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold text-left',
                'title' => 'Import Confirmation',
                'message' => 'Upload your CSV file to import COS vacancies.',
                'showCancel' => true,
                'cancelText' => 'Cancel',
                'okText' => null, // disables default OK button to render form instead
                'content' =>
                '<form action="' . route('importJobVacancyCOS') . '" method="POST" enctype="multipart/form-data" class="flex flex-col items-center gap-3 w-full">
                    ' . csrf_field() . '
                    <input type="file" name="import_file" accept=".csv" required class="border border-gray-300 rounded px-2 py-1 w-full">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-full font-semibold transition">
                        Import COS Vacancies
                    </button>
                </form>',
            ])

            {{-- Import Plantilla Vacancies --}}
            @include('partials.alerts_template', [
                'id' => 'importPlantilla',
                'showTrigger' => true,
                'triggerText' => 'Import Plantilla vacancies',
                'triggerClass' => 'block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold text-left',
                'title' => 'Import Confirmation',
                'message' => 'Upload your CSV file to import Plantilla vacancies.',
                'showCancel' => true,
                'cancelText' => 'Cancel',
                'okText' => null,
                'content' =>
                '<form action="' . route('importJobVacancyPlantilla') . '" method="POST" enctype="multipart/form-data" class="flex flex-col items-center gap-3 w-full">
                    ' . csrf_field() . '
                    <input type="file" name="import_file" accept=".csv" required class="border border-gray-300 rounded px-2 py-1 w-full">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-full font-semibold transition">
                        Import Plantilla Vacancies
                    </button>
                </form>',
            ])

                @include('partials.alerts_template', [
                    'id' => 'exportCOS',
                    'showTrigger' => true,
                    'triggerText' => 'Download COS template',
                    'triggerClass' => 'block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold text-left',
                    'title' => 'Export Confirmation',
                    'message' => 'Are you sure you want to import Plantilla vacancies?',
                    'showCancel' => true,
                    'cancelText' => 'No, Cancel',
                    'okText' => 'Yes, Download',
                    'okAction' => "window.location.href='" . route('downloadCOSTemplate') . "'",
                ])
                @include('partials.alerts_template', [
                    'id' => 'exportCOS',
                    'showTrigger' => true,
                    'triggerText' => 'Download Plantilla templates',
                    'triggerClass' => 'block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold text-left',
                    'title' => 'Export Confirmation',
                    'message' => 'Are you sure you want to import Plantilla vacancies?',
                    'showCancel' => true,
                    'cancelText' => 'No, Cancel',
                    'okText' => 'Yes, Download',
                    'okAction' => "window.location.href='" . route('downloadPlantillaTemplate') . "'",
                ])
            </div>
        </div>

        <!-- Add New Vacancy Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button
                @click="open = !open"
                class="bg-[#C5292F] hover:bg-red-700 transition text-white font-semibold rounded-full flex items-center gap-2 px-5 py-2 select-none"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 stroke-[3]" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                Add New Vacancy
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
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
                <a href="{{ route('addcos') }}"
                    class="use-loader block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold">
                    Add COS Vacancy
                </a>
                <a href="{{ route('addplantilla') }}"
                    class="use-loader block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold">
                    Add Plantilla Vacancy
                </a>
            </div>
        </div>
    </div>

    </section>

    @if(session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Success!</strong>
        <p class="text-sm">{{ session('success') }}</p>
    </div>
    @endif

    @if ($errors->any())
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Whoops!</strong>
        <ul class="list-disc list-inside text-sm mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if (session('error'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3000)"
            x-show="show"
            x-transition
            class="fixed top-5 right-5 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
        >
            <strong class="font-bold">Whoops!</strong>
                    {{ session('error') }}
        </div>
    @endif

    <!-- Table Header -->
    <section
        class="grid grid-cols-6 gap-4 bg-[#0D2B70] text-white font-bold rounded-xl py-5 px-6 select-none items-center min-h-[70px]">
        <div class="flex items-center justify-start">PLANTILLA ITEM NO.</div>
        <div class="flex items-left justify-start gap-3 flex-col">JOB TITLE
            <select aria-label="Filter Status" id="jobFilter"
                class="rounded-md text-[#0D2B70] p-1 px-2 font-semibold cursor-pointer w-3/4">
                <option value="" {{ session('vacancyFilterJob') == '' ? 'selected' : '' }}>All</option>
                <option value="COS" {{ session('vacancyFilterJob') == 'COS' ? 'selected' : '' }}>COS</option>
                <option value="Plantilla" {{ session('vacancyFilterJob') == 'Plantilla' ? 'selected' : '' }}>PLANTILLA</option>
            </select>
        </div>
        <div class="flex items-center justify-start">MONTHLY SALARY</div>
        <div class="flex items-center justify-center">DEADLINE OF APPLICATION</div>
        <div class="flex items-center justify-start">PLACE OF ASSIGNMENT</div>
        <div class="flex items-left justify-center gap-3 flex-col">
            STATUS
            <select aria-label="Filter Status" id="statusFilter"
                class="rounded-md text-[#0D2B70] p-1 px-2 font-semibold cursor-pointer w-3/4">
                <option value="" {{ session('vacancyFilterStatus') == '' ? 'selected' : '' }}>All</option>
                <option value="OPEN" {{ session('vacancyFilterStatus') == 'OPEN' ? 'selected' : '' }}>OPEN</option>
                <option value="CLOSED" {{ session('vacancyFilterStatus') == 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
            </select>
        </div>
    </section>

    <!-- Table Rows -->
    <section class="space-y-4" id="vacancy-list">
        @forelse ($vacancies as $vacancy)
            @include('partials.admin_job_vacancy_card', ['vacancy' => $vacancy, 'index' => $loop->index])
        @empty
            <div class="text-center text-gray-500 font-semibold text-3xl mt-10">
                <i data-feather="info" class="w-7 h-7 inline-block mr-2 text-gray-400 font-montserrat"></i>
                No Job Vacancy
            </div>
        @endforelse
    </section>
</main>

@push('scripts')
<script>
    const loader_ = document.getElementById('loader');

    function attachLoaderListeners() {
        document.querySelectorAll('.use-loader').forEach(button => {
            button.addEventListener('click', () => {
                loader_?.classList.remove('hidden');
            });
        });

        document.querySelectorAll('form .use-loader').forEach(button => {
            button.closest('form')?.addEventListener('submit', () => {
                loader_?.classList.remove('hidden');
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        attachLoaderListeners();
        fetchVacancies();
    });

    window.attachLoaderListeners = attachLoaderListeners;

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    const fetchVacanciesDebounced = debounce(fetchVacancies, 10);

    function fetchVacancies() {
        const status = document.getElementById('statusFilter')?.value ?? '';
        const sort = document.getElementById('sortFilter')?.value ?? '';
        const job = document.getElementById('jobFilter')?.value ?? '';
        const search = document.getElementById('searchInput').value;

        fetch(`/admin/vacancies_management/filter?status=${status}&sort=${sort}&job=${job}&search=${encodeURIComponent(search)}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('vacancy-list').innerHTML = html;
                loader_?.classList.add('hidden');
                attachLoaderListeners(); // Re-bind to new buttons
            })
            .catch(() => {
                alert('Failed to load vacancies.');
                loader_?.classList.add('hidden');
            });
    }

    document.getElementById('statusFilter')?.addEventListener('change', fetchVacancies);
    document.getElementById('jobFilter')?.addEventListener('change', fetchVacancies);
    document.getElementById('sortFilter')?.addEventListener('change', fetchVacancies);
</script>
@endpush
@endsection
