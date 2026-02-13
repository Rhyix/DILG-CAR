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
<div class="fixed mt-3 right-10 rounded-lg px-4 py-2 shadow-lg z-50">
    <p class="uppercase text-xs text-gray-500 text-right">Time Remaining</p>
    <p id="timer" class="text-2xl font-bold text-gray-800 text-right">00:00</p>
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

<form id="exam-form" action="{{ route('exam.submit', ['vacancy_id' => $vacancy_id]) }}" method="POST">
    @csrf

    <input type="hidden" name="user_id" value="{{ Auth::id() }}">
    <input type="hidden" name="vacancy_id" value="{{ $vacancy_id }}">

    <div id="question-container" class="px-6 pb-10"></div>

    <div id="submitContainer" class="px-6 mt-6 max-w-3xl mx-auto hidden">
        <div x-data="{ showSubmitConfirm: false }" class="inline">
            <div class="flex justify-end">
                <button @click="showSubmitConfirm = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold transition min-w-[150px]">
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
                        <button @click="window.prepareSubmit()" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-full font-semibold transition">Submit</button>
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

    const totalItems = Questions.length;
    const container = document.getElementById('question-container');
    const submitContainer = document.getElementById('submitContainer');
    const form = document.getElementById('exam-form');

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

    form.onsubmit = e => { e.preventDefault(); collectCurrentAnswers(); console.log('Final answers:', answers); };

    renderAllQuestions();

    let duration = 10 * 60; // Adjust the dura
    const timerDisplay = document.getElementById('timer');
    setInterval(() => {
        if (duration >= 0) {
            timerDisplay.textContent = `${String(Math.floor(duration / 60)).padStart(2, '0')}:${String(duration % 60).padStart(2, '0')}`;
            duration--;
        } else {
            collectCurrentAnswers(); window.triggerTimesUp();
        }
    }, 1000);

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
                    ${key}: ${val}
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
}

    window.prepareSubmit = prepareSubmit;
</script>
@include('partials.loader')
@endsection
