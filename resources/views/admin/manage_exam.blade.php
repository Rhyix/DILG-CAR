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
                    <button class="bg-[#0D2B70] hover:scale-105 text-white text-sm font-semibold rounded-full px-4 py-2 flex items-center gap-2 shadow">
                        <i class="fa-regular fa-copy"></i> Copy Link
                    </button>
                    <button onclick="window.location.href='{{ route('admin.exam.edit.vue', $vacancy->vacancy_id) }}'"
                        class="use-loader border border-[#0D2B70] text-[#0D2B70] hover:scale-105 text-sm font-semibold rounded-full px-4 py-2 flex items-center gap-2 shadow">
                        <i class="fa-solid fa-pencil"></i> Edit Questions
                    </button>
                    {{-- <button id="notify_button" onclick="notifyApplicants('{{ $vacancy->vacancy_id }}')" {{ !isset($examDetails['time'])? 'disabled' : '' }}
                    class="{{ !isset($examDetails['time'])? 'opacity-50 cursor-not-allowed' : '' }} bg-green-600 hover:bg-green-800 text-white text-sm font-semibold rounded-full px-4 py-2 flex items-center gap-2 shadow">
                        <i class="fa-solid fa-bell"></i> Notify applicants
                    </button> --}}
                </div>
                <p id="notifiedAt" class="self-end font-montserrat text-xs m-2 mt-1">
                    @if ($examDetails && $examDetails->notified_at)
                        Already Notified: {{ $examDetails->notified_at }}
                    @endif
                </p>
            </div>
        </div>

        <!-- Divider -->
        {{-- <div class="border-t border-gray-300 my-4"></div> --}}
    <hr class="my-4" style="border: none; height: 2px; background-color: #0D2B70;">

        <!-- Bottom Row: Title + Action -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <!-- Job Title -->
            <div>
                <div class="text-xl md:text-2xl font-extrabold text-[#002C76]">{{ $vacancy->position_title}}</div>
                <div class="text-sm font-semibold text-[#002C76]">{{ $vacancy->vacancy_type}}</div>
            </div>

            <!-- Start Exam -->
            {{-- <div class="flex flex-col items-center justify-center space-y-1 text-center min-w-[170px]">
                <button class="bg-green-600 hover:bg-green-800 transition text-white text-sm font-semibold rounded-full flex items-center gap-2 px-5 py-2">
                    <i class="fa-solid fa-play"></i> Start Exam
                </button>
                <span class="text-sm font-bold text-red-600">2/4 are READY</span>
            </div> --}}
        </div>

            <form id="examDetailsForm">
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
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

                    {{-- NOTE: nilagyan q ng ending time, para ma auto calculate yung duration, mas comprehensive --}}
                    <!-- STARTING TIME -->
                    <div>
                        <label class="block text-sm font-semibold text-[#002C76] mb-1">Starting time:</label>
                        <input type="time" name="time" value="{{ $examDetails->time ?? '' }}" required
                        class="w-full text-sm px-3 py-2 border border-[#002C76] rounded-md focus:outline-none focus:ring-1 focus:ring-[#002C76] shadow-sm">
                    </div>
                    {{-- ENDING TIME --}}
                    <div>
                        <label class="block text-sm font-semibold text-[#002C76] mb-1">Ending time:</label>
                        <input type="time" name="time" value="{{ $examDetails->time ?? '' }}" required
                        class="w-full text-sm px-3 py-2 border border-[#002C76] rounded-md focus:outline-none focus:ring-1 focus:ring-[#002C76] shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#002C76] mb-1">Duration(mins):</label>
                        <input disabled style="-moz-appearance: textfield; -webkit-appearance: textfield; margin: 0;" type="number" max="999" name="duration" placeholder="Duration" value="{{ $examDetails->duration ?? '' }}" required
                            class="w-full text-sm px-3 py-2 border border-[#002C76] rounded-md focus:outline-none focus:ring-1 focus:ring-[#002C76] shadow-sm">
                    </div>
                    
                    {{-- 
                        NOTE: if info is not saved yet, start exam button is disabled
                        - if all examiners are not yet ready, exam button is disabled

                        both conditions must be met
                    
                    --}}
                    <div class="sm:col-span-5 flex justify-end items-center gap-3">
                        {{-- once saved, notify applicants --}}
                        <button
                            type="submit"
                            disabled
                            class="opacity-50 cursor-not-allowed bg-green-600 hover:bg-green-800 transition text-white text-sm font-semibold rounded-full flex items-center gap-2 px-5 py-2">
                            <i class="fa-solid fa-save"></i> Save Details
                        </button>
                        <button
                            disabled
                            class="bg-green-600 hover:bg-green-800 transition text-white text-sm font-semibold rounded-full flex items-center gap-2 px-5 py-2"
                        >
                            <i class="fa-solid fa-play"></i> Start Exam
                        </button>
                    </div>
                </div>
            </form>

    </section>

    <hr class="my-4" style="border: none; height: 2px; background-color: #0D2B70;">

    <!-- Table Header -->
    <section class="bg-gradient-to-r from-[#0D2B70] to-[#1a3a8a] text-white font-bold rounded-2xl shadow-lg overflow-hidden">
        <div class="grid grid-cols-4 gap-4 py-4 px-6 border-b border-white/20">
            <div class="flex items-center">
                <i class="fas fa-user mr-2 text-white/80"></i>
                NAME
            </div>
            <div class="flex items-center justify-center">
                <i class="fas fa-chart-line mr-2 text-white/80"></i>
                SCORE
            </div>
            <div class="flex items-center">
                <i class="fas fa-info-circle mr-2 text-white/80"></i>
                STATUS
            </div>
            <div class="flex items-center justify-center">
                <i class="fas fa-cog mr-2 text-white/80"></i>
                ACTIONS
            </div>
        </div>
    </section>

    <!-- Table Rows -->
    <section class="mt-4 space-y-2">
        @if (count($participants) > 0)
            @foreach ($participants as $index => $p)
            <div class="group bg-white rounded-xl border border-[#0D2B70]/20 hover:border-[#0D2B70]/40 hover:shadow-md transition-all duration-200 overflow-hidden">
                <div class="grid grid-cols-4 gap-4 py-3 px-6 items-center">
                    <!-- Name -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-[#0D2B70]/10 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-user text-[#0D2B70]/60 text-xs"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-[#0D2B70] text-sm truncate">{{ $user_name[$index] }}</div>
                            <div class="text-xs text-[#0D2B70]/50">Participant</div>
                        </div>
                    </div>

                    <!-- Score -->
                    <div class="flex justify-center">
                        @if($p['result'] > 0)
                            <div class="text-center">
                                <div class="text-lg font-bold text-[#0D2B70]">{{ $p['result'] }}</div>
                                <div class="text-xs text-[#0D2B70]/60">points</div>
                            </div>
                        @else
                            <div class="text-center">
                                <div class="text-lg font-bold text-[#0D2B70]/30">-</div>
                                <div class="text-xs text-[#0D2B70]/60">not taken</div>
                            </div>
                        @endif
                    </div>

                    <!-- Status -->
                    <div class="flex items-center">
                        @php
                            $statusConfig = [
                                'ready' => ['color' => '#10b981', 'bg' => '#10b981/10', 'icon' => 'fa-check-circle'],
                                'in-progress' => ['color' => '#f59e0b', 'bg' => '#f59e0b/10', 'icon' => 'fa-spinner'],
                                'submitted' => ['color' => '#3b82f6', 'bg' => '#3b82f6/10', 'icon' => 'fa-paper-plane'],
                                'pending' => ['color' => '#ef4444', 'bg' => '#ef4444/10', 'icon' => 'fa-clock'],
                            ];

                            $status = strtolower($p['status']);
                            $config = $statusConfig[$status] ?? ['color' => '#9ca3af', 'bg' => '#9ca3af/10', 'icon' => 'fa-question-circle'];
                        @endphp
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full" style="background-color: {{ $config['bg'] }}">
                            <i class="fas {{ $config['icon'] }}" style="color: {{ $config['color'] }}"></i>
                            <span class="text-xs font-medium" style="color: {{ $config['color'] }}">{{ $p['status'] }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end">
                        @if($p['result'] > 0)
                            <a href="{{ route('admin.view_exam', [$p->vacancy_id, $p->user_id] ) }}" 
                               class="bg-[#0D2B70] hover:bg-[#0D2B70]/80 text-white text-sm font-medium rounded-lg flex items-center gap-2 px-4 py-2 transition-all duration-200 hover:shadow-md hover:scale-[1.02]">
                                <i class="fas fa-eye"></i>
                                View
                            </a>
                        @else
                            <div class="flex items-center gap-2 px-4 py-2">
                                <span class="text-xs text-[#0D2B70]/40">No results yet</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="bg-white rounded-xl border border-[#0D2B70]/20 p-12 text-center">
                <div class="w-16 h-16 bg-[#0D2B70]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-[#0D2B70]/40 text-2xl"></i>
                </div>
                <p class="text-lg font-semibold text-[#0D2B70] mb-2">No Participants Yet</p>
                <p class="text-sm text-[#0D2B70]/60">Participants will appear here once they register for the exam.</p>
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
