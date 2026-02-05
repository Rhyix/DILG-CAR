@extends('layout.admin')
@section('title', 'DILG - Manage Exam')
@section('content')

<main class="w-full max-w-7xl h-[calc(100vh-6rem)] flex flex-col space-y-4 overflow-hidden">
    <!-- header -->
    <section class="flex-none flex items-center space-x-4 max-w-full border-b border-[#0D2B70]">
        <button aria-label="Back" onclick="window.location.href='{{ route('admin_exam_management') }}'" class="use-loader group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <h1 class="flex items-center gap-3 w-full py-2 tracking-wide select-none">
            <span class="text-[#0D2B70] text-4xl font-montserrat whitespace-nowrap">Exam Overview</span>
        </h1>
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
                    <input type="text" id="venue" name="venue" required
                        class="w-full px-4 py-1 border border-[#0D2B70] rounded-lg 
                            focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:border-transparent
                            transition-all duration-200 text-[#0D2B70] placeholder-gray-400"
                        placeholder="Enter venue location"
                    />
                </div>

                <!-- DATE -->
                <div class="flex flex-col">
                    <label for="date" class="text-[#0D2B70] font-semibold text-sm mb-1">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="date" name="date" required
                        class="w-full px-4 py-1 border border-[#0D2B70] rounded-lg 
                            focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:border-transparent
                            transition-all duration-200 text-[#0D2B70] placeholder-gray-400"
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
                            class="w-full px-4 py-1 border border-[#0D2B70] rounded-lg 
                                focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:border-transparent
                                transition-all duration-200 text-[#0D2B70] placeholder-gray-400"
                        />
                    </div>

                    <!-- ENDING TIME -->
                    <div class="flex flex-col w-1/2">
                        <label for="time_end" class="text-[#0D2B70] font-semibold text-sm mb-1">
                            End Time <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="time_end" name="time_end" required
                            class="w-full px-4 py-1 border border-[#0D2B70] rounded-lg 
                                focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:border-transparent
                                transition-all duration-200 text-[#0D2B70] placeholder-gray-400"
                        />
                    </div>
                </div>

                <!-- DURATION -->
                <div class="flex flex-col">
                    <label for="duration" class="text-[#0D2B70] font-semibold text-sm mb-1">
                        Duration <span class="text-red-500"></span>
                    </label>
                    <input type="text" id="venue" name="duration" disabled
                        class="w-full px-4 py-1 border border-[#0D2B70] rounded-lg 
                            focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:border-transparent
                            transition-all duration-200 text-[#0D2B70] placeholder-gray-400"
                        placeholder="Duration"
                    />
                </div>

                <!-- SAVE AND START EXAM BUTTONS-->
                <div class="flex flex-row justify-between gap-2 mt-2">
                    <button class="w-full px-4 py-2 border border-[#0D2B70] rounded-lg 
                            hover:scale-105 flex items-center justify-center gap-2
                            transition-all duration-200 text-[#0D2B70] placeholder-gray-400 font-semibold">
                        <x-heroicon-o-check class="w-5 h-5" />
                        <span>Save</span>
                    </button>
                    <button class="w-full px-4 py-2 bg-[#0D2B70] rounded-lg 
                            hover:scale-105 flex items-center justify-center gap-2
                            transition-all duration-200 text-white placeholder-gray-400 font-semibold">
                        <x-heroicon-o-play class="w-5 h-5" />
                        <span>Start Exam</span>
                    </button>
                </div>
            </div>


            <!-- SEND LINK AND EDIT QUESTIONS (Bottom Section) -->
            <div class="flex flex-col gap-2 mt-auto">
                <button class="w-full px-4 py-2 border border-[#0D2B70] rounded-lg 
                        hover:scale-105 flex items-center justify-center gap-2
                        transition-all duration-200 text-[#0D2B70] placeholder-gray-400 font-semibold">
                    <x-heroicon-o-paper-airplane class="w-5 h-5" />
                    <span>Send Link via Email</span>
                </button>
                <button onclick="window.location.href='{{ route('admin.exam.edit', $vacancy->vacancy_id) }}'"
                        class="w-full px-4 py-2 bg-[#0D2B70] rounded-lg 
                        hover:scale-105 flex items-center justify-center gap-2
                        transition-all duration-200 text-white placeholder-gray-400 font-semibold">
                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                    <span>Edit Questions</span>
                </button>
            </div>

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
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    notifEl.innerText = "Already Notified: " + data.notified_at;

                    alert("Applicants notified successfully!");
                } else {
                    alert("Failed to notify applicants.");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while notifying applicants.");
            });


        }


    document.getElementById('examDetailsForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const vacancyId = '{{ $vacancy->vacancy_id }}';
        const formData = new FormData(this);

        fetch(`/admin/exam_management/${vacancyId}/details/save`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const saveButton = document.querySelector('#examDetailsForm button[type="submit"]');
                saveButton.disabled = true;
                saveButton.classList.add('opacity-50', 'cursor-not-allowed');

                const notifyButton = document.querySelector('#notify_button');
                notifyButton.disabled = false;
                notifyButton.classList.remove('opacity-50', 'cursor-not-allowed');
                alert("Exam details saved successfully!");
            } else {
                alert("Failed to save exam details.");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while saving exam details.");
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



</script>

@endsection
