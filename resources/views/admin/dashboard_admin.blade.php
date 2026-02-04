@extends('layout.admin')
@section('title', 'DILG - Dashboard Admin')

@section('content')
<main class="space-y-6">

    <!-- Welcome Back -->
    <section>
        <p class="text-xl font-normal text-black font-montserrat">Welcome back,</p>
        <h1 class="text-3xl font-extrabold text-black uppercase font-montserrat">
            {{ auth('admin')->user()->name ?? 'Admin' }}
        </h1>
    </section>

    <!-- OLD STATS SUMMARY -->
    <!-- Stats Summary
    <section class="border border-blue-700 rounded-2xl max-w-full flex divide-x divide-blue-700 bg-white select-none"
        style="box-shadow: 0 3px 6px rgb(29 78 216 / 0.24);">
        @php
            $stats = [
                ['url' => '/admin/vacancies_management', 'icon' => 'briefcase', 'label' => 'Open Vacancies', 'count' => $openVacancyCount],
                ['url' => '/admin/applications_list', 'icon' => 'folder-closed', 'label' => 'Reviewed Applications', 'count' => $reviewedApplicationsCount],
                ['url' => '/admin/exam_management', 'icon' => 'file-signature', 'label' => 'Upcoming Exams', 'count' => $upcomingExamsCount],
                ['url' => '/admin/admin_account_management', 'icon' => 'user', 'label' => 'System Users', 'count' => $systemUsersCount],
            ];
        @endphp

        @foreach ($stats as $stat)
        <a href="{{ $stat['url'] }}" class="flex-1 block use-loader">
            <div class="flex flex-col items-center p-4 space-y-1 hover:bg-blue-50">
                <div class="flex justify-center items-center rounded-full bg-blue-300 w-10 h-10">
                    <i class="fa-solid fa-{{ $stat['icon'] }} text-blue-700 text-lg"></i>
                </div>
                <span class="font-extrabold text-xl font-montserrat">{{ $stat['count'] }}</span>
                <span class="text-sm font-semibold text-gray-400 font-montserrat">{{ $stat['label'] }}</span>
            </div>
        </a>
        @endforeach        
    </section> -->

    <!--NEW SUGGESTION-->
    <div class="grid grid-cols-4 gap-6">
        <!-- Open Vacancies -->
        <div
        class="cursor-pointer flex items-center justify-between border border-[#002C76] rounded-xl p-4 bg-white shadow-xl
                hover:shadow-xl hover:-translate-y-1 hover:bg-blue-50 hover:scale-105
                transition-all duration-300 ease-out"
        >
            <div class="flex flex-col items-start space-y-1">
                <span class="text-[#313131]">OPEN VACANCIES</span>
                <span class="font-extrabold text-6xl font-montserrat">{{ $openVacancyCount }}</span>
            </div>
            <div class="w-16 h-16 flex items-center justify-center rounded-full bg-blue-100">
                <svg class="w-8 h-8 text-[#002C76]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20 7h-4a2 2 0 01-2-2V4H10v1a2 2 0 01-2 2H4v11a2 2 0 002 2h12a2 2 0 002-2V7z" />
                </svg>
            </div>
        </div>

        <!-- Reviewed Applications -->
        <div
        class="cursor-pointer flex items-center justify-between border border-[#002C76] rounded-xl p-4 bg-white shadow-xl
                hover:shadow-xl hover:-translate-y-1 hover:bg-blue-50 hover:scale-105
                transition-all duration-300 ease-out"
        >
            <div class="flex flex-col items-start space-y-1">
                <span class="text-[#313131]">REVIEWED APPLICATIONS</span>
                <span class="font-extrabold text-6xl font-montserrat">{{ $reviewedApplicationsCount }}</span>
            </div>
            <div class="w-16 h-16 flex items-center justify-center rounded-full bg-blue-100">
                <svg class="w-8 h-8 text-[#002C76]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5h6m-6 4h6m-7 4l2 2 4-4M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                </svg>
            </div>
        </div>

        <!-- Upcoming Exams -->
        <div
        class="cursor-pointer flex items-center justify-between border border-[#002C76] rounded-xl p-4 bg-white shadow-xl
                hover:shadow-xl hover:-translate-y-1 hover:bg-blue-50 hover:scale-105
                transition-all duration-300 ease-out"
        >
            <div class="flex flex-col items-start space-y-1">
                <span class="text-[#313131]">UPCOMING EXAMS</span>
                <span class="font-extrabold text-6xl font-montserrat">{{ $upcomingExamsCount }}</span>
            </div>
            <div class="w-16 h-16 flex items-center justify-center rounded-full bg-blue-100">
                <svg class="w-8 h-8 text-[#002C76]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10m-12 9h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- System Users -->
        <div
        class="cursor-pointer flex items-center justify-between border border-[#002C76] rounded-xl p-4 bg-white shadow-xl
                hover:shadow-xl hover:-translate-y-1 hover:bg-blue-50 hover:scale-105
                transition-all duration-300 ease-out"
        >
            <div class="flex flex-col items-start space-y-1">
                <span class="text-[#313131]">SYSTEM USERS</span>
                <span class="font-extrabold text-6xl font-montserrat">{{ $systemUsersCount }}</span>
            </div>
            <div class="w-16 h-16 flex items-center justify-center rounded-full bg-blue-100">
                <svg class="w-8 h-8 text-[#002C76]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-1a4 4 0 00-4-4h-1M9 20H4v-1a4 4 0 014-4h1m6-5a4 4 0 10-8 0 4 4 0 008 0zm6 4a3 3 0 10-6 0 3 3 0 006 0z" />
                </svg>
            </div>
        </div>
    </div>





    <!-- Job Vacancies + Exam Management Row -->
    <section class="flex flex-row gap-6 max-w-full">
        <!-- Job Vacancies -->
        <div class="flex-1 bg-blue-900 rounded-2xl p-6 text-white shadow-lg flex flex-col">
            <h2 class="font-extrabold text-2xl flex items-center gap-3 mb-5 font-montserrat">
                <i class="fa-solid fa-clipboard"></i> JOB VACANCIES
            </h2>

            <div class="text-lg font-light space-y-1 mb-6 max-w-md font-montserrat">
                @forelse ($openVacancies as $vacancy)
                    <p>{{ $vacancy->position_title }}</p>
                @empty
                    <p class="italic text-sm text-gray-300">No open vacancies.</p>
                @endforelse
            </div>

            <div class="mt-auto flex justify-end pt-6">
                <button onclick="window.location.href='/admin/vacancies_management'"
                    class="use-loader bg-red-700 hover:bg-red-800 transition font-montserrat font-semibold rounded-lg py-3 px-6 flex items-center space-x-2 shadow-md shadow-red-900/50">
                    <i class="fa-regular fa-eye"></i>
                    <span>Edit Job Vacancies</span>
                </button>
            </div>
        </div>

        <!-- Exam Management -->
        <div class="flex-1 bg-blue-900 rounded-2xl p-6 text-white shadow-lg flex flex-col">
            <h2 class="font-extrabold text-2xl flex items-center gap-3 mb-5 font-montserrat">
                <i class="fa-solid fa-file-pen"></i> EXAM MANAGEMENT
            </h2>

            <div class="flex flex-col text-white text-sm mr-4">
                <p class="font-bold uppercase mb-1 font-montserrat">Upcoming Exams</p>
                <ul class="space-y-3">
                    @forelse ($upcomingExams as $exam)
                        <li class="grid grid-cols-[minmax(300px,_auto)_160px_80px] items-center">
                            <div class="flex items-center">
                                <i class="fa-solid fa-paperclip"></i>
                                <strong class="ml-2 font-montserrat">
                                    {{ $exam->vacancy->position_title ?? 'Unknown Position' }}
                                </strong>
                            </div>
                            <div class="flex items-center">
                                <i class="fa-solid fa-calendar"></i>
                                <span class="ml-2 font-montserrat">
                                    {{ \Carbon\Carbon::parse($exam->date)->format('F j, Y') }}
                                </span>
                            </div>
                            <div class="flex items-center">
                                <i class="fa-solid fa-clock"></i>
                                <span class="ml-2 font-montserrat">
                                    {{ \Carbon\Carbon::parse($exam->time)->format('H:i') }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-white font-montserrat">No upcoming exams.</li>
                    @endforelse
                </ul>
            </div>

            <div class="mt-auto flex justify-end pt-6">
                <button onclick="window.location.href='/admin/exam_management'"
                    class="use-loader bg-red-700 hover:bg-red-800 transition font-semibold rounded-lg py-3 px-6 flex items-center space-x-2 shadow-md shadow-red-900/50">
                    <i class="fa-regular fa-file-lines"></i>
                    <span class="font-montserrat">Manage Exam</span>
                </button>
            </div>
        </div>
    </section>

    <!-- Applicants Over Time + Reviewed Applicants --> <!-- FIXED THE CONTAINER-->
    <section class="grid grid-cols-1 xl:grid-cols-2 gap-6 max-w-full h-[380px]">
        <!-- Chart Box -->
        <div class="bg-white border border-blue-700 rounded-2xl p-6 shadow-lg flex flex-col justify-between h-full">
            <div class="space-y-4">
                <h2 class="text-xl font-extrabold font-montserrat text-blue-900">Monthly Applicants</h2>
    
                <div class="relative h-[230px]">
                    <canvas id="applicantsChart" class="w-full h-full"></canvas>
                </div>
    
                <div class="flex justify-end mt-4">
                    <form method="GET" action="{{ route('dashboard_admin') }}" class="mb-4">
                        <label for="year" class="text-sm font-semibold mr-2 text-blue-900">Select Year:</label>
                        <select name="year" id="year" onchange="this.form.submit()" class="border border-blue-6700 rounded px-2 py-1">
                            @foreach ($years as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    
        <!-- Reviewed Applicants -->
        <div class="bg-white border border-blue-700 rounded-2xl p-6 shadow-md flex flex-col justify-between h-full">
            <div>
                <h3 class="text-lg font-extrabold font-montserrat mb-4 text-blue-900">REVIEWED APPLICANTS</h3>
                <div class="text-blue-900 space-y-2 text-sm font-montserrat overflow-y-auto max-h-[260px] pr-2">
                    @forelse ($reviewedApplications as $applicant)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-user text-blue-600"></i>
                            <span>{{ optional($applicant->personalInformation)->first_name ?? 'N/A' }} {{ optional($applicant->personalInformation)->surname ?? '' }}</span>
                        </div>
                    @empty
                        <p class="text-gray-500 italic">No reviewed applicants.</p>
                    @endforelse
                </div>
            </div>
            <div class="pt-6 flex justify-end">
                <a href="{{ route('applications_list') }}" class="use-loader bg-red-700 hover:bg-red-800 transition font-semibold text-white rounded-lg py-3 px-6 flex items-center space-x-2 shadow-md shadow-red-900/50">
                    <i class="fa-regular fa-file-lines text-base"></i>
                    <span class="font-montserrat text-sm">View Applicants</span>
                </a>
            </div>
        </div>
    </section>
    


<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let myChart;

    document.addEventListener("DOMContentLoaded", function () {
        const chartLabels = {!! $chartLabels !!};
        const chartData = {!! $chartData !!};

        const canvas = document.getElementById('applicantsChart');
        const ctx = canvas.getContext('2d');

        // Set an explicit height for the canvas to prevent collapse
        canvas.height = 230;

        myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Applicants in {{ $selectedYear }}',
                    data: chartData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(0, 44, 118, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });

        // Resize on window resize
        window.addEventListener('resize', () => {
            if (myChart) {
                myChart.resize();
                myChart.update();
            }
        });

        // Poll for container size changes (e.g., due to sidebar toggle)
        let lastWidth = canvas.offsetWidth;
        setInterval(() => {
            const newWidth = canvas.offsetWidth;
            if (newWidth !== lastWidth && newWidth > 0) {
                lastWidth = newWidth;
                if (myChart) {
                    myChart.resize();
                    myChart.update();
                }
            }
        }, 500);
    });
</script>
@include('partials.loader')



</main>
@endsection
