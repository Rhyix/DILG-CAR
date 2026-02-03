<!-- resources/views/partials/alerts_template.blade.php -->

@props([
    'id' => 'alertModal',        // unique id if needed
    'showTrigger' => true,       // show trigger button or not (true or false)
    'triggerText' => 'Open',     // trigger button text (text shown if showTrigger is true)
    'triggerClass' => 'bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold transition',
    'title' => 'Alert',          // modal title
    'message' => 'Are you sure?',// modal message (HTML allowed)
    'showCancel' => true,        // show cancel button or not (true or false)
    'cancelText' => 'Cancel',    // cancel button text
    'okText' => 'OK',            // OK/confirm button text
    'okAction' => '',            // JS action for OK button (e.g. "window.location.href='/home'")
    'content' => '', 
])

<div x-data="{ showModal: {{ $showTrigger ? 'false' : 'true' }} }" class="">

    <!-- Trigger Button (if enabled) -->
    @if ($showTrigger)
        <button 
            @click="showModal = true"
            class="{{ $triggerClass }}">
            {{ $triggerText }}
        </button>
    @endif

    <!-- Modal Overlay -->
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:leave="transition ease-in duration-200"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         style="display: none;"
         @keydown.escape.window="showModal = false">

        <!-- Modal Box -->
        <div class="bg-white p-8 rounded-2xl max-w-md w-full shadow-2xl relative">
            <!-- Close Button (optional X) -->
            <button 
                @click="showModal = false"
                class="absolute top-4 right-4 text-gray-400 text-xl font-bold hover:text-red-600">
                &times;
            </button>

            <!-- Title -->
            <h2 class="text-2xl font-extrabold text-[#002C76] text-center mb-2">
                {{ $title }}
            </h2>

            <!-- Content -->
            <p class="text-gray-700 text-sm text-center mb-6">
                {!! $message !!}
            </p>

            <!-- Buttons -->
            <div class="flex justify-center items-end gap-4">
                @if ($showCancel)
                    <!-- Cancel Button -->
                    <button 
                        @click="showModal = false"
                        class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-full font-semibold transition max-h-fit">
                        {{ $cancelText }}
                    </button>
                @endif

                @if ($content)
                    {!! $content !!}
                @else
                    <button 
                        @click="showModal = false; {{ $okAction }};"
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-full font-semibold transition">
                        {{ $okText }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>


