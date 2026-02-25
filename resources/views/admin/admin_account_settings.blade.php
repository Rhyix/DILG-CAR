@extends('layout.admin')
@section('title', 'DILG - Account Settings')
@section('content')
@php
    $roleLabel = match ($admin->role ?? null) {
        'superadmin' => 'Superadmin',
        'admin' => 'Admin (HR)',
        'hr_division' => 'HR Division',
        'viewer' => 'Viewer',
        default => 'Administrator',
    };

    $rawName = trim((string) ($admin->name ?? ''));
    $nameParts = preg_split('/\s+/', $rawName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $defaultFirstName = $nameParts[0] ?? '';
    $defaultLastName = count($nameParts) > 1 ? (string) end($nameParts) : '';
    $defaultMiddleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : '';

    $displayName = trim(implode(' ', array_filter([$defaultFirstName, $defaultMiddleName, $defaultLastName])));
    if ($displayName === '') {
        $displayName = $admin->name ?? 'N/A';
    }

    $openEditModal = $errors->settingsUpdate->any();
    $openPasswordModal = $errors->passwordUpdate->any();
@endphp

<main class="mx-auto w-full max-w-[1300px] pb-8"
    x-data="{ showEditModal: {{ $openEditModal ? 'true' : 'false' }}, showPasswordModal: {{ $openPasswordModal ? 'true' : 'false' }} }">
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-4xl font-montserrat tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Account Settings</span>
        </h1>
    </section>

    @if (session('settings_success'))
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('settings_success') }}
        </div>
    @endif

    @if (session('password_success'))
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('password_success') }}
        </div>
    @endif

    <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-lg font-bold text-[#0D2B70]">Profile Details</h2>
                <p class="mt-1 text-sm text-slate-500">View your account details. Use Edit or Reset Password to make changes.</p>
            </div>
            <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                {{ $roleLabel }}
            </span>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs uppercase tracking-wide text-slate-500">Name</p>
                <p class="mt-1 text-sm font-semibold text-slate-800">{{ $displayName }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs uppercase tracking-wide text-slate-500">Email</p>
                <p class="mt-1 text-sm font-semibold text-slate-800 break-all">{{ $admin->email }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs uppercase tracking-wide text-slate-500">Status</p>
                <p class="mt-1 text-sm font-semibold {{ (int) ($admin->is_active ?? 0) === 1 ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ (int) ($admin->is_active ?? 0) === 1 ? 'Active' : 'Inactive' }}
                </p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs uppercase tracking-wide text-slate-500">Office</p>
                <p class="mt-1 text-sm font-semibold text-slate-800">{{ $admin->office ?: 'N/A' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs uppercase tracking-wide text-slate-500">Designation</p>
                <p class="mt-1 text-sm font-semibold text-slate-800">{{ $admin->designation ?: 'N/A' }}</p>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
            <button type="button" @click="showEditModal = true"
                class="rounded-xl border border-[#0D2B70] px-4 py-2 text-sm font-semibold text-[#0D2B70] transition hover:bg-[#0D2B70] hover:text-white">
                Edit
            </button>
            <button type="button" @click="showPasswordModal = true"
                class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-900">
                Reset Password
            </button>
        </div>
    </section>

    <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/50 px-4 py-8"
        style="display:none;" @keydown.escape.window="showEditModal = false" @click.self="showEditModal = false">
        <div class="mx-auto flex min-h-full w-full max-w-3xl items-center justify-center">
            <div class="w-full rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h3 class="text-xl font-bold text-[#0D2B70]">Edit Profile Details</h3>
                        <p class="text-sm text-slate-500">Update your profile and contact information.</p>
                    </div>
                    <button type="button" @click="showEditModal = false"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.account.settings.update') }}"
                    class="js-account-settings-form no-spinner space-y-5 p-6">
                    @csrf
                    @method('PUT')

                    @if ($errors->settingsUpdate->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->settingsUpdate->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $defaultFirstName) }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $defaultLastName) }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name', $defaultMiddleName) }}"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Office</label>
                            <input type="text" name="office" value="{{ old('office', $admin->office) }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Designation</label>
                            <input type="text" name="designation" value="{{ old('designation', $admin->designation) }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $admin->email) }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="button" @click="showEditModal = false"
                            class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0A2259]">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="showPasswordModal" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/50 px-4 py-8"
        style="display:none;" @keydown.escape.window="showPasswordModal = false" @click.self="showPasswordModal = false">
        <div class="mx-auto flex min-h-full w-full max-w-xl items-center justify-center">
            <div class="w-full rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h3 class="text-xl font-bold text-[#0D2B70]">Reset Password</h3>
                        <p class="text-sm text-slate-500">Set a strong password to secure your account.</p>
                    </div>
                    <button type="button" @click="showPasswordModal = false"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.account.password.update') }}"
                    class="js-account-password-form no-spinner space-y-5 p-6">
                    @csrf
                    @method('PUT')

                    @if ($errors->passwordUpdate->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->passwordUpdate->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
                        Password requirements:
                        <ul class="mt-2 space-y-1 text-xs">
                            <li id="pw-req-length" class="flex items-center gap-2 text-slate-600">
                                <span data-req-icon class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-300 text-[10px]">•</span>
                                <span>At least 8 characters</span>
                            </li>
                            <li id="pw-req-upper" class="flex items-center gap-2 text-slate-600">
                                <span data-req-icon class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-300 text-[10px]">•</span>
                                <span>At least 1 uppercase letter</span>
                            </li>
                            <li id="pw-req-lower" class="flex items-center gap-2 text-slate-600">
                                <span data-req-icon class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-300 text-[10px]">•</span>
                                <span>At least 1 lowercase letter</span>
                            </li>
                            <li id="pw-req-number" class="flex items-center gap-2 text-slate-600">
                                <span data-req-icon class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-300 text-[10px]">•</span>
                                <span>At least 1 number</span>
                            </li>
                            <li id="pw-req-special" class="flex items-center gap-2 text-slate-600">
                                <span data-req-icon class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-300 text-[10px]">•</span>
                                <span>At least 1 special character</span>
                            </li>
                            <li id="pw-req-match" class="flex items-center gap-2 text-slate-600">
                                <span data-req-icon class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-300 text-[10px]">•</span>
                                <span>New password matches confirmation</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Current Password</label>
                        <input type="password" name="current_password" required autocomplete="current-password"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">New Password</label>
                        <input type="password" name="new_password" minlength="8"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}" required autocomplete="new-password"
                            title="Use at least 8 characters with uppercase, lowercase, number, and special character."
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" minlength="8"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}" required autocomplete="new-password"
                            title="Use at least 8 characters with uppercase, lowercase, number, and special character."
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                    </div>

                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="button" @click="showPasswordModal = false"
                            class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-900">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirm-modal title="Confirm Profile Update" message="Save these profile detail changes?"
        event="open-account-settings-save-confirm" confirm="confirm-account-settings-save" />
    <x-confirm-modal title="Confirm Password Reset" message="Reset your password with the entered values?"
        event="open-account-password-reset-confirm" confirm="confirm-account-password-reset" />

    @include('partials.loader')
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let pendingConfirmationForm = null;

        const showLoaderOverlay = () => {
            const overlay = document.getElementById('loader');
            const liveRegion = document.getElementById('loader-live');
            const loaderText = document.getElementById('loader-text');
            if (overlay) {
                overlay.classList.remove('hidden');
                overlay.classList.remove('pds-loading-nonblocking');
                overlay.setAttribute('aria-busy', 'true');
            }
            if (liveRegion) liveRegion.textContent = 'Loading...';
            if (loaderText) loaderText.textContent = 'Loading...';
        };

        const submitPendingForm = () => {
            if (!pendingConfirmationForm) return;
            const form = pendingConfirmationForm;
            pendingConfirmationForm = null;
            showLoaderOverlay();
            form.submit();
        };

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            if (form.matches('.js-account-settings-form')) {
                event.preventDefault();
                pendingConfirmationForm = form;
                window.dispatchEvent(new CustomEvent('open-account-settings-save-confirm'));
                return;
            }

            if (form.matches('.js-account-password-form')) {
                event.preventDefault();
                pendingConfirmationForm = form;
                window.dispatchEvent(new CustomEvent('open-account-password-reset-confirm'));
            }
        }, true);

        window.addEventListener('confirm-account-settings-save', submitPendingForm);
        window.addEventListener('confirm-account-password-reset', submitPendingForm);

        const passwordForm = document.querySelector('.js-account-password-form');
        const newPasswordInput = passwordForm?.querySelector('input[name="new_password"]');
        const confirmPasswordInput = passwordForm?.querySelector('input[name="new_password_confirmation"]');
        const requirementNodes = {
            length: document.getElementById('pw-req-length'),
            upper: document.getElementById('pw-req-upper'),
            lower: document.getElementById('pw-req-lower'),
            number: document.getElementById('pw-req-number'),
            special: document.getElementById('pw-req-special'),
            match: document.getElementById('pw-req-match'),
        };

        const setRequirementState = (node, isValid, neutral = false) => {
            if (!node) return;
            const icon = node.querySelector('[data-req-icon]');

            node.classList.remove('text-slate-600', 'text-emerald-700', 'text-rose-700');
            if (neutral) {
                node.classList.add('text-slate-600');
            } else {
                node.classList.add(isValid ? 'text-emerald-700' : 'text-rose-700');
            }

            if (icon) {
                icon.classList.remove('border-slate-300', 'border-emerald-300', 'border-rose-300', 'bg-emerald-50', 'bg-rose-50');
                if (neutral) {
                    icon.classList.add('border-slate-300');
                    icon.textContent = '•';
                } else if (isValid) {
                    icon.classList.add('border-emerald-300', 'bg-emerald-50');
                    icon.textContent = '✓';
                } else {
                    icon.classList.add('border-rose-300', 'bg-rose-50');
                    icon.textContent = '×';
                }
            }
        };

        const runPasswordValidation = () => {
            if (!newPasswordInput || !confirmPasswordInput) return;

            const password = newPasswordInput.value || '';
            const confirmPassword = confirmPasswordInput.value || '';
            const hasPassword = password.length > 0;

            const checks = {
                length: password.length >= 8,
                upper: /[A-Z]/.test(password),
                lower: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[^A-Za-z\d]/.test(password),
            };

            setRequirementState(requirementNodes.length, checks.length, !hasPassword);
            setRequirementState(requirementNodes.upper, checks.upper, !hasPassword);
            setRequirementState(requirementNodes.lower, checks.lower, !hasPassword);
            setRequirementState(requirementNodes.number, checks.number, !hasPassword);
            setRequirementState(requirementNodes.special, checks.special, !hasPassword);

            const hasConfirm = confirmPassword.length > 0;
            const matches = hasConfirm && password === confirmPassword;
            setRequirementState(requirementNodes.match, matches, !hasConfirm && !hasPassword);
        };

        if (newPasswordInput && confirmPasswordInput) {
            newPasswordInput.addEventListener('input', runPasswordValidation);
            confirmPasswordInput.addEventListener('input', runPasswordValidation);
            runPasswordValidation();
        }
    });
</script>
@endpush
