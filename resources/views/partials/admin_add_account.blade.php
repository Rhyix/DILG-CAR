<div x-data="{ showCreateAccount: {{ $errors->any() ? 'true' : 'false' }} }" class="inline">
    <button @click="showCreateAccount = true"
        class="bg-[#C5292F] hover:bg-red-700 transition text-white font-semibold rounded-full flex items-center gap-2 px-5 py-2 select-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 stroke-[3]" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
        </svg>
        Add Account
    </button>

    <!-- Modal -->
    <div x-show="showCreateAccount"
        x-transition:enter="transition ease-out duration-300"
        x-transition:leave="transition ease-in duration-200"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        style="display: none;">
        
        <div class="bg-white p-8 rounded-2xl max-w-xl w-full shadow-2xl relative">
            <!-- Close Button -->
            <button @click="showCreateAccount = false"
                class="absolute top-4 right-4 text-red-500 text-2xl font-bold hover:text-red-700">&times;</button>

            <h2 class="text-2xl font-extrabold text-[#C5292F] text-center mb-4">Create an Admin Account</h2>

    <form action="{{ route('admin.store') }}" method="POST" class="space-y-4">
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif



    @csrf

    <div class="flex flex-col">
        <label class="font-bold text-[#002C76] text-sm mb-1">USERNAME</label>
        <input type="text" name="username" value="{{ old('username') }}" class="rounded-full border px-4 py-2 focus:outline-none" />
    </div>

    <div class="flex flex-col">
        <label class="font-bold text-[#002C76] text-sm mb-1">NAME</label>
        <input type="text" name="name" value="{{ old('name') }}" class="rounded-full border px-4 py-2 focus:outline-none" />
    </div>

    <div class="flex flex-col">
        <label class="font-bold text-[#002C76] text-sm mb-1">OFFICE</label>
        <input type="text" name="office" value="{{ old('office') }}" class="rounded-full border px-4 py-2 focus:outline-none" />
    </div>

    <div class="flex flex-col">
        <label class="font-bold text-[#002C76] text-sm mb-1">DESIGNATION</label>
        <input type="text" name="designation" value="{{ old('designation') }}" class="rounded-full border px-4 py-2 focus:outline-none" />
    </div>

    <div class="flex flex-col">
        <label class="font-bold text-[#002C76] text-sm mb-1">EMAIL</label>
        <input type="email" name="email" value="{{ old('email') }}" class="rounded-full border px-4 py-2 focus:outline-none" />
    </div>

    <div class="flex flex-col">
        <label class="font-bold text-[#002C76] text-sm mb-1">PASSWORD</label>
        <input type="password" name="password" class="rounded-full border px-4 py-2 focus:outline-none" />
    </div>

    <div>
        <label class="font-bold text-[#002C76] text-sm mb-1">ACCOUNT TYPE</label>
        <div class="flex items-center gap-6 mt-1">
            <label class="flex items-center gap-2 font-bold text-[#002C76] text-sm">
                <input type="radio" name="account_type" value="admin" class="accent-[#002C76]"
                    {{ old('account_type') === 'admin' ? 'checked' : '' }}>
                ADMIN
            </label>
            <label class="flex items-center gap-2 font-bold text-[#002C76] text-sm">
                <input type="radio" name="account_type" value="viewer" class="accent-[#002C76]"
                    {{ old('account_type') === 'viewer' ? 'checked' : '' }}>
                VIEWER
            </label>
        </div>
    </div>


    <div class="flex justify-end pt-4">
        <button type="submit"
            class="bg-[#C5292F] hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-full flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 stroke-[3]" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
            </svg>
            CREATE
        </button>
    </div>
</form>

        </div>
    </div>
</div>
