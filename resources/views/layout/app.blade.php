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
    class="bg-[#F3F8FF] min-h-screen font-montserrat text-gray-900 overflow-x-hidden">

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
        <main class="flex-1 overflow-y-auto ml-2 pt-0 md:ml-20 transition-all duration-300"
            style="margin-left: 0; padding-left: 18px;">
            <header
                class="sticky top-0 z-40 bg-[#F3F8FF] backdrop-blur px-4 sm:px-8 py-3 flex items-center justify-end gap-6">
                <div id="notifBell" class="relative group">
                    <button id="notifToggle" aria-label="Notifications"
                        class="relative p-2 rounded-full hover:bg-blue-50 transition-colors group-hover:bg-blue-50">
                        <i data-feather="bell" class="w-6 h-6 text-[#0D2B70]"></i>
                        <span id="notifBadge"
                            class="absolute top-1 right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center border-2 border-[#F3F8FF]"
                            style="display: none;">0</span>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="notifMenu"
                        class="hidden absolute right-0 mt-3 w-80 sm:w-96 bg-white shadow-2xl rounded-2xl border border-gray-100 overflow-hidden transform origin-top-right transition-all duration-200 z-50">
                        <!-- Header -->
                        <div
                            class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-white sticky top-0 z-10">
                            <h3 class="font-bold text-[#0D2B70] text-base">Notifications</h3>
                            <button id="notifMarkAll"
                                class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                Mark all as read
                            </button>
                        </div>

                        <!-- List -->
                        <ul id="notifList" class="max-h-[400px] overflow-y-auto divide-y divide-gray-50 scrollbar-thin">
                            <!-- Items will be injected here via JS -->
                        </ul>

                        <!-- Footer -->
                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 text-center">
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
                <div class="relative">
                    <button id="profileToggle" aria-label="Profile menu"
                        class="flex items-center gap-2 p-2 rounded hover:bg-gray-100">
                        @php
                            $u = Auth::user();
                            $avatar = $u->avatar_path ? asset('storage/' . $u->avatar_path) : null;
                            $initials = collect(explode(' ', $u->name))->map(fn($p) => mb_substr($p, 0, 1))->join('');
                        @endphp
                        @if($avatar)
                            <img src="{{ $avatar }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                        @else
                            <div
                                class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">
                                {{ $initials }}</div>
                        @endif
                        <span class="text-sm font-semibold">{{ $u->name }}</span>
                        <i data-feather="chevron-down" class="w-4 h-4"></i>
                    </button>
                    <div id="profileMenu"
                        class="hidden absolute right-0 mt-2 w-56 bg-white shadow-lg rounded-lg border border-gray-200 p-2">
                        <a href="{{ route('account.settings') }}"
                            class="block px-3 py-2 text-sm rounded hover:bg-gray-100">
                            <i data-feather="settings" class="w-4 h-4 inline-block mr-2"></i> Account Settings
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-3 py-2 text-sm rounded hover:bg-gray-100">Logout</button>
                        </form>
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
            const notifLoadMore = document.getElementById('notifLoadMore');
            const notifMarkAll = document.getElementById('notifMarkAll');
            let page = 1;
            let loading = false;
            function fetchCount() {
                fetch("{{ route('notifications.count') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json()).then(d => { notifBadge.textContent = d.count; notifBadge.style.display = d.count > 0 ? 'flex' : 'none'; });
            }
            function fetchItems(reset = false) {
                if (loading) return; loading = true;
                if (reset) { page = 1; notifList.innerHTML = ''; }
                fetch("{{ route('notifications.fetch') }}?page=" + page, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json()).then(d => {
                        d.data.forEach(n => {
                            const li = document.createElement('li');
                            // Render component HTML
                            li.innerHTML = `{!! str_replace("\n", '', view('components.notification-item', ['notification' => (object) ['id' => '__ID__', 'data' => [], 'created_at' => now(), 'read_at' => null]])->render()) !!}`;
                            
                            // 1. Set ID
                            li.setAttribute('data-id', n.id);
                            
                            // 2. Inject Content (using specific classes)
                            const titleEl = li.querySelector('.notif-title');
                            const msgEl = li.querySelector('.notif-message');
                            if (titleEl) titleEl.textContent = n.data.title ?? 'Notification';
                            if (msgEl) msgEl.textContent = n.data.message ?? '';

                            // 2.5 Fix timestamps if possible (client-side formatting is complex without a library, so we accept the server-rendered placeholder or use a simple date)
                            // Ideally, backend returns a formatted string. For now, leave the placeholder or set a simple text.
                            // const timeEl = li.querySelector('.notif-time');
                            // if (timeEl) timeEl.textContent = 'Just now'; // Or parse n.created_at

                            page = d.current_page + 1;
                            
                            // 3. Attach click listener to the DIV inside (or the LI itself)
                            // Since the component is now a DIV, the LI wraps it.
                            li.addEventListener('click', () => {
                                // Mark as read
                                fetch("{{ url('/notifications') }}/" + n.id + "/read", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                                    .then(() => { 
                                        // Visually mark read (remove blue border and bg)
                                        const div = li.querySelector('div[data-id]');
                                        if(div) {
                                            div.classList.remove('border-blue-600', 'bg-blue-50/30');
                                            div.classList.add('border-transparent', 'bg-white');
                                        }
                                        fetchCount(); 
                                    });
                                
                                // Navigate
                                if (n.data.action_url || n.data.link) { 
                                    window.location.href = n.data.action_url || n.data.link; 
                                }
                            });
                            
                            // Replace icons if needed? No, the component has logic for levels.
                            // But wait, the component renders based on the DUMMY data (level=info).
                            // If the actual notification has level=error, the icon will be wrong (Info icon).
                            // WE NEED TO UPDATE THE ICON TOO via JS or pass the level.
                            // JS updating of feather icons is tricky because feather.replace() runs on load.
                            // Better: The backend view() call should theoretically happen PER notification if we were using server-side rendering for the list.
                            // But here we are using client-side fetching with a SINGLE server-side template.
                            // This limits us. We can't easily change the icon class via JS without mapping 'level' -> 'icon-name'.
                            
                            // Quick Fix for Icons:
                            // We can map levels to feathers here.
                            const level = n.data.level || 'info';
                            const iconMap = {
                                'success': 'check', 'warning': 'alert-triangle', 'error': 'x', 'info': 'info'
                            };
                            const colorMap = {
                                'success': 'bg-green-50 text-green-600',
                                'warning': 'bg-yellow-50 text-yellow-600',
                                'error': 'bg-red-50 text-red-600',
                                'info': 'bg-blue-50 text-blue-600'
                            };
                            
                            const iconContainer = li.querySelector('.rounded-full'); // The icon container
                            const icon = iconContainer ? iconContainer.querySelector('i') : null;
                            
                            if (iconContainer && colorMap[level]) {
                                // Reset classes
                                iconContainer.className = `mt-0.5 w-8 h-8 rounded-full flex items-center justify-center shrink-0 ${colorMap[level]}`;
                            }
                            if (icon && iconMap[level]) {
                                icon.setAttribute('data-feather', iconMap[level]);
                            }

                            notifList.appendChild(li);
                        });
                        
                        // Re-run feather to render new icons
                        if (window.feather) feather.replace();

                        notifLoadMore.style.display = d.next_page_url ? 'block' : 'none';
                    }).finally(() => { loading = false; });
            }
            notifToggle?.addEventListener('click', () => {
                notifMenu.classList.toggle('hidden');
                if (!notifMenu.classList.contains('hidden')) { fetchItems(true); fetchCount(); }
            });
            notifLoadMore?.addEventListener('click', () => fetchItems(false));
            notifMarkAll?.addEventListener('click', () => {
                fetch("{{ route('notifications.mark_all') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                    .then(() => { fetchCount(); notifMenu.classList.add('hidden'); });
            });
            const profileToggle = document.getElementById('profileToggle');
            const profileMenu = document.getElementById('profileMenu');
            profileToggle?.addEventListener('click', () => profileMenu.classList.toggle('hidden'));
            setInterval(fetchCount, 15000);
            fetchCount();
            const isAuthed = @json(auth()->check());
            const channelId = @json(auth()->id());
            if (window.Echo && isAuthed && channelId) {
                window.Echo.private('notifications.' + channelId).listen('.NewSystemNotification', () => {
                    fetchCount();
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
        let isOpen = localStorage.getItem('sidebarOpen') === 'true';

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
                openSidebar();
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
