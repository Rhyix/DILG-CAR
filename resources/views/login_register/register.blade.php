<!DOCTYPE html>
<html lang="en" x-data="signupPage({ hasErrors: '{{ $errors->any() ? 'true' : 'false' }}' })" x-init="initModal()">
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
<body class="h-screen overflow-hidden bg-gradient-to-br from-[#071A4D] via-[#0A2566] to-[#12398B] flex items-center justify-center p-4">

<!-- Flash Messages (styled like login page success message) -->
@if(session('success'))
  <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
    class="fixed top-5 right-5 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm">
    <strong class="font-bold">Success!</strong>
    <p class="text-sm">{{ session('success') }}</p>
  </div>
@endif

@if ($errors->any())
  <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
    class="fixed top-5 right-5 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm">
    <strong class="font-bold">Whoops!</strong>
    <ul class="list-disc list-inside text-sm mt-1">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@php $firstErrorField = collect($errors->keys())->first(); @endphp

<!-- Privacy Modal -->
<template x-if="showModal">
  <div x-cloak>
    @include('partials.data_privacy_notice_signup_version')
  </div>
</template>

<!-- Main Container (exactly like login page) -->
<div class="w-full max-w-6xl bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden h-full max-h-full">
  <div class="flex flex-col lg:flex-row h-full">

    <!-- Left: Registration Form (exactly like login page styling) -->
    <div class="flex-1 p-8 lg:p-12 overflow-y-auto">
      <div class="max-w-md mx-auto">
        <div class="flex items-center justify-center gap-3 mb-6">
          <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="w-12 h-12">
          <div class="text-center">
            <h2 class="text-3xl font-extrabold text-blue-900 tracking-tight">SIGN UP</h2>
            <p class="text-blue-800 font-semibold">Create an account to proceed</p>
          </div>
        </div>

        <form method="POST" action="{{ route('register') }}" autocomplete="off"
          class="space-y-5"
          x-on:submit.prevent="if (agreed && checkboxChecked) { isSubmitting = true; $el.submit(); }">
          @csrf

          <!-- Name fields (styled exactly like login email field) -->
          <div class="space-y-5">
            <!-- First Name -->
            <div class="relative">
              <span class="absolute inset-y-0 left-4 flex items-center">
                <i class="fas fa-user text-yellow-400"></i>
              </span>
              <input type="text" name="first_name" placeholder="Enter First Name" value="{{ old('first_name') }}" required
                class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-4 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                pattern="^[A-Za-z\s\-\.]{2,50}$"
                title="Name should contain only letters, spaces, hyphens, or periods (2-50 characters).">
            </div>  

            <!-- Middle Name -->
            <div class="relative">
              <span class="absolute inset-y-0 left-4 flex items-center">
                <i class="fas fa-font text-yellow-400"></i>
              </span>
              <input type="text" name="middle_name" placeholder="Enter Middle Name" value="{{ old('middle_name') }}" required
                class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-4 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                pattern="^[A-Za-z\s\-\.]{2,50}$"
                title="Middle name should contain only letters, spaces, hyphens, or periods (2-50 characters).">
            </div>

            <!-- Last Name -->
            <div class="relative">
              <span class="absolute inset-y-0 left-4 flex items-center">
                <i class="fas fa-id-badge text-yellow-400"></i>
              </span>
              <input type="text" name="last_name" placeholder="Enter Last Name" value="{{ old('last_name') }}" required
                class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-4 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                pattern="^[A-Za-z\s\-\.]{2,50}$"
                title="Name should contain only letters, spaces, hyphens, or periods (2-50 characters).">
            </div>
          </div>
          @error('first_name') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror
          @error('middle_name') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror
          @error('last_name') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror
          @error('fname') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror
          @error('mname') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror
          @error('lname') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror

          <!-- Sex (styled with consistent spacing) -->
          <div class="flex flex-col gap-2">
            <span class="text-blue-900 font-semibold">Sex</span>
            <div class="flex gap-6">
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="sex" value="Male" required
                  class="w-4 h-4 text-blue-600 border-blue-400 focus:ring-blue-600">
                <span class="text-blue-900">Male</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="sex" value="Female" required
                  class="w-4 h-4 text-blue-600 border-blue-400 focus:ring-blue-600">
                <span class="text-blue-900">Female</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="sex" value="Prefer not to say" required
                  class="w-4 h-4 text-blue-600 border-blue-400 focus:ring-blue-600">
                <span class="text-blue-900">Prefer not to say</span>
              </label>
            </div>
          </div>
 
          <!-- Contact Number (styled exactly like login email field) -->
          <div class="relative">
            <span class="absolute inset-y-0 left-4 flex items-center">
              <i class="fas fa-phone text-yellow-400"></i>
            </span>
            <input type="text" name="phone_number" placeholder="Enter Contact Number" value="{{ old('phone_number') }}" required
              class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-4 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
              pattern="^09[0-9]{9}$"
              title="Contact number must be 11 digits long and start with 09."
              inputmode="numeric"
              oninput="this.value = this.value.replace(/[^0-9]/g, '')"
              maxlength="11"
            >
          </div>

          <!-- Email (styled exactly like login email field) -->
          <div class="relative">
            <span class="absolute inset-y-0 left-4 flex items-center">
              <i class="fas fa-envelope text-yellow-400"></i>
            </span>
            <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required
              class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-4 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
          </div>
          @error('email') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror

          <!-- Password (styled exactly like login password field WITH toggle button) -->
          <div class="relative">
            <span class="absolute inset-y-0 left-4 flex items-center">
              <i class="fas fa-lock text-yellow-400"></i>
            </span>
            <input 
              id="password" 
              type="password" 
              name="password" 
              placeholder="Password" 
              required 
              minlength="8"
              class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-12 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
              pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*#?&]).{8,}"
              title="Password must be at least 8 characters long, contain 1 uppercase letter, 1 number, and 1 special character.">
            <button type="button" id="togglePassword" class="absolute inset-y-0 right-3 px-3 flex items-center text-blue-800/70 hover:text-blue-900">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          @error('password') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror

          <!-- Confirm Password (styled exactly like login password field WITH toggle button) -->
          <div class="relative">
            <span class="absolute inset-y-0 left-4 flex items-center">
              <i class="fas fa-lock text-yellow-400"></i>
            </span>
            <input 
              id="password_confirmation" 
              type="password" 
              name="password_confirmation" 
              placeholder="Confirm Password" 
              required 
              minlength="8"
              class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-12 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
            <button type="button" id="togglePasswordConfirm" class="absolute inset-y-0 right-3 px-3 flex items-center text-blue-800/70 hover:text-blue-900">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          @error('password_confirmation') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror

          <!-- Data Privacy (styled like remember me checkbox) -->
          <div class="flex items-start mt-2">
            <input type="checkbox" id="agree" @click.prevent="showModal = true" :checked="checkboxChecked"
              class="mr-2 mt-1 cursor-pointer rounded border-blue-400 text-blue-800 focus:ring-blue-600">
            <label for="agree" class="text-sm text-blue-800">
              I have read and agree to the
              <span @click="showModal = true" class="font-bold underline cursor-pointer hover:text-blue-900">Data Privacy Notice</span>
            </label>
          </div>

          <!-- Register Button (styled exactly like login button - changed to match login blue color) -->
          <button type="submit"
            :disabled="!(agreed && checkboxChecked) || isSubmitting"
            :class="{ 'opacity-50 cursor-not-allowed': !(agreed && checkboxChecked), 'cursor-wait': isSubmitting }"
            class="w-full bg-[#0D2B70] text-white font-bold py-3 rounded-full hover:bg-[#0A2259] shadow-md transition transform hover:scale-[1.02] active:scale-[0.98]">
            <span x-text="isSubmitting ? 'Processing…' : 'Sign Up'"></span>
          </button>

          <!-- Google Login (styled exactly like login google button) -->
          <div class="flex items-center justify-center my-4">
            <a :class="{
                'opacity-50 cursor-not-allowed': !(agreed && checkboxChecked),
                'use-loader flex items-center justify-center gap-3 w-full bg-white border-2 border-yellow-400 text-blue-900 font-bold py-3 rounded-full hover:bg-yellow-100 shadow-md transition transform hover:scale-[1.02] active:scale-[0.98]': true
              }"
              :href="(agreed && checkboxChecked) ? '{{ route('google.login', [], false) }}' : '#'">
              <img src="{{ asset('images/google-icon.png') }}" alt="Google Icon" class="w-5 h-5">
              Continue with Google
            </a>
          </div>

          <!-- Login Link (styled exactly like register link in login page) -->
          <p class="text-center text-sm text-blue-800">
            Already have an account?
            <a href="{{ route('login') }}" class="use-loader font-bold hover:underline">LOG-IN</a>
          </p>
        </form>
      </div>
    </div>

    <!-- Right: Logo and Agency Info (exactly like login page) -->
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

@include('partials.loader')

<!-- Password Toggle Scripts (exactly like login page) -->
<script>
  // Toggle for password field
  const toggle = document.getElementById('togglePassword');
  if (toggle) {
    toggle.addEventListener('click', function () {
      const input = document.getElementById('password');
      if (!input) return;
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

  // Toggle for confirm password field
  const toggleConfirm = document.getElementById('togglePasswordConfirm');
  if (toggleConfirm) {
    toggleConfirm.addEventListener('click', function () {
      const input = document.getElementById('password_confirmation');
      if (!input) return;
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
