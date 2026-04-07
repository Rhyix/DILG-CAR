<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DILG-CAR Careers</title>
    @vite(['resources/css/app.css', 'resources/js/public-landing.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-blue-100 font-sans text-gray-900">
    @php
        $pageItems = $vacancies->items();

        $vacancyTypeCounts = [];
        $allCount = 0;

        foreach ($pageItems as $vacancy) {
            $closingDate = \Carbon\Carbon::parse($vacancy->closing_date)->setTime(17, 0, 0);
            $now = \Carbon\Carbon::now();
            $isClosed = $now->greaterThan($closingDate);

            if (! $isClosed) {
                $type = strtolower(trim((string) ($vacancy->vacancy_type ?? '')));
                $vacancyTypeCounts[$type] = ($vacancyTypeCounts[$type] ?? 0) + 1;
                $allCount++;
            }
        }

        $plantillaCount = ($vacancyTypeCounts['permanent'] ?? 0) + ($vacancyTypeCounts['plantilla'] ?? 0);
        $cosCount = ($vacancyTypeCounts['cos'] ?? 0) + ($vacancyTypeCounts['contract of service'] ?? 0) + ($vacancyTypeCounts['contract'] ?? 0);
        $ojtCount = ($vacancyTypeCounts['ojt'] ?? 0) + ($vacancyTypeCounts['on-the-job training'] ?? 0);
        $contractualCount = $vacancyTypeCounts['contractual'] ?? 0;

        $documentMetaForLanding = [
            'application_letter' => 'Application Letter',
            'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
            'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
            'photocopy_diploma' => 'Diploma',
            'signed_pds' => 'Signed and Subscribed Personal Data Sheet',
            'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
            'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
            'passport_photo' => '2" x 2" or Passport Size Picture',
            'cert_eligibility' => 'Certificate of Eligibility/Board Rating',
            'ipcr' => 'Certification of Numerical Rating/Performance Rating/IPCR',
            'non_academic' => 'Non-Academic Awards Received',
            'cert_training' => 'Certificates of Training/Participation Relevant to the Position',
            'designation_order' => 'Confirmed Designation Order/s',
            'grade_masteraldoctorate' => 'Certificate of Grades with Masteral/Doctorate Units Earned',
            'tor_masteraldoctorate' => 'TOR with Masteral/Doctorate Degree',
            'cert_employment' => 'Certificate of Employment',
            'other_documents' => 'Other Documents Submitted',
        ];

        $allDocumentTypesForLanding = array_keys($documentMetaForLanding);
        $requiredDocsByTrackForLanding = [
            'COS' => [
                'passport_photo',
                'signed_pds',
                'signed_work_exp_sheet',
                'photocopy_diploma',
                'application_letter',
                'cert_training',
            ],
            'Plantilla' => array_values(array_diff(
                $allDocumentTypesForLanding,
                ['tor_masteraldoctorate', 'grade_masteraldoctorate', 'cert_lgoo_induction', 'other_documents', 'pqe_result']
            )),
        ];

        $icon = static function (string $name, string $classes = 'h-5 w-5'): string {
            $path = match ($name) {
                'x' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />',
                'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
                'file-text' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-8.625a1.125 1.125 0 0 0-1.125-1.125H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5A2.25 2.25 0 0 0 6.75 19.5h8.625a1.125 1.125 0 0 0 1.125-1.125V14.25m-9 1.5h6m-6-3h6m-6-3h3" />',
                'info' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v.01M11.25 12h.75v3h.75m-6 3.75a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" />',
                'log-in' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15" /><path stroke-linecap="round" stroke-linejoin="round" d="M18 12H9m0 0 3-3m-3 3 3 3" />',
                'user-plus' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v6m3-3h-6M15.75 19.128a9.716 9.716 0 0 1-3.75.747c-4.97 0-9-2.239-9-5s4.03-5 9-5a9.716 9.716 0 0 1 3.75.747M12 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />',
                'search' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6 16.65a7.5 7.5 0 0 0 10.65 0Z" />',
                'map-pin' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />',
                'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 5.25h15A1.5 1.5 0 0 1 21 6.75v12A1.5 1.5 0 0 1 19.5 20.25h-15A1.5 1.5 0 0 1 3 18.75v-12A1.5 1.5 0 0 1 4.5 5.25Z" />',
                'arrow-right' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />',
                'inbox' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75A2.25 2.25 0 0 1 5.25 4.5h13.5A2.25 2.25 0 0 1 21 6.75v10.5A2.25 2.25 0 0 1 18.75 19.5H5.25A2.25 2.25 0 0 1 3 17.25V6.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5h4.125a2.25 2.25 0 0 0 2.121 1.5h5.508a2.25 2.25 0 0 0 2.121-1.5H21" />',
                default => '',
            };

            return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="' . e($classes) . '">' . $path . '</svg>';
        };
    @endphp

    <div
        id="landingDocumentConfig"
        data-document-meta='@json($documentMetaForLanding)'
        data-required-docs='@json($requiredDocsByTrackForLanding)'
        hidden
    ></div>

    <div id="documentsModal" class="fixed inset-0 z-50 hidden flex items-center justify-center overflow-y-auto bg-black/50 py-4">
        <div class="mx-4 flex max-h-[90vh] w-full max-w-2xl flex-col rounded-3xl bg-white shadow-2xl">
            <div class="flex shrink-0 items-center justify-between rounded-t-3xl border-b border-gray-200 bg-white p-6">
                <div>
                    <h3 class="text-2xl font-bold text-[#0D2B70]" id="modalJobTitle">Job Position</h3>
                    <p class="mt-1 text-sm text-gray-600" id="modalVacancyType">Vacancy Type</p>
                </div>
                <button type="button" data-close-modal class="shrink-0 text-gray-400 transition-colors hover:text-gray-600">
                    {!! $icon('x', 'h-6 w-6') !!}
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <div class="mb-6">
                    <h4 class="mb-3 flex items-center gap-2 font-bold text-[#0D2B70]">
                        {!! $icon('check-circle', 'h-5 w-5') !!}
                        Qualification Standards
                    </h4>
                    <div class="space-y-3 rounded-xl bg-gray-50 p-4 text-sm">
                        <div class="grid grid-cols-[120px_1fr] gap-2">
                            <span class="font-semibold text-gray-700">Education:</span>
                            <span class="text-gray-600" id="modalEducation"></span>
                        </div>
                        <div class="grid grid-cols-[120px_1fr] gap-2">
                            <span class="font-semibold text-gray-700">Training:</span>
                            <span class="text-gray-600" id="modalTraining"></span>
                        </div>
                        <div class="grid grid-cols-[120px_1fr] gap-2">
                            <span class="font-semibold text-gray-700">Experience:</span>
                            <span class="text-gray-600" id="modalExperience"></span>
                        </div>
                        <div class="grid grid-cols-[120px_1fr] gap-2">
                            <span class="font-semibold text-gray-700">Eligibility:</span>
                            <span class="text-gray-600" id="modalEligibility"></span>
                        </div>
                        <div class="grid hidden grid-cols-[120px_1fr] gap-2" id="modalCompetencyContainer">
                            <span class="font-semibold text-gray-700">Competency:</span>
                            <span class="text-gray-600" id="modalCompetency"></span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h4 class="mb-3 flex items-center gap-2 font-bold text-[#0D2B70]">
                        {!! $icon('file-text', 'h-5 w-5') !!}
                        Required Documents
                    </h4>
                    <div class="rounded-xl bg-blue-50 p-4">
                        <p id="requiredDocumentsHint" class="mb-3 text-xs font-semibold text-gray-500"></p>
                        <ul id="requiredDocumentsList" class="space-y-3"></ul>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="mb-3 flex items-center gap-2 font-bold text-[#0D2B70]">
                        {!! $icon('info', 'h-5 w-5') !!}
                        Additional Information
                    </h4>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p>Ensure all documents are clear and legible.</p>
                        <p>Upload in PDF or image format, maximum 2 MB per file.</p>
                        <p>Incomplete requirements may delay application processing.</p>
                    </div>
                </div>

                <div class="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 p-3">
                    <p class="flex items-center gap-2 text-sm text-yellow-800">
                        {!! $icon('info', 'h-4 w-4') !!}
                        You need to be logged in to apply for this position.
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 items-center justify-end gap-3 rounded-b-3xl border-t border-gray-200 bg-white p-6">
                <button type="button" data-close-modal class="rounded-lg px-4 py-2 font-semibold text-gray-600 transition-colors hover:bg-gray-100">
                    Close
                </button>
                <a href="{{ route('login.form') }}" id="applyNowBtn" class="inline-flex items-center gap-2 rounded-lg bg-[#0D2B70] px-6 py-2 font-bold text-white transition-colors hover:bg-[#002C76]">
                    {!! $icon('log-in', 'h-4 w-4') !!}
                    Login to Apply
                </a>
            </div>
        </div>
    </div>

    <header class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-[#0D2B70] via-[#17439e] to-[#002C76]"></div>
        <div class="absolute -left-20 -top-40 h-80 w-80 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute -bottom-40 right-0 h-96 w-96 rounded-full bg-white/5 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-6 py-8">
            <nav class="mb-12 flex flex-col gap-6 border-b border-white/20 pb-6 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG" class="h-16 w-16 rounded-full border-2 border-white/40 bg-white/10">
                    <div class="flex w-[800px] flex-col rounded-lg px-4 py-2">
                        <span class="text-l font-bold text-white">REPUBLIC OF THE PHILIPPINES</span>
                        <hr>
                        <span class="text-xl font-semibold text-white">DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT - CORDILLERA ADMINISTRATIVE REGION</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('login.form') }}" class="text-sm font-semibold text-white/90 transition-colors hover:text-white">Sign In</a>
                    <a href="{{ route('register.form') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2.5 font-bold text-[#0D2B70] shadow-lg transition-all hover:bg-gray-50 hover:shadow-xl">
                        {!! $icon('user-plus', 'h-4 w-4') !!}
                        Create Account
                    </a>
                </div>
            </nav>

            <div class="max-w-4xl">
                <h1 class="mb-4 text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">
                    Ang DILG ay Matino, Mahusay at Maaasahan
                </h1>
            </div>
        </div>
    </header>

    <main class="flex-1 bg-gradient-to-b from-blue-50 via-indigo-100 to-blue-50">
        <section class="mx-auto max-w-7xl px-6 py-8">
            <div class="rounded-2xl bg-white p-5 shadow-lg sm:p-6 md:p-8">
                <div class="mb-6 flex flex-col gap-5">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-2xl font-bold text-gray-800 sm:text-3xl">Latest Jobs</h2>
                        <span class="hidden items-center rounded-full bg-[#0D2B70]/10 px-3 py-1 text-sm font-semibold text-[#0D2B70] sm:inline-flex">
                            {{ $allCount }} Open {{ $allCount === 1 ? 'Vacancy' : 'Vacancies' }}
                        </span>
                    </div>

                    <div class="flex flex-col flex-wrap items-start gap-4 border-b border-[#0D2B70] sm:flex-row sm:items-center sm:gap-2">
                        <div class="flex flex-1 flex-wrap items-center gap-2" id="filterButtons">
                            <button type="button" class="filter-btn active rounded-full bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white shadow-sm" data-filter="all">All Vacancies ({{ $allCount }})</button>
                            <button type="button" class="filter-btn rounded-full bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-600" data-filter="plantilla">Plantilla ({{ $plantillaCount }})</button>
                            <button type="button" class="filter-btn rounded-full bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-600" data-filter="cos">Contract of Service ({{ $cosCount }})</button>
                            <!-- <button type="button" class="filter-btn rounded-full bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-600" data-filter="ojt">On-the-Job Training ({{ $ojtCount }})</button> -->
                            <!-- <button type="button" class="filter-btn rounded-full bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-600" data-filter="contractual">Contractual ({{ $contractualCount }})</button> -->
                        </div>
                        <div class="relative w-full sm:w-auto">
                            <input
                                type="text"
                                id="searchInput"
                                placeholder="Search job titles..."
                                class="w-full rounded-full border-2 border-gray-200 bg-white px-6 py-3 pr-14 text-sm font-semibold placeholder-gray-400 transition-all focus:border-[#0D2B70] focus:outline-none focus:shadow-md"
                            />
                            <button type="button" id="searchBtn" class="absolute right-1 top-1/2 rounded-full p-2 text-[#0D2B70] transition-all hover:bg-blue-50 active:scale-95">
                                {!! $icon('search', 'h-5 w-5') !!}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2" id="vacancyGrid">
                    @forelse ($vacancies as $vacancy)
                        @php
                            $closingDate = \Carbon\Carbon::parse($vacancy->closing_date)->setTime(17, 0, 0);
                            $now = \Carbon\Carbon::now();
                            $isClosed = $now->greaterThan($closingDate);

                            $typeNormalized = strtolower(trim((string) ($vacancy->vacancy_type ?? '')));
                            $filterType = match ($typeNormalized) {
                                'permanent', 'plantilla' => 'plantilla',
                                'cos', 'contract of service', 'contract' => 'cos',
                                default => 'other',
                            };

                            $vacancyTypeDisplay = match ($typeNormalized) {
                                'cos', 'contract of service', 'contract' => 'Contract of Service Position',
                                'plantilla', 'permanent' => 'Plantilla Position',
                                default => strtoupper($vacancy->vacancy_type ?? '') . ' Position',
                            };
                        @endphp
                        @if (! $isClosed)
                            <article
                                class="vacancy-card cursor-pointer rounded-xl border border-gray-200 bg-white transition-all duration-200 hover:border-[#0D2B70]/40 hover:shadow-md"
                                data-type="{{ $filterType }}"
                                data-vacancy="{{ base64_encode(json_encode([
                                    'vacancy_id' => $vacancy->vacancy_id,
                                    'position_title' => $vacancy->position_title,
                                    'vacancy_type' => $vacancy->vacancy_type,
                                    'qualification_education' => $vacancy->qualification_education,
                                    'qualification_training' => $vacancy->qualification_training,
                                    'qualification_experience' => $vacancy->qualification_experience,
                                    'qualification_eligibility' => $vacancy->qualification_eligibility,
                                    'competencies' => $vacancy->competencies,
                                ])) }}"
                            >
                                <div class="p-5 sm:p-6">
                                    @php
                                        $closingSoonStart = $closingDate->copy()->subDay()->startOfDay();
                                        $isDeadlineSoon = $now->greaterThanOrEqualTo($closingSoonStart) && ! $isClosed;
                                    @endphp
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h3 class="text-lg font-bold text-[#0D2B70] sm:text-xl">{{ $vacancy->position_title }}</h3>
                                            <p class="mt-1 text-sm font-medium text-gray-700 sm:text-base">{{ $vacancy->office_assignment ?? 'DILG - CAR' }}</p>
                                            <p class="mt-1 text-sm italic text-[#0D2B70]/75">{{ $vacancyTypeDisplay }}</p>
                                        </div>
                                        <span class="w-fit whitespace-nowrap rounded-lg bg-blue-50 px-4 py-2 text-base font-bold text-[#0D2B70] sm:text-lg">
                                            @if($vacancy->salary_grade)
                                                @php
                                                    $gradeNum = preg_replace('/[^0-9]/', '', $vacancy->salary_grade);
                                                    if (empty($gradeNum)) {
                                                        $gradeNum = $vacancy->salary_grade;
                                                    }
                                                @endphp
                                                SG {{ $gradeNum }} - PHP {{ number_format((float) ($vacancy->monthly_salary ?? 0), 2) }}
                                            @else
                                                PHP {{ number_format((float) ($vacancy->monthly_salary ?? 0), 2) }}
                                            @endif
                                        </span>
                                    </div>

                                    <div class="mt-5 flex flex-col gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2 text-sm text-gray-600 sm:text-base">
                                                {!! $icon('map-pin', 'h-4 w-4') !!}
                                                <span>{{ $vacancy->place_of_assignment ?? 'DILG-CAR' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-sm text-gray-500 sm:text-base">
                                                {!! $icon('calendar', 'h-4 w-4') !!}
                                                <span>Deadline: {{ \Carbon\Carbon::parse($vacancy->closing_date)->format('F d, Y') }}</span>
                                            </div>
                                        </div>

                                        <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:gap-3">
                                            @if ($isClosed)
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">
                                                    <span class="mr-1.5 h-2 w-2 rounded-full bg-red-500"></span>
                                                    Closed
                                                </span>
                                            @elseif ($isDeadlineSoon)
                                                <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-semibold text-orange-800">
                                                    <span class="mr-1.5 h-2 w-2 rounded-full bg-orange-500"></span>
                                                    Closing Soon
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
                                                    <span class="mr-1.5 h-2 w-2 rounded-full bg-green-500"></span>
                                                    Open
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center gap-2 font-semibold text-[#0D2B70] hover:underline">
                                                View details
                                                {!! $icon('arrow-right', 'h-4 w-4') !!}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endif
                    @empty
                        <div class="flex flex-col items-center justify-center py-12 text-center text-gray-500">
                            <div class="mb-3 rounded-full bg-gray-100 p-4">
                                {!! $icon('inbox', 'h-8 w-8 text-gray-400') !!}
                            </div>
                            <span class="text-lg font-semibold">No Job Vacancy Found</span>
                            <p class="mt-1 text-sm text-gray-400">Please check back soon for new openings.</p>
                        </div>
                    @endforelse
                </div>

                @if ($vacancies->hasPages())
                    <div class="mt-12 flex justify-center">
                        {{ $vacancies->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-300 bg-blue-50">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-6 py-4 text-sm text-gray-600 sm:flex-row">
            <div>&copy; {{ date('Y') }} DILG - CAR</div>
            <div class="flex items-center gap-4">
                <a href="{{ route('job_vacancy') }}" class="font-semibold text-[#0D2B70] hover:underline">Vacancies</a>
                <a href="{{ route('about') }}" class="font-semibold text-[#0D2B70] hover:underline">About</a>
            </div>
        </div>
    </footer>
</body>
</html>
