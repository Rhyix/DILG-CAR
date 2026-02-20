@extends('layout.exam_user')

@section('title', 'Exam')

@push('styles')
<style>
    body {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
</style>
@endpush

<!-- Floating Sticky Timer (Overlay Style) -->
<div x-data="{ expanded: true }" class="fixed mt-3 right-10 bg-white rounded-lg px-4 py-2 shadow-lg z-50 border border-gray-200">
    <div class="flex items-center justify-end gap-4 mb-2">
        <!-- Circular Progress (Pacman-ish ring) -->
        <div class="relative w-12 h-12 flex-shrink-0">
            <svg class="transform -rotate-90 w-12 h-12">
                <!-- Background Circle -->
                <circle cx="24" cy="24" r="18" stroke="#e5e7eb" stroke-width="6" fill="transparent" />
                <!-- Progress Circle -->
                <circle id="timer-circle" cx="24" cy="24" r="18" stroke="#002C76" stroke-width="6" fill="transparent" stroke-linecap="round" class="transition-all duration-1000 ease-linear" />
            </svg>
        </div>

        <div>
            <p class="uppercase text-xs text-gray-500 text-right">Time Remaining</p>
            <p id="timer" class="text-2xl font-bold text-gray-800 text-right">00:00</p>
        </div>
        
        <!-- Toggle Button -->
        <button @click="expanded = !expanded" class="ml-1 text-gray-400 hover:text-[#002C76] focus:outline-none transition-colors" title="Toggle Time Info">
            <svg x-show="expanded" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            <svg x-show="!expanded" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
    
    <!-- Collapsible PST Section -->
    <div x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="border-t border-gray-200 pt-2">
        <p class="uppercase text-xs text-gray-500 text-right">Philippine Standard Time</p>
        <p id="pst-time" class="text-lg font-bold text-[#002C76] text-right">--:--:--</p>
        <p id="pst-date" class="text-xs text-gray-600 text-right">--</p>
    </div>
</div>

@section('content')
<!-- Original Header -->
<div class="px-6 mb-6">
    <h2 class="text-2xl font-bold uppercase text-[#002C76]">Engineer III</h2>
    <p class="uppercase text-sm font-semibold text-gray-700 tracking-wide">Examination</p>
</div>

 @if(session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Success!</strong>
        <p class="text-sm">{{ session('success') }}</p>
    </div>
@endif

@if ($errors->any())
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Whoops!</strong>
        <ul class="list-disc list-inside text-sm mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="exam-form" class="no-spinner" action="{{ route('exam.submit', ['vacancy_id' => $vacancy_id]) }}" method="POST">
    @csrf

    <input type="hidden" name="user_id" value="{{ Auth::id() }}">
    <input type="hidden" name="vacancy_id" value="{{ $vacancy_id }}">

    <div id="question-container" class="px-6 pb-10"></div>

    <div id="submitContainer" class="px-6 mt-6 max-w-3xl mx-auto hidden">
        <div x-data="{ showSubmitConfirm: false }" class="inline">
            <div class="flex justify-end">
                <button id="openSubmitBtn" @click="showSubmitConfirm = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold transition min-w-[150px] disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Submit
                </button>
        </div>
            <div x-show="showSubmitConfirm" x-transition class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;" @keydown.escape.window="showSubmitConfirm = false">
                <div class="bg-white p-8 rounded-2xl max-w-md w-full shadow-2xl relative">
                    <button @click="showSubmitConfirm = false" class="absolute top-4 right-4 text-gray-400 text-xl font-bold hover:text-red-600">&times;</button>
                    <h2 class="text-2xl font-extrabold text-[#002C76] text-center mb-2">Submission</h2>
                    <p class="text-gray-700 text-sm text-center mb-6">Click <span class="font-semibold text-[#0D2B70]">Submit</span> to finalize your answers.</p>
                    <div class="flex justify-center gap-4">
                        <button @click="showSubmitConfirm = false" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-full font-semibold transition">Cancel</button>
                        <button id="confirmSubmitBtn" @click="window.prepareSubmit()" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-full font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<div x-data="timesUpModal()" x-init="init()" id="timesup-modal-wrapper">
    @include('partials.exam_timesup')
</div>

<div class="px-6 mt-2 max-w-3xl mx-auto">
    <div id="saveNotification" class="hidden text-green-600 text-sm font-semibold transition-opacity duration-300">
        Answers from the previous screen have been saved.
    </div>
</div>

<script>
    const Questions = @json($examItems);

    // Jumble the sequence of the questions
    for (let i = Questions.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [Questions[i], Questions[j]] = [Questions[j], Questions[i]];
    }

    const totalItems = Questions.length;
    const container = document.getElementById('question-container');
    const submitContainer = document.getElementById('submitContainer');
    const form = document.getElementById('exam-form');
    const openSubmitBtn = document.getElementById('openSubmitBtn');
    const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');

    const answers = {};
    let switchCount = 0;

    Questions.forEach((q, idx) => q.number = idx + 1);

    function collectCurrentAnswers() {
        form.querySelectorAll('[name^="answers["]').forEach(input => {
            const idMatch = input.name.match(/answers\[(\d+)]/);
            if (!idMatch) return;
            const questionId = idMatch[1]   ;

            if (input.type === 'radio') {
                if (input.checked) {
                    answers[questionId] = input.value;
                } else if (!(questionId in answers)) {
                    answers[questionId] = ''; // explicitly store empty if nothing checked yet
                }
            } else {
                answers[questionId] = input.value.trim();
            }
        });
    }


/*
    function prepareSubmit() {
        collectCurrentAnswers(); // updates 'answers' object with current screen inputs

        // Remove old hidden inputs if re-preparing
        form.querySelectorAll('input[name^="answers["]').forEach(el => el.remove());

        // For each collected answer, create a hidden input
        Object.entries(answers).forEach(([questionId, value]) => {
            console.log(value);
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `answers[${questionId}]`;
            input.value = value;
            form.appendChild(input);
        });

        form.submit(); // finally submit
    }*/

    // Ensure native submits are routed through prepareSubmit to avoid loader issues
    form.addEventListener('submit', (e) => {
        if (!window.isSubmitting) {
            e.preventDefault();
            prepareSubmit();
        }
    });

    renderAllQuestions();

    let duration = {{ $remaining_seconds }};
    const totalDuration = {{ $total_seconds }}; // Total duration from controller
    const timerDisplay = document.getElementById('timer');
    const timerCircle = document.getElementById('timer-circle');
    
    // Circle Config
    const radius = 18;
    const circumference = 2 * Math.PI * radius;
    if (timerCircle) {
        timerCircle.style.strokeDasharray = `${circumference} ${circumference}`;
        timerCircle.style.strokeDashoffset = circumference;
    }
    
    // Initial display
    updateTimerDisplay(duration);

    const timerInterval = setInterval(() => {
        duration--;
        if (duration >= 0) {
            updateTimerDisplay(duration);
        } else {
            clearInterval(timerInterval);
            collectCurrentAnswers(); 
            window.triggerTimesUp();
            // Optional: Auto submit after a few seconds of "Times Up" modal
            setTimeout(() => {
                if(!window.isSubmitting) prepareSubmit();
            }, 3000);
        }
    }, 1000);

    function updatePST() {
        const now = new Date();
        // Format time: 01:31:35 PM
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        const timeStr = now.toLocaleTimeString('en-US', timeOptions);
        
        // Format date: Friday, 20 February 2026
        const dateOptions = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        const dateStr = now.toLocaleDateString('en-US', dateOptions);

        const tEl = document.getElementById('pst-time');
        const dEl = document.getElementById('pst-date');
        if(tEl) tEl.textContent = timeStr;
        if(dEl) dEl.textContent = dateStr;
    }
    setInterval(updatePST, 1000);
    updatePST();

    function updateTimerDisplay(seconds) {
        if (seconds < 0) seconds = 0;
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = Math.floor(seconds % 60);
        
        // Update Circle
        if (timerCircle) {
            const offset = circumference - (seconds / totalDuration) * circumference;
            timerCircle.style.strokeDashoffset = offset;
            
            // Color change warning
            if (seconds < 60) {
                timerCircle.style.stroke = '#DC2626'; // Red
            } else if (seconds < 300) {
                timerCircle.style.stroke = '#F59E0B'; // Orange
            } else {
                timerCircle.style.stroke = '#002C76'; // Blue
            }
        }

        let timeStr = '';
        if (h > 0) {
            timeStr += `${String(h).padStart(2, '0')}:`;
        }
        timeStr += `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        
        timerDisplay.textContent = timeStr;
        
        // Visual warning when low on time
        if (seconds < 60) {
            timerDisplay.classList.add('text-red-600');
            timerDisplay.classList.add('animate-pulse');
        }
    }

    function showSaveNotification() {
        const notif = document.getElementById('saveNotification');
        notif.classList.remove('hidden');
        notif.style.opacity = 1;
        setTimeout(() => { notif.style.opacity = 0; setTimeout(() => notif.classList.add('hidden'), 300); }, 2000);
    }

    function timesUpModal() {
        return {
            showTimesUp: false,
            init() { window.triggerTimesUp = () => { this.showTimesUp = true; }; }
        };
    }

    document.addEventListener('contextmenu', e => e.preventDefault());
    ['copy', 'cut', 'paste'].forEach(evt => document.addEventListener(evt, e => e.preventDefault()));

/*
    document.addEventListener('keydown', e => {
        const key = e.key.toLowerCase();
        if ((e.ctrlKey || e.metaKey) && ['c', 'u', 'x', 's', 'a'].includes(key) ||
            e.ctrlKey && e.shiftKey && ['i', 'j'].includes(key) ||
            key === 'f12') e.preventDefault();
    });
*/
    window.allowFocusLoss = false;
    window.isSubmitting = false;

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden' && !window.allowFocusLoss && !window.isSubmitting) {
            justSwitched = true;
        }
    });

    window.addEventListener('focus', () => {
        if (justSwitched) {
            justSwitched = false;
            handleTabSwitch();
        }
    });

    function handleTabSwitch() {
        switchCount++;
        if (switchCount < 3) {
            alert(`⚠️ Warning ${switchCount}/3: Please stay on the exam page.`);
        } else if (switchCount === 3) {
            if ( confirm( "🚫 You have switched tabs 3 times. Your exam will now be submitted. Click OK to continue." ) ) {
                prepareSubmit();
            }
            /* ✂️  Removed confirm() – we’re forcing the submit */
            alert("🚫 You have switched tabs 3 times. Your exam is being auto‑submitted.");
            prepareSubmit();
        }

        fetch('/log-switch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ type: 'tab-switch', count: switchCount, time: new Date().toISOString() })
        });
    }

    function prepareSubmit() {
        collectCurrentAnswers(); // updates 'answers' object with current screen inputs

        // Remove old hidden inputs if re-preparing
        form.querySelectorAll('input[type="hidden"][name^="answers["]').forEach(el => el.remove());

        // For each collected answer, create a hidden input
        Object.entries(answers).forEach(([questionId, value]) => {
            console.log(value);
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `answers[${questionId}]`;
            input.value = value;
            form.appendChild(input);
        });

        window.isSubmitting = true;
        window.allowFocusLoss = true;
        document.getElementById('exam-form').submit();
    }

    function isAllAnswered() {
        let allAnswered = true;
        Questions.forEach((q) => {
            if (!q.is_essay) {
                const checked = document.querySelector(`input[name="answers[${q.id}]"]:checked`);
                if (!checked) allAnswered = false;
            } else {
                const ta = document.querySelector(`textarea[name="answers[${q.id}]"]`);
                const val = (ta?.value || '').trim();
                if (val.length === 0) allAnswered = false;
            }
        });
        return allAnswered;
    }

    function updateSubmitEnabled() {
        const enabled = isAllAnswered();
        if (openSubmitBtn) {
            openSubmitBtn.disabled = !enabled;
            openSubmitBtn.classList.toggle('opacity-50', !enabled);
            openSubmitBtn.classList.toggle('cursor-not-allowed', !enabled);
        }
        if (confirmSubmitBtn) {
            confirmSubmitBtn.disabled = !enabled;
        }
    }

    function attachAnswerListeners() {
        // Radio inputs
        document.querySelectorAll('input[type="radio"][name^="answers["]').forEach(input => {
            input.addEventListener('change', updateSubmitEnabled);
        });
        // Essay textareas
        document.querySelectorAll('textarea[name^="answers["]').forEach(ta => {
            ta.addEventListener('input', updateSubmitEnabled);
            ta.addEventListener('change', updateSubmitEnabled);
        });
    }

    function renderAllQuestions() {
    container.innerHTML = '';
    Questions.forEach((q, idx) => {
        const qDiv = document.createElement('div');
        qDiv.className = 'bg-white rounded-xl border border-blue-200 shadow-md p-6 mb-6 max-w-3xl mx-auto';

        const number = idx + 1;
        let html = `<p class="text-lg font-semibold text-gray-800">QUESTION ${number} of ${Questions.length}</p>`;
        html += `<p class="mb-3 text-gray-700">${q.question}</p>`;

        if (!q.is_essay) {
            const opts = q.choices;
            html += Object.entries(opts).map(([key, val]) =>
                `<label class="block text-[#002C76] font-semibold">
                    <input type="radio" name="answers[${q.id}]" value="${key}" class="mr-2">
                    ${val}
                </label>`
            ).join('');
        } else {
            html += `<textarea name="answers[${q.id}]" class="w-full border border-gray-300 rounded-md p-4 min-h-[150px]" placeholder="Type your answer..."></textarea>`;
        }

        qDiv.innerHTML = html;
        container.appendChild(qDiv);
    });

    // Always show submit button at bottom now
    submitContainer.classList.remove('hidden');
    // After rendering inputs, attach listeners and set initial state
    attachAnswerListeners();
    updateSubmitEnabled();
}

    window.prepareSubmit = prepareSubmit;
</script>
@include('partials.loader')
@endsection
