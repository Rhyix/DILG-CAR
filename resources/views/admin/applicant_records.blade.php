@extends('layout.admin')
@section('title', 'DILG - Applicant Records')

@section('content')
<main class="mx-auto w-full">
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-4xl font-montserrat tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Applicant Records</span>
        </h1>
    </section>

    <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <form id="applicantRecordsForm" method="GET" action="{{ route('admin.applicant_records.index') }}" class="flex w-full flex-col gap-3 lg:flex-row lg:items-end">
                <div class="relative w-full lg:max-w-md">
                    <label for="applicantSearchInput" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Search
                    </label>
                    <input id="applicantSearchInput" name="search" type="search" value="{{ $search }}"
                        placeholder="Search applicant name, email, or mobile number"
                        class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-11 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                    <svg class="pointer-events-none absolute left-3 top-[39px] h-5 w-5 -translate-y-1/2 text-slate-400"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                    <div id="applicantSearchSpinner" class="pointer-events-none absolute right-3 top-[39px] hidden -translate-y-1/2 text-[#0D2B70]">
                        <div class="h-4 w-4 animate-spin rounded-full border-2 border-[#0D2B70]/20 border-t-[#0D2B70]"></div>
                    </div>
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
                    <a id="applicantResetBtn" href="{{ route('admin.applicant_records.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Reset
                    </a>
                </div>
            </form>

            <p id="applicantRecordsTotal" class="shrink-0 text-sm font-semibold text-[#0D2B70] xl:pb-2">
                Total: {{ number_format($applicants->total()) }}
            </p>
        </div>
    </section>

    <div id="applicantRecordsResultsWrapper" class="relative mt-4">
        <div id="applicantRecordsLoading" class="pointer-events-none absolute inset-0 z-10 hidden rounded-2xl bg-white/70 backdrop-blur-[1px]">
            <div class="flex h-full items-center justify-center">
                <div class="flex items-center gap-3 rounded-full border border-slate-200 bg-white px-4 py-2 shadow-sm">
                    <div class="h-5 w-5 animate-spin rounded-full border-2 border-[#0D2B70]/20 border-t-[#0D2B70]"></div>
                    <span class="text-sm font-semibold text-[#0D2B70]">Loading applicant records...</span>
                </div>
            </div>
        </div>

        <div id="applicantRecordsResults">
            @include('partials.applicant_records_results', ['applicants' => $applicants])
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('applicantRecordsForm');
        const searchInput = document.getElementById('applicantSearchInput');
        const sortSelect = document.getElementById('applicantSort');
        const resetBtn = document.getElementById('applicantResetBtn');
        const resultsContainer = document.getElementById('applicantRecordsResults');
        const loadingOverlay = document.getElementById('applicantRecordsLoading');
        const searchSpinner = document.getElementById('applicantSearchSpinner');
        const totalLabel = document.getElementById('applicantRecordsTotal');
        const baseUrl = @json(route('admin.applicant_records.index'));

        let debounceTimer = null;
        let activeController = null;
        let latestRequestId = 0;

        const errorState = `
            <section class="overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-sm">
                <div class="px-5 py-10 text-center text-sm font-medium text-rose-600">
                    Unable to load applicant records. Please try again.
                </div>
            </section>
        `;

        const setLoading = (isLoading) => {
            loadingOverlay.classList.toggle('hidden', !isLoading);
            searchSpinner.classList.toggle('hidden', !isLoading);
        };

        const syncTotalLabel = () => {
            const totalSource = resultsContainer.querySelector('[data-total]');
            if (!totalSource || !totalLabel) return;
            totalLabel.textContent = `Total: ${totalSource.dataset.total ?? '0'}`;
        };

        const buildUrl = () => {
            const params = new URLSearchParams();
            const search = searchInput.value.trim();
            const sort = sortSelect.value.trim();

            if (search !== '') params.set('search', search);
            if (sort !== '') params.set('sort', sort);

            const query = params.toString();
            return query ? `${baseUrl}?${query}` : baseUrl;
        };

        const fetchResults = async (url = null) => {
            if (activeController) activeController.abort();
            activeController = new AbortController();
            const requestId = ++latestRequestId;
            const targetUrl = url ?? buildUrl();

            setLoading(true);

            try {
                const response = await fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: activeController.signal
                });

                if (!response.ok) {
                    throw new Error(`Request failed with status ${response.status}`);
                }

                const html = await response.text();
                if (requestId !== latestRequestId) return;

                resultsContainer.innerHTML = html;
                syncTotalLabel();
                window.history.replaceState({}, '', targetUrl);
            } catch (error) {
                if (error.name === 'AbortError') return;
                resultsContainer.innerHTML = errorState;
            } finally {
                if (requestId === latestRequestId) {
                    setLoading(false);
                }
            }
        };

        const debouncedFetch = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchResults(), 400);
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();
        });

        searchInput.addEventListener('input', debouncedFetch);
        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });

        sortSelect.addEventListener('change', () => {
            fetchResults();
        });

        resetBtn.addEventListener('click', (event) => {
            event.preventDefault();
            searchInput.value = '';
            sortSelect.value = 'latest';
            fetchResults(baseUrl);
        });

        resultsContainer.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');
            if (!link) return;

            const href = link.getAttribute('href') ?? '';
            if (!href.includes('page=')) return;

            event.preventDefault();
            fetchResults(link.href);
        });

        syncTotalLabel();
    });
</script>
@endsection
