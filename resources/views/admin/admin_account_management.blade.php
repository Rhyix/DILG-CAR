@extends('layout.admin')
@section('title', 'DILG - User Management')
@section('content')

<main class="mx-auto w-full max-w-[1400px] pb-8">
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-4xl font-montserrat tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">User Management</span>
        </h1>
    </section>

    <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-end">
                <div class="relative w-full lg:max-w-md">
                    <label for="adminSearchInput" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Search
                    </label>
                    <input id="adminSearchInput" type="search" placeholder="Search name, email, office, or role"
                        class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-12 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                    <svg class="pointer-events-none absolute left-3 top-[39px] h-5 w-5 -translate-y-1/2 text-slate-400"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                    <button id="adminSearchClear" type="button" hidden
                        class="absolute right-2 top-[39px] -translate-y-1/2 rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Clear search">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-auto lg:min-w-[370px]">
                    <div>
                        <label for="adminRoleFilter" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            Role
                        </label>
                        <select id="adminRoleFilter"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            <option value="">All roles</option>
                            <option value="superadmin">Superadmin</option>
                            <option value="admin">Admin (HR)</option>
                            <option value="hr_division">HR Division</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <div>
                        <label for="adminStatusFilter" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            Status
                        </label>
                        <select id="adminStatusFilter"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            <option value="">All status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending Approval</option>
                            <option value="declined">Declined</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="shrink-0">
                @include('partials.admin_add_account')
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center justify-end gap-2">
            <button id="adminFilterReset" type="button" hidden
                class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                Reset filters
            </button>
        </div>
    </section>

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Accounts Table</p>
            <span id="pendingCountBadge"
                class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                <span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span>
                Pending registrations: <span id="pendingCountValue">0</span>
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-[#0D2B70] text-white">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold">Email</th>
                        <th class="px-5 py-3 text-left font-semibold">Role</th>
                        <th class="px-5 py-3 text-left font-semibold">Office / Designation</th>
                        <th class="px-5 py-3 text-center font-semibold">Status</th>
                        <th class="px-5 py-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody id="adminRows" class="divide-y divide-slate-200">
                    @include('partials.admin_list', ['admins' => $admins])
                </tbody>
            </table>
        </div>
    </section>

    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition
            class="fixed right-5 top-5 z-50 w-full max-w-sm rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-lg">
            <p class="text-sm font-semibold">Success</p>
            <p class="mt-1 text-sm">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4500)" x-show="show" x-transition
            class="fixed right-5 top-5 z-50 w-full max-w-sm rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-rose-800 shadow-lg">
            <p class="text-sm font-semibold">Error</p>
            <ul class="mt-1 list-disc pl-5 text-sm">
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

    <div id="adminApproveModal"
        class="fixed inset-0 z-[9990] hidden items-center justify-center bg-slate-900/55 px-4 py-6 backdrop-blur-sm"
        data-route-template="{{ url('/admin/__ID__/approve') }}">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-base font-bold text-[#0D2B70]">Approve Account</h2>
                    <p class="text-xs text-slate-500">Assign role before final approval.</p>
                </div>
                <button type="button" id="adminApproveModalClose"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="adminApproveForm" method="POST" class="js-admin-approve-form no-spinner space-y-4 p-5">
                @csrf
                <p class="text-sm text-slate-700">
                    Approving account:
                    <span id="adminApproveTargetName" class="font-semibold text-[#0D2B70]">-</span>
                </p>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Assign Role</label>
                    <div class="grid gap-2">
                        <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                            <div class="flex items-start gap-2">
                                <input type="radio" name="approval_role" value="admin" class="mt-0.5 accent-[#0D2B70]" checked>
                                <div>
                                    <p class="text-sm font-semibold text-[#0D2B70]">Admin (HR)</p>
                                    <p class="text-xs text-slate-500">All admin tools except user management.</p>
                                </div>
                            </div>
                        </label>
                        <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                            <div class="flex items-start gap-2">
                                <input type="radio" name="approval_role" value="hr_division" class="mt-0.5 accent-[#0D2B70]">
                                <div>
                                    <p class="text-sm font-semibold text-[#0D2B70]">HR Division</p>
                                    <p class="text-xs text-slate-500">Applicants management only.</p>
                                </div>
                            </div>
                        </label>
                        <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                            <div class="flex items-start gap-2">
                                <input type="radio" name="approval_role" value="viewer" class="mt-0.5 accent-[#0D2B70]">
                                <div>
                                    <p class="text-sm font-semibold text-[#0D2B70]">Viewer</p>
                                    <p class="text-xs text-slate-500">Exam management only.</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" id="adminApproveModalCancel"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        Cancel
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Approve Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <x-confirm-modal title="Confirm Activation" message="Are you sure you want to activate this account?"
        event="open-admin-activate-confirm" confirm="confirm-admin-activate" />
    <x-confirm-modal title="Confirm Deactivation" message="Are you sure you want to deactivate this account?"
        event="open-admin-deactivate-confirm" confirm="confirm-admin-deactivate" />
    <x-confirm-modal title="Confirm Approval" message="Approve this account and apply the selected role?"
        event="open-admin-approve-confirm" confirm="confirm-admin-approve" confirmText="Approve" tone="success" />
    <x-confirm-modal title="Confirm Decline" message="Decline this registration request?"
        event="open-admin-decline-confirm" confirm="confirm-admin-decline" confirmText="Decline" tone="danger" />
    <x-confirm-modal title="Confirm Changes" message="Are you sure you want to save changes to this account?"
        event="open-admin-edit-confirm" confirm="confirm-admin-edit" />

    @include('partials.loader')
</main>
@endsection

@push('styles')
<style>
    .role-option {
        transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
    }

    .role-option:has(input[type="radio"]:checked) {
        border-color: #0d2b70;
        background-color: #eff6ff;
        box-shadow: 0 0 0 2px rgba(13, 43, 112, 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let pendingConfirmationForm = null;
        const approveModal = document.getElementById('adminApproveModal');
        const approveForm = document.getElementById('adminApproveForm');
        const approveTargetName = document.getElementById('adminApproveTargetName');
        const approveModalClose = document.getElementById('adminApproveModalClose');
        const approveModalCancel = document.getElementById('adminApproveModalCancel');

        const showLoaderOverlay = () => {
            const overlay = document.getElementById('loader');
            const liveRegion = document.getElementById('loader-live');
            const loaderText = document.getElementById('loader-text');
            if (overlay) {
                overlay.classList.remove('hidden');
                overlay.classList.remove('pds-loading-nonblocking');
                overlay.setAttribute('aria-busy', 'true');
            }
            if (liveRegion) liveRegion.textContent = 'Loading...';
            if (loaderText) loaderText.textContent = 'Loading...';
        };

        const submitPendingForm = () => {
            if (!pendingConfirmationForm) return;
            const form = pendingConfirmationForm;
            pendingConfirmationForm = null;
            showLoaderOverlay();
            form.submit();
        };

        const closeApproveModal = () => {
            if (!approveModal) return;
            approveModal.classList.add('hidden');
            approveModal.classList.remove('flex');
        };

        const openApproveModal = (adminId, adminName) => {
            if (!approveModal || !approveForm) return;
            const routeTemplate = approveModal.dataset.routeTemplate || '';
            approveForm.action = routeTemplate.replace('__ID__', String(adminId));
            if (approveTargetName) {
                approveTargetName.textContent = adminName || '-';
            }
            approveModal.classList.remove('hidden');
            approveModal.classList.add('flex');
        };

        if (approveModalClose) {
            approveModalClose.addEventListener('click', closeApproveModal);
        }
        if (approveModalCancel) {
            approveModalCancel.addEventListener('click', closeApproveModal);
        }
        if (approveModal) {
            approveModal.addEventListener('click', (event) => {
                if (event.target === approveModal) {
                    closeApproveModal();
                }
            });
        }

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            if (form.matches('.js-admin-status-form')) {
                event.preventDefault();
                pendingConfirmationForm = form;
                const action = form.dataset.action === 'deactivate' ? 'open-admin-deactivate-confirm' : 'open-admin-activate-confirm';
                window.dispatchEvent(new CustomEvent(action));
                return;
            }

            if (form.matches('.js-admin-edit-form')) {
                event.preventDefault();
                pendingConfirmationForm = form;
                window.dispatchEvent(new CustomEvent('open-admin-edit-confirm'));
                return;
            }

            if (form.matches('.js-admin-approve-form')) {
                event.preventDefault();
                pendingConfirmationForm = form;
                window.dispatchEvent(new CustomEvent('open-admin-approve-confirm'));
                return;
            }

            if (form.matches('.js-admin-decline-form')) {
                event.preventDefault();
                pendingConfirmationForm = form;
                window.dispatchEvent(new CustomEvent('open-admin-decline-confirm'));
                return;
            }
        }, true);

        document.addEventListener('click', (event) => {
            const button = event.target.closest('.js-open-approve-modal');
            if (!button) return;
            const adminId = button.dataset.adminId;
            const adminName = button.dataset.adminName;
            if (!adminId) return;
            openApproveModal(adminId, adminName);
        });

        window.addEventListener('confirm-admin-activate', submitPendingForm);
        window.addEventListener('confirm-admin-deactivate', submitPendingForm);
        window.addEventListener('confirm-admin-approve', () => {
            closeApproveModal();
            submitPendingForm();
        });
        window.addEventListener('confirm-admin-decline', submitPendingForm);
        window.addEventListener('confirm-admin-edit', submitPendingForm);

        const searchInput = document.getElementById('adminSearchInput');
        const clearBtn = document.getElementById('adminSearchClear');
        const roleFilter = document.getElementById('adminRoleFilter');
        const statusFilter = document.getElementById('adminStatusFilter');
        const resetFiltersBtn = document.getElementById('adminFilterReset');
        const rowsContainer = document.getElementById('adminRows');
        const pendingCountBadge = document.getElementById('pendingCountBadge');
        const pendingCountValue = document.getElementById('pendingCountValue');
        if (!searchInput || !rowsContainer || !roleFilter || !statusFilter || !resetFiltersBtn) return;

        const updatePendingCount = () => {
            if (!pendingCountBadge || !pendingCountValue) return;
            const pendingRows = rowsContainer.querySelectorAll('tr[data-status="pending"]').length;
            pendingCountValue.textContent = String(pendingRows);

            const hasPending = pendingRows > 0;
            pendingCountBadge.classList.toggle('border-amber-200', hasPending);
            pendingCountBadge.classList.toggle('bg-amber-50', hasPending);
            pendingCountBadge.classList.toggle('text-amber-700', hasPending);
            pendingCountBadge.classList.toggle('border-slate-200', !hasPending);
            pendingCountBadge.classList.toggle('bg-slate-100', !hasPending);
            pendingCountBadge.classList.toggle('text-slate-600', !hasPending);
        };

        const loadingRow = `
            <tr>
                <td colspan="5" class="px-5 py-10 text-center text-slate-500">
                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-600">
                        <svg class="h-4 w-4 animate-spin text-[#0D2B70]" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-20"></circle>
                            <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="4" class="opacity-90"></path>
                        </svg>
                        Searching accounts...
                    </div>
                </td>
            </tr>`;

        const errorRow = `
            <tr>
                <td colspan="5" class="px-5 py-10 text-center text-rose-600">
                    Unable to load search results. Please try again.
                </td>
            </tr>`;

        const searchUrl = '{{ route('admin.search') }}';
        let searchTimer = null;
        let activeController = null;
        let latestRequestId = 0;

        const updateControlsVisibility = () => {
            clearBtn.hidden = searchInput.value.trim() === '';
            resetFiltersBtn.hidden = searchInput.value.trim() === '' && roleFilter.value === '' && statusFilter.value === '';
        };

        const buildSearchParams = () => {
            const params = new URLSearchParams();
            const query = searchInput.value.trim();
            if (query !== '') params.set('query', query);
            if (roleFilter.value !== '') params.set('role', roleFilter.value);
            if (statusFilter.value !== '') params.set('status', statusFilter.value);
            return params;
        };

        const fetchRows = async () => {
            if (activeController) activeController.abort();
            activeController = new AbortController();
            const requestId = ++latestRequestId;

            rowsContainer.innerHTML = loadingRow;
            try {
                const params = buildSearchParams();
                const response = await fetch(`${searchUrl}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: activeController.signal
                });
                if (!response.ok) throw new Error(`Search request failed with ${response.status}`);
                const html = await response.text();
                if (requestId !== latestRequestId) return;
                rowsContainer.innerHTML = html;
                updatePendingCount();
                if (window.feather) window.feather.replace();
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(rowsContainer);
                }
            } catch (e) {
                if (e.name === 'AbortError') return;
                rowsContainer.innerHTML = errorRow;
                updatePendingCount();
            }
        };

        const debouncedFetch = () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(fetchRows, 280);
        };

        searchInput.addEventListener('input', () => {
            updateControlsVisibility();
            debouncedFetch();
        });

        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            updateControlsVisibility();
            fetchRows();
            searchInput.focus();
        });

        roleFilter.addEventListener('change', () => {
            updateControlsVisibility();
            fetchRows();
        });

        statusFilter.addEventListener('change', () => {
            updateControlsVisibility();
            fetchRows();
        });

        resetFiltersBtn.addEventListener('click', () => {
            searchInput.value = '';
            roleFilter.value = '';
            statusFilter.value = '';
            updateControlsVisibility();
            fetchRows();
        });

        updateControlsVisibility();
        updatePendingCount();
    });
</script>
@endpush
