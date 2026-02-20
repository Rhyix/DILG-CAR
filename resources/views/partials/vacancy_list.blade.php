@forelse ($vacancies as $vacancy)
    <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
        <td class="py-4 px-6 w-[25%]">
            <p class="font-medium">{{ $vacancy->position_title }}</p>
            <p class="text-[#0D2B70]/70 text-[0.9rem] italic">{{ $vacancy->vacancy_type }}</p>
        </td>
        <td class="py-4 px-6 w-[12%]">₱{{ number_format($vacancy->monthly_salary, 2) }}</td>
        <td class="py-4 px-6 w-[18%]">{{ $vacancy->place_of_assignment }}</td>
        <td class="py-4 px-6 text-center w-[15%]">
            @php
                $closing = \Carbon\Carbon::parse($vacancy->closing_date);
                $daysLeft = now()->diffInDays($closing, false);
                $isUrgent = $vacancy->status === 'OPEN' && $daysLeft >= 0 && $daysLeft <= 7;
            @endphp
            <div class="flex flex-col items-center">
                <span class="font-semibold">{{ $closing->format('F j, Y') }}</span>
                @if($isUrgent)
                    <span class="text-xs text-red-600 font-bold flex items-center gap-1 mt-1 animate-pulse">
                        <i data-feather="alert-circle" class="w-3 h-3"></i>
                        Expiring Soon
                    </span>
                @endif
            </div>
        </td>
        <td class="py-4 px-6 text-center w-[10%]">
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $vacancy->status === 'OPEN' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $vacancy->status }}
            </span>
        </td>
        <td class="py-4 px-6 text-center w-[10%]">
            @php
                $examStatus = 'Unscheduled';
                $examBadge = 'bg-gray-100 text-gray-800';
                
                if ($vacancy->examDetail && $vacancy->examDetail->date) {
                    try {
                        $date = $vacancy->examDetail->date;
                        $time = $vacancy->examDetail->time;
                        $timeEnd = $vacancy->examDetail->time_end;
                        
                        $startDateTime = \Carbon\Carbon::parse($date . ' ' . $time);
                        $endDateTime = $timeEnd 
                            ? \Carbon\Carbon::parse($date . ' ' . $timeEnd)
                            : $startDateTime->copy()->addHours(2); // Default 2 hours if no end time
                        
                        $now = \Carbon\Carbon::now();
                        
                        if ($now->lt($startDateTime)) {
                            $examStatus = 'Scheduled';
                            $examBadge = 'bg-blue-100 text-blue-800';
                        } elseif ($now->between($startDateTime, $endDateTime)) {
                            $examStatus = 'Ongoing';
                            $examBadge = 'bg-yellow-100 text-yellow-800'; // Or animate pulse
                        } else {
                            $examStatus = 'Completed';
                            $examBadge = 'bg-purple-100 text-purple-800';
                        }
                    } catch (\Exception $e) {
                        // Keep unscheduled on error
                    }
                }
            @endphp
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $examBadge }}">
                {{ $examStatus }}
            </span>
        </td>
        <td class="py-4 px-6 text-center w-[10%]">
            <button
                onclick="window.location.href='{{ route('job_description', $vacancy->vacancy_id) }}'"
                class="use-loader text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all 
                duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md inline-flex items-center gap-2 mx-auto">
                <i data-feather="eye" class="w-4 h-4"></i>
                <span>View</span>
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-10 text-gray-500 text-xl">
            No Job Vacancy
        </td>
    </tr>
@endforelse
