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
      background-color: #374151; /* Tailwind gray-700 */
      border-radius: 9999px;
    }

    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield; /* Firefox */
    }


    </style>

    @stack('styles')
</head>

<body class="bg-[#F3F8FF] h-screen font-sans font-montserrat text-gray-900 overflow-hidden">

    <!-- App Container: Sidebar + Content -->
    <div class="flex h-screen w-full overflow-hidden">

        {{-- Sidebar --}}
        @include('partials.sidebar_admin')

        {{-- Main Content Scrollable --}}
        <main class="flex-1 overflow-y-auto p-10 pt-8 space-y-10">
            @yield('content')
        </main>

    </div>
</body>

    <!-- JS Scripts -->
    <script>
        feather.replace();

        const sidebar = document.getElementById('sidebar');
        const textElements = [
            "sidebarText",
            "textHome",
            "textJobVacancies",
            "textMyApplications",
            "textPersonalDataSheet",
            "textAboutWebsite",
            "textLogOut",
            "textActivityLog"
        ].map(id => document.getElementById(id));

        const logo = document.querySelector('img[alt="DILG Logo"]');
        const toggleButton = document.getElementById('toggleSidebar');
        let isOpen = localStorage.getItem('sidebarOpen') === 'true'; // Retrieve sidebar state from localStorage

        function openSidebar() {
            sidebar.classList.remove('w-16');
            sidebar.classList.add('w-72');
            logo.classList.remove('logo-small');
            textElements.forEach(el => {
                el.classList.remove('sidebar-text-hidden');
                el.classList.add('sidebar-text-visible');
            });
            isOpen = true;
            localStorage.setItem('sidebarOpen', 'false'); // Save state to localStorage
        }

        function closeSidebar() {
            sidebar.classList.remove('w-72');
            sidebar.classList.add('w-16');
            logo.classList.add('logo-small');
            textElements.forEach(el => {
                el.classList.remove('sidebar-text-visible');
                el.classList.add('sidebar-text-hidden');
            });
            isOpen = false;
            localStorage.setItem('sidebarOpen', 'false'); // Save state to localStorage
        }

        toggleButton?.addEventListener('click', () => {
            if (isOpen) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        window.onload = () => {
            if (isOpen) {
                openSidebar();
            } else {
                closeSidebar();
            }
        };

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
