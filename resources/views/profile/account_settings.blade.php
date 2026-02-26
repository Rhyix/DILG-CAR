@extends('layout.app')
@section('title', 'Account Settings')

@section('content')
    <div class="mx-auto w-full max-w-5xl px-4 pb-8 sm:px-8">
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
            $initials = collect(explode(' ', $user->name))->map(fn($p) => mb_substr($p, 0, 1))->join('');
            $profile = $user->profile;
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

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Name</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $user->name }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Email</p>
                    <p class="mt-1 break-all text-sm font-semibold text-slate-800">{{ $user->email }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Phone</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $profile?->phone ?: 'N/A' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Address</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $profile?->address ?: 'N/A' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 sm:col-span-2">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Bio</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $profile?->bio ?: ($user->bio ?: 'N/A') }}</p>
                </div>
            </div>
        </section>

        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold text-[#0D2B70]">Edit Profile</h2>
            <p class="mt-1 text-sm text-slate-500">Update your account details.</p>

            <form method="POST" action="{{ route('profile.update') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        @error('name')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        @error('email')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $profile?->phone) }}"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        @error('phone')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Address</label>
                        <input type="text" name="address" value="{{ old('address', $profile?->address) }}"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        @error('address')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Bio</label>
                        <textarea name="bio" rows="4"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">{{ old('bio', $profile?->bio ?: $user->bio) }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-100 pt-4">
                    <button type="submit"
                        class="rounded-xl bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0A2259]">
                        Save Changes
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold text-[#0D2B70]">Reset Password</h2>
            <p class="mt-1 text-sm text-slate-500">Use a strong password with uppercase, lowercase, number, and symbol.</p>

            <form method="POST" action="{{ route('profile.password') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Current Password</label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                    @error('current_password')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">New Password</label>
                    <input type="password" name="password" required autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                    @error('password')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                </div>

                <div class="flex justify-end border-t border-slate-100 pt-4">
                    <button type="submit"
                        class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-900">
                        Update Password
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
