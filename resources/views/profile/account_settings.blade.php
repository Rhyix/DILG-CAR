@extends('layout.app')
@section('title', 'Account Settings')

@section('content')
    @php
        $editErrorKeys = ['name', 'email', 'phone', 'address', 'bio'];
        $passwordErrorKeys = ['current_password', 'password', 'password_confirmation'];
        $editErrors = collect($editErrorKeys)->flatMap(fn($key) => $errors->get($key))->all();
        $passwordErrors = collect($passwordErrorKeys)->flatMap(fn($key) => $errors->get($key))->all();
        $openEditModal = !empty($editErrors);
        $openPasswordModal = !empty($passwordErrors);
    @endphp

    <main class="mx-auto w-full max-w-5xl px-4 pb-8 sm:px-8"
        x-data="{ showEditModal: {{ $openEditModal ? 'true' : 'false' }}, showPasswordModal: {{ $openPasswordModal ? 'true' : 'false' }} }">
        <section class="mb-4 flex items-center space-x-4">
            <h1 class="flex w-full items-center gap-3 border-b border-[#0D2B70] pb-2 text-3xl font-montserrat font-bold tracking-wide text-[#0D2B70]">
                Account Settings
            </h1>
        </section>

        @if (session('settings_success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('settings_success') }}
            </div>
        @endif

        @if (session('password_success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('password_success') }}
            </div>
        @endif

        @php
            $avatar = $user->avatar_path ? asset('storage/' . $user->avatar_path) : null;
            $profile = $user->profile;
            $personalInfo = $personalInfo ?? $user->personalInformation;
            $isGoogleSignup = $isGoogleSignup ?? false;

            $hasPdsProfile = $personalInfo && collect([
                $personalInfo->first_name,
                $personalInfo->surname,
                $personalInfo->email_address,
                $personalInfo->mobile_no,
                $personalInfo->telephone_no,
            ])->filter(fn($value) => filled($value))->isNotEmpty();

            $usePdsProfile = $hasPdsProfile;
            $allowAccountFallback = !$usePdsProfile && !$isGoogleSignup;

            $middleInitial = filled($personalInfo?->middle_name)
                ? mb_substr(trim($personalInfo->middle_name), 0, 1) . '.'
                : '';
            $pdsNameParts = array_filter([
                trim($personalInfo?->first_name ?? ''),
                $middleInitial,
                trim($personalInfo?->surname ?? ''),
                trim($personalInfo?->name_extension ?? ''),
            ], fn($part) => $part !== '');
            $pdsName = $pdsNameParts ? trim(implode(' ', $pdsNameParts)) : null;

            $displayName = $usePdsProfile ? ($pdsName ?: 'N/A') : ($allowAccountFallback ? ($user->name ?: 'N/A') : 'N/A');
            $displayEmail = $usePdsProfile ? ($personalInfo?->email_address ?: 'N/A') : ($allowAccountFallback ? ($user->email ?: 'N/A') : 'N/A');

            $pdsPhone = $personalInfo?->mobile_no ?: $personalInfo?->telephone_no;
            $displayPhone = $usePdsProfile ? ($pdsPhone ?: 'N/A') : ($allowAccountFallback ? ($profile?->phone ?: 'N/A') : 'N/A');

            $initialsSource = $displayName !== 'N/A' ? $displayName : 'N A';
            $initials = collect(preg_split('/\s+/', trim($initialsSource)))
                ->filter()
                ->map(fn($p) => mb_substr($p, 0, 1))
                ->join('');
            $initials = $initials !== '' ? $initials : 'NA';
        @endphp

        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-[#0D2B70]">Profile Details</h2>
                    <p class="mt-1 text-sm text-slate-500">Manage your account profile and avatar.</p>
                </div>
                <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    Applicant
                </span>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-[auto,1fr]">
                <div class="flex items-center gap-3">
                    @if ($avatar)
                        <img src="{{ $avatar }}" alt="Avatar" class="h-16 w-16 rounded-full object-cover">
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">
                            {{ $initials }}
                        </div>
                    @endif
                </div>
                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Upload Avatar</label>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="file" name="avatar" accept="image/png,image/jpeg"
                            class="w-full max-w-sm rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700">
                        <button type="submit"
                            class="rounded-xl bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0A2259]">
                            Upload
                        </button>
                    </div>
                    @error('avatar')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </form>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Name</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $displayName }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Email</p>
                    <p class="mt-1 break-all text-sm font-semibold text-slate-800">{{ $displayEmail }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Phone</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $displayPhone }}</p>
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
                            <p class="text-sm text-slate-500">Update your account details.</p>
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

                    <form method="POST" action="{{ route('profile.update') }}" class="space-y-5 p-6">
                        @csrf

                        @if (!empty($editErrors))
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                                <ul class="list-disc pl-5">
                                    @foreach ($editErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Name</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $profile?->phone) }}"
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
                            <p class="text-sm text-slate-500">Use a strong password with uppercase, lowercase, number, and symbol.</p>
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

                    <form method="POST" action="{{ route('profile.password') }}" class="space-y-5 p-6">
                        @csrf

                        @if (!empty($passwordErrors))
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                                <ul class="list-disc pl-5">
                                    @foreach ($passwordErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Current Password</label>
                            <input type="password" name="current_password" required autocomplete="current-password"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">New Password</label>
                            <input type="password" name="password" required autocomplete="new-password"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Confirm New Password</label>
                            <input type="password" name="password_confirmation" required autocomplete="new-password"
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
    </main>
@endsection
