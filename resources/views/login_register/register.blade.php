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

        <form id="registerForm" method="POST" action="{{ route('register') }}" autocomplete="off"
          class="space-y-5"
          x-on:submit.prevent="submitForm($el)">
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
            <input id="phone_number" type="text" name="phone_number" placeholder="09XX XXX XXXX" value="{{ old('phone_number') }}" required
              class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-4 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
              pattern="^09[0-9]{2}\s[0-9]{3}\s[0-9]{4}$"
              title="Contact number must follow the format 09XX XXX XXXX."
              aria-describedby="phone_number_hint phone_number_feedback"
              inputmode="numeric"
              autocomplete="tel"
              maxlength="13"
            >
          </div>
          <p id="phone_number_hint" class="text-xs text-blue-800/70 ml-3 -mt-2">Format: 09XX XXX XXXX</p>
          <p id="phone_number_feedback" class="hidden text-red-600 text-sm ml-3 -mt-3" aria-live="polite"></p>
          @error('phone_number') <p class="text-red-600 text-sm ml-3 -mt-2">{{ $message }}</p> @enderror

          <!-- Email (styled exactly like login email field) -->
          <div class="relative">
            <span class="absolute inset-y-0 left-4 flex items-center">
              <i class="fas fa-envelope text-yellow-400"></i>
            </span>
            <input id="email" type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required
              aria-describedby="email_feedback"
              class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-4 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
          </div>
          <p id="email_feedback" class="hidden text-red-600 text-sm ml-3 -mt-3" aria-live="polite"></p>
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
              aria-describedby="password_requirements password_feedback"
              class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-12 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
              pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}"
              title="Password must be at least 8 characters long and include uppercase and lowercase letters, a number, and a special character.">
            <button type="button" id="togglePassword" class="absolute inset-y-0 right-3 px-3 flex items-center text-blue-800/70 hover:text-blue-900">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <div id="password_requirements" class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900 -mt-2">
            <p class="font-semibold">Password must include:</p>
            <div class="mt-2 space-y-2">
              <div class="password-requirement flex items-center gap-2 text-blue-800/80" data-rule="length">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-blue-200 text-[10px] text-blue-300">
                  <i class="fas fa-check"></i>
                </span>
                <span>At least 8 characters</span>
              </div>
              <div class="password-requirement flex items-center gap-2 text-blue-800/80" data-rule="mixedCase">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-blue-200 text-[10px] text-blue-300">
                  <i class="fas fa-check"></i>
                </span>
                <span>Uppercase and lowercase letters</span>
              </div>
              <div class="password-requirement flex items-center gap-2 text-blue-800/80" data-rule="number">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-blue-200 text-[10px] text-blue-300">
                  <i class="fas fa-check"></i>
                </span>
                <span>At least 1 number</span>
              </div>
              <div class="password-requirement flex items-center gap-2 text-blue-800/80" data-rule="symbol">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-blue-200 text-[10px] text-blue-300">
                  <i class="fas fa-check"></i>
                </span>
                <span>At least 1 special character</span>
              </div>
            </div>
          </div>
          <p id="password_feedback" class="hidden text-red-600 text-sm ml-3 -mt-3" aria-live="polite"></p>
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
              aria-describedby="password_confirmation_feedback"
              class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-12 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
            <button type="button" id="togglePasswordConfirm" class="absolute inset-y-0 right-3 px-3 flex items-center text-blue-800/70 hover:text-blue-900">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <p id="password_confirmation_feedback" class="hidden text-red-600 text-sm ml-3 -mt-3" aria-live="polite"></p>
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

<!-- Form Scripts -->
<script>
  const FIELD_BASE_CLASSES = ['border-blue-400', 'focus:ring-blue-600', 'focus:border-blue-600'];
  const FIELD_ERROR_CLASSES = ['border-red-500', 'focus:ring-red-500', 'focus:border-red-500'];
  const FIELD_SUCCESS_CLASSES = ['border-green-500', 'focus:ring-green-500', 'focus:border-green-500'];

  function applyFieldClasses(input, classesToAdd) {
    if (!input) return;
    input.classList.remove(...FIELD_BASE_CLASSES, ...FIELD_ERROR_CLASSES, ...FIELD_SUCCESS_CLASSES);
    input.classList.add(...classesToAdd);
  }

  function setFieldState(input, feedback, state, message = '') {
    if (!input || !feedback) return state !== 'invalid';

    if (state === 'invalid') {
      applyFieldClasses(input, FIELD_ERROR_CLASSES);
      input.setCustomValidity(message);
      feedback.textContent = message;
      feedback.classList.remove('hidden');
      return false;
    }

    input.setCustomValidity('');
    feedback.textContent = '';
    feedback.classList.add('hidden');

    if (state === 'valid') {
      applyFieldClasses(input, FIELD_SUCCESS_CLASSES);
    } else {
      applyFieldClasses(input, FIELD_BASE_CLASSES);
    }

    return true;
  }

  function formatPhoneNumber(value) {
    const digits = value.replace(/\D/g, '').slice(0, 11);
    const parts = [];

    if (digits.length > 0) parts.push(digits.slice(0, 4));
    if (digits.length > 4) parts.push(digits.slice(4, 7));
    if (digits.length > 7) parts.push(digits.slice(7, 11));

    return parts.join(' ');
  }

  function validatePhoneNumber(force = false) {
    const input = document.getElementById('phone_number');
    const feedback = document.getElementById('phone_number_feedback');
    if (!input || !feedback) return true;

    input.value = formatPhoneNumber(input.value);
    const normalized = input.value.replace(/\D/g, '');
    const showFeedback = force || input.dataset.touched === 'true';

    if (normalized === '') {
      return force
        ? setFieldState(input, feedback, 'invalid', 'Contact number is required.')
        : setFieldState(input, feedback, 'neutral');
    }

    if (!/^09\d{9}$/.test(normalized)) {
      return showFeedback
        ? setFieldState(input, feedback, 'invalid', 'Enter a valid contact number using the format 09XX XXX XXXX.')
        : setFieldState(input, feedback, 'neutral');
    }

    return setFieldState(input, feedback, 'valid');
  }

  function validateEmailAddress(force = false) {
    const input = document.getElementById('email');
    const feedback = document.getElementById('email_feedback');
    if (!input || !feedback) return true;

    const value = input.value.trim();
    input.value = value;
    const showFeedback = force || input.dataset.touched === 'true';
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (value === '') {
      return force
        ? setFieldState(input, feedback, 'invalid', 'Email address is required.')
        : setFieldState(input, feedback, 'neutral');
    }

    if (!emailPattern.test(value)) {
      return showFeedback
        ? setFieldState(input, feedback, 'invalid', 'Enter a valid email address.')
        : setFieldState(input, feedback, 'neutral');
    }

    return setFieldState(input, feedback, 'valid');
  }

  function updatePasswordRequirements() {
    const input = document.getElementById('password');
    if (!input) return { length: false, mixedCase: false, number: false, symbol: false };

    const value = input.value;
    const status = {
      length: value.length >= 8,
      mixedCase: /[a-z]/.test(value) && /[A-Z]/.test(value),
      number: /\d/.test(value),
      symbol: /[^A-Za-z0-9\s]/.test(value),
    };

    document.querySelectorAll('.password-requirement').forEach((item) => {
      const isMet = Boolean(status[item.dataset.rule]);
      const badge = item.querySelector('span');

      item.classList.toggle('text-green-700', isMet);
      item.classList.toggle('text-blue-800/80', !isMet);

      if (badge) {
        badge.classList.toggle('border-green-300', isMet);
        badge.classList.toggle('bg-green-100', isMet);
        badge.classList.toggle('text-green-600', isMet);
        badge.classList.toggle('border-blue-200', !isMet);
        badge.classList.toggle('bg-transparent', !isMet);
        badge.classList.toggle('text-blue-300', !isMet);
      }
    });

    return status;
  }

  function validatePassword(force = false) {
    const input = document.getElementById('password');
    const feedback = document.getElementById('password_feedback');
    if (!input || !feedback) return true;

    const value = input.value;
    const showFeedback = force || input.dataset.touched === 'true';
    const requirements = updatePasswordRequirements();
    const isValid = Object.values(requirements).every(Boolean);

    if (value === '') {
      return force
        ? setFieldState(input, feedback, 'invalid', 'Password is required.')
        : setFieldState(input, feedback, 'neutral');
    }

    if (!isValid) {
      return showFeedback
        ? setFieldState(input, feedback, 'invalid', 'Password must be at least 8 characters and include uppercase and lowercase letters, a number, and a special character.')
        : setFieldState(input, feedback, 'neutral');
    }

    return setFieldState(input, feedback, 'valid');
  }

  function validatePasswordConfirmation(force = false) {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const feedback = document.getElementById('password_confirmation_feedback');
    if (!passwordInput || !confirmInput || !feedback) return true;

    const value = confirmInput.value;
    const showFeedback = force || confirmInput.dataset.touched === 'true';

    if (value === '') {
      return force
        ? setFieldState(confirmInput, feedback, 'invalid', 'Please confirm your password.')
        : setFieldState(confirmInput, feedback, 'neutral');
    }

    if (value !== passwordInput.value) {
      return showFeedback
        ? setFieldState(confirmInput, feedback, 'invalid', 'Passwords do not match.')
        : setFieldState(confirmInput, feedback, 'neutral');
    }

    return setFieldState(confirmInput, feedback, 'valid');
  }

  function validateRegistrationForm(form) {
    const isPhoneValid = validatePhoneNumber(true);
    const isEmailValid = validateEmailAddress(true);
    const isPasswordValid = validatePassword(true);
    const isPasswordConfirmationValid = validatePasswordConfirmation(true);
    const isFormValid = isPhoneValid && isEmailValid && isPasswordValid && isPasswordConfirmationValid && form.checkValidity();

    if (!isFormValid) {
      form.reportValidity();
    }

    return isFormValid;
  }

  function bindBlurValidation(input, validator, onInput) {
    if (!input) return;

    input.addEventListener('input', () => {
      if (typeof onInput === 'function') {
        onInput();
      }

      if (input.dataset.touched === 'true') {
        validator(false);
      }
    });

    input.addEventListener('blur', () => {
      input.dataset.touched = 'true';
      validator(false);
    });
  }

  function setupPasswordToggle(buttonId, inputId) {
    const button = document.getElementById(buttonId);
    const input = document.getElementById(inputId);
    if (!button || !input) return;

    button.addEventListener('click', function () {
      const isPassword = input.getAttribute('type') === 'password';
      input.setAttribute('type', isPassword ? 'text' : 'password');
      this.setAttribute('aria-pressed', isPassword ? 'true' : 'false');

      const icon = this.querySelector('i');
      if (!icon) return;

      icon.classList.toggle('fa-eye', !isPassword);
      icon.classList.toggle('fa-eye-slash', isPassword);
    });
  }

  function initializeRegisterForm() {
    const phoneInput = document.getElementById('phone_number');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');

    if (phoneInput) {
      phoneInput.value = formatPhoneNumber(phoneInput.value);
    }

    bindBlurValidation(phoneInput, validatePhoneNumber, () => {
      phoneInput.value = formatPhoneNumber(phoneInput.value);
    });
    bindBlurValidation(emailInput, validateEmailAddress);
    bindBlurValidation(passwordInput, validatePassword, () => {
      updatePasswordRequirements();

      if (passwordConfirmInput && passwordConfirmInput.value !== '') {
        validatePasswordConfirmation(false);
      }
    });
    bindBlurValidation(passwordConfirmInput, validatePasswordConfirmation);

    updatePasswordRequirements();
    setupPasswordToggle('togglePassword', 'password');
    setupPasswordToggle('togglePasswordConfirm', 'password_confirmation');
  }

  initializeRegisterForm();

  function signupPage({ hasErrors }) {
    return {
      showModal: false,
      agreed: false,
      checkboxChecked: false,
      hasErrors: hasErrors === 'true',
      isSubmitting: false,
      submitForm(form) {
        if (!(this.agreed && this.checkboxChecked)) {
          return;
        }

        if (!validateRegistrationForm(form)) {
          return;
        }

        this.isSubmitting = true;
        form.submit();
      },
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
