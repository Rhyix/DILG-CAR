@extends('layout.admin')

@section('content')
<main class="w-full space-x-2">
    <div>
        <!-- Title bar -->
        <div class="flex items-center gap-2 sm:gap-4 mb-5">
            <button aria-label="Back" onclick="window.history.back()" class="p-1 sm:p-2 rounded-full bg-[#D9D9D9] hover:bg-[#002C76] h-9 w-9 sm:h-11 sm:w-11 flex items-center justify-center transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-8 sm:w-8 text-[#002c76] hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Title Container -->
        <section class="w-full mb-4">
            <h1 class="flex items-center gap-2 sm:gap-3 w-full border-b-2 border-[#0D2B70] text-white text-2xl sm:text-3xl lg:text-4xl font-montserrat py-2 tracking-wide select-none">
                <span class="text-[#0D2B70]">Generate Report</span>
            </h1>
        </section>
    </div>
    <!-- Filter Section -->
    <section class="bg-[#F1F6FF] p-6 rounded-xl shadow-sm mb-6">
        <form action="{{ route('admin.report.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Report Type -->
                <div>
                    <label for="report_type" class="block text-xs sm:text-sm font-semibold text-[#002C76] mb-2">Report Type</label>
                    <select name="report_type" id="report_type" class="w-full px-2 sm:px-3 py-2 text-xs sm:text-sm border border-[#002C76] rounded-md bg-white text-[#002C76] font-semibold" required>
                        <option value="">Select Report Type</option>
                        <option value="daily">Daily Report</option>
                        <option value="weekly">Weekly Report</option>
                        <option value="monthly">Monthly Report</option>
                    </select>
                </div>

                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-xs sm:text-sm font-semibold text-[#002C76] mb-2">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="w-full px-2 sm:px-3 py-2 text-xs sm:text-sm border border-[#002C76] rounded-md bg-white text-[#002C76] font-semibold" min="2020-01-01" max="2026-12-31" required>
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-xs sm:text-sm font-semibold text-[#002C76] mb-2">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="w-full px-2 sm:px-3 py-2 text-xs sm:text-sm border border-[#002C76] rounded-md bg-white text-[#002C76] font-semibold" min="2020-01-01" max="2026-12-31" required>
                </div>

                <!-- Format -->
                <div>
                    <label for="format" class="block text-xs sm:text-sm font-semibold text-[#002C76] mb-2">Export Format</label>
                    <select name="format" id="format" class="w-full px-2 sm:px-3 py-2 text-xs sm:text-sm border border-[#002C76] rounded-md bg-white text-[#002C76] font-semibold" required>
                        <option value="excel">Excel</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4">
                <button type="submit" class="bg-green-600 hover:bg-green-800 transition text-white text-sm sm:text-base font-semibold rounded-full px-4 sm:px-6 py-2 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-download"></i> Export Report
                </button>
                <button type="button" onclick="filterAndPreview()" class="bg-[#2559B1] hover:bg-blue-900 text-white text-sm sm:text-base font-semibold rounded-full px-4 sm:px-6 py-2 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-eye"></i> Preview
                </button>
                <a href="{{ route('dashboard_admin') }}" class="bg-gray-600 hover:bg-gray-800 text-white text-sm sm:text-base font-semibold rounded-full px-4 sm:px-6 py-2 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-left-long"></i> Back
                </a>
            </div>
        </form>
    </section>

    <!-- Table Header -->
    <section class="grid grid-cols-3 sm:grid-cols-5 gap-2 sm:gap-4 bg-[#0D2B70] text-white font-bold rounded-md py-3 sm:py-5 px-3 sm:px-6 select-none w-full mt-4 sm:mt-6">
        <div class="flex items-center justify-start text-xs sm:text-sm">ID</div>
        <div class="flex items-center justify-start text-xs sm:text-sm">APPLICANT</div>
        <div class="hidden sm:flex items-center justify-start text-xs sm:text-sm">VACANCY</div>
        <div class="flex items-center justify-center text-xs sm:text-sm">STATUS</div>
        <div class="flex items-center justify-center text-xs sm:text-sm">DATE</div>
        <div class="flex items-center justify-center">APPLIED DATE</div>
    </section>

    <!-- Table Rows -->
    <section class="space-y-3 w-full mt-4" id="applicationsTable">
        @php
            $applications = [];
        @endphp

        @if(count($applications) === 0)
            <section class="grid grid-cols-5 gap-4 bg-gray-100 border-b-4 border-[#0D2B70] rounded-md py-4 px-6 shadow-md items-center text-center text-gray-500">
                <div colspan="5" class="col-span-5 text-center py-8">
                    <p class="text-lg font-semibold">No applications found. Filter and preview to see results.</p>
                </div>
            </section>
        @else
            @foreach ($applications as $app)
                <section class="grid grid-cols-3 sm:grid-cols-5 gap-2 sm:gap-4 bg-white border-b-4 border-[#0D2B70] rounded-lg sm:rounded-2xl py-2 sm:py-4 px-3 sm:px-6 shadow-md items-center">
                    <div class="font-semibold text-xs sm:text-sm text-[#002C76]">{{ $app->id }}</div>
                    <div class="font-semibold text-xs sm:text-sm text-[#002C76] truncate">
                        @if($app->personalInformation)
                            {{ trim(($app->personalInformation->first_name ?? '') . ' ' . ($app->personalInformation->surname ?? '')) ?: 'N/A' }}
                        @else
                            {{ $app->user->name ?? 'N/A' }}
                        @endif
                    </div>
                    <div class="hidden sm:block text-xs sm:text-sm text-[#002C76]">{{ $app->vacancy?->position_title ?? 'N/A' }}</div>
                    <div class="flex justify-center">
                        @php
                            $statusColor = match($app->status) {
                                'Pending' => 'yellow',
                                'Incomplete' => 'red',
                                'Approved' => 'green',
                                'Rejected' => 'red',
                                default => 'gray'
                            };
                        @endphp
                        <span class="px-2 sm:px-3 py-1 rounded-full text-white font-semibold text-xs sm:text-sm bg-{{ $statusColor }}-500">
                            {{ $app->status ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="text-xs sm:text-sm text-[#002C76] text-center">{{ $app->created_at?->format('d/m/Y') ?? 'N/A' }}</div>
                </section>
            @endforeach
        @endif
    </section>
</main>

<script>
    function filterAndPreview() {
        const reportType = document.getElementById('report_type').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        if (!reportType || !startDate || !endDate) {
            alert('Please fill in all filter fields');
            return;
        }
        alert('Palo');
    }
</script>
@endsection