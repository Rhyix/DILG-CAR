@extends('layout.admin')
@section('title', 'DILG - Manage Exam')
@section('content')

<main class="w-full h-full min-h-0 mx-auto flex flex-col gap-2 overflow-hidden px-4 lg:px-0">
    <!-- header -->
    <section class="flex-none flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3 max-w-full border-b border-[#0D2B70] pb-2">
        <div class="flex items-center gap-4">
            <button aria-label="Back" onclick="window.location.href='{{ route('admin_exam_management') }}'" class="use-loader group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h1 class="flex items-center gap-3 py-2 tracking-wide select-none">
                <span class="text-[#0D2B70] text-2xl md:text-3xl lg:text-4xl font-montserrat">Exam Overview</span>
            </h1>
        </div>

        <!-- EXAM STATUS BANNER (Compact) -->
        @php
            // Determine exam status
            $isExamActive = false;
            $isExamCompleted = false; // New flag
            $statusMessage = '';
            $statusClass = '';
            $isExamDay = false;
            $isBeforeStart = false;
            $isWithinOneHourBeforeStart = false;
            $qualifiedCount = isset($qualifiedApplicants) ? $qualifiedApplicants->count() : 0;
            $lobbyCount = isset($participants) ? $participants->count() : 0;
            $questionsCount = \App\Models\ExamItems::where('vacancy_id', $vacancy->vacancy_id)->count();
            $hasQuestions = $questionsCount > 0;

            if(isset($examDetails->date) && isset($examDetails->time)) {
                 $startDateTime = \Carbon\Carbon::parse($examDetails->date . ' ' . $examDetails->time);
                 
                 // Use time_end if available, otherwise fallback to duration
                 if (isset($examDetails->time_end)) {
                     $endDateTime = \Carbon\Carbon::parse($examDetails->date . ' ' . $examDetails->time_end);
                 } else {
                     $endDateTime = $startDateTime->copy()->addMinutes($examDetails->duration ?? 0);
                 }
                 
                 $now = now();
                 $isExamDay = \Carbon\Carbon::parse($examDetails->date)->isSameDay($now);
                 $isBeforeStart = $now->lt($startDateTime);
                 $oneHourBefore = $startDateTime->copy()->subHour();
                 $isWithinOneHourBeforeStart = $now->between($oneHourBefore, $startDateTime);

                 if ($now->gt($endDateTime)) {
                     // Current time is after end time
                     $isExamCompleted = true; // Set completed flag
                     $statusMessage = 'Exam Completed';
                     $statusClass = 'bg-green-100 text-green-800 border-green-400';
                 } elseif (($examDetails->is_started ?? false) || $now->between($startDateTime, $endDateTime)) {
                     // Exam is explicitly started OR current time is within window
                     $isExamActive = true;
                     $statusMessage = 'Exam in Progress';
                     $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-400';
                 } elseif ($now->lt($startDateTime)) {
                     // Current time is before start time (Future)
                     $statusMessage = 'Exam Scheduled';
                     $statusClass = 'bg-blue-100 text-blue-800 border-blue-400';
                 }
            } else {
                // Default status if details are not fully set
                $statusMessage = 'Not Scheduled';
                $statusClass = 'bg-gray-100 text-gray-800 border-gray-400';
            }
        @endphp

        @if($statusMessage)
            <div class="px-4 py-1 border-l-4 rounded shadow-sm flex items-center gap-3 {{ $statusClass }} lg:mr-4 w-full lg:w-auto">
                <div class="flex items-center gap-2">
                    @if($isExamActive)
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                        </span>
                    @endif
                    <span class="font-bold uppercase text-xs tracking-wide">{{ $statusMessage }}</span>
                </div>
                @if($isExamActive || $isExamCompleted)
                    <span class="text-[10px] font-semibold opacity-80 hidden md:inline">Editing disabled</span>
                @endif
            </div>
        @endif
        <!-- END EXAM STATUS BANNER -->
    </section>  

    <!-- OLD SCHEDULE -->
    <section class="flex-none rounded-xl">
        <!-- Top Row: Info and Buttons -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2 mb-2">
            <!-- Left Info -->
            <div class="text-sm text-[#002C76] font-montserrat">
                <span class="text-xl md:text-2xl lg:text-3xl font-semibold">
                    {{ $vacancy->position_title }},

                    @if($questionsCount > 0)
                        <span class="text-xl md:text-2xl lg:text-3xl font-normal">
                            {{ $questionsCount }}-question examination
                        </span>
                    @else
                        <span class="text-xl md:text-2xl lg:text-3xl font-normal text-red-600 font-bold">
                            No questions yet
                        </span>
                    @endif
                </span>
                <p class="text-xs md:text-sm"><span class="font-bold">VACANCY ID:</span> {{ $vacancy->vacancy_id }}, {{ $vacancy->vacancy_type }} Position</p>
                <!-- <div class="mt-1">
                    @if($questionsCount > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] md:text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                            {{ $questionsCount }}-question examination
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] md:text-xs font-bold bg-red-100 text-red-700 border border-red-300">
                            No questions yet
                        </span>
                    @endif
                </div> -->
            </div>

        </div>

        <!-- horizontal rule -->
        <div class="border-t border-gray-300 my-2"></div>

    </section>

    <div class="flex-1 flex flex-col lg:flex-row min-h-0 gap-4 overflow-hidden pt-1">
        
        <!-- LEFT COLUMN: Tabs + Content (70% width) -->
        <div class="w-full lg:w-[70%] flex flex-col min-h-0 border-r border-gray-200 pr-4">
            <!-- Tab Navigation (Moved inside left col) -->
            <div class="flex-none flex gap-6 border-b border-gray-200 mb-4">
                <button id="tab-qualified" onclick="switchTab('qualified')"
                    class="tab-button pb-2 font-bold text-[#0D2B70] border-b-2 border-[#0D2B70] transition-all duration-200 text-sm uppercase tracking-wide">
                    Qualified Applicants
                    @if($qualifiedApplicants->count() > 0)
                        <span class="ml-2 bg-[#0D2B70] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full align-middle">
                            {{ $qualifiedApplicants->count() }}
                        </span>
                    @endif
                </button>
                <button id="tab-lobby" onclick="switchTab('lobby')"
                    class="tab-button pb-2 font-bold text-gray-400 border-b-2 border-transparent hover:text-[#0D2B70] transition-all duration-200 text-sm uppercase tracking-wide">
                    Exam Monitor
                </button>
            </div>
            
            <!-- Tab Content: Qualified Applicants -->
            <div id="content-qualified" class="tab-content flex-1 flex flex-col min-h-0 overflow-hidden">
                <div class="flex-none flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                    <!-- Search Bar -->
                    <form onsubmit="return false;" class="relative w-full max-w-xs">
                        <input id="searchInputQualified" type="search" placeholder="Search applicants" aria-label="Search"
                            class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1 w-full" />
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                        </svg>
                    </form>

                    <!-- Bulk Actions -->
                    <div class="flex items-center gap-2">
                        <span id="selectedCount" class="text-sm text-[#0D2B70] font-semibold">0 selected</span>
                        <button id="notifySelectedButton" onclick="notifySelected()" disabled
                            class="px-3 py-1 bg-[#0D2B70] text-white text-xs font-bold rounded shadow-sm opacity-50 cursor-not-allowed transition-all hover:bg-[#002C76] flex items-center gap-1 ml-2">
                            <x-heroicon-o-paper-airplane class="w-3 h-3 transform rotate-90" />
                            Send Link
                        </button>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                    <div class="flex-1 overflow-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                                <tr>
                                    <th class="py-3 px-4 font-normal w-12">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                            class="w-4 h-4 rounded border-gray-300 text-[#0D2B70] focus:ring-[#0D2B70] cursor-pointer">
                                    </th>
                                    <th class="py-3 px-6 font-normal">Name</th>
                                    <th class="py-3 px-6 font-normal">Email</th>
                                    <th class="py-3 px-6 font-normal">Application Date</th>
                                    <th class="py-3 px-6 font-normal text-center">Notification Status</th>
                                    <th class="py-3 px-6 font-normal text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="qualified-applicants-list" class="divide-y divide-[#0D2B70]">
                                @forelse ($qualifiedApplicants as $applicant)
                                    <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                        <td class="py-2.5 px-4">
                                            <input type="checkbox" name="applicant_ids[]" value="{{ $applicant['id'] }}"
                                                data-user-id="{{ $applicant['user_id'] }}"
                                                data-link-sent="{{ $applicant['link_sent'] ? '1' : '0' }}"
                                                onchange="updateSelectedCount()"
                                                class="applicant-checkbox w-4 h-4 rounded border-gray-300 text-[#0D2B70] focus:ring-[#0D2B70] cursor-pointer">
                                        </td>
                                        <td class="py-2.5 px-6 font-semibold">{{ $applicant['name'] }}</td>
                                        <!-- <td class="py-2.5 px-6">{{ $applicant['email'] }}</td> -->
                                        <td class="py-2.5 px-6 max-w-[200px] truncate"> {{ $applicant['email'] }}</td>
                                        <td class="py-2.5 px-6">{{ $applicant['application_date'] }}</td>
                                        <td class="py-2.5 px-6 text-center">
                                            @if($applicant['is_read'])
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 flex items-center justify-center gap-1"
                                                    title="Confirmed: {{ \Carbon\Carbon::parse($applicant['read_at'])->format('M d, Y h:i A') }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Confirmed
                                                </span>
                                            @elseif($applicant['link_sent'])
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 flex items-center justify-center gap-1"
                                                    title="Sent: {{ $applicant['link_sent_at'] ? \Carbon\Carbon::parse($applicant['link_sent_at'])->format('M d, Y h:i A') : 'N/A' }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Pending
                                                </span>
                                            @else
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                                    Not Sent
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-6 text-center">
                                            <a
                                                href="{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}"
                                                target="_blank"
                                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                <span>View</span>
                                            </a>
                                            <!-- <button
                                                onclick="window.location.href='{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}'"
                                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                <span>View</span>
                                            </button> -->
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-10 text-gray-500 text-xl">
                                            No qualified applicants found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab Content: EXAM MONITOR (Phase 2 - Participants Table) -->
            <div id="content-lobby" class="tab-content hidden flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                 <div class="flex-none bg-[#0D2B70] text-white">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-[#0D2B70] text-white">
                            <tr>
                                <th class="py-2.5 px-3 md:py-3 md:px-6 text-left text-xs md:text-sm tracking-wider w-[25%] md:w-[25%]">Name</th>
                                <th class="py-2.5 px-3 md:py-3 md:px-6 text-center text-xs md:text-sm tracking-wider w-[15%] md:w-[15%]">MC</th>
                                <th class="py-2.5 px-3 md:py-3 md:px-6 text-center text-xs md:text-sm tracking-wider w-[15%] md:w-[15%]">Essay</th>
                                <th class="py-2.5 px-3 md:py-3 md:px-6 text-center text-xs md:text-sm tracking-wider w-[20%]">Status</th>
                                <th class="py-2.5 px-3 md:py-3 md:px-6 text-center text-xs md:text-sm tracking-wider w-[25%]">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <!-- EXAM MONITOR TABLE -->
                <div class="flex-1 overflow-y-auto bg-white">
                    <div class="flex items-center justify-between p-2 bg-gray-50 border-b border-[#0D2B70]">
                        <div class="flex items-center gap-3">
                            <span id="lobbyLastUpdated" class="text-xs text-gray-400"></span>
                        </div>
                        <button id="refreshLobbyBtn" onclick="fetchLobbyData(true)" class="text-xs bg-white border border-[#0D2B70] text-[#0D2B70] hover:bg-[#0D2B70] hover:text-white px-3 py-1 rounded transition-colors duration-200 flex items-center gap-1">
                            <x-heroicon-o-arrow-path class="w-3 h-3" />
                            Refresh Now
                        </button>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <tbody id="exam-lobby-tbody" class="bg-white divide-y divide-gray-200">
                            @if (count($participants) > 0)
                                @foreach ($participants as $index => $p)
                                <tr class="hover:bg-blue-50 transition-colors duration-200">
                                    <!-- Name -->
                                    <td class="py-2.5 px-3 md:py-3 md:px-6 text-[#0D2B70] font-semibold text-xs md:text-sm w-[25%] md:w-[25%]">
                                        {{ $user_name[$index] ?? 'Unknown User' }}
                                    </td>

                                    <!-- MC Score -->
                                    <td class="py-2.5 px-3 md:py-3 md:px-6 text-center text-[#0D2B70] font-medium text-xs md:text-sm w-[15%] md:w-[15%]">
                                        {{ $p->mc_score_str ?? '-' }}
                                    </td>

                                    <!-- Essay Score -->
                                    <td class="py-2.5 px-3 md:py-3 md:px-6 text-center text-[#0D2B70] font-medium text-xs md:text-sm w-[15%] md:w-[15%]">
                                        {{ $p->essay_score_str ?? '-' }}
                                    </td>

                                    <!-- Status -->
                                    <td class="py-2.5 px-3 md:py-3 md:px-6 text-center w-[20%]">
                                        <div class="inline-flex items-center gap-1 md:gap-2 text-[#0D2B70] font-medium text-xs md:text-sm">
                                            @php
                                                $statusColors = [
                                                    'ready' => '#4ade80',        // green-400
                                                    'in-progress' => '#facc15',  // yellow-400
                                                    'submitted' => '#3b82f6',    // blue-500
                                                    'pending' => '#f75555',      // red
                                                ];

                                                $status = strtolower($p->status ?? 'pending');
                                                $color = $statusColors[$status] ?? '#9ca3af'; // gray-400 as default
                                            @endphp
                                            <i class="fa-solid fa-circle text-xs" style="color: {{ $color }}"></i>
                                            <span>{{ $p->status ?? 'Pending' }}</span>
                                        </div>
                                    </td>

                                    <!-- Action Button -->
                                     <td class="py-2.5 px-3 md:py-3 md:px-6 text-center w-[25%]">
                                        <a href="{{ route('admin.view_exam', ['vacancy_id' => $p->vacancy_id, 'user_id' => $p->user_id]) }}" target="_blank"
                                            class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1.5 px-3 md:py-2 md:px-6 rounded-md text-xs md:text-sm
                                                transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)]
                                                hover:scale-105 hover:bg-[#002C76] hover:text-white hover:shadow-md inline-flex items-center gap-1 md:gap-2">
                                            <x-heroicon-o-eye class="w-3 h-3 md:w-4 md:h-4" />
                                            <span class="hidden sm:inline">View</span>
                                        </a>
                                    </td>
                                    <!-- <td class="py-3 px-3 md:py-4 md:px-6 text-center w-[25%]">
                                        <button target="_blank" onclick="window.location.href='{{ route('admin.view_exam', ['vacancy_id' => $p->vacancy_id, 'user_id' => $p->user_id]) }}'"
                                            class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1.5 px-3 md:py-2 md:px-6 rounded-md text-xs md:text-sm
                                                transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)]
                                                hover:scale-105 hover:bg-[#002C76] hover:text-white hover:shadow-md inline-flex items-center gap-1 md:gap-2">
                                            <x-heroicon-o-eye class="w-3 h-3 md:w-4 md:h-4" />
                                            <span class="hidden sm:inline">View</span>
                                        </button>
                                    </td> -->
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="py-10 text-center text-gray-500">
                                        <p class="text-xl font-semibold">There are no participants yet.</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>



        <!--     scheduling form, buttons -->
        <!-- RIGHT COLUMN: Scheduling form -->
        <div class="w-full lg:w-[30%] lg:min-w-[320px] flex flex-col mt-4 lg:mt-0 pl-2 min-h-0">
            <form id="examDetailsForm" class="flex flex-col h-full justify-between">
            @csrf
            
            <!-- PANEL 1: SCHEDULE EXAM (Default Visible) -->
            <div id="panel-schedule" class="flex flex-col gap-3">
                <!-- Header -->
                <span class="text-xl text-[#0D2B70] font-bold border-b border-gray-200 pb-2 mb-1">
                    Schedule Exam
                </span>
                
                <!-- VENUE -->
                <div class="flex flex-col">
                    <label for="venue" class="text-[#0D2B70] font-bold text-xs mb-1">Venue <span class="text-red-500">*</span></label>
                    <input type="text" id="venue" name="place" required
                        value="{{ $examDetails->place ?? '' }}"
                        {{ ($isExamActive || $isExamCompleted || ($examDetails && $examDetails->details_saved)) ? 'disabled' : '' }}
                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#0D2B70] focus:border-[#0D2B70] placeholder-gray-400 disabled:bg-gray-100"
                        placeholder="Enter venue" />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <!-- DATE -->
                    <div class="flex flex-col">
                        <label for="date" class="text-[#0D2B70] font-bold text-xs mb-1">Date <span class="text-red-500">*</span></label>
                        <input type="date" id="date" name="date" required
                            value="{{ $examDetails->date ?? '' }}"
                            {{ ($isExamActive || $isExamCompleted || ($examDetails && $examDetails->details_saved)) ? 'disabled' : '' }}
                            class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#0D2B70] disabled:bg-gray-100" />
                    </div>
    
                    <!-- TIME (Start) -->
                    <div class="flex flex-col">
                        <label for="time" class="text-[#0D2B70] font-bold text-xs mb-1">Time <span class="text-red-500">*</span></label>
                        <input class="font-sm h-full" type="time" id="time" name="time" required
                            value="{{ $examDetails->time ?? '' }}"
                            {{ ($isExamActive || $isExamCompleted || ($examDetails && $examDetails->details_saved)) ? 'disabled' : '' }}
                            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#0D2B70] disabled:bg-gray-100" />
                    </div>
                </div>

                <!-- MESSAGE (New Field) -->
                <div class="flex flex-col">
                    <label for="message" class="text-[#0D2B70] font-bold text-xs mb-1">Message <span class="text-red-500">*</span></label>
                    <textarea id="message" name="message" rows="3" required
                        {{ ($isExamActive || $isExamCompleted || ($examDetails && $examDetails->details_saved)) ? 'disabled' : '' }}
                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#0D2B70] disabled:bg-gray-100 placeholder-gray-400 resize-none"
                        placeholder="Enter message for applicants">{{ $examDetails->message ?? '' }}</textarea>
                </div>

                <!-- HIDDEN FIELDS FOR BACKEND COMPATIBILITY -->
                <input type="hidden" id="time_end_hidden" name="time_end" value="{{ $examDetails->time_end ?? '' }}">
                <input type="hidden" id="duration" name="duration" value="{{ $examDetails->duration ?? '' }}">

                <!-- ACTION BUTTONS: SCHEDULE -->
                <div class="flex flex-col gap-2 mt-4">
                    <div class="flex flex-col">
                        <div>
                            <button type="submit" id="saveNotifyButton" name="action" value="save_notify" 
                                    {{ ($isExamActive || $isExamCompleted || ($examDetails && $examDetails->details_saved) || ($qualifiedCount < 1)) ? 'disabled' : '' }}
                                    class="w-full py-2 bg-[#0D2B70] border-2 border-[#0D2B70] rounded-lg text-white font-bold text-sm hover:scale-[1.02] flex items-center justify-center gap-2 transition-transform disabled:opacity-50 disabled:hover:scale-100">
                                Save and Notify Applicants
                            </button>
                        </div>
                        <div>
                            @if($examDetails && $examDetails->notified_at)
                                <p class="text-xs text-gray-600 italic text-left mt-1">
                                    <b>{{ $notifiedByName ?? 'An admin' }} </b> notified applicants on 
                                    <b>{{ \Carbon\Carbon::parse($examDetails->notified_at)->format('M d, h:i A') }}</b>
                                </p>
                            @endif
                        </div>
                    </div>
                    
                    <button type="button" onclick="handleEditClick(event)"
                            {{ (($isExamActive && $isExamDay) || $isExamCompleted) ? 'disabled' : '' }}
                            class="w-full py-2 bg-white border-2 border-[#0D2B70] rounded-lg text-[#0D2B70] font-bold text-sm hover:scale-[1.02] flex items-center justify-center gap-2 transition-transform disabled:opacity-50 disabled:hover:scale-100">
                        Edit Questions
                    </button>
                </div>
            </div>

            <!-- PANEL 2: EXAM MONITOR (Hidden initially) -->
            <div id="panel-monitor" class="flex flex-col gap-3 hidden">
                <!-- Header -->
                <span class="text-xl text-[#0D2B70] font-bold border-b border-gray-200 pb-2 mb-1">
                    Exam Monitor
                </span>

                <!-- START (Autofilled) & END -->
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex flex-col">
                        <label class="text-[#0D2B70] font-bold text-xs mb-1">Start</label>
                        <input type="text" id="monitor_start" readonly
                            value="{{ !empty($examDetails->time) ? \Carbon\Carbon::createFromFormat('H:i:s', strlen($examDetails->time) === 5 ? $examDetails->time . ':00' : $examDetails->time)->format('g:i A') : '--:--' }}"
                            class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm bg-gray-100 text-gray-600 cursor-not-allowed" />
                        <p class="text-[10px] text-red-500 mt-0.5">*start field is autofilled*</p>
                    </div>
                    <div class="flex flex-col">
                        <label for="monitor_end" class="text-[#0D2B70] font-bold text-xs mb-1">End <span class="text-red-500">*</span></label>
                        <input type="time" id="monitor_end" 
                            value="{{ $examDetails->time_end ?? '' }}"
                            {{ ($isExamActive || $isExamCompleted) ? 'disabled' : '' }}
                            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#0D2B70] disabled:bg-gray-100" />
                    </div>
                </div>

                <!-- ACTION BUTTONS: MONITOR -->
                <div class="flex flex-col gap-2 mt-4">
                    <button type="button" id="sendLinkButton" onclick="triggerSendLinkConfirm('{{ $vacancy->vacancy_id }}')" 
                            {{ (!$examDetails || !$examDetails->details_saved || $examDetails->link_sent || $isExamActive || $isExamCompleted || !$isExamDay || ($qualifiedCount < 1)) ? 'disabled' : '' }}
                            class="w-full py-2 bg-[#0D2B70] border-2 border-[#0D2B70] rounded-lg text-white font-bold text-sm hover:scale-[1.02] flex items-center justify-center gap-2 transition-transform disabled:opacity-50 disabled:hover:scale-100">
                        Send Link via Email
                    </button>
                    
                    <button type="button" id="startExamButton" onclick="triggerStartExamConfirm('{{ $vacancy->vacancy_id }}')" 
                            {{ (!$examDetails || !$examDetails->link_sent || $isExamActive || $isExamCompleted || !$isExamDay || !$hasQuestions || ($lobbyCount < 1)) ? 'disabled' : '' }}
                            class="w-full py-2 bg-white border-2 border-[#0D2B70] rounded-lg text-[#0D2B70] font-bold text-sm hover:scale-[1.02] flex items-center justify-center gap-2 transition-transform disabled:opacity-50 disabled:hover:scale-100">
                        Start Exam
                    </button>
                </div>
            </div>

            </form>
        </div>
                                        











                                        
    </div>


    @include('partials.loader')
    <!-- Confirmation Modals -->
    <x-confirm-modal 
        title="Save & Notify Applicants"
        message="Save exam details and notify qualified applicants?"
        event="open-save-notify-confirm"
        confirm="confirm-save-notify"
    />
    <x-confirm-modal 
        title="Send Exam Links"
        message="Send exam lobby links to all eligible participants?"
        event="open-send-link-confirm"
        confirm="confirm-send-link"
    />
    <x-confirm-modal 
        title="Start Exam"
        message="Start the exam now? All ready participants will enter the exam."
        event="open-start-exam-confirm"
        confirm="confirm-start-exam"
    />
</main>
<script>
    // Confirmation wrappers
    function triggerSendLinkConfirm(vacancyId) {
        window._pendingVacancyId = vacancyId;
        window.dispatchEvent(new CustomEvent('open-send-link-confirm'));
    }
    function triggerStartExamConfirm(vacancyId) {
        window._pendingVacancyId = vacancyId;
        window.dispatchEvent(new CustomEvent('open-start-exam-confirm'));
    }
    // Confirm handlers
    window.addEventListener('confirm-send-link', () => {
        const id = window._pendingVacancyId;
        if (id) sendExamLink(id);
    });
    window.addEventListener('confirm-start-exam', () => {
        const id = window._pendingVacancyId;
        if (id) startExam(id);
    });

    // Send exam link via email (executes after confirmation)
    function sendExamLink(vacancyId) {

        const sendLinkButton = document.getElementById('sendLinkButton');
        const originalText = sendLinkButton.innerHTML;
        sendLinkButton.disabled = true;
        sendLinkButton.innerHTML = '<span>Sending...</span>';

        fetch(`/admin/exam_management/${vacancyId}/notify`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ vacancy_id: vacancyId })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => { 
                    throw new Error(data.message || 'Failed to send links');
                });
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                showAppToast(data.message || "Exam links sent successfully.");
                // Mark links as sent on client and update Start Exam state
                linkSentClient = true;
                updateStartButtonState();
                // Keep Send Link button disabled
                sendLinkButton.innerHTML = originalText;
            } else {
                sendLinkButton.disabled = false;
                sendLinkButton.innerHTML = originalText;
                showAppToast("Failed to send links: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            sendLinkButton.disabled = false;
            sendLinkButton.innerHTML = originalText;
            showAppToast("An error occurred: " + error.message);
        });
    }

    // Start exam function (executes after confirmation)
    function startExam(vacancyId) {

        const startButton = document.getElementById('startExamButton');
        const originalText = startButton.innerHTML;
        startButton.disabled = true;
        startButton.innerHTML = '<span>Starting...</span>';

        fetch(`/admin/exam_management/${vacancyId}/start`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => { 
                    throw new Error(data.message || 'Failed to start exam');
                });
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                showAppToast("Exam started successfully!");
                window.location.reload(); // Reload to update status
            } else {
                startButton.disabled = false;
                startButton.innerHTML = originalText;
                showAppToast("Failed to start exam: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            startButton.disabled = false;
            startButton.innerHTML = originalText;
            showAppToast("An error occurred: " + error.message);
        });
    }


    const saveNotifyBtnEl = document.getElementById('saveNotifyButton');
    if (saveNotifyBtnEl) {
        saveNotifyBtnEl.addEventListener('click', function(e) {
            // Only intercept when this button would submit
            if (!this.disabled) {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('open-save-notify-confirm'));
            }
        });
    }

    // After confirmation, submit form via existing handler
    window.addEventListener('confirm-save-notify', () => {
        const formEl = document.getElementById('examDetailsForm');
        const btn = document.getElementById('saveNotifyButton');
        if (formEl && btn && !btn.disabled) {
            if (formEl.requestSubmit) {
                formEl.requestSubmit(btn);
            } else {
                // Fallback
                btn.disabled = true;
                formEl.submit();
            }
        }
    });

    document.getElementById('examDetailsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submitted');
        
        // Validate times before submission
        const startTime = document.getElementById('time').value;
        const endTime = document.getElementById('time_end_hidden').value;
        
        if (startTime && endTime && endTime <= startTime) {
            showAppToast('End time must be after start time. Please correct the times.');
            return;
        }
        
        // Hide the loader immediately if it was triggered by the global listener
        const loader = document.getElementById('loader');
        if (loader) {
            loader.classList.add('hidden');
            // Ensure z-index doesn't block clicks even if hidden class fails
            loader.style.display = 'none'; 
        }

        const vacancyId = '{{ $vacancy->vacancy_id }}';
        const formData = new FormData(this);
        
        // Append action if submitting via the Save & Notify button
        if (e.submitter && e.submitter.name === 'action' && e.submitter.value === 'save_notify') {
            formData.append('notify', '1');
            console.log('Notify flag set to true');
        }

        const saveButton = document.getElementById('saveNotifyButton');
        const originalText = saveButton.innerHTML;
        saveButton.disabled = true;
        saveButton.innerHTML = '<span>Saving...</span>';

        console.log('Sending request to:', `/admin/exam_management/${vacancyId}/details/save`);

        fetch(`/admin/exam_management/${vacancyId}/details/save`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            // Read response as text first to avoid "body stream already read" error
            return response.text().then(text => {
                console.log('Raw response:', text);
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    data = { success: false, message: text || 'Invalid server response' };
                }
                
                if (!response.ok) {
                    console.error('Error response:', data);
                    throw new Error(data.message || `Server error: ${response.status}`);
                }
                
                return data;
            });
        })
        .then(data => {
            console.log('Success response:', data);
            saveButton.innerHTML = originalText;
            
            if (data.success) {
                // Keep save button disabled permanently
                saveButton.disabled = true;
                saveButton.classList.add('opacity-50', 'cursor-not-allowed');

                // Disable all form fields
                document.getElementById('venue').disabled = true;
                document.getElementById('date').disabled = true;
                document.getElementById('time').disabled = true;
                document.getElementById('message').disabled = true;
                const monitorEnd = document.getElementById('monitor_end');
                if (monitorEnd) monitorEnd.disabled = true;

                // Re-evaluate Send Link button state (uses latest lobby count)
                updateSendLinkButtonState(currentLobbyCount);
                
                let msg = "Exam details saved successfully!";
                if (data.notified) {
                    msg += " " + (data.notify_message || "Applicants have been notified.");
                }
                showAppToast(msg);
                
                // Optionally reload the page to reflect changes
                // window.location.reload();
            } else {
                saveButton.disabled = false;
                console.error('Save failed:', data.message);
                showAppToast("Failed to save exam details: " + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            console.error('Error caught:', error);
            console.error('Error message:', error.message);
            showAppToast("An error occurred while saving exam details.\n\nError: " + error.message + "\n\nPlease check the browser console and Laravel logs for more details.");
        });
    });

    // Server-provided counts and flags for gating logic
    const qualifiedCount = @json(isset($qualifiedApplicants) ? $qualifiedApplicants->count() : 0);
    let currentLobbyCount = @json(isset($participants) ? $participants->count() : 0);
    const hasExamDetails = @json(!is_null($examDetails ?? null));
    const detailsSavedConst = @json($examDetails && $examDetails->details_saved);
    const linkSentConst = @json($examDetails && $examDetails->link_sent);
    let linkSentClient = linkSentConst;
    const isExamActiveConst = @json($isExamActive);
    const isExamCompletedConst = @json($isExamCompleted);
    const isExamDayConst = @json($isExamDay);
    const isBeforeStartConst = @json($isBeforeStart);
    const isWithinOneHourConst = @json($isWithinOneHourBeforeStart);
    const hasQuestionsConst = @json($hasQuestions ?? false);

    // Helper: update Send Link button state using lobby count and flags
    function updateSendLinkButtonState(participantsCount) {
        currentLobbyCount = participantsCount;
        const btn = document.getElementById('sendLinkButton');
        if (!btn) return;
        const shouldEnable = hasExamDetails 
            && detailsSavedConst 
            && !linkSentClient 
            && !isExamActiveConst 
            && !isExamCompletedConst 
            && isExamDayConst 
            && qualifiedCount > 0;
        btn.disabled = !shouldEnable;
        btn.classList.toggle('opacity-50', !shouldEnable);
        btn.classList.toggle('cursor-not-allowed', !shouldEnable);
    }

    // Helper: update Start Exam button state using flags and lobby count
    function updateStartButtonState() {
        const btn = document.getElementById('startExamButton');
        if (!btn) return;
        const shouldEnable = hasExamDetails
            && linkSentClient
            && !isExamActiveConst
            && !isExamCompletedConst
            && isExamDayConst
            && hasQuestionsConst
            && currentLobbyCount > 0;
        btn.disabled = !shouldEnable;
        btn.classList.toggle('opacity-50', !shouldEnable);
        btn.classList.toggle('cursor-not-allowed', !shouldEnable);
    }

    // Form validation - enable Save button only when all required fields are filled
    function validateForm() {
        const venue = document.getElementById('venue').value.trim();
        const date = document.getElementById('date').value.trim();
        const time = document.getElementById('time').value.trim();
        const message = document.getElementById('message').value.trim();
        const saveButton = document.getElementById('saveNotifyButton');
        
        // Ensure hidden end time is set (default to +1 hour if empty)
        const timeEndInput = document.getElementById('time_end_hidden');
        if (time && !timeEndInput.value) {
            // Auto-set end time to 1 hour later
            const [hours, minutes] = time.split(':');
            const dateObj = new Date();
            dateObj.setHours(parseInt(hours) + 1);
            dateObj.setMinutes(parseInt(minutes));
            const endHours = String(dateObj.getHours()).padStart(2, '0');
            const endMinutes = String(dateObj.getMinutes()).padStart(2, '0');
            timeEndInput.value = `${endHours}:${endMinutes}`;
        }

        const allFilled = venue && date && time && message;
        
        // Only enable if all fields are filled AND details haven't been saved yet AND qualified applicants exist
        const detailsSaved = {{ $examDetails && $examDetails->details_saved ? 'true' : 'false' }};
        const hasQualified = qualifiedCount > 0;

        if (allFilled && !detailsSaved && hasQualified) {
            saveButton.disabled = false;
            saveButton.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            saveButton.disabled = true;
            saveButton.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Add event listeners to form fields for validation
    const formFields = ['venue', 'date', 'time', 'message'];
    formFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', validateForm);
            field.addEventListener('change', validateForm);
        }
    });


    // Run validation on page load and initialize Send Link button state
    validateForm();
    updateSendLinkButtonState(currentLobbyCount);
    updateStartButtonState();
    // Sync initial state
    syncMonitorFields();

    // Auto-calculate duration
    const startTimeInput = document.getElementById('time');
    const timeEndHidden = document.getElementById('time_end_hidden');
    const durationInput = document.getElementById('duration');

    function calculateDuration() {
        const start = startTimeInput.value;
        let end = timeEndHidden.value;

        if (start && !end) {
             // Default 1 hour
             const [h, m] = start.split(':');
             const d = new Date();
             d.setHours(parseInt(h) + 1);
             d.setMinutes(parseInt(m));
             end = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
             timeEndHidden.value = end;
        }

        if (start && end) {
            // Check if end is before start (next day not supported for simplicity)
            if (end <= start) {
                // Force end to be start + 1 hour
                 const [h, m] = start.split(':');
                 const d = new Date();
                 d.setHours(parseInt(h) + 1);
                 d.setMinutes(parseInt(m));
                 end = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
                 timeEndHidden.value = end;
            }

            const startDate = new Date(`1970-01-01T${start}:00`);
            const endDate = new Date(`1970-01-01T${end}:00`);
            
            let diffMs = endDate - startDate;
            let diffMins = Math.round(diffMs / 60000);

            if (diffMins > 0) {
                durationInput.value = diffMins;
            }
        }
        
        syncMonitorFields();
        validateForm();
    }

    if (startTimeInput) {
        startTimeInput.addEventListener('input', calculateDuration);
        startTimeInput.addEventListener('change', calculateDuration);
    }

    // Prevent navigation if exam is active and user tries to edit
    function handleEditClick(e) {
        e.preventDefault();
        const isExamActive = @json($isExamActive);
        const isExamCompleted = @json($isExamCompleted);
        
        if (isExamActive) {
            showAppToast('Cannot edit questions while an exam is currently in progress.');
            return;
        }

        if (isExamCompleted) {
            showAppToast('Cannot edit questions after the exam has been completed.');
            return;
        }
        
        
        window.location.href = '{{ route('admin.exam.edit', $vacancy->vacancy_id) }}';
    }

    // ========================================
    // TAB SWITCHING
    // ========================================
    function switchTab(tab) {
        const tabs = ['qualified', 'lobby'];
        const panelSchedule = document.getElementById('panel-schedule');
        const panelMonitor = document.getElementById('panel-monitor');

        tabs.forEach(t => {
            const tabBtn = document.getElementById(`tab-${t}`);
            const content = document.getElementById(`content-${t}`);

            if (t === tab) {
                tabBtn.classList.add('border-[#0D2B70]', 'text-[#0D2B70]');
                tabBtn.classList.remove('border-transparent', 'text-gray-400');
                content.classList.remove('hidden');
                
                // Toggle Right Panel
                if (t === 'qualified') {
                    if (panelSchedule) panelSchedule.classList.remove('hidden');
                    if (panelMonitor) panelMonitor.classList.add('hidden');
                    stopLobbyPolling();
                } else if (t === 'lobby') {
                    if (panelSchedule) panelSchedule.classList.add('hidden');
                    if (panelMonitor) panelMonitor.classList.remove('hidden');
                    fetchLobbyData();
                    startLobbyPolling();
                    // Sync Monitor fields
                    syncMonitorFields();
                }
            } else {
                tabBtn.classList.remove('border-[#0D2B70]', 'text-[#0D2B70]');
                tabBtn.classList.add('border-transparent', 'text-gray-400');
                content.classList.add('hidden');
            }
        });
    }

    function syncMonitorFields() {
        const timeInput = document.getElementById('time');
        const timeEndInput = document.getElementById('time_end_hidden');
        const monitorStart = document.getElementById('monitor_start');
        const monitorEnd = document.getElementById('monitor_end');

        if (timeInput && monitorStart) {
            const value = timeInput.value;
            if (!value) {
                monitorStart.value = '--:--';
            } else {
                const parts = value.split(':');
                const hour = parseInt(parts[0], 10);
                const minute = parts[1] ?? '00';
                if (Number.isNaN(hour)) {
                    monitorStart.value = value;
                } else {
                    const suffix = hour >= 12 ? 'PM' : 'AM';
                    const hour12 = (hour % 12) || 12;
                    monitorStart.value = `${hour12}:${minute} ${suffix}`;
                }
            }
        }
        if (timeEndInput && monitorEnd) monitorEnd.value = timeEndInput.value || '';
    }


    // ========================================
    // CHECKBOX MANAGEMENT
    // ========================================
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.applicant-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.applicant-checkbox:checked');
        const count = checkboxes.length;
        const countDisplay = document.getElementById('selectedCount');
        countDisplay.textContent = `${count} selected`;

        // Update button state
        const notifyBtn = document.getElementById('notifySelectedButton');
        if (notifyBtn) {
            if (count > 0) {
                notifyBtn.disabled = false;
                notifyBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                notifyBtn.classList.add('opacity-100', 'cursor-pointer');
            } else {
                notifyBtn.disabled = true;
                notifyBtn.classList.add('opacity-50', 'cursor-not-allowed');
                notifyBtn.classList.remove('opacity-100', 'cursor-pointer');
            }
        }

        // Update select all checkbox state
        const selectAllCheckbox = document.getElementById('selectAll');
        const totalCheckboxes = document.querySelectorAll('.applicant-checkbox');
        // Prevent division by zero if no checkboxes exist
        if (totalCheckboxes.length > 0) {
            selectAllCheckbox.checked = count > 0 && count === totalCheckboxes.length;
            selectAllCheckbox.indeterminate = count > 0 && count < totalCheckboxes.length;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    function notifySelected() {
        const selectedCheckboxes = document.querySelectorAll('.applicant-checkbox:checked');
        const userIds = Array.from(selectedCheckboxes).map(cb => cb.dataset.userId);

        if (userIds.length === 0) return;

        if (!confirm(`Are you sure you want to send exam links to ${userIds.length} selected applicants?`)) {
            return;
        }

        const btn = document.getElementById('notifySelectedButton');
        const originalContent = btn.innerHTML;
        const originalOpacity = btn.classList.contains('opacity-50'); 
        
        btn.disabled = true;
        btn.innerHTML = `<span class="animate-pulse">Sending...</span>`;
        btn.classList.add('opacity-75', 'cursor-wait');

        fetch(`/admin/exam_management/{{ $vacancy->vacancy_id }}/notify-selected`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ user_ids: userIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAppToast(data.message);
                // Refresh list
                const search = document.getElementById('searchInputQualified').value;
                fetchQualifiedApplicants(search);
                // Reset Selection
                document.getElementById('selectAll').checked = false;
            } else {
                showAppToast('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAppToast('An error occurred while sending notifications.');
        })
        .finally(() => {
            // Re-enable handled by updateSelectedCount when table refreshes (checkboxes lost)
            // But if error occurred and table didn't refresh, checkboxes are still there
            btn.disabled = false;
            btn.innerHTML = originalContent;
            btn.classList.remove('opacity-75', 'cursor-wait');
            updateSelectedCount();
        });
    }

    // ========================================
    // SEARCH FUNCTIONALITY
    // ========================================
    const searchInputQualified = document.getElementById('searchInputQualified');
    
    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    const handleQualifiedSearch = debounce(function () {
        const search = searchInputQualified.value.trim();
        fetchQualifiedApplicants(search);
    }, 500);

    if (searchInputQualified) {
        searchInputQualified.addEventListener('input', handleQualifiedSearch);
    }

    function fetchQualifiedApplicants(search = '') {
        const params = new URLSearchParams({
            search: search
        });

        fetch(`/admin/exam_management/{{ $vacancy->vacancy_id }}/qualified?${params.toString()}`, {
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateQualifiedApplicantsTable(data.applicants);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function updateQualifiedApplicantsTable(applicants) {
        const tbody = document.getElementById('qualified-applicants-list');
        
        if (applicants.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-10 text-gray-500 text-xl">
                        No qualified applicants found.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = applicants.map(app => `
            <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                <td class="py-2.5 px-4">
                    <input type="checkbox" name="applicant_ids[]" value="${app.id}"
                        data-user-id="${app.user_id}"
                        data-link-sent="${app.link_sent ? '1' : '0'}"
                        onchange="updateSelectedCount()"
                        class="applicant-checkbox w-4 h-4 rounded border-gray-300 text-[#0D2B70] focus:ring-[#0D2B70] cursor-pointer">
                </td>
                <td class="py-2.5 px-6 font-semibold">${app.name}</td>
                <td class="py-2.5 px-6">${app.email}</td>
                <td class="py-2.5 px-6">${app.application_date}</td>
                <td class="py-2.5 px-6 text-center">
                    ${app.is_read ? 
                        `<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 flex items-center justify-center gap-1" title="Confirmed">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Confirmed
                        </span>` : 
                        (app.link_sent ? 
                            `<span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 flex items-center justify-center gap-1" title="Pending">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Pending
                            </span>` :
                            `<span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Not Sent</span>`
                        )
                    }
                </td>
                <td class="py-2.5 px-6 text-center">
                    <button
                        onclick="window.location.href='/admin/applicant_status/${app.user_id}/${app.vacancy_id}'"
                        class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span>View</span>
                    </button>
                </td>
            </tr>
        `).join('');

        updateSelectedCount();
    }
    // ========================================
    // LOBBY POLLING & AJAX
    // ========================================
    let lobbyPollingInterval = null;

    function startLobbyPolling() {
        if (lobbyPollingInterval) clearInterval(lobbyPollingInterval);
        lobbyPollingInterval = setInterval(fetchLobbyData, 10000); // Poll every 10 seconds
    }

    function stopLobbyPolling() {
        if (lobbyPollingInterval) clearInterval(lobbyPollingInterval);
        lobbyPollingInterval = null;
    }

    function fetchLobbyData(isManual = false) {
        const vacancyId = '{{ $vacancy->vacancy_id }}';
        const btn = document.getElementById('refreshLobbyBtn');
        const icon = btn?.querySelector('svg');

        if (isManual && btn) {
            btn.disabled = true;
            icon?.classList.add('animate-spin');
        }

        fetch(`/admin/exam_management/${vacancyId}/lobby-data`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateLobbyTable(data.participants);
                updateLastUpdatedTime();
                const count = Array.isArray(data.participants) ? data.participants.length : 0;
                updateSendLinkButtonState(count);
                updateStartButtonState();
            }
        })
        .catch(error => console.error('Error fetching lobby data:', error))
        .finally(() => {
            if (isManual && btn) {
                btn.disabled = false;
                icon?.classList.remove('animate-spin');
            }
        });
    }

    function updateLastUpdatedTime() {
        const el = document.getElementById('lobbyLastUpdated');
        if (el) {
            const now = new Date();
            el.textContent = 'Last updated: ' + now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }

    function updateLobbyTable(participants) {
        const tbody = document.getElementById('exam-lobby-tbody');
        
        if (participants.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="py-10 text-center text-gray-500">
                        <p class="text-xl font-semibold">There are no participants yet.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = participants.map(p => `
            <tr class="hover:bg-blue-50 transition-colors duration-200">
                <!-- Name -->
                <td class="py-2.5 px-3 md:py-3 md:px-6 text-[#0D2B70] font-semibold text-xs md:text-sm w-[25%] md:w-[25%]">
                    ${p.name}
                </td>

                <!-- MC Score -->
                <td class="py-2.5 px-3 md:py-3 md:px-6 text-center text-[#0D2B70] font-medium text-xs md:text-sm w-[15%] md:w-[15%]">
                    ${p.mc_score}
                </td>

                <!-- Essay Score -->
                <td class="py-2.5 px-3 md:py-3 md:px-6 text-center text-[#0D2B70] font-medium text-xs md:text-sm w-[15%] md:w-[15%]">
                    ${p.essay_score}
                </td>

                <!-- Status -->
                <td class="py-2.5 px-3 md:py-3 md:px-6 text-center w-[20%]">
                    <div class="inline-flex items-center gap-1 md:gap-2 text-[#0D2B70] font-medium text-xs md:text-sm">
                        <i class="fa-solid fa-circle text-xs" style="color: ${p.status_color}"></i>
                        <span class="capitalize">${p.status}</span>
                    </div>
                </td>

                <!-- Action Button -->
                <td class="py-2.5 px-3 md:py-3 md:px-6 text-center w-[25%]">
                     <button onclick="window.location.href='/admin/exam_management/${p.vacancy_id}/view_exam/${p.user_id}'"
                        class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1.5 px-3 md:py-2 md:px-6 rounded-md text-xs md:text-sm
                            transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)]
                            hover:scale-105 hover:bg-[#002C76] hover:text-white hover:shadow-md inline-flex items-center gap-1 md:gap-2">
                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span class="hidden sm:inline">View</span>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    // Stop polling if user leaves page (though modern browsers throttle this anyway)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopLobbyPolling();
        } else {
             // Only resume if we are on the lobby tab
             if (!document.getElementById('content-lobby').classList.contains('hidden')) {
                 fetchLobbyData();
                 startLobbyPolling();
             }
        }
    });

</script>

@endsection

