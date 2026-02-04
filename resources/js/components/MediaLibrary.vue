<template>
    <div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-4">Media Library</h2>
            <p>Media library functionality will be implemented here.</p>
            <button @click="$emit('close')" class="mt-4 bg-red-500 text-white px-4 py-2 rounded">Close</button>
        </div>
    </div>
</template>

<script>
export default {
    // No props needed for now
};
</script>
