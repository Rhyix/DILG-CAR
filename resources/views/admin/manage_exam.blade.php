@extends('layout.admin')
@section('title', 'DILG - Manage Exam')
@section('content')

<main class="w-full max-w-7xl h-[calc(100vh-6rem)] flex flex-col space-y-4 overflow-hidden">
    <!-- header -->
    <section class="flex-none flex items-center justify-between space-x-4 max-w-full border-b border-[#0D2B70]">
        <div class="flex items-center gap-4">
            <button aria-label="Back" onclick="window.location.href='{{ route('admin_exam_management') }}'" class="use-loader group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h1 class="flex items-center gap-3 py-2 tracking-wide select-none">
                <span class="text-[#0D2B70] text-4xl font-montserrat whitespace-nowrap">Exam Overview</span>
            </h1>
        </div>

        <!-- EXAM STATUS BANNER (Compact) -->
        @php
            // Determine exam status
            $isExamActive = false;
            $statusMessage = '';
            $statusClass = '';

            if(isset($examDetails->date) && isset($examDetails->time) && isset($examDetails->duration)) {
                 $startDateTime = \Carbon\Carbon::parse($examDetails->date . ' ' . $examDetails->time);
                 $endDateTime = $startDateTime->copy()->addMinutes($examDetails->duration);
                 $now = now();

                 if ($now->between($startDateTime, $endDateTime)) {
                     // Current time is between start and end
                     $isExamActive = true;
                     $statusMessage = 'Exam in Progress';
                     $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-400';
                 } elseif ($now->gt($endDateTime)) {
                     // Current time is after end time
                     $statusMessage = 'Exam Completed';
                     $statusClass = 'bg-green-100 text-green-800 border-green-400';
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
            <div class="px-4 py-1 border-l-4 rounded shadow-sm flex items-center gap-3 {{ $statusClass }} mr-4">
                <div class="flex items-center gap-2">
                    @if($isExamActive)
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                        </span>
                    @endif
                    <span class="font-bold uppercase text-xs tracking-wide">{{ $statusMessage }}</span>
                </div>
                @if($isExamActive)
                    <span class="text-[10px] font-semibold opacity-80 hidden md:inline">Editing disabled</span>
                @endif
            </div>
        @endif
        <!-- END EXAM STATUS BANNER -->
    </section>  

    <!-- OLD SCHEDULE -->
    <section class="flex-none rounded-xl shadow-sm">
        <!-- Top Row: Info and Buttons -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
            <!-- Left Info -->
            <div class="text-sm text-[#002C76] font-montserrat">
                <span class="text-3xl">{{ $vacancy->position_title }}</span>
                <p><span class="font-bold">VACANCY ID:</span> {{ $vacancy->vacancy_id }}, {{ $vacancy->vacancy_type }} Position</p>
            </div>

        </div>

        <!-- horizontal rule -->
        <div class="border-t border-gray-300 my-4"></div>

    </section>

    <div class="flex-1 flex flex-row min-h-0 gap-4 pb-4">
        <!-- TABLE -->
        <div class="w-[70%] flex flex-col rounded-xl border border-[#0D2B70] overflow-hidden">
            <div class="flex-none bg-[#0D2B70] text-white">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#0D2B70] text-white">
                        <tr>
                            <th class="py-4 px-6 text-left font-bold uppercase text-sm tracking-wider w-[30%]">Name</th>
                            <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[20%]">Score</th>
                            <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[25%]">Status</th>
                            <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[25%]">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="flex-1 overflow-y-auto">
                <table class="w-full text-left border-collapse">
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if (count($participants) > 0)
                            @foreach ($participants as $index => $p)
                            <tr class="hover:bg-blue-50 transition-colors duration-200">
                                <!-- Name -->
                                <td class="py-4 px-6 text-[#0D2B70] font-semibold w-[30%]">
                                    {{ $user_name[$index] ?? 'Unknown User' }}
                                </td>

                                <!-- Score -->
                                <td class="py-4 px-6 text-center text-[#0D2B70] font-medium w-[20%]">
                                    {{ $p->result ?: '-' }}
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-6 text-center w-[25%]">
                                    <div class="inline-flex items-center gap-2 text-[#0D2B70] font-medium">
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
                                <td class="py-4 px-6 text-center w-[25%]">
                                    <button class="text-[#0D2B70] border border-[#0D2B70] font-bold py-2 px-6 rounded-md text-sm
                                            transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)]
                                            hover:scale-105 hover:bg-[#002C76] hover:text-white hover:shadow-md inline-flex items-center gap-2">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                        <span>View</span>
                                    </button>
                                </td>
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

        <!--     scheduling form, buttons -->
        <div class="w-[30%] flex flex-col justify-between h-full px-2">
            <form id="examDetailsForm" class="flex flex-col h-full no-spinner">
            @csrf
            <!-- Top Section: Form and Primary Actions -->
            <div class="flex flex-col gap-4">
                <span class="text-3xl text-[#0D2B70] font-bold">
                    Schedule Exam
                </span>
                                        
                <!-- VENUE -->
                <div class="flex flex-col">
                    <label for="venue" class="text-[#0D2B70] font-semibold text-sm mb-1">
                        Venue <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="venue" name="place" required
                        value="{{ $examDetails->place ?? '' }}"
                        {{ $isExamActive ? 'disabled' : '' }}
                        class="w-full px-4 py-1 border border-[#0D2B70] rounded-lg 
                            focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:border-transparent
                            transition-all duration-200 text-[#0D2B70] placeholder-gray-400 disabled:bg-gray-100 disabled:cursor-not-allowed"
                        placeholder="Enter venue location"
                    />
                </div>

                <!-- DATE -->
                <div class="flex flex-col">
                    <label for="date" class="text-[#0D2B70] font-semibold text-sm mb-1">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="date" name="date" required
                        value="{{ $examDetails->date ?? '' }}"
                        {{ $isExamActive ? 'disabled' : '' }}
                        class="w-full px-4 py-1 border border-[#0D2B70] rounded-lg 
                            focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:border-transparent
                            transition-all duration-200 text-[#0D2B70] placeholder-gray-400 disabled:bg-gray-100 disabled:cursor-not-allowed"
                    />
                </div>

                <!-- STARTING AND ENDING TIME -->
                <div class="flex flex-row justify-between gap-2">
                    <!-- STARTING TIME -->
                    <div class="flex flex-col w-1/2">
                        <label for="time" class="text-[#0D2B70] font-semibold text-sm mb-1">
                            Start Time <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="time" name="time" required
                            value="{{ $examDetails->time ?? '' }}"
                            {{ $isExamActive ? 'disabled' : '' }}
                            class="w-full px-4 py-1 border border-[#0D2B70] rounded-lg 
                                focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:border-transparent
                                transition-all duration-200 text-[#0D2B70] placeholder-gray-400 disabled:bg-gray-100 disabled:cursor-not-allowed"
                        />
                    </div>

                    <!-- ENDING TIME -->
                    <div class="flex flex-col w-1/2">
                        <label for="time_end" class="text-[#0D2B70] font-semibold text-sm mb-1">
                            End Time <span class="text-red-500">*</span>
                        </label>
                        @php
                            $endTime = '';
                            if (isset($examDetails->time) && isset($examDetails->duration)) {
                                $startTime = \Carbon\Carbon::parse($examDetails->time);
                                $endTime = $startTime->addMinutes($examDetails->duration)->format('H:i');
                            }
                        @endphp
                        <input type="time" id="time_end" name="time_end" required
                            value="{{ $endTime }}"
                            {{ $isExamActive ? 'disabled' : '' }}
                            class="w-full px-4 py-1 border border-[#0D2B70] rounded-lg 
                                focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:border-transparent
                                transition-all duration-200 text-[#0D2B70] placeholder-gray-400 disabled:bg-gray-100 disabled:cursor-not-allowed"
                        />
                    </div>
                </div>

                <!-- DURATION -->
                <div class="flex flex-col">
                    <label for="duration_display" class="text-[#0D2B70] font-semibold text-sm mb-1">
                        Duration <span class="text-red-500"></span>
                    </label>
                    <input type="text" id="duration_display" readonly
                        value="{{ isset($examDetails->duration) ? $examDetails->duration . ' minutes' : '' }}"
                        class="w-full px-4 py-1 border border-[#0D2B70] rounded-lg bg-gray-50
                            focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:border-transparent
                            transition-all duration-200 text-[#0D2B70] placeholder-gray-400"
                        placeholder="Duration (minutes)"
                    />
                    <!-- Hidden input to store the raw numeric value for form submission -->
                    <input type="hidden" id="duration" name="duration" value="{{ $examDetails->duration ?? '' }}">
                </div>

                <!-- SAVE AND START EXAM BUTTONS-->
                <div class="flex flex-col justify-between gap-2 mt-2">
                    <button type="submit" name="action" value="save_notify" 
                            {{ $isExamActive ? 'disabled' : '' }}
                            class="w-full px-4 py-2 border border-[#0D2B70] rounded-lg 
                            hover:scale-105 flex items-center justify-center gap-2
                            transition-all duration-200 text-[#0D2B70] placeholder-gray-400 font-semibold disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                        <x-heroicon-o-check class="w-5 h-5" />
                        <span>Save & Notify Applicants</span>
                    </button>
                    <button type="button" id="notify_button" onclick="notifyApplicants('{{ $vacancy->vacancy_id }}')" 
                            {{ $isExamActive ? 'disabled' : '' }}
                            class="w-full px-4 py-2 border border-[#0D2B70] rounded-lg 
                            hover:scale-105 flex items-center justify-center gap-2
                            transition-all duration-200 text-[#0D2B70] placeholder-gray-400 font-semibold disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                        <x-heroicon-o-paper-airplane class="w-5 h-5" />
                        <span>Send Link via Email</span>
                    </button>
                </div>
            </div>


            <!-- SEND LINK AND EDIT QUESTIONS (Bottom Section) -->
            <div class="flex flex-row gap-2 mt-auto">
                <button type="button" onclick="handleEditClick(event)"
                        {{ $isExamActive ? 'disabled' : '' }}
                        class="w-full px-4 py-2 bg-[#0D2B70] rounded-lg 
                        hover:scale-105 flex items-center justify-center gap-2
                        transition-all duration-200 text-white placeholder-gray-400 font-semibold disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                    <span>Edit Questions</span>
                </button>
                <button type="button" class="w-full px-4 py-2 bg-[#0D2B70] rounded-lg 
                        hover:scale-105 flex items-center justify-center gap-2
                        transition-all duration-200 text-white placeholder-gray-400 font-semibold">
                    <x-heroicon-o-play class="w-5 h-5" />
                    <span>Start Exam</span>
                </button>

            </div>
            </form>
        </div>

                                        
    </div>


    @include('partials.loader')
</main>
<script>
        function notifyApplicants(vacancyId) {
            if (!confirm("Are you sure you want to notify all applicants?")) {
                return;
            }

            notifEl = document.getElementById('notifiedAt');

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
                    return response.text().then(text => { 
                        try {
                            const json = JSON.parse(text);
                            throw new Error(json.message || text);
                        } catch(e) {
                            throw new Error(text || response.statusText);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    notifEl.innerText = "Already Notified: " + data.notified_at;

                    alert("Applicants notified successfully!");
                } else {
                    alert("Failed to notify applicants: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while notifying applicants: " + error.message);
            });


        }


    document.getElementById('examDetailsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
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
        }

        const saveButton = document.querySelector('#examDetailsForm button[type="submit"]');
        const originalText = saveButton.innerHTML;
        saveButton.disabled = true;
        saveButton.innerHTML = '<span>Saving...</span>';

        fetch(`/admin/exam_management/${vacancyId}/details/save`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => { throw new Error(text || response.statusText) });
            }
            return response.json();
        })
        .then(data => {
            saveButton.innerHTML = originalText;
            if (data.success) {
                saveButton.disabled = true;
                saveButton.classList.add('opacity-50', 'cursor-not-allowed');

                const notifyButton = document.querySelector('#notify_button');
                notifyButton.disabled = false;
                notifyButton.classList.remove('opacity-50', 'cursor-not-allowed');
                
                let msg = "Exam details saved successfully!";
                if (data.notified) {
                    msg += " Applicants have been notified.";
                    if(document.getElementById('notifiedAt')) {
                        document.getElementById('notifiedAt').innerText = "Already Notified: " + data.notified_at;
                    }
                }
                alert(msg);
            } else {
                saveButton.disabled = false;
                alert("Failed to save exam details: " + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            console.error('Error:', error);
            alert("An error occurred while saving exam details. Please check the console for more details.");
        });
    });

    // Disable the save button by default
    const saveButton = document.querySelector('#examDetailsForm button[type="submit"]');
    saveButton.disabled = true;
    saveButton.classList.add('opacity-50', 'cursor-not-allowed');

    // Store initial values
    const inputs = document.querySelectorAll('#examDetailsForm input');
    const initialValues = {};
    inputs.forEach(input => {
        initialValues[input.name] = input.value;
    });

    // Function to check for changes
    function checkForChanges() {
        let hasChanged = false;
        inputs.forEach(input => {
            if (input.value !== initialValues[input.name]) {
                hasChanged = true;
            }
        });

        // Enable or disable button based on change
        if (hasChanged) {
            saveButton.disabled = false;
            saveButton.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            saveButton.disabled = true;
            saveButton.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Listen to input changes
    inputs.forEach(input => {
        input.addEventListener('input', checkForChanges);
    });

    // Auto-calculate duration
    const startTimeInput = document.getElementById('time');
    const endTimeInput = document.getElementById('time_end');
    const durationInput = document.getElementById('duration');
    const durationDisplay = document.getElementById('duration_display');

    function calculateDuration() {
        const start = startTimeInput.value;
        const end = endTimeInput.value;

        if (start && end) {
            const startDate = new Date(`1970-01-01T${start}:00`);
            const endDate = new Date(`1970-01-01T${end}:00`);

            let diffMs = endDate - startDate;
            let diffMins = Math.round(diffMs / 60000);

            if (diffMins > 0) {
                durationInput.value = diffMins;
                durationDisplay.value = `${diffMins} minutes`;
                // Trigger change event to update button state
                durationInput.dispatchEvent(new Event('input')); 
            } else {
                durationInput.value = '';
                durationDisplay.value = 'Invalid Time';
            }
        } else {
            durationInput.value = '';
            durationDisplay.value = '';
        }
    }

    startTimeInput.addEventListener('input', calculateDuration);
    endTimeInput.addEventListener('input', calculateDuration);

    // Prevent navigation if exam is active and user tries to edit
    function handleEditClick(e) {
        e.preventDefault();
        const isExamActive = @json($isExamActive);
        
        if (isExamActive) {
            alert('Cannot edit questions while an exam is currently in progress.');
            return;
        }
        
        window.location.href = '{{ route('admin.exam.edit', $vacancy->vacancy_id) }}';
    }
</script>

@endsection
