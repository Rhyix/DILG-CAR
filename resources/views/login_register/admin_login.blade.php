<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Access - DILG CAR Recruitment and Selection Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html,
        body {
            height: 100%;
            font-family: 'Montserrat', sans-serif;
            overflow: hidden;
        }

        .requirement-valid {
            color: #047857;
        }

        .requirement-invalid {
            color: #64748b;
        }
    </style>
</head>
@php
    $registerErrors = $errors->getBag('adminRegister');
    $openRegisterModal = $registerErrors->any() || old('auth_tab') === 'register';
@endphp
<body class="min-h-screen overflow-hidden bg-gradient-to-br from-[#071A4D] via-[#0A2566] to-[#12398B] p-4 md:p-6">
    <div class="mx-auto flex min-h-[calc(100vh-2rem)] w-full max-w-7xl items-center justify-center">
        <div class="grid w-full max-h-[calc(100vh-2rem)] overflow-hidden rounded-3xl border border-white/20 bg-white shadow-2xl lg:grid-cols-[1.15fr_1fr]">
            <section class="relative hidden overflow-hidden bg-[#0B2A71] px-8 py-10 text-white lg:block">
                <div class="absolute -left-16 -top-20 h-56 w-56 rounded-full bg-white/10 blur-xl"></div>
                <div class="absolute -bottom-20 -right-20 h-72 w-72 rounded-full bg-yellow-300/20 blur-xl"></div>

                <div class="relative z-10 flex h-full flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-14 w-14 rounded-full bg-white/10 p-1" />
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-100">DILG CAR</p>
                                <h1 class="text-2xl font-extrabold">Recruitment Portal</h1>
                            </div>
                        </div>
                        <div class="mt-10 max-w-md space-y-4">
                            <h2 class="text-3xl font-extrabold leading-tight">Access Portal</h2>
                            <p class="text-sm leading-relaxed text-blue-100">
                                Use this portal to sign in or register your employee account. New registrations are reviewed by the superadmin before role-based access is granted.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur-sm">
                        <p class="text-sm font-semibold text-yellow-200">Approval Workflow</p>
                        <ul class="mt-3 space-y-2 text-sm text-blue-50">
                            <li class="flex items-start gap-2"><i class="fa-solid fa-user-plus mt-0.5"></i><span>Register account details (no role selected).</span></li>
                            <li class="flex items-start gap-2"><i class="fa-solid fa-hourglass-half mt-0.5"></i><span>Wait for superadmin approval.</span></li>
                            <li class="flex items-start gap-2"><i class="fa-solid fa-shield-halved mt-0.5"></i><span>Access modules based on assigned role.</span></li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="relative bg-slate-50 px-5 py-8 sm:px-8 lg:px-10">
                <div class="mx-auto w-full max-w-xl">
                    <div class="mb-6 flex items-center gap-3 lg:hidden">
                        <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-12 w-12" />
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#0D2B70]">DILG CAR</p>
                            <p class="text-lg font-bold text-[#0D2B70]">Access Portal</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-5">
                            <h2 class="text-2xl font-extrabold text-[#0D2B70]">Admin Login</h2>
                            <p class="text-sm text-slate-500">Sign in to continue to your assigned dashboard.</p>
                        </div>

                        @if (session('status'))
                            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any() && !$registerErrors->any())
                            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                <ul class="list-disc pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-4" autocomplete="off">
                            @csrf
                            <input type="hidden" name="auth_tab" value="login">

                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
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
                                        id="admin-password"
                                        type="password"
                                        name="password"
                                        required
                                        class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-10 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
                                    >
                                    <button
                                        type="button"
                                        onclick="toggleAdminPassword()"
                                        class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600"
                                        tabindex="-1"
                                        aria-label="Toggle password visibility"
                                    >
                                        <i id="admin-password-icon" class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1 text-sm">
                                <button
                                    type="button"
                                    class="font-semibold text-[#0D2B70] hover:underline"
                                    onclick="document.getElementById('adminForgotModal').classList.remove('hidden')"
                                >
                                    Forgot Password?
                                </button>
                                <a href="{{ route('login.form') }}" class="font-semibold text-slate-500 hover:text-[#0D2B70] hover:underline">User Login</a>
                            </div>

                            @if(app()->environment('production'))
                                <div class="pt-1">
                                    <div class="g-recaptcha" data-sitekey="6LfpjpErAAAAADcMjUqP3AZmsMae7WvrjcA5OSvs" data-action="LOGIN"></div>
                                </div>
                            @endif

                            <button type="submit" class="w-full rounded-xl bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0A2259]">
                                Sign In
                            </button>
                        </form>

                        <div class="mt-5 border-t border-slate-200 pt-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">New Employee Account</p>
                            <button id="openRegisterModalBtn" type="button"
                                class="mt-2 w-full rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] transition hover:bg-[#0D2B70] hover:text-white">
                                Register Employee Account
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div id="adminRegisterModal"
        class="fixed inset-0 z-[12000] {{ $openRegisterModal ? 'flex' : 'hidden' }} items-center justify-center bg-slate-900/70 px-4 py-6">
        <div class="w-full max-w-4xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h3 class="text-lg font-bold text-[#0D2B70]">Employee Registration</h3>
                    <p class="text-xs text-slate-500">Role assignment is handled by superadmin after approval.</p>
                </div>
                <button id="closeRegisterModalBtn" type="button"
                    class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Close registration modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="max-h-[78vh] overflow-y-auto p-6">
                @if ($registerErrors->any())
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <ul class="list-disc pl-5">
                            @foreach ($registerErrors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="adminRegisterForm" action="{{ route('admin.register.submit') }}" method="POST" class="space-y-4" autocomplete="off">
                    @csrf
                    <input type="hidden" name="auth_tab" value="register">

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-600">Work Assignment</p>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Office</label>
                                    <input type="text" name="office" value="{{ old('office') }}" required placeholder="Enter office/unit"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Designation</label>
                                    <input type="text" name="designation" value="{{ old('designation') }}" required placeholder="Enter position/designation"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Password</label>
                            <input id="registerPassword" type="password" name="password" required minlength="8"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Confirm Password</label>
                            <input id="registerPasswordConfirm" type="password" name="password_confirmation" required minlength="8"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Password Requirements</p>
                        <ul id="passwordRequirements" class="mt-2 space-y-1 text-xs">
                            <li data-rule="length" class="requirement requirement-invalid flex items-center gap-2"><i class="fa-regular fa-circle"></i><span>At least 8 characters</span></li>
                            <li data-rule="upper" class="requirement requirement-invalid flex items-center gap-2"><i class="fa-regular fa-circle"></i><span>At least 1 uppercase letter</span></li>
                            <li data-rule="lower" class="requirement requirement-invalid flex items-center gap-2"><i class="fa-regular fa-circle"></i><span>At least 1 lowercase letter</span></li>
                            <li data-rule="number" class="requirement requirement-invalid flex items-center gap-2"><i class="fa-regular fa-circle"></i><span>At least 1 number</span></li>
                            <li data-rule="special" class="requirement requirement-invalid flex items-center gap-2"><i class="fa-regular fa-circle"></i><span>At least 1 special character</span></li>
                            <li data-rule="match" class="requirement requirement-invalid flex items-center gap-2"><i class="fa-regular fa-circle"></i><span>Password and confirm password must match</span></li>
                        </ul>
                    </div>

                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button id="cancelRegisterModalBtn" type="button"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-xl bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0A2259]">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="adminForgotModal" class="fixed inset-0 z-[11000] hidden flex items-center justify-center bg-slate-900/60 px-4 py-6">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between">
                <h3 class="text-lg font-bold text-[#0D2B70]">Password Reset</h3>
                <button type="button" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    onclick="document.getElementById('adminForgotModal').classList.add('hidden')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <p class="mt-3 text-sm text-slate-600">
                Contact the superadmin to reset your account password.
            </p>
            <button type="button" class="mt-6 w-full rounded-xl bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0A2259]"
                onclick="document.getElementById('adminForgotModal').classList.add('hidden')">
                Close
            </button>
        </div>
    </div>

    @if(app()->environment('production'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const registerModal = document.getElementById('adminRegisterModal');
            const openRegisterButton = document.getElementById('openRegisterModalBtn');
            const closeRegisterButton = document.getElementById('closeRegisterModalBtn');
            const cancelRegisterButton = document.getElementById('cancelRegisterModalBtn');

            const passwordInput = document.getElementById('registerPassword');
            const passwordConfirmInput = document.getElementById('registerPasswordConfirm');

            const openRegisterModal = () => {
                if (!registerModal) return;
                registerModal.classList.remove('hidden');
                registerModal.classList.add('flex');
            };

            const closeRegisterModal = () => {
                if (!registerModal) return;
                registerModal.classList.add('hidden');
                registerModal.classList.remove('flex');
            };

            if (openRegisterButton) {
                openRegisterButton.addEventListener('click', openRegisterModal);
            }

            if (closeRegisterButton) {
                closeRegisterButton.addEventListener('click', closeRegisterModal);
            }

            if (cancelRegisterButton) {
                cancelRegisterButton.addEventListener('click', closeRegisterModal);
            }

            if (registerModal) {
                registerModal.addEventListener('click', (event) => {
                    if (event.target === registerModal) {
                        closeRegisterModal();
                    }
                });
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && registerModal && !registerModal.classList.contains('hidden')) {
                    closeRegisterModal();
                }
            });

            const setRequirementState = (ruleElement, isValid) => {
                if (!ruleElement) return;
                const icon = ruleElement.querySelector('i');

                ruleElement.classList.toggle('requirement-valid', isValid);
                ruleElement.classList.toggle('requirement-invalid', !isValid);

                if (icon) {
                    icon.classList.toggle('fa-circle-check', isValid);
                    icon.classList.toggle('fa-circle', !isValid);
                    icon.classList.toggle('fa-regular', !isValid);
                    icon.classList.toggle('fa-solid', isValid);
                }
            };

            const updatePasswordRequirements = () => {
                const password = passwordInput ? passwordInput.value : '';
                const passwordConfirm = passwordConfirmInput ? passwordConfirmInput.value : '';

                const rules = {
                    length: password.length >= 8,
                    upper: /[A-Z]/.test(password),
                    lower: /[a-z]/.test(password),
                    number: /\d/.test(password),
                    special: /[^A-Za-z\d]/.test(password),
                    match: password.length > 0 && password === passwordConfirm,
                };

                Object.keys(rules).forEach((ruleName) => {
                    const element = document.querySelector(`[data-rule="${ruleName}"]`);
                    setRequirementState(element, rules[ruleName]);
                });
            };

            if (passwordInput) {
                passwordInput.addEventListener('input', updatePasswordRequirements);
            }

            if (passwordConfirmInput) {
                passwordConfirmInput.addEventListener('input', updatePasswordRequirements);
            }

            updatePasswordRequirements();

            const shouldOpenRegisterModal = @json($openRegisterModal);
            if (shouldOpenRegisterModal) {
                openRegisterModal();
            }
        });

        function toggleAdminPassword() {
            const input = document.getElementById('admin-password');
            const icon  = document.getElementById('admin-password-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
