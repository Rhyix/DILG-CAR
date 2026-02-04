<template>
    <div>
        <textarea class="w-full border-b-2 border-dotted border-gray-400 focus:border-blue-500 outline-none" placeholder="Long answer text" v-model="question.answer"></textarea>
    </div>
</template>

<script>
export default {
    props: ['question']
};
</script>
