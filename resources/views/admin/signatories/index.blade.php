@extends('layout.admin')

@section('content')
<!-- <div class="container mx-auto px-4 py-8"> -->
<div class="w-full space-y-6 font-montserrat" x-data="logTable()">
    
    <!-- <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-[#002C76]">Signatories Management</h1>
        <a href="{{ route('signatories.create') }}" class="bg-[#002C76] text-white px-6 py-2 rounded-lg hover:bg-blue-900 transition-colors">
            + Add Signatory
        </a>
    </div> -->

        <!-- Header Section -->
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Signatories Management</span>
        </h1>
    </section>
        <div class="grid grid-cols-2 items-center w-full justify-between h-full">
            <div>
                <form action="{{ route('signatories.index') }}" method="GET" class="flex gap-2">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by name, designation, or office..." 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002C76] focus:border-transparent">
                    <button type="submit" class="border border-[#002C76] text-[#002C76] px-6 py-2 rounded-lg hover:bg-blue-900 transition">
                        Search
                    </button>
                    @if ($search)
                        <a href="{{ route('signatories.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('signatories.create') }}" class="bg-[#002C76] text-white px-6 py-2 rounded-lg hover:bg-blue-900 transition">
                    + Signatory
                </a>
            </div>
        </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-[#002C76] text-white">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Designation</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Office</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Office Address</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($signatories as $signatory)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $signatory->first_name }} {{ $signatory->middle_name }} {{ $signatory->last_name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $signatory->designation }}</td>
                        <td class="px-6 py-4 text-sm">{{ $signatory->office }}</td>
                        <td class="px-6 py-4 text-sm">{{ $signatory->office_address }}</td>
                        <td class="px-6 py-4 text-center text-sm space-x-2">
                            <a href="{{ route('signatories.show', $signatory->id) }}" class="text-blue-600 hover:underline">View</a>
                            <a href="{{ route('signatories.edit', $signatory->id) }}" class="text-green-600 hover:underline">Edit</a>
                            <form action="{{ route('signatories.destroy', $signatory->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Are you sure?')">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">No signatories added yet</td>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            <a href="{{ route('signatories.create') }}" class="text-[#002C76] hover:underline">
                                Create one
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
