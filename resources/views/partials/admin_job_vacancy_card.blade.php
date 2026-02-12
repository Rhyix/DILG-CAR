@php
@endphp
<!--[160px_1fr_100px_120px_176px_200px_160px]-->
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
        <!-- Edit Icon Button  -->
        <button 
            onclick="event.stopPropagation(); window.location.href='{{ route('vacancies.edit', $vacancy->vacancy_id) }}'"
            class="use-loader py-1 px-3 rounded-md text-xl text-[#0D2B70] hover:text-[#0D2B70]/70
                  transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)]
                  hover:scale-110"
            aria-label="Edit Vacancy"
            title="Edit Vacancy">
            <i class="fa-solid fa-pen-to-square h-10 w-10"></i>
        </button>
    </div>
  </td>
</tr>
