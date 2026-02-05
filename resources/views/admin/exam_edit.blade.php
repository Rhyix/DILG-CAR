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
        <button aria-label="Back" onclick="window.location.href='{{ route('admin_exam_management') }}'" class="use-loader group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <h1 class="flex items-center gap-3 w-full py-2 tracking-wide select-none">
            <span class="text-[#0D2B70] text-4xl font-montserrat whitespace-nowrap">Edit Exam Questions</span>
        </h1>
    </section>

    <!-- Question Form -->
    <form method="POST" @submit.prevent="submitForm" action="{{ route('admin.exam.update', $vacancy_id) }}">
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
                    <input type="text" required class="w-full border border-blue-300 rounded-lg h-10 px-4" placeholder="Untitled Question" x-model="q.duration">
                    <div>
                        
                    <select id="typeOfQuestion"
                        x-model="q.type"
                        class="h-10 cursor-pointer px-4 rounded-md border border-[#0D2B70] text-[#0D2B70] font-semibold bg-white
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
                            <div @click="q.correctAnswer = optIndex" 
                                class="w-5 h-5 rounded-full border-2 flex-shrink-0 cursor-pointer transition-all flex items-center justify-center"
                                :class="q.correctAnswer === optIndex ? 'border-[#0D2B70] bg-[#0D2B70]' : 'border-gray-400 hover:border-[#0D2B70]'">
                                <div x-show="q.correctAnswer === optIndex" 
                                    class="w-2 h-2 rounded-full bg-white"></div>
                            </div>
                            
                            <!-- Option Input -->
                            <input type="text" 
                                x-model="q.choices[optIndex]" 
                                class="w-full border-b border-transparent hover:border-gray-300 focus:border-[#0D2B70] focus:outline-none py-1 px-2 transition-colors"
                                placeholder="Option">

                            <!-- Remove Option Button -->
                            <button type="button" 
                                @click="removeOption(index, optIndex)"
                                class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition-opacity p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </template>

                    <!-- Add Option / Add Other -->
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex-shrink-0"></div>
                        <div class="flex items-center gap-1 text-sm text-gray-500">
                            <button type="button" 
                                @click="addOption(index)"
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

                <div class="flex flex-row justify-between items-center gap-2">
                    <span class="italic text-sm text-[#0D2B70]">
                        Tick the option to declare as answer.
                    </span>
                    <div>
                        <button type="button" 
                            @click="duplicateQuestion(index)"
                            class="bg-[#002C76] hover:bg-blue-900 text-white font-bold py-2 px-6 rounded">
                            Duplicate
                        </button>
                        <button type="button" 
                            @click="removeQuestion(index)"
                            class="bg-red-700 hover:bg-red-800 text-white font-bold py-2 px-6 rounded">
                            Remove
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </template>
</div>

        <!-- Add More -->
        <div class="text-right" x-show="questions.length > 0">
            <button type="button" class="bg-[#002C76] mt-10 hover:bg-blue-900 text-white font-bold py-2 px-6 rounded flex items-center gap-2" @click="addQuestion()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add More Questions
            </button>
        </div>

        <!-- Actions -->
        <div class="mt-10 flex justify-end gap-4" x-show="questions.length > 0">
            <button type="button" @click="clearAll" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded">DISCARD</button>
            <button type="submit" class="use-loader bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">SAVE</button>
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
            init() {
                const data = @json($exam_items);
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
                             } catch(e) { console.error('Error parsing choices', e); }
                         }
                    }

                    // Ensure at least one empty option if none exist
                    if (parsedChoices.length === 0 && (!q.is_essay)) {
                        parsedChoices = ['Option 1'];
                    }

                    return {
                        text: q.question,
                        type: q.is_essay ? 'Essay' : 'MCQ',
                        answer: q.ans || '',
                        duration: q.duration || '', // Added duration mapping if available in DB
                        choices: parsedChoices,
                    };
                });
            },

            addQuestion() {
                this.questions.push({
                    text: '',
                    type: 'MCQ',
                    answer: '',
                    duration: '',
                    choices: ['Option 1'], // Start with Option 1
                });
                this.$nextTick(() => feather.replace());
            },

            addOption(questionIndex) {
                const currentLength = this.questions[questionIndex].choices.length;
                this.questions[questionIndex].choices.push(`Option ${currentLength + 1}`);
            },

            removeOption(questionIndex, optionIndex) {
                this.questions[questionIndex].choices.splice(optionIndex, 1);
            },
            removeQuestion(index) {
                this.questions.splice(index, 1);
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
            clearAll() {
                if (confirm('Clear all questions?')) {
                    this.questions = [];
                }
            },
            submitForm() {
                this.$root.querySelector('form').submit();
            }
        }
    }
</script>
@include('partials.loader')
@endpush
