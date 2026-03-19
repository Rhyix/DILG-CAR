@extends('layout.app')
@section('title', 'Dashboard | DILG-CAR')

@section('content')
    <div class="bg-[#f5f7fb]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

            <!-- Hero / Welcome Section -->
            <div
                class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0c2a6a] via-[#1b3f9a] to-[#1f67d1] text-white shadow-2xl">
                <div class="relative z-10 p-6 sm:p-7 lg:p-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="space-y-3">
                        <p class="text-xs sm:text-sm uppercase tracking-[0.2em] text-white/70 font-semibold">Applicant Portal</p>
                        <h1 class="font-montserrat font-extrabold text-2xl sm:text-3xl lg:text-4xl">
                            @php
                                $hour = now()->format('H');
                                $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
                            @endphp
                            {{ $greeting }}, {{ Auth::user()->name }}!
                        </h1>
                        <p class="text-blue-100 text-sm sm:text-base max-w-2xl leading-relaxed">
                            Welcome to your applicant portal. Track your applications, manage your PDS, and stay updated with the
                            latest announcements.
                        </p>
                        <div class="flex flex-wrap gap-3 pt-1">
                            <a href="{{ route('job_vacancy') }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-white px-5 py-2.5 text-sm font-bold text-[#0D2B70] shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-gray-100">
                                <i data-feather="search" class="w-4 h-4"></i>
                                Search Jobs
                            </a>
                            <a href="{{ route('account.settings') }}"
                                class="inline-flex items-center gap-2 rounded-lg border border-white/25 bg-white/15 px-5 py-2.5 text-sm font-bold text-white backdrop-blur-sm transition hover:-translate-y-0.5 hover:bg-white/25">
                                <i data-feather="settings" class="w-4 h-4"></i>
                                Account Settings
                            </a>
                        </div>
                    </div>
                </div>
                <div class="absolute -left-10 -bottom-16 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute right-6 -top-10 h-32 w-32 rounded-full bg-white/15 blur-2xl"></div>
            </div>

            <!-- Quick Stats Grid -->
            @php $actionRequiredCount = collect($deadlineCountdown ?? [])->count(); @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
                <div class="group relative overflow-hidden rounded-2xl bg-white p-4 shadow-lg shadow-slate-200/70 border border-slate-100">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-[#0D2B70]">
                                <i data-feather="briefcase" class="w-5 h-5"></i>
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Applications in Progress</p>
                                <p class="text-3xl font-extrabold text-[#0D2B70]">
                                    {{ $applications->filter(fn($a) => strtolower($a->status) !== 'closed')->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 right-0 h-20 w-20 rounded-full bg-blue-50/60 blur-3xl transition-transform duration-500 group-hover:scale-110"></div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl bg-white p-4 shadow-lg shadow-slate-200/70 border border-slate-100">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                                <i data-feather="clock" class="w-5 h-5"></i>
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Deadlines This Week</p>
                                <p class="text-3xl font-extrabold text-[#0D2B70]">
                                    {{ collect($deadlineCountdown)->filter(fn($d) => $d['days_remaining'] <= 5)->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 right-0 h-20 w-20 rounded-full bg-orange-50/60 blur-3xl transition-transform duration-500 group-hover:scale-110"></div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl bg-white p-4 shadow-lg shadow-slate-200/70 border border-slate-100">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                                <i data-feather="calendar" class="w-5 h-5"></i>
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Scheduled Exams</p>
                                <p class="text-3xl font-extrabold text-[#0D2B70]">{{ $upcomingExamsCount ?? $upcomingExams->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 right-0 h-20 w-20 rounded-full bg-purple-50/60 blur-3xl transition-transform duration-500 group-hover:scale-110"></div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl bg-white p-4 shadow-lg shadow-slate-200/70 border border-slate-100">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <button type="button" id="action-required-card" class="flex items-center gap-3 text-left">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                                    <i data-feather="alert-triangle" class="w-5 h-5"></i>
                                </span>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Action Required</p>
                                    <div class="flex items-center gap-2">
                                        <p class="text-3xl font-extrabold text-[#0D2B70]">{{ $actionRequiredCount }}</p>
                                        <span class="text-[11px] font-semibold text-orange-600">Tap to view list</span>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                    <div class="absolute bottom-0 right-0 h-20 w-20 rounded-full bg-orange-50/60 blur-3xl transition-transform duration-500 group-hover:scale-110"></div>
                </div>
            </div>

            @if($actionRequiredCount > 0)
                <div id="action-required-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
                    <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-slate-100 p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-[#0D2B70] font-bold">
                                <i data-feather="alert-circle" class="h-4 w-4 text-orange-500"></i>
                                <span>Action Required</span>
                                <span class="text-xs font-semibold text-orange-600">({{ $actionRequiredCount }})</span>
                            </div>
                            <button type="button" id="action-required-close" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Close</button>
                        </div>
                        <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
                            @foreach(collect($deadlineCountdown) as $deadline)
                                @php
                                    $daysRemaining = (int) ($deadline['days_remaining'] ?? 0);
                                @endphp
                                <div class="rounded-xl border border-orange-100 bg-orange-50 px-3 py-3">
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-orange-800">Due in {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'Day' : 'Days' }}</p>
                                    <p class="mt-1 text-sm font-bold text-[#0D2B70] leading-snug">{{ $deadline['position_title'] }}</p>
                                    <p class="text-[11px] text-slate-600">Deadline: {{ \Carbon\Carbon::parse($deadline['deadline'])->format('M d, h:i A') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main Content Layout -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <!-- My Applications Section -->
                <section class="xl:col-span-7 overflow-hidden rounded-2xl bg-white shadow-lg shadow-slate-200/70 border border-slate-100">
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 sm:px-6 py-3">
                        <div class="flex items-center gap-2 text-[#0D2B70] font-bold">
                            <i data-feather="briefcase" class="w-4 h-4"></i>
                            My Applications
                        </div>
                        <a href="{{ route('my_applications') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline">View All</a>
                    </div>

                    @if($applications->isEmpty())
                        <div class="p-8 text-center">
                            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-50">
                                <i data-feather="inbox" class="h-8 w-8 text-slate-300"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">No applications yet</h3>
                            <p class="mt-1 text-sm text-slate-500">You have not applied to any job vacancies yet.</p>
                            <div class="mt-4">
                                <a href="{{ route('job_vacancy') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#0D2B70] px-4 py-2 text-sm font-bold text-white shadow-md transition hover:bg-[#0b2560]">
                                    <i data-feather="arrow-right" class="w-4 h-4"></i>
                                    Explore Jobs
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach($applications->take(5) as $app)
                                <div class="flex flex-col gap-3 p-4 transition hover:bg-blue-50/30">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-base font-bold text-[#0D2B70] leading-tight">
                                                    {{ $app->vacancy->position_title ?? 'Unknown Position' }}
                                                </h3>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                                        'qualified' => 'bg-blue-100 text-blue-700',
                                                        'hired' => 'bg-green-100 text-green-700',
                                                        'rejected' => 'bg-red-100 text-red-700',
                                                        'closed' => 'bg-gray-100 text-gray-600',
                                                        'not qualified' => 'bg-red-100 text-red-700',
                                                    ];
                                                    $statusKey = strtolower(trim($app->status));
                                                    $statusClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-600';
                                                @endphp
                                                <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide rounded-full {{ $statusClass }}">
                                                    {{ $app->status }}
                                                </span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                                <span class="flex items-center gap-1"><i data-feather="map-pin" class="h-3 w-3"></i>{{ $app->vacancy->place_of_assignment ?? 'N/A' }}</span>
                                                <span class="flex items-center gap-1"><i data-feather="calendar" class="h-3 w-3"></i>Applied {{ $app->created_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('application_status', ['user' => Auth::id(), 'vacancy' => $app->vacancy_id]) }}"
                                                class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-[#0D2B70] shadow-sm transition hover:border-[#0D2B70] hover:bg-[#0D2B70] hover:text-white">
                                                View Status
                                                <i data-feather="chevron-right" class="w-3 h-3"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- Right Column: PDS Links -->
                <div class="xl:col-span-5 space-y-4">
                    <div class="rounded-2xl bg-white shadow-lg shadow-slate-200/70 border border-slate-100 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2 font-bold text-[#0D2B70]">
                                <i data-feather="file-text" class="h-4 w-4"></i>
                                Personal Data Sheet
                            </div>
                            <span class="text-xs font-semibold text-green-600">{{ $pdsProgress }}% Completed</span>
                        </div>
                        <div class="mb-4 h-2 w-full rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-green-500" style="width: {{ $pdsProgress }}%"></div>
                        </div>
                        @php
                            $pdsLinks = [
                                ['name' => 'Personal Information', 'route' => 'display_c1', 'icon' => 'user'],
                                ['name' => 'Family Background', 'route' => 'display_c2', 'icon' => 'users'],
                                ['name' => 'Educational Background', 'route' => 'display_c2', 'icon' => 'book'],
                                ['name' => 'Civil Service & Work Exp.', 'route' => 'display_c3', 'icon' => 'briefcase'],
                                ['name' => 'Voluntary Work & Training', 'route' => 'display_c3', 'icon' => 'award'],
                                ['name' => 'Other Information', 'route' => 'display_c4', 'icon' => 'info'],
                            ];
                        @endphp
                        <div class="space-y-2">
                            @foreach($pdsLinks as $link)
                                <a href="{{ route($link['route']) }}" class="flex items-center gap-3 rounded-lg border border-slate-100 px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-50 text-slate-500">
                                        <i data-feather="{{ $link['icon'] }}" class="h-4 w-4"></i>
                                    </span>
                                    <span class="flex-1 truncate">{{ $link['name'] }}</span>
                                    <i data-feather="chevron-right" class="h-4 w-4 text-slate-400"></i>
                                </a>
                            @endforeach
                            <a href="{{ route('display_wes') }}" class="flex items-center gap-3 rounded-lg border border-dashed border-blue-200 bg-blue-50/60 px-3 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-blue-600 border border-blue-200">
                                    <i data-feather="file-plus" class="h-4 w-4"></i>
                                </span>
                                <span class="flex-1 truncate">Work Experience Sheet</span>
                                <i data-feather="plus" class="h-4 w-4 text-blue-600"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();

            const actionCard = document.getElementById('action-required-card');
            const actionModal = document.getElementById('action-required-modal');
            const actionClose = document.getElementById('action-required-close');
            const openModal = () => {
                if (!actionModal) return;
                actionModal.classList.remove('hidden');
            };
            const closeModal = () => {
                if (!actionModal) return;
                actionModal.classList.add('hidden');
            };
            [actionCard, actionClose, actionModal].forEach((el) => {
                if (!el) return;
                if (el === actionCard) el.addEventListener('click', openModal);
                if (el === actionClose) el.addEventListener('click', closeModal);
                if (el === actionModal) el.addEventListener('click', (e) => { if (e.target === actionModal) closeModal(); });
            });

            const normalizeNotificationUrl = window.normalizeNotificationUrl || ((targetUrl) => targetUrl || '');
            document.querySelectorAll('.js-recent-notification').forEach((item) => {
                item.addEventListener('click', () => {
                    const targetUrl = item.dataset.link;
                    if (!targetUrl) return;
                    window.location.href = normalizeNotificationUrl(targetUrl);
                });
            });
        });
    </script>
@endpush
