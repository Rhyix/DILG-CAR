<template>
    <div>
        <div class="flex items-center gap-2 mb-2" v-for="(choice, index) in question.choices" :key="index">
            <input type="checkbox" :value="choice.letter" v-model="question.answer">
            <div class="min-w-[2rem] min-h-[2rem] w-8 h-8 flex-shrink-0 flex items-center justify-center bg-[#002C76] text-white font-bold rounded">
                {{ choice.letter }}
            </div>
            <input type="text" class="w-full border border-blue-500 rounded px-3 py-2" v-model="choice.text">
            <button aria-label="Remove choice" @click="removeChoice(index)" class="text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <button aria-label="Add choice" @click="addChoice" class="text-blue-600">Add Option</button>
    </div>
</template>

<script>
export default {
    props: ['question'],
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
        } else if (typeof this.question.choices === 'object' && !Array.isArray(this.question.choices)) {
            // Convert the object to an array
            this.question.choices = Object.keys(this.question.choices).map(key => {
                return { letter: key, text: this.question.choices[key] };
            });
        }

        if (!this.question.answer) {
            this.question.answer = [];
        }
    }
};
</script>
