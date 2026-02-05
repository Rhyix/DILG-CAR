<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Verification Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .font-montserrat {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!isset($status)): ?>
    <script>
        window.location.href = "<?php echo e(route('register.form')); ?>";
    </script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<body class="bg-white h-screen flex flex-col">
    <!-- Header Bar -->
    <header class="bg-[#002b6d] flex items-center h-20 px-6 space-x-6">
        <div class="flex-shrink-0">
            <img
                src="<?php echo e(asset('images/dilg_logo.png')); ?>"
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
        <form method="POST" action="<?php echo e(route('otp_check')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="email" value="<?php echo e(old('email', $email ?? '')); ?>">
            <div class="bg-[#002b6d] rounded-3xl py-5 px-8 flex flex-col items-center shadow-md w-auto h-auto">
                <h1 class="text-white font-bold text-xl mb-1 text-center font-montserrat mt-10">VERIFICATION CODE</h1>
                <p class="text-white text-sm text-center mb-10 max-w-xs font-montserrat">
                    We have sent the OTP code to your email address.<br>
                    OTP expires in 5 minutes, after which you will need to resend a new OTP.<br>
                    Please do not reload this page as it may invalidate the OTP.
                </p>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('app.debug')): ?>
                <div class="mb-4 text-center">
                    <span class="text-yellow-300 font-montserrat text-sm font-bold">
                        Dev OTP: <?php echo e(session('pending_registration.otp')); ?>

                    </span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <input
                    required
                    type="text"
                    name="otp"
                    inputmode="numeric"
                    pattern="\d*"
                    maxlength="6"
                    placeholder="Enter verification code"
                    autocomplete="off"
                    class="w-full max-w-xs py-2 px-6 rounded-full placeholder:font-bold placeholder:text-gray-400 font-montserrat text-center focus:outline-none focus:ring-2 focus:ring-blue-400"
                />

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div class="text-red-500 text-sm mt-3 mb-3 font-montserrat">
                        <ul>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <li><?php echo e($error); ?></li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-4 text-xs text-white text-center font-montserrat">
                    <span id="timer">
                        Resend OTP in <span id="countdown"></span>
                    </span>
                    <a href="#" id="resend-link" class="font-bold hover:underline hidden text-yellow-300">
                        RESEND CODE
                    </a>
                </div>

                <button
                    class="bg-yellow-400 mb-10 hover:bg-yellow-500 text-gray-700 font-semibold rounded-full mt-6 py-2 px-14 shadow-md focus:outline-none focus:ring-4 focus:ring-blue-300"
                    type="submit">
                    NEXT
                </button>
            </div>
        </form>
    </main>

    <script>
        const countdownEl = document.getElementById("countdown");
        const resendLink = document.getElementById("resend-link");
        const timerSpan = document.getElementById("timer");
        let timerInterval;

        // OTP expiry from Blade (in ms)
        const expiresAt = <?php echo e(\Carbon\Carbon::parse(session('pending_registration.expires_at'))->timestamp); ?> * 1000;
        const now = Date.now();
        //console.log(expiresAt);
        //console.log(now);
        let countdown = Math.floor((expiresAt - now) / 1000);
        countdown = countdown > 0 ? countdown : 0;

        function updateTimer() {
            const minutes = String(Math.floor(countdown / 60)).padStart(2, '0');
            const seconds = String(countdown % 60).padStart(2, '0');

            //console.log(`${minutes}:${seconds}`);
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

            fetch("<?php echo e(route('otp_resend')); ?>", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({ email: '<?php echo e($email ?? old('email')); ?>' })
            })
            .then(response => {
                if (!response.ok) throw new Error('Resend failed');
                return response.json();
            })
            .then(data => {
                alert("New OTP sent successfully.");

                // Reset countdown to 5 minutes dynamically
                countdown = 300;
                const newExpiresAt = Date.now() + countdown * 1000;

                startCountdown();
            })
            .catch(error => {
                console.error("Resend error:", error);
                timerSpan.innerHTML = `<span class="text-red-500">Failed to resend OTP. Try again later.</span>`;
            });
        });
    </script>

    <?php echo $__env->make('partials.loader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\PDS-main\resources\views/login_register/otp.blade.php ENDPATH**/ ?>