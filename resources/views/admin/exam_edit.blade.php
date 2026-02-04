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
<div x-data="examEditor()" class="w-full px-10 py-8 space-y-10 max-w-full font-montserrat">

    <!-- Header -->
    <div class="flex items-center gap-4">
        <button aria-label="Back" onclick="window.location.href='{{ route('admin_exam_management') }}'" class="use-loader p-2 rounded-full bg-[#D9D9D9] hover:bg-[#002C76] h-11 w-11 flex items-center justify-center transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#002c76] hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <h1 class="shadow-lg shadow-black/30 w-full flex items-center font-extrabold text-3xl border-2 border-[#002C76] text-[#002C76] rounded-xl px-4 py-2 gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m-6-8h6m2 12H7a2 2 0 01-2-2V6a2 2 0 012-2h7.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V18a2 2 0 01-2 2z" />
            </svg>
            Edit Exam Questions
        </h1>
    </div>

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

                    <!-- Reorder/Delete Buttons -->
                    <div class="absolute top-4 right-4 flex space-x-2">
                        <button type="button" @click="moveUp(index)" x-show="index > 0" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-blue-700 hover:bg-blue-100 rounded" title="Move Up">
                            <i data-feather="arrow-up" class="w-4 h-4"></i>
                        </button>
                        <button type="button" @click="moveDown(index)" x-show="index < questions.length - 1" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-blue-700 hover:bg-blue-100 rounded" title="Move Down">
                            <i data-feather="arrow-down" class="w-4 h-4"></i>
                        </button>
                        <button type="button" @click="removeQuestion(index)" class="w-8 h-8 flex items-center justify-center text-red-500 hover:bg-red-100 rounded" title="Remove">
                            <i data-feather="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Question Label -->
                    <div class="mb-4 font-regular text-lg" x-text="`Question ${index + 1} of ${questions.length}`"></div>

                    <!-- Question Body -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <textarea required class="w-full h-40 resize-none border border-blue-300 rounded-lg p-4" placeholder="Enter your question..." x-model="q.text"></textarea>

                        <div class="space-y-4">
                            <div class="flex gap-4">
                                <div class="w-1/2">
                                    <label class="font-semibold block mb-1">Type:</label>
                                    <select class="w-full border border-gray-300 rounded px-3 py-2" x-model="q.type">
                                        <option value="MCQ">MCQ</option>
                                        <option value="Essay">Essay</option>
                                    </select>
                                </div>
                                <div class="w-1/2" x-show="q.type === 'MCQ'">
                                    <label class="font-semibold block mb-1">Answer:</label>
                                    <select class="w-full border border-gray-300 rounded px-3 py-2" x-model="q.answer">
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                    </select>
                                </div>
                            </div>

                            <!-- MCQ Choices in 2x2 format (Responsive) -->
                            <div class="grid grid-cols-2 gap-4" x-show="q.type === 'MCQ'">
                                <template x-for="(letter, index) in ['A', 'B', 'C', 'D']" :key="letter">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <!-- Always-square letter box -->
                                        <div class="min-w-[2rem] min-h-[2rem] w-8 h-8 flex-shrink-0 flex items-center justify-center bg-[#002C76] text-white font-bold rounded">
                                            <span x-text="letter"></span>
                                        </div>
                                        <!-- Flexible answer input -->
                                        <input type="text"
                                            class="w-full border border-blue-500 rounded px-3 py-2"
                                            :placeholder="`(${letter})`"
                                            x-model="q.choices[letter]">
                                    </div>
                                </template>
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
            this.questions = data.map(q => ({
                    text: q.question,
                    type: q.is_essay ? 'Essay' : 'MCQ',
                    answer: q.ans || '',
                    choices: q.choices || { A: '', B: '', C: '', D: '' },
                }));
            },

            addQuestion() {
                this.questions.push({
                    text: '',
                    type: 'MCQ',
                    answer: 'A',
                    choices: { A: '', B: '', C: '', D: '' },
                });
                this.$nextTick(() => feather.replace());
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
