@extends('layout.exam_user')

@section('title', 'Exam Lobby')

@push('styles')
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    ::-webkit-scrollbar {
        display: none;
    }
    .flex-1 {
        height: 100%;
    }
    main {
        overflow: hidden !important;
        height: 100% !important;
    }
</style>
@endpush

@section('scroll_class', 'flex-1 h-full')

@section('content')
<div class="flex-1 flex items-stretch justify-center h-full">
    <div class="flex gap-6 w-full">
        <div class="flex-1 bg-white rounded-xl shadow-md border border-blue-300 p-8 flex flex-col justify-center items-center">
            <div class="flex flex-col items-center text-center space-y-2">
                <h3 class="text-3xl font-extrabold text-black">{{ $vacancy->position_title ?? 'Examination' }}</h3>
                <p class="text-lg font-semibold tracking-widest uppercase text-gray-700">EXAMINATION</p>
                @if($examDetail)
                <p class="text-gray-700 text-lg">
                    {{ \Carbon\Carbon::parse($examDetail->date)->format('F d, Y') }} | 
                    {{ \Carbon\Carbon::parse($examDetail->time)->format('h:i A') }}
                </p>
                <p class="text-gray-700 text-lg mb-4">Duration: {{ $examDetail->duration }} minutes</p>
                @else
                <p class="text-gray-700 text-lg mb-4">Schedule to be announced</p>
                @endif

                <div id="waitingMessage" class="mt-6 flex items-center gap-2">
                    <div class="w-4 h-4 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-lg text-gray-600">Waiting for the Admin to start the exam.</p>
                </div>

                <div id="examStartedMessage" class="hidden mt-6 text-green-600 font-semibold text-lg text-center">
                    The exam has started! Redirecting in <span id="startCountdown">5</span> seconds...
                </div>
            </div>
        </div>

        <div class="w-72 bg-white rounded-xl shadow-md border border-blue-300 p-6 flex flex-col">
            <h4 class="font-bold text-black mb-4 uppercase text-sm tracking-wider">
                Examination Reminders
            </h4>
            <ol class="list-decimal list-inside text-gray-800 space-y-2 text-lg">
                <li>Ensure you have a stable internet connection.</li>
                <li>Do not refresh the page once the exam starts.</li>
                <li>Do not switch tabs or windows.</li>
                <li>The exam will auto-submit when time is up.</li>
            </ol>
        </div>
    </div>
</div>

<form id="redirect-form" action="{{ route('user.exam_question_page', ['vacancy_id' => $vacancy_id]) }}" method="GET" class="hidden"></form>

<script>
    let pollInterval = null;
    let countdownInterval = null;

    function startPolling() {
        if (pollInterval) return; // Prevent multiple pollers
        checkStatus();
        pollInterval = setInterval(checkStatus, 1000);
    }

    function checkStatus() {
        console.log("Checking if admin started exam...");
        fetch("{{ route('exam.status.check', ['vacancy_id' => $vacancy_id]) }}", {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            cache: 'no-store',
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(t => { throw new Error(`Status check failed: ${response.status} ${t}`) });
            }
            return response.json();
        })
        .then(data => {
            if (data && data.started === true) {
                 markExamStarted();
            }
        })
        .catch(error => {
            console.warn("Exam status polling error:", error.message);
        });
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    function markExamStarted() {
        if (countdownInterval) return; // Prevent multiple intervals

        document.getElementById('waitingMessage').classList.add('hidden');
        document.getElementById('examStartedMessage').classList.remove('hidden');
        stopPolling();

        const countdownEl = document.getElementById('startCountdown');
        let remaining = 5;
        countdownEl.textContent = remaining;
        
        countdownInterval = setInterval(() => {
            remaining -= 1;
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                document.getElementById('redirect-form').submit();
            } else {
                countdownEl.textContent = remaining;
            }
        }, 1000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        startPolling(); // Auto-start polling on load
    });
</script>
@include('partials.loader')
@endsection
