@extends('layout.admin')
@section('title', 'Admin Activity Log')
@section('content')

<main class="w-full space-y-6 font-montserrat -mt-6" x-data="logTable()">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Header -->
    <section class="flex items-center space-x-4 mb-4">
        <h1 class="w-full flex items-center bg-[#0D2B70] text-white rounded-xl text-2xl font-extrabold px-8 py-3 tracking-wide select-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9 fill-white mr-3" viewBox="0 0 26 26">
            <path d="M 7 0 C 4.796875 0 3 1.796875 3 4 L 3 22 C 3 24.203125 4.796875 26 7 26 L 19 26 C 21.203125 26 23 24.203125 23 22 L 23 8 C 23 6.9375 22.027344 5.929688 20.28125 4.21875 C 20.039063 3.980469 19.777344 3.714844 19.53125 3.46875 C 19.285156 3.222656 19.019531 2.992188 18.78125 2.75 C 17.070313 1.003906 16.0625 0 15 0 Z M 7 2 L 14.28125 2 C 15.003906 2.183594 15 3.050781 15 3.9375 L 15 7 C 15 7.550781 15.449219 8 16 8 L 19 8 C 19.996094 8 21 8.003906 21 9 L 21 22 C 21 23.105469 20.105469 24 19 24 L 7 24 C 5.894531 24 5 23.105469 5 22 L 5 4 C 5 2.894531 5.894531 2 7 2 Z M 7.8125 10 C 7.261719 10.050781 6.855469 10.542969 6.90625 11.09375 C 6.957031 11.644531 7.449219 12.050781 8 12 L 18 12 C 18.359375 12.003906 18.695313 11.816406 18.878906 11.503906 C 19.058594 11.191406 19.058594 10.808594 18.878906 10.496094 C 18.695313 10.183594 18.359375 9.996094 18 10 L 8 10 C 7.96875 10 7.9375 10 7.90625 10 C 7.875 10 7.84375 10 7.8125 10 Z M 7.8125 14 C 7.261719 14.050781 6.855469 14.542969 6.90625 15.09375 C 6.957031 15.644531 7.449219 16.050781 8 16 L 16 16 C 16.359375 16.003906 16.695313 15.816406 16.878906 15.503906 C 17.058594 15.191406 17.058594 14.808594 16.878906 14.496094 C 16.695313 14.183594 16.359375 13.996094 16 14 L 8 14 C 7.96875 14 7.9375 14 7.90625 14 C 7.875 14 7.84375 14 7.8125 14 Z M 7.8125 18 C 7.261719 18.050781 6.855469 18.542969 6.90625 19.09375 C 6.957031 19.644531 7.449219 20.050781 8 20 L 18 20 C 18.359375 20.003906 18.695313 19.816406 18.878906 19.503906 C 19.058594 19.191406 19.058594 18.808594 18.878906 18.496094 C 18.695313 18.183594 18.359375 17.996094 18 18 L 8 18 C 7.96875 18 7.9375 18 7.90625 18 C 7.875 18 7.84375 18 7.8125 18 Z"></path>
            </svg>
            Admin Activity Log
        </h1>
    </section>

    <!-- Filters -->
    
    <section class="bg-white px-6 py-4 rounded-xl shadow flex items-center justify-between gap-4 overflow-x-auto">
        <form method="GET" class="flex items-center gap-2 flex-nowrap">
            <!-- Search Input -->
            <div class="relative w-[200px]">
                <svg
                    class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                    xmlns="http://www.w3.org/2000/svg"
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
                    type="search"
                    placeholder="Search..."
                    x-model="search"
                    @input="onInputChange"
                    class="pl-10 pr-4 py-1.5 rounded-full border-2 border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1 w-full"
                />
            </div>

            <!-- Sort Order -->
            <select x-model="sortOrder" @change="fetchLogs"
                class="border-2 border-[#0D2B70] rounded-full px-4 py-2 text-sm font-semibold text-[#0D2B70]">
                <option value="desc">LATEST</option>
                <option value="asc">OLDEST</option>
            </select>

            <!-- Admin Name -->
            <select x-model="adminName" @change="fetchLogs"
                class="border-2 border-[#0D2B70] rounded-full px-4 py-2 text-sm font-semibold text-[#0D2B70]">
                <option value="">All Admins</option>
                @foreach ($adminNames as $name)
                    <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
            </select>

            <!-- Section Filter -->
            <select x-model="section" @change="fetchLogs"
                class="border-2 border-[#0D2B70] rounded-full px-4 py-2 text-sm font-semibold text-[#0D2B70]">
                <option value="">All Sections</option>
                @foreach ($sections as $sec)
                    <option value="{{ $sec }}">{{ $sec }}</option>
                @endforeach
            </select>

            <!-- Date Range -->
            <input id="dateRange" x-model="dateRange" @change="fetchLogs" type="text"
                placeholder="Select Date Range"
                class="border-2 border-[#0D2B70] rounded-full px-4 py-2 text-sm font-semibold text-[#0D2B70]" />

        </form>


        <!-- Export Button -->
        <div class="shrink-0">
            @include('partials.alerts_template', [
                'id' => 'exportAll',
                'showTrigger' => true,
                'triggerText' => 'Export Log',
                'triggerClass' => 'bg-[#0D2B70] hover:bg-blue-400 transition text-white font-semibold rounded-full flex items-center gap-2 px-5 py-2',
                'title' => 'Export Confirmation',
                'message' => 'Are you sure you want to export All activities?',
                'showCancel' => true,
                'cancelText' => 'No, Cancel',
                'okText' => 'Yes, Export',
                'okAction' => "window.location.href='" . route('exportActivities') . "'",
            ])
        </div>
    </section>
    <!--
        <form class="bg-white px-6 py-4 rounded-xl shadow flex items-center justify-start gap-4 overflow-x-auto">
            <input type="checkbox" id="selectAll"
                x-model="allSelected"
                class="peer appearance-none w-5 h-5 border-2 border-[#0D2B70] bg-white checked:bg-[#0D2B70] checked:border-[#0D2B70] focus:ring-2 focus:ring-offset-1 focus:ring-[#0D2B70] transition">
            <label for="selectAll" class="text-[#0D2B70] font-semibold cursor-pointer">
                Select All
            </label>
            <input type="checkbox" id="selectAll"
                x-model="allSelected"
                class="peer appearance-none w-5 h-5 border-2 border-[#0D2B70] bg-white checked:bg-[#0D2B70] checked:border-[#0D2B70] focus:ring-2 focus:ring-offset-1 focus:ring-[#0D2B70] transition">
            <label for="selectAll" class="text-[#0D2B70] font-semibold cursor-pointer">
                view
            </label>
            <input type="checkbox" id="selectAll"
                x-model="allSelected"
                class="peer appearance-none w-5 h-5 border-2 border-[#0D2B70] bg-white checked:bg-[#0D2B70] checked:border-[#0D2B70] focus:ring-2 focus:ring-offset-1 focus:ring-[#0D2B70] transition">
            <label for="selectAll" class="text-[#0D2B70] font-semibold cursor-pointer">
                export
            </label>
            <input type="checkbox" id="selectAll"
                x-model="allSelected"
                class="peer appearance-none w-5 h-5 border-2 border-[#0D2B70] bg-white checked:bg-[#0D2B70] checked:border-[#0D2B70] focus:ring-2 focus:ring-offset-1 focus:ring-[#0D2B70] transition">
            <label for="selectAll" class="text-[#0D2B70] font-semibold cursor-pointer">
                submit
            </label>
            <input type="checkbox" id="selectAll"
                x-model="allSelected"
                class="peer appearance-none w-5 h-5 border-2 border-[#0D2B70] bg-white checked:bg-[#0D2B70] checked:border-[#0D2B70] focus:ring-2 focus:ring-offset-1 focus:ring-[#0D2B70] transition">
            <label for="selectAll" class="text-[#0D2B70] font-semibold cursor-pointer">
                create
            </label>
            <input type="checkbox" id="selectAll"
                x-model="allSelected"
                class="peer appearance-none w-5 h-5 border-2 border-[#0D2B70] bg-white checked:bg-[#0D2B70] checked:border-[#0D2B70] focus:ring-2 focus:ring-offset-1 focus:ring-[#0D2B70] transition">
            <label for="selectAll" class="text-[#0D2B70] font-semibold cursor-pointer">
                edit
            </label>
        </form>
    -->
    <!-- Entry Count Info -->
    <div x-show="logsData.length > 0">
        Showing <span x-text="startEntry"></span>–<span x-text="endEntry"></span> out of <span x-text="logsData.length"></span> entries
    </div>
    <!-- Activity Table -->
    <section class="space-y-1">
        <!-- Table Header -->
        <div class="grid grid-cols-12 bg-[#0D2B70] text-white font-bold rounded-xl py-4 px-6 select-none items-center text-sm">
            <div class="col-span-2">TIMESTAMP</div>
            <div class="col-span-1">ADMIN</div>
            <div class="col-span-2">SECTION</div>
            <div class="col-span-3">DESCRIPTION</div>
            <div class="col-span-3">TARGET</div>
            <div class="col-span-1 text-center">ACTION</div>
        </div>

        <!-- Table Body -->
        <template x-for="log in paginatedLogs" :key="log.id">
            <div class="grid grid-cols-12 bg-white rounded-xl px-6 py-4 items-center text-sm font-semibold text-[#0D2B70] border-t border-gray-100">
                <div class="col-span-2" x-text="log.timestamp"></div>
                <div class="col-span-1 truncate" x-text="log.admin_name"></div>
                <div class="col-span-2" x-text="log.section"></div>
                <div class="col-span-3 truncate" x-text="log.description"></div>
                <div class="col-span-3 truncate" x-text="log.target"></div>
                <div class="col-span-1 flex justify-center">
                    <button @click="viewLog(log.id)"     class="bg-[#0D2B70] hover:bg-blue-400 transition text-white rounded-full px-4 py-1 flex items-center gap-1 text-xs" >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View
                    </button>
                </div>
            </div>
        </template>
    </section>

    <!-- Pagination -->
    <section class="flex items-center justify-center gap-6 pt-4">
        <button @click="prevPage" :disabled="currentPage === 1"
            class="w-9 h-9 rounded-full bg-gray-200 text-[#0D2B70] hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
            &lt;
        </button>

        <span class="text-lg text-gray-700 font-semibold">
            <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
        </span>

        <button @click="nextPage" :disabled="currentPage === totalPages"
            class="w-9 h-9 rounded-full bg-gray-200 text-[#0D2B70] hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
            &gt;
        </button>
    </section>

</main>

    @include('partials.loader')

<script>
    
    function logTable() {
        return {
            logsData: [],
            perPage: 50,
            currentPage: 1,
            search: '',
            adminName: '',
            section: '',
            dateRange: '',
            debounceTimer: 1,
            sortOrder: 'desc', // default

            selectedLogs: [],
            allSelected: false,

            toggleAll() {
                if (this.allSelected) {
                    this.selectedLogs = this.paginatedLogs.map(log => log.id);
                } else {
                    this.selectedLogs = [];
                }
            },

            init() {
                this.fetchLogs();
            },

            fetchLogs() {
                const params = new URLSearchParams({
                    search: this.search,
                    admin_name: this.adminName,
                    section: this.section,
                    date_range: this.dateRange,
                    sort: this.sortOrder,
                });

                fetch(`/admin/activity-log/data?${params}`)
                    .then(res => res.json())
                    .then(data => {
                        this.logsData = data;
                        this.currentPage = 1;
                    });
            },

            onInputChange() {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => this.fetchLogs(), 500);
            },

            get totalPages() {
                return Math.ceil(this.logsData.length / this.perPage) || 1;
            },

            get paginatedLogs() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.logsData.slice(start, start + this.perPage);
            },

            get startEntry() {
                return (this.currentPage - 1) * this.perPage + 1;
            },

            get endEntry() {
                return Math.min(this.currentPage * this.perPage, this.logsData.length);
            },


            nextPage() {
                if (this.currentPage < this.totalPages) this.currentPage++;
            },

            prevPage() {
                if (this.currentPage > 1) this.currentPage--;
            },

            viewLog(id) {
                const log = this.logsData.find(log => log.id === id);
                if (!log) {
                    alert("Log not found.");
                    return;
                }

                const section = log.section.toLowerCase();

                // Normalize and redirect based on known sections
                if (section.includes('vacancies') || section.includes('job vacancy')) {
                    if(log.subject.vacancy_id) {
                        window.location.href = `/admin/vacancies/${log.subject.vacancy_id}/edit`;
                    } else {
                        window.location.href = '/admin/vacancies_management';
                    }
                } else if (section.includes('system')) {
                    window.location.href = '/admin/admin_account_management';
                } else if (section.includes('exam')) {
                    window.location.href = '/admin/exam_management';
                } else if (section.includes('activity')) {
                    window.location.href = '/admin/activity_log';
                } else if (section.includes('application')) {
                    if(log.subject.id && log.vacancy_id !== null) {
                        window.location.href = `/admin/applicant_status/${log.subject.id}/${log.vacancy_id}`;
                    }else if(log.subject.vacancy_id) {
                        window.location.href = `/admin/applicants/${log.subject.vacancy_id}`;
                    }
                     else {
                        window.location.href = '/admin/applications_list';
                    }
                    
                } else {
                    alert("No redirect rule for section: " + log.section);
                }
            }
        }
    }

    flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: function () {
            document.querySelector('#dateRange').dispatchEvent(new Event('change'));
        }
    });
</script>

@endsection
