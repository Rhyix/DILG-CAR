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
    <!-- Alpine Plugins -->
    <script src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Alpine Core -->
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        [x-cloak] { display: none !important; }
        
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
    </style>

    @stack('styles')
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sidebar', () => ({
                open: localStorage.getItem('sidebarOpen') === 'true',
                isMobile: window.innerWidth < 1024,
                
                init() {
                    // Initialize state
                    if (this.isMobile && this.open) {
                        document.body.style.overflow = 'hidden';
                    }
                    
                    this.$watch('open', value => {
                        localStorage.setItem('sidebarOpen', value);
                        if (this.isMobile) {
                            if (value) document.body.style.overflow = 'hidden';
                            else document.body.style.overflow = '';
                        }
                    });
                    
                    window.addEventListener('resize', () => {
                        const wasMobile = this.isMobile;
                        this.isMobile = window.innerWidth < 1024;
                        
                        if (wasMobile && !this.isMobile) {
                             document.body.style.overflow = '';
                        }
                    });
                },
                
                toggle() {
                    this.open = !this.open;
                },
                
                close() {
                    this.open = false;
                }
            }))
        })
    </script>
</head>

<body class="bg-[#F1F6FC] h-screen font-sans font-montserrat text-gray-900 overflow-hidden" x-data="sidebar">

    <!-- App Container: Sidebar + Content -->
    <div class="flex h-screen w-full overflow-hidden">

        {{-- Sidebar --}}
        @include('partials.sidebar_admin')

        <!-- Content Wrapper -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden relative min-w-0">
            <!-- Top Header (Notification Bell & Profile) -->
            <header class="flex justify-end items-center gap-6 px-6 sm:px-8 md:px-10 pt-6 sm:pt-8 pb-4 shrink-0 z-50 bg-[#F1F6FC]">
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
                        style="display: none;" x-cloak>
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
                        style="display: none;" x-cloak>
                        <a href="{{ route('admin_account_management') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#0D2B70]">
                            <i data-feather="settings" class="w-4 h-4 inline-block mr-2"></i> Account Settings
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form id="adminLogoutForm" method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="button"
                                @click.prevent="$dispatch('open-logout-confirm')"
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
                <button id="mobileMenuButton" @click="toggle"
                    x-show="isMobile && !open"
                    class="lg:hidden fixed top-4 left-4 z-20 bg-[#0D2B70] text-white p-3 rounded-lg shadow-lg hover:bg-[#001a4d] transition-all duration-200"
                    aria-label="Open menu" x-cloak>
                    <i data-feather="menu" class="w-6 h-6"></i>
                </button>

                @yield('content')
            </main>
        </div>

    </div>
</body>

<!-- REUSABLE CONFIRMATION MODAL -->
<x-confirm-modal 
    title="Confirm Logout"
    message="Are you sure you want to log out?"
    event="open-logout-confirm"
    confirm="confirm-logout"
/>


<!-- JS Scripts -->
<script>
    feather.replace();

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
                // Check if target is not blank
                if (this.target !== '_blank') {
                    e.preventDefault();
                    loader?.classList.remove('hidden');
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 100);
                }
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

    // Submit admin logout only after confirmation
    window.addEventListener('confirm-logout', () => {
        const logoutForm = document.getElementById('adminLogoutForm');
        if (logoutForm) logoutForm.submit();
    });

</script>

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
        const search = document.getElementById('adminSearchInput');
        if(!search) return;

        fetch(`/admin/search?query=${encodeURIComponent(search.value)}`)
            .then(response => response.text())
            .then(html => {
                const container = document.querySelector('section.space-y-4');
                if(container) container.innerHTML = html;
            });
    };

    const fetchAdminsDebounced = debounce(fetchAdmins, 300);
</script>


@stack('scripts')

</html>
