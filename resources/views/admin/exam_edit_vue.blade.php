@extends('layout.admin')
@section('title', 'DILG - Edit Exam (Vue)')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        .font-montserrat {
            font-family: 'Montserrat', sans-serif;
        }
        input:checked ~ .dot {
            transform: translateX(100%);
            background-color: #48bb78;
        }
    </style>
@endpush

@section('content')
<div id="app" class="w-full px-10 py-8 space-y-10 max-w-full font-montserrat">
    <question-editor :vacancy-id="{{ $vacancy_id }}" :initial-questions='@json($exam_items)'></question-editor>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuedraggable@next/dist/vuedraggable.umd.min.js"></script>

    <script>
        const app = Vue.createApp({});

        if (window.vuedraggable) {
            app.component('vuedraggable', window.vuedraggable);
        }

        app.component('question-editor', {
            props: ['vacancyId', 'initialQuestions'],
            template: `
                <div>
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

                    <div class="mt-6">
                        <vuedraggable v-model="questions" item-key="id">
                            <template #item="{ element, index }">
                                <question-card :question="element" :index="index" @remove="removeQuestion" />
                            </template>
                        </vuedraggable>
                    </div>

                    <div class="mt-8 flex justify-center">
                        <button type="button"
                            class="bg-[#002C76] hover:bg-blue-900 text-white font-bold py-2 px-6 rounded inline-flex items-center gap-2"
                            @click="addQuestion('MCQ')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add question
                        </button>
                    </div>

                     <div class="mt-10 flex justify-end gap-4" v-show="questions.length > 0">
                        <span v-if="isSaving" class="text-gray-500">Saving...</span>
                        <button type="button" @click="preview" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">PREVIEW</button>
                        <button type="button" @click="save" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">SAVE</button>
                    </div>
                </div>
            `,
            data() {
                return {
                    questions: [],
                    isSaving: false,
                };
            },
            mounted() {
                const source = Array.isArray(this.initialQuestions) ? this.initialQuestions : [];

                this.questions = source.map((q, index) => {
                    let choices = { A: '', B: '', C: '', D: '' };

                    if (q.choices) {
                        try {
                            const parsed = JSON.parse(q.choices);
                            if (parsed && typeof parsed === 'object') {
                                choices = parsed;
                            }
                        } catch (e) {
                            choices = { A: '', B: '', C: '', D: '' };
                        }
                    }

                    return {
                        id: index,
                        text: q.question,
                        type: q.is_essay ? 'Essay' : 'MCQ',
                        answer: q.ans || '',
                        choices: choices,
                        is_required: true,
                    };
                });

                if (this.questions.length === 0) {
                    this.addQuestion('MCQ');
                }

                setInterval(() => {
                    this.save();
                }, 30000);
            },
            methods: {
                addQuestion(type) {
                    this.questions.push({
                        id: Date.now(),
                        text: '',
                        type: type,
                        answer: '',
                        choices: { A: '', B: '', C: '', D: ''},
                        is_required: true,
                        media: null,
                        errors: {},
                    });
                },
                removeQuestion(index) {
                    if (confirm('Are you sure you want to remove this question?')) {
                        this.questions.splice(index, 1);
                    }
                },
                preview() {
                    window.open(`/admin/exam/${this.vacancyId}/preview`, '_blank');
                },
                save() {
                    this.isSaving = true;
                    fetch(`/admin/exam/${this.vacancyId}/update-vue`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ questions: this.questions })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);
                        this.isSaving = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.isSaving = false;
                    });
                }
            },
        });

        app.component('question-card', {
            props: ['question', 'index'],
            template: `
                <div class="p-6 bg-white rounded-lg shadow border border-gray-200 w-full relative my-4">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-4">
                            <div class="font-bold text-lg">Question @{{ index + 1 }}</div>
                            <select v-model="question.type" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                <option value="MCQ">Multiple choice</option>
                                <option value="Essay">Essay</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="toggle" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-blue-700 hover:bg-blue-100 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" v-if="!isOpen">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" v-else>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            <button type="button" @click="$emit('remove', index)" class="w-8 h-8 flex items-center justify-center text-red-500 hover:bg-red-100 rounded" title="Remove">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div v-show="isOpen">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <textarea required class="w-full h-40 resize-none border border-blue-300 rounded-lg p-4" placeholder="Enter your question..." v-model="question.text" @input="validate"></textarea>
                                <p v-if="question.errors && question.errors.text" class="text-red-500 text-sm mt-1">@{{ question.errors.text }}</p>
                            </div>
                            <div>
                                <MCQEditor v-if="question.type === 'MCQ'" :question="question" />
                                <ParagraphEditor v-if="question.type === 'Essay'" :question="question" />
                            </div>
                        </div>
                        <div v-if="question.media" class="mt-4">
                            <img v-if="question.media.type === 'image'" :src="question.media.url" class="max-w-full h-auto rounded">
                            <video v-if="question.media.type === 'video'" :src="question.media.url" controls class="max-w-full h-auto rounded"></video>
                        </div>
                    </div>
                    </div>
                </div>
            `,
            data() {
                return {
                    isOpen: true,
                };
            },
            methods: {
                toggle() {
                    this.isOpen = !this.isOpen;
                },
                validate() {
                    if (!this.question.errors) {
                        this.question.errors = {};
                    }
                    if (!this.question.text) {
                        this.question.errors.text = 'Question text is required.';
                    } else {
                        delete this.question.errors.text;
                    }
                }
            },
        });

        app.component('MCQEditor', {
            props: ['question'],
            template: `
                <div>
                    <div class="flex items-center gap-2 mb-2" v-for="(choice, index) in question.choices" :key="index">
                        <input type="radio" :name="'answer' + question.id" :value="choice.letter" v-model="question.answer">
                        <div class="min-w-[2rem] min-h-[2rem] w-8 h-8 flex-shrink-0 flex items-center justify-center bg-[#002C76] text-white font-bold rounded">
                            @{{ choice.letter }}
                        </div>
                        <input type="text" class="w-full border border-blue-500 rounded px-3 py-2" v-model="choice.text" @input="validate">
                        <button aria-label="Remove choice" @click="removeChoice(index)" class="text-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="question.errors && question.errors.choices" class="text-red-500 text-sm mt-1">@{{ question.errors.choices }}</p>
                    <button aria-label="Add choice" @click="addChoice" class="text-blue-600">Add Option</button>
                </div>
            `,
            methods: {
                addChoice() {
                    if (!this.question.choices) {
                        this.question.choices = [];
                    }
                    const nextLetter = String.fromCharCode(65 + this.question.choices.length);
                    this.question.choices.push({ letter: nextLetter, text: '' });
                },
                removeChoice(index) {
                    this.question.choices.splice(index, 1);
                    // Re-letter the remaining choices
                    this.question.choices.forEach((choice, i) => {
                        choice.letter = String.fromCharCode(65 + i);
                    });
                },
                validate() {
                    if (!this.question.errors) {
                        this.question.errors = {};
                    }
                    const emptyChoices = this.question.choices.filter(c => !c.text.trim());
                    if (emptyChoices.length > 0) {
                        this.question.errors.choices = 'All choices must have a value.';
                    } else {
                        delete this.question.errors.choices;
                    }
                }
            },
             mounted() {
                if (!this.question.choices || Object.keys(this.question.choices).length === 0) {
                    this.question.choices = [
                        { letter: 'A', text: '' },
                        { letter: 'B', text: '' },
                        { letter: 'C', text: '' },
                        { letter: 'D', text: '' },
                    ];
                } else {
                    // Convert the object to an array
                    this.question.choices = Object.keys(this.question.choices).map(key => {
                        return { letter: key, text: this.question.choices[key] };
                    });
                }
            }
        });

        app.component('ParagraphEditor', {
                props: ['question'],
                template: `
                    <div>
                        <textarea class="w-full border-b-2 border-dotted border-gray-400 focus:border-blue-500 outline-none" placeholder="Long answer text" v-model="question.answer"></textarea>
                    </div>
                `
            });

        app.component('MediaLibrary', {
            template: `
                <div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
                    <div class="bg-white p-8 rounded-lg shadow-lg">
                        <h2 class="text-2xl font-bold mb-4">Media Library</h2>
                        <p>Media library functionality will be implemented here.</p>
                        <button @click="$emit('close')" class="mt-4 bg-red-500 text-white px-4 py-2 rounded">Close</button>
                    </div>
                </div>
            `
        });

        app.mount('#app');
    </script>
@endpush
