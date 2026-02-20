@extends('layout.app')

@section('title', 'My Applications')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="px-4 pb-8 sm:px-8">
        <!-- Header Section -->
        <div class="flex-none flex items-center mb-6 sm:mb-10 pace-x-4 max-w-full">
            <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-2xl sm:text-4xl font-montserrat py-2 tracking-wide select-none">
                <span class="whitespace-nowrap text-[#0D2B70]">My Applications</span>
            </h1>
        </div>

        <div class="flex-none flex flex-row gap-4 sort-section-mobile justify-end mb-4">
            <div x-data="{ open: false }" class="relative">
                <button
                    @click="open = !open"
                    class="font-semibold flex items-center px-4 py-2 bg-white text-[#0D2B70] rounded-md hover:bg-[#0D2B70] transition whitespace-nowrap hover:text-white hover:shadow-md border border-[#0D2B70]"
                >
                    <span>SORT: </span>
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <div
                    x-show="open"
                    x-cloak
                    @click.away="open = false"
                    x-transition
                    class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-xl shadow-lg z-50"
                >
                    <a href="#" 
                    @click.prevent="document.getElementById('sortMyApplications').value = 'latest'; document.getElementById('sortMyApplications').dispatchEvent(new Event('change')); open = false;"
                    class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold">
                        LATEST
                    </a>
                    <a href="#"
                    @click.prevent="document.getElementById('sortMyApplications').value = 'oldest'; document.getElementById('sortMyApplications').dispatchEvent(new Event('change')); open = false;"
                    class="block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold">
                        OLDEST
                    </a>
                </div>
            </div>
            
            <!-- Keep your original select (now visually hidden but functional) -->
            <select id="sortMyApplications" class="sr-only">
                <option value="latest">LATEST</option>
                <option value="oldest">OLDEST</option>
            </select>
        </div>
            
        <!-- Application List -->
        <div id="applicationListContainer" class="space-y-6 application-list-mobile">
            @include('partials.application_list_container', ['applications' => $applications])
        </div>
        
        @include('partials.loader')
    </div>
@endsection

@push('scripts')
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
@endpush

