@extends('layout.app')

@section('title', 'My Applications')

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DILG - Job Vacancies</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>

@section('content')
<body class="bg-[#F3F8FF] min-h-screen font-sans text-gray-900 overflow-x-hidden">
    <div class="flex min-h-screen w-full">
        <!-- Main Content -->
        <main class="w-full space-y-6 main-content-mobile">

        <!-- Header Section -->
            <section class="flex-none flex items-center space-x-4 max-w-full">
                <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
                    <span class="whitespace-nowrap text-[#0D2B70]">My Applications</span>
                </h1>
            </section>
            
            <div class="flex flex-row gap-4 sort-section-mobile justify-end">
                <p class="text-lg font-bold font-montserrat text-black-600 ml-3 sm:ml-0">SORT</p>
                <select id="sortMyApplications" class="border border-gray-300 rounded-lg border-2 border-red-400 px-4 py-1 sm:py-2 text-xs sm:text-sm font-montserrat">
                    <option value="latest">LATEST</option>
                    <option value="oldest">OLDEST</option>
                </select>
            </div>
            
            <!-- Application List -->
            <section id="applicationListContainer" class="space-y-6 application-list-mobile">
                @include('partials.application_list_container', ['applications' => $applications])
            </section>
            
            @include('partials.loader')
        </main>
    </div>
</body>
@endsection
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script>
        document.getElementById('sortMyApplications').addEventListener('change', function () {
            const sortOrder = this.value;

            axios.get('{{ route("my_applications.sort") }}', {
                params: {
                    sort_order: sortOrder
                }
            })
            .then(response => {
                const container = document.getElementById('applicationListContainer');
                container.innerHTML = response.data;
                feather.replace(); // re-render feather icons
            })
            .catch(error => {
                console.error("Failed to sort applications:", error);
            });
        });
        </script>

