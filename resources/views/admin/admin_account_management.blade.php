@extends('layout.admin')
@section('title', 'DILG - Admin Account Management')
@section('content')

    <main class="w-full space-y-6">

        <!-- Header with back arrow and title -->
        <section class="flex items-center space-x-4 mb-4">
            <h1
                class="flex items-center gap-3 w-full bg-[#0D2B70] text-white rounded-xl text-2xl font-extrabold font-montserrat px-8 py-4 tracking-wide select-none">
                <!-- Bootstrap Person icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                    class="admin_manage" viewBox="0 0 512 512">
                        <path d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2c-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7L336 192c-8.8 0-16-7.2-16-16l0-57.4c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0C263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5zM80 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/>
                </svg>
                <span class="whitespace-nowrap">ADMIN ACCOUNT MANAGEMENT</span>
            </h1>

        </section>

        <!-- Search and Add New Vacancy button -->
        <section class="flex justify-between items-center">
           <form class="relative w-full max-w-xs" onsubmit="return false;">
                <input id="adminSearchInput" type="search" placeholder="Search admin..."
                    class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1"
                    oninput="fetchAdminsDebounced()" />
                <svg class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
            </form>

           @include('partials.admin_add_account')
        </section>

        <!-- Table Header -->
        <section
            class="grid grid-cols-[1.2fr_2fr_1fr_1.5fr_1fr] gap-4 bg-[#0D2B70] text-white font-bold rounded-xl py-5 px-6 select-none overflow-hidden">
            <div class="flex items-center justify-start">USERNAME</div>
            <div class="flex items-center justify-start">EMAIL ADDRESS</div>
            <div class="flex items-center justify-center">ACCOUNT TYPE</div>
            <div class="flex items-center justify-center">ACCOUNT STATUS</div>
            <div></div>
            <div></div>
        </section>



        <!-- Table Rows -->
 <section class="space-y-4">
    @foreach ($admins as $admin)
        <div class="grid grid-cols-[1.2fr_2fr_1fr_1.5fr_1fr] gap-4 border-2 border-[#0D2B70] rounded-xl py-5 px-6">
            <div class="font-extrabold">{{ $admin->username }}</div>
            <div class="font-extrabold overflow-hidden text-ellipsis whitespace-nowrap">
                {{ $admin->email }}
            </div>
            <div class="text-center font-semibold">{{ ucfirst($admin->role) }}</div>
            <div class="font-extrabold text-center">
                {{ $admin->is_active ? 'Active' : 'Inactive' }}
            </div>

            <div class="flex justify-center items-center gap-3">
                <form method="POST" action="{{ route($admin->is_active ? 'admin.deactivate' : 'admin.activate', $admin->id) }}">
    @csrf
    <button type="submit"
        class="w-[130px] 
               {{ $admin->is_active ? 'bg-[#C5292F] hover:bg-red-700' : 'bg-[#008000] hover:bg-green-700' }} 
               text-white font-semibold rounded-full flex items-center justify-center gap-2 px-5 py-2 transition">
        {{ $admin->is_active ? 'DEACTIVATE' : 'ACTIVATE' }}
    </button>
</form>


                @include('partials.admin_edit_account', ['admin' => $admin])
            </div>
        </div>
    @endforeach
</section>

@if (session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Success!</strong>
        <p class="text-sm">{{ session('success') }}</p>
    </div>
@endif

@if (session('error'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Error:</strong>
        <ul class="mt-1 list-disc list-inside text-sm">
            @if(is_array(session('error')))
                @foreach (session('error') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            @else
                <li>{{ session('error') }}</li>
            @endif
        </ul>
    </div>
@endif


    </div>

    @include('partials.loader')
    </main>

@endsection
