@php
@endphp
<!--[160px_1fr_100px_120px_176px_200px_160px]-->
<style>
  [x-cloak] { display: none !important; }
</style>
<tr
  onclick="window.location.href='{{ route('applicants_profile.all', ['vacancy_id' => $vacancy->vacancy_id]) }}'"
  class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200 cursor-pointer"
>
  <td class="py-4 px-6 ml-2">{{ $vacancy->plantilla_item_no }}</td>
  <td class="py-4 px-6">
    <p>{{ $vacancy->position_title }}</p>
    <p class="text-[#0D2B70]/70 text-[0.9rem] italic">{{ $vacancy->vacancy_type }}</p>
  </td>
  <td class="py-4 px-6">₱{{ number_format($vacancy->monthly_salary, 2) }}</td>
  <td class="py-4 px-6">{{ \Carbon\Carbon::parse($vacancy->closing_date)->subMinute()->format('n/j/Y g:i A') }}</td>
  <td class="py-4 px-6">{{ $vacancy->place_of_assignment }}</td>
  <td class="py-4 px-6 text-center">
    <div class="flex justify-center items-center gap-3 font-normal">
        <!-- Manage Button -->
        <button onclick="event.stopPropagation(); window.location.href='{{ route('vacancies.edit', $vacancy->vacancy_id) }}'" 
                class="use-loader text-[#0D2B70] border border-[#0D2B70] py-1 px-4 rounded-md text-sm
                transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
                hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md"
        >
            Manage
        </button>
    </div>
  </td>
</tr>
