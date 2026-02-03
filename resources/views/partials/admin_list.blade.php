@if ($admins->isEmpty())
    <div class="flex justify-center items-center min-h-[300px]">
    <div class="text-center text-gray-500 font-semibold text-lg">
        <i data-feather="info" class="w-6 h-6 inline-block mr-2 text-gray-400"></i>
        No admin account found.
    </div>
</div>

@else
    @foreach ($admins as $admin)
        <div class="grid grid-cols-[1.2fr_2fr_1fr_1.5fr_1fr] gap-4 border-2 border-[#0D2B70] rounded-xl py-5 px-6">
            <div class="font-extrabold">{{ $admin->username }}</div>

            <div class="font-extrabold overflow-hidden text-ellipsis whitespace-nowrap">
                {{ $admin->email }}
            </div>

            <div class="text-center font-semibold">
                {{ ucfirst($admin->role) }}
            </div>

            <div class="font-extrabold text-center">
                {{ $admin->is_active ? 'Active' : 'Inactive' }}
            </div>

            <div class="flex justify-center items-center gap-3">
                <form method="POST" action="{{ route($admin->is_active ? 'admin.deactivate' : 'admin.activate', $admin->id) }}">
                    @csrf
                    <button type="submit"
                        class="w-[130px] 
                               {{ $admin->is_active ? 'bg-[#C5292F] hover:bg-red-700' : 'bg-[#00127.0.0.1] hover:bg-green-700' }} 
                               text-white font-semibold rounded-full flex items-center justify-center gap-2 px-5 py-2 transition">
                        {{ $admin->is_active ? 'DEACTIVATE' : 'ACTIVATE' }}
                    </button>
                </form>

                @include('partials.admin_edit_account', ['admin' => $admin])
            </div>
        </div>
    @endforeach
@endif
