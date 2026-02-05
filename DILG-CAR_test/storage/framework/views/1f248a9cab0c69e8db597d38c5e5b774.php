<!DOCTYPE html>
<html lang="en" x-data="signupPage({ hasErrors: '<?php echo e($errors->any() ? 'true' : 'false'); ?>' })" x-init="initModal()">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register - DILG CAR Recruitment and Selection Portal</title>

  <!-- Tailwind + AlpineJS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs" defer></script>

  <!-- Fonts & Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />

  <style>
    body { font-family: 'Montserrat', sans-serif; }
    [x-cloak] { display: none !important; }
  </style>
</head>
<body class="min-h-screen bg-white flex items-center justify-center">

<!-- Flash Messages -->
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
  <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
    class="fixed top-5 right-5 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm">
    <strong class="font-bold">Success!</strong>
    <p class="text-sm"><?php echo e(session('success')); ?></p>
  </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
  <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
    class="fixed top-5 right-5 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm">
    <strong class="font-bold">Whoops!</strong>
    <ul class="list-disc list-inside text-sm mt-1">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><?php echo e($error); ?></li>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </ul>
  </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $firstErrorField = collect($errors->keys())->first(); ?>

<!-- Privacy Modal -->
<template x-if="showModal">
  <div x-cloak>
    <?php echo $__env->make('partials.data_privacy_notice_signup_version', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </div>
</template>

<!-- Main Content -->
<div class="w-full min-h-screen flex flex-col lg:flex-row">

  <!-- Left: Branding -->
  <div class="flex-1 bg-blue-800 text-white flex flex-col items-center justify-center p-8 text-center">
    <img src="<?php echo e(asset('images/dilg_logo.png')); ?>" alt="DILG Logo" class="w-28 sm:w-36 md:w-40 mb-6" />
    <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold leading-tight">
      DEPARTMENT OF THE INTERIOR<br/>AND LOCAL GOVERNMENT
    </h1>
    <p class="text-sm sm:text-base md:text-lg mt-1 font-semibold">CORDILLERA ADMINISTRATIVE REGION</p>
    <p class="text-sm sm:text-base md:text-lg mt-1 text-blue-200">MATINO. MAHUSAY. MAAASAHAN.</p>
    <p class="text-yellow-400 font-bold mt-4 text-base sm:text-lg">RECRUITMENT SELECTION AND PLACEMENT PORTAL</p>
  </div>

  <!-- Right: Registration Form -->
  <div class="flex-1 flex items-center justify-center p-6 bg-white">
    <form method="POST" action="<?php echo e(route('register')); ?>" autocomplete="off"
      class="no-spinner w-full max-w-md bg-white rounded-xl border border-blue-400 p-8 shadow-xl"
      x-on:submit.prevent="if (agreed && checkboxChecked) { isSubmitting = true; $el.submit(); }">
      <?php echo csrf_field(); ?>

      <h2 class="text-3xl font-bold text-center text-blue-900 mb-2">SIGN UP</h2>
      <p class="text-center text-blue-800 font-semibold mb-6">Create an account to proceed</p>

      <!-- Name -->
      <div class="flex items-center border border-blue-400 rounded-full px-4 py-2 mb-2">
        <i class="fas fa-user text-yellow-400 mr-3"></i>
        <input type="text" name="name" placeholder="Enter Name" value="<?php echo e(old('name')); ?>" required
          class="w-full bg-transparent outline-none"
          pattern="^[A-Za-z\s\-\.]{2,50}$"
          title="Name should contain only letters, spaces, hyphens, or periods (2-50 characters).">
      </div>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-sm ml-3 -mt-2"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

      <!-- Email -->
      <div class="flex items-center border border-blue-400 rounded-full px-4 py-2  mb-2 mt-3">
        <i class="fas fa-envelope text-yellow-400 mr-3"></i>
        <input type="email" name="email" placeholder="Email Address" value="<?php echo e(old('email')); ?>" required
          class="w-full bg-transparent outline-none">
      </div>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-sm ml-3 -mt-2"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

      <!-- Password -->
      <div class="flex items-center border border-blue-400 rounded-full px-4 py-2 mb-2 mt-3">
        <i class="fas fa-lock text-yellow-400 mr-3"></i>
        <input type="password" name="password" placeholder="Password" required minlength="8"
          class="w-full bg-transparent outline-none"
          title="Password must be at least 8 characters long.">
      </div>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-sm ml-3 -mt-2"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

      <!-- Confirm Password -->
      <div class="flex items-center border border-blue-400 rounded-full px-4 py-2 mb-2 mt-3">
        <i class="fas fa-lock text-yellow-400 mr-3"></i>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required minlength="8"
          class="w-full bg-transparent outline-none">
      </div>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-sm ml-3 -mt-2"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

      <!-- Data Privacy -->
      <div class="flex items-start mb-4 mt-4">
        <input type="checkbox" id="agree" @click.prevent="showModal = true" :checked="checkboxChecked"
          class="mr-2 mt-1 cursor-pointer">
        <label for="agree" class="text-xs text-blue-700">
          I have read and agree to the
          <span @click="showModal = true" class="font-bold underline cursor-pointer">Data Privacy Notice</span>
        </label>
      </div>

      <!-- Register Button -->
      <button type="submit"
        :disabled="!(agreed && checkboxChecked) || isSubmitting"
        :class="{ 'opacity-50 cursor-not-allowed': !(agreed && checkboxChecked), 'cursor-wait': isSubmitting }"
        class="w-full bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold py-3 rounded-full shadow-md transition ease-in-out duration-200">
        <span x-text="isSubmitting ? 'Processing…' : 'REGISTER'"></span>
      </button>

      <!-- Google Login -->
      <div class="flex items-center justify-center my-4">
        <a :class="{
            'opacity-50 cursor-not-allowed': !(agreed && checkboxChecked),
            'use-loader flex items-center justify-center gap-3 w-full bg-white border-2 border-yellow-400 text-blue-900 font-bold py-2 rounded-full hover:bg-yellow-100 shadow-md transition ease-in-out duration-200': true
          }"
          :href="(agreed && checkboxChecked) ? '<?php echo e(route('google.login')); ?>' : '#'">
          <img src="<?php echo e(asset('images/google-icon.png')); ?>" alt="Google Icon" class="w-5 h-5">
          Continue with Google
        </a>
      </div>

      <p class="text-xs text-blue-700 text-center">
        Already have an account?
        <a href="<?php echo e(route('login')); ?>" class="use-loader font-bold hover:underline">LOG-IN</a>
      </p>
    </form>
  </div>
</div>

<?php echo $__env->make('partials.loader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- AlpineJS Logic -->
<script>
  function signupPage({ hasErrors }) {
    return {
      showModal: false,
      agreed: false,
      checkboxChecked: false,
      hasErrors: hasErrors === 'true',
        isSubmitting: false,
      initModal() {
        if (!this.hasErrors && !localStorage.getItem('modalShown')) {
          this.showModal = true;
          localStorage.setItem('modalShown', 'yes');
        }
      }
    }
  }
</script>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\PDS-main\resources\views/login_register/register.blade.php ENDPATH**/ ?>