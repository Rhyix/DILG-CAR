@extends('layout.app')
@section('title', 'Dashboard | DILG-CAR')

@section('content')
    <div class="px-4 pb-8 sm:px-8">

        <!-- Hero / Welcome Section -->
        <div
            class="relative bg-gradient-to-r from-[#0D2B70] to-[#1e40af] rounded-2xl p-5 sm:p-8 lg:p-10 text-white shadow-lg mb-6 sm:mb-8 overflow-hidden">
            <div class="relative z-10">
                <h1 class="font-montserrat font-extrabold text-2xl sm:text-3xl lg:text-4xl mb-2">
                    @php
                        $hour = now()->format('H');
                        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
                    @endphp
                    {{ $greeting }}, {{ Auth::user()->name }}!
                </h1>
                <p class="text-blue-100 text-sm sm:text-base max-w-2xl">
                    Welcome to your applicant portal. Track your applications, manage your PDS, and stay updated with the
                    latest announcements.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('job_vacancy') }}"
                        class="inline-flex items-center gap-2 bg-white text-[#0D2B70] px-5 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-100 transition shadow-sm">
                        <i data-feather="search" class="w-4 h-4"></i> Browse Jobs
                    </a>
                    <a href="{{ route('account.settings') }}"
                        class="inline-flex items-center gap-2 bg-[#ffffff20] text-white px-5 py-2.5 rounded-lg font-bold text-sm hover:bg-[#ffffff30] transition backdrop-blur-sm border border-white/20">
                        <i data-feather="settings" class="w-4 h-4"></i> Account Settings
                    </a>
                </div>
            </div>
            <!-- Decorative Circle -->
            <div class="absolute -right-10 -bottom-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute top-0 right-20 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
            <!-- Active Applications -->
            <div
                class="bg-white p-4 sm:p-5 rounded-xl border border-blue-50 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex justify-between items-start mb-4">
                    <div
                        class="p-2 bg-blue-50 text-[#0D2B70] rounded-lg group-hover:bg-[#0D2B70] group-hover:text-white transition-colors">
                        <i data-feather="briefcase" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Applications in progress</span>
                </div>
                <div class="text-3xl font-extrabold text-[#0D2B70] mb-1">
                    {{ $applications->filter(fn($a) => strtolower($a->status) !== 'closed')->count() }}
                </div>
                <!-- <div class="text-sm text-slate-500">Applications in progress</div> -->
            </div>

            <!-- Upcoming Deadlines -->
            <div
                class="bg-white p-4 sm:p-5 rounded-xl border border-blue-50 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex justify-between items-start mb-4">
                    <div
                        class="p-2 bg-orange-50 text-orange-600 rounded-lg group-hover:bg-orange-600 group-hover:text-white transition-colors">
                        <i data-feather="clock" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Deadlines this week</span>
                </div>
                <div class="text-3xl font-extrabold text-[#0D2B70] mb-1">
                    {{ collect($deadlineCountdown)->filter(fn($d) => $d['days_remaining'] <= 5)->count() }}
                </div>
                <!-- <div class="text-sm text-slate-500">Deadlines this week</div> -->
            </div>

            <!-- Upcoming Exams -->
            <div
                class="bg-white p-4 sm:p-5 rounded-xl border border-blue-50 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex justify-between items-start mb-4">
                    <div
                        class="p-2 bg-purple-50 text-purple-600 rounded-lg group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <i data-feather="calendar" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Scheduled exams</span>
                </div>
                <div class="text-3xl font-extrabold text-[#0D2B70] mb-1">
                    {{ $upcomingExamsCount ?? $upcomingExams->count() }}
                </div>
                <!-- <div class="text-sm text-slate-500">Scheduled exams</div> -->
            </div>

            <!-- PDS Progress -->
            <div
                class="bg-white p-4 sm:p-5 rounded-xl border border-blue-50 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex justify-between items-start mb-4">
                    <div
                        class="p-2 bg-green-50 text-green-600 rounded-lg group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <i data-feather="file-text" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">PDS Profile</span>
                </div>
                <div class="flex items-end gap-2 mb-1">
                    <div class="text-3xl font-extrabold text-[#0D2B70]">{{ $pdsProgress }}%</div>
                    <div
                        class="mb-1.5 text-xs font-medium {{ $pdsProgress >= 100 ? 'text-green-600' : 'text-orange-500' }}">
                        {{ $pdsProgress >= 100 ? 'Completed' : 'Incomplete' }}
                    </div>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                    <div class="bg-green-500 h-1.5 rounded-full transition-all duration-500"
                        style="width: {{ $pdsProgress }}%"></div>
                </div>
            </div>
        </div>

        <!-- Main Content Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

            <!-- Left Column: Applications (2/3 width) -->
            <div class="lg:col-span-2 space-y-8">

                <!-- My Applications Section -->
                <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h2 class="font-bold text-[#0D2B70] flex items-center gap-2">
                            <i data-feather="briefcase" class="w-4 h-4"></i> My Applications
                        </h2>
                        <a href="{{ route('my_applications') }}"
                            class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline">View All</a>
                    </div>

                    @if($applications->isEmpty())
                        <div class="p-8 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-50 rounded-full mb-3">
                                <i data-feather="inbox" class="w-8 h-8 text-gray-300"></i>
                            </div>
                            <h3 class="text-gray-900 font-bold mb-1">No applications yet</h3>
                            <p class="text-gray-500 text-sm mb-4">You haven't applied to any job vacancies yet.</p>
                            <a href="{{ route('job_vacancy') }}"
                                class="text-sm font-bold text-white bg-blue-600 px-4 py-2 rounded-lg hover:bg-blue-700 transition">Explore
                                Jobs</a>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($applications->take(5) as $app)
                                                <div
                                                    class="p-4 sm:p-5 hover:bg-blue-50/30 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                                    <div>
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <h3 class="font-bold text-[#0D2B70]">
                                                                {{ $app->vacancy->position_title ?? 'Unknown Position' }}</h3>
                                                            @php
                                                                $statusColors = [
                                                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                                                    'qualified' => 'bg-blue-100 text-blue-700',
                                                                    'hired' => 'bg-green-100 text-green-700',
                                                                    'rejected' => 'bg-red-100 text-red-700',
                                                                    'closed' => 'bg-gray-100 text-gray-600',
                                                                ];
                                                                $statusClass = $statusColors[strtolower($app->status)] ?? 'bg-gray-100 text-gray-600';
                                                            @endphp
                                 <span
                                                                class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full {{ $statusClass }}">
                                                                {{ $app->status }}
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center gap-4 text-xs text-slate-500">
                                                            <span class="flex items-center gap-1"><i data-feather="map-pin" class="w-3 h-3"></i>
                                                                {{ $app->vacancy->place_of_assignment ?? 'N/A' }}</span>
                                                            <span class="flex items-center gap-1"><i data-feather="calendar" class="w-3 h-3"></i>
                                                                Applied: {{ $app->created_at->format('M d, Y') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <a href="{{ route('application_status', ['user' => Auth::id(), 'vacancy' => $app->vacancy_id]) }}"
                                                            class="px-3.5 py-1.5 text-xs font-bold text-[#0D2B70] border border-gray-200 bg-white rounded-md hover:border-[#0D2B70] hover:bg-[#0D2B70] hover:text-white transition">
                                                            View Status
                                                        </a>
                                                    </div>
                                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- Recent Notifications -->
                <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h2 class="font-bold text-[#0D2B70] flex items-center gap-2">
                            <i data-feather="bell" class="w-4 h-4"></i> Recent Notifications
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($recentNotifications as $notif)
                            <div class="p-4 hover:bg-gray-50 transition cursor-pointer {{ $notif->read_at ? '' : 'bg-blue-50/40' }}"
                                onclick="window.location.href='{{ isset($notif->data['action_url']) ? $notif->data['action_url'] : '#' }}'">
                                <div class="flex justify-between items-start gap-2">
                                    <div class="flex-1">
                                        <p
                                            class="text-sm font-semibold text-[#0D2B70] {{ $notif->read_at ? 'font-normal' : '' }}">
                                            {{ $notif->data['title'] ?? 'Notification' }}
                                        </p>
                                        <p class="text-xs text-slate-600 mt-1 line-clamp-2">
                                            {{ $notif->data['message'] ?? '' }}
                                        </p>
                                    </div>
                                    <span class="text-[10px] text-gray-400 whitespace-nowrap">
                                        @php
                                            $diff = $notif->created_at->diffForHumans(null, true, true);
                                        @endphp
                                        {{ $diff === '0s' ? 'Just now' : $diff }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500 text-sm">
                                No recent notifications.
                            </div>
                        @endforelse
                    </div>
                </section>

            </div>

            <!-- Right Column: Sidebar (1/3 width) -->
            <div class="space-y-6">

                <!-- Quick Actions / PDS Links -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="font-bold text-[#0D2B70] mb-4 flex items-center gap-2">
                        <i data-feather="file-text" class="w-4 h-4"></i> Personal Data Sheet
                    </h3>
                    <div class="grid grid-cols-1 gap-2.5">
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
                        @foreach($pdsLinks as $link)
                            <a href="{{ route($link['route']) }}"
                                class="flex items-center gap-3 p-2.5 sm:p-3 rounded-lg border border-gray-100 hover:border-blue-300 hover:bg-blue-50 text-slate-700 hover:text-[#0D2B70] transition group">
                                <div
                                    class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-xs group-hover:bg-[#0D2B70] group-hover:text-white transition-colors shrink-0">
                                    <i data-feather="{{ $link['icon'] }}" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-sm font-medium truncate">{{ $link['name'] }}</span>
                                <i data-feather="chevron-right"
                                    class="w-4 h-4 ml-auto text-gray-300 group-hover:text-[#0D2B70] shrink-0"></i>
                            </a>
                        @endforeach

                        <a href="{{ route('display_wes') }}"
                            class="flex items-center gap-3 p-2.5 sm:p-3 rounded-lg border border-dashed border-gray-300 hover:border-[#0D2B70] hover:bg-blue-50 text-slate-600 hover:text-[#0D2B70] transition mt-2">
                            <div
                                class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs shrink-0">
                                <i data-feather="file-plus" class="w-3.5 h-3.5"></i>
                            </div>
                            <span class="text-sm font-semibold truncate">Work Experience Sheet</span>
                        </a>
                    </div>
                </div>

                <!-- Deadlines Widget -->
                @if(count($deadlineCountdown) > 0)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                        <h3 class="font-bold text-[#0D2B70] mb-3 flex items-center gap-2">
                            <i data-feather="alert-circle" class="w-4 h-4 text-orange-500"></i> Action Required
                        </h3>
                        <div class="space-y-3">
                            @foreach(collect($deadlineCountdown)->take(3) as $deadline)
                                <div class="p-3 bg-orange-50 rounded-lg border border-orange-100">
                                    @php($daysRemaining = (int) ($deadline['days_remaining'] ?? 0))
                                    <p class="text-xs font-bold text-orange-800 uppercase mb-1">Due in
                                        {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'Day' : 'Days' }}</p>
                                    <p class="text-sm font-bold text-[#0D2B70]">{{ $deadline['position_title'] }}</p>
                                    <p class="text-[11px] text-gray-600 mt-0.5">Deadline:
                                        {{ \Carbon\Carbon::parse($deadline['deadline'])->format('M d, h:i A') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Help / Support -->
                <div class="bg-gradient-to-br from-blue-900 to-[#0D2B70] rounded-2xl p-6 text-white text-center">
                    <div
                        class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3 backdrop-blur-sm">
                        <i data-feather="help-circle" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-1">Need Help?</h3>
                    <p class="text-blue-200 text-xs mb-4">Contact HR if you encounter issues with your application.</p>
                    <div class="text-xs font-mono bg-black/20 py-2 rounded-lg">hr.support@dilg-car.gov.ph</div>
                </div>

            </div>

        </div>

    </div>
@endsection

@section('scripts')
    <script>
        feather.replace();
    </script>
@endsection
