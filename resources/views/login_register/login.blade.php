@if (session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Success!</strong>
        <p class="text-sm">{{ session('success') }}</p>
    </div>
@endif

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - DILG CAR Recruitment and Selection Portal</title>
  @if(app()->environment('production'))
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  @endif
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
  
  <!-- Dynamic Open Graph Meta Tags -->
  <meta property="og:title" content="DILG - CAR Recruitment Selection and Placement Portal" />
  <meta property="og:description" content="Isa ka bang MATINO, MAHUSAY, at MAAASAHAN na manggagawang Pilipino?" />
  <meta property="og:image" content="{{ asset('images/dilg_rsp_thumbnail.png') }}" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:image:type" content="image/png" />
  <meta property="og:url" content="url()->current();-->" />
  <meta property="og:type" content="website" />

  <!-- Dynamic Twitter Card Meta Tags -->
  <meta name="twitter:card" content="DILG - CAR Recruitment Selection and Placement Portal" />
  <meta name="twitter:title" content="DILG - CAR Recruitment Selection and Placement Portal" />
  <meta name="twitter:description" content="Isa ka bang MATINO, MAHUSAY, at MAAASAHAN na manggagawang Pilipino?" />
  <meta name="twitter:image" content="{{ asset('images/dilg_rsp_thumbnail.png') }}" />
  
</head>
<body class="min-h-screen bg-gradient-to-br from-[#071A4D] via-[#0A2566] to-[#12398B] flex items-center justify-center p-4">

  <div class="w-full max-w-6xl bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden">
    <div class="flex flex-col lg:flex-row">
      
      <!-- Left: Login Form -->
      <div class="flex-1 p-8 lg:p-12">
        <div class="max-w-md mx-auto">
          <div class="flex items-center justify-center gap-3 mb-6">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="w-12 h-12">
            <div class="text-center">
              <h2 class="text-3xl font-extrabold text-blue-900 tracking-tight">WELCOME</h2>
              <p class="text-blue-800 font-semibold">Please log-in to continue</p>
            </div>
          </div>

          <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email -->
            <div class="relative">
              <span class="absolute inset-y-0 left-4 flex items-center">
                <i class="fas fa-user text-yellow-400"></i>
              </span>
              <input 
                id="email" 
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                autofocus 
                placeholder="Email"
                class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-4 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
              />
            </div>
            @error('email')
              <p class="text-red-600 text-sm ml-3 -mt-4">{{ $message }}</p>
            @enderror

            <!-- Password -->
            <div class="relative">
              <span class="absolute inset-y-0 left-4 flex items-center">
                <i class="fas fa-lock text-yellow-400"></i>
              </span>
              <input
                id="user_password"
                type="password"
                name="password"
                placeholder="Password"
                class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-12 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                required
              />
              <button type="button" id="togglePassword" aria-label="Show password" aria-pressed="false" class="absolute inset-y-0 right-3 px-3 flex items-center text-blue-800/70 hover:text-blue-900">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            @error('password')
              <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p>
            @enderror

            <!-- Remember me & Forgot password -->
            <div class="flex items-center justify-between text-sm">
              <label class="inline-flex items-center gap-2 text-blue-900">
                <input type="checkbox" name="remember" class="rounded border-blue-400 text-blue-800 focus:ring-blue-600">
                <span class="font-semibold">Remember me</span>
              </label>
              <a href="{{ route('forgot.password.form') }}" class="text-blue-800 font-semibold hover:underline">Forgot Password?</a>
            </div>
             
            @if(app()->environment('production'))
              <div class="mb-4">
                  <div class="g-recaptcha" data-sitekey="6LfpjpErAAAAADcMjUqP3AZmsMae7WvrjcA5OSvs" data-action="LOGIN"></div>
              </div>
            @endif
            <!-- w-full rounded-xl bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0A2259] -->
            <!-- LOG-IN Button - Visible -->
            <button type="submit" class="use-loader flex items-center justify-center bg-[#0D2B70] gap-3 w-full text-white font-bold py-3 rounded-full hover:bg-[#0A2259] shadow-md transition transform hover:scale-[1.02] active:scale-[0.98]">
              Sign In
            </button>

            <!-- Continue with Google Button - Visible -->
            <div class="flex items-center justify-center my-4">
              <a class="use-loader flex items-center justify-center gap-3 w-full bg-white border-2 border-yellow-400 text-blue-900 font-bold py-3 rounded-full hover:bg-yellow-100 shadow-md transition transform hover:scale-[1.02] active:scale-[0.98]"
                href='{{ route('google.login') }}'>
                <img src="{{ asset('images/google-icon.png') }}" alt="Google Icon" class="w-5 h-5">
                Continue with Google
              </a>
            </div>

            <!-- Register Link -->
            @if (Route::has('register'))
              <p class="text-center text-sm text-blue-800">
                Don't have an account?
                <a href="{{ route('register') }}" class="use-loader font-bold hover:underline">SIGN-UP</a>
              </p>
            @endif
          </form>
        </div>
      </div>

      <!-- Right: Logo and Agency Info -->
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
      </div>
    </div>
  </div>

  <!-- @include('partials.loader') -->

  <script>
    const toggle = document.getElementById('togglePassword');
    const input = document.getElementById('user_password');
    if (toggle && input) {
      toggle.addEventListener('click', function () {
        const isPassword = input.getAttribute('type') === 'password';
        input.setAttribute('type', isPassword ? 'text' : 'password');
        this.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
        
        const icon = this.querySelector('i');
        if (icon) {
            if (isPassword) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
      });
    }
  </script>
</body>
</html>
