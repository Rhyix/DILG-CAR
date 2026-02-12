<!-- resources/views/partials/sidebar.blade.php -->

<!-- Mobile Overlay Backdrop -->
<div id="sidebarOverlay"
    class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden transition-opacity duration-300 lg:hidden"
    onclick="closeSidebar()">
</div>

<!-- Sidebar -->
<aside id="sidebar" style="visibility: hidden;"
    class="sidebar sidebar-preload sidebar-transition fixed lg:relative lg:ml-5 lg:mt-5 lg:mb-5 flex flex-col justify-between bg-white text-[#002C76] lg:rounded-xl shadow-2xl overflow-y-auto overflow-x-hidden w-16 z-40 h-full lg:h-[calc(100vh-2.5rem)] top-0 left-0 lg:top-auto lg:left-auto -translate-x-full lg:translate-x-0 flex-shrink-0">

    <!-- Upper -->
    <div class="flex-1 overflow-y-auto">
        <button id="toggleSidebar"
            class="p-2 focus:outline-none absolute top-3 left-3 z-20 hover:bg-gray-100 rounded-lg transition-colors"
            aria-label="Toggle sidebar">
            <i data-feather="menu" class="w-5 h-5 stroke-[3]"></i>
        </button>

        <a href="#" class="flex items-center gap-2 pt-14 px-2 cursor-pointer">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo"
                class="h-12 w-12 rounded-full border border-white flex-shrink-0 logo-transition" />
            <div id="sidebarText" class="sidebar-text-hidden whitespace-nowrap overflow-hidden">
                <div class="font-bold font-montserrat text-[#002C76] text-[20px] uppercase leading-tight tracking-wide">
                    DILG - CAR
                </div>
                <div class="text-[16px] leading-4 font-bold font-montserrat tracking-tighter text-[#002C76] uppercase">
                    RECRUITMENT SELECTION
                    <br>
                    AND PLACEMENT PORTAL<br>
                    <span style="color: #C9282D;">ADMIN PANEL</span>
                </div>
            </div>
        </a>

        <nav class="mt-8 space-y-1 px-2 font-montserrat pb-4">
            <a href="{{ route('dashboard_admin') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                    {{ request()->routeIs('dashboard_admin')
    ? 'bg-[#002C76] text-white shadow-md'
    : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                <i data-feather="home" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textHome" class="sidebar-text-hidden ml-3">HOME</span>
            </a>
            <a href="{{ route('vacancies_management') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                    {{ request()->routeIs('vacancies_management')
    ? 'bg-[#002C76] text-white shadow-md'
    : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                <i data-feather="archive" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textJobVacancies" class="sidebar-text-hidden ml-3">VACANCIES MANAGEMENT</span>
            </a>

            <a href="{{ route('applications_list') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                    {{ request()->routeIs('applications_list') || request()->routeIs('admin.applicant_status*')
    ? 'bg-[#002C76] text-white shadow-md'
    : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                <i data-feather="user" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textMyApplications" class="sidebar-text-hidden ml-3">APPLICATIONS LIST</span>
            </a>

            <a href="{{ route('admin_exam_management') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                    {{ request()->routeIs('admin_exam_management') || request()->routeIs('admin.exam*')
    ? 'bg-[#002C76] text-white shadow-md'
    : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                <i data-feather="file-text" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textPersonalDataSheet" class="sidebar-text-hidden ml-3">EXAM MANAGEMENT</span>
            </a>

            <a href="{{ route('admin_account_management') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                    {{ request()->routeIs('admin_account_management')
    ? 'bg-[#002C76] text-white shadow-md'
    : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                <i class="fa-solid fa-wrench w-5 h-5 flex-shrink-0"></i>
                <span id="textAboutWebsite" class="sidebar-text-hidden ml-3">USER MANAGEMENT</span>
            </a>

            <!-- Utilities Dropdown -->
            <div x-data="{ open: {{ request()->routeIs('admin_activity_log') ? 'true' : 'false' }} }" class="relative">
                <button @click="open = !open" class="w-full group flex items-center justify-between rounded-md px-4 py-2 text-sm font-bold transition-all duration-200 text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md">
                    <div class="flex items-center">
                        <i data-feather="tool" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                        <span id="textUtilities" class="sidebar-text-hidden ml-3 uppercase">UTILITIES</span>
                    </div>
                    <i id="utilitiesChevron" data-feather="chevron-down" class="sidebar-text-hidden w-4 h-4 stroke-[3] transition-transform duration-200 ml-auto" :class="{'rotate-180': open}"></i>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-collapse id="utilitiesSubmenu" class="sidebar-text-hidden pl-4 mt-1 space-y-1 overflow-hidden">
                    <!-- Signatories -->
                    <a href="#" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200 text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md">
                        <span class="ml-8">SIGNATORIES</span>
                    </a>

                    <!-- Activity Log -->
                    <a href="{{ route('admin_activity_log') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                            {{ request()->routeIs('admin_activity_log')
            ? 'bg-[#002C76] text-white shadow-md'
            : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                        <span class="ml-8">ACTIVITY LOG</span>
                    </a>
                </div>
            </div>

        </nav>
    </div>

    <div class="px-2 pb-4 flex-shrink-0">
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="group flex items-center w-full rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                       bg-[#C5292F] text-white hover:bg-red-700 hover:shadow-lg">
                <i data-feather="log-out" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textLogOut" class="sidebar-text-hidden ml-3">LOGOUT</span>
            </button>
        </form>
    </div>
</aside>