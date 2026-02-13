@extends('layout.admin')

@section('title', 'Reports & Analytics')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar Navigation for Reports -->
    <div class="w-64 bg-white shadow-md z-10 hidden md:block">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Reports
            </h2>
        </div>
        <nav class="mt-4 px-4 space-y-1">
            <button onclick="loadReport('recruitment_performance')" class="report-nav-item w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors group active-report">
                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Recruitment Perf.
            </button>
            <button onclick="loadReport('applicant_demographics')" class="report-nav-item w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors group">
                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Applicant Demographics
            </button>
            <button onclick="loadReport('financial_summary')" class="report-nav-item w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors group">
                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Financial Summaries
            </button>
            <button onclick="loadReport('inventory')" class="report-nav-item w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors group">
                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                Talent Inventory
            </button>
            <button onclick="loadReport('user_activity')" class="report-nav-item w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors group">
                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                User Activity
            </button>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="bg-white shadow-sm z-10 p-4 flex justify-between items-center">
            <h1 id="report-title" class="text-2xl font-bold text-gray-800">Recruitment Performance</h1>
            <div class="flex items-center gap-3">
                <!-- Date Range Picker -->
                <div class="flex items-center gap-2 bg-gray-100 p-1 rounded-md">
                    <input type="date" id="start_date" class="bg-transparent border-none text-sm focus:ring-0 text-gray-600">
                    <span class="text-gray-400">-</span>
                    <input type="date" id="end_date" class="bg-transparent border-none text-sm focus:ring-0 text-gray-600">
                </div>
                <button onclick="refreshReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
                <div class="relative group">
                    <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block border">
                        <a href="#" onclick="exportReport('csv'); return false;" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export as CSV</a>
                        <!-- <a href="#" onclick="exportReport('pdf'); return false;" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export as PDF</a> -->
                    </div>
                </div>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div id="loading-state" class="hidden flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>

            <div id="report-content" class="space-y-6">
                <!-- Summary Cards -->
                <div id="summary-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Dynamic Content -->
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4" id="chart-1-title">Analytics</h3>
                        <canvas id="chart1" height="200"></canvas>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4" id="chart-2-title">Breakdown</h3>
                        <canvas id="chart2" height="200"></canvas>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">Detailed Data</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="data-table">
                            <thead class="bg-gray-50">
                                <!-- Dynamic Headers -->
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let currentReport = 'recruitment_performance';
    let chart1Instance = null;
    let chart2Instance = null;

    document.addEventListener('DOMContentLoaded', () => {
        // Set default dates (This Month)
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        document.getElementById('start_date').valueAsDate = firstDay;
        document.getElementById('end_date').valueAsDate = now;

        loadReport('recruitment_performance');
    });

    function loadReport(type) {
        currentReport = type;
        
        // Update Sidebar UI
        document.querySelectorAll('.report-nav-item').forEach(el => {
            el.classList.remove('bg-blue-50', 'text-blue-700', 'border-r-4', 'border-blue-600');
            el.classList.add('text-gray-700');
        });
        const activeBtn = document.querySelector(`button[onclick="loadReport('${type}')"]`);
        if(activeBtn) {
            activeBtn.classList.add('bg-blue-50', 'text-blue-700', 'border-r-4', 'border-blue-600');
            activeBtn.classList.remove('text-gray-700');
        }

        // Update Title
        const titles = {
            'recruitment_performance': 'Recruitment Performance Analytics',
            'applicant_demographics': 'Applicant Demographics Report',
            'financial_summary': 'Financial & Salary Summary',
            'user_activity': 'User Activity Logs',
            'inventory': 'Talent Pool Inventory'
        };
        document.getElementById('report-title').innerText = titles[type];

        fetchReportData();
    }

    function refreshReport() {
        fetchReportData();
    }

    function exportReport(format) {
        const start = document.getElementById('start_date').value;
        const end = document.getElementById('end_date').value;
        const url = `{{ route('admin.reports.export') }}?type=${currentReport}&start_date=${start}&end_date=${end}&format=${format}`;
        window.location.href = url;
    }

    async function fetchReportData() {
        const start = document.getElementById('start_date').value;
        const end = document.getElementById('end_date').value;
        
        document.getElementById('loading-state').classList.remove('hidden');
        document.getElementById('report-content').classList.add('hidden');

        try {
            const response = await fetch(`{{ route('admin.reports.data') }}?type=${currentReport}&start_date=${start}&end_date=${end}`);
            const data = await response.json();
            
            renderDashboard(data);
        } catch (error) {
            console.error('Error fetching report:', error);
            alert('Failed to load report data.');
        } finally {
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('report-content').classList.remove('hidden');
        }
    }

    function renderDashboard(data) {
        renderSummaryCards(data);
        renderCharts(data);
        renderTable(data);
    }

    function renderSummaryCards(data) {
        const container = document.getElementById('summary-cards');
        container.innerHTML = '';
        
        let cards = [];

        if (currentReport === 'recruitment_performance') {
            cards = [
                { label: 'Total Applications', value: data.total_applications, color: 'blue' },
                { label: 'Total Vacancies', value: data.total_vacancies, color: 'green' },
                { label: 'Avg. Apps/Vacancy', value: data.total_vacancies ? (data.total_applications / data.total_vacancies).toFixed(1) : 0, color: 'purple' },
                { label: 'Active Campaigns', value: data.vacancy_status.find(s => s.status === 'OPEN')?.count || 0, color: 'yellow' }
            ];
        } else if (currentReport === 'financial_summary') {
            cards = [
                { label: 'Total Projected Cost', value: '₱' + Number(data.total_projected_cost).toLocaleString(), color: 'green' },
                { label: 'Positions Count', value: data.top_salaries.length, color: 'blue' }
            ];
        } else if (currentReport === 'inventory') {
            cards = [
                { label: 'Total Candidates', value: data.total_candidates, color: 'indigo' },
                { label: 'Eligibility Types', value: data.eligibility_breakdown.length, color: 'pink' }
            ];
        }

        cards.forEach(card => {
            container.innerHTML += `
                <div class="bg-white rounded-xl p-6 shadow-sm border-l-4 border-${card.color}-500">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">${card.label}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-800">${card.value}</p>
                </div>
            `;
        });
    }

    function renderCharts(data) {
        if (chart1Instance) chart1Instance.destroy();
        if (chart2Instance) chart2Instance.destroy();

        const ctx1 = document.getElementById('chart1').getContext('2d');
        const ctx2 = document.getElementById('chart2').getContext('2d');

        if (currentReport === 'recruitment_performance') {
            // Chart 1: Applications Over Time (Line)
            const dates = data.applications_over_time.map(d => d.date);
            const counts = data.applications_over_time.map(d => d.count);
            
            chart1Instance = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Applications',
                        data: counts,
                        borderColor: '#3B82F6',
                        tension: 0.3,
                        fill: true,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)'
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });

            // Chart 2: Vacancy Status (Doughnut)
            const statuses = data.vacancy_status.map(s => s.status);
            const statusCounts = data.vacancy_status.map(s => s.count);

            chart2Instance = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: statuses,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: ['#10B981', '#EF4444', '#F59E0B', '#6366F1']
                    }]
                }
            });
        } 
        else if (currentReport === 'financial_summary') {
             // Chart 1: Cost by Type (Bar)
             const types = data.cost_by_type.map(d => d.vacancy_type);
             const costs = data.cost_by_type.map(d => d.total);

             chart1Instance = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: types,
                    datasets: [{
                        label: 'Total Cost',
                        data: costs,
                        backgroundColor: '#8B5CF6'
                    }]
                }
            });
        }
        else if (currentReport === 'inventory') {
             // Chart 1: Eligibility (Pie)
             const labels = data.eligibility_breakdown.map(d => d.qs_eligibility || 'N/A');
             const vals = data.eligibility_breakdown.map(d => d.count);

             chart1Instance = new Chart(ctx1, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: vals,
                        backgroundColor: ['#EC4899', '#8B5CF6', '#3B82F6', '#10B981', '#F59E0B']
                    }]
                }
            });
        }
    }

    function renderTable(data) {
        const thead = document.querySelector('#data-table thead');
        const tbody = document.querySelector('#data-table tbody');
        thead.innerHTML = '';
        tbody.innerHTML = '';

        if (currentReport === 'recruitment_performance') {
            // We can show raw application logs or vacancy summary
            thead.innerHTML = `<tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
            </tr>`;
            
            data.applications_over_time.slice(0, 10).forEach(row => {
                tbody.innerHTML += `<tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.date}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${row.count}</td>
                </tr>`;
            });
        } else if (currentReport === 'financial_summary') {
            thead.innerHTML = `<tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monthly Salary</th>
            </tr>`;
            
            data.top_salaries.forEach(row => {
                tbody.innerHTML += `<tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${row.position_title}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${row.vacancy_type}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱${Number(row.monthly_salary).toLocaleString()}</td>
                </tr>`;
            });
        } else if (currentReport === 'user_activity') {
            thead.innerHTML = `<tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
            </tr>`;
            
            data.recent_logs.forEach(row => {
                tbody.innerHTML += `<tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(row.created_at).toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${row.causer ? row.causer.name : 'System'}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">${row.description}</td>
                </tr>`;
            });
        }
    }
</script>
@endpush
@endsection
