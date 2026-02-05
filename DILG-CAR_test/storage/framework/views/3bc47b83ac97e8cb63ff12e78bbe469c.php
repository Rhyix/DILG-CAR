<!-- resources/views/dashboard_user/job_vacancy.blade.php -->



<?php $__env->startSection('title', 'Job Vacancies'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Add this CSS to your layout or in a <style> tag -->
<style>
/* Mobile-first responsive styles */
@media (max-width: 767px) {  
    /* Filters Section Mobile */
    .filters-mobile {
        display: grid !important;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem !important;
        margin-bottom: 1.5rem;
    }
    
    .filters-mobile select {
        width: 100% !important;
        padding: 0.75rem 0.5rem !important;
        font-size: 0.875rem !important;
        border: 2px solid #0D2B70;
        border-radius: 0.5rem;
        background-color: white;
        font-family: 'Montserrat', sans-serif;
    }
    
    /* Make salary and place filters full width on mobile */
    .filters-mobile select:nth-child(4),
    .filters-mobile select:nth-child(5) {
        grid-column: span 2;
    }
    
    /* Job Vacancies List Mobile */
    .vacancy-list-mobile {
        display: block !important;
        space-y: 1rem;
    }
    
    /* Empty state mobile */
    .empty-state-mobile {
        text-align: center;
        color: #6b7280;
        font-weight: 600;
        font-size: 1.5rem !important;
        margin-top: 2rem;
        font-family: 'Montserrat', sans-serif;
    }
    
    .empty-state-mobile i {
        width: 1.75rem !important;
        height: 1.75rem !important;
        display: inline-block;
        margin-right: 0.5rem;
        color: #9ca3af;
    }
}

/* Tablet adjustments */
@media (min-width: 768px) and (max-width: 1023px) {
    .filters-section {
        gap: 0.75rem !important;
    }
    
    .filters-section select {
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
    }
}
</style>

<!-- Updated HTML with mobile classes -->
<!-- Page Header -->
<section class="flex items-center gap-2 sm:gap-4 ml-12 sm:ml-0" style="margin-top:0">
                <h1 class="w-full max-w-full text-lg sm:text-4xl font-extrabold text-white font-montserrat flex items-center gap-3 bg-[#002C76] px-4 py-2 rounded-lg shadow-md" style="margin-top:0px;">
                    <i data-feather="briefcase" class="w-6 h-6 text-white"></i> Browse Job Vacancies
                </h1>
</section>

<!-- Sorting & Filtering -->
<section class="flex flex-wrap gap-3 sm:gap-4 filters-mobile">
    <select id="sortFilter" class="border-2 border-[#0D2B70] rounded-lg px-6 sm:px-4 py-2 text-sm font-montserrat">
        <option value="latest">LATEST</option>
        <option value="oldest">OLDEST</option>
    </select>
    <select id="statusFilter" class="border-2 border-[#0D2B70] rounded-lg px-6 sm:px-4 py-2 text-sm font-montserrat">
        <option value="">ALL</option>
        <option value="OPEN" selected>OPEN</option>
        <option value="CLOSED">CLOSED</option>
    </select>
    <select id="typeFilter" class="border-2 border-[#0D2B70] rounded-lg px-4 sm:px-4 py-2 text-sm font-montserrat">
        <option value="">Vacancy Type</option>
        <option value="COS">Job Order/Contract of Service</option>
        <option value="Plantilla">Plantilla Item</option>
    </select>
    <select id="salaryFilter" class="border-2 border-[#0D2B70] rounded-lg px-12 sm:px-4 py-2 text-sm font-montserrat text-left">
        <option value="">Monthly Salary</option>
        <option value="10-20">₱10,000 - ₱20,000</option>
        <option value="20-30">₱20,001 - ₱30,000</option>
        <option value="30-40">₱30,001 - ₱40,000</option>
        <option value="40-50">₱40,001 - ₱50,000</option>
        <option value="50-60">₱50,001 - ₱60,000</option>
        <option value="60-70">₱60,001 - ₱70,000</option>
        <option value="70-80">₱70,001 - ₱80,000</option>
        <option value="80-90">₱80,001 - ₱90,000</option>
        <option value="90-100">₱90,001 - ₱100,000</option>
        <option value="100">₱100,000+</option>
        <option value="0">Not Specified</option>
    </select>
    <select id="placeFilter" class="border-2 border-[#0D2B70] rounded-lg px-2 sm:px-4 py-2 text-sm font-montserrat">
        <option value="">Place of Assignment</option>
        <option value="DILG-CAR Regional Office">DILG-CAR Regional Office</option>
        <option value="Apayao Provincial Office">Apayao Provincial Office</option>
        <option value="Abra Provincial Office">Abra Provincial Office</option>
        <option value="Mountain Province Provincial Office">Mountain Province Provincial Office</option>
        <option value="Ifugao Provincial Office">Ifugao Provincial Office</option>
        <option value="Kalinga Provincial Office">Kalinga Provincial Office</option>
        <option value="Benguet Provincial Office">Benguet Provincial Office</option>
        <option value="Baguio City Office">Baguio City Office</option>
    </select>
</section>

<!-- Job Vacancies List -->
<section class="space-y-6 flex flex items-center justify-center flex-wrap mt-6 vacancy-list-mobile" id="vacancy-list">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $vacancies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacancy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
        <?php echo $__env->make('partials.job_vacancy_card', ['vacancy' => $vacancy], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <div class="text-center text-gray-500 font-semibold text-3xl mt-10 empty-state-mobile">
            <i data-feather="info" class="w-7 h-7 inline-block mr-2 text-gray-400 font-montserrat"></i>
            No Job Vacancy
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</section>

<?php echo $__env->make('partials.loader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <script>
            function fetchVacancies() {
                const status = document.getElementById('statusFilter').value;
                const sort = document.getElementById('sortFilter').value;
                const type = document.getElementById('typeFilter').value;
                const salary = document.getElementById('salaryFilter').value;
                const place = document.getElementById('placeFilter').value;
                const loader = document.getElementById('loader');
                loader?.classList.remove('hidden');

                // Build query parameters
                const params = new URLSearchParams({
                    status: status,
                    sort: sort,
                    type: type,
                    salary: salary,
                    place: place
                });

                fetch(`/job-vacancies/filter?${params.toString()}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('vacancy-list').innerHTML = html;
                        feather.replace();
                        loader?.classList.add('hidden');
                    })
                    .catch(() => {
                        alert('Failed to load vacancies.');
                        loader?.classList.add('hidden');
                    });
            }

            // Attach change event listeners to all filters
            document.getElementById('statusFilter').addEventListener('change', fetchVacancies);
            document.getElementById('sortFilter').addEventListener('change', fetchVacancies);
            document.getElementById('typeFilter').addEventListener('change', fetchVacancies);
            document.getElementById('salaryFilter').addEventListener('change', fetchVacancies);
            document.getElementById('placeFilter').addEventListener('change', fetchVacancies);

            window.addEventListener('DOMContentLoaded', () => {
                fetchVacancies();
            });
        </script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\PDS-main\resources\views/dashboard_user/job_vacancy.blade.php ENDPATH**/ ?>