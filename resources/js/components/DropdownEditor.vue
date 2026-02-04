<template>
    <div>
        <div class="flex items-center gap-2 mb-2" v-for="(choice, index) in question.choices" :key="index">
            <span class="font-bold">{{ index + 1 }}.</span>
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
            this.question.choices.push({ text: '' });
        },
        removeChoice(index) {
            this.question.choices.splice(index, 1);
        }
    },
    mounted() {
        if (!this.question.choices) {
            this.question.choices = [
                { text: '' }
            ];
        }
    }
};
</script>
