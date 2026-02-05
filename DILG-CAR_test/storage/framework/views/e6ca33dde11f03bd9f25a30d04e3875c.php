<?php
    $levels = [
        'info' => 'bg-blue-50 text-blue-700',
        'success' => 'bg-green-50 text-green-700',
        'warning' => 'bg-yellow-50 text-yellow-700',
        'error' => 'bg-red-50 text-red-700',
    ];
    $cls = $levels[$notification->data['level'] ?? 'info'] ?? $levels['info'];
?>
<li class="p-3 rounded-lg mb-2 <?php echo e($cls); ?> flex items-start gap-3" data-id="<?php echo e($notification->id); ?>">
    <div class="mt-0.5">
        <i data-feather="<?php echo e(($notification->data['level'] ?? 'info') === 'success' ? 'check-circle' : (($notification->data['level'] ?? 'info') === 'warning' ? 'alert-triangle' : (($notification->data['level'] ?? 'info') === 'error' ? 'x-circle' : 'info'))); ?>"></i>
    </div>
    <div class="flex-1">
        <div class="font-semibold"><?php echo e($notification->data['title'] ?? 'Notification'); ?></div>
        <div class="text-sm"><?php echo e($notification->data['message'] ?? ''); ?></div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($notification->data['action_url'] ?? null)): ?>
            <a href="<?php echo e($notification->data['action_url']); ?>" class="text-blue-600 underline text-xs">Open</a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="text-xs opacity-70 mt-1"><?php echo e($notification->created_at->diffForHumans()); ?></div>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$notification->read_at): ?>
        <span class="ml-2 inline-block px-2 py-1 text-xs bg-white rounded">New</span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</li>
<?php /**PATH C:\xampp\htdocs\DILG-CAR_pp\resources\views/components/notification-item.blade.php ENDPATH**/ ?>