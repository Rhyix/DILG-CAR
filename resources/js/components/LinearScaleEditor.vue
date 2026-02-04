<template>
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2">
            <label>From</label>
            <select v-model="question.scale.from" class="border border-blue-500 rounded px-3 py-2">
                <option v-for="i in 10" :value="i">{{ i }}</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <label>To</label>
            <select v-model="question.scale.to" class="border border-blue-500 rounded px-3 py-2">
                <option v-for="i in 10" :value="i">{{ i }}</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <label>Start Label</label>
            <input type="text" class="border border-blue-500 rounded px-3 py-2" v-model="question.scale.start_label">
        </div>
        <div class="flex items-center gap-2">
            <label>End Label</label>
            <input type="text" class="border border-blue-500 rounded px-3 py-2" v-model="question.scale.end_label">
        </div>
    </div>
</template>

<script>
export default {
    props: ['question'],
    mounted() {
        if (!this.question.scale) {
            this.question.scale = { from: 1, to: 5, start_label: '', end_label: '' };
        }
    }
};
</script>
