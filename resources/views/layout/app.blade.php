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

<body x-data="{ mobileSidebarOpen: false, showLogoutModal: false }" class="bg-[#F3F8FF] min-h-screen font-montserrat text-gray-900 overflow-x-hidden">

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
        <main class="flex-1 overflow-y-auto ml-2 p-3 sm:p-10 pt-8 mt-0 sm:mt-1 space-y-10 md:ml-20 transition-all duration-300" style="margin-left: 0; padding-left: 18px;">
            @yield('content')
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
                // Open sidebar by default if not set in localStorage
                if (isOpen === null) {
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