@extends('layout.app')
@section('title', 'DILG - DASHBOARD')

@section('content')

<style>
    .success-container {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 50;
    }
</style>
<style>
    #pdsDropdown {
        max-height: 240px;
        overflow-y: auto;
    }
    #pdsRightPane {
        display: none;
    }
    #pdsRightPane.show {
        display: block;
    }
    #pdsRightPane iframe {
        width: 100%;
        height: 100%;
        border: 0;
    }
    #pdsDropdownToggle [data-feather="chevron-down"] {
        display: none !important;
    }
    #pdsRightPane {
        position: relative;
    }
    #pdsPaneLoader {
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    #pdsRightPane.loading #pdsPaneLoader {
        display: flex;
    }
    .pane-spinner {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 4px solid #cbd5e1;
        border-top-color: #2563eb;
        animation: pane-spin 0.8s linear infinite;
    }
    @keyframes pane-spin {
        to { transform: rotate(360deg); }
    }
</style>

<main class="mt-7 sm:mt-0 flex-1 min-w-0 space-y-10 bg-[#F3F8FF] font-sans text-gray-900 overflow-x-hidden p-6" style="margin-top: 0">
    <!-- Welcome Section -->
    <section class="text-center sm:text-left">
        <div class="text-xl font-normal mb-1 font-montserrat">Welcome,</div>
        <h1 class="font-extrabold text-2xl sm:text-3xl tracking-tight font-montserrat">{{ Auth::user()->name }}</h1>
    </section>

    <section class="grid grid-cols-12 gap-6 w-full">

        <!-- My Job Applications -->
        <article class="col-span-12 sm:col-span-7 rounded-xl bg-white text-[#002C76] border-4 border-[#002C76] p-8 flex flex-col gap-4">
            <h2 class="text-base sm:text-2xl font-extrabold flex items-center gap-3 font-montserrat">
                <i class="w-5 h-5" data-feather="clipboard"></i> MY JOB APPLICATIONS
            </h2>
            <div class="text-sm sm:text-base font-normal leading-relaxed font-montserrat space-y-1">
                @forelse($applications->filter(fn($app) => strtolower($app->status) !== 'closed') as $application)
                    <p>{{ $application->vacancy->position_title ?? 'N/A' }}</p>
                @empty
                    <p>You have not applied to any vacancies yet.</p>
                @endforelse
            </div>
            <button onclick="window.location.href='{{ route('my_applications') }}'"
                class="use-loader mt-3 inline-flex items-center font-montserrat gap-2 rounded-full bg-green-600 text-white px-5 py-2 text-sm font-medium shadow-sm hover:bg-opacity-90 transition w-fit">
                <i data-feather="eye" class="w-4 h-4"></i> View Your Job Applications
            </button>
        </article>

        <!-- Deadline of Applications -->
        @php
            use Carbon\Carbon;

            $applicationsWithDeadlines = $applications
                ->filter(fn($app) => $app->deadline_date && $app->deadline_time && strtolower($app->status) !== 'closed')
                ->sortBy(fn($app) => Carbon::parse($app->deadline_date . ' ' . $app->deadline_time))
                ->take(3);
        @endphp

        <article class="col-span-12 sm:col-span-5 bg-white border-4 border-[#002C76] rounded-xl p-6 flex flex-col gap-4">
            <h2 class="text-base sm:text-xl font-extrabold flex items-center gap-3 font-montserrat text-[#C9282D]">
                <i class="w-5 h-5" data-feather="check-square"></i> DEADLINE OF APPLICATIONS
            </h2>

            @if ($applicationsWithDeadlines->isNotEmpty())
                @foreach ($applicationsWithDeadlines as $app)
                    @php
                        $deadline = Carbon::parse($app->deadline_date . ' ' . $app->deadline_time);
                        $isPastDeadline = now()->greaterThan($deadline);
                    @endphp
                    <div>
                        <p class="text-sm sm:text-base font-bold font-montserrat">
                            {{ $deadline->format('F d, Y') }} | {{ $deadline->format('h:i A') }}
                        </p>
                        <p class="uppercase text-xs sm:text-sm tracking-wide font-montserrat">
                            {{ $app->vacancy->position_title }}
                            @if ($isPastDeadline)
                                — <span class="text-red-700 font-semibold">Past Deadline</span>
                            @endif
                        </p>
                    </div>
                @endforeach
            @else
                <p class="text-sm text-gray-700 font-montserrat">You haven't applied to any vacancies with deadlines yet.</p>
            @endif
        </article>

        <!-- Job Vacancies -->
        <article class="col-span-12 sm:col-span-7 rounded-xl bg-white border-4 border-[#002C76] p-8 flex flex-col text-[#002C76] min-h-[360px]">
            <h2 class="text-base sm:text-2xl font-extrabold flex items-center gap-3 font-montserrat mb-2">
                <i class="w-5 h-5" data-feather="box"></i> JOB VACANCIES
            </h2>
            <div class="flex-1 text-sm sm:text-base font-normal leading-relaxed space-y-1 font-montserrat">
                @forelse ($vacancies as $vacancy)
                    <p>{{ $vacancy->position_title }}</p>
                @empty
                    <p>No open vacancies available at the moment.</p>
                @endforelse
            </div>
            <div class="mt-5">
                <button onclick="window.location.href='{{ route('job_vacancy') }}'"
                    class="use-loader inline-flex items-center gap-2 rounded-full font-montserrat bg-blue-600 text-white px-5 py-2 text-sm font-medium shadow-sm hover:bg-opacity-90 transition w-fit">
                    <i data-feather="search" class="w-4 h-4"></i> Browse All Job Vacancies
                </button>
            </div>
        </article>

        <!-- Personal Data Sheet -->
        <article class="col-span-12 sm:col-span-5 rounded-xl bg-white border-4 border-[#002C76] p-8 flex flex-col gap-4">
            <button type="button" id="pdsDropdownToggle" class="text-left text-base sm:text-3xl font-extrabold flex items-center justify-between gap-3 font-montserrat text-[#002C76]">
                <span class="flex items-center gap-3">
                    <i class="w-5 h-5" data-feather="file"></i> PERSONAL DATA SHEET
                </span>
                <i class="w-5 h-5" data-feather="chevron-down"></i>
            </button>
            <div id="pdsDropdown" class="hidden mt-2 bg-blue-50 rounded-lg p-2">
                <div class="space-y-1">
                    <a href="{{ route('display_c1', ['simple' => 1]) }}" class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition text-[#002C76] hover:text-white hover:bg-[#002C76]">
                        <i data-feather="user" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                        <span class="ml-3">Personal Information</span>
                    </a>
                    <a href="{{ route('display_c2', ['simple' => 1]) }}" class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition text-[#002C76] hover:text-white hover:bg-[#002C76]">
                        <i data-feather="briefcase" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                        <span class="ml-3">Work Experience</span>
                    </a>
                    <a href="{{ route('display_c3', ['simple' => 1]) }}" class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition text-[#002C76] hover:text-white hover:bg-[#002C76]">
                        <i data-feather="book-open" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                        <span class="ml-3">Learning & Development</span>
                    </a>
                    <a href="{{ route('display_c4', ['simple' => 1]) }}" class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition text-[#002C76] hover:text-white hover:bg-[#002C76]">
                        <i data-feather="info" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                        <span class="ml-3">Other Information</span>
                    </a>
                    <a href="{{ route('display_wes', ['simple' => 1]) }}" class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition text-[#002C76] hover:text-white hover:bg-[#002C76]">
                        <i data-feather="briefcase" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                        <span class="ml-3">Work Experience Sheet</span>
                    </a>
                    <a href="{{ route('display_c5', ['simple' => 1]) }}" class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition text-[#002C76] hover:text-white hover:bg-[#002C76]">
                        <i data-feather="upload" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                        <span class="ml-3">Upload PDF</span>
                    </a>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 h-2 rounded-full">
                <div class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: {{ $pdsProgress }}%"></div>
            </div>
            <p class="text-sm text-gray-600 font-montserrat">{{ $pdsProgress }}% PDS Completed</p>

            <!-- Status Info + Checklist -->
            <div class="text-sm font-montserrat space-y-3 bg-blue-50 p-4 rounded-lg text-[#002C76]">
                <p>
                    <strong>Status:</strong>
                    @if ((int) $pdsProgress == 100)
                        <span class="text-green-600">Completed</span>
                    @elseif ((int) $pdsProgress >= 50)
                        <span class="text-yellow-600">In Progress</span>
                    @else
                        <span class="text-red-600">Incomplete</span>
                    @endif
                </p>
                <p><strong>Last Updated:</strong> 
                    {{ Auth::user()->updated_at ? \Carbon\Carbon::parse(Auth::user()->updated_at)->format('F j, Y') : 'N/A' }}
                </p>


                <!-- Checklist -->
                <div class="pt-3 border-t border-blue-200 mt-3">
                    <p class="font-bold text-sm mb-2">Required Forms:</p>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center gap-2">
                            <label class="flex items-center gap-2 cursor-not-allowed">
                                <input type="checkbox" disabled {{ $hasPDS ? 'checked' : '' }} class="peer hidden">
                                <div class="w-4 h-4 rounded border border-gray-400 flex items-center justify-center peer-checked:bg-green-500">
                                    <svg class="hidden peer-checked:block w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span>Personal Data Sheet</span>
                            </label>
                        </li>
                        <li class="flex items-center gap-2">
                            <label class="flex items-center gap-2 cursor-not-allowed">
                                <input type="checkbox" disabled {{ $hasWES ? 'checked' : '' }} class="peer hidden">
                                <div class="w-4 h-4 rounded border border-gray-400 flex items-center justify-center peer-checked:bg-green-500">
                                    <svg class="hidden peer-checked:block w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span>Work Experience Sheet</span>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 mt-2">
                <button type="button" onclick="window.location.href='{{ route('display_c1') }}'"
                    class="use-loader inline-flex font-montserrat items-center gap-2 rounded-full bg-green-600 text-white px-5 py-2 text-sm font-medium shadow-sm hover:bg-opacity-90 transition w-fit">
                    <i data-feather="edit-2" class="w-4 h-4"></i> Edit My Personal Data Sheet
                </button>
                <a href="{{ route('export.pds') }}" target="_blank"
                    class="use-loader inline-flex font-montserrat items-center gap-2 rounded-full bg-blue-600 text-white px-5 py-2 text-sm font-medium shadow-sm hover:bg-opacity-90 transition w-fit">
                    <i data-feather="download" class="w-4 h-4"></i> Export PDS
                </a>
            </div>
            <div id="pdsRightPane" class="mt-4 h-[700px] rounded-xl border border-blue-200 overflow-hidden bg-white shadow-sm">
                <iframe id="pdsFrame" src="about:blank"></iframe>
                <div id="pdsPaneLoader"><div class="pane-spinner"></div></div>
            </div>
        </article>

    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
        <article class="group relative overflow-hidden bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-3 mb-2">
                <i class="w-5 h-5 text-[#0D2B70]" data-feather="bar-chart-2"></i>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Application Status Summary</span>
            </div>
            <div class="grid grid-cols-2 gap-3 text-[#0D2B70]">
                @php
                    $summary = $statusSummary ?? collect();
                    $primaryStatuses = ['Pending', 'Under Review', 'Interview', 'Hired', 'Rejected'];
                @endphp
                @foreach ($primaryStatuses as $label)
                    <div class="flex items-center justify-between bg-blue-50 rounded-md px-3 py-2">
                        <span class="text-xs font-semibold">{{ $label }}</span>
                        <span class="text-base font-extrabold">{{ $summary[$label] ?? 0 }}</span>
                    </div>
                @endforeach
                @foreach (($summary instanceof \Illuminate\Support\Collection ? $summary->toArray() : (array) $summary) as $label => $count)
                    @if (!in_array($label, $primaryStatuses))
                        <div class="flex items-center justify-between bg-blue-50 rounded-md px-3 py-2">
                            <span class="text-xs font-semibold">{{ $label }}</span>
                            <span class="text-base font-extrabold">{{ $count }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </article>

        <article class="group relative overflow-hidden bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-3 mb-2">
                <i class="w-5 h-5 text-[#0D2B70]" data-feather="calendar"></i>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Upcoming Exams</span>
            </div>
            <div class="space-y-2 text-sm text-[#0D2B70]">
                @forelse($upcomingExams ?? [] as $exam)
                    <div class="border border-blue-100 rounded-md p-3">
                        <p class="font-bold">{{ $exam->vacancy->position_title ?? 'Exam' }}</p>
                        <p>{{ \Carbon\Carbon::parse($exam->date)->format('F d, Y') }} | {{ $exam->time }}</p>
                        <p class="text-xs">Venue: {{ $exam->place ?? 'TBA' }}</p>
                    </div>
                @empty
                    <p class="text-gray-600">No upcoming exams scheduled.</p>
                @endforelse
            </div>
        </article>

        <article class="group relative overflow-hidden bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-3 mb-2">
                <i class="w-5 h-5 text-[#0D2B70]" data-feather="file-text"></i>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Required Documents Status</span>
            </div>
            @php
                $docs = collect($documentStatusSummary ?? []);
                $completed = $docs->filter(fn($d) => in_array(strtolower($d['status']), ['completed', 'verified', 'okay/confirmed']));
            @endphp
            <div class="flex items-center gap-3 mb-3">
                <div class="flex-1 bg-gray-200 h-2 rounded-full">
                    @php $percent = $docs->count() ? round(($completed->count() / $docs->count()) * 100) : 0; @endphp
                    <div class="bg-[#0D2B70] h-2 rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
                </div>
                <span class="text-xs font-semibold text-[#0D2B70]">{{ $percent }}% Complete</span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs">
                @foreach ($docs->take(6) as $doc)
                    <div class="flex items-center justify-between bg-blue-50 rounded-md px-3 py-2">
                        <span class="font-semibold">{{ ucwords(str_replace('_',' ',$doc['type'])) }}</span>
                        <span class="{{ in_array(strtolower($doc['status']), ['completed','verified','okay/confirmed']) ? 'text-green-600' : 'text-yellow-600' }}">
                            {{ $doc['status'] }}
                        </span>
                    </div>
                @endforeach
            </div>
            @if ($docs->count() > 6)
                <p class="text-xs text-gray-500 mt-2">+ {{ $docs->count() - 6 }} more</p>
            @endif
        </article>

        <article class="group relative overflow-hidden bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-3 mb-2">
                <i class="w-5 h-5 text-[#0D2B70]" data-feather="layers"></i>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Vacancy Types</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="flex items-center justify-between bg-blue-50 rounded-md px-3 py-2">
                    <span class="text-xs font-semibold">COS</span>
                    <span class="text-base font-extrabold text-[#0D2B70]">{{ $cosVacancyCount ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between bg-blue-50 rounded-md px-3 py-2">
                    <span class="text-xs font-semibold">Plantilla</span>
                    <span class="text-base font-extrabold text-[#0D2B70]">{{ $plantillaVacancyCount ?? 0 }}</span>
                </div>
            </div>
        </article>

        <article class="group relative overflow-hidden bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-3 mb-2">
                <i class="w-5 h-5 text-[#0D2B70]" data-feather="x-circle"></i>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Recently Closed Positions</span>
            </div>
            <div class="space-y-2 text-sm text-[#0D2B70]">
                @forelse($recentlyClosedApplications ?? [] as $app)
                    <div class="border border-blue-100 rounded-md p-3">
                        <p class="font-bold">{{ $app->vacancy->position_title ?? 'Position' }}</p>
                        <p class="text-xs">Vacancy ID: {{ $app->vacancy_id }}</p>
                    </div>
                @empty
                    <p class="text-gray-600">No closed positions among your applications.</p>
                @endforelse
            </div>
        </article>

        <article class="group relative overflow-hidden bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-3 mb-2">
                <i class="w-5 h-5 text-[#0D2B70]" data-feather="clock"></i>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Application Deadlines</span>
            </div>
            <div class="space-y-2 text-sm text-[#0D2B70]">
                @forelse($deadlineCountdown ?? [] as $item)
                    @php
                        $days = $item['days_remaining'];
                        $badge = $days <= 3 ? 'text-red-700' : ($days <= 7 ? 'text-yellow-600' : 'text-green-600');
                        $label = $days <= 3 ? 'Urgent' : ($days <= 7 ? 'Upcoming' : 'Plenty of time');
                    @endphp
                    <div class="border border-blue-100 rounded-md p-3">
                        <p class="font-bold">{{ $item['position_title'] }}</p>
                        <p class="text-xs">Due {{ \Carbon\Carbon::parse($item['deadline'])->format('F d, Y h:i A') }}</p>
                        <p class="text-xs {{ $badge }}">{{ $label }} ({{ $days }} days)</p>
                    </div>
                @empty
                    <p class="text-gray-600">No active application deadlines.</p>
                @endforelse
            </div>
        </article>

        <article class="group relative overflow-hidden bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-3 mb-2">
                <i class="w-5 h-5 text-[#0D2B70]" data-feather="calendar"></i>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Interview Schedule</span>
            </div>
            <div class="space-y-2 text-sm text-[#0D2B70]">
                <p class="text-gray-600">No upcoming interviews scheduled.</p>
            </div>
        </article>

        <article class="group relative overflow-hidden bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-3 mb-2">
                <i class="w-5 h-5 text-[#0D2B70]" data-feather="bell"></i>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Notifications / Alerts</span>
            </div>
            <div class="flex items-center gap-2 mb-3">
                <span class="text-xs text-[#0D2B70] font-semibold">Unread:</span>
                <span class="text-xs font-bold {{ ($unreadNotificationsCount ?? 0) > 0 ? 'text-red-700' : 'text-gray-500' }}">
                    {{ $unreadNotificationsCount ?? 0 }}
                </span>
            </div>
            <div class="space-y-2">
                @forelse(($recentNotifications ?? []) as $n)
                    @php
                        $data = $n->data ?? [];
                    @endphp
                    <div class="border border-blue-100 rounded-md p-3 text-sm text-[#0D2B70]">
                        <p class="font-bold">{{ $data['title'] ?? 'Notification' }}</p>
                        <p class="text-xs">{{ $data['message'] ?? ($n->data['message'] ?? '') }}</p>
                        @if (!empty($data['action_url']))
                            <a href="{{ $data['action_url'] }}" class="text-xs text-[#0D2B70] underline">View</a>
                        @endif
                        <p class="text-[11px] text-gray-500 mt-1">{{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-600">No notifications.</p>
                @endforelse
            </div>
        </article>
    </section>

    @include('partials.loader')

    @if (session('pds_submitted'))
        @include('partials.alerts_template', [
            'id' => 'pdsSuccessModal',
            'showTrigger' => false,
            'title' => 'Success!',
            'message' => 'Personal Data Sheet has been successfully saved.',
            'okText' => 'Back to Dashboard',
            'okAction' => 'showModal = false',
            'showCancel' => false
        ])
    @endif

</main>
@endsection

@section('scripts')
<script>
    feather.replace();

    const sidebar = document.getElementById('sidebar');
    const textElements = [
        "sidebarText", "textHome", "textJobVacancies", "textMyApplications",
        "textPersonalDataSheet", "textAboutWebsite", "textLogOut"
    ].map(id => document.getElementById(id));

    const logo = document.querySelector('img[alt="DILG Logo"]');
    const toggleButton = document.getElementById('toggleSidebar');
    let isOpen = true;

    function openSidebar() {
        sidebar.classList.remove('w-16');
        sidebar.classList.add('w-72');
        logo.classList.remove('logo-small');
        textElements.forEach(el => {
            el.classList.remove('sidebar-text-hidden');
            el.classList.add('sidebar-text-visible');
        });
        isOpen = true;
    }

    function closeSidebar() {
        sidebar.classList.remove('w-72');
        sidebar.classList.add('w-16');
        logo.classList.add('logo-small');
        textElements.forEach(el => {
            el.classList.remove('sidebar-text-visible');
            el.classList.add('sidebar-text-hidden');
        });
        isOpen = false;
    }

    toggleButton?.addEventListener('click', () => {
        isOpen ? closeSidebar() : openSidebar();
    });

    window.onload = () => openSidebar();
    
    const pdsToggle = document.getElementById('pdsDropdownToggle');
    const pdsDropdown = document.getElementById('pdsDropdown');
    pdsToggle?.addEventListener('click', () => {
        pdsDropdown.classList.toggle('hidden');
    });
    const pdsLinks = document.querySelectorAll('#pdsDropdown a');
    const pdsRightPane = document.getElementById('pdsRightPane');
    const pdsFrame = document.getElementById('pdsFrame');
    function showPaneLoader(){ pdsRightPane.classList.add('loading'); }
    function hidePaneLoader(){ pdsRightPane.classList.remove('loading'); }
    pdsLinks.forEach(a => {
        a.addEventListener('click', (e) => {
            e.preventDefault();
            showPaneLoader();
            pdsRightPane.classList.add('show');
            pdsFrame.src = a.href;
        });
    });
    function ensureSimple(u) {
        const url = new URL(u, window.location.origin);
        if (!url.searchParams.has('simple')) url.searchParams.set('simple', '1');
        return url.toString();
    }
    function rewriteNextOrder(doc) {
        const href = doc.location.href;
        const formC1 = doc.querySelector('form#myForm[action*="submit_c1"]');
        const formC2 = doc.querySelector('form#myForm[action*="submit_c2"]');
        const formC3 = doc.querySelector('form#learning-form[action*="submit_c3"]');
        const formC4 = doc.querySelector('form#other-info-form[action*="submit_c4"]');
        if (formC1) {
            formC1.action = ensureSimple('/pds/submit_c1/display_c2');
            formC1.addEventListener('submit', () => { if (parent.showPaneLoader) parent.showPaneLoader(); });
        }
        if (formC2) {
            formC2.action = ensureSimple('/pds/submit_c2/display_c3');
            formC2.addEventListener('submit', () => { if (parent.showPaneLoader) parent.showPaneLoader(); });
        }
        if (formC3) {
            formC3.action = ensureSimple('/pds/submit_c3/display_c4');
            formC3.addEventListener('submit', () => { if (parent.showPaneLoader) parent.showPaneLoader(); });
        }
        if (formC4) {
            formC4.action = ensureSimple('/pds/submit_c4/display_wes');
            formC4.addEventListener('submit', () => { if (parent.showPaneLoader) parent.showPaneLoader(); });
        }
        const wesNext = Array.from(doc.querySelectorAll('button')).find(b => /Upload PDF/i.test(b.textContent));
        if (wesNext) {
            wesNext.addEventListener('click', (ev) => {
                ev.preventDefault();
                if (parent.showPaneLoader) parent.showPaneLoader();
                const routeDisplayC5 = @json(route('display_c5'));
                doc.location.href = ensureSimple(routeDisplayC5);
            }, { once: true });
        }
        doc.querySelectorAll('a').forEach(link=>{
            link.addEventListener('click', ()=> { if (parent.showPaneLoader) parent.showPaneLoader(); });
        });
    }
    pdsFrame.addEventListener('load', () => {
        const doc = pdsFrame.contentWindow.document;
        rewriteNextOrder(doc);
        const loc = pdsFrame.contentWindow.location;
        const pathname = loc.pathname || '';
        const needsSimple = !new URL(loc.href).searchParams.has('simple') && /(display_c1|display_c2|display_c3|display_c4|display_wes|display_c5)/.test(pathname);
        if (needsSimple) {
            showPaneLoader();
            pdsFrame.src = ensureSimple(loc.href);
            return;
        }
        const style = doc.createElement('style');
        style.textContent = '#loader{display:none!important}';
        doc.head.appendChild(style);
        doc.querySelectorAll('.use-loader').forEach(el => el.classList.remove('use-loader'));
        hidePaneLoader();
    });
</script>
@endsection
