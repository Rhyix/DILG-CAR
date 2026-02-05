<?php $__env->startSection('title', 'DILG - Applications List'); ?>
<?php $__env->startSection('content'); ?>

<main class="w-full space-y-6">

    <!-- Header with back arrow and title -->
    <section class="flex items-center space-x-4 mb-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full bg-[#0D2B70] text-white rounded-xl text-2xl font-extrabold font-montserrat px-8 py-4 tracking-wide select-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"d="M96 128a128 128 0 1 0 256 0A128 128 0 1 0 96 128zm94.5 200.2l18.6 31L175.8 483.1l-36-146.9c-2-8.1-9.8-13.4-17.9-11.3C51.9 342.4 0 405.8 0 481.3c0 17 13.8 30.7 30.7 30.7l131.7 0c0 0 0 0 .1 0l5.5 0 112 0 5.5 0c0 0 0 0 .1 0l131.7 0c17 0 30.7-13.8 30.7-30.7c0-75.5-51.9-138.9-121.9-156.4c-8.1-2-15.9 3.3-17.9 11.3l-36 146.9L238.9 359.2l18.6-31c6.4-10.7-1.3-24.2-13.7-24.2L224 304l-19.7 0c-12.4 0-20.1 13.6-13.7 24.2z"/></svg>
            <span class="whitespace-nowrap ml-2">APPLICATIONS LIST</span>
        </h1>
    </section>

    
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
    <!-- Search Bar -->
    <form onsubmit="return false;" class="relative w-full max-w-xs">
        <input id="searchInput" type="search" placeholder="Search" aria-label="Search"
            class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1" />
        <svg xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
        </svg>
    </form>

    <!-- Sort Dropdown with Custom Design -->
    <section class="flex flex-wrap gap-2 items-center">
        <p class="text-lg font-bold font-montserrat text-black-600 mr-1">SORT</p>
        <select id="statusFilter"
            class="border border-gray-300 rounded-lg border-2 border-red-400 px-4 py-1 sm:py-2 text-xs sm:text-sm font-montserrat">
            <option value="">ALL</option>
            <option value="open">OPEN</option>
            <option value="closed">CLOSED</option>
        </select>
    </section>
</div>

    <!-- Table Header -->
    <section class="grid grid-cols-[1.4fr_3.2fr_1.2fr_2fr_1.5fr] gap-x-1 bg-[#0D2B70] text-white font-bold rounded-xl py-5 select-none overflow-hidden">
        <div class="flex items-center justify-center">VACANCY ID</div> 
        <div class="flex items-center ml-5">JOB TITLE</div>
        <div class="flex items-center justify-center">STATUS</div>
        <div class="flex items-center justify-center ml-2">REVIEWED APPLICANTS</div>
        <div class="flex items-center justify-center">NEW APPLICANTS</div>
    </section>


    <!-- Backend to be implemented: GO TO THEIR OWN RESPECTIVE LINK -->
    <section class="space-y-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $vacancies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacancy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
        <div class="grid grid-cols-[1.5fr_3.2fr_1.2fr_2fr_1.5fr] gap-x-2 border-2 border-[#0D2B70] rounded-xl py-3 items-center text-[#0D2B70] select-none overflow-x-hidden ">
            
            <!-- Vacancy ID -->
            <div class="font-extrabold ml-10"><?php echo e($vacancy->vacancy_id); ?></div>

            <!-- Job Title and Type -->
            <div>
                <p class="font-extrabold"><?php echo e($vacancy->position_title); ?></p>
                <p class="text-[#0D2B70]/70 text-[0.9rem] italic"><?php echo e($vacancy->vacancy_type); ?></p>
            </div>

            <!-- Status -->
            <div class="flex justify-center items-center gap-3 font-normal">
                <?php
                    $statusColor = match(strtolower($vacancy->status)) {
                        'open' => 'bg-green-600',
                        'closed' => 'bg-red-600',
                        default => 'bg-gray-400'
                    };
                ?>
                <span class="w-5 h-5 rounded-full inline-block <?php echo e($statusColor); ?>"></span>
                <div class="text-center font-semibold uppercase"><?php echo e($vacancy->status); ?></div>
            </div>

            <!-- Reviewed Applicants -->
            <div class="flex justify-center items-center ml-2">
                <button onclick="window.location.href='<?php echo e(route('admin.reviewed', ['vacancy_id' => $vacancy->vacancy_id])); ?>'" 
                    class="use-loader bg-[#00127.0.0.1] hover:opacity-80 transition text-white font-semibold rounded-full flex items-center gap-2 px-4 py-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" stroke-width="1.5" class="size-6 mr-2" viewBox="0 0 640 512"><path d="M144 160A80 80 0 1 0 144 0a80 80 0 1 0 0 160zm368 0A80 80 0 1 0 512 0a80 80 0 1 0 0 160zM0 298.7C0 310.4 9.6 320 21.3 320l213.3 0c.2 0 .4 0 .7 0c-26.6-23.5-43.3-57.8-43.3-96c0-7.6 .7-15 1.9-22.3c-13.6-6.3-28.7-9.7-44.6-9.7l-42.7 0C47.8 192 0 239.8 0 298.7zM320 320c24 0 45.9-8.8 62.7-23.3c2.5-3.7 5.2-7.3 8-10.7c2.7-3.3 5.7-6.1 9-8.3C410 262.3 416 243.9 416 224c0-53-43-96-96-96s-96 43-96 96s43 96 96 96zm65.4 60.2c-10.3-5.9-18.1-16.2-20.8-28.2l-103.2 0C187.7 352 128 411.7 128 485.3c0 14.7 11.9 26.7 26.7 26.7l300.6 0c-2.1-5.2-3.2-10.9-3.2-16.4l0-3c-1.3-.7-2.7-1.5-4-2.3l-2.6 1.5c-16.8 9.7-40.5 8-54.7-9.7c-4.5-5.6-8.6-11.5-12.4-17.6l-.1-.2-.1-.2-2.4-4.1-.1-.2-.1-.2c-3.4-6.2-6.4-12.6-9-19.3c-8.2-21.2 2.2-42.6 19-52.3l2.7-1.5c0-.8 0-1.5 0-2.3s0-1.5 0-2.3l-2.7-1.5zM533.3 192l-42.7 0c-15.9 0-31 3.5-44.6 9.7c1.3 7.2 1.9 14.7 1.9 22.3c0 17.4-3.5 33.9-9.7 49c2.5 .9 4.9 2 7.1 3.3l2.6 1.5c1.3-.8 2.6-1.6 4-2.3l0-3c0-19.4 13.3-39.1 35.8-42.6c7.9-1.2 16-1.9 24.2-1.9s16.3 .6 24.2 1.9c22.5 3.5 35.8 23.2 35.8 42.6l0 3c1.3 .7 2.7 1.5 4 2.3l2.6-1.5c16.8-9.7 40.5-8 54.7 9.7c2.3 2.8 4.5 5.8 6.6 8.7c-2.1-57.1-49-102.7-106.6-102.7zm91.3 163.9c6.3-3.6 9.5-11.1 6.8-18c-2.1-5.5-4.6-10.8-7.4-15.9l-2.3-4c-3.1-5.1-6.5-9.9-10.2-14.5c-4.6-5.7-12.7-6.7-19-3l-2.9 1.7c-9.2 5.3-20.4 4-29.6-1.3s-16.1-14.5-16.1-25.1l0-3.4c0-7.3-4.9-13.8-12.1-14.9c-6.5-1-13.1-1.5-19.9-1.5s-13.4 .5-19.9 1.5c-7.2 1.1-12.1 7.6-12.1 14.9l0 3.4c0 10.6-6.9 19.8-16.1 25.1s-20.4 6.6-29.6 1.3l-2.9-1.7c-6.3-3.6-14.4-2.6-19 3c-3.7 4.6-7.1 9.5-10.2 14.6l-2.3 3.9c-2.8 5.1-5.3 10.4-7.4 15.9c-2.6 6.8 .5 14.3 6.8 17.9l2.9 1.7c9.2 5.3 13.7 15.8 13.7 26.4s-4.5 21.1-13.7 26.4l-3 1.7c-6.3 3.6-9.5 11.1-6.8 17.9c2.1 5.5 4.6 10.7 7.4 15.8l2.4 4.1c3 5.1 6.4 9.9 10.1 14.5c4.6 5.7 12.7 6.7 19 3l2.9-1.7c9.2-5.3 20.4-4 29.6 1.3s16.1 14.5 16.1 25.1l0 3.4c0 7.3 4.9 13.8 12.1 14.9c6.5 1 13.1 1.5 19.9 1.5s13.4-.5 19.9-1.5c7.2-1.1 12.1-7.6 12.1-14.9l0-3.4c0-10.6 6.9-19.8 16.1-25.1s20.4-6.6 29.6-1.3l2.9 1.7c6.3 3.6 14.4 2.6 19-3c3.7-4.6 7.1-9.4 10.1-14.5l2.4-4.2c2.8-5.1 5.3-10.3 7.4-15.8c2.6-6.8-.5-14.3-6.8-17.9l-3-1.7c-9.2-5.3-13.7-15.8-13.7-26.4s4.5-21.1 13.7-26.4l3-1.7zM472 384a40 40 0 1 1 80 0 40 40 0 1 1 -80 0z"/></svg>
                    View Reviewed 
                </button>
            </div>

            <!-- New Applicants -->
        <div class="flex justify-center items-center relative">
            <button onclick="window.location.href='<?php echo e(route('admin.applicants', ['vacancy_id' => $vacancy->vacancy_id])); ?>'" 
                class="use-loader bg-[#2559B1] hover:opacity-80 transition text-white font-semibold rounded-full flex items-center gap-2 px-4 py-2 text-sm relative">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" stroke-width="1.5" class="size-6 mr-2" viewBox="0 0 576 512"><path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/></svg>
                New Applicants
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vacancy->pending_count > 0): ?>
                <span class="absolute top-0 right-0 -mt-1 -mr-1 bg-red-600 text-white text-[10px] font-bold px-2 py-1.5 rounded-full leading-none z-10">
                    <?php echo e($vacancy->pending_count); ?>

                </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>
        </div>
        </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    <?php echo $__env->make('partials.loader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</section>
<script>
    const searchInput = document.getElementById('searchInput');
    let debounceTimeout;

    const statusFilter = document.getElementById('statusFilter');

function getSearchAndStatus() {
    return {
        search: searchInput.value.trim(),
        status: statusFilter.value.trim()
    };
}

searchInput.addEventListener('input', function () {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        const { search, status } = getSearchAndStatus();
        fetchVacancies(search, status);
    }, 300);
});

statusFilter.addEventListener('change', function () {
    const { search, status } = getSearchAndStatus();
    fetchVacancies(search, status);
});


function fetchVacancies(search = '', status = '') {
    const params = new URLSearchParams({
        search: search,
        status: status
    });

    fetch(`/admin/applications_list?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => renderVacancies(data))
        .catch(error => console.error('Error:', error));
    }

    function renderVacancies(vacancies) {
        const container = document.querySelector('section.space-y-4');
        container.innerHTML = '';

        vacancies.forEach(vacancy => {
            const statusColor = {
                'open': 'bg-green-600',
                'closed': 'bg-red-600'
            }[vacancy.status?.toLowerCase()] ?? 'bg-gray-400';

            container.innerHTML += `
            <div class="grid grid-cols-[1.5fr_3.2fr_1.2fr_2fr_1.5fr] gap-x-2 border-2 border-[#0D2B70] rounded-xl py-3 items-center text-[#0D2B70] select-none overflow-x-hidden ">
                <div class="font-extrabold ml-10">${vacancy.vacancy_id}</div>
                <div>
                    <p class="font-extrabold">${vacancy.position_title}</p>
                    <p class="text-[#0D2B70]/70 text-[0.9rem] italic">${vacancy.vacancy_type}</p>
                </div>
                <div class="flex justify-center items-center gap-3 font-normal">
                    <span class="w-5 h-5 rounded-full inline-block ${statusColor}"></span>
                    <div class="text-center font-semibold uppercase">${vacancy.status}</div>
                </div>
                <div class="flex justify-center items-center ml-2">
                    <button onclick="window.location.href='/admin/reviewed/${vacancy.vacancy_id}'"
                        class="bg-[#00127.0.0.1] hover:opacity-80 transition text-white font-semibold rounded-full flex items-center gap-2 px-4 py-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" stroke-width="1.5" class="size-6 mr-2" viewBox="0 0 640 512"><path d="M144 160A80 80 0 1 0 144 0a80 80 0 1 0 0 160zm368 0A80 80 0 1 0 512 0a80 80 0 1 0 0 160zM0 298.7C0 310.4 9.6 320 21.3 320l213.3 0c.2 0 .4 0 .7 0c-26.6-23.5-43.3-57.8-43.3-96c0-7.6 .7-15 1.9-22.3c-13.6-6.3-28.7-9.7-44.6-9.7l-42.7 0C47.8 192 0 239.8 0 298.7zM320 320c24 0 45.9-8.8 62.7-23.3c2.5-3.7 5.2-7.3 8-10.7c2.7-3.3 5.7-6.1 9-8.3C410 262.3 416 243.9 416 224c0-53-43-96-96-96s-96 43-96 96s43 96 96 96zm65.4 60.2c-10.3-5.9-18.1-16.2-20.8-28.2l-103.2 0C187.7 352 128 411.7 128 485.3c0 14.7 11.9 26.7 26.7 26.7l300.6 0c-2.1-5.2-3.2-10.9-3.2-16.4l0-3c-1.3-.7-2.7-1.5-4-2.3l-2.6 1.5c-16.8 9.7-40.5 8-54.7-9.7c-4.5-5.6-8.6-11.5-12.4-17.6l-.1-.2-.1-.2-2.4-4.1-.1-.2-.1-.2c-3.4-6.2-6.4-12.6-9-19.3c-8.2-21.2 2.2-42.6 19-52.3l2.7-1.5c0-.8 0-1.5 0-2.3s0-1.5 0-2.3l-2.7-1.5zM533.3 192l-42.7 0c-15.9 0-31 3.5-44.6 9.7c1.3 7.2 1.9 14.7 1.9 22.3c0 17.4-3.5 33.9-9.7 49c2.5 .9 4.9 2 7.1 3.3l2.6 1.5c1.3-.8 2.6-1.6 4-2.3l0-3c0-19.4 13.3-39.1 35.8-42.6c7.9-1.2 16-1.9 24.2-1.9s16.3 .6 24.2 1.9c22.5 3.5 35.8 23.2 35.8 42.6l0 3c1.3 .7 2.7 1.5 4 2.3l2.6-1.5c16.8-9.7 40.5-8 54.7 9.7c2.3 2.8 4.5 5.8 6.6 8.7c-2.1-57.1-49-102.7-106.6-102.7zm91.3 163.9c6.3-3.6 9.5-11.1 6.8-18c-2.1-5.5-4.6-10.8-7.4-15.9l-2.3-4c-3.1-5.1-6.5-9.9-10.2-14.5c-4.6-5.7-12.7-6.7-19-3l-2.9 1.7c-9.2 5.3-20.4 4-29.6-1.3s-16.1-14.5-16.1-25.1l0-3.4c0-7.3-4.9-13.8-12.1-14.9c-6.5-1-13.1-1.5-19.9-1.5s-13.4 .5-19.9 1.5c-7.2 1.1-12.1 7.6-12.1 14.9l0 3.4c0 10.6-6.9 19.8-16.1 25.1s-20.4 6.6-29.6 1.3l-2.9-1.7c-6.3-3.6-14.4-2.6-19 3c-3.7 4.6-7.1 9.5-10.2 14.6l-2.3 3.9c-2.8 5.1-5.3 10.4-7.4 15.9c-2.6 6.8 .5 14.3 6.8 17.9l2.9 1.7c9.2 5.3 13.7 15.8 13.7 26.4s-4.5 21.1-13.7 26.4l-3 1.7c-6.3 3.6-9.5 11.1-6.8 17.9c2.1 5.5 4.6 10.7 7.4 15.8l2.4 4.1c3 5.1 6.4 9.9 10.1 14.5c4.6 5.7 12.7 6.7 19 3l2.9-1.7c9.2-5.3 20.4-4 29.6 1.3s16.1 14.5 16.1 25.1l0 3.4c0 7.3 4.9 13.8 12.1 14.9c6.5 1 13.1 1.5 19.9 1.5s13.4-.5 19.9-1.5c7.2-1.1 12.1-7.6 12.1-14.9l0-3.4c0-10.6 6.9-19.8 16.1-25.1s20.4-6.6 29.6-1.3l2.9 1.7c6.3 3.6 14.4 2.6 19-3c3.7-4.6 7.1-9.4 10.1-14.5l2.4-4.2c2.8-5.1 5.3-10.3 7.4-15.8c2.6-6.8-.5-14.3-6.8-17.9l-3-1.7c-9.2-5.3-13.7-15.8-13.7-26.4s4.5-21.1 13.7-26.4l3-1.7zM472 384a40 40 0 1 1 80 0 40 40 0 1 1 -80 0z"/></svg>
                        View Reviewed
                    </button>
                </div>
                <div class="flex justify-center items-center relative">
                    <button onclick="window.location.href='/admin/applicants/${vacancy.vacancy_id}'"
                        class="bg-[#2559B1] hover:opacity-80 transition text-white font-semibold rounded-full flex items-center gap-2 px-4 py-2 text-sm relative">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" stroke-width="1.5" class="size-6 mr-2" viewBox="0 0 576 512"><path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/></svg>
                        New Applicants
                        ${(vacancy.pending_count > 0) ? `
                            <span class="absolute top-0 right-0 -mt-1 -mr-1 bg-red-600 text-white text-[10px] font-bold px-2 py-1.5 rounded-full leading-none z-10">
                                ${vacancy.pending_count}
                            </span>` : ''}
                    </button>
                </div>
            </div>`;
        });
    }
</script>
</main>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\PDS-main\resources\views/admin/applications_list.blade.php ENDPATH**/ ?>