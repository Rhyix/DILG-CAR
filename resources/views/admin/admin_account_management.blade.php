@extends('layout.admin')
@section('title', 'DILG - Admin Account Management')
@section('content')

<main class="w-full h-[calc(100vh-6rem)] flex flex-col space-y-4 overflow-hidden">

    <!-- Header with back arrow and title -->
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Manage Users</span>
        </h1>
    </section>

    <!-- Search and Add New Vacancy button -->
    <section class="flex-none flex justify-between items-center">
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

    <!-- Table Container -->
    <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
        <div class="flex-1 overflow-auto">
            <table class="w-full text-left border-collapse table-fixed">
                <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                    <tr>
                        <th class="py-4 px-6 font-semibold w-[20%]">Username</th>
                        <th class="py-4 px-6 font-semibold w-[30%]">Email Address</th>
                        <th class="py-4 px-6 font-semibold text-center w-[15%]">Account Type</th>
                        <th class="py-4 px-6 font-semibold text-center w-[15%]">Account Status</th>
                        <th class="py-4 px-6 font-semibold text-center w-[20%]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#0D2B70]">
                    <!-- Admin Users -->
                    @foreach ($admins as $admin)
                    <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                        <!-- Username -->
                        <td class="py-4 px-6 font-normal w-[20%]">{{ $admin->username }}</td>
                        
                        <!-- Email -->
                        <td class="py-4 px-6 font-normal overflow-hidden text-ellipsis whitespace-nowrap w-[30%]">
                            {{ $admin->email }}
                        </td>
                        
                        <!-- Role -->
                        <td class="py-4 px-6 text-center font-semibold w-[15%]">
                            {{ ucfirst($admin->role) }}
                        </td>
                        
                        <!-- Status -->
                        <td class="py-4 px-6 text-center w-[15%]">
                            <span class="px-3 py-1 rounded-full border-2
                                {{ $admin->is_active ? 'bg-green-200 border-green-700 text-green-700' : 'bg-red-200 border-red-700 text-red-700' }}">
                                {{ $admin->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-6 text-center w-[20%]">
                            <div class="flex justify-center items-center gap-3">
                                <form method="POST" action="{{ route($admin->is_active ? 'admin.deactivate' : 'admin.activate', $admin->id) }}">
                                    @csrf
                                    <button type="submit"
                                        title="{{ $admin->is_active ? 'Deactivate Admin' : 'Activate Admin' }}"
                                        class="use-loader 
                                        {{ $admin->is_active 
                                            ? 'text-[#C5292F]' 
                                            : 'text-green-600 border border-green-600 hover:bg-green-600' }} 
                                        py-1 px-3 rounded-md text-sm
                                        transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)]
                                        hover:scale-110">

                                        <i data-feather="{{ $admin->is_active ? 'user-x' : 'user-check' }}" class="w-5 h-5"></i>
                                    </button>
                                </form>

                                @include('partials.admin_edit_account', ['admin' => $admin])
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    <!-- Regular Users -->
                    @foreach ($users as $user)
                    <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                        <!-- Username -->
                        <td class="py-4 px-6 font-normal w-[20%]">
                             {{ $user->last_name }}, {{ $user->first_name }}
                        </td>
                        
                        <!-- Email -->
                        <td class="py-4 px-6 font-normal overflow-hidden text-ellipsis whitespace-nowrap w-[30%]">
                            {{ $user->email }}
                        </td>
                        
                        <!-- Role -->
                        <td class="py-4 px-6 text-center font-semibold w-[15%]">
                            Applicant
                        </td>
                        
                        <!-- Status -->
                        <td class="py-4 px-6 text-center font-bold w-[15%]">
                             @if($user->email_verified_at)
                                <span class="text-green-600">Verified</span>
                             @else
                                <span class="text-red-600">Unverified</span>
                             @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-6 text-center w-[20%]">
                            <div class="flex justify-center items-center gap-3">
                                <span class="text-gray-400 text-sm italic">No actions available</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

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

    @include('partials.loader')
</main>
@endsection
