<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <title>Admin Login - DILG CAR Recruitment and Selection Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      margin: 0;
      font-family: 'Montserrat', sans-serif;
    }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-800 via-blue-700 to-blue-900 flex items-center justify-center p-4">

  <!-- Main Container (exactly like login page) -->
  <div class="w-full max-w-6xl bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden">
    <div class="flex flex-col lg:flex-row">

      <!-- Left: Admin Login Form (styled exactly like login form) -->
      <div class="flex-1 p-8 lg:p-12">
        <div class="max-w-md mx-auto">
          <div class="flex items-center justify-center gap-3 mb-6">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="w-12 h-12">
            <div class="text-center">
              <h1 class="text-3xl font-extrabold text-blue-900 tracking-tight">WELCOME ADMIN</h1>
              <p class="text-blue-800 font-semibold">Please log-in to continue</p>
            </div>
          </div>

          @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
              <span class="block sm:inline">{{ session('status') }}</span>
            </div>
          @endif

          <form action="{{ route('admin.login') }}" method="POST" autocomplete="off" class="space-y-5">
            @csrf

            <!-- Email (styled exactly like login email field) -->
            <div class="relative">
              <span class="absolute inset-y-0 left-4 flex items-center">
                <i class="fas fa-user text-yellow-400"></i>
              </span>
              <input
                type="email"
                name="email"
                placeholder="Email"
                class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-4 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                required
                value="{{ old('email') }}"
              />
            </div>
            @error('email')
              <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p>
            @enderror

            <!-- Password (styled exactly like login password field WITH toggle button) -->
            <div class="relative">
              <span class="absolute inset-y-0 left-4 flex items-center">
                <i class="fas fa-lock text-yellow-400"></i>
              </span>
              <input
                id="admin_password"
                type="password"
                name="password"
                placeholder="Password"
                class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-12 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                required
              />
              <button type="button" id="toggleAdminPassword" class="absolute inset-y-0 right-3 px-3 flex items-center text-blue-800/70 hover:text-blue-900">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            @error('password')
              <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p>
            @enderror

            <!-- Forgot Password (styled like login page) -->
            <div class="flex items-center justify-end text-sm">
              <a href="#" class="text-blue-800 font-semibold hover:underline" onclick="document.getElementById('adminForgotModal').classList.remove('hidden')">
                Forgot Password?
              </a>
            </div>

            @if(!env('APP_DEBUG'))
              <div class="mb-4">
                <div class="g-recaptcha" data-sitekey="6LfpjpErAAAAADcMjUqP3AZmsMae7WvrjcA5OSvs" data-action="LOGIN"></div>
              </div>
            @endif

            <!-- LOG-IN Button (styled exactly like login button) -->
            <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold py-3 rounded-full shadow-md transition transform hover:scale-[1.02] active:scale-[0.98]">
              LOG-IN
            </button>

            <!-- Back to User Login Link (styled like register link in login page) -->
            <p class="text-center text-sm text-blue-800">
              <a href="{{ route('login') }}" class="use-loader font-bold hover:underline">← BACK TO USER LOGIN</a>
            </p>
          </form>
        </div>
      </div>

      <!-- Right: Logo and Agency Info (exactly like login page with ADMIN indicator) -->
      <div class="flex-1 bg-gradient-to-br from-blue-800 to-blue-900 text-white flex flex-col items-center justify-center p-8 lg:p-12 text-center relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(circle at 20% 20%, #fff 0, transparent 20%), radial-gradient(circle at 80% 30%, #fff 0, transparent 20%), radial-gradient(circle at 30% 80%, #fff 0, transparent 20%);"></div>
        <img 
          src="{{ asset('images/dilg_logo.png') }}" 
          alt="DILG Logo" 
          class="w-28 sm:w-36 md:w-40 mb-6 drop-shadow-lg"
        />
        <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold leading-tight">
          DEPARTMENT OF THE INTERIOR <br/>
          AND LOCAL GOVERNMENT
        </h1>
        <p class="text-sm sm:text-base md:text-lg mt-1 font-semibold">CORDILLERA ADMINISTRATIVE REGION</p>
        <p class="text-sm sm:text-base md:text-lg mt-1 text-blue-200">MATINO. MAHUSAY. MAAASAHAN.</p>
        <p class="text-yellow-400 font-bold mt-4 text-base sm:text-lg">
          RECRUITMENT SELECTION AND PLACEMENT PORTAL
        </p>
        <p class="text-yellow-400 font-extrabold mt-2 text-xl sm:text-2xl border-t-2 border-yellow-400/50 pt-4 w-full max-w-md">
          ADMIN ACCESS
        </p>
      </div>
    </div>
  </div>

  <!-- Error Messages (styled like login page) -->
  @if ($errors->any())
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Whoops!</strong>
        <ul class="list-disc list-inside text-sm mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
  @endif

  @include('partials.loader')

  <!-- Forgot Password Modal (updated to match login page aesthetic) -->
  <div id="adminForgotModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 p-6 text-center relative border border-blue-200">
      <button 
        class="absolute top-3 right-4 text-blue-800 hover:text-red-600 text-2xl font-bold"
        onclick="document.getElementById('adminForgotModal').classList.add('hidden')"
      >
        &times;
      </button>
      <div class="flex justify-center mb-4">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
          <i class="fas fa-lock text-blue-800 text-2xl"></i>
        </div>
      </div>
      <h2 class="text-blue-900 font-bold text-xl mb-2">Forgot Password?</h2>
      <p class="text-sm text-blue-800 font-medium mb-4">
        Please contact your system administrator to reset your password or request access support.
      </p>
      <button 
        class="w-full bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold py-2 rounded-full shadow-md transition transform hover:scale-[1.02] active:scale-[0.98]"
        onclick="document.getElementById('adminForgotModal').classList.add('hidden')"
      >
        Close
      </button>
    </div>
  </div>

  <script src="https://www.google.com/recaptcha/api.js" async defer></script>

  <!-- Password Toggle Script (exactly like login page) -->
  <script>
    const toggle = document.getElementById('toggleAdminPassword');
    if (toggle) {
      toggle.addEventListener('click', function () {
        const input = document.getElementById('admin_password');
        if (!input) return;
        const is = input.getAttribute('type') === 'password';
        input.setAttribute('type', is ? 'text' : 'password');
        this.querySelector('i').classList.toggle('fa-eye-slash');
      });
    }
  </script>

  <!-- Alpine.js for flash messages -->
  <script src="https://unpkg.com/alpinejs" defer></script>
</body>
</html>