<!-- resources/views/partials/sidebar.blade.php -->
@php
    $simple = in_array(request()->input('simple'), [1, '1', true, 'true'], true);
@endphp
<aside id="sidebar"
    class="sidebar sidebar-transition fixed ml-5 mt-5 mb-5 flex flex-col justify-between bg-white text-[#002C76] rounded-xl shadow-lg {{ $simple ? 'overflow-y-auto w-72' : 'overflow-hidden w-16' }} relative z-60 h-[95vh]">

    <!-- Toggle Button -->
    <button id="toggleSidebar" class="p-2 focus:outline-none absolute top-3 left-3 z-20" aria-label="Toggle sidebar">
        <i data-feather="menu" class="w-5 h-5 stroke-[3]"></i>
    </button>

    <!-- Upper Section -->
    <div>
        <a class="flex items-center gap-2 pt-14 px-2">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo"
                class="h-12 w-12 rounded-full border border-white flex-shrink-0 logo-transition" />
            <div id="sidebarText" class="{{ $simple ? 'sidebar-text-visible' : 'sidebar-text-hidden' }} whitespace-nowrap overflow-hidden">
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

            <div class="w-full">
                <div class="flex items-center justify-between w-full rounded-md px-4 py-2 text-sm font-bold transition
                    {{ (request()->routeIs('display_c1') || request()->routeIs('display_c2') || request()->routeIs('display_c3') || request()->routeIs('display_c4') || request()->routeIs('display_wes') || request()->routeIs('display_c5'))
                        ? 'bg-[#002C76] text-white'
                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <a href="{{ route('display_c1') }}" class="flex items-center use-loader">
                        <i data-feather="file-text" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                        <span id="textPersonalDataSheet" class="sidebar-text-hidden ml-3">PERSONAL DATA SHEET</span>
                    </a>
                    <button type="button" id="pdsToggle" aria-expanded="{{ (request()->routeIs('display_c1') || request()->routeIs('display_c2') || request()->routeIs('display_c3') || request()->routeIs('display_c4') || request()->routeIs('display_wes') || request()->routeIs('display_c5')) ? 'true' : 'false' }}" class="ml-2">
                        <i id="pdsCaret" data-feather="chevron-down" class="w-4 h-4 stroke-[3]"></i>
                    </button>
                </div>
                <div id="pdsMenu" class="{{ (request()->routeIs('display_c1') || request()->routeIs('display_c2') || request()->routeIs('display_c3') || request()->routeIs('display_c4') || request()->routeIs('display_wes') || request()->routeIs('display_c5')) ? '' : 'hidden' }} mt-1 pl-10 space-y-1">
                    <a href="{{ route('display_c1', ['simple' => 1]) }}"
                        class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition
                            {{ request()->routeIs('display_c1')
                                ? 'bg-[#002C76] text-white'
                                : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                        <i data-feather="user" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                        <span class="ml-3">PERSONAL INFORMATION</span>
                    </a>
                    <a href="{{ route('display_c2', ['simple' => 1]) }}"
                        class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition
                            {{ request()->routeIs('display_c2')
                                ? 'bg-[#002C76] text-white'
                                : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                        <i data-feather="briefcase" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                        <span class="ml-3">WORK EXPERIENCE</span>
                    </a>
                    <a href="{{ route('display_c3', ['simple' => 1]) }}"
                        class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition
                            {{ request()->routeIs('display_c3')
                                ? 'bg-[#002C76] text-white'
                                : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                        <i data-feather="book-open" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                        <span class="ml-3">LEARNING &amp; DEVELOPMENT</span>
                    </a>
                    <a href="{{ route('display_c4', ['simple' => 1]) }}"
                        class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition
                            {{ request()->routeIs('display_c4')
                                ? 'bg-[#002C76] text-white'
                                : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                        <i data-feather="info" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                        <span class="ml-3">OTHER INFORMATION</span>
                    </a>
                    <a href="{{ route('display_wes', ['simple' => 1]) }}"
                        class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition
                            {{ request()->routeIs('display_wes')
                                ? 'bg-[#002C76] text-white'
                                : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                        <i data-feather="briefcase" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                        <span class="ml-3">WORK EXPERIENCE SHEET</span>
                    </a>
                    <a href="{{ route('display_c5', ['simple' => 1]) }}"
                        class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition
                            {{ request()->routeIs('display_c5')
                                ? 'bg-[#002C76] text-white'
                                : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                        <i data-feather="upload" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                        <span class="ml-3">UPLOAD PDF</span>
                    </a>
                </div>
            </div>

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
        const pdsToggle = document.getElementById('pdsToggle');
        const pdsMenu = document.getElementById('pdsMenu');
        const pdsCaret = document.getElementById('pdsCaret');
        if (pdsToggle && pdsMenu && pdsCaret) {
            if (!pdsMenu.classList.contains('hidden')) {
                pdsCaret.classList.add('rotate-180');
            }
            pdsToggle.addEventListener('click', () => {
                pdsMenu.classList.toggle('hidden');
                pdsCaret.classList.toggle('rotate-180');
            });
        }

        // Ensure sidebar stays open and allow scroll when PDS submenu is open
        const sidebarEl = document.getElementById('sidebar');
        const toggleButton = document.getElementById('toggleSidebar');
        const logo = document.querySelector('img[alt="DILG Logo"]');
        const textElements = [
            "sidebarText", "textHome", "textJobVacancies", "textMyApplications",
            "textPersonalDataSheet", "textAboutWebsite", "textWorkExperience", "textLogOut"
        ].map(id => document.getElementById(id));

        const isSimple = {{ $simple ? 'true' : 'false' }};

        function openSidebarLocal() {
            sidebarEl?.classList.remove('w-16');
            sidebarEl?.classList.add('w-72');
            logo?.classList.remove('logo-small');
            textElements.forEach(el => {
                el?.classList.remove('sidebar-text-hidden');
                el?.classList.add('sidebar-text-visible');
            });
        }

        function closeSidebarLocal() {
            sidebarEl?.classList.remove('w-72');
            sidebarEl?.classList.add('w-16');
            logo?.classList.add('logo-small');
            if (!isSimple) {
                textElements.forEach(el => {
                    el?.classList.remove('sidebar-text-visible');
                    el?.classList.add('sidebar-text-hidden');
                });
            }
        }

        if (isSimple) {
            openSidebarLocal();
            sidebarEl?.classList.add('overflow-y-auto');
        }

        toggleButton?.addEventListener('click', () => {
            if (sidebarEl?.classList.contains('w-72')) {
                closeSidebarLocal();
            } else {
                openSidebarLocal();
            }
        });
    });
</script>
