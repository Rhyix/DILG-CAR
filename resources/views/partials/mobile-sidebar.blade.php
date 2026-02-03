<!-- resources/views/partials/mobile-sidebar.blade.php -->
<div 
    x-show="mobileSidebarOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed inset-0 z-50 flex lg:hidden"
    style="display: none;"
>
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="mobileSidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside class="relative w-72 bg-white h-full shadow-xl flex flex-col justify-between z-50">
        <!-- Header -->
        <div class="flex items-center gap-3 p-4 border-b border-gray-200">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-10 w-10 rounded-full" />
            <div class="font-bold text-sm text-[#002C76] font-montserrat leading-tight">
                DILG - CAR <br>
                <span class="text-xs font-medium tracking-tight">RECRUITMENT SELECTION AND PLACEMENT PORTAL</span>
            </div>
            <button @click="mobileSidebarOpen = false" class="ml-auto text-gray-600 hover:text-gray-800">
                <i data-feather="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Links -->
        <nav class="flex-1 overflow-y-auto font-montserrat p-4 space-y-2">
            <x-mobile-nav-link icon="home" label="Home" :active="request()->routeIs('dashboard_user')" href="{{ route('dashboard_user') }}" />
            <x-mobile-nav-link icon="archive" label="Job Vacancies" :active="request()->routeIs('job_vacancy')" href="{{ route('job_vacancy') }}" />
            <x-mobile-nav-link icon="user" label="My Applications" :active="request()->routeIs('my_applications')" href="{{ route('my_applications') }}" />
            <x-mobile-nav-link icon="briefcase" label="Work Experience" :active="request()->routeIs('work_experience')" href="{{ route('work_experience') }}" />
            <x-mobile-nav-link icon="file-text" label="Personal Data Sheet" :active="request()->routeIs('display_c1')" href="{{ route('display_c1') }}" />
            <x-mobile-nav-link icon="info" label="About This Website" :active="request()->routeIs('about')" href="{{ route('about') }}" />
        </nav>
        
        <!-- Footer -->
        <div class="p-4 border-t border-gray-200">
            <button @click="showLogoutModal = true" class="flex items-center gap-2 text-[#C9282D] font-bold hover:bg-red-100 w-full px-4 py-2 rounded-md transition">
                <i data-feather="log-out" class="w-5 h-5 stroke-[3]"></i>
                Log Out
            </button>
        </div>
    </aside>
</div>

<!-- Logout Modal (Shared with Desktop for DRYness) -->
@include('partials.logout-modal')
