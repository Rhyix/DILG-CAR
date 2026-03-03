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
        $galleryItems = $galleryItems ?? collect();
        $documentTypeOptions = $documentTypeOptions ?? [];
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

        @if (session('document_gallery_success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('document_gallery_success') }}
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

        @php
            $formatGalleryBytes = function ($bytes) {
                $size = (float) ($bytes ?? 0);
                if ($size < 1024) {
                    return (int) $size . ' B';
                }
                if ($size < 1048576) {
                    return number_format($size / 1024, 2) . ' KB';
                }
                return number_format($size / 1048576, 2) . ' MB';
            };
        @endphp

        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <!-- <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-[#0D2B70]">Document Gallery</h2>
                    <p class="mt-1 text-sm text-slate-500">Store documents here so you can quickly reuse them later.</p>
                </div>
                <span class="inline-flex rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                    Reusable Files
                </span>
            </div> -->

            <form method="POST" action="{{ route('profile.document_gallery.store') }}" enctype="multipart/form-data"
                class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <div class="grid gap-3 md:grid-cols-[1fr,1fr,auto]">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Document Type (Optional)</label>
                        <select name="document_type"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            <option value="" {{ old('document_type') === null || old('document_type') === '' ? 'selected' : '' }}>General / Unclassified</option>
                            @foreach ($documentTypeOptions as $docType)
                                <option value="{{ $docType }}" {{ old('document_type') === $docType ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $docType)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Upload File</label>
                        <input type="file" name="gallery_document" accept=".pdf,.jpg,.jpeg,.png" required
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="w-full rounded-xl bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0A2259] md:w-auto">
                            Save to Gallery
                        </button>
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-500">Allowed files: PDF, JPG, JPEG, PNG. Max size: 10MB.</p>
                @error('gallery_document')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
                @error('document_type')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </form>

            <div class="mt-4">
                @if ($galleryItems->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                        No saved documents yet. Upload files above to build your reusable document gallery.
                    </div>
                @else
                    <div class="grid gap-3 md:grid-cols-2">
                        @foreach ($galleryItems as $item)
                            <article class="rounded-xl border border-slate-200 bg-white p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-800">{{ $item->original_name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $item->document_type ? ucwords(str_replace('_', ' ', $item->document_type)) : 'General / Unclassified' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $formatGalleryBytes($item->file_size_8b) }} • {{ optional($item->created_at)->format('M d, Y h:i A') }}
                                        </p>
                                    </div>
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-600">
                                        {{ strtoupper(pathinfo((string) $item->original_name, PATHINFO_EXTENSION) ?: 'FILE') }}
                                    </span>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <a href="{{ route('profile.document_gallery.preview', $item->id) }}" target="_blank" rel="noopener"
                                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                        Preview
                                    </a>
                                    <a href="{{ route('profile.document_gallery.download', $item->id) }}"
                                        class="rounded-lg border border-blue-300 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                        Download
                                    </a>
                                    <form method="POST" action="{{ route('profile.document_gallery.delete', $item->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Delete this saved document?')"
                                            class="rounded-lg border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
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
