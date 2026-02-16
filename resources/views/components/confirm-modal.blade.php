@props([
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'event' => 'open-confirm-modal',
    'confirm' => 'confirm-action'
])
<div 
    x-data="{ open: false }"
    x-on:{{ $event }}.window="open = true"
    x-show="open"
    x-cloak
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
    style="display: none;"
>
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-lg font-semibold mb-4">
            {{ $title }}
        </h2>

        <p class="mb-4">
            {{ $message }}
        </p>

        <div class="flex justify-end gap-2">
            <button 
                class="px-4 py-2 bg-gray-300 rounded"
                @click="open = false"
            >
                Cancel
            </button>

            <button 
                class="px-4 py-2 bg-red-600 text-white rounded"
                @click="open = false; $dispatch('{{ $confirm }}')"
            >
                Confirm
            </button>
        </div>
    </div>
</div>
