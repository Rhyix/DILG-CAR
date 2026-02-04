<template>
    <div>
        <div class="flex items-center gap-4">
            <button aria-label="Back" onclick="window.location.href='/admin/exam-management'" class="use-loader p-2 rounded-full bg-[#D9D9D9] hover:bg-[#002C76] h-11 w-11 flex items-center justify-center transition">
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

        <div v-if="questions.length === 0" class="text-center text-gray-500 mt-10">
            <p class="text-xl font-semibold">There are no questions yet.</p>
            <button type="button"
                class="mt-4 bg-[#002C76] hover:bg-blue-900 text-white font-bold py-2 px-6 rounded inline-flex items-center gap-2"
                @click="addQuestion('MCQ')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Your First Question
            </button>
        </div>

        <div v-else>
             <vuedraggable v-model="questions" item-key="id">
                <template #item="{element: question, index}">
                    <question-card :question="question" :index="index" @remove="removeQuestion" />
                </template>
            </vuedraggable>
        </div>

        <div class="fixed bottom-10 right-10">
            <div class="relative">
                <button @click="showAddQuestionMenu = !showAddQuestionMenu" class="bg-blue-600 text-white rounded-full p-4 shadow-lg hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
                <div v-if="showAddQuestionMenu" class="absolute bottom-20 right-0 bg-white rounded-lg shadow-lg py-2">
                    <a href="#" @click.prevent="addQuestion('MCQ')" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Multiple Choice</a>
                    <a href="#" @click.prevent="addQuestion('Checkbox')" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Checkbox</a>
                      <a href="#" @click.prevent="addQuestion('short_answer')" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Short Answer</a>
                       <a href="#" @click.prevent="addQuestion('Paragraph')" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Paragraph</a>
                        <a href="#" @click.prevent="addQuestion('Dropdown')" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Dropdown</a>
                         <a href="#" @click.prevent="addQuestion('LinearScale')" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Linear Scale</a>
                          <a href="#" @click.prevent="addQuestion('Date')" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Date</a>
                          <a href="#" @click.prevent="addQuestion('Time')" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Time</a>
                      </div>
            </div>
        </div>

         <div class="mt-10 flex justify-end gap-4" v-show="questions.length > 0">
            <span v-if="isSaving" class="text-gray-500">Saving...</span>
            <button type="button" @click="preview" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">PREVIEW</button>
            <button type="button" @click="save" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">SAVE</button>
        </div>
    </div>
</template>

<script>
import vuedraggable from 'vuedraggable';
import QuestionCard from './QuestionCard.vue';

export default {
    props: ['vacancyId', 'initialQuestions'],
    components: {
        vuedraggable,
        QuestionCard,
    },
    data() {
        return {
            questions: [],
            showAddQuestionMenu: false,
            isSaving: false,
        };
    },
    mounted() {
        this.questions = this.initialQuestions.map((q, index) => ({
            id: index,
            text: q.question,
            type: q.is_essay ? 'Paragraph' : 'MCQ',
            answer: q.ans || '',
            choices: q.choices ? JSON.parse(q.choices) : { A: '', B: '', C: '', D: '' },
            is_required: true,
            media: null,
            errors: {},
        }));

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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
};
</script>
