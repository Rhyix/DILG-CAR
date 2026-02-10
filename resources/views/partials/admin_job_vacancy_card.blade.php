@php
@endphp
<style>
  [x-cloak] { display: none !important; }
</style>
<tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
  <td class="py-4 px-6 ml-2">
    <div class="flex items-center gap-2">
      <div class="w-3 h-3 rounded-full {{ $vacancy->status === 'OPEN' ? 'bg-green-500' : 'bg-red-500' }}"></div>
      {{ $vacancy->plantilla_item_no }}
    </div>
  </td>
  <td class="py-4 px-6">
    <p>{{ $vacancy->position_title }}</p>
    <p class="text-[#0D2B70]/70 text-[0.9rem] italic">{{ $vacancy->vacancy_type }}</p>
  </td>
  <td class="py-4 px-6">₱{{ number_format($vacancy->monthly_salary, 2) }}</td>
  <td class="py-4 px-6">{{ \Carbon\Carbon::parse($vacancy->closing_date)->subMinute()->format('n/j/Y g:i A') }}</td>
  <td class="py-4 px-6">{{ $vacancy->place_of_assignment }}</td>
  <td class="py-4 px-6 text-center">
<div class="flex justify-center items-center gap-3 font-normal">
    <!-- View Applicants Icon Button -->
    <button onclick="window.location.href='{{ route('applicants_profile.all', ['vacancy_id' => $vacancy->vacancy_id]) }}'"
            class="use-loader text-[#0D2B70]  p-2 rounded-full text-sm
            transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
            hover:scale-110 hover:bg-[#0D2B70] hover:text-white hover:shadow-md"
            aria-label="View Applicants"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12c0 0 4.5-6.75 9.75-6.75S21.75 12 21.75 12s-4.5 6.75-9.75 6.75S2.25 12 2.25 12zm9.75 3a3 3 0 100-6 3 3 0 000 6z" />
        </svg>
    </button>
    <!-- Circular Edit Icon Button -->
    <button onclick="event.stopPropagation(); window.location.href='{{ route('vacancies.edit', $vacancy->vacancy_id) }}'" 
            class="use-loader hover:scale-110 transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]"
            aria-label="Edit"
    >
        <!-- Pencil/Edit Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6-6 3 3-6 6H9v-3z" />
        </svg>
    </button>
</div>

  </td>
</tr>
