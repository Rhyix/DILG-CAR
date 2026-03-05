@extends('layout.admin')
@section('title', 'Signatories Management')

@section('content')
<main class="w-full h-full min-h-0 flex flex-col gap-4 overflow-hidden font-montserrat">
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Signatories Management</span>
        </h1>
    </section>

    <section class="flex-none mt-1 w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5">
        <form id="signatorySearchForm" action="{{ route('signatories.index') }}" method="GET" class="flex w-full flex-col gap-4">
            <div class="relative w-full">
                <label for="signatorySearchInput" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Search</label>
                <input
                    id="signatorySearchInput"
                    type="search"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Search by name, designation, office, or address"
                    autocomplete="off"
                    class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
                />
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="pointer-events-none absolute left-3 top-[39px] h-5 w-5 -translate-y-1/2 text-slate-400"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
            </div>

            <div class="grid w-full gap-2 sm:grid-cols-2 lg:grid-cols-4">
                <div class="min-w-0">
                    <select
                        id="officeFilter"
                        name="office"
                        class="w-full rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm outline-none transition focus:ring-2 focus:ring-[#0D2B70]/20">
                        <option value="">All Offices</option>
                        @foreach($offices as $officeOption)
                            <option value="{{ $officeOption }}" {{ ($office ?? '') === $officeOption ? 'selected' : '' }}>
                                {{ $officeOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-0">
                    <select
                        id="sortFilter"
                        name="sort"
                        class="w-full rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm outline-none transition focus:ring-2 focus:ring-[#0D2B70]/20">
                        <option value="latest" {{ ($sort ?? 'latest') === 'latest' ? 'selected' : '' }}>Latest</option>
                        <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Oldest</option>
                        <option value="name_asc" {{ ($sort ?? '') === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                        <option value="name_desc" {{ ($sort ?? '') === 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-2 flex justify-end">
                    <a
                        href="{{ route('signatories.create') }}"
                        class="use-loader inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-[#0D2B70] bg-white px-5 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm transition hover:bg-[#0D2B70] hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                        </svg>
                        New Signatory
                    </a>
                </div>
            </div>
        </form>
    </section>

    @if (session('success'))
        <div class="flex-none rounded-xl border border-green-300 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl bg-white">
        <div class="bg-[#0D2B70] text-white rounded-t-xl">
            <table class="min-w-[980px] w-full border-collapse table-fixed">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-[11px] font-semibold text-center w-[22%]">Name</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-center w-[19%]">Designation</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-center w-[15%]">Office</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-center w-[28%]">Office Address</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-center w-[16%]">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="flex-1 min-h-0 overflow-auto">
            <table class="min-w-[980px] w-full border-collapse table-fixed">
                <tbody class="divide-y divide-[#0D2B70]">
                    @forelse($signatories as $signatory)
                        <tr class="hover:bg-blue-50/50 text-[#0D2B70]">
                            <td class="w-[22%] px-2 py-3 text-sm text-center">
                                {{ trim($signatory->first_name . ' ' . $signatory->middle_name . ' ' . $signatory->last_name) }}
                            </td>
                            <td class="w-[19%] px-4 py-3 text-sm text-center">{{ $signatory->designation }}</td>
                            <td class="w-[15%] px-4 py-3 text-sm text-center">{{ $signatory->office }}</td>
                            <td class="w-[28%] px-4 py-3 text-sm text-center">{{ $signatory->office_address }}</td>
                            <td class="w-[16%] px-4 py-3 text-sm whitespace-nowrap">
                                <div class="flex flex-nowrap items-center justify-center gap-1 whitespace-nowrap">
                                    <a href="{{ route('signatories.show', $signatory->id) }}"
                                        class="use-loader inline-flex items-center justify-center rounded-md border border-[#0D2B70] px-1.5 py-1 text-[11px] font-bold text-[#0D2B70] transition hover:bg-[#0D2B70] hover:text-white">
                                        View
                                    </a>
                                    <a href="{{ route('signatories.edit', $signatory->id) }}"
                                        class="use-loader inline-flex items-center justify-center rounded-md border border-[#0D2B70] px-1.5 py-1 text-[11px] font-bold text-[#0D2B70] transition hover:bg-[#0D2B70] hover:text-white">
                                        Edit
                                    </a>
                                    <form action="{{ route('signatories.destroy', $signatory->id) }}" method="POST" class="inline-flex">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center rounded-md border border-red-600 px-1.5 py-1 text-[11px] font-bold text-red-600 transition hover:bg-red-600 hover:text-white"
                                            onclick="openDeleteModal(this)"
                                        >
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-center text-sm text-slate-500">
                                No signatories found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>

<x-confirm-modal
    title="Remove Signatory"
    message="Are you sure you want to remove this signatory?"
    event="open-confirm-modal"
    confirm="confirm-remove-signatory"
/>

<script>
    let deleteForm = null;

    function openDeleteModal(button) {
        deleteForm = button.closest('form');
        window.dispatchEvent(new CustomEvent('open-confirm-modal'));
    }

    document.addEventListener('confirm-remove-signatory', function () {
        if (deleteForm) {
            deleteForm.submit();
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('signatorySearchInput');
        const form = document.getElementById('signatorySearchForm');
        const officeFilter = document.getElementById('officeFilter');
        const sortFilter = document.getElementById('sortFilter');

        if (!form || !input) return;

        const submitDebounced = (typeof debounce === 'function')
            ? debounce(() => form.requestSubmit(), 300)
            : (() => {
                let t;
                return () => {
                    clearTimeout(t);
                    t = setTimeout(() => form.requestSubmit(), 300);
                };
            })();

        input.addEventListener('input', submitDebounced);
        officeFilter?.addEventListener('change', () => form.requestSubmit());
        sortFilter?.addEventListener('change', () => form.requestSubmit());
    });
</script>
@endsection
