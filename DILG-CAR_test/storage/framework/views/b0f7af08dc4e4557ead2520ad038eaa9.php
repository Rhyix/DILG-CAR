<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Success!</strong>
        <p class="text-sm"><?php echo e(session('success')); ?></p>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>



<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - DILG CAR Recruitment and Selection Portal</title>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- FontAwesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    crossorigin="anonymous"
  />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
    }
  </style>
  
        <!--$metaTitle = 'DILG - CAR Recruitment and Selection Portal';-->
        <!--$metaDescription = 'This is the official Recruitment and Selection Portal of the Department of the Interior and Local Government - Cordillera Administrative Region.';-->
        <!--$metaImage = asset('images/dilg_rsp_thumbnail.png');-->
        <!--$metaUrl = url()->current();-->

        <!--if (request()->is('login')) {-->
        <!--    $metaTitle = 'Login - DILG Recruitment Portal';-->
        <!--    $metaDescription = 'Access your account to view job vacancies and submit your application.';-->
        <!--    $metaImage = asset('images/dilg_login_thumbnail.png'); // fallback if you want a separate image-->
        <!--} elseif (request()->is('jobs/*')) {-->
        <!--    $metaTitle = 'View Job Vacancy - DILG CAR';-->
        <!--    $metaDescription = 'Explore available job opportunities and join our team at DILG CAR.';-->
        <!--}    -->

    <!-- Dynamic Open Graph Meta Tags -->
    <meta property="og:title" content="DILG - CAR Recruitment Selection and Placement Portal" />
    <meta property="og:description" content="Isa ka bang MATINO, MAHUSAY, at MAAASAHAN na manggagawang Pilipino?" />
    <meta property="og:image" content="<?php echo e(asset('images/dilg_rsp_thumbnail.png')); ?>" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:type" content="image/png" />
    <meta property="og:url" content="url()->current();-->" />
    <meta property="og:type" content="website" />

    <!-- Dynamic Twitter Card Meta Tags -->
    <meta name="twitter:card" content="DILG - CAR Recruitment Selection and Placement Portal" />
    <meta name="twitter:title" content="DILG - CAR Recruitment Selection and Placement Portal" />
    <meta name="twitter:description" content="Isa ka bang MATINO, MAHUSAY, at MAAASAHAN na manggagawang Pilipino?" />
    <meta name="twitter:image" content="<?php echo e(asset('images/dilg_rsp_thumbnail.png')); ?>" />
    
</head>
<body class="min-h-screen bg-white flex items-center justify-center">

  <div class="w-full min-h-screen flex flex-col-reverse lg:flex-row">
    
    <!-- Left: Login Form -->
    <div class="flex-1 flex items-center justify-center p-6 bg-white">
      <div class="w-full max-w-md bg-white rounded-xl border border-blue-400 p-8 shadow-xl">
        <h2 class="text-3xl font-bold text-center text-blue-900 mb-2">WELCOME</h2>
        <p class="text-center text-blue-800 font-semibold mb-6">Please log-in to continue</p>

        <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-5">
          <?php echo csrf_field(); ?>

          <!-- Email -->
          <div class="flex items-center border border-blue-400 rounded-full px-4 py-2">
            <i class="fas fa-user text-yellow-400 mr-3"></i>
            <input 
              id="email" 
              type="email" 
              name="email" 
              value="<?php echo e(old('email')); ?>" 
              required 
              autofocus 
              placeholder="Email"
              class="w-full bg-transparent outline-none"
            />
          </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="text-red-600 text-sm ml-3 -mt-4"><?php echo e($message); ?></p>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

          <!-- Password -->
          <div class="flex items-center border border-blue-400 rounded-full px-4 py-2">
            <i class="fas fa-lock text-yellow-400 mr-3"></i>
            <input 
              id="password" 
              type="password" 
              name="password" 
              required 
              placeholder="Password"
              class="w-full bg-transparent outline-none"
            />
          </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="text-red-600 text-sm ml-3 -mt-4"><?php echo e($message); ?></p>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

          <!-- Forgot password -->
            <div class="text-right text-sm">
              <a href="<?php echo e(route('forgot.password.form')); ?>" class="text-blue-800 hover:underline">Forgot Password?</a>
           </div>
           
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!env('APP_DEBUG')): ?>
            <div class="mb-4">
                <div class="g-recaptcha" data-sitekey="6LfpjpErAAAAADcMjUqP3AZmsMae7WvrjcA5OSvs" data-action="LOGIN"></div>
            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          
          <!-- Submit -->
          <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold py-3 rounded-full shadow-md transition">
            LOG-IN
          </button>

          <!-- Google Login -->
          <!-- Google Login -->
                <div class="flex items-center justify-center my-4">
                  <a class="use-loader flex items-center justify-center gap-3 w-full bg-white border-2 border-yellow-400 text-blue-900 font-bold py-2 rounded-full hover:bg-yellow-100 shadow-md transition ease-in-out duration-200"
                    href='<?php echo e(route('google.login')); ?>'>
                    <img src="<?php echo e(asset('images/google-icon.png')); ?>" alt="Google Icon" class="w-5 h-5">
                    Continue with Google
                  </a>
                </div>

          <!-- Register -->
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('register')): ?>
            <p class="text-center text-sm text-blue-800">
              Don’t have an account?
              <a href="<?php echo e(route('register')); ?>" class="use-loader font-bold hover:underline">SIGN-UP</a>
            </p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </form>
      </div>
    </div>

    <!-- Right: Logo and Agency Info -->
    <div class="flex-1 bg-blue-800 text-white flex flex-col items-center justify-center p-8 text-center">
      <img 
        src="<?php echo e(asset('images/dilg_logo.png')); ?>" 
        alt="DILG Logo" 
        class="w-28 sm:w-36 md:w-40 mb-6"
      />
      <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold leading-tight">
        DEPARTMENT OF THE INTERIOR  <br/>
        AND LOCAL GOVERNMENT
      </h1>
      <p class="text-sm sm:text-base md:text-lg mt-1 font-semibold">CORDILLERA ADMINISTRATIVE REGION</p>
      <p class="text-sm sm:text-base md:text-lg mt-1 text-blue-200">MATINO. MAHUSAY. MAAASAHAN.</p>
      <p class="text-yellow-400 font-bold mt-4 text-base sm:text-lg">
        RECRUITMENT SELECTION AND PLACEMENT PORTAL
      </p>
    </div>
  </div>
</body>
<?php echo $__env->make('partials.loader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</html>
<?php /**PATH C:\xampp\htdocs\PDS-main\resources\views/login_register/login.blade.php ENDPATH**/ ?>