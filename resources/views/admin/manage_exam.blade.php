@extends('layout.admin')
@section('title', 'DILG - Manage Exam')
@section('content')

<main class="w-full max-w-7xl space-x-2">
    <!-- Title bar -->
    <div class="flex items-center gap-4 mb-5">
        <button aria-label="Back" onclick="window.history.back()" class="p-2 rounded-full bg-[#D9D9D9] hover:bg-[#002C76] h-11 w-11 flex items-center justify-center transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="use-loader h-8 w-8 text-[#002c76] hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Title and Sub-labels Container -->
        <div class="flex flex-col gap-2 w-full">
            <div class="flex items-center px-4 py-2 rounded-xl bg-[#F1F6FF] border-2 border-[#002C76] text-[#002C76] font-extrabold font-montserrat text-3xl gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m-6-8h6m2 12H7a2 2 0 01-2-2V6a2 2 0 012-2h7.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V18a2 2 0 01-2 2z" />
                </svg>
                Manage Exam
            </div>
        </div>
    </div>

    <!-- Vacancy Info + Action Buttons -->
<section class="bg-[#F1F6FF] p-6 rounded-xl shadow-sm">
    <!-- Top Row: Info and Buttons -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
        <!-- Left Info -->
        <div class="space-y-1 text-sm text-[#002C76] font-montserrat">
            <p><span class="font-bold">VACANCY ID:</span> {{ $vacancy->vacancy_id }}</p>
            <p><span class="font-bold">EXAM ID:</span> {{ $vacancy->vacancy_id }}-EXAM</p>
            <p><span class="font-bold">EXAM LINK:</span> sample.domain.com/exam/{{ $vacancy->vacancy_id }}-exam</p>
        </div>

        <!-- Right Buttons -->
        <div class="flex flex-col">
            <div class="flex gap-2">
                <button class="bg-[#2559B1] hover:bg-blue-900 text-white text-sm font-semibold rounded-full px-4 py-2 flex items-center gap-2 shadow">
                    <i class="fa-regular fa-copy"></i> Copy Link
                </button>
                <button onclick="window.location.href='{{ route('admin.exam.edit', $vacancy->vacancy_id) }}'" class="use-loader bg-red-600 hover:bg-red-800 text-white text-sm font-semibold rounded-full px-4 py-2 flex items-center gap-2 shadow">
                    <i class="fa-solid fa-pencil"></i> Edit Questions
                </button>
                <button id="notify_button" onclick="notifyApplicants('{{ $vacancy->vacancy_id }}')" {{ !isset($examDetails['time'])? 'disabled' : '' }}
                class="{{ !isset($examDetails['time'])? 'opacity-50 cursor-not-allowed' : '' }} bg-green-600 hover:bg-green-800 text-white text-sm font-semibold rounded-full px-4 py-2 flex items-center gap-2 shadow">
                    <i class="fa-solid fa-bell"></i> Notify applicants
                </button>
            </div>
            <p id="notifiedAt" class="self-end font-montserrat text-xs m-2 mt-1">
                @if ($examDetails && $examDetails->notified_at)
                    Already Notified: {{ $examDetails->notified_at }}
                @endif
            </p>
        </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-gray-300 my-4"></div>

    <!-- Bottom Row: Title + Action -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <!-- Job Title -->
        <div>
            <div class="text-xl md:text-2xl font-extrabold text-[#002C76]">{{ $vacancy->position_title}}</div>
            <div class="text-sm font-semibold text-[#002C76]">{{ $vacancy->vacancy_type}}</div>
        </div>

        <!-- Start Exam -->
        <div class="flex flex-col items-center justify-center space-y-1 text-center min-w-[170px]">
            <button class="bg-green-600 hover:bg-green-800 transition text-white text-sm font-semibold rounded-full flex items-center gap-2 px-5 py-2">
                <i class="fa-solid fa-play"></i> Start Exam
            </button>
            <span class="text-sm font-bold text-red-600">2/4 are READY</span>
        </div>
    </div>

        <form id="examDetailsForm">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <!-- Time -->
            <div>
                <label class="block text-sm font-semibold text-[#002C76] mb-1">Time:</label>
                <input type="time" name="time" value="{{ $examDetails->time ?? '' }}" required
                 class="w-full text-sm px-3 py-2 border border-[#002C76] rounded-md focus:outline-none focus:ring-1 focus:ring-[#002C76] shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-[#002C76] mb-1">Date:</label>
                <input type="date" name="date" value="{{ $examDetails->date ?? '' }}" required
                 class="w-full text-sm px-3 py-2 border border-[#002C76] rounded-md focus:outline-none focus:ring-1 focus:ring-[#002C76] shadow-sm">
            </div>
           <div>
                <label class="block text-sm font-semibold text-[#002C76] mb-1">Place:</label>
                <input type="text" name="place" placeholder="Enter place" value="{{ $examDetails->place ?? '' }}" required
                    class="w-full text-sm px-3 py-2 border border-[#002C76] rounded-md focus:outline-none focus:ring-1 focus:ring-[#002C76] shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-[#002C76] mb-1">Duration(mins):</label>
                <input style="-moz-appearance: textfield; -webkit-appearance: textfield; margin: 0;" type="number" max="999" name="duration" placeholder="Enter Duration" value="{{ $examDetails->duration ?? '' }}" required
                    class="w-full text-sm px-3 py-2 border border-[#002C76] rounded-md focus:outline-none focus:ring-1 focus:ring-[#002C76] shadow-sm">
            </div>
                <!-- Button -->
                <div class="sm:col-span-4 flex justify-end">
                    <button type="submit" disabled class="opacity-50 cursor-not-allowed bg-green-600 hover:bg-green-800 transition text-white text-sm font-semibold rounded-full flex items-center gap-2 px-5 py-2">
                        <i class="fa-solid fa-save"></i> Save Details
                    </button>
                </div>
            </div>
        </form>

</section>


    <!-- Table Header -->
    <section class="grid grid-cols-4 gap-5 bg-[#0D2B70] text-white font-bold rounded-3xl py-5 px-6 select-none w-full">
        <div class="flex items-center justify-start">NAME</div>
        <div class="flex items-center justify-center">SCORE</div>
        <div class="flex items-center justify-start">STATUS</div>
        <div class="flex items-center justify-center"></div>
    </section>

    <!-- Table Rows -->
    <section class="space-y-3 w-full mt-4">
        @if (count($participants) > 0)
            @foreach ($participants as $index => $p)
            <div class="grid grid-cols-4 gap-4 border-2 border-[#0D2B70] rounded-3xl py-4 px-6 items-center text-[#0D2B70] bg-white shadow-sm">
                <!-- Name -->
                <div class="font-bold text-sm truncate">{{ $user_name[$index] }}</div>

                <!-- Score (center-aligned to match header) -->
                <div class="flex justify-center font-bold text-sm">{{ $p['result'] ?: '-' }}</div>

                <!-- Status -->
                <div class="text-sm font-semibold text-center flex items-center justify-start gap-2">
                    @php
                        $statusColors = [
                            'ready' => '#4ade80',        // green-400
                            'in-progress' => '#facc15',  // yellow-400
                            'submitted' => '#3b82f6',  // blue-500
                            'pending' => '#f75555',  // red
                        ];

                        $status = strtolower($p['status']);
                        $color = $statusColors[$status] ?? '#9ca3af'; // gray-400 as default
                    @endphp
                    <i class="fa-solid fa-circle" style="color: {{ $color }}"></i>
                    {{ $p['status'] }}
                </div>

                <!-- Button -->
                <div class="flex justify-end">
                    @if($p['result'] > 0)
                    <a href="{{ route('admin.view_exam', [$p->vacancy_id, $p->user_id] ) }}" class="bg-[#008000] hover:bg-green-900 transition text-white text-sm font-medium rounded-full flex items-center gap-2 px-4 py-1.5">
                        <i class="fa-solid fa-play"></i>
                        View Answers
                    </a>
                    @else
                    <div class="h-[36px] w-[130px]"></div> <!-- Maintains layout spacing -->
                    @endif
                </div>
            </div>
            @endforeach
        @else
            <div x-show="questions.length === 0" class="text-center text-gray-500 mt-10">
            <p class="text-xl font-semibold">There are no participants yet.</p>
            </div>
        @endif
    </section>
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
