<?php $__env->startSection('title', 'Change Password'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-md mx-auto bg-white rounded-xl shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Change Password</h1>
    <form method="POST" action="<?php echo e(route('profile.password')); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>
        <div>
            <label class="block text-sm font-medium">Current Password</label>
            <input type="password" name="current_password" class="mt-1 w-full border rounded px-3 py-2" required>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium">New Password</label>
            <input type="password" name="password" class="mt-1 w-full border rounded px-3 py-2" required>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-red-600 text-sm"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="mt-1 w-full border rounded px-3 py-2" required>
        </div>
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Update Password</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\PDS-main\resources\views/profile/password.blade.php ENDPATH**/ ?>