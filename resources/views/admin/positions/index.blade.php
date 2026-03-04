@extends('layout.admin')
@section('title', 'Positions')

@section('content')
<div class="h-full max-h-full max-w-6xl mx-auto font-montserrat flex flex-col gap-4 overflow-hidden">
    <div class="border-b border-[#0D2B70] pb-3 flex-none">
        <h1 class="text-3xl font-semibold text-[#0D2B70]">Positions</h1>
        <p class="text-sm text-slate-600 mt-2">
            Existing positions from created vacancies. Use <span class="font-semibold">Reuse</span> to prefill a new vacancy form.
        </p>
    </div>

    <form method="GET" action="{{ route('admin.positions.index') }}" class="max-w-md flex-none">
        <label for="positions-search" class="sr-only">Search positions</label>
        <div class="relative">
            <input
                id="positions-search"
                name="search"
                value="{{ $search }}"
                type="search"
                placeholder="Search vacancy ID, title, type, assignment"
                class="w-full border border-slate-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0D2B70]"
            >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
            </svg>
        </div>
    </form>

    <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl bg-white shadow">
        <div class="bg-[#0D2B70] text-white text-left rounded-t-xl">
            <table class="w-full border-collapse">
                <thead class="bg-[#0D2B70] text-white">
                    <tr>
                        <th class="py-4 px-6 font-semibold text-left w-[20%]">Sample Vacancy ID</th>
                        <th class="py-4 px-6 font-semibold text-left w-[28%]">Job Title</th>
                        <th class="py-4 px-6 font-semibold text-left w-[14%]">Monthly Salary</th>
                        <th class="py-4 px-6 font-semibold text-left w-[14%]">Last Used</th>
                        <th class="py-4 px-6 font-semibold text-left w-[16%]">Place of Assignment</th>
                        <th class="py-4 px-6 font-semibold text-center w-[8%]">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full text-left border-collapse">
                <tbody class="divide-y divide-[#0D2B70]">
                    @forelse($positions as $position)
                        <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                            <td class="py-4 px-6 w-[20%]">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full {{ strtoupper((string) $position->status) === 'OPEN' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    <span>{{ $position->vacancy_id }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 w-[28%]">
                                <p>{{ $position->position_title }}</p>
                                <p class="text-[#0D2B70]/70 text-[0.9rem] italic">{{ strtoupper((string) $position->vacancy_type) }}</p>
                            </td>
                            <td class="py-4 px-6 w-[14%]">
                                {{ $position->monthly_salary !== null ? 'PHP ' . number_format((float) $position->monthly_salary, 2) : 'N/A' }}
                            </td>
                            <td class="py-4 px-6 w-[14%]">
                                {{ optional($position->updated_at)->format('F j, Y') ?: 'N/A' }}
                            </td>
                            <td class="py-4 px-6 w-[16%]">{{ $position->place_of_assignment ?: 'N/A' }}</td>
                            <td class="py-4 px-6 text-center w-[8%]">
                                @php
                                    $reuseRoute = strtoupper((string) $position->vacancy_type) === 'PLANTILLA'
                                        ? route('addplantilla', ['reuse' => $position->vacancy_id])
                                        : route('addcos', ['reuse' => $position->vacancy_id]);
                                @endphp
                                <button
                                    onclick="window.location.href='{{ $reuseRoute }}'"
                                    class="use-loader text-[#0D2B70] py-1 px-3 rounded-md text-xl transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)] hover:scale-110"
                                    aria-label="Reuse Position"
                                    title="Reuse Position">
                                    <i class="fa-solid fa-copy h-10 w-10"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-gray-500 text-2xl">
                                <i data-feather="info" class="w-7 h-7 inline-block mr-2 text-gray-400"></i>
                                No positions found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
