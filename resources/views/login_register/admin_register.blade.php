<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Register Employee - DILG CAR Recruitment and Selection Portal</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet" />

</head>
<body class="relative min-h-screen overflow-x-hidden bg-[#031029] p-3 md:p-4">
  @if(session('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
      class="fixed top-5 right-5 z-50 w-full max-w-sm rounded-xl border border-green-400 bg-green-100 px-4 py-3 text-green-700 shadow-lg">
      <strong class="font-bold">Success!</strong>
      <p class="text-sm">{{ session('success') }}</p>
    </div>
  @endif

  @php
    $adminRegisterErrors = $errors->getBag('adminRegister');
    $allErrors = array_merge($errors->all(), $adminRegisterErrors->all());
  @endphp
  @if (!empty($allErrors))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition
      class="fixed top-5 right-5 z-50 w-full max-w-sm rounded-xl border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow-lg">
      <strong class="font-bold">Whoops!</strong>
      <ul class="mt-1 list-disc list-inside text-sm">
        @foreach ($allErrors as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div aria-hidden="true" class="pointer-events-none absolute inset-0">
    <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(24,99,156,0.78)_0%,rgba(255,255,255,0.012)_24%,rgba(255,255,255,0)_100%)]"></div>
    <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(3,13,33,0.28)_0%,rgba(3,13,33,0)_44%,rgba(3,13,33,0.2)_100%)]"></div>
  </div>

  <div class="relative z-10 mx-auto flex min-h-[calc(100vh-1.5rem)] w-full max-w-[980px] items-center justify-center">
    <div class="relative z-10 w-full overflow-hidden rounded-[22px] border border-white/20 bg-[linear-gradient(140deg,rgba(255,255,255,0.96)_0%,rgba(247,251,255,0.96)_100%)] shadow-[0_30px_85px_rgba(2,9,25,0.37)] backdrop-blur-[10px]">
      <section class="relative z-10 max-h-[calc(100vh-1.5rem)] overflow-y-auto bg-[linear-gradient(180deg,rgba(255,255,255,0.82)_0%,rgba(248,251,255,0.92)_100%)] px-5 py-5 sm:px-8 sm:py-7 lg:px-10 lg:py-8">
        <div class="mx-auto w-full">
          <div class="mb-5 flex flex-col gap-4 border-b border-[#d6dceb] pb-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-center gap-3">
              <div class="flex h-14 w-14 items-center justify-center">
                <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-12 w-12">
              </div>
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#1d3f79]">DILG CAR</p>
                <h2 class="mt-1 font-['Space_Grotesk'] text-3xl font-extrabold tracking-tight text-[#162a56]">Create Employee Account</h2>
                <p class="mt-1 max-w-2xl text-sm leading-relaxed text-[#4c638e]">
                  Create an employee account. Role assignment and approval is handled by the superadmin.
                </p>
              </div>
            </div>

            <div class="rounded-xl border border-[#d2d9ea] bg-[#eef2f8] px-4 py-3 text-sm text-[#4b5f86] lg:max-w-sm">
              <div class="flex items-start gap-3">
                <span class="mt-0.5 text-[#2d4f8c]"><i class="fa-solid fa-shield-halved"></i></span>
                <div>
                  <p class="font-semibold text-[#223a69]">Secure registration</p>
                  <p class="mt-1">Employee accounts require approval before access is granted.</p>
                </div>
              </div>
            </div>
          </div>

          <form id="adminRegisterPageForm" method="POST" action="{{ route('admin.register.submit') }}" autocomplete="off" class="space-y-4">
            @csrf

            <div class="grid gap-4 rounded-2xl border border-[#d5dcea] bg-[#f2f5fb] p-4 md:p-5 xl:grid-cols-[1.15fr_1fr] xl:divide-x xl:divide-[#dce2ef]">
              <section class="rounded-xl bg-transparent p-0 md:pr-5">
                <div class="mb-3">
                  <p class="text-xs font-bold uppercase tracking-wide text-[#2b4575]">Personal Information</p>
                  <p class="mt-1 text-sm text-[#4f6590]">Provide the name and profile details for the employee.</p>
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                  <div>
                    <label for="first_name" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">First Name <span class="text-red-600">*</span></label>
                    <input id="first_name" type="text" name="first_name" placeholder="First name" value="{{ old('first_name') }}" required
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200">
                  </div>

                  <div>
                    <label for="middle_name" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Middle Name</label>
                    <input id="middle_name" type="text" name="middle_name" placeholder="Middle name" value="{{ old('middle_name') }}"
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200">
                  </div>

                  <div>
                    <label for="last_name" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Last Name <span class="text-red-600">*</span></label>
                    <input id="last_name" type="text" name="last_name" placeholder="Last name" value="{{ old('last_name') }}" required
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200">
                  </div>
                </div>
              </section>

              <section class="rounded-xl bg-transparent p-0 md:pl-5">
                <div class="mb-3">
                  <p class="text-xs font-bold uppercase tracking-wide text-[#2b4575]">Work Assignment & Account Details</p>
                  <p class="mt-1 text-sm text-[#4f6590]">Enter office and designation, and set account credentials.</p>
                </div>

                <div class="space-y-3">
                  <div>
                    <label for="office" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Office <span class="text-red-600">*</span></label>
                    <input id="office" type="text" name="office" placeholder="Office / unit" value="{{ old('office') }}" required
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200">
                  </div>

                  <div>
                    <label for="designation" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Designation <span class="text-red-600">*</span></label>
                    <input id="designation" type="text" name="designation" placeholder="Position / designation" value="{{ old('designation') }}" required
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200">
                  </div>

                  <div>
                    <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email Address <span class="text-red-600">*</span></label>
                    <input id="email" type="email" name="email" placeholder="name@example.com" value="{{ old('email') }}" required
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200">
                  </div>

                  <div>
                    <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Password</label>
                    <div class="relative">
                      <input id="password" type="password" name="password" placeholder="Create password" required minlength="8"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200">
                      <button type="button" id="togglePassword" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                  </div>

                  <div>
                    <label for="password_confirmation" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Confirm Password</label>
                    <div class="relative">
                      <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirm password" required minlength="8"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200">
                      <button type="button" id="togglePasswordConfirm" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                  </div>
                  
                  <div id="adminPasswordRequirementsPanel" class="hidden rounded-lg border border-slate-200 bg-white px-3 py-3 text-xs text-slate-700 sm:text-sm mt-2">
                    <p class="font-semibold text-slate-800">Password must include:</p>
                    <div class="mt-2 space-y-1.5">
                      <div class="password-requirement flex items-center gap-2 text-slate-500" data-rule="length">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-200 bg-transparent text-[9px] text-slate-300">
                          <i class="fas fa-check"></i>
                        </span>
                        <span>At least 8 characters</span>
                      </div>
                      <div class="password-requirement flex items-center gap-2 text-slate-500" data-rule="mixedCase">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-200 bg-transparent text-[9px] text-slate-300">
                          <i class="fas fa-check"></i>
                        </span>
                        <span>Uppercase and lowercase letters</span>
                      </div>
                      <div class="password-requirement flex items-center gap-2 text-slate-500" data-rule="number">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-200 bg-transparent text-[9px] text-slate-300">
                          <i class="fas fa-check"></i>
                        </span>
                        <span>At least 1 number</span>
                      </div>
                      <div class="password-requirement flex items-center gap-2 text-slate-500" data-rule="symbol">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-200 bg-transparent text-[9px] text-slate-300">
                          <i class="fas fa-check"></i>
                        </span>
                        <span>At least 1 special character</span>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
            </div>

            <div class="rounded-xl border border-[#d3dbe9] bg-[#eef2f8] p-3 sm:p-4">
              <p class="mt-2.5 text-center text-xs text-slate-600 sm:text-sm">
                After creating an account you will be logged in and your account will be pending approval by superadmin.
              </p>

              <div class="mt-4">
                <button type="submit"
                  class="w-full rounded-lg bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0A2259]">
                  Create Employee Account
                </button>
              </div>

              <p class="mt-3 text-center text-xs text-slate-600 sm:text-sm">
                Already have an account?
                <a href="{{ route('admin.login') }}" class="font-semibold text-[#0D2B70] hover:underline">Log in</a>
              </p>
            </div>
          </form>
        </div>
      </section>
    </div>
  </div>

  @include('partials.loader')

  <script>
    (function () {
      const passInput = document.getElementById('password');
      const passConfirm = document.getElementById('password_confirmation');
      const toggle = document.getElementById('togglePassword');
      const toggleConfirm = document.getElementById('togglePasswordConfirm');
      const panel = document.getElementById('adminPasswordRequirementsPanel');

      function updatePasswordRequirements() {
        if (!passInput || !panel) return;
        const value = passInput.value || '';
        const status = {
          length: value.length >= 8,
          mixedCase: /[a-z]/.test(value) && /[A-Z]/.test(value),
          number: /\d/.test(value),
          symbol: /[^A-Za-z0-9\s]/.test(value),
        };

        panel.querySelectorAll('.password-requirement').forEach((item) => {
          const rule = item.dataset.rule;
          const isMet = Boolean(status[rule]);
          item.classList.toggle('text-emerald-700', isMet);
          item.classList.toggle('text-slate-500', !isMet);
          const badge = item.querySelector('span');
          if (badge) {
            badge.classList.toggle('border-emerald-200', isMet);
            badge.classList.toggle('bg-emerald-50', isMet);
            badge.classList.toggle('text-emerald-600', isMet);
            badge.classList.toggle('border-slate-200', !isMet);
            badge.classList.toggle('bg-transparent', !isMet);
            badge.classList.toggle('text-slate-300', !isMet);
          }
        });

        return Object.values(status).every(Boolean);
      }

      function showPanel() {
        if (panel) {
          panel.classList.remove('hidden');
          panel.setAttribute('aria-hidden', 'false');
        }
      }

      function hidePanel() {
        if (panel) {
          panel.classList.add('hidden');
          panel.setAttribute('aria-hidden', 'true');
        }
      }

      if (toggle) {
        toggle.addEventListener('click', function () {
          if (!passInput) return;
          const isPassword = passInput.getAttribute('type') === 'password';
          passInput.setAttribute('type', isPassword ? 'text' : 'password');
          this.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
          const icon = this.querySelector('i'); if (icon) icon.classList.toggle('fa-eye-slash');
        });
      }

      if (toggleConfirm) {
        toggleConfirm.addEventListener('click', function () {
          if (!passConfirm) return;
          const isPassword = passConfirm.getAttribute('type') === 'password';
          passConfirm.setAttribute('type', isPassword ? 'text' : 'password');
          this.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
          const icon = this.querySelector('i'); if (icon) icon.classList.toggle('fa-eye-slash');
        });
      }

      if (passInput) {
        passInput.addEventListener('focus', () => { showPanel(); updatePasswordRequirements(); });
        passInput.addEventListener('input', updatePasswordRequirements);
        passInput.addEventListener('blur', () => { setTimeout(() => { hidePanel(); }, 150); });
      }

      const form = document.getElementById('adminRegisterPageForm');
      if (form) {
        form.addEventListener('submit', function (e) {
          const ok = updatePasswordRequirements();
          const confirmMatches = passConfirm && passInput && (passConfirm.value === passInput.value);
          if (!ok || !confirmMatches) {
            e.preventDefault();
            showPanel();
            if (!confirmMatches && passConfirm) {
              passConfirm.classList.add('border-red-500');
              passConfirm.focus();
            } else if (!ok && passInput) {
              passInput.classList.add('border-red-500');
              passInput.focus();
            }
            return false;
          }
          // otherwise let the form submit normally
        });
      }
    })();
  </script>
</body>
</html>
