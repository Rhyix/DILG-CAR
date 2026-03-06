<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Verification Code - DILG CAR Recruitment and Selection Portal</title>
    
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
    @include('partials.global_toast')
    
    <style>
        body { font-family: 'Montserrat', sans-serif; }
    </style>
</head>

@if (!isset($status))
    <script>
        window.location.href = "{{ route('register.form') }}";
    </script>
@endif

<body class="min-h-screen bg-gradient-to-br from-blue-800 via-blue-700 to-blue-900 flex items-center justify-center p-4">

    <!-- Main Container (exactly like login page) -->
    <div class="w-full max-w-6xl bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden">
        <div class="flex flex-col lg:flex-row">

            <!-- Left: OTP Form (styled exactly like login form) -->
            <div class="flex-1 p-8 lg:p-12">
                <div class="max-w-md mx-auto">
                    <div class="flex items-center justify-center gap-3 mb-6">
                        <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="w-12 h-12">
                        <div class="text-center">
                            <h2 class="text-3xl font-extrabold text-blue-900 tracking-tight">VERIFICATION CODE</h2>
                            <p class="text-blue-800 font-semibold">Enter the OTP sent to your email</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('otp_check') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}">

                        <!-- Info Message (styled to match login page aesthetic) -->
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center mb-2">
                            <p class="text-blue-800 text-sm">
                                We have sent the OTP code to your email address.<br>
                                <span class="font-semibold">OTP expires in 5 minutes</span>, after which you will need to resend a new OTP.<br>
                                Please do not reload this page as it may invalidate the OTP.
                            </p>
                        </div>

                        <!-- OTP Input (styled exactly like login email field) -->
                        <div class="relative">
                            <span class="absolute inset-y-0 left-4 flex items-center">
                                <i class="fas fa-lock text-yellow-400"></i>
                            </span>
                            <input
                                required
                                type="text"
                                name="otp"
                                inputmode="numeric"
                                pattern="\d*"
                                maxlength="6"
                                placeholder="Enter verification code"
                                autocomplete="off"
                                class="w-full bg-white border border-blue-400 rounded-full pl-12 pr-4 py-3 outline-none text-blue-900 placeholder:text-blue-800/60 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-center tracking-widest text-lg"
                            />
                        </div>

                        @if ($errors->any())
                            <div class="bg-red-50 border border-red-200 rounded-xl p-3">
                                <ul class="text-red-600 text-sm list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Timer and Resend (styled to match login page) -->
                        <div class="flex flex-col items-center mt-2 text-sm text-blue-800">
                            <div id="timer" class="mb-2">
                                Resend OTP in <span id="countdown" class="font-bold"></span>
                            </div>
                            <a href="#" id="resend-link" class="font-bold text-blue-800 hover:text-blue-900 hover:underline hidden">
                                RESEND CODE
                            </a>
                        </div>

                        <!-- NEXT Button (styled exactly like login button) -->
                        <button
                            type="submit"
                            class="w-full bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold py-3 rounded-full shadow-md transition transform hover:scale-[1.02] active:scale-[0.98]">
                            NEXT
                        </button>

                        <!-- Back to Login Link (styled like register link in login page) -->
                        <p class="text-center text-sm text-blue-800">
                            <a href="{{ route('login') }}" class="use-loader font-bold hover:underline">← BACK TO LOGIN</a>
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

    <script>
        const countdownEl = document.getElementById("countdown");
        const resendLink = document.getElementById("resend-link");
        const timerSpan = document.getElementById("timer");
        let timerInterval;

        // OTP expiry from Blade (in ms)
        const expiresAt = {{ \Carbon\Carbon::parse(session('pending_registration.expires_at'))->timestamp }} * 1000;
        const now = Date.now();
        let countdown = Math.floor((expiresAt - now) / 1000);
        countdown = countdown > 0 ? countdown : 0;

        function updateTimer() {
            const minutes = String(Math.floor(countdown / 60)).padStart(2, '0');
            const seconds = String(countdown % 60).padStart(2, '0');

            countdownEl.textContent = `${minutes}:${seconds}`;

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

            fetch("{{ route('otp_resend') }}", {
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
                alert("New OTP sent successfully.");
                countdown = 300;
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
