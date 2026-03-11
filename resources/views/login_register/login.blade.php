<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login - DILG CAR Recruitment and Selection Portal</title>
  @if(app()->environment('production'))
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  @endif
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <meta property="og:title" content="DILG - CAR Recruitment Selection and Placement Portal" />
  <meta property="og:description" content="Isa ka bang MATINO, MAHUSAY, at MAAASAHAN na manggagawang Pilipino?" />
  <meta property="og:image" content="{{ asset('images/dilg_rsp_thumbnail.png') }}" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:image:type" content="image/png" />
  <meta property="og:url" content="{{ url()->current() }}" />
  <meta property="og:type" content="website" />

  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="DILG - CAR Recruitment Selection and Placement Portal" />
  <meta name="twitter:description" content="Isa ka bang MATINO, MAHUSAY, at MAAASAHAN na manggagawang Pilipino?" />
  <meta name="twitter:image" content="{{ asset('images/dilg_rsp_thumbnail.png') }}" />

  <style>
    html,
    body {
      height: 100%;
      font-family: 'Montserrat', sans-serif;
      overflow: hidden;
    }

    body {
      margin: 0;
      background-color: #04132f;
      background-image: url('{{ asset('templates/template.png') }}');
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
    }

    .portal-page-bg {
      position: absolute;
      inset: 0;
      pointer-events: none;
      background:
        radial-gradient(circle at 18% 16%, rgba(96, 165, 250, 0.18) 0%, rgba(96, 165, 250, 0) 26%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.012) 22%, rgba(255, 255, 255, 0) 100%),
        linear-gradient(90deg, rgba(3, 13, 33, 0.22) 0%, rgba(3, 13, 33, 0) 38%, rgba(3, 13, 33, 0.14) 100%);
    }

    .portal-page-bg::before {
      content: '';
      position: absolute;
      inset: 0;
      opacity: 0.32;
      background:
        radial-gradient(circle at 74% 24%, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 28%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.06) 0%, transparent 24%);
    }

    .portal-page-bg::after {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 82% 58%, rgba(96, 165, 250, 0.10) 0%, rgba(96, 165, 250, 0) 24%),
        linear-gradient(180deg, rgba(2, 9, 25, 0) 0%, rgba(2, 9, 25, 0.18) 100%);
      opacity: 0.55;
    }

    .portal-shell {
      position: relative;
      isolation: isolate;
    }

    .portal-shell::before {
      content: '';
      position: absolute;
      top: 50%;
      right: 6%;
      width: min(34rem, 42vw);
      height: min(34rem, 42vw);
      border-radius: 9999px;
      transform: translateY(-50%);
      background: radial-gradient(circle, rgba(147, 197, 253, 0.22) 0%, rgba(96, 165, 250, 0.12) 34%, rgba(59, 130, 246, 0.04) 54%, rgba(59, 130, 246, 0) 72%);
      filter: blur(12px);
      pointer-events: none;
      z-index: 0;
    }

    .portal-hero {
      background:
        linear-gradient(160deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0) 32%),
        linear-gradient(180deg, #081c47 0%, #0d2b70 56%, #17438b 100%);
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
    }

    .portal-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.07) 0%, rgba(255, 255, 255, 0.03) 18%, rgba(255, 255, 255, 0) 44%);
      opacity: 0.5;
    }

    .portal-form-surface {
      background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.96) 100%);
    }

    .portal-brand-panel {
      border: 1px solid rgba(255, 255, 255, 0.12);
      background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.02) 28%, rgba(255, 255, 255, 0.01) 100%);
      box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 24px 60px rgba(2, 12, 34, 0.18);
      backdrop-filter: blur(4px);
      overflow: hidden;
    }

    .portal-department-line {
      display: block;
      width: 100%;
      font-size: clamp(0.5rem, 0.42rem + 0.18vw, 0.6rem);
      letter-spacing: 0.015em;
      line-height: 1.5;
      white-space: nowrap;
    }

    @media (max-width: 1023px) {
      .portal-shell::before {
        right: 50%;
        width: min(30rem, 82vw);
        height: min(30rem, 82vw);
        transform: translate(50%, -44%);
      }
    }
  </style>
</head>
<body class="relative min-h-screen overflow-hidden p-4 md:p-6">
  <div aria-hidden="true" class="portal-page-bg"></div>

  <div class="portal-shell relative z-10 mx-auto flex min-h-[calc(100vh-2rem)] w-full max-w-7xl items-center justify-center">
    <div class="relative z-10 grid w-full max-h-[calc(100vh-2rem)] overflow-hidden rounded-3xl border border-white/12 bg-white/96 shadow-[0_28px_90px_rgba(3,12,32,0.34)] backdrop-blur-sm lg:grid-cols-[minmax(0,0.94fr)_minmax(0,1.06fr)]">
      <section class="portal-form-surface relative px-5 py-8 sm:px-8 lg:px-10 xl:px-12">
        <div class="mx-auto w-full max-w-lg">
          <div class="mb-6 flex items-center gap-3 lg:hidden">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-12 w-12" />
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#0D2B70]">DILG CAR</p>
              <p class="text-lg font-bold text-[#0D2B70]">Recruitment Portal</p>
            </div>
          </div>

          <div class="rounded-[1.75rem] border border-slate-200/90 bg-white p-6 shadow-[0_18px_48px_rgba(15,23,42,0.08)] sm:p-7">
            <div class="mb-6">
              <p class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">Applicant Access</p>
              <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-[#0D2B70]">User Login</h2>
              <p class="mt-2 text-sm leading-6 text-slate-500">Sign in to continue to your applicant dashboard.</p>
            </div>

            @if (session('success'))
              <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
              </div>
            @endif

            @if (session('status'))
              <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                {{ session('status') }}
              </div>
            @endif

            @if ($errors->any())
              <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc pl-5">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4" autocomplete="off">
              @csrf

              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email</label>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <i class="fa-solid fa-envelope"></i>
                  </span>
                  <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
                  >
                </div>
              </div>

              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Password</label>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <i class="fa-solid fa-lock"></i>
                  </span>
                  <input
                    id="user_password"
                    type="password"
                    name="password"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-10 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
                  >
                  <button
                    type="button"
                    id="togglePassword"
                    aria-label="Toggle password visibility"
                    aria-pressed="false"
                    class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600"
                  >
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
              </div>

              <div class="flex flex-col gap-2 pt-1 text-sm sm:flex-row sm:items-center sm:justify-between">
                <label class="inline-flex items-center gap-2 text-slate-600">
                  <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="rounded border-slate-300 text-[#0D2B70] focus:ring-[#0D2B70]/30">
                  <span class="font-semibold">Remember me</span>
                </label>
                <a href="{{ route('forgot.password.form') }}" class="font-semibold text-[#0D2B70] hover:underline">Forgot Password?</a>
              </div>

              @if(app()->environment('production'))
                <div class="pt-1">
                  <div class="g-recaptcha" data-sitekey="6LfpjpErAAAAADcMjUqP3AZmsMae7WvrjcA5OSvs" data-action="LOGIN"></div>
                </div>
              @endif

              <button type="submit" class="use-loader w-full rounded-xl bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0A2259]">
                Sign In
              </button>
            </form>

            <div class="mt-5 border-t border-slate-200 pt-4">
              <p class="text-xs uppercase tracking-wide text-slate-500">Alternative Access</p>
              <a
                href="{{ route('google.login', [], false) }}"
                class="use-loader mt-2 flex w-full items-center justify-center gap-3 rounded-xl border border-[#0D2B70]/15 bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] transition hover:bg-slate-50"
              >
                <img src="{{ asset('images/google-icon.png') }}" alt="Google Icon" class="h-5 w-5">
                Continue with Google
              </a>

              @if (Route::has('register.form'))
                <p class="mt-4 text-center text-sm text-slate-600">
                  Don't have an account?
                  <a href="{{ route('register.form') }}" class="use-loader font-semibold text-[#0D2B70] hover:underline">Create one here</a>
                </p>
              @endif
            </div>
          </div>
        </div>
      </section>

      <section class="portal-hero relative hidden overflow-hidden px-5 py-10 text-white lg:flex lg:items-center lg:justify-center xl:px-6">
        <div class="portal-brand-panel relative z-10 w-full max-w-[40rem] rounded-[2rem] px-5 py-10 text-center sm:px-6 xl:px-7 xl:py-12">
          <img
            src="{{ asset('images/dilg_logo.png') }}"
            alt="DILG Logo"
            class="mx-auto h-24 w-24 rounded-full bg-white/10 p-3 shadow-[0_16px_32px_rgba(2,12,34,0.24)] xl:h-28 xl:w-28"
          />
          <p class="mt-6 text-sm font-semibold uppercase tracking-[0.42em] text-blue-100">DILG CAR</p>
          <h3 class="mt-4 text-3xl font-extrabold leading-tight text-white xl:text-[2rem]">
            Recruitment Selection and Placement Portal
          </h3>
          <p class="portal-department-line mt-5 font-semibold uppercase">
            DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT CORDILLERA ADMINISTRATIVE REGION
          </p>
          <p class="mt-4 text-sm font-semibold uppercase tracking-[0.24em] text-yellow-200 xl:text-base">
            Matino, Mahusay, Maaasahan.
          </p>
          
        </div>
      </section>
    </div>
  </div>

  @include('partials.loader')

  <script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('user_password');

    if (togglePassword && passwordInput) {
      togglePassword.addEventListener('click', function () {
        const isPassword = passwordInput.getAttribute('type') === 'password';
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        this.setAttribute('aria-pressed', isPassword ? 'true' : 'false');

        const icon = this.querySelector('i');
        if (!icon) return;

        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
      });
    }
  </script>
</body>
</html>
