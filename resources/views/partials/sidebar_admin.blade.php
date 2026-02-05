<!-- resources/views/partials/sidebar.blade.php -->
<aside id="sidebar"
    class="sidebar sidebar-transition fixed ml-5 mt-5 mb-5 flex flex-col justify-between bg-white text-[#002C76] rounded-xl shadow-lg overflow-hidden w-16 relative">

    <!-- Upper -->
    <div>
        <button id="toggleSidebar" class="p-2 focus:outline-none absolute top-3 left-3 z-20" aria-label="Toggle sidebar">
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

        <nav class="mt-8 space-y-1 px-2 font-montserrat">
            <a href="{{ route('dashboard_admin') }}"
            class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition
                    {{ request()->routeIs('dashboard_admin')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
            <i data-feather="home" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
            <span id="textHome" class="sidebar-text-hidden ml-3">HOME</span>
            </a>
            <a href="{{ route('vacancies_management') }}"
            class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition
                    {{ request()->routeIs('vacancies_management')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
            <i data-feather="archive" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
            <span id="textJobVacancies" class="sidebar-text-hidden ml-3">VACANCIES MANAGEMENT</span>
            </a>

            <a href="{{ route('applications_list') }}"
            class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition
                    {{ request()->routeIs('admin_applications_list')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
            <i data-feather="user" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
            <span id="textMyApplications" class="sidebar-text-hidden ml-3">APPLICATIONS LIST</span>
            </a>

            <a href="{{ route('admin_exam_management') }}"
            class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition
                    {{ request()->routeIs('admin_exam_management')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
            <i data-feather="file-text" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
            <span id="textPersonalDataSheet" class="sidebar-text-hidden ml-3">EXAM MANAGEMENT</span>
            </a>

            <a href="{{ route('generate_report') }}"
            class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition
                    {{ request()->routeIs('generate_report')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
            <i data-feather="bar-chart-2" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
            <span id="textGenerateReport" class="ml-3">GENERATE REPORT</span>
            </a>

            <a href="{{ route('admin_account_management') }}"
            class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition
                    {{ request()->routeIs('admin_account_management')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
            <i class="fa-solid fa-wrench class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
            <span id="textAboutWebsite" class="sidebar-text-hidden ml-3">SYSTEM USER MANAGEMENT</span>
            </a>
            <a href="{{ route('admin_activity_log') }}"
            class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition
                    {{ request()->routeIs('admin_activity_log')
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
            <i class="fa-solid fa-clipboard-list text-lg flex-shrink-0"></i>
            <span id="textActivityLog" class="sidebar-text-hidden ml-3">ACTIVITY LOG</span>
            </a>

        </nav>
    </div>

    <div class="px-2 pb-4">
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit"
                class="group flex items-center w-full rounded-md px-4 py-2 text-sm font-bold transition
                       bg-[#C5292F] text-white hover:bg-red-700">
                <i data-feather="log-out" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textLogOut" class="sidebar-text-hidden ml-3">LOGOUT</span>
            </button>
        </form>
    </div>
</aside>
