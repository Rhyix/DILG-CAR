@forelse ($vacancies as $vacancy)
    <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
        <td class="py-4 px-6 w-[35%]">
            <p class="font-medium">{{ $vacancy->position_title }}</p>
            <p class="text-[#0D2B70]/70 text-[0.9rem] italic">{{ $vacancy->vacancy_type }}</p>
        </td>
        <td class="py-4 px-6 w-[15%]">₱{{ number_format($vacancy->monthly_salary, 2) }}</td>
        <td class="py-4 px-6 w-[25%]">{{ $vacancy->place_of_assignment }}</td>
        <td class="py-4 px-6 text-center w-[10%]">
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $vacancy->status === 'OPEN' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $vacancy->status }}
            </span>
        </td>
        <td class="py-4 px-6 text-center w-[15%]">
            <button
                onclick="window.location.href='{{ route('job_description', $vacancy->vacancy_id) }}'"
                class="use-loader text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md inline-flex items-center gap-2 mx-auto">
                <i data-feather="eye" class="w-4 h-4"></i>
                <span>View</span>
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center py-10 text-gray-500 text-xl">
            No Job Vacancy
        </td>
    </tr>
@endforelse
