@extends('layout.admin')
@section('title', 'DILG - Dashboard Admin')

@section('content')
    <div class="flex flex-col h-full gap-3 md:gap-4 overflow-hidden">

        <!-- Welcome Section -->
        <section class="shrink-0">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1 sm:gap-2">
                <p class="text-sm sm:text-base md:text-lg font-normal text-gray-800 font-montserrat">Welcome!</p>
                <h1
                    class="text-sm sm:text-base md:text-lg font-bold text-[#002C76] uppercase font-montserrat tracking-wide">
                    {{ auth('admin')->user()->name ?? 'Admin' }}
                </h1>
            </div>
        </section>

        <!-- Key Metrics Grid -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 shrink-0">
            <!-- Available Vacancies -->
            <div
                class="cursor-pointer group relative overflow-hidden bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                <div class="flex justify-between items-start z-10 relative h-full">
                    <!-- Content -->
                    <div class="flex flex-col justify-center h-full space-y-1">
                        <span class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-wider">AVAILABLE
                            VACANCIES</span>

                        <div class="flex items-center">
                            <!-- Total Count -->
                            <div class="flex flex-col items-start">
                                <span
                                    class="font-extrabold text-2xl sm:text-3xl md:text-4xl text-[#002C76] font-montserrat">{{ $openVacancyCount }}</span>
                                <span
                                    class="text-[9px] sm:text-[10px] text-gray-600 uppercase tracking-wider mt-1">POSITIONS</span>
                            </div>

                            <!-- Vertical Divider -->
                            <div class="h-10 sm:h-12 w-0.5 bg-[#002C76] mx-2 sm:mx-4"></div>

                            <!-- Breakdown -->
                            <div class="flex flex-col justify-center gap-1">
                                <div class="text-xs sm:text-sm text-gray-800 font-montserrat">
                                    <span class="font-bold">{{ $cosVacancyCount }}</span> <span
                                        class="hidden sm:inline">Contract of Service</span><span
                                        class="sm:hidden">COS</span>
                                </div>
                                <div class="text-xs sm:text-sm text-gray-800 font-montserrat">
                                    <span class="font-bold">{{ $plantillaVacancyCount }}</span> Plantilla
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Icon (Briefcase) -->
                    <div
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-50 group-hover:bg-[#002C76] transition-colors duration-300">
                        <svg class="w-5 h-5 text-[#002C76] transition-colors duration-300 group-hover:text-white"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 7h-4a2 2 0 01-2-2V4H10v1a2 2 0 01-2 2H4v11a2 2 0 002 2h12a2 2 0 002-2V7z" />
                        </svg>
                    </div>
                </div>
                <!-- Decorative Background Circle -->
                <div
                    class="absolute bottom-0 right-0 w-20 h-20 bg-blue-50 rounded-full -mr-6 -mb-6 opacity-20 group-hover:scale-150 transition-transform duration-500 ease-out">
                </div>
            </div>

            <!-- Reviewed Applications -->
            <div
                class="cursor-pointer group relative overflow-hidden bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                <div class="flex justify-between items-start z-10 relative h-full">
                    <div class="flex flex-col justify-center h-full space-y-1">
                        <span class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-wider">Reviewed
                            Applications</span>
                        <span
                            class="font-extrabold text-2xl sm:text-3xl md:text-4xl text-[#002C76] font-montserrat">{{ $reviewedApplicationsCount }}</span>
                    </div>
                    <div
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-50 group-hover:bg-[#002C76] transition-colors duration-300">
                        <svg class="w-5 h-5 text-[#002C76] group-hover:text-white transition-colors duration-300"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5h6m-6 4h6m-7 4l2 2 4-4M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                        </svg>
                    </div>
                </div>
                <div
                    class="absolute bottom-0 right-0 w-20 h-20 bg-blue-50 rounded-full -mr-6 -mb-6 opacity-20 group-hover:scale-150 transition-transform duration-500 ease-out">
                </div>
            </div>

            <!-- Upcoming Exams -->
            <div
                class="cursor-pointer group relative overflow-hidden bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                <div class="flex justify-between items-start z-10 relative h-full">
                    <div class="flex flex-col justify-center h-full space-y-1">
                        <span class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-wider">Upcoming
                            Exams</span>
                        <span
                            class="font-extrabold text-2xl sm:text-3xl md:text-4xl text-[#002C76] font-montserrat">{{ $upcomingExamsCount }}</span>
                    </div>
                    <div
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-50 group-hover:bg-[#002C76] transition-colors duration-300">
                        <svg class="w-5 h-5 text-[#002C76] group-hover:text-white transition-colors duration-300"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10m-12 9h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div
                    class="absolute bottom-0 right-0 w-20 h-20 bg-blue-50 rounded-full -mr-6 -mb-6 opacity-20 group-hover:scale-150 transition-transform duration-500 ease-out">
                </div>
            </div>

            <!-- System Users -->
            <div
                class="cursor-pointer group relative overflow-hidden bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                <div class="flex justify-between items-start z-10 relative h-full">
                    <div class="flex flex-col justify-center h-full space-y-1">
                        <span class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-wider">System
                            Users</span>
                        <span
                            class="font-extrabold text-2xl sm:text-3xl md:text-4xl text-[#002C76] font-montserrat">{{ $systemUsersCount }}</span>
                    </div>
                    <div
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-50 group-hover:bg-[#002C76] transition-colors duration-300">
                        <svg class="w-5 h-5 text-[#002C76] group-hover:text-white transition-colors duration-300"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-1a4 4 0 00-4-4h-1M9 20H4v-1a4 4 0 014-4h1m6-5a4 4 0 10-8 0 4 4 0 008 0zm6 4a3 3 0 10-6 0 3 3 0 006 0z" />
                        </svg>
                    </div>
                </div>
                <div
                    class="absolute bottom-0 right-0 w-20 h-20 bg-blue-50 rounded-full -mr-6 -mb-6 opacity-20 group-hover:scale-150 transition-transform duration-500 ease-out">
                </div>
            </div>
        </section>

        <!-- Main Analytics Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 md:gap-4 flex-1 min-h-0 overflow-hidden">

            <!-- Left Column: Line Chart & Bottom Widgets -->
            <div class="lg:col-span-2 flex flex-col gap-3 md:gap-4 h-full min-h-0">

                <!-- Monthly Applications Chart -->
                <div
                    class="bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-shadow duration-300 flex-1 min-h-0 flex flex-col">
                    <div class="flex items-center justify-between gap-2 mb-2 shrink-0">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-blue-50 flex items-center justify-center">
                                <i class="fas fa-chart-line text-[#002C76] text-xs sm:text-sm"></i>
                            </div>
                            <h2 class="text-sm sm:text-base font-bold text-[#002C76] font-montserrat">Monthly Applications
                            </h2>
                        </div>
                        <div id="chartLoadingIndicator" class="hidden">
                            <div class="animate-pulse flex items-center gap-2">
                                <div class="h-2 w-2 bg-[#002C76] rounded-full"></div>
                                <div class="h-2 w-2 bg-[#002C76] rounded-full animation-delay-200"></div>
                                <div class="h-2 w-2 bg-[#002C76] rounded-full animation-delay-400"></div>
                            </div>
                        </div>
                    </div>
                    <div class="relative w-full flex-1 min-h-0">
                        <canvas id="monthlyApplicantsLineChart"></canvas>
                    </div>
                    <div id="noDataMessage" class="hidden flex-1 flex items-center justify-center">
                        <p class="text-gray-500 text-sm">No application data available for
                            {{ $selectedYear ?? now()->year }}
                        </p>
                    </div>
                </div>

                <!-- Bottom Widgets Grid -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 h-auto lg:h-[40%] min-h-[160px] lg:min-h-[180px] shrink-0">
                    <!-- Applicants Status (Pie Chart) -->
                    <div
                        class="bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col h-full overflow-hidden">
                        <div class="flex items-center gap-2 mb-2 shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-blue-50 flex items-center justify-center">
                                <i class="fas fa-users text-[#002C76] text-xs sm:text-sm"></i>
                            </div>
                            <h2 class="text-sm sm:text-base font-bold text-[#002C76] font-montserrat">Applicants Status</h2>
                        </div>
                        <div class="flex-1 flex items-center justify-center relative min-h-0 overflow-hidden">
                            <div
                                class="relative w-full h-full max-h-[140px] sm:max-h-[160px] flex items-center justify-center">
                                <canvas id="applicantsPie"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Examinations (Calendar) -->
                    <div
                        class="bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col h-full overflow-hidden">
                        <div class="flex items-center gap-2 mb-2 shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-blue-50 flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-[#002C76] text-xs sm:text-sm"></i>
                            </div>
                            <h2 class="text-sm sm:text-base font-bold text-[#002C76] font-montserrat">Examination Calendar
                            </h2>
                        </div>
                        <div class="flex-1 flex justify-center items-center overflow-hidden">
                            <input id="examCalendar" class="hidden" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Job Vacancies Ratio -->
            <div class="lg:col-span-1 h-full min-h-0">
                <div
                    class="bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-shadow duration-300 h-full flex flex-col min-h-0">
                    <div class="flex items-center gap-2 mb-3 md:mb-4 shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-blue-50 flex items-center justify-center">
                            <i class="fas fa-briefcase text-[#002C76] text-xs sm:text-sm"></i>
                        </div>
                        <h2 class="text-sm sm:text-base font-bold text-[#002C76] font-montserrat">Job Vacancies Ratio</h2>
                    </div>
                    <div class="flex-1 relative min-h-0">
                        <canvas id="jobBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <!-- Libraries -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://kit.fontawesome.com/a076d05399.js"></script>

        <style>
            /* Calendar Customization */
            .flatpickr-calendar {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
            }

            .flatpickr-days {
                width: 100% !important;
            }

            .dayContainer {
                width: 100% !important;
                min-width: unset !important;
                max-width: unset !important;
            }

            .flatpickr-day {
                max-width: unset !important;
                height: 38px !important;
                line-height: 38px !important;
                color: #374151 !important;
                /* text-gray-700 to ensure visibility */
            }

            .flatpickr-day.flatpickr-disabled,
            .flatpickr-day.flatpickr-disabled:hover {
                color: #9ca3af !important;
                /* text-gray-400 for disabled dates */
                background: transparent !important;
                border-color: transparent !important;
                cursor: default !important;
            }

            .flatpickr-day.selected,
            .flatpickr-day.startRange,
            .flatpickr-day.endRange,
            .flatpickr-day.selected.inRange,
            .flatpickr-day.startRange.inRange,
            .flatpickr-day.endRange.inRange,
            .flatpickr-day.selected:focus,
            .flatpickr-day.startRange:focus,
            .flatpickr-day.endRange:focus,
            .flatpickr-day.selected:hover,
            .flatpickr-day.startRange:hover,
            .flatpickr-day.endRange:hover,
            .flatpickr-day.selected.prevMonthDay,
            .flatpickr-day.startRange.prevMonthDay,
            .flatpickr-day.endRange.prevMonthDay,
            .flatpickr-day.selected.nextMonthDay,
            .flatpickr-day.startRange.nextMonthDay,
            .flatpickr-day.endRange.nextMonthDay {
                background: #002C76 !important;
                border-color: #002C76 !important;
                color: #fff !important;
                font-weight: bold !important;
            }

            .flatpickr-day.event-day {
                font-weight: bold !important;
                color: #002C76 !important;
                border: 1px solid #002C76 !important;
                background-color: #f0f9ff !important;
            }

            .flatpickr-current-month .flatpickr-monthDropdown-months .flatpickr-monthDropdown-month {
                background-color: white !important;
                color: black !important;
            }

            span.flatpickr-weekday {
                background: transparent !important;
                color: #002C76 !important;
                font-weight: bold !important;
            }
        </style>

        <script>
            document.addEventListener("DOMContentLoaded", function () {

                // --- Monthly Applications Chart ---
                const ctxLine = document.getElementById('monthlyApplicantsLineChart');
                const noDataMessage = document.getElementById('noDataMessage');
                const chartLoadingIndicator = document.getElementById('chartLoadingIndicator');

                if (ctxLine) {
                    // Get data from backend
                    const chartLabels = {!! json_encode($chartLabels ?? []) !!};
                    const chartData = {!! json_encode($chartData ?? []) !!};

                    // Debug logging
                    console.log('Chart Labels:', chartLabels);
                    console.log('Chart Data:', chartData);

                    // Check if there's any data
                    const hasData = chartData && Array.isArray(chartData) && chartData.some(value => value > 0);

                    console.log('Has Data:', hasData);

                    if (!hasData) {
                        // Hide chart and show no data message
                        ctxLine.style.display = 'none';
                        if (noDataMessage) {
                            noDataMessage.classList.remove('hidden');
                        }
                    } else {
                        // Show chart and hide no data message
                        ctxLine.style.display = 'block';
                        if (noDataMessage) {
                            noDataMessage.classList.add('hidden');
                        }

                        new Chart(ctxLine, {
                            type: 'line',
                            data: {
                                labels: chartLabels,
                                datasets: [{
                                    label: 'Applications',
                                    data: chartData,
                                    borderColor: '#002C76',
                                    backgroundColor: (context) => {
                                        const ctx = context.chart.ctx;
                                        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                                        gradient.addColorStop(0, 'rgba(0, 44, 118, 0.2)');
                                        gradient.addColorStop(1, 'rgba(0, 44, 118, 0)');
                                        return gradient;
                                    },
                                    fill: true,
                                    tension: 0.4,
                                    borderWidth: 2,
                                    pointBackgroundColor: '#fff',
                                    pointBorderColor: '#002C76',
                                    pointBorderWidth: 2,
                                    pointRadius: 3,
                                    pointHoverRadius: 5
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: '#002C76',
                                        titleFont: { family: 'Montserrat', size: 12 },
                                        bodyFont: { family: 'Montserrat', size: 11 },
                                        padding: 8,
                                        cornerRadius: 6,
                                        displayColors: false,
                                        callbacks: {
                                            label: function (context) {
                                                return 'Applications: ' + context.parsed.y;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        ticks: {
                                            font: { family: 'Montserrat', size: 10 },
                                            maxRotation: 0,
                                            autoSkip: true,
                                            maxTicksLimit: 12
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        border: { dash: [4, 4] },
                                        grid: { color: '#f3f4f6' },
                                        ticks: {
                                            font: { family: 'Montserrat', size: 10 },
                                            callback: function (value) {
                                                if (Number.isInteger(value)) {
                                                    return value;
                                                }
                                            }
                                        }
                                    }
                                },
                                interaction: {
                                    intersect: false,
                                    mode: 'index'
                                }
                            }
                        });
                    }
                }

                // --- Applicants Pie Chart ---
                const ctxPie = document.getElementById('applicantsPie');
                if (ctxPie) {
                    const reviewedCount = {{ $reviewedApplicationsCount ?? 0 }};
                    const ongoingCount = {{ $onGoingApplicationsCount ?? 0 }};

                    new Chart(ctxPie, {
                        type: 'doughnut',
                        data: {
                            labels: ['Reviewed', 'Ongoing'],
                            datasets: [{
                                data: [reviewedCount, ongoingCount],
                                backgroundColor: ['#002C76', '#9CA3AF'],
                                borderWidth: 0,
                                hoverOffset: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: { family: 'Montserrat', size: 10 },
                                        usePointStyle: true,
                                        padding: 12,
                                        boxWidth: 12
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#002C76',
                                    titleFont: { family: 'Montserrat', size: 11 },
                                    bodyFont: { family: 'Montserrat', size: 10 },
                                    padding: 8,
                                    cornerRadius: 6
                                }
                            }
                        }
                    });
                }

                // --- Job Vacancies Ratio Bar Chart ---
                const ctxBar = document.getElementById('jobBarChart');
                if (ctxBar) {
                    const cosCount = {{ $cosVacancyCount ?? 0 }};
                    const plantillaCount = {{ $plantillaVacancyCount ?? 0 }};

                    new Chart(ctxBar, {
                        type: 'bar',
                        data: {
                            labels: ['COS', 'Plantilla'],
                            datasets: [{
                                label: 'Vacancies',
                                data: [cosCount, plantillaCount],
                                backgroundColor: ['#002C76', '#9CA3AF'],
                                borderRadius: 6,
                                barThickness: 40,
                                maxBarThickness: 60
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#002C76',
                                    titleFont: { family: 'Montserrat', size: 11 },
                                    bodyFont: { family: 'Montserrat', size: 10 },
                                    padding: 8,
                                    cornerRadius: 6,
                                    callbacks: {
                                        label: function (context) {
                                            return 'Vacancies: ' + context.parsed.y;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f3f4f6' },
                                    ticks: {
                                        font: { family: 'Montserrat', size: 10 },
                                        callback: function (value) {
                                            if (Number.isInteger(value)) {
                                                return value;
                                            }
                                        }
                                    }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { family: 'Montserrat', size: 11 } }
                                }
                            }
                        }
                    });
                }

                // --- Calendar ---
                const examDates = @json($upcomingExams->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))->unique()->values());

                flatpickr("#examCalendar", {
                    inline: true,
                    dateFormat: "Y-m-d",
                    enable: examDates.length > 0 ? examDates : [],
                    disableMobile: true,
                    static: true,
                    locale: {
                        firstDayOfWeek: 1 // Start week on Monday
                    },
                    onDayCreate: function (dObj, dStr, fp, dayElem) {
                        // Improve accessibility and styling
                        if (examDates.includes(dStr)) {
                            dayElem.classList.add('event-day');
                            dayElem.setAttribute('title', 'Exam Scheduled');
                        }
                    }
                });

            });
        </script>
    @endpush
@endsection