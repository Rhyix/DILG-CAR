<template>
    <div>
        <input type="text" class="w-full border-b-2 border-dotted border-gray-400 focus:border-blue-500 outline-none" placeholder="Short answer text" v-model="question.answer">
    </div>
</template>

<script>
export default {
    props: ['question']
};
</script>
