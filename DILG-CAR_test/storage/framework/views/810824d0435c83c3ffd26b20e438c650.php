<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <title>Admin Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.js"></script>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    h1, h2, h3, p {
        font-family: 'Montserrat', sans-serif;
        text-align: center;
    }
  </style>
</head>
<body 
  x-data="{ 
    isMobile: window.innerWidth < 768, 
    showForgotModal: false 
  }"
  x-init="window.addEventListener('resize', () => { isMobile = window.innerWidth < 768 })">
  
  <!-- Desktop Only Message -->

  <!-- Main Content (hidden on mobile) -->
  <div x-show="!isMobile" class="min-h-screen flex">
    <!-- Left side: Login Form Container -->
    <div class="flex-1 flex items-center justify-center bg-white min-h-screen shadow-lg rounded-r-3xl">
      <form class="w-[468px] h-[538px] p-8 rounded-xl border border-blue-700 shadow-xl" action="<?php echo e(route('admin.login')); ?>" method="POST" autocomplete="off">
        <?php echo csrf_field(); ?>
        <h1 class="text-3xl font-extrabold text-blue-900 mb-1 drop-shadow-md">WELCOME ADMIN</h1>
        <p class="text-base font-bold text-blue-900 mb-14 drop-shadow-md">Please log-in to continue</p>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 font-montserrat" role="alert">
                <span class="block sm:inline"><?php echo e(session('status')); ?></span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <label class="relative block mb-6 mt-3">
          <span class="material-icons absolute inset-y-0 left-3 flex items-center text-yellow-400 text-lg select-none">email</span>
          <input
            type="email"
            name="email"
            placeholder="E-mail Address"
            class="pl-10 pr-4 h-10 w-full border border-blue-700 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50"
            required
          />
        </label>

        <label class="relative block mb-2">
          <span class="material-icons absolute inset-y-0 left-3 flex items-center text-yellow-400 text-lg select-none">lock</span>
          <input
            type="password"
            name="password"
            placeholder="Password"
            class="pl-10 pr-4 h-10 w-full border border-blue-700 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50"
            required
          />
        </label>

        <div class="text-xs text-blue-700 mb-6 text-right">
          <a href="#" class="hover:underline" @click.prevent="showForgotModal = true">Forgot Password?</a>
        </div>

          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!env('APP_DEBUG')): ?>
            <div class="mb-4">
                <div class="g-recaptcha" data-sitekey="6LfpjpErAAAAADcMjUqP3AZmsMae7WvrjcA5OSvs" data-action="LOGIN"></div>
            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <button
          type="submit"
          class="w-full bg-yellow-400 text-blue-900 font-bold mt-16 py-2 rounded-full hover:bg-yellow-500 shadow-md transition ease-in-out duration-200"
        >
          LOG-IN
        </button>
      </form>
    </div>

    <!-- Right side: Branding and Info -->
    <div class="flex-1 bg-blue-800 text-center p-10 flex flex-col justify-center min-h-screen">
      <img
        src="<?php echo e(asset('images/dilg_logo.png')); ?>"
        alt="DILG Logo"
        class="mx-auto mb-8 max-w-[200px]"
        loading="lazy"
      />
      <h2 class="text-white text-2xl font-bold leading-tight max-w-md mx-auto mb-1 drop-shadow-lg">
        DEPARTMENT OF THE INTERIOR <br>AND LOCAL GOVERNMENT
      </h2>
      <p class="text-white text-sm font-semibold mb-2 drop-shadow-sm">
        CORDILLERA ADMINISTRATIVE REGION
      </p>
      <p class="text-blue-300 text-lg font-semibold mb-5 tracking-widest uppercase drop-shadow-sm">
        MATINO. MAHUSAY. MAAASAHAN.
      </p>
      <h3 class="text-yellow-400 text-xl font-extrabold max-w-lg mx-auto leading-snug drop-shadow-md">
        RECRUITMENT SELECTION AND <br>PLACEMENT PORTAL
      </h3>
      <h3 class="text-yellow-400 text-xl font-extrabold max-w-lg mx-auto leading-snug drop-shadow-md mt-2">
        ADMIN ACCESS PORTAL
      </h3>
    </div>
  </div>

  <!-- Error Messages -->
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Whoops!</strong>
        <ul class="list-disc list-inside text-sm mt-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <li><?php echo e($error); ?></li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </ul>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php echo $__env->make('partials.loader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <!-- Forgot Password Modal -->
<div 
  x-show="showForgotModal"
  x-transition
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
  <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full mx-4 p-6 text-center relative border-2 border-blue-700">
    <button 
      class="absolute top-2 right-3 text-blue-900 hover:text-red-600 text-xl font-bold"
      @click="showForgotModal = false"
    >
      &times;
    </button>
    <h2 class="text-blue-900 font-bold text-xl mb-2">Forgot Password?</h2>
    <p class="text-sm text-blue-800 font-medium">Please contact your system administrator to reset your password or request access support.</p>
    <button 
      class="mt-6 px-4 py-2 bg-yellow-400 text-blue-900 font-bold rounded-full hover:bg-yellow-500 transition"
      @click="showForgotModal = false"
    >
      Close
    </button>
  </div>
</div>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</body>
    <script>
if (window.innerWidth < 768) {
    window.location.href = "<?php echo e(route('mobile.locked')); ?>";
  }
    </script>
</html><?php /**PATH C:\xampp\htdocs\PDS-main\resources\views/login_register/admin_login.blade.php ENDPATH**/ ?>