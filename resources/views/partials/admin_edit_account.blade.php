@props(['admin'])
<meta name="csrf-token" content="{{ csrf_token() }}">

<div x-data="{ 
        showEditAccount: false, 
        forceShowOnError: {{ session('_editing') == $admin->id ? 'true' : 'false' }} 
    }" class="inline">

    <button @click="showEditAccount = true" aria-label="Edit" title="Edit"
        class="stroke-[#0D2B70] hover:scale-110 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" />
        </svg>
    </button>

    <!-- Modal always rendered -->
    <div x-show="showEditAccount || forceShowOnError"
        x-transition:enter="transition ease-out duration-300"
        x-transition:leave="transition ease-in duration-200"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        style="display: none;">

        <div class="bg-white p-8 rounded-2xl max-w-xl w-full shadow-2xl relative">
            <!-- Close Button -->
            <button @click="showEditAccount = false; forceShowOnError = false"
                class="absolute top-4 right-4 text-red-500 text-2xl font-bold hover:text-red-700">&times;</button>

            <h2 class="text-2xl font-extrabold text-[#0D2B70] text-center mb-4">Edit Admin Account</h2>

            <form class="space-y-4" method="POST" action="{{ route('admin.update', $admin->id) }}">
                @csrf
                @method('PUT')

                <input type="hidden" name="_editing" value="{{ $admin->id }}">

                @if ($errors->hasBag('edit_'.$admin->id))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->getBag('edit_'.$admin->id)->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- USERNAME -->
                <div class="flex flex-col">
                    <label class="font-bold text-[#002C76] text-sm mb-1">USERNAME</label>
                    <input type="text" name="username"
                        value="{{ session('_editing') == $admin->id ? old('username', $admin->username) : $admin->username }}"
                        class="rounded-full border px-4 py-2 focus:outline-none" />
                </div>

                <!-- NAME -->
                <div class="flex flex-col">
                    <label class="font-bold text-[#002C76] text-sm mb-1">NAME</label>
                    <input type="text" name="name"
                        value="{{ session('_editing') == $admin->id ? old('name', $admin->name) : $admin->name }}"
                        class="rounded-full border px-4 py-2 focus:outline-none" />
                </div>

                <!-- OFFICE -->
                <div class="flex flex-col">
                    <label class="font-bold text-[#002C76] text-sm mb-1">OFFICE</label>
                    <input type="text" name="office"
                        value="{{ session('_editing') == $admin->id ? old('office', $admin->office) : $admin->office }}"
                        class="rounded-full border px-4 py-2 focus:outline-none" />
                </div>

                <!-- DESIGNATION -->
                <div class="flex flex-col">
                    <label class="font-bold text-[#002C76] text-sm mb-1">DESIGNATION</label>
                    <input type="text" name="designation"
                        value="{{ session('_editing') == $admin->id ? old('designation', $admin->designation) : $admin->designation }}"
                        class="rounded-full border px-4 py-2 focus:outline-none" />
                </div>

                <!-- EMAIL -->
                <div class="flex flex-col">
                    <label class="font-bold text-[#002C76] text-sm mb-1">EMAIL</label>
                    <input type="email" name="email"
                        value="{{ session('_editing') == $admin->id ? old('email', $admin->email) : $admin->email }}"
                        class="rounded-full border px-4 py-2 focus:outline-none" />
                </div>

                <!-- PASSWORD -->
                <div class="flex flex-col">
                    <label class="font-bold text-[#002C76] text-sm mb-1">PASSWORD</label>
                    <input type="password" name="password" class="rounded-full border px-4 py-2 focus:outline-none" />
                </div>

                <!-- ACCOUNT TYPE -->
                <div>
                    <label class="font-bold text-[#002C76] text-sm mb-1">ACCOUNT TYPE</label>
                    <div class="flex items-center gap-6 mt-1">
                        <label class="flex items-center gap-2 font-bold text-[#002C76] text-sm">
                            <input type="radio" name="account_type" value="admin" class="accent-[#002C76]"
                                {{ (session('_editing') == $admin->id ? old('account_type', $admin->role) : $admin->role) === 'admin' ? 'checked' : '' }}>
                            ADMIN
                        </label>
                        <label class="flex items-center gap-2 font-bold text-[#002C76] text-sm">
                            <input type="radio" name="account_type" value="viewer" class="accent-[#002C76]"
                                {{ (session('_editing') == $admin->id ? old('account_type', $admin->role) : $admin->role) === 'viewer' ? 'checked' : '' }}>
                            VIEWER
                        </label>
                    </div>
                    <p class="text-xs mt-1 text-gray-600">
                        <span class="text-[#002C76] font-semibold">Admin:</span> Gains full access of Admin Tools<br>
                        <span class="text-[#002C76] font-semibold">Viewer:</span> Can view the Examination blah blah
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4">
                    <button type="submit"
                        class="use-loader bg-[#C5292F] hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-full flex items-center gap-2">
                        UPDATE
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
