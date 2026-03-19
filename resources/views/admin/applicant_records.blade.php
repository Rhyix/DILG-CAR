@extends('layout.admin')
@section('title', 'DILG - Applicant Records')

@php
    $buildApplicantName = static function ($applicant) {
        $pi = $applicant->personalInformation;

        if (!$pi) {
            return $applicant->name ?: 'N/A';
        }

        return trim(
            ($pi->first_name ?? '') . ' ' .
            ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
            ($pi->surname ?? '') . ' ' .
            ($pi->name_extension ?? '')
        ) ?: ($applicant->name ?: 'N/A');
    };
@endphp

@section('content')
<main class="mx-auto w-full">
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-4xl font-montserrat tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Applicant Records</span>
        </h1>
    </section>

    <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <form method="GET" action="{{ route('admin.applicant_records.index') }}" class="flex w-full flex-col gap-3 lg:flex-row lg:items-end">
                <div class="relative w-full lg:max-w-md">
                    <label for="applicantSearchInput" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Search
                    </label>
                    <input id="applicantSearchInput" name="search" type="search" value="{{ $search }}"
                        placeholder="Search applicant name, email, or mobile number"
                        class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                    <svg class="pointer-events-none absolute left-3 top-[39px] h-5 w-5 -translate-y-1/2 text-slate-400"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </div>

                <div class="w-full sm:max-w-[220px]">
                    <label for="applicantSort" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Sort
                    </label>
                    <select id="applicantSort" name="sort"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        <option value="latest" @selected($sort === 'latest')>Latest application</option>
                        <option value="oldest" @selected($sort === 'oldest')>Oldest application</option>
                    </select>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-[#0D2B70] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0A235C]">
                        Apply
                    </button>
                    <a href="{{ route('admin.applicant_records.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
            <p class="text-sm text-slate-600">
                Unique applicants with at least one submitted application.
            </p>
            <p class="text-sm font-semibold text-[#0D2B70]">
                Total: {{ number_format($applicants->total()) }}
            </p>
        </div>
    </section>

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Applicant</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Email</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Mobile</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">Applications</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Last Applied</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($applicants as $applicant)
                        @php
                            $personalInfo = $applicant->personalInformation;
                            $email = $personalInfo?->email_address ?: $applicant->email ?: 'N/A';
                            $mobile = $personalInfo?->mobile_no ?: $applicant->phone_number ?: 'N/A';
                            $applicantCode = $applicant->applicant_code ?: ('USER-' . $applicant->id);
                            $lastApplied = $applicant->applications_max_created_at
                                ? \Illuminate\Support\Carbon::parse($applicant->applications_max_created_at)->format('M d, Y h:i A')
                                : 'N/A';
                        @endphp
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-4 align-top">
                                <div class="font-semibold text-slate-800">{{ $buildApplicantName($applicant) }}</div>
                                <div class="mt-1 text-xs uppercase tracking-wide text-slate-500">Applicant ID: {{ $applicantCode }}</div>
                            </td>
                            <td class="px-5 py-4 align-top text-sm text-slate-700">{{ $email }}</td>
                            <td class="px-5 py-4 align-top text-sm text-slate-700">{{ $mobile }}</td>
                            <td class="px-5 py-4 align-top text-center">
                                <span class="inline-flex min-w-[42px] items-center justify-center rounded-full bg-[#0D2B70]/10 px-3 py-1 text-sm font-semibold text-[#0D2B70]">
                                    {{ $applicant->applications_count }}
                                </span>
                            </td>
                            <td class="px-5 py-4 align-top text-sm text-slate-700">{{ $lastApplied }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm font-medium text-slate-500">
                                No applicant records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($applicants->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">
                {{ $applicants->links() }}
            </div>
        @endif
    </section>
</main>
@endsection
