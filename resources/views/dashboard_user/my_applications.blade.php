@extends('layout.app')

@section('title', 'My Applications')

@push('styles')
<style>
    .no-page-scroll {
        height: calc(100vh - 6rem);
    }
</style>
@endpush

@section('content')
<div class="w-full no-page-scroll flex flex-col overflow-hidden">

            <!-- Header Section - Fixed -->
            <section class="flex-none pt-4 px-4 sm:px-6 lg:px-8">
                <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-4xl font-montserrat py-2 tracking-wide select-none">
                    <span class="whitespace-nowrap text-[#0D2B70]">My Applications</span>
                </h1>
            </section>
            
            <!-- Sort Section - Fixed -->
            <div class="flex-none flex flex-row items-center gap-4 sort-section-mobile justify-end px-4 sm:px-6 lg:px-8 mt-2">
                <p class="text-lg font-bold font-montserrat text-gray-600">SORT</p>
                <select id="sortMyApplications" class="border-2 border-red-400 rounded-lg px-4 py-1 sm:py-2 text-xs sm:text-sm font-montserrat bg-white">
                    <option value="latest">LATEST</option>
                    <option value="oldest">OLDEST</option>
                </select>
            </div>
            
            <!-- Table Container - Matches Browse Job Vacancies layout -->
            <div class="flex-1 min-h-0 px-4 sm:px-6 lg:px-8 pb-4 mt-4">
                <div id="applicationListContainer" class="rounded-xl border border-[#0D2B70] h-full flex flex-col overflow-hidden">
                    <div class="flex-none bg-[#0D2B70] text-white">
                        <table class="w-full text-left border-collapse table-fixed">
                            <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                                <tr>
                                    <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[20%]">Application No.</th>
                                    <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[30%]">Position Title</th>
                                    <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[25%]">Place of Assignment</th>
                                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[10%]">Status</th>
                                    <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[15%]">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="flex-1 overflow-y-auto min-h-0">
                        <table class="w-full text-left border-collapse table-fixed">
                            <tbody class="divide-y divide-[#0D2B70]">
                                @foreach ($applications as $application)
                                    <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                        <td class="py-4 px-6 w-[20%]">
                                            <span class="font-semibold">{{ $application->id ?? $application->application_no ?? 'N/A' }}</span>
                                        </td>
                                        <td class="py-4 px-6 w-[30%]">
                                            <p class="font-medium">{{ $application->vacancy->position_title ?? 'Position Title Unavailable' }}</p>
                                            <p class="text-[#0D2B70]/70 text-[0.9rem] italic">{{ $application->vacancy->vacancy_type ?? '' }}</p>
                                        </td>
                                        <td class="py-4 px-6 w-[25%]">{{ $application->vacancy->place_of_assignment ?? 'N/A' }}</td>
                                        <td class="py-4 px-6 text-center w-[10%]">
                                            @php
                                                $status = $application->status;
                                                $badge = 'bg-gray-100 text-gray-800';
                                                if ($status === 'Complete') $badge = 'bg-green-100 text-green-800';
                                                elseif ($status === 'Incomplete') $badge = 'bg-orange-100 text-orange-800';
                                                elseif ($status === 'Closed') $badge = 'bg-red-100 text-red-800';
                                                elseif ($status === 'Pending') $badge = 'bg-yellow-100 text-yellow-800';
                                            @endphp
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                                {{ $status }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center w-[15%]">
                                            <button
                                                onclick="window.location.href='{{ route('application_status', [$application->user_id, $application->vacancy_id]) }}'"
                                                class="use-loader text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all 
                                                duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md inline-flex items-center gap-2 mx-auto">
                                                <i data-feather="eye" class="w-4 h-4"></i>
                                                <span>View</span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($applications->count() === 0)
                                    <tr>
                                        <td colspan="5" class="py-6 px-6 text-center text-gray-600">
                                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-50 rounded-full mb-3">
                                                <i data-feather="inbox" class="w-8 h-8 text-gray-300"></i>
                                            </div>
                                            <div class="font-bold mb-1">No applications yet</div>
                                            <div class="text-sm text-gray-500">Browse job vacancies to get started.</div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            @include('partials.loader')
</div>
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
        container.outerHTML = response.data;
        feather.replace(); // re-render feather icons
    })
    .catch(error => {
        console.error("Failed to sort applications:", error);
    });
});
</script>
