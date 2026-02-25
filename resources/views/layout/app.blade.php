<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'DILG Dashboard')</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('dilg_logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @php
        $metaTitle = 'DILG - CAR Recruitment Selection and Placement Portal';
        $metaDescription = 'Isa ka bang "Matino, Mahusay, at Maaasahan" na manggagawang Pilipino?';
        $metaImage = asset('images/dilg_rsp_thumbnail.png');
        $metaUrl = url()->current();

        if (request()->is('login')) {
            $metaTitle = 'Login - DILG Recruitment Portal';
            $metaDescription = 'Access your account to view job vacancies and submit your application.';
            $metaImage = asset('images/dilg_login_thumbnail.png');
        } elseif (request()->is('jobs/*')) {
            $metaTitle = 'View Job Vacancy - DILG CAR';
            $metaDescription = 'Explore available job opportunities and join our team at DILG CAR.';
        }
    @endphp

    <!-- Open Graph Meta -->
    <meta property="og:title" content="{{ $metaTitle }}" />
    <meta property="og:description" content="{{ $metaDescription }}" />
    <meta property="og:image" content="{{ $metaImage }}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:type" content="image/png" />
    <meta property="og:url" content="{{ $metaUrl }}" />
    <meta property="og:type" content="website" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $metaTitle }}" />
    <meta name="twitter:description" content="{{ $metaDescription }}" />
    <meta name="twitter:image" content="{{ $metaImage }}" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Libraries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        .font-montserrat {
            font-family: 'Montserrat', sans-serif;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-track {
            background-color: transparent;
        }

        .sidebar-transition {
            transition: width 0.4s ease, padding 0.4s ease;
        }

        .sidebar-text-hidden {
            opacity: 0;
            pointer-events: none;
            width: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-text-visible {
            opacity: 1;
            pointer-events: auto;
            width: auto;
            transition: all 0.3s ease;
        }

        .logo-transition {
            transition: all 0.3s ease;
        }

        .logo-small {
            max-width: 48px;
            max-height: 48px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
        }

        /* Remove number input arrows */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        @media (max-width: 1024px) {
            .sidebar-desktop {
                display: none;
            }
        }
    </style>

    @stack('styles')
</head>

<body x-data="{ mobileSidebarOpen: false, showLogoutModal: false }"
    class="bg-[#F3F8FF] min-h-screen font-montserrat text-gray-900 overflow-hidden">

    <!-- 🔥 Mobile Toggle Button -->
    <button @click="mobileSidebarOpen = true"
        class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-white rounded-full shadow-md mt-4">
        <i data-feather="menu" class="w-5 h-5"></i>
    </button>

    <!-- 📱 Mobile Sidebar (only visible on small screens) -->
    <div class="lg:hidden">
        @include('partials.mobile-sidebar')
    </div>

    <!-- 💻 Main Layout -->
    <div class="flex h-screen w-full overflow-hidden">

        <!-- 🖥️ Desktop Sidebar (hidden on mobile) -->
        <div class="sidebar-desktop">
            @include('partials.sidebar')
        </div>

        <!-- Main Content -->
        <main class="flex-1 {{ request()->routeIs('my_applications') ? 'overflow-hidden' : 'overflow-y-auto' }} ml-2 pt-0 md:ml-20 transition-all duration-300"
            style="padding-left: 18px;">
            <header
                class="sticky top-0 z-40 bg-[#F3F8FF] backdrop-blur px-4 sm:px-8 pt-5 sm:pt-6 pb-3 flex items-center justify-end">
                <div class="flex items-center gap-1 rounded-full border border-slate-200 bg-white/80 backdrop-blur-sm px-2 py-1 shadow-sm">
                    <div id="notifBell" class="relative">
                        <button id="notifToggle" aria-label="Notifications"
                            class="relative h-10 w-10 rounded-full text-slate-500 hover:text-[#0D2B70] hover:bg-slate-100/80 transition-colors">
                            <i data-feather="bell" class="w-5 h-5 mx-auto"></i>
                            <span id="notifBadge"
                                class="absolute top-0.5 right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center border-2 border-white"
                                style="display: none;">0</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="notifMenu"
                            class="hidden absolute right-0 mt-3 w-[24rem] sm:w-[26rem] bg-white shadow-2xl rounded-2xl border border-slate-200 overflow-hidden transform origin-top-right transition-all duration-200 z-50">
                            <!-- Header -->
                            <div
                                class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
                                <h3 class="font-bold text-[#0D2B70] text-base">Notifications</h3>
                                <button id="notifMarkAll"
                                    class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                    Mark all as read
                                </button>
                            </div>

                            <!-- List -->
                            <ul id="notifList" class="max-h-[420px] overflow-y-auto divide-y divide-slate-100 scrollbar-thin">
                                <!-- Items will be injected here via JS -->
                            </ul>

                            <!-- Footer -->
                            <div class="px-4 py-3 bg-white border-t border-slate-100 text-center">
                                <a href="{{ route('notifications.index') }}"
                                    class="text-xs font-bold text-[#0D2B70] hover:text-blue-700 hover:underline">
                                    View Full History
                                </a>
                            </div>

                            <!-- Loader (Hidden by default) -->
                            <div id="notifLoader"
                                class="hidden absolute inset-0 bg-white/80 flex items-center justify-center z-20">
                                <div
                                    class="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="h-6 w-px bg-slate-200"></div>

                    <div class="relative">
                        <button id="profileToggle" aria-label="Profile menu"
                            class="flex items-center gap-2 rounded-full pl-1 pr-2 py-1 hover:bg-slate-100/80 transition-colors">
                            @php
                                $u = Auth::user();
                                $avatar = $u->avatar_path ? asset('storage/' . $u->avatar_path) : null;
                                $initials = collect(explode(' ', $u->name))->map(fn($p) => mb_substr($p, 0, 1))->join('');
                            @endphp
                            @if($avatar)
                                <img src="{{ $avatar }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover">
                            @else
                                <div
                                    class="w-9 h-9 rounded-full bg-[#0D2B70] text-white flex items-center justify-center text-xs font-bold shadow-sm">
                                    {{ $initials }}</div>
                            @endif
                            <span class="text-sm font-semibold text-slate-800 hidden sm:inline">{{ $u->name }}</span>
                            <i data-feather="chevron-down" class="w-4 h-4 text-slate-400"></i>
                        </button>
                        <div id="profileMenu"
                            class="hidden absolute right-0 mt-3 w-56 bg-white shadow-xl rounded-xl border border-slate-200 p-2">
                            <a href="{{ route('profile.show') }}"
                                class="block px-3 py-2.5 text-sm rounded text-slate-700 hover:bg-slate-50 hover:text-[#0D2B70]">View Profile</a>
                            <a href="{{ route('profile.edit') }}"
                                class="block px-3 py-2.5 text-sm rounded text-slate-700 hover:bg-slate-50 hover:text-[#0D2B70]">Edit Profile</a>
                            <a href="{{ route('profile.password.form') }}"
                                class="block px-3 py-2.5 text-sm rounded text-slate-700 hover:bg-slate-50 hover:text-[#0D2B70]">Change Password</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-3 py-2.5 text-sm rounded text-red-600 hover:bg-red-50 font-medium">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
            <!-- <div class="p-3 sm:p-10 pt-8 mt-0 sm:mt-1 space-y-10"> -->
            <div class="mt-0 sm:mt-1 space-y-10">
                @yield('content')
            </div>
        </main>

        <!-- Chatbot -->
        @if(!request()->routeIs('about'))
            @include('partials.chat_ai')
        @endif
    </div>

    <!-- Feather + Sidebar Script -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.effect(() => {
                feather.replace();
            });
        });
        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();
            const notifToggle = document.getElementById('notifToggle');
            const notifMenu = document.getElementById('notifMenu');
            const notifBadge = document.getElementById('notifBadge');
            const notifList = document.getElementById('notifList');
            const notifMarkAll = document.getElementById('notifMarkAll');
            let loading = false;

            const iconMap = {
                success: 'check',
                warning: 'alert-triangle',
                error: 'x',
                info: 'bell'
            };
            const iconClassMap = {
                success: 'bg-emerald-50 text-emerald-600',
                warning: 'bg-amber-50 text-amber-600',
                error: 'bg-red-50 text-red-600',
                info: 'bg-slate-100 text-slate-500'
            };

            function formatTime(value) {
                const ts = new Date(value);
                if (Number.isNaN(ts.getTime())) return '';
                const seconds = Math.floor((Date.now() - ts.getTime()) / 1000);
                if (seconds < 60) return 'Just now';
                if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
                if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
                return ts.toLocaleString();
            }

            function renderEmptyState() {
                notifList.innerHTML = `
                    <li class="px-5 py-10 text-center text-slate-500">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 mb-3">
                            <i data-feather="bell-off" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <p class="text-sm">No notifications yet</p>
                    </li>
                `;
            }

            function renderNotificationItems(items) {
                if (!Array.isArray(items) || items.length === 0) {
                    renderEmptyState();
                    if (window.feather) feather.replace();
                    return;
                }

                notifList.innerHTML = '';
                items.forEach((n) => {
                    const level = n?.data?.level || n?.type || 'info';
                    const iconName = iconMap[level] || iconMap.info;
                    const iconTone = iconClassMap[level] || iconClassMap.info;
                    const unread = !n.read_at;

                    const li = document.createElement('li');
                    li.className = `px-5 py-4 hover:bg-slate-50 transition-colors cursor-pointer ${unread ? 'bg-blue-50/30' : 'bg-white'}`;
                    li.innerHTML = `
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 ${iconTone}">
                                <i data-feather="${iconName}" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm ${unread ? 'font-semibold' : 'font-medium'} text-[#0D2B70] leading-5">
                                        ${n?.data?.title || 'Notification'}
                                    </p>
                                    <span class="text-[11px] text-slate-400 whitespace-nowrap">${formatTime(n.created_at)}</span>
                                </div>
                                <p class="text-sm text-slate-600 mt-1 leading-6">
                                    ${n?.data?.message || ''}
                                </p>
                            </div>
                        </div>
                    `;

                    li.addEventListener('click', async () => {
                        try {
                            await fetch(`{{ url('/notifications') }}/${n.id}/read`, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                            });
                            fetchCount();
                        } catch (_) {}

                        const targetUrl = n?.data?.action_url || n?.data?.link;
                        if (targetUrl) window.location.href = targetUrl;
                    });

                    notifList.appendChild(li);
                });

                if (window.feather) feather.replace();
            }

            function fetchCount() {
                fetch("{{ route('notifications.count') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json()).then(d => { notifBadge.textContent = d.count; notifBadge.style.display = d.count > 0 ? 'flex' : 'none'; });
            }

            function fetchItems() {
                if (loading) return;
                loading = true;
                fetch("{{ route('notifications.fetch') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json()).then(d => {
                        renderNotificationItems(d.data || d.notifications || []);
                    }).finally(() => { loading = false; });
            }

            notifToggle?.addEventListener('click', () => {
                notifMenu.classList.toggle('hidden');
                if (!notifMenu.classList.contains('hidden')) {
                    fetchItems();
                    fetchCount();
                }
            });

            notifMarkAll?.addEventListener('click', () => {
                fetch("{{ route('notifications.mark_all') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                    .then(() => {
                        fetchCount();
                        fetchItems();
                    });
            });

            document.addEventListener('click', (e) => {
                if (!notifMenu || notifMenu.classList.contains('hidden')) return;
                if (!notifMenu.contains(e.target) && !notifToggle?.contains(e.target)) {
                    notifMenu.classList.add('hidden');
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') notifMenu?.classList.add('hidden');
            });

            const profileToggle = document.getElementById('profileToggle');
            const profileMenu = document.getElementById('profileMenu');
            profileToggle?.addEventListener('click', () => profileMenu.classList.toggle('hidden'));
            setInterval(() => {
                fetchCount();
                if (notifMenu && !notifMenu.classList.contains('hidden')) fetchItems();
            }, 15000);
            fetchCount();
            window.addEventListener('focus', () => {
                fetchCount();
                if (notifMenu && !notifMenu.classList.contains('hidden')) fetchItems();
            });
            const isAuthed = @json(auth()->check());
            const channelId = @json(auth()->id());
            if (window.Echo && isAuthed && channelId) {
                window.Echo.private('notifications.' + channelId).listen('.NewSystemNotification', () => {
                    fetchCount();
                    if (notifMenu && !notifMenu.classList.contains('hidden')) fetchItems();
                });
            }
        });

        const sidebar = document.getElementById('sidebar');
        const textElements = [
            "sidebarText",
            "textHome",
            "textJobVacancies",
            "textMyApplications",
            "textPersonalDataSheet",
            "textAboutWebsite",
            "textWorkExperience",
            "textLogOut"
        ].map(id => document.getElementById(id));

        const logo = document.querySelector('img[alt="DILG Logo"]');
        const toggleButton = document.getElementById('toggleSidebar');
        const storedOpen = localStorage.getItem('sidebarOpen');
        let isOpen = storedOpen === 'true';

        function openSidebar() {
            sidebar?.classList.remove('w-16');
            sidebar?.classList.add('w-72');
            logo?.classList.remove('logo-small');
            textElements.forEach(el => {
                el?.classList.remove('sidebar-text-hidden');
                el?.classList.add('sidebar-text-visible');
            });
            isOpen = true;
            localStorage.setItem('sidebarOpen', 'true');
        }

        function closeSidebar() {
            sidebar?.classList.remove('w-72');
            sidebar?.classList.add('w-16');
            logo?.classList.add('logo-small');
            textElements.forEach(el => {
                el?.classList.remove('sidebar-text-visible');
                el?.classList.add('sidebar-text-hidden');
            });
            isOpen = false;
            localStorage.setItem('sidebarOpen', 'false');
        }

        toggleButton?.addEventListener('click', () => {
            isOpen ? closeSidebar() : openSidebar();
        });

        window.addEventListener('DOMContentLoaded', () => {
            if (window.innerWidth >= 1024) {
                // Open sidebar by default if not set in localStorage
                if (storedOpen === null) {
                    openSidebar();
                } else {
                    isOpen ? openSidebar() : closeSidebar();
                }
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
