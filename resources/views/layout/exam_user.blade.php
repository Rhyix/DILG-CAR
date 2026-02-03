<!DOCTYPE html>
<html lang="en">

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">


<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'DILG RHRMSPB')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-[#F3F8FF] min-h-screen flex flex-col text-gray-900">
    <div class="flex flex-col h-screen overflow-hidden">

        {{-- Header --}}
        <header class="px-10 py-4 flex items-center gap-4">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo"
                class="h-14 w-14 rounded-full border border-gray-300" />
            <div>
                <div class="text-sm text-[#002C76] font-bold uppercase tracking-wide leading-snug">
                    DILG - CAR
                </div>
                <div class="text-xl text-[#002C76] font-bold uppercase tracking-tight leading-tight">
                    RHRMSPB SYSTEM
                </div>
            </div>
        </header>

        {{-- Title Bar --}}
        <div class="px-10">
            <h1
                class="flex items-center gap-3 bg-[#002C76] text-white text-xl font-bold font-montserrat px-6 py-4 rounded-xl w-full shadow-sm select-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                    class="bi bi-file-text" viewBox="0 0 16 16">
                    <path
                        d="M5 4a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5A.5.5 0 0 1 5 4zm0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5A.5.5 0 0 1 5 6zm0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5A.5.5 0 0 1 5 8z" />
                    <path
                        d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6.5L14 4.5zM13.5 5H10a1 1 0 0 1-1-1V.5H4a1 1 0 0 0-1 1V14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V5z" />
                </svg>
                <span>Examination</span>
            </h1>
        </div>

        {{-- Main content area --}}
        <div class="flex-1 overflow-auto px-10">
            <main class="min-h-[calc(100vh-8.5rem)] p-6 pb-10 px-5 space-y-10">
                @yield('content')
            </main>
        </div>

    </div>

    <script>
        feather.replace();
    </script>

    @stack('scripts')
</body>

</html>
