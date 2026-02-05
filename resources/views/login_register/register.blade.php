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
<body class="min-h-screen bg-white flex items-center justify-center">

<!-- Flash Messages -->
@if(session('success'))
  <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
    class="fixed top-5 right-5 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm">
    <strong class="font-bold">Success!</strong>
    <p class="text-sm">{{ session('success') }}</p>
  </div>
@endif

@if ($errors->any())
  <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
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

<!-- Main Content -->
<div class="w-full min-h-screen flex flex-col lg:flex-row">

  <!-- Left: Branding -->
  <div class="flex-1 bg-blue-800 text-white flex flex-col items-center justify-center p-8 text-center">
    <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="w-28 sm:w-36 md:w-40 mb-6" />
    <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold leading-tight">
      DEPARTMENT OF THE INTERIOR<br/>AND LOCAL GOVERNMENT
    </h1>
    <p class="text-sm sm:text-base md:text-lg mt-1 font-semibold">CORDILLERA ADMINISTRATIVE REGION</p>
    <p class="text-sm sm:text-base md:text-lg mt-1 text-blue-200">MATINO. MAHUSAY. MAAASAHAN.</p>
    <p class="text-yellow-400 font-bold mt-4 text-base sm:text-lg">RECRUITMENT SELECTION AND PLACEMENT PORTAL</p>
  </div>

  <!-- Right: Registration Form -->
  <div class="flex-1 flex items-center justify-center p-6 bg-white">
    <form method="POST" action="{{ route('register') }}" autocomplete="off"
      class="no-spinner w-full max-w-md bg-white rounded-xl border border-blue-400 p-8 shadow-xl"
      x-on:submit.prevent="if (agreed && checkboxChecked) { isSubmitting = true; $el.submit(); }">
      @csrf

      <h2 class="text-3xl font-bold text-center text-blue-900 mb-2">SIGN UP</h2>
      <p class="text-center text-blue-800 font-semibold mb-6">Create an account to proceed</p>

      <!-- Name -->
      <div class="flex items-center border border-blue-400 rounded-full px-4 py-2 mb-2">
        <i class="fas fa-user text-yellow-400 mr-3"></i>
        <input type="text" name="name" placeholder="Enter Name" value="{{ old('name') }}" required
          class="w-full bg-transparent outline-none"
          pattern="^[A-Za-z\s\-\.]{2,50}$"
          title="Name should contain only letters, spaces, hyphens, or periods (2-50 characters).">
      </div>
      @error('name') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror

      <!-- Email -->
      <div class="flex items-center border border-blue-400 rounded-full px-4 py-2  mb-2 mt-3">
        <i class="fas fa-envelope text-yellow-400 mr-3"></i>
        <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required
          class="w-full bg-transparent outline-none">
      </div>
      @error('email') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror

      <!-- Password -->
      <div class="flex items-center border border-blue-400 rounded-full px-4 py-2 mb-2 mt-3">
        <i class="fas fa-lock text-yellow-400 mr-3"></i>
        <input type="password" name="password" placeholder="Password" required minlength="8"
          class="w-full bg-transparent outline-none"
          title="Password must be at least 8 characters long.">
      </div>
      @error('password') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror

      <!-- Confirm Password -->
      <div class="flex items-center border border-blue-400 rounded-full px-4 py-2 mb-2 mt-3">
        <i class="fas fa-lock text-yellow-400 mr-3"></i>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required minlength="8"
          class="w-full bg-transparent outline-none">
      </div>
      @error('password_confirmation') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror

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
          :href="(agreed && checkboxChecked) ? '{{ route('google.login') }}' : '#'">
          <img src="{{ asset('images/google-icon.png') }}" alt="Google Icon" class="w-5 h-5">
          Continue with Google
        </a>
      </div>

      <p class="text-xs text-blue-700 text-center">
        Already have an account?
        <a href="{{ route('login') }}" class="use-loader font-bold hover:underline">LOG-IN</a>
      </p>
    </form>
  </div>
</div>

@include('partials.loader')

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
