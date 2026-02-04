<template>
    <div class="p-6 bg-white rounded-lg shadow border border-gray-200 w-full relative my-4">
        <div class="flex justify-between items-center mb-4">
            <div class="font-bold text-lg">Question {{ index + 1 }}</div>
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
                    <p v-if="question.errors && question.errors.text" class="text-red-500 text-sm mt-1">{{ question.errors.text }}</p>
                </div>
                <div>
                    <MCQEditor v-if="question.type === 'MCQ'" :question="question" />
                    <CheckboxEditor v-if="question.type === 'Checkbox'" :question="question" />
                    <ShortAnswerEditor v-if="question.type === 'short_answer'" :question="question" />
                    <ParagraphEditor v-if="question.type === 'Paragraph'" :question="question" />
                    <DropdownEditor v-if="question.type === 'Dropdown'" :question="question" />
                    <LinearScaleEditor v-if="question.type === 'LinearScale'" :question="question" />
                    <DateEditor v-if="question.type === 'Date'" :question="question" />
                    <TimeEditor v-if="question.type === 'Time'" :question="question" />
                </div>
            </div>
            <div v-if="question.media" class="mt-4">
                <img v-if="question.media.type === 'image'" :src="question.media.url" class="max-w-full h-auto rounded">
                <video v-if="question.media.type === 'video'" :src="question.media.url" controls class="max-w-full h-auto rounded"></video>
            </div>
        </div>
    </div>
</template>

<script>
import MCQEditor from './MCQEditor.vue';
import CheckboxEditor from './CheckboxEditor.vue';
import ShortAnswerEditor from './ShortAnswerEditor.vue';
import ParagraphEditor from './ParagraphEditor.vue';
import DropdownEditor from './DropdownEditor.vue';
import LinearScaleEditor from './LinearScaleEditor.vue';
import DateEditor from './DateEditor.vue';
import TimeEditor from './TimeEditor.vue';

export default {
    props: ['question', 'index'],
    components: {
        MCQEditor,
        CheckboxEditor,
        ShortAnswerEditor,
        ParagraphEditor,
        DropdownEditor,
        LinearScaleEditor,
        DateEditor,
        TimeEditor,
    },
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
};
</script>
