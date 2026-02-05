<?php $__env->startSection('title', 'My Profile'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Profile</h1>
    <div class="flex items-center gap-4 mb-6">
        <?php
            $avatar = $user->avatar_path ? asset('storage/'.$user->avatar_path) : null;
            $initials = collect(explode(' ', $user->name))->map(fn($p)=>mb_substr($p,0,1))->join('');
        ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($avatar): ?>
            <img src="<?php echo e($avatar); ?>" alt="Avatar" class="w-16 h-16 rounded-full object-cover">
        <?php else: ?>
            <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg font-bold"><?php echo e($initials); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <form method="POST" action="<?php echo e(route('profile.avatar')); ?>" enctype="multipart/form-data" class="flex items-center gap-2">
            <?php echo csrf_field(); ?>
            <input type="file" name="avatar" accept="image/png,image/jpeg" class="text-sm">
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Upload</button>
        </form>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
        <div class="p-3 bg-green-50 text-green-700 rounded mb-3"><?php echo e(session('status')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <div class="text-sm text-gray-500">Name</div>
            <div class="font-semibold"><?php echo e($user->name); ?></div>
        </div>
        <div>
            <div class="text-sm text-gray-500">Email</div>
            <div class="font-semibold"><?php echo e($user->email); ?></div>
        </div>
        <?php $bio = optional($user->profile)->bio ?? $user->bio; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bio): ?>
        <div class="sm:col-span-2">
            <div class="text-sm text-gray-500">Bio</div>
            <div class="font-semibold"><?php echo e($bio); ?></div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(optional($user->profile)->phone): ?>
        <div>
            <div class="text-sm text-gray-500">Phone</div>
            <div class="font-semibold"><?php echo e($user->profile->phone); ?></div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(optional($user->profile)->address): ?>
        <div>
            <div class="text-sm text-gray-500">Address</div>
            <div class="font-semibold"><?php echo e($user->profile->address); ?></div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <div class="mt-6 flex gap-2">
        <a href="<?php echo e(route('profile.edit')); ?>" class="px-3 py-2 bg-gray-100 rounded">Edit Profile</a>
        <a href="<?php echo e(route('profile.password.form')); ?>" class="px-3 py-2 bg-gray-100 rounded">Change Password</a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\PDS-main\resources\views/profile/show.blade.php ENDPATH**/ ?>