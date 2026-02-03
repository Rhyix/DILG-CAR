<!-- resources/views/partials/sidebar.blade.php -->
<aside id="sidebar"
    class="sidebar sidebar-transition fixed ml-5 mt-5 mb-5 flex flex-col justify-between bg-white text-[#002C76] rounded-xl shadow-lg overflow-hidden w-16 relative z-60 h-[95vh]">

    <!-- Toggle Button -->
    <button id="toggleSidebar" class="p-2 focus:outline-none absolute top-3 left-3 z-20" aria-label="Toggle sidebar">
        <i data-feather="menu" class="w-5 h-5 stroke-[3]"></i>
    </button>

    <!-- Upper Section -->
    <div>
        <a class="flex items-center gap-2 pt-14 px-2">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo"
                class="h-12 w-12 rounded-full border border-white flex-shrink-0 logo-transition" />
            <div id="sidebarText" class="sidebar-text-hidden whitespace-nowrap overflow-hidden">
                <div class="font-bold font-montserrat text-[#002C76] text-[20px] uppercase leading-tight tracking-wide">
                    DILG - CAR
                </div>
                <div class="text-[16px] leading-4 font-bold font-montserrat tracking-tighter text-[#002C76] uppercase">
                    RECRUITMENT SELECTION
                    <br>
                    AND PLACEMENT PORTAL
                </div>
            </div>
        </a>

        <!-- Navigation -->
        <nav class="mt-8 space-y-1 px-2 font-montserrat" aria-label="Main navigation">
            <a href="{{ route('dashboard_user') }}"
                class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                    {{ request()->routeIs('dashboard_user')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                <i data-feather="home" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textHome" class="sidebar-text-hidden ml-3">HOME</span>
            </a>

            <a href="{{ route('job_vacancy') }}"
                class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                    {{ request()->routeIs('job_vacancy')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                <i data-feather="archive" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textJobVacancies" class="sidebar-text-hidden ml-3">JOB VACANCIES</span>
            </a>

            <a href="{{ route('my_applications') }}"
                class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                    {{ request()->routeIs('my_applications')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                <i data-feather="user" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textMyApplications" class="sidebar-text-hidden ml-3">MY APPLICATIONS</span>
            </a>

            <a href="{{ route('work_experience') }}"
                class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                    {{ request()->routeIs('work_experience')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                <i data-feather="briefcase" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textWorkExperience" class="sidebar-text-hidden ml-3">WORK EXPERIENCE</span>
            </a>

            <a href="{{ route('display_c1') }}"
                class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                    {{ request()->routeIs('display_c1')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                <i data-feather="file-text" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textPersonalDataSheet" class="sidebar-text-hidden ml-3">PERSONAL DATA SHEET</span>
            </a>

            <a href="{{ route('about') }}"
                class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                    {{ request()->routeIs('about')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                <i data-feather="info" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textAboutWebsite" class="sidebar-text-hidden ml-3">ABOUT THIS WEBSITE</span>
            </a>
        </nav>
    </div>

    <!-- Bottom Section -->
    <div class="px-2 pb-6">
        <button
            id="logoutButton"
            class="group flex items-center rounded-md border border-[#FFFFFF] px-4 py-2 text-sm font-bold text-[#C9282D] hover:bg-[#C9282D] hover:bg-opacity-20 hover:border-red-500 transition w-full"
        >
            <i data-feather="log-out" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
            <span id="textLogOut" class="sidebar-text-hidden ml-3">LOG-OUT</span>
        </button>
    </div>
</aside>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl p-6 shadow-lg max-w-sm w-full">
        <h2 class="text-lg font-bold text-[#002C76] mb-4">Confirm Logout</h2>
        <p class="mb-6 text-gray-700">Are you sure you want to log out?</p>
        <div class="flex justify-end gap-4">
            <button id="cancelLogout" class="px-4 py-2 -mt-4 text-sm font-semibold text-gray-600 hover:text-gray-800">
                Cancel
            </button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-[#C9282D] text-white text-sm font-semibold rounded hover:bg-red-700">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Sidebar Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const logoutButton = document.getElementById('logoutButton');
        const logoutModal = document.getElementById('logoutModal');
        const cancelLogout = document.getElementById('cancelLogout');

        logoutButton.addEventListener('click', () => {
            logoutModal.classList.remove('hidden');
            logoutModal.classList.add('flex');
        });

        cancelLogout.addEventListener('click', () => {
            logoutModal.classList.add('hidden');
            logoutModal.classList.remove('flex');
        });

        feather.replace();
    });
</script>
