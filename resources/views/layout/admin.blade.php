<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DILG Dashboard')</title>


    <!-- Tailwind CSS + Alpine + Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

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
            transition: width 0.25s ease, padding 0.25s ease;
        }

        .sidebar-text-hidden {
            opacity: 0;
            pointer-events: none;
            width: 0;
            overflow: hidden;
            transition: none;
        }

        .sidebar-text-visible {
            opacity: 1;
            pointer-events: auto;
            width: auto;
            transition: opacity 0.15s ease;
        }

        .logo-transition {
            transition: all 0.2s ease;
        }

        .logo-small {
            max-width: 48px;
            max-height: 48px;
        }

        .badge-notification {
            position: absolute;
            top: -0.2rem;
            right: -0.3rem;
            background-color: #ef4444;
            color: white;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 0 0.3rem;
            border-radius: 9999px;
            line-height: 1rem;
            user-select: none;
        }

        .sidebar a {
            display: flex;
            align-items: center;
        }

        /* Custom scrollbar for left panel */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background-color: #374151;
            /* Tailwind gray-700 */
            border-radius: 9999px;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
            /* Firefox */
            appearance: textfield;
        }

        /* Disable transitions on page load */
        .sidebar-preload,
        .sidebar-preload * {
            transition: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-[#F1F6FC] h-screen font-sans font-montserrat text-gray-900 overflow-hidden">

    <!-- App Container: Sidebar + Content -->
    <div class="flex h-screen w-full overflow-hidden">

        {{-- Sidebar --}}
        @include('partials.sidebar_admin')

        {{-- Immediate sidebar initialization to prevent glitch --}}
        <script>
            (function () {
                const sidebar = document.getElementById('sidebar');
                const logo = document.querySelector('img[alt="DILG Logo"]');
                const textElements = [
                    "sidebarText", "textHome", "textJobVacancies", "textMyApplications",
                    "textPersonalDataSheet", "textAdmins", "textLogOut",
                    "textUtilities", "utilitiesChevron", "utilitiesSubmenu"
                ].map(id => document.getElementById(id));

                const isMobile = window.innerWidth < 1024;
                const isOpen = localStorage.getItem('sidebarOpen') === 'true';

                // Apply state immediately
                if (isMobile) {
                    sidebar.classList.add('w-16', '-translate-x-full');
                    sidebar.classList.remove('w-72', 'translate-x-0');
                    if (logo) logo.classList.add('logo-small');
                    textElements.forEach(el => {
                        if (el) {
                            el.classList.add('sidebar-text-hidden');
                            el.classList.remove('sidebar-text-visible');
                        }
                    });
                } else {
                    if (isOpen) {
                        sidebar.classList.remove('w-16', '-translate-x-full');
                        sidebar.classList.add('w-72', 'translate-x-0');
                        if (logo) logo.classList.remove('logo-small');
                        textElements.forEach(el => {
                            if (el) {
                                el.classList.remove('sidebar-text-hidden');
                                el.classList.add('sidebar-text-visible');
                            }
                        });
                    } else {
                        sidebar.classList.add('w-16');
                        sidebar.classList.remove('w-72', 'translate-x-0', '-translate-x-full');
                        if (logo) logo.classList.add('logo-small');
                        textElements.forEach(el => {
                            if (el) {
                                el.classList.add('sidebar-text-hidden');
                                el.classList.remove('sidebar-text-visible');
                            }
                        });
                    }
                }

                // Make visible immediately
                sidebar.style.visibility = 'visible';

                // Remove preload class after a tiny delay
                setTimeout(() => {
                    sidebar.classList.remove('sidebar-preload');
                }, 50);
            })();
        </script>

        <!-- Content Wrapper -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden relative min-w-0">
            <!-- Top Header (Notification Bell & Profile) -->
            <header class="flex justify-end items-center gap-6 px-6 sm:px-8 md:px-10 pt-6 sm:pt-8 pb-4 shrink-0 z-10 bg-[#F1F6FC]">
                <!-- Notification Bell -->
                <div class="relative" x-data="{ open: false, count: 0, notifications: [] }" x-init="
                    fetch('/notifications/count').then(r => r.json()).then(d => count = d.count);
                    setInterval(() => fetch('/notifications/count').then(r => r.json()).then(d => count = d.count), 30000);
                ">
                    <button
                        @click="open = !open; if(open) { fetch('/notifications/fetch').then(r => r.json()).then(d => { notifications = d.notifications; count = 0; fetch('/notifications/mark-all', {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}}); }); }"
                        class="relative p-2 text-gray-600 hover:text-[#0D2B70] transition-colors focus:outline-none">
                        <i data-feather="bell" class="w-6 h-6"></i>
                        <span x-show="count > 0"
                            class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                            x-text="count"></span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-xl z-50 overflow-hidden"
                        style="display: none;">
                        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <h3 class="text-sm font-bold text-gray-700">Notifications</h3>
                            <button
                                @click="notifications = []; fetch('/notifications/cleanup', {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}})"
                                class="text-xs text-red-500 hover:text-red-700">Clear All</button>
                        </div>
                        <ul class="max-h-64 overflow-y-auto divide-y divide-gray-100">
                            <template x-for="notif in notifications" :key="notif.id">
                                <li class="px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer"
                                    @click="if(notif.data && notif.data.link) window.location.href = notif.data.link">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="w-2 h-2 rounded-full" 
                                                 :class="notif.read_at ? 'bg-gray-300' : 'bg-blue-500'"></div>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-800 font-medium" x-text="notif.data?.title || notif.title"></p>
                                            <p class="text-xs text-gray-600 mt-0.5" x-text="notif.data?.message || notif.message"></p>
                                            <p class="text-[10px] text-gray-400 mt-1"
                                                x-text="new Date(notif.created_at).toLocaleString()"></p>
                                        </div>
                                    </div>
                                </li>
                            </template>
                            <li x-show="notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">
                                No new notifications
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-3 focus:outline-none group">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-gray-800 group-hover:text-[#0D2B70] transition-colors">
                                {{ Auth::guard('admin')->user()->name ?? 'Admin User' }}
                            </p>
                            <p class="text-xs text-gray-500 uppercase">
                                {{ Auth::guard('admin')->user()->role ?? 'Administrator' }}
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-full bg-[#0D2B70] text-white flex items-center justify-center font-bold text-lg shadow-md group-hover:shadow-lg transition-all">
                            {{ substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <i data-feather="chevron-down"
                            class="w-4 h-4 text-gray-400 group-hover:text-[#0D2B70] transition-colors"></i>
                    </button>

                    <!-- Profile Menu -->
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-xl z-50 py-1"
                        style="display: none;">
                        <a href="{{ route('admin_account_management') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#0D2B70]">
                            <i data-feather="settings" class="w-4 h-4 inline-block mr-2"></i> Account Settings
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">
                                <i data-feather="log-out" class="w-4 h-4 inline-block mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Main Content Scrollable -->
            <main class="flex-1 overflow-y-auto px-6 sm:px-8 md:px-10 pb-6 sm:pb-8 md:pb-10 pt-0 relative scroll-smooth">
                <!-- Mobile Menu Button (visible only on mobile) -->
                <button id="mobileMenuButton" onclick="window.openSidebar ? window.openSidebar() : null"
                    class="lg:hidden fixed top-4 left-4 z-20 bg-[#0D2B70] text-white p-3 rounded-lg shadow-lg hover:bg-[#001a4d] transition-all duration-200"
                    aria-label="Open menu">
                    <i data-feather="menu" class="w-6 h-6"></i>
                </button>

                @yield('content')
            </main>
        </div>

    </div>
</body>

<!-- JS Scripts -->
<script>
    feather.replace();

    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const textElements = [
        "sidebarText",
        "textHome",
        "textJobVacancies",
        "textMyApplications",
        "textPersonalDataSheet",
        "textAdmins",
        "textLogOut",
        "textUtilities",
        "utilitiesChevron",
        "utilitiesSubmenu"
    ].map(id => document.getElementById(id));

    const logo = document.querySelector('img[alt="DILG Logo"]');
    const toggleButton = document.getElementById('toggleSidebar');

    // Check if we're on mobile
    const isMobile = () => window.innerWidth < 1024;

    // Retrieve sidebar state from localStorage (default to false/closed)
    let isOpen = localStorage.getItem('sidebarOpen') === 'true';

    function openSidebar() {
        sidebar.classList.remove('w-16', '-translate-x-full');
        sidebar.classList.add('w-72', 'translate-x-0');
        logo.classList.remove('logo-small');
        textElements.forEach(el => {
            if (el) {
                el.classList.remove('sidebar-text-hidden');
                el.classList.add('sidebar-text-visible');
            }
        });

        // Show overlay on mobile
        if (isMobile() && sidebarOverlay) {
            sidebarOverlay.classList.remove('hidden');
            // Prevent body scroll on mobile when sidebar is open
            document.body.style.overflow = 'hidden';
        }

        // Hide mobile menu button when sidebar is open
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        if (mobileMenuButton && isMobile()) {
            mobileMenuButton.style.display = 'none';
        }

        isOpen = true;
        localStorage.setItem('sidebarOpen', 'true');
    }

    function closeSidebar() {
        sidebar.classList.remove('w-72', 'translate-x-0');
        sidebar.classList.add('w-16');

        // On mobile, slide it off-screen
        if (isMobile()) {
            sidebar.classList.add('-translate-x-full');
        }

        logo.classList.add('logo-small');
        textElements.forEach(el => {
            if (el) {
                el.classList.remove('sidebar-text-visible');
                el.classList.add('sidebar-text-hidden');
            }
        });

        // Hide overlay
        if (sidebarOverlay) {
            sidebarOverlay.classList.add('hidden');
            // Re-enable body scroll
            document.body.style.overflow = '';
        }

        // Show mobile menu button when sidebar is closed
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        if (mobileMenuButton && isMobile()) {
            mobileMenuButton.style.display = 'block';
        }

        isOpen = false;
        localStorage.setItem('sidebarOpen', 'false');
    }

    // Make functions globally accessible
    window.closeSidebar = closeSidebar;
    window.openSidebar = openSidebar;

    toggleButton?.addEventListener('click', () => {
        if (isOpen) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    // Handle window resize - close sidebar on mobile when resizing
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (isMobile() && isOpen) {
                // On mobile, if sidebar is open, keep it open but ensure overlay is visible
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('hidden');
                }
            } else if (!isMobile()) {
                // On desktop, remove mobile-specific classes
                sidebar.classList.remove('-translate-x-full');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.add('hidden');
                }
                document.body.style.overflow = '';
            }
        }, 250);
    });

    // Initialize feather icons only (state already applied inline)
    function initializeSidebarState() {
        // Just initialize feather icons
        feather.replace();
    }

    // Call initialization for feather icons
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeSidebarState);
    } else {
        initializeSidebarState();
    }

    const form = document.querySelector('form');
    const loader = document.getElementById('loader');

    if (form) {
        form.addEventListener('submit', () => {
            loader?.classList.remove('hidden');
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('a.use-loader').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                loader?.classList.remove('hidden');
                setTimeout(() => {
                    window.location.href = this.href;
                }, 100);
            });
        });
    });

    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            document.querySelector('.background')?.classList.add('hidden');
        }
    });

    function viewPDF(filePath, title = 'Document') {
        const previewContainer = document.getElementById('pdf-preview');

        previewContainer.innerHTML = `
                <div class="border rounded-lg shadow p-4 mt-4">
                    <p class="font-semibold text-gray-700 mb-2">📄 ${title}</p>
                    <embed src="${filePath}" type="application/pdf" class="w-full h-96 rounded border">
                </div>
            `;
    }

</script>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
    function debounce(func, delay) {
        let debounceTimer;
        return function () {
            const context = this;
            const args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(context, args), delay);
        };
    }

    const fetchAdmins = () => {
        const search = document.getElementById('adminSearchInput').value;

        fetch(`/admin/search?query=${encodeURIComponent(search)}`)
            .then(response => response.text())
            .then(html => {
                document.querySelector('section.space-y-4').innerHTML = html;
            });
    };

    const fetchAdminsDebounced = debounce(fetchAdmins, 300);
</script>


@stack('scripts')

</html>