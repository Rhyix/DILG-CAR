<!-- resources/views/partials/alerts_template.blade.php -->

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
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
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
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
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="{ showModal: <?php echo e($showTrigger ? 'false' : 'true'); ?> }" class="">

    <!-- Trigger Button (if enabled) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTrigger): ?>
        <button 
            @click="showModal = true"
            class="<?php echo e($triggerClass); ?>">
            <?php echo e($triggerText); ?>

        </button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
                <?php echo e($title); ?>

            </h2>

            <!-- Content -->
            <p class="text-gray-700 text-sm text-center mb-6">
                <?php echo $message; ?>

            </p>

            <!-- Buttons -->
            <div class="flex justify-center items-end gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCancel): ?>
                    <!-- Cancel Button -->
                    <button 
                        @click="showModal = false"
                        class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-full font-semibold transition max-h-fit">
                        <?php echo e($cancelText); ?>

                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($content): ?>
                    <?php echo $content; ?>

                <?php else: ?>
                    <button 
                        @click="showModal = false; <?php echo e($okAction); ?>;"
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-full font-semibold transition">
                        <?php echo e($okText); ?>

                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php /**PATH C:\xampp\htdocs\PDS-main\resources\views/partials/alerts_template.blade.php ENDPATH**/ ?>