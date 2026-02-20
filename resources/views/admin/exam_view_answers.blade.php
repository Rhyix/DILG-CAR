@extends('layout.admin')

@section('title', 'Juan Dela Cruz - Answers')

@push('styles')
    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body, .font-montserrat {
            font-family: 'Montserrat', sans-serif !important;
        }
    </style>
@endpush

@section('content')
<div class="px-6 mb-6 flex justify-between items-center font-montserrat">
    <div class="flex items-center gap-4">
        <!-- Back Button -->
        <button aria-label="Go back" title="Go back"
            class="w-12 h-12 rounded-full bg-[#D8DCE3] flex justify-center items-center text-[#1E3664] hover:bg-[#c0c7d8] transition"
            onclick="window.history.back()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="#1E3664" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <div>
            <h2 id="applicant-name" class="text-2xl font-bold uppercase text-[#002C76]">{{ $userName->name }}</h2>
            <p class="uppercase text-sm font-semibold text-gray-700 tracking-wide">Examination Answers</p>
            <p class="text-xs font-medium text-gray-500">For Position: <span class="font-semibold text-[#002C76]">{{ $positionTitle->position_title }}</span></p>
        </div>
    </div>

    <div class="flex flex-col items-end space-y-1 text-sm text-gray-700 font-montserrat">
        <div class="text-right leading-tight">
            <p><span class="font-medium text-gray-600">Last Refreshed:</span> <span id="last-refreshed">--</span></p>
            <p><span class="font-medium text-gray-600">Score:</span> <span id="score">--</span></p>
        </div>
        <button onclick="renderAnswers()" aria-label="Refresh answers" title="Refresh answers"
            class="mt-2 w-10 h-10 rounded-full bg-blue-600 hover:bg-blue-700 flex justify-center items-center transition shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <polyline points="1 4 1 10 7 10"></polyline>
                <polyline points="23 20 23 14 17 14"></polyline>
                <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path>
            </svg>
        </button>
    </div>
</div>
<form action="{{ route('admin.save_result', ['vacancy_id' => $vacancy_id, 'user_id' => $user_id] ) }}" method="POST">
@csrf
<input type="hidden" name="result" id="result">
<div id="question-container" class="px-6 pb-10 font-montserrat"></div>
</form>
<script>
    let Questions = @json($examResults);

    // This object stores the updated correctness per question
    const checkedAnswers = {};
    let pollingInterval = null;

    let correctCount = 0;
    let final_score = 0;
    let highest_score = 0;

    function startPolling() {
        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(fetchAnswers, 5000); // Poll every 5 seconds
    }

    function fetchAnswers() {
        const refreshEl = document.getElementById('last-refreshed');
        if(refreshEl) refreshEl.classList.add('animate-pulse', 'text-blue-600');

        fetch(`{{ route('admin.view_exam.json', ['vacancy_id' => $vacancy_id, 'user_id' => $user_id]) }}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the Questions data source
                    // We need to be careful not to overwrite 'score' if we want to preserve local edits,
                    // BUT for now let's assume 'Questions' tracks the server state.
                    // The dropdowns are the 'local state'.
                    
                    // Actually, we only care about 'given_answer' updating.
                    const newQuestions = data.examResults;
                    
                    newQuestions.forEach(nq => {
                        const oldQ = Questions.find(oq => oq.id === nq.id);
                        if (oldQ) {
                            oldQ.given_answer = nq.given_answer;
                            oldQ.given_answer_text = nq.given_answer_text;
                            oldQ.is_correct = nq.is_correct;
                            // We don't update 'score' here to avoid messing with manual grading in progress
                            // unless we want to sync concurrent admins. Let's stick to answers.
                        }
                    });

                    updateAnswersUI();
                    
                    if(refreshEl) {
                        refreshEl.textContent = new Date().toLocaleString();
                        refreshEl.classList.remove('animate-pulse', 'text-blue-600');
                    }
                }
            })
            .catch(err => console.error('Polling error:', err));
    }

    function updateAnswersUI() {
        Questions.forEach(q => {
            // Update Answer Text
            const ansEl = document.getElementById(`answer-text-${q.id}`);
            if (ansEl) {
                if (q.is_essay) {
                     // For essay
                     ansEl.textContent = q.given_answer ?? 'No answer yet';
                } else {
                     // For MCQ
                     const text = q.given_answer 
                        ? `${q.given_answer}${q.given_answer_text ? ' - ' + q.given_answer_text : ''}`
                        : 'No answer';
                     ansEl.textContent = text;
                }
            }

            // Update Badge (MCQ only)
            if (!q.is_essay) {
                const badgeEl = document.getElementById(`status-badge-${q.id}`);
                const inputEl = document.querySelector(`input[name="scores[${q.id}]"]`);
                
                if (badgeEl) {
                    if (q.is_correct) {
                        badgeEl.className = 'text-sm font-semibold px-3 py-1 rounded-full bg-green-100 text-green-700';
                        badgeEl.textContent = 'Correct';
                    } else {
                        badgeEl.className = 'text-sm font-semibold px-3 py-1 rounded-full bg-red-100 text-red-700';
                        badgeEl.textContent = 'Incorrect';
                    }
                }
                
                // Also update the hidden input for score if auto-graded
                if (inputEl) {
                    inputEl.value = q.is_correct ? '1' : '0';
                }
            }
        });
        
        // Recompute score based on new auto-grades + current dropdown values
        recomputeScore();
    }

    function recomputeScore() {
        let mcqCorrect = 0;
        let mcqCount = 0;
        let essaySum = 0;
        let essayMax = 0;

        Questions.forEach((q) => {
            if (q.is_essay) {
                essayMax += 4;
                const sel = document.getElementById(`score-select-${q.id}`);
                // Use current DOM value, not the Question object (which might be stale from DB)
                const val = sel && sel.value !== '' ? parseInt(sel.value, 10) : null;
                if (val !== null) essaySum += val;
            } else {
                mcqCount++;
                if (q.is_correct) mcqCorrect++;
            }
        });

        final_score = mcqCorrect + essaySum;
        highest_score = mcqCount + essayMax;

        const scoreEl = document.getElementById('score');
        if(scoreEl) scoreEl.textContent = `${final_score} / ${highest_score}`;

        const resultEl = document.getElementById('result');
        if(resultEl) resultEl.value = `${final_score} / ${highest_score}`;
    }

    function updateDropdownColor(selectElement) {
        const classMap = {
            "4": ["bg-green-100", "text-green-700"],
            "3": ["bg-[#bbdb44]/20", "text-[#749300]"],
            "2": ["bg-[#f7e379]/40", "text-[#b29100]"],
            "1": ["bg-[#f2a134]/30", "text-[#cc6d00]"],
            "0": ["bg-red-100", "text-red-700"],
            "not scored": ["bg-gray-200", "text-gray-500"]
        };

        // Remove all existing bg/text classes
        if(selectElement) {
            selectElement.classList.remove(
                "bg-green-100", "text-green-700",
                "bg-[#bbdb44]/20", "text-[#749300]",
                "bg-[#f7e379]/40", "text-[#b29100]",
                "bg-[#f2a134]/30", "text-[#cc6d00]",
                "bg-red-100", "text-red-700",
                "bg-gray-200", "text-gray-500"
            );
            const selected = selectElement.value;
            const [bgClass, textClass] = classMap[selected] || [];
    
            if (bgClass && textClass) {
                selectElement.classList.add(bgClass, textClass);
            }
        }
    }


    // Optional: Apply color on page load based on pre-selected value
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll("select[id^='score-select-']").forEach(select => {
            updateDropdownColor(select);
        });
        renderAnswers();
        startPolling();
    });

    function renderAnswers() {
        const container = document.getElementById('question-container');
        const refreshedEl = document.getElementById('last-refreshed');
        const scoreEl = document.getElementById('score');
        container.innerHTML = '';

        Questions.forEach((q, index) => {
            const userAnswer = q.given_answer ?? 'No answer yet';
            // use manual check if available, otherwise fallback to auto
            const isCorrect = q.is_correct;
            const isEssay = q.is_essay;
            if (isCorrect) correctCount++;

            const div = document.createElement('div');
            div.className = 'bg-white rounded-xl border border-blue-200 shadow-md p-6 mb-6 max-w-3xl mx-auto';

            div.innerHTML = `
                <div class="flex justify-between items-start mb-1">
                    <div>
                        <p class="text-lg font-semibold text-gray-800">QUESTION ${index + 1} of ${Questions.length}</p>
                    </div>
            <div class="flex items-center gap-3">
                        ${!isEssay ? `
                            <input name="scores[${q.id}]" type="hidden" ${isCorrect ? 'value="1"' : 'value="0"'}>
                            <span id="status-badge-${q.id}" class="text-sm font-semibold px-3 py-1 rounded-full ${isCorrect ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                                ${isCorrect ? 'Correct' : 'Incorrect'}
                            </span>
                        ` : `
                            <div class="flex items-end gap-4">
                                <div class="flex-1">
                                    <select
                                        name="scores[${q.id}]"
                                        id="score-select-${q.id}"
                                        onchange="recomputeScore(); updateDropdownColor(this)"
                                        class="text-sm font-semibold px-3 py-1 rounded-full transition ease-in-out duration-150 scored">
                                        <option value="" ${q.score == '' ? 'selected' : ''}>Not Scored</option>
                                        <option value=4 ${q.score == '4' ? 'selected' : ''}>4 - Excellent</option>
                                        <option value=3 ${q.score == '3' ? 'selected' : ''}>3 - Great</option>
                                        <option value=2 ${q.score == '2' ? 'selected' : ''}>2 - Good</option>
                                        <option value=1 ${q.score == '1' ? 'selected' : ''}>1 - Fair</option>
                                        <option value=0 ${q.score == '0' ? 'selected' : ''}>0 - Poor</option>
                                    </select>
                                </div>
                            </div>
                        `}
                    </div>
                </div>
                <p class="mb-3 text-gray-700">${q.question}</p>
                ${
                    !isEssay
                    ? `
                        <div class="bg-gray-50 border border-gray-200 rounded px-4 py-3 space-y-1">
                            <p class="text-sm">
                                <span class="font-semibold text-gray-700 mr-1">Examinee Answer:</span>
                                <span id="answer-text-${q.id}" class="text-gray-900">
                                    ${q.given_answer ? `${q.given_answer}${q.given_answer_text ? ' - ' + q.given_answer_text : ''}` : 'No answer'}
                                </span>
                            </p>
                            <p class="text-sm">
                                <span class="font-semibold text-gray-700 mr-1">Correct Answer:</span>
                                <span class="text-gray-900">
                                    ${q.correct_answer ? `${q.correct_answer}${q.correct_answer_text ? ' - ' + q.correct_answer_text : ''}` : '—'}
                                </span>
                            </p>
                        </div>
                      `
                    : `
                        <div class="bg-gray-50 border border-gray-200 rounded px-4 py-3">
                            <p class="whitespace-pre-line">
                                <span class="font-semibold text-gray-700 mr-1">Examinee Answer:</span>
                                <span id="answer-text-${q.id}">${userAnswer}</span>
                            </p>
                        </div>
                      `
                }
            `;


            container.appendChild(div);

            if(isEssay === 1){updateDropdownColor(div.querySelector(`#score-select-${q.id}`));};

        });

        // Save score + time
        if(refreshedEl) refreshedEl.textContent = new Date().toLocaleString();
        if(scoreEl) scoreEl.textContent = `${final_score} / ${highest_score}`;

        // Show Save Score button
        const saveBtn = document.createElement('div');
        saveBtn.className = 'flex justify-end mt-8 max-w-3xl mx-auto';
        saveBtn.innerHTML = `
            <button type="submit"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition">
                Save Score
            </button>
        `;
        container.appendChild(saveBtn);
        recomputeScore();
    }

    function updateCorrect(questionId, isChecked) {
        checkedAnswers[questionId] = isChecked;
        renderAnswers(); // re-render to update tag color/score
    }

    /*function saveScore() {
        const results = Questions.map(q => ({
            question_id: q.id,
            is_correct: checkedAnswers[q.id] ?? (
                mockAnswers[q.id]?.trim().toLowerCase() === correctAnswers[q.id]?.trim().toLowerCase()
            )
        }));

        console.log('Saving score:', results);

        alert('Score saved successfully! (Check console for data)');
        // TODO: Send via AJAX to backend
    }*/

    document.addEventListener('DOMContentLoaded', renderAnswers);
</script>

@include('partials.loader')

@endsection
