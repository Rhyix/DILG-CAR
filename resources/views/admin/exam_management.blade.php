@extends('layout.admin')
@section('title', 'DILG - Admin Exam Management')
@section('content')

<main class="w-full space-y-6">

    <!-- Header with back arrow and title -->
    <section class="flex items-center space-x-4 mb-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full bg-[#0D2B70] text-white rounded-xl text-2xl font-extrabold font-montserrat px-8 py-4 tracking-wide select-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
            </svg>
            <span class="whitespace-nowrap">EXAM MANAGEMENT</span>
        </h1>
    </section>

    <form onsubmit="return false;" class="relative w-full max-w-xs">
        <input id="searchInput" type="search" placeholder="Search" aria-label="Search"
            class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1" />
        <svg xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
        </svg>
    </form>

    <!-- Table Header -->
    <section class="grid grid-cols-[1.4fr_3.2fr_3.1fr_1.9fr_2.5fr] gap-4 bg-[#0D2B70] text-white font-bold rounded-xl py-5 px-6 select-none overflow-hidden">
        <div class="flex items-center justify-center">VACANCY ID</div>
        <div class="flex items-center justify-center">JOB TITLE</div>
        <div class="flex items-center justify-center">EXAM ID</div>
        <div class="flex items-center justify-center"></div>
        <div class="flex items-center justify-center gap-2">
            
            <span>Actions</span>
        </div>
    </section>

    <!-- Backend to be implemented: GO TO THEIR OWN RESPECTIVE LINK -->
    <section class="space-y-4">
        @foreach ($vacancies as $vacancy)
        <div class="grid grid-cols-[1.2fr_2fr_1.5fr_auto_1fr] gap-4 border-2 border-[#0D2B70] rounded-xl py-5 px-6 items-center text-[#0D2B70] select-none overflow-x-hidden">
            <div class="font-extrabold">{{ $vacancy->vacancy_id }}</div>
                <div>
                    <p class="font-extrabold">{{ $vacancy->position_title }}</p>
                    <p class="text-[#0D2B70]/70 text-[0.9rem] italic">{{ $vacancy->vacancy_type }}</p>
                </div>
            <div class="text-center font-semibold">{{ $vacancy->vacancy_id }}-EXAM</div>

            <!-- Edit -->
            <div class="text-center w-fit mx-auto">
                <button onclick="window.location.href='{{ route('admin.exam.edit', $vacancy->vacancy_id) }}'" class="use-loader bg-[#00127.0.0.1] hover:opacity-80 transition text-white font-semibold rounded-full flex items-center gap-2 px-4 py-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    Edit Questions
                </button>
            </div>

            <!-- Copy Link + Manage Buttons -->
            <div class="flex items-center justify-center gap-2">
                {{-- <!-- Send Link -->
                <button onclick="window.location.href='exam/{{ $vacancy->vacancy_id }}'"
                    class="bg-[#2559B1] hover:opacity-80 transition text-white font-semibold rounded-full flex items-center gap-2 px-4 py-2 text-sm whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V9.75L16.5 8.25 12 3.75H10.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75v6h6M10.5 13.5h3m-3 3h3" />
                    </svg>
                    Send Link
                </button> --}}

                <!-- Manage -->
                <button onclick="window.location.href='{{ route('admin.manage_exam', $vacancy->vacancy_id) }}'" class="use-loader bg-[#002C76] hover:opacity-80 transition text-white font-semibold rounded-full flex items-center gap-2 px-3 py-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281a1.1 1.1 0 0 0 .865.997l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827a1.1 1.1 0 0 0 0 1.983l1.004.827a1.125 1.125 0 0 1 .26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456a1.1 1.1 0 0 0-.865.997l-.213 1.281a1.125 1.125 0 0 1-1.11.94h-2.594a1.125 1.125 0 0 1-1.11-.94l-.213-1.281a1.1 1.1 0 0 0-.865-.997l-1.217.456a1.125 1.125 0 0 1-1.369-.491l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827a1.1 1.1 0 0 0 0-1.983l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456a1.1 1.1 0 0 0 .865-.997l.214-1.28Z" />
                    </svg>
                    Manage
                </button>
            </div>

        </div>
        @endforeach
    </section>

    <script>
        const searchInput = document.getElementById('searchInput');
        let debounceTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                fetchVacancies(this.value);
            }, 300); // debounce delay in ms
        });

        function fetchVacancies(query) {
            fetch(`/admin/exam_management?search=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                renderVacancies(data);
            })
            .catch(error => console.error('Error:', error));
        }

        function renderVacancies(vacancies) {
            const container = document.querySelector('section.space-y-4');
            container.innerHTML = '';

            vacancies.forEach(vacancy => {
                container.innerHTML += `
                <div class="grid grid-cols-[1.2fr_2fr_1.5fr_auto_1fr] gap-4 border-2 border-[#0D2B70] rounded-xl py-5 px-6 items-center text-[#0D2B70] select-none overflow-x-hidden">
                    <div class="font-extrabold">${vacancy.vacancy_id}</div>
                    <div>
                        <p class="font-extrabold">${vacancy.position_title}</p>
                        <p class="text-[#0D2B70]/70 text-[0.9rem] italic">${vacancy.vacancy_type}</p>
                    </div>
                    <div class="text-center font-semibold">${vacancy.vacancy_id}-EXAM</div>

                    <div class="text-center w-fit mx-auto">
                        <button onclick="window.location.href='/admin/exam/${vacancy.vacancy_id}/edit'" class="bg-[#00127.0.0.1] hover:opacity-80 transition text-white font-semibold rounded-full flex items-center gap-2 px-4 py-2 text-sm">
                            Edit Questions
                        </button>
                    </div>

                    <div class="flex items-center justify-center gap-2">
                        <button onclick="window.location.href='exam/${vacancy.vacancy_id}'"
                            class="bg-[#2559B1] hover:opacity-80 transition text-white font-semibold rounded-full flex items-center gap-2 px-3 py-2 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V9.75L16.5 8.25 12 3.75H10.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75v6h6M10.5 13.5h3m-3 3h3" />
                            </svg>
                            Send Link
                        </button>

                        <button onclick="window.location.href='/admin/exam_management/manage_exam/${vacancy.vacancy_id}'" class="bg-[#002C76] hover:opacity-80 transition text-white font-semibold rounded-full flex items-center gap-2 px-3 py-2 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281a1.1 1.1 0 0 0 .865.997l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827a1.1 1.1 0 0 0 0 1.983l1.004.827a1.125 1.125 0 0 1 .26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456a1.1 1.1 0 0 0-.865.997l-.213 1.281a1.125 1.125 0 0 1-1.11.94h-2.594a1.125 1.125 0 0 1-1.11-.94l-.213-1.281a1.1 1.1 0 0 0-.865-.997l-1.217.456a1.125 1.125 0 0 1-1.369-.491l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827a1.1 1.1 0 0 0 0-1.983l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456a1.1 1.1 0 0 0 .865-.997l.214-1.28Z" />
                            </svg>
                            Manage
                        </button>
                    </div>
                </div>
                `;
            });
        }
    </script>
    @include('partials.loader')
</main>

@endsection
