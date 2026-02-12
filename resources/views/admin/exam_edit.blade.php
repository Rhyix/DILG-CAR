@extends('layout.admin')
@section('title', 'DILG - Edit Exam')

@push('styles')
    <!-- Import Montserrat font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        .font-montserrat {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
@endpush

@section('content')
    <div x-data="examEditor()" class="w-full max-w-full font-montserrat">

        <!-- Header -->
        <section class="flex items-center space-x-4 mb-4 max-w-full border-b border-[#0D2B70]">
            <button aria-label="Back" onclick="window.location.href='{{ route('admin.manage_exam', $vacancy_id) }}'"
                class="use-loader group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h1 class="flex items-center gap-3 w-full py-2 tracking-wide select-none">
                <span class="text-[#0D2B70] text-4xl font-montserrat whitespace-nowrap">Edit Exam Questions</span>
            </h1>
        </section>

        <div class="flex flex-row justify-between items-center gap-2">
            <span class="text-[#0D2B70] text-2xl font-montserrat whitespace-nowrap">{{ $vacancy->position_title }},
                {{ $vacancy->vacancy_type }} position</span>
            <button
                class="border border-[#002C76] hover:bg-blue-900 text-[#002C76] hover:text-white font-bold py-2 px-6 rounded inline-flex items-center gap-2">
                <span>Add from Exam Library</span>
            </button>
        </div>

        <!-- Confirmation Modal -->
        <div x-show="showConfirmModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="if(!isProcessing) closeModal()">
            </div>

            <!-- Modal Panel -->
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10"
                                :class="modalType === 'discard' ? 'bg-red-100' : 'bg-blue-100'">
                                <template x-if="modalType === 'discard'">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                </template>
                                <template x-if="modalType === 'save'">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </template>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-semibold leading-6 text-gray-900" x-text="modalTitle"></h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500" x-text="modalMessage"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button"
                            class="inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto flex items-center gap-2"
                            :class="modalType === 'discard' ? 'bg-red-600 hover:bg-red-500' : 'bg-[#002C76] hover:bg-blue-800'"
                            :disabled="isProcessing" @click="confirmAction()">
                            <svg x-show="isProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span x-text="isProcessing ? 'Processing...' : 'Confirm'"></span>
                        </button>
                        <button type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                            :disabled="isProcessing" @click="closeModal()">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Form -->
        <form method="POST" id="examForm" @submit.prevent="handleSaveClick"
            action="{{ route('admin.exam.update', $vacancy_id) }}">
            @csrf
            <input type="hidden" name="questions" :value="JSON.stringify(questions)">

            <!-- Empty state -->
            <div x-show="questions.length === 0" class="text-center text-gray-500 mt-10">
                <p class="text-xl font-semibold">There are no questions yet.</p>
                <button type="button"
                    class="mt-4 bg-[#002C76] hover:bg-blue-900 text-white font-bold py-2 px-6 rounded inline-flex items-center gap-2"
                    @click="addQuestion()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Your First Question
                </button>
            </div>

            <!-- Question List -->
            <div class="space-y-8" x-show="questions.length > 0">
                <template x-for="(q, index) in questions" :key="index">
                    <div class="p-6 bg-white rounded-lg shadow border border-gray-200 w-full relative">
                        <!-- Question Label -->
                        <!-- <div class="mb-4 font-regular text-lg" x-text="`Question ${index + 1} of ${questions.length}`"></div> -->

                        <!-- Question Body -->
                        <div class="">
                            <!-- <textarea required class="w-full h-40 resize-none border border-blue-300 rounded-lg p-4" placeholder="Enter your question..." x-model="q.text"></textarea> -->
                            <div class="flex flex-row justify-between items-center gap-2">
                                <input type="text" required class="w-full border border-blue-300 rounded-lg h-10 px-4"
                                    placeholder="Untitled Question" x-model="q.duration">
                                <div>

                                    <select id="typeOfQuestion" x-model="q.type" class="h-10 cursor-pointer px-4 rounded-md border border-[#0D2B70] text-[#0D2B70] font-semibold bg-white
                                                focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1">
                                        <option value="MCQ">MCQ</option>
                                        <option value="Essay">Essay</option>
                                    </select>
                                </div>
                            </div>

                            <!-- MCQ Choices -->
                            <div class="mt-4 space-y-2" x-show="q.type === 'MCQ'">
                                <template x-for="(option, optIndex) in q.choices" :key="optIndex">
                                    <div class="flex items-center gap-3 group">
                                        <!-- Radio Button (Functional) -->
                                        <div @click="q.correctAnswer = optIndex; checkForChanges()"
                                            class="w-5 h-5 rounded-full border-2 flex-shrink-0 cursor-pointer transition-all flex items-center justify-center"
                                            :class="q.correctAnswer === optIndex ? 'border-[#0D2B70] bg-[#0D2B70]' : 'border-gray-400 hover:border-[#0D2B70]'">
                                            <div x-show="q.correctAnswer === optIndex"
                                                class="w-2 h-2 rounded-full bg-white"></div>
                                        </div>

                                        <!-- Option Input -->
                                        <input type="text" x-model="q.choices[optIndex]"
                                            class="w-full border-b border-transparent hover:border-gray-300 focus:border-[#0D2B70] focus:outline-none py-1 px-2 transition-colors"
                                            placeholder="Option">

                                        <!-- Remove Option Button -->
                                        <button type="button" @click="removeOption(index, optIndex)"
                                            class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition-opacity p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>

                                <!-- Add Option / Add Other -->
                                <div class="flex items-center gap-3">
                                    <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex-shrink-0"></div>
                                    <div class="flex items-center gap-1 text-sm text-gray-500">
                                        <button type="button" @click="addOption(index)"
                                            class="hover:underline hover:text-[#0D2B70] font-medium">
                                            Add option
                                        </button>
                                        <!-- <span>or</span>
                                                <button type="button" class="hover:underline hover:text-[#0D2B70] font-medium">
                                                    add "Other"
                                                </button> -->
                                    </div>
                                </div>
                            </div>

                            <!-- TIP, DUPLICATE, AND REMOVE BUTTON -->
                            <div class="flex flex-row justify-between items-center gap-2">
                                <span class="italic text-sm text-[#0D2B70]">
                                    Tick the option to declare as answer.
                                </span>
                                <div class="flex gap-2">
                                    <!-- Duplicate Button -->
                                    <button type="button" @click="duplicateQuestion(index)" title="Duplicate this question"
                                        class="text-white font-bold p-3 rounded-lg 
                                                    transition-all duration-200 hover:scale-105 hover:shadow-md
                                                    relative group">
                                        <i class="fa-solid fa-copy text-[#0D2B70] text-xl"></i>
                                        <span
                                            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 
                                                            bg-gray-800 text-white text-xs rounded-md whitespace-nowrap
                                                            opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            Duplicate Question
                                        </span>
                                    </button>

                                    <!-- Remove Button -->
                                    <button type="button" @click="removeQuestion(index)" title="Remove this question" class="text-white font-bold p-3 rounded-lg 
                                                    transition-all duration-200 hover:scale-105 hover:shadow-md
                                                    relative group">
                                        <i class="fa-solid fa-trash text-red-700 text-xl"></i>
                                        <span
                                            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 
                                                            bg-gray-800 text-white text-xs rounded-md whitespace-nowrap
                                                            opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            Remove Question
                                        </span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </template>
            </div>

            <!-- Actions -->
            <div class="mt-10 flex flex-row justify-between items-center gap-4" x-show="questions.length > 0">
                <!-- Add More Questions - Left -->
                <button type="button" class="border-2 border-[#002C76] hover:bg-[#002C76] hover:scale-105 
                                text-[#002C76] hover:text-white font-bold py-2 px-6 rounded-lg 
                                flex items-center gap-2 transition-all duration-200" @click="addQuestion()">
                    <i class="fa-solid fa-plus text-lg"></i>
                    <span>Add More Questions</span>
                </button>

                <!-- Discard and Save - Right -->
                <div class="flex gap-3">
                    <button type="button" @click="handleDiscardClick" class="border-2 border-red-600 hover:bg-red-600 hover:text-white 
                                    text-red-600 font-bold py-2 px-6 rounded-lg 
                                    transition-all duration-200 hover:scale-105 hover:shadow-md">
                        <i class="fa-solid fa-trash-can mr-2"></i>
                        Discard
                    </button>
                    <button type="submit" :disabled="!hasChanges"
                        :class="hasChanges ? 'bg-[#002C76] hover:scale-105 hover:shadow-md' : 'bg-gray-400 cursor-not-allowed'"
                        class="text-white font-bold py-2 px-6 rounded-lg transition-all duration-200">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            feather.replace();
        });

        function examEditor() {
            return {
                questions: [], // Start with no questions
                originalQuestionsSnapshot: '', // JSON snapshot of original questions for change detection
                hasChanges: false, // Track if there are unsaved changes
                showConfirmModal: false,
                modalType: '', // 'discard' or 'save'
                modalTitle: '',
                modalMessage: '',
                isProcessing: false, // Loading state for modal

                init() {
                    const data = @json($exam_items);
                    // console.log('Loaded Exam Data:', data); // Debugging

                    this.questions = data.map(q => {
                        let parsedChoices = [];
                        // Ensure choices is an array for the new format
                        if (q.choices) {
                            // If it's already an array (from JSON decode or new format)
                            if (Array.isArray(q.choices)) {
                                parsedChoices = q.choices;
                            }
                            // If it's an object {A:..., B:...} (legacy format), convert to array
                            else if (typeof q.choices === 'object') {
                                parsedChoices = Object.values(q.choices).filter(val => val !== '');
                            }
                            // If it's a string (from DB), try to parse
                            else if (typeof q.choices === 'string') {
                                try {
                                    const parsed = JSON.parse(q.choices);
                                    if (Array.isArray(parsed)) {
                                        parsedChoices = parsed;
                                    } else if (typeof parsed === 'object') {
                                        parsedChoices = Object.values(parsed).filter(val => val !== '');
                                    }
                                } catch (e) { console.error('Error parsing choices', e); }
                            }
                        }

                        // Ensure at least one empty option if none exist
                        if (parsedChoices.length === 0 && parseInt(q.is_essay) !== 1) {
                            parsedChoices = ['Option 1'];
                        }

                        // Find correct answer index
                        let correctAnswerIndex = -1;
                        if (q.ans && parsedChoices.length > 0) {
                            correctAnswerIndex = parsedChoices.indexOf(q.ans);
                        }

                        // Determine text vs duration
                        // Prioritize 'question' column from DB which is mapped to 'q.question'
                        // If empty, check if there's a legacy 'duration' (unlikely for question text but kept for safety)
                        let questionText = q.question || '';

                        // If still empty, check if the object has a 'text' property (unlikely from DB but possible in JS)
                        if (!questionText && q.text) questionText = q.text;

                        return {
                            text: questionText,
                            type: parseInt(q.is_essay) === 1 ? 'Essay' : 'MCQ',
                            answer: q.ans || '',
                            duration: questionText, // Use question as default for the input field model (x-model="q.duration")
                            choices: parsedChoices,
                            correctAnswer: correctAnswerIndex
                        };
                    });

                    // Save original state for change detection
                    this.originalQuestionsSnapshot = JSON.stringify(this.questions);
                    this.hasChanges = false;

                    // Ensure icons are rendered after data load
                    this.$nextTick(() => {
                        if (window.feather) feather.replace();
                    });
                },

                addQuestion() {
                    this.questions.push({
                        text: '',
                        type: 'MCQ',
                        answer: '',
                        choices: ['Option 1'], // Start with Option 1
                        correctAnswer: -1 // Initialize with no selection
                    });
                    this.checkForChanges();
                    this.$nextTick(() => feather.replace());
                },

                addOption(questionIndex) {
                    const currentLength = this.questions[questionIndex].choices.length;
                    this.questions[questionIndex].choices.push(`Option ${currentLength + 1}`);
                    this.checkForChanges();
                },

                removeOption(questionIndex, optionIndex) {
                    this.questions[questionIndex].choices.splice(optionIndex, 1);
                    this.checkForChanges();
                },
                removeQuestion(index) {
                    this.questions.splice(index, 1);
                    this.checkForChanges();
                },
                moveUp(index) {
                    if (index > 0) {
                        [this.questions[index], this.questions[index - 1]] = [this.questions[index - 1], this.questions[index]];
                    }
                },
                moveDown(index) {
                    if (index < this.questions.length - 1) {
                        [this.questions[index], this.questions[index + 1]] = [this.questions[index + 1], this.questions[index]];
                    }
                },
                duplicateQuestion(index) {
                    const questionToClone = this.questions[index];
                    const clonedQuestion = {
                        text: questionToClone.text,
                        type: questionToClone.type,
                        answer: questionToClone.answer,
                        duration: questionToClone.duration,
                        choices: [...questionToClone.choices], // Deep copy the array
                        correctAnswer: questionToClone.correctAnswer // Copy correct answer index
                    };

                    // Insert right after the current question
                    this.questions.splice(index + 1, 0, clonedQuestion);
                    this.checkForChanges();

                    // Re-initialize icons for the new element
                    this.$nextTick(() => {
                        if (window.feather) feather.replace();
                    });
                },

                handleDiscardClick() {
                    this.modalType = 'discard';
                    this.modalTitle = 'Discard All Questions';
                    this.modalMessage = 'Are you sure you want to discard all questions? This action cannot be undone and all current progress will be lost.';
                    this.showConfirmModal = true;
                },

                handleSaveClick() {
                    if (!this.validateForm()) {
                        return; // Stop if validation fails
                    }
                    this.modalType = 'save';
                    this.modalTitle = 'Save Exam Changes';
                    this.modalMessage = 'Are you sure you want to save these changes? This will update the exam questions for all applicants.';
                    this.showConfirmModal = true;
                },

                closeModal() {
                    this.showConfirmModal = false;
                },

                confirmAction() {
                    if (this.modalType === 'discard') {
                        this.questions = [];
                        this.originalQuestionsSnapshot = JSON.stringify(this.questions);
                        this.hasChanges = false;
                        this.showConfirmModal = false;
                    } else if (this.modalType === 'save') {
                        // Set processing state to true
                        this.isProcessing = true;

                        // Optional: Show global loader as well if desired, but button spinner is cleaner
                        // const loader = document.getElementById('loader');
                        // if (loader) loader.classList.remove('hidden');

                        // Submit form
                        this.$nextTick(() => {
                            const form = document.getElementById('examForm');
                            if (form) {
                                // We need to bypass the submit listener to avoid recursion
                                // But since it is @submit.prevent, calling form.submit() usually works natively
                                form.submit();
                            }
                            // Note: isProcessing stays true until page reloads
                        });
                    }
                },

                validateForm() {
                    // Check if there are any questions
                    if (this.questions.length === 0) {
                        // It's technically valid to save an empty exam, but let's confirm
                        // Reuse modal logic here for empty exam? Or just allow it.
                        // For now, let's treat it as valid but maybe prompt?
                        // The requirement says "check if all questions... are filled". If 0 questions, it's trivially true.
                        // But usually you want at least 1.
                        // Let's allow it but maybe the user wants a warning.
                        // The prompt "You are about to save an exam with NO questions" is good.
                        // We can handle this inside the modal message dynamically if we want.
                    }

                    for (let i = 0; i < this.questions.length; i++) {
                        const q = this.questions[i];

                        // 1. Check if question text is empty (using duration field as question text based on UI)
                        if (!q.duration || q.duration.trim() === '') {
                            alert(`Question ${i + 1} is empty. Please enter the question text.`);
                            return false;
                        }

                        // 2. Validation for MCQ
                        if (q.type === 'MCQ') {
                            // Check if choices exist
                            if (q.choices.length < 2) {
                                alert(`Question ${i + 1} (MCQ) must have at least 2 options.`);
                                return false;
                            }

                            // Check empty options
                            for (let j = 0; j < q.choices.length; j++) {
                                if (!q.choices[j] || q.choices[j].trim() === '') {
                                    alert(`Question ${i + 1} has an empty option at position ${j + 1}.`);
                                    return false;
                                }
                            }

                            // Check if correct answer is selected
                            if (q.correctAnswer === undefined || q.correctAnswer === null || q.correctAnswer < 0 || q.correctAnswer >= q.choices.length) {
                                alert(`Please select a correct answer for Question ${i + 1}.`);
                                return false;
                            }
                        }
                    }

                    return true;
                },

                checkForChanges() {
                    // Compare current questions with original snapshot
                    const currentSnapshot = JSON.stringify(this.questions);
                    this.hasChanges = currentSnapshot !== this.originalQuestionsSnapshot;
                }
            }
        }
    </script>
@endpush