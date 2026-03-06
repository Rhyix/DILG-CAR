<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Verification Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    @include('partials.global_toast')
    <style>
        .font-montserrat {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="bg-white h-screen flex flex-col">
    <!-- Header Bar -->
    <header class="bg-[#002b6d] flex items-center h-20 px-6 space-x-6">
        <div class="flex-shrink-0">
            <img
                src="{{ asset('images/dilg_logo.png') }}"
                alt="DILG Logo"
                class="mx-auto mb-5 mt-5 max-w-[67px]"
                loading="lazy"
            />
        </div>
        <div class="flex flex-col text-white leading-tight max-w-lg">
            <span class="font-bold text-sm font-montserrat">DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT</span>
            <span class="text-xs opacity-70 font-montserrat">CORDILLERA ADMINISTRATIVE REGION</span>
            <span class="font-bold text-yellow-400 text-xs font-montserrat">
                RECRUITMENT SELECTION AND PLACEMENT PORTAL
            </span>
        </div>
    </header>

    <!-- Main content -->
    <main class="flex-grow flex justify-center items-center">
        <form method="POST" action="{{ route('forgot.password.verify.otp') }}">
            @csrf
            <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}">
            <div class="bg-[#002b6d] rounded-3xl py-5 px-8 flex flex-col items-center shadow-md w-auto h-auto">
                <h1 class="text-white font-bold text-xl mb-1 text-center font-montserrat mt-10">VERIFICATION CODE</h1>
                <p class="text-white text-sm text-center mb-10 max-w-xs font-montserrat">
                    We have sent the OTP code to your email address.<br>
                    OTP expires in 5 minutes, after which you will need to resend a new OTP.
                </p>

                <input
                    required
                    type="text"
                    name="otp"
                    inputmode="numeric"
                    pattern="\d*"
                    maxlength="6"
                    placeholder="Enter verification code"
                    class="w-full max-w-xs py-2 px-6 rounded-full placeholder:font-semibold placeholder:text-gray-400 font-montserrat text-center focus:outline-none focus:ring-2 focus:ring-blue-400"
                />

                @if ($errors->any())
                    <div class="text-red-500 text-sm mt-3 mb-3 font-montserrat">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-4 text-xs text-white text-center font-montserrat">
                    <span id="timer">
                        Resend OTP in <span id="countdown"></span>
                    </span>
                    <a href="{{ route('otp_resend') }}"
                        id="resend-link"
                        class="font-bold hover:underline hidden text-yellow-300">
                        RESEND CODE
                    </a>
                </div>

                <button
                    class="bg-yellow-400 hover:bg-yellow-500 mb-10 text-gray-700 font-semibold rounded-full mt-6 py-2 px-14 shadow-md focus:outline-none focus:ring-4 focus:ring-blue-300"
                    type="submit">
                    NEXT
                </button>
            </div>
        </form>
    </main>

    <script>
        let countdownEl = document.getElementById("countdown");
        let resendLink = document.getElementById("resend-link");
        let timerSpan = document.getElementById("timer");
        let timerInterval;

        // Get OTP expiry from Blade (in ms)
        const expiresAt = {{ \Carbon\Carbon::parse($otpExpiresAt ?? now())->timestamp }} * 1000;
        const now = Date.now();
        let countdown = Math.floor((expiresAt - now) / 1000);
        countdown = countdown > 0 ? countdown : 0;

        function updateTimer() {
            const minutes = String(Math.floor(countdown / 60)).padStart(2, '0');
            const seconds = String(countdown % 60).padStart(2, '0');

            if (countdownEl) countdownEl.textContent = `${minutes}:${seconds}`;

            if (countdown <= 0) {
                clearInterval(timerInterval);
                resendLink.classList.remove("hidden");
                timerSpan.innerHTML = '';
                return;
            }

            countdown--;
        }

        function startCountdown() {
            clearInterval(timerInterval);
            resendLink.classList.add("hidden");
            updateTimer();
            timerInterval = setInterval(updateTimer, 1000);
        }

        startCountdown();

        resendLink.addEventListener('click', function(e) {
            e.preventDefault();

            fetch("{{ route('forgot.password.otp.resend') }}", {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ email: '{{ $email ?? old('email') }}' })
        })
        .then(response => {
            if (!response.ok) throw new Error('Resend failed');
            return response.json();
        })
        .then(data => {
            countdown = 300; // reset to 5 mins
            alert(data.message);
            startCountdown();
        })
        .catch(error => {
            console.error("Resend error:", error);
            timerSpan.innerHTML = `<span class="text-red-500">Failed to resend OTP. Try again later.</span>`;
        });
        });
    </script>

    @include('partials.loader')
</body>
</html>
