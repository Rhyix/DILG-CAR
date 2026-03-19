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

    <div id="applicantDeleteModal" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/45 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_30px_90px_-40px_rgba(15,23,42,0.65)]">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Delete Applicant Record</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-900">Permanent deletion flow</h2>
                </div>
                <button type="button" id="applicantDeleteModalClose" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5">
                <section id="applicantDeleteStepConfirm" class="space-y-5">
                    <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-4">
                        <p class="text-sm font-semibold text-rose-700">You are about to permanently delete this applicant record.</p>
                        <p class="mt-2 text-sm leading-6 text-rose-700/90">This removes the applicant account and all applicant-linked records from the portal.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Applicant</p>
                        <p id="deleteApplicantName" class="mt-2 text-base font-semibold text-slate-900">N/A</p>
                        <p id="deleteApplicantCode" class="mt-1 text-sm text-slate-500">N/A</p>
                    </div>
                </section>

                <section id="applicantDeleteStepChallenge" class="hidden space-y-5">
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                        <p class="text-sm font-semibold text-amber-800">Type the generated code exactly as shown to continue.</p>
                        <p class="mt-2 text-sm leading-6 text-amber-800/90">This check is case-sensitive and uses 7 random letters.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Verification Code</p>
                        <div id="deleteApplicantChallengeCode" class="mt-3 rounded-2xl bg-white px-4 py-3 text-center font-mono text-2xl font-bold tracking-[0.35em] text-[#0D2B70] ring-1 ring-slate-200"></div>
                    </div>
                    <div>
                        <label for="deleteApplicantChallengeInput" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                            Enter Code
                        </label>
                        <input id="deleteApplicantChallengeInput" type="text" autocomplete="off"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
                            placeholder="Type the 7-letter code exactly" />
                        <p id="deleteApplicantChallengeHint" class="mt-2 text-xs text-slate-500">Proceed becomes available only when the code matches exactly.</p>
                    </div>
                </section>

                <section id="applicantDeleteStepWarning" class="hidden space-y-5">
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4">
                        <p class="text-sm font-semibold text-rose-700">Final warning</p>
                        <p class="mt-2 text-sm leading-6 text-rose-700/90">This action is permanent and cannot be undone from the admin panel.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">The following records will be removed</p>
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-700">
                            <li>Applicant account details and profile information</li>
                            <li>PDS records, work experience sheet, and related personal-information tables</li>
                            <li>Applications, uploaded documents, gallery files, notifications, sessions, and exam-tab records</li>
                        </ul>
                    </div>
                </section>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">
                <button type="button" id="applicantDeleteModalCancel" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Cancel
                </button>
                <button type="button" id="applicantDeleteModalBack" class="hidden inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Back
                </button>
                <button type="button" id="applicantDeleteModalPrimary" class="inline-flex items-center justify-center rounded-2xl bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0A235C]">
                    Continue
                </button>
            </div>
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        const deleteModal = document.getElementById('applicantDeleteModal');
        const deleteModalClose = document.getElementById('applicantDeleteModalClose');
        const deleteModalCancel = document.getElementById('applicantDeleteModalCancel');
        const deleteModalBack = document.getElementById('applicantDeleteModalBack');
        const deleteModalPrimary = document.getElementById('applicantDeleteModalPrimary');
        const deleteApplicantName = document.getElementById('deleteApplicantName');
        const deleteApplicantCode = document.getElementById('deleteApplicantCode');
        const deleteChallengeCode = document.getElementById('deleteApplicantChallengeCode');
        const deleteChallengeInput = document.getElementById('deleteApplicantChallengeInput');
        const deleteChallengeHint = document.getElementById('deleteApplicantChallengeHint');

        const deleteSteps = {
            confirm: document.getElementById('applicantDeleteStepConfirm'),
            challenge: document.getElementById('applicantDeleteStepChallenge'),
            warning: document.getElementById('applicantDeleteStepWarning'),
        };

        if (deleteModal) {
            document.body.appendChild(deleteModal);
        }

        let debounceTimer = null;
        let activeController = null;
        let latestRequestId = 0;
        let deleteRequestInFlight = false;
        let deleteState = {
            url: '',
            name: '',
            code: '',
            challenge: '',
            step: 'confirm',
        };

        const errorState = `
            <section class="overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-sm">
                <div class="px-5 py-10 text-center text-sm font-medium text-rose-600">
                    Unable to load applicant records. Please try again.
                </div>
            </section>
        `;
        const challengeCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        const showStatus = (type, message) => {
            if (!message) return;

            if (typeof window.showAppToast === 'function') {
                window.showAppToast(message, type);
                return;
            }

            window.alert(message);
        };

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

        const generateChallengeCode = () => {
            let code = '';

            for (let index = 0; index < 7; index += 1) {
                code += challengeCharacters[Math.floor(Math.random() * challengeCharacters.length)];
            }

            return code;
        };

        const updateDeleteModal = () => {
            const isConfirmStep = deleteState.step === 'confirm';
            const isChallengeStep = deleteState.step === 'challenge';
            const isWarningStep = deleteState.step === 'warning';
            const hasMatchingChallenge = deleteChallengeInput.value === deleteState.challenge;

            deleteSteps.confirm.classList.toggle('hidden', !isConfirmStep);
            deleteSteps.challenge.classList.toggle('hidden', !isChallengeStep);
            deleteSteps.warning.classList.toggle('hidden', !isWarningStep);

            deleteModalBack.classList.toggle('hidden', isConfirmStep || deleteRequestInFlight);

            deleteModalPrimary.classList.remove('cursor-not-allowed', 'opacity-60', 'bg-slate-300', 'hover:bg-slate-300', 'bg-rose-600', 'hover:bg-rose-700', 'bg-[#0D2B70]', 'hover:bg-[#0A235C]');

            if (isConfirmStep) {
                deleteModalPrimary.textContent = 'Continue';
                deleteModalPrimary.disabled = false;
                deleteModalPrimary.classList.add('bg-[#0D2B70]', 'hover:bg-[#0A235C]');
                return;
            }

            if (isChallengeStep) {
                deleteModalPrimary.textContent = 'Proceed';
                deleteModalPrimary.disabled = !hasMatchingChallenge;
                deleteModalPrimary.classList.add(hasMatchingChallenge ? 'bg-[#0D2B70]' : 'bg-slate-300');
                deleteModalPrimary.classList.add(hasMatchingChallenge ? 'hover:bg-[#0A235C]' : 'hover:bg-slate-300');

                if (!hasMatchingChallenge) {
                    deleteModalPrimary.classList.add('cursor-not-allowed', 'opacity-60');
                }
                return;
            }

            deleteModalPrimary.textContent = deleteRequestInFlight ? 'Deleting...' : 'Permanently Delete';
            deleteModalPrimary.disabled = deleteRequestInFlight;
            deleteModalPrimary.classList.add('bg-rose-600', 'hover:bg-rose-700');

            if (deleteRequestInFlight) {
                deleteModalPrimary.classList.add('cursor-not-allowed', 'opacity-60');
            }
        };

        const closeDeleteModal = () => {
            if (deleteRequestInFlight) return;

            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');

            deleteState = {
                url: '',
                name: '',
                code: '',
                challenge: '',
                step: 'confirm',
            };

            deleteChallengeInput.value = '';
            deleteChallengeCode.textContent = '';
            deleteChallengeHint.textContent = 'Proceed becomes available only when the code matches exactly.';
            deleteChallengeHint.className = 'mt-2 text-xs text-slate-500';
            updateDeleteModal();
        };

        const openDeleteModal = ({ url, name, code }) => {
            deleteState = {
                url,
                name,
                code,
                challenge: '',
                step: 'confirm',
            };

            deleteApplicantName.textContent = name;
            deleteApplicantCode.textContent = `Applicant ID: ${code}`;
            deleteChallengeInput.value = '';
            deleteChallengeCode.textContent = '';
            deleteChallengeHint.textContent = 'Proceed becomes available only when the code matches exactly.';
            deleteChallengeHint.className = 'mt-2 text-xs text-slate-500';
            updateDeleteModal();

            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');
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

        deleteChallengeInput.addEventListener('input', () => {
            const isExactMatch = deleteChallengeInput.value === deleteState.challenge;

            if (deleteChallengeInput.value === '') {
                deleteChallengeHint.textContent = 'Proceed becomes available only when the code matches exactly.';
                deleteChallengeHint.className = 'mt-2 text-xs text-slate-500';
            } else if (isExactMatch) {
                deleteChallengeHint.textContent = 'Code matched. You can proceed to the final warning.';
                deleteChallengeHint.className = 'mt-2 text-xs text-emerald-600';
            } else {
                deleteChallengeHint.textContent = 'Code does not match yet. Case matters.';
                deleteChallengeHint.className = 'mt-2 text-xs text-rose-600';
            }

            updateDeleteModal();
        });

        const triggerDeleteRequest = async () => {
            deleteRequestInFlight = true;
            deleteModalBack.disabled = true;
            deleteModalCancel.disabled = true;
            deleteModalClose.disabled = true;
            updateDeleteModal();

            try {
                const response = await fetch(deleteState.url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message ?? 'Unable to delete applicant record.');
                }

                deleteRequestInFlight = false;
                deleteModalBack.disabled = false;
                deleteModalCancel.disabled = false;
                deleteModalClose.disabled = false;
                closeDeleteModal();
                showStatus('success', payload.message ?? 'Applicant record deleted.');
                fetchResults(window.location.href);
            } catch (error) {
                deleteRequestInFlight = false;
                deleteModalBack.disabled = false;
                deleteModalCancel.disabled = false;
                deleteModalClose.disabled = false;
                updateDeleteModal();
                showStatus('error', error.message ?? 'Unable to delete applicant record.');
            }
        };

        const stepDeleteModalForward = () => {
            if (deleteState.step === 'confirm') {
                deleteState.challenge = generateChallengeCode();
                deleteState.step = 'challenge';
                deleteChallengeCode.textContent = deleteState.challenge;
                deleteChallengeInput.value = '';
                deleteChallengeInput.focus();
                updateDeleteModal();
                return;
            }

            if (deleteState.step === 'challenge') {
                if (deleteChallengeInput.value !== deleteState.challenge) return;

                deleteState.step = 'warning';
                updateDeleteModal();
                return;
            }

            triggerDeleteRequest();
        };

        const stepDeleteModalBackward = () => {
            if (deleteRequestInFlight) return;

            if (deleteState.step === 'warning') {
                deleteState.step = 'challenge';
                updateDeleteModal();
                return;
            }

            if (deleteState.step === 'challenge') {
                deleteState.step = 'confirm';
                updateDeleteModal();
            }
        };

        deleteModalPrimary.addEventListener('click', stepDeleteModalForward);
        deleteModalBack.addEventListener('click', stepDeleteModalBackward);
        deleteModalCancel.addEventListener('click', closeDeleteModal);
        deleteModalClose.addEventListener('click', closeDeleteModal);
        deleteModal.addEventListener('click', (event) => {
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDeleteModal();
            }
        });

        resultsContainer.addEventListener('click', (event) => {
            const deleteButton = event.target.closest('[data-delete-applicant-url]');
            if (deleteButton) {
                event.preventDefault();
                openDeleteModal({
                    url: deleteButton.dataset.deleteApplicantUrl ?? '',
                    name: deleteButton.dataset.deleteApplicantName ?? 'Applicant',
                    code: deleteButton.dataset.deleteApplicantCode ?? 'N/A',
                });
                return;
            }

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
