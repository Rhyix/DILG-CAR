@if ($applications->count() > 0)
<div class="rounded-xl border border-[#0D2B70] mt-2 overflow-hidden">
    <div class="bg-[#0D2B70] text-white">
        <table class="w-full text-left border-collapse table-fixed">
            <thead class="bg-[#0D2B70] text-white">
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
    <div>
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
            </tbody>
        </table>
    </div>
</div>
@else
    <div class="text-center text-gray-600 font-montserrat text-lg mt-10">
        <i data-feather="inbox" class="w-12 h-12 mx-auto mb-2 text-gray-400"></i>
        <p class="font-semibold">No applications yet.</p>
        <p class="text-sm text-gray-500">Browse available job vacancies and apply to get started!</p>
    </div>
@endif
