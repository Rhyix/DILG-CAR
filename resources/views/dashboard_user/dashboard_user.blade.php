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

<main class="mt-7 sm:mt-0 flex-1 min-w-0 bg-[#F3F8FF] font-sans text-gray-900 overflow-hidden h-screen px-4 lg:px-6 py-4" style="margin-top: 0">
    
    <!-- dynamic message eyyy💅 -->
    <section class="text-center sm:text-left">
        <div class="text-xl font-normal mb-1 font-montserrat text-[#002C76]">
            @php
                $hour = now()->format('H');
                
                if ($hour >= 5 && $hour < 12) {
                    $greeting = 'Good morning';
                } elseif ($hour >= 12 && $hour < 17) {
                    $greeting = 'Good afternoon';
                } elseif ($hour >= 17 && $hour < 21) {
                    $greeting = 'Good evening';
                } else {
                    $greeting = 'Good night';
                }
            @endphp
            
            {{ $greeting }},
        </div>
        <h1 class="font-extrabold text-2xl sm:text-3xl tracking-tight font-montserrat text-[#002C76]">{{ Auth::user()->name }}</h1>
    </section>

    <section class="space-y-3">
        @php
            $activeApplicationsCount = $applications->filter(fn($a) => strtolower($a->status) !== 'closed')->count();
            $deadlinesSoonCount = collect($deadlineCountdown ?? [])->filter(fn($d) => ($d['days_remaining'] ?? 999) >= 0 && ($d['days_remaining'] ?? 999) <= 7)->count();
            $upcomingExamsCount = ($upcomingExams ?? collect())->count();
            $pdsPercent = (int) ($pdsProgress ?? 0);
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-3 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Active Applications</p>
                    <p class="text-2xl font-extrabold text-[#0D2B70]">{{ $activeApplicationsCount }}</p>
                </div>
                <i data-feather="clipboard" class="w-6 h-6 text-[#0D2B70]"></i>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Deadlines (≤7d)</p>
                    <p class="text-2xl font-extrabold text-[#0D2B70]">{{ $deadlinesSoonCount }}</p>
                </div>
                <i data-feather="clock" class="w-6 h-6 text-[#0D2B70]"></i>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Upcoming Exams</p>
                    <p class="text-2xl font-extrabold text-[#0D2B70]">{{ $upcomingExamsCount }}</p>
                </div>
                <i data-feather="calendar" class="w-6 h-6 text-[#0D2B70]"></i>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3">
                <p class="text-xs text-gray-500 uppercase font-bold">PDS Completion</p>
                <div class="mt-2 flex items-center gap-3">
                    <div class="flex-1 bg-gray-200 h-2 rounded-full">
                        <div class="bg-[#0D2B70] h-2 rounded-full" style="width: {{ $pdsPercent }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-[#0D2B70]">{{ $pdsPercent }}%</span>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-3">
                <article class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <i class="w-5 h-5 text-[#0D2B70]" data-feather="briefcase"></i>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">My Applications</span>
                    </div>
                    <div class="space-y-2 text-sm text-[#0D2B70] max-h-36 overflow-hidden">
                        @php $apps = $applications->filter(fn($a)=> strtolower($a->status) !== 'closed')->take(3); @endphp
                        @forelse($apps as $a)
                            <div class="flex items-center justify-between border border-blue-100 rounded-md px-3 py-2">
                                <span class="font-semibold">{{ $a->vacancy->position_title ?? 'N/A' }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ strtolower($a->status) === 'pending' ? 'bg-yellow-50 text-yellow-700' : (strtolower($a->status)==='hired' ? 'bg-green-50 text-green-700' : 'bg-blue-50 text-[#0D2B70]') }}">{{ $a->status }}</span>
                            </div>
                        @empty
                            <p class="text-gray-600">You have no active applications.</p>
                        @endforelse
                    </div>
                    <button onclick="window.location.href='{{ route('my_applications') }}'"
                        class="use-loader mt-4 inline-flex items-center gap-2 rounded-md border border-[#0D2B70] text-[#0D2B70] px-4 py-2 text-sm font-bold hover:bg-[#0D2B70] hover:text-white transition">
                        <i data-feather="eye" class="w-4 h-4"></i>
                        <span>View Applications</span>
                    </button>
                </article>
                <article class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <i class="w-5 h-5 text-[#0D2B70]" data-feather="clock"></i>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Application Deadlines</span>
                    </div>
                    <div class="space-y-2 text-sm text-[#0D2B70] max-h-36 overflow-hidden">
                        @forelse(collect($deadlineCountdown ?? [])->take(3) as $item)
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
            </div>
            <div class="grid grid-cols-1 gap-3">
                <article class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="w-5 h-5 text-[#0D2B70]" data-feather="file-text"></i>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Required Documents</span>
                    </div>
                    @php
                        $docs = collect($documentStatusSummary ?? []);
                        $completed = $docs->filter(fn($d) => in_array(strtolower($d['status']), ['completed', 'verified', 'okay/confirmed']));
                        $percent = $docs->count() ? round(($completed->count() / $docs->count()) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex-1 bg-gray-200 h-2 rounded-full">
                            <div class="bg-[#0D2B70] h-2 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                        <span class="text-xs font-semibold text-[#0D2B70]">{{ $percent }}%</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs max-h-24 overflow-hidden">
                        @foreach ($docs->take(6) as $doc)
                            <div class="flex items-center justify-between bg-blue-50 rounded-md px-3 py-2">
                                <span class="font-semibold">{{ ucwords(str_replace('_',' ',$doc['type'])) }}</span>
                                <span class="{{ in_array(strtolower($doc['status']), ['completed','verified','okay/confirmed']) ? 'text-green-600' : 'text-yellow-600' }}">{{ $doc['status'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
                <article class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <i class="w-5 h-5 text-[#0D2B70]" data-feather="bell"></i>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Notifications</span>
                    </div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs text-[#0D2B70] font-semibold">Unread:</span>
                        <span class="text-xs font-bold {{ ($unreadNotificationsCount ?? 0) > 0 ? 'text-red-700' : 'text-gray-500' }}">{{ $unreadNotificationsCount ?? 0 }}</span>
                    </div>
                    <div class="space-y-2 max-h-36 overflow-hidden">
                        @forelse(collect($recentNotifications ?? [])->take(3) as $n)
                            @php $data = $n->data ?? []; @endphp
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
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 w-full">
        <article class="group relative overflow-hidden bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-3 mb-2">
                <i class="w-5 h-5 text-[#0D2B70]" data-feather="bar-chart-2"></i>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Application Status Summary</span>
            </div>
            <div class="grid grid-cols-2 gap-3 text-[#0D2B70]">
                @php
                    $summary = $statusSummary ?? ($applicationStatusSummary ?? collect());
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

        <article class="group relative overflow-hidden bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-3 mb-2">
                <i class="w-5 h-5 text-[#0D2B70]" data-feather="calendar"></i>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Upcoming Exams</span>
            </div>
            <div class="space-y-2 text-sm text-[#0D2B70] max-h-36 overflow-hidden">
                @forelse(collect($upcomingExams ?? [])->take(3) as $exam)
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

        

        <article class="group relative overflow-hidden bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
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
    function showPaneLoader(){ if (pdsRightPane) pdsRightPane.classList.add('loading'); }
    function hidePaneLoader(){ if (pdsRightPane) pdsRightPane.classList.remove('loading'); }
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
    if (pdsFrame) {
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
    }
</script>
@endsection
