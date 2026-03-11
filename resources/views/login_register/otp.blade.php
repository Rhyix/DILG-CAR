<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verification Code - DILG CAR Recruitment and Selection Portal</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    @include('partials.global_toast')

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            overflow: hidden;
        }

        body {
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
                radial-gradient(circle at 14% 74%, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 46%),
                linear-gradient(120deg, rgba(255, 255, 255, 0.10) 0%, rgba(255, 255, 255, 0) 34%);
        }

        .otp-shell {
            position: relative;
            z-index: 1;
            max-height: calc(100vh - 2rem);
            overflow: hidden;
        }
    </style>
</head>

@if (!isset($status))
    <script>
        window.location.href = "{{ route('register.form') }}";
    </script>
@endif

<body class="relative min-h-screen p-4 md:p-6">
    <div aria-hidden="true" class="portal-page-bg"></div>

    <div class="otp-shell mx-auto flex min-h-[calc(100vh-2rem)] w-full max-w-6xl items-center justify-center">
        <div class="grid w-full overflow-hidden rounded-3xl border border-white/15 bg-white/95 shadow-[0_30px_90px_rgba(2,12,34,0.35)] backdrop-blur-sm lg:grid-cols-[1fr_0.95fr]">
            <section class="bg-gradient-to-br from-[#081c47] via-[#0d2b70] to-[#17438b] px-6 py-9 text-white sm:px-10 lg:px-12">
                <div class="mx-auto flex h-full max-w-xl flex-col justify-center">
                    <div class="mb-8 flex items-center gap-4">
                        <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-14 w-14 rounded-full bg-white/10 p-1 shadow-[0_10px_20px_rgba(2,12,34,0.25)]" />
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.34em] text-blue-100">DILG CAR</p>
                            <h2 class="mt-1 text-2xl font-extrabold tracking-tight">OTP Verification</h2>
                        </div>
                    </div>

                    <p class="max-w-lg text-sm leading-relaxed text-blue-100 sm:text-base">
                        A verification code was sent to your email. Enter the 6-digit OTP to continue account verification.
                    </p>

                    <p class="mt-10 text-sm font-semibold tracking-[0.12em] text-yellow-300">
                        MATINO. MAHUSAY. MAAASAHAN.
                    </p>
                </div>
            </section>

            <section class="bg-white px-6 py-9 sm:px-10 lg:px-12">
                <div class="mx-auto max-w-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Secure Access</p>
                    <h3 class="mt-2 text-3xl font-extrabold tracking-tight text-[#0D2B70]">Enter Verification Code</h3>
                    <p class="mt-2 text-sm text-slate-600">Code sent to <span class="font-semibold">{{ $email ?? old('email') }}</span></p>

                    <form method="POST" action="{{ route('otp_check', [], false) }}" class="mt-7 space-y-5" autocomplete="off">
                        @csrf
                        <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}">

                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                                <i class="fa-solid fa-shield-halved"></i>
                            </span>
                            <input
                                required
                                type="text"
                                name="otp"
                                inputmode="numeric"
                                pattern="\d*"
                                maxlength="6"
                                placeholder="Enter 6-digit OTP"
                                autocomplete="one-time-code"
                                class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-12 pr-4 text-center text-lg tracking-[0.35em] text-[#0D2B70] outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
                            />
                        </div>

                        @if ($errors->any())
                            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                                <ul class="list-disc pl-5 text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-center">
                            <p id="timer" class="text-sm text-slate-700">
                                Resend OTP in <span id="countdown" class="font-bold text-[#0D2B70]">00:30</span>
                            </p>
                            <a href="#" id="resend-link" class="hidden text-sm font-semibold text-[#0D2B70] hover:underline">
                                Resend Code
                            </a>
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-[#0D2B70] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#0A2259]">
                            Verify OTP
                        </button>

                        <p class="text-center text-sm text-slate-600">
                            <a href="{{ route('login.form', [], false) }}" class="use-loader font-semibold text-[#0D2B70] hover:underline">Back to Login</a>
                        </p>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script>
        const countdownEl = document.getElementById('countdown');
        const resendLink = document.getElementById('resend-link');
        const timerEl = document.getElementById('timer');
        const defaultCooldown = 30;
        const storageKey = 'register_otp_resend_available_at';
        const serverResendAvailableAt = {{ (int) ($resendAvailableAtTs ?? now()->timestamp) }} * 1000;
        const storedResendAvailableAt = Number(sessionStorage.getItem(storageKey) || 0);
        const resendAvailableAt = Math.max(serverResendAvailableAt, storedResendAvailableAt);
        let timerInterval = null;
        let countdown = Math.max(0, Math.floor((resendAvailableAt - Date.now()) / 1000));

        function renderCountdown() {
            const minutes = String(Math.floor(countdown / 60)).padStart(2, '0');
            const seconds = String(countdown % 60).padStart(2, '0');
            countdownEl.textContent = `${minutes}:${seconds}`;

            if (countdown <= 0) {
                clearInterval(timerInterval);
                sessionStorage.removeItem(storageKey);
                timerEl.classList.add('hidden');
                resendLink.classList.remove('hidden');
                return;
            }

            countdown -= 1;
        }

        function startCooldown(seconds) {
            clearInterval(timerInterval);
            countdown = Math.max(0, Number(seconds) || defaultCooldown);
            const expiresAt = Date.now() + (countdown * 1000);
            sessionStorage.setItem(storageKey, String(expiresAt));
            resendLink.classList.add('hidden');
            timerEl.classList.remove('hidden');
            renderCountdown();
            timerInterval = setInterval(renderCountdown, 1000);
        }

        startCooldown(countdown);

        resendLink.addEventListener('click', async function (event) {
            event.preventDefault();

            try {
                const response = await fetch("{{ route('otp_resend', [], false) }}", {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email: '{{ $email ?? old('email') }}' })
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const retryAfter = Number(data.retry_after || defaultCooldown);
                    if (response.status === 429) {
                        showAppToast(data.message || `Please wait ${retryAfter} seconds.`);
                        startCooldown(retryAfter);
                        return;
                    }
                    throw new Error(data.message || 'Resend failed');
                }

                showAppToast(data.message || 'New OTP sent successfully.');
                const serverNextAllowed = Number(data.resend_available_at || 0) * 1000;
                if (serverNextAllowed > 0) {
                    sessionStorage.setItem(storageKey, String(serverNextAllowed));
                }
                startCooldown(Number(data.retry_after || defaultCooldown));
            } catch (error) {
                console.error('Resend error:', error);
                timerEl.classList.remove('hidden');
                timerEl.innerHTML = '<span class="text-sm text-red-600">Failed to resend OTP. Try again.</span>';
            }
        });
    </script>

    @include('partials.loader')
</body>
</html>
