@php
@endphp
<style>
  [x-cloak] { display: none !important; }
</style>
<tr class="text-sm text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
  <td class="w-[10%] px-3 py-2 text-center font-semibold">
    <div class="flex items-center justify-center gap-1.5">
      <div class="h-2.5 w-2.5 rounded-full {{ $vacancy->status === 'OPEN' ? 'bg-green-500' : 'bg-red-500' }}"></div>
      {{ $vacancy->vacancy_id }}
    </div>
  </td>
  <td class="w-[25%] px-3 py-2 text-center">
    <p>{{ $vacancy->position_title }}</p>
    <p class="text-xs italic text-[#0D2B70]/70">
      {{ $vacancy->vacancy_type }}@if(filled($vacancy->plantilla_item_no)), <span class="font-bold text-[#0D2B70]">{{ $vacancy->plantilla_item_no }}</span>@endif
    </p>
  </td>
  <td class="w-[15%] px-3 py-2 text-center">&#8369;{{ number_format($vacancy->monthly_salary, 2) }}</td>
  <td class="w-[15%] px-3 py-2 text-center">{{ \Carbon\Carbon::parse($vacancy->closing_date)->format('F j, Y') }}</td>
  <td class="w-[25%] px-3 py-2 text-center">{{ $vacancy->place_of_assignment }}</td>
  <td class="w-[10%] px-3 py-2 text-center">
    <button
      onclick="event.stopPropagation(); window.location.href='{{ route('vacancies.edit', $vacancy->vacancy_id) }}'"
      class="use-loader rounded-md border border-[#0D2B70] px-2.5 py-1 text-xs font-bold text-[#0D2B70] transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md"
      aria-label="Edit Vacancy"
      title="Edit Vacancy"
    >
      Edit
    </button>
  </td>
</tr>
