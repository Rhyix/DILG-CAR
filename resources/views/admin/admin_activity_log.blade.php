
@extends('layout.admin')
@section('title', 'Admin Activity Log')
@section('content')

<main class="w-full h-[calc(98vh-6rem)] flex flex-col space-y-6 font-montserrat overflow-hidden -mt-6" x-data="logTable()">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Header Section -->
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Activity Log</span>
        </h1>
    </section>

    <!-- Filters -->
    <section class="px-6 py-4 rounded-xl flex items-center justify-between gap-4 overflow-x-auto">
        <form method="GET" class="flex items-center gap-2 flex-nowrap">
            <!-- Search Input -->
            <div class="relative w-[325px]">
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
                    class="pl-10 pr-4 py-1.5 rounded-md border-2 border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1 w-full"
                />
            </div>

            <!-- Sort Order -->
            <select x-model="sortOrder" @change="fetchLogs"
                class="border-2 border-[#0D2B70] rounded-md px-4 py-2 text-sm font-semibold text-[#0D2B70]">
                <option value="desc">LATEST</option>
                <option value="asc">OLDEST</option>
            </select>

            <!-- Admin Name -->
            <select x-model="adminName" @change="fetchLogs"
                class="border-2 border-[#0D2B70] rounded-md px-4 py-2 text-sm font-semibold text-[#0D2B70]">
                <option value="">All Admins</option>
                @foreach ($adminNames as $name)
                    <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
            </select>

            <!-- Section Filter -->
            <select x-model="section" @change="fetchLogs"
                class="border-2 border-[#0D2B70] rounded-md px-4 py-2 text-sm font-semibold text-[#0D2B70]">
                <option value="">All Sections</option>
                @foreach ($sections as $sec)
                    <option value="{{ $sec }}">{{ $sec }}</option>
                @endforeach
            </select>

            <!-- Date Range -->
            <input id="dateRange" x-model="dateRange" @change="fetchLogs" type="text"
                placeholder="Select Date Range"
                class="border-2 border-[#0D2B70] rounded-md px-4 py-2 text-sm font-semibold text-[#0D2B70]" />

        </form>


        <!-- Export Button -->
        <div class="shrink-0">
            @include('partials.alerts_template', [
                'id' => 'exportAll',
                'showTrigger' => true,
                'triggerText' => 'Export Log',
                'triggerClass' => 'font-semibold flex items-center px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70]  transition whitespace-nowrap
                                 hover:text-white hover:shadow-md border border-[#0D2B70]',
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
<section class="flex-1 flex flex-col min-h-0">
    <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
        <div class="flex-1 overflow-auto">
            <table class="w-full text-left border-collapse">
                <!-- Table Header -->
                <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                    <tr>
                        <th class="py-4 px-6 font-normal">Timestamp</th>
                        <th class="py-4 px-6 font-normal">User</th>
                        <th class="py-4 px-6 font-normal">Role</th>
                        <th class="py-4 px-6 font-normal">Section</th>
                        <th class="py-4 px-6 font-normal">Description</th>
                        <th class="py-4 px-6 font-normal text-center">Actions</th>
                    </tr>
                </thead>
                <!-- Table Body -->
                <tbody class="divide-y divide-[#0D2B70]">
                    <template x-for="log in paginatedLogs" :key="log.id">
                        <tr class="bg-white text-[#0D2B70]">
                            <td class="py-4 px-6" x-text="log.timestamp"></td>
                            <td class="py-4 px-6 truncate" x-text="log.admin_name"></td>
                            <td class="py-4 px-6" x-text="log.role"></td>
                            <td class="py-4 px-6" x-text="log.section"></td>
                            <td class="py-4 px-6 text-left whitespace-normal break-words max-w-[420px]" x-html="log.description_html"></td>
                            <td class="py-4 px-6 text-center">
                                <button @click="viewLog(log.id)" class="use-loader font-semibold px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70] transition whitespace-nowrap hover:text-white hover:shadow-md border border-[#0D2B70]">
                                    View
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
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
