@php
  $statusColor = strtolower($vacancy->status) === 'closed' ? 'bg-red-600' : 'bg-green-600';
@endphp
<!--[160px_1fr_100px_120px_176px_200px_160px]-->
<style>
  [x-cloak] { display: none !important; }
</style>
<div
class="grid grid-cols-6 gap-4 border-2 border-[#0D2B70] rounded-xl py-5 px-6 items-center text-[#0D2B70] select-none">
  <div class="font-extrabold ml-2">{{ $vacancy->plantilla_item_no }}</div>
  <div>
    <p class="font-extrabold">{{ $vacancy->position_title }}</p>
    <p class="text-[#0D2B70]/70 text-[0.9rem] italic">{{ $vacancy->vacancy_type }}</p>
  </div>
  <div class="font-extrabold">₱{{ number_format($vacancy->monthly_salary, 2) }}</div>
  <div class="">{{ \Carbon\Carbon::parse($vacancy->closing_date)->subMinute()->format('n/j/Y g:i A') }}</div>
  <div class="font-extrabold">{{ $vacancy->place_of_assignment }}</div>
  <div class="flex justify-center items-center gap-3 font-normal">
    <span class="w-5 h-5 rounded-full inline-block {{ $statusColor }}"></span>
    <!-- Edit icon -->
    <button onclick="window.location.href='{{ route('vacancies.edit', $vacancy->vacancy_id) }}'" aria-label="Edit vacancy" title="Edit vacancy"
      class="use-loader stroke-[#0D2B70] hover:stroke-[#c5292f] transition">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
        stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" />
      </svg>
    </button>
    <div x-data="{ open: false }">
        <!-- Delete Button -->
        <button type="button"
            @click="open = true"
            class="text-red-600 hover:text-red-800 mt-1"
            title="Delete"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7L5 7M10 11v6M14 11v6M5 7l1.5 12.5A2 2 0 008.5 21h7a2 2 0 002-1.5L19 7M9 7V4h6v3" />
            </svg>
        </button>

        <!-- Modal -->
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div @click.away="open = false"
                class="bg-white rounded-lg p-6 w-full max-w-lg shadow-xl space-y-4 text-center">

                <h2 class="text-xl font-bold text-[#C5292F]">Are you sure you want to delete this vacancy?</h2>
                <p class="text-gray-700 text-lg">"<strong>{{ $vacancy->position_title }}</strong>"</p>
                <p class="text-red-500 text-sm mt-2 font-semibold">Warning: This will also remove the applicants for this vacancy. Please do it with caution.</p>

                <div class="flex justify-center gap-4 mt-4">
                    <form action="{{ route('vacancies.delete', $vacancy->vacancy_id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="use-loader bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                            Yes, Delete
                        </button>
                    </form>
                    <button @click="open = false"
                        class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </button>
                </div>
            </div>
    </div>
</div>
    <!-- View Applicants Button -->
    <button onclick="window.location.href='{{ route('applicants_profile.all', ['vacancy_id' => $vacancy->vacancy_id]) }}'"
      class="use-loader text-blue-600 hover:text-blue-800" title="View Applicants">
      <svg xmlns="ht  tp://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
          stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round"
              d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
      </svg>
      </button>

  </div>
</div>
