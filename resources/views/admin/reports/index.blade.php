@extends('layout.admin')

@section('title', 'Reports & Analytics')

@section('content')
<main class="mx-auto w-full max-w-[1500px] pb-8">
    <section class="flex items-center gap-3 border-b border-[#0D2B70] pb-3">
        <h1 class="text-3xl font-montserrat font-semibold text-[#0D2B70]">Reports & Analytics</h1>
        <span id="reportTitleBadge" class="rounded-full border border-[#0D2B70]/30 bg-[#0D2B70]/10 px-3 py-1 text-xs font-semibold text-[#0D2B70]">
            Vacancy Summary Report
        </span>
    </section>

    <section class="mt-4 grid gap-4 xl:grid-cols-[280px_1fr]">
        <aside class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <p class="px-2 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Report Modules</p>
            <div class="space-y-1" id="reportNav">
                <button type="button" data-report="vacancy_summary" class="report-nav-btn is-active">A. Vacancy Summary Report</button>
                <button type="button" data-report="vacancy_performance" class="report-nav-btn">B. Vacancy Performance Report</button>
                <button type="button" data-report="vacancy_detailed" class="report-nav-btn">C. Vacancy Detailed Report (Printable)</button>
                <button type="button" data-report="applicant_master_list" class="report-nav-btn">Applicant Master List</button>
                <button type="button" data-report="applicant_status_analytics" class="report-nav-btn">Applicant Status Analytics</button>
                <button type="button" data-report="exam_schedule" class="report-nav-btn">Exam Schedule Report</button>
                <button type="button" data-report="exam_result_summary" class="report-nav-btn">Exam Result Summary</button>
                <button type="button" data-report="exam_vacancy_based_result" class="report-nav-btn">Vacancy-Based Exam Result</button>
            </div>
        </aside>

        <div class="space-y-4">
            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                    <div>
                        <label for="startDate" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Start Date</label>
                        <input id="startDate" type="date" class="filter-input">
                    </div>
                    <div>
                        <label for="endDate" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">End Date</label>
                        <input id="endDate" type="date" class="filter-input">
                    </div>
                    <div>
                        <label for="vacancyFilter" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Vacancy</label>
                        <select id="vacancyFilter" class="filter-input">
                            <option value="">All Vacancies</option>
                            @foreach($vacancies ?? [] as $vacancy)
                                <option value="{{ $vacancy->vacancy_id }}">
                                    {{ $vacancy->vacancy_id }} - {{ $vacancy->position_title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="statusFilterWrap" class="hidden">
                        <label for="statusFilter" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Status</label>
                        <select id="statusFilter" class="filter-input">
                            <option value="">All Statuses</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="passed">Passed</option>
                            <option value="failed">Failed</option>
                            <option value="withdrawn">Withdrawn</option>
                            <option value="pending">Pending</option>
                            <option value="submitted">Submitted</option>
                            <option value="updated">Updated</option>
                            <option value="qualified">Qualified</option>
                        </select>
                    </div>
                    <div id="qualificationFilterWrap" class="hidden">
                        <label for="qualificationFilter" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Qualification</label>
                        <select id="qualificationFilter" class="filter-input">
                            <option value="">All Qualifications</option>
                            <option value="Qualified">Qualified</option>
                            <option value="Not Qualified">Not Qualified</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" id="applyFiltersBtn" class="primary-btn">Apply Filters</button>
                        <button type="button" id="resetFiltersBtn" class="secondary-btn">Reset</button>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" id="exportCsvBtn" class="secondary-btn">Export CSV</button>
                        <button type="button" id="exportExcelBtn" class="secondary-btn hidden">Export Excel</button>
                        <button type="button" id="exportPdfBtn" class="secondary-btn hidden">Export PDF</button>
                        <button type="button" id="printReportBtn" class="secondary-btn hidden">Print</button>
                    </div>
                </div>
            </section>

            <section id="reportError" class="hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700"></section>

            <section id="reportLoading" class="hidden rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                <div class="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-[#0D2B70]"></div>
                <p class="mt-3 text-sm font-semibold text-slate-600">Loading report data...</p>
            </section>

            <section id="reportContent" class="space-y-4">
                <div id="summaryCards" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4"></div>

                <div id="chartsGrid" class="grid gap-4 lg:grid-cols-2"></div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-4 py-3">
                        <h2 id="tableTitle" class="text-sm font-bold uppercase tracking-wide text-[#0D2B70]">Report Data</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto text-sm" id="reportTable">
                            <thead class="bg-slate-50"></thead>
                            <tbody class="divide-y divide-slate-100 bg-white"></tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    .report-nav-btn {
        width: 100%;
        border: 1px solid transparent;
        border-radius: 12px;
        padding: 0.65rem 0.7rem;
        text-align: left;
        font-size: 0.84rem;
        font-weight: 600;
        color: #334155;
        transition: all 0.15s ease;
    }
    .report-nav-btn:hover { border-color: #bfdbfe; background: #eff6ff; color: #0d2b70; }
    .report-nav-btn.is-active { border-color: #0d2b70; background: #0d2b70; color: #fff; }
    .filter-input {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        padding: 0.58rem 0.7rem;
        font-size: 0.875rem;
        color: #1e293b;
        background: #fff;
        outline: none;
    }
    .filter-input:focus { border-color: #0d2b70; box-shadow: 0 0 0 2px rgba(13, 43, 112, 0.12); }
    .primary-btn {
        border: 1px solid #0d2b70;
        background: #0d2b70;
        color: #fff;
        border-radius: 0.6rem;
        padding: 0.5rem 0.85rem;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .primary-btn:hover { background: #0a2259; }
    .secondary-btn {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        border-radius: 0.6rem;
        padding: 0.5rem 0.85rem;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .secondary-btn:hover { background: #f8fafc; }
    @media print {
        #reportNav, #applyFiltersBtn, #resetFiltersBtn, #exportCsvBtn, #exportExcelBtn, #exportPdfBtn, #printReportBtn,
        #statusFilterWrap, #qualificationFilterWrap {
            display: none !important;
        }
        .border, .shadow-sm, .rounded-2xl, .rounded-xl { box-shadow: none !important; border: 0 !important; }
        main { max-width: none !important; padding: 0 !important; margin: 0 !important; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const reportMeta = {
        vacancy_summary: 'Vacancy Summary Report',
        vacancy_performance: 'Vacancy Performance Report',
        vacancy_detailed: 'Vacancy Detailed Report (Printable)',
        applicant_master_list: 'Applicant Master List',
        applicant_status_analytics: 'Applicant Status Analytics',
        exam_schedule: 'Exam Schedule Report',
        exam_result_summary: 'Exam Result Summary',
        exam_vacancy_based_result: 'Vacancy-Based Exam Result'
    };

    const els = {
        nav: document.getElementById('reportNav'),
        titleBadge: document.getElementById('reportTitleBadge'),
        startDate: document.getElementById('startDate'),
        endDate: document.getElementById('endDate'),
        vacancy: document.getElementById('vacancyFilter'),
        statusWrap: document.getElementById('statusFilterWrap'),
        qualificationWrap: document.getElementById('qualificationFilterWrap'),
        status: document.getElementById('statusFilter'),
        qualification: document.getElementById('qualificationFilter'),
        applyBtn: document.getElementById('applyFiltersBtn'),
        resetBtn: document.getElementById('resetFiltersBtn'),
        exportCsvBtn: document.getElementById('exportCsvBtn'),
        exportExcelBtn: document.getElementById('exportExcelBtn'),
        exportPdfBtn: document.getElementById('exportPdfBtn'),
        printBtn: document.getElementById('printReportBtn'),
        loading: document.getElementById('reportLoading'),
        content: document.getElementById('reportContent'),
        error: document.getElementById('reportError'),
        cards: document.getElementById('summaryCards'),
        chartsGrid: document.getElementById('chartsGrid'),
        tableTitle: document.getElementById('tableTitle'),
        tableHead: document.querySelector('#reportTable thead'),
        tableBody: document.querySelector('#reportTable tbody')
    };

    let currentReport = 'vacancy_summary';
    let chartInstances = [];

    function setDefaultDates() {
        const now = new Date();
        const startOfYear = new Date(now.getFullYear(), 0, 1);
        els.startDate.valueAsDate = startOfYear;
        els.endDate.valueAsDate = now;
    }

    function setActiveReport(reportType) {
        currentReport = reportType;
        els.titleBadge.textContent = reportMeta[reportType] || reportType;
        [...document.querySelectorAll('.report-nav-btn')].forEach((btn) => {
            btn.classList.toggle('is-active', btn.dataset.report === reportType);
        });

        const applicantReport = reportType === 'applicant_master_list' || reportType === 'applicant_status_analytics';
        els.qualificationWrap.classList.toggle('hidden', !applicantReport);
        els.statusWrap.classList.toggle('hidden', reportType !== 'applicant_master_list');

        const masterList = reportType === 'applicant_master_list';
        els.exportExcelBtn.classList.toggle('hidden', !masterList);
        els.exportPdfBtn.classList.toggle('hidden', !masterList);
        els.printBtn.classList.toggle('hidden', reportType !== 'vacancy_detailed');
    }

    function buildParams(extra = {}) {
        const params = new URLSearchParams({
            type: currentReport,
            start_date: els.startDate.value || '',
            end_date: els.endDate.value || '',
            vacancy_id: els.vacancy.value || '',
            ...extra
        });

        if (!els.statusWrap.classList.contains('hidden') && els.status.value) {
            params.set('status', els.status.value);
        }
        if (!els.qualificationWrap.classList.contains('hidden') && els.qualification.value) {
            params.set('qualification', els.qualification.value);
        }
        return params;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showError(message) {
        els.error.textContent = message;
        els.error.classList.remove('hidden');
    }

    function clearError() {
        els.error.textContent = '';
        els.error.classList.add('hidden');
    }

    function renderCards(cards = []) {
        els.cards.innerHTML = '';
        if (!cards.length) return;

        cards.forEach((card) => {
            const wrap = document.createElement('div');
            wrap.className = 'rounded-xl border border-slate-200 bg-white p-4 shadow-sm';
            wrap.innerHTML = `
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">${escapeHtml(card.label)}</p>
                <p class="mt-1 text-2xl font-bold text-[#0D2B70]">${escapeHtml(card.value)}</p>
            `;
            els.cards.appendChild(wrap);
        });
    }

    function destroyCharts() {
        chartInstances.forEach((chart) => chart.destroy());
        chartInstances = [];
    }

    function renderCharts(charts = []) {
        destroyCharts();
        els.chartsGrid.innerHTML = '';
        if (!charts.length) return;

        charts.forEach((chartConfig, index) => {
            const card = document.createElement('div');
            card.className = 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm';
            const canvasId = `report-chart-${index}`;
            card.innerHTML = `
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-[#0D2B70]">${escapeHtml(chartConfig.title || 'Chart')}</h3>
                <div class="h-[280px]"><canvas id="${canvasId}"></canvas></div>
            `;
            els.chartsGrid.appendChild(card);

            const ctx = document.getElementById(canvasId);
            if (!ctx) return;

            const instance = new Chart(ctx, {
                type: chartConfig.type || 'bar',
                data: {
                    labels: chartConfig.labels || [],
                    datasets: chartConfig.datasets || []
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: ['bar', 'line'].includes(chartConfig.type)
                        ? { y: { beginAtZero: true } }
                        : {}
                }
            });
            chartInstances.push(instance);
        });
    }

    function renderTable(table = {}) {
        els.tableTitle.textContent = table.title || 'Report Data';
        const headers = table.headers || [];
        const rows = table.rows || [];

        els.tableHead.innerHTML = `<tr>${headers.map((h) => `<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">${escapeHtml(h)}</th>`).join('')}</tr>`;

        if (!rows.length) {
            els.tableBody.innerHTML = `<tr><td colspan="${Math.max(headers.length, 1)}" class="px-4 py-10 text-center text-sm text-slate-500">No records found for the selected filters.</td></tr>`;
            return;
        }

        els.tableBody.innerHTML = rows.map((row) => {
            const cols = Array.isArray(row) ? row : [];
            return `<tr>${cols.map((value) => `<td class="px-4 py-3 align-top text-sm text-slate-700">${escapeHtml(value)}</td>`).join('')}</tr>`;
        }).join('');
    }

    async function loadReport() {
        clearError();
        els.loading.classList.remove('hidden');
        els.content.classList.add('hidden');

        try {
            const params = buildParams();
            const response = await fetch(`{{ route('admin.reports.data') }}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                throw new Error(`Failed to load report (${response.status})`);
            }

            const payload = await response.json();
            if (payload.error) {
                throw new Error(payload.error);
            }

            renderCards(payload.summary_cards || []);
            renderCharts(payload.charts || []);
            renderTable(payload.table || {});
        } catch (error) {
            showError(error.message || 'Unable to load report data.');
            renderCards([]);
            renderCharts([]);
            renderTable({ headers: ['Message'], rows: [['No data available']] });
        } finally {
            els.loading.classList.add('hidden');
            els.content.classList.remove('hidden');
        }
    }

    function exportReport(format) {
        const params = buildParams({ format });
        window.location.href = `{{ route('admin.reports.export') }}?${params.toString()}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        setDefaultDates();
        setActiveReport('vacancy_summary');

        els.nav?.addEventListener('click', (event) => {
            const btn = event.target.closest('.report-nav-btn');
            if (!btn) return;
            setActiveReport(btn.dataset.report);
            loadReport();
        });

        els.applyBtn?.addEventListener('click', loadReport);
        els.resetBtn?.addEventListener('click', () => {
            setDefaultDates();
            els.vacancy.value = '';
            els.status.value = '';
            els.qualification.value = '';
            loadReport();
        });

        els.exportCsvBtn?.addEventListener('click', () => exportReport('csv'));
        els.exportExcelBtn?.addEventListener('click', () => exportReport('excel'));
        els.exportPdfBtn?.addEventListener('click', () => exportReport('pdf'));
        els.printBtn?.addEventListener('click', () => window.print());

        loadReport();
    });
</script>
@endpush
