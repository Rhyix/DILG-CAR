<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    @include('partials.global_toast')
</head>
<style>
    .font-monserrat {
          font-family: 'Montserrat', sans-serif;
      }
  </style>
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
            <span class="font-bold text-sm font-monserrat">DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT</span>
            <span class="text-xs opacity-70 font-monserrat">CORDILLERA ADMINISTRATIVE REGION</span>
            <span class="font-bold text-yellow-400 text-xs font-monserrat">
                RECRUITMENT SELECTION AND PLACEMENT PORTAL
            </span>
        </div>
    </header>

    <!-- Main content -->
    <main class="flex-grow flex justify-center items-center">
        <form method="POST" action="{{ route('forgot.password.send.otp') }}">
        @csrf
        <div class="bg-[#002b6d] rounded-3xl py-5 px-8 flex flex-col items-center shadow-md w-auto h-auto">
            <h1 class="text-white font-bold text-xl mb-1 text-center font-monserrat mt-10">FORGOT PASSWORD</h1>
            <p class="text-white text-sm text-center mb-10 max-w-xs font-monserrat">
                Please enter your email address that is registered with your account. We will send you a verification code to reset your password.
            </p>
            <input
                required
                type="text"
                name="email"
                inputmode="email"
                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                maxlength="255"
                placeholder="Enter your email"
                class="w-full max-w-xs py-2 px-6 rounded-full placeholder:font-semibold placeholder:text-gray-400 font-monserrat text-center focus:outline-none focus:ring-2 focus:ring-blue-400"
            />
                @if ($errors->any())
        <div class="text-red-500 text-sm mt-3 mb-3 font-monserrat">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

            <button
                class="bg-yellow-400 hover:bg-yellow-500 mb-10 text-gray-700 font-semibold rounded-full mt-6 py-2 px-14 shadow-md focus:outline-none focus:ring-4 focus:ring-blue-300"
                type="submit"
                >
                SEND OTP
            </button>
        </div>
            </ul>

        </form>
    </main>
  
      <script>
 let countdown = 300; // default 5 mins
let countdownEl = document.getElementById("countdown");
let resendLink = document.getElementById("resend-link");
let timerSpan = document.getElementById("timer");
let timerInterval;

// Calculate remaining time from session
const expiresAt = {{ \Carbon\Carbon::parse(session('pending_registration.expires_at'))->timestamp }} * 1000;
const now = Date.now();
const timeLeft = Math.floor((expiresAt - now) / 1000);
countdown = timeLeft > 0 ? timeLeft : 0;

// Format and display countdown
function updateTimer() {
    const minutes = String(Math.floor(countdown / 60)).padStart(2, '0');
    const seconds = String(countdown % 60).padStart(2, '0');

    if (countdownEl) countdownEl.textContent = `${minutes}:${seconds}`;

    if (countdown <= 0) {
        clearInterval(timerInterval);
        resendLink.classList.remove("hidden");
        if (timerSpan) timerSpan.innerHTML = '';
        return;
    }

    countdown--;
}

// Start new countdown cycle
function startCountdown() {
    clearInterval(timerInterval);
    resendLink.classList.add("hidden");

    const minutes = String(Math.floor(countdown / 60)).padStart(2, '0');
    const seconds = String(countdown % 60).padStart(2, '0');

    timerSpan.innerHTML = `Resend OTP in <span id="countdown"><strong>${minutes}:${seconds}</strong></span>`;
    countdownEl = document.getElementById("countdown");

    timerInterval = setInterval(updateTimer, 1000);
}

startCountdown();

// Handle resend link click
resendLink.addEventListener('click', function(e) {
    e.preventDefault();

    fetch("{{ route('otp_resend') }}", {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Resend failed');
        return response.json();
    })
    .then(data => {
        // Reset countdown to 5 minutes
        countdown = 300;
        resendLink.classList.add("hidden");
        timerSpan.innerHTML = `Resend OTP in <span id="countdown"><strong>05:00</strong></span>`;
        countdownEl = document.getElementById("countdown");
        startCountdown();

        showAppToast("New OTP sent successfully.");
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

