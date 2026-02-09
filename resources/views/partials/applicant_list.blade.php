<table class="w-full text-left border-collapse">
    <thead>
        <tr class="bg-blue-950 text-white">
            <th class="py-3 px-4 font-semibold">Name</th>
            <th class="py-3 px-4 font-semibold">Job Applied</th>
            <th class="py-3 px-4 font-semibold">Place of Assignment</th>
            <th class="py-3 px-4 font-semibold">Status</th>
            <th class="py-3 px-4 font-semibold">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($applicants as $applicant)
        <tr class="{{ $applicant['status'] === 'Closed' ? 'bg-gray-100 text-gray-500' : 'bg-white' }} border-b">
            <td class="py-3 px-4 font-montserrat text-[#002C76]">{{ $applicant['name'] }}</td>
            <td class="py-3 px-4 font-montserrat">{{ $applicant['job_applied'] }}</td>
            <td class="py-3 px-4 font-montserrat">{{ $applicant['place_of_assignment'] }}</td>
            <td class="py-3 px-4 font-montserrat
                {{ $applicant['status'] === 'Incomplete' ? 'text-orange-500' : '' }}
                {{ $applicant['status'] === 'Complete' ? 'text-green-500' : '' }}
                {{ $applicant['status'] === 'Closed' ? 'text-red-500' : '' }}
                {{ $applicant['status'] === 'Pending' ? 'text-yellow-600' : '' }}">
                {{ $applicant['status'] }}
            </td>
            <td class="py-3 px-4">
                <button onclick="window.location.href='{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}'"
                    class="use-loader border border-red-400 font-montserrat text-black font-semibold px-4 py-2 rounded-full text-sm shadow-md flex items-center gap-2 hover:bg-red-400 hover:text-white transition
                    {{ $applicant['status'] === 'Closed' ? 'hover:bg-gray-400' : '' }}">
                    <i data-feather="eye" class="w-4 h-4 text-black"></i> View Status
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
