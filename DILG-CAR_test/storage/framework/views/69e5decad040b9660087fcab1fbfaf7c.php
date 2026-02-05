<?php $__env->startSection('title', 'My Applications'); ?>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DILG - Job Vacancies</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<?php $__env->startSection('content'); ?>
<body class="bg-[#F3F8FF] min-h-screen font-sans text-gray-900 overflow-x-hidden">
    <div class="flex min-h-screen w-full">
        <!-- Main Content -->
        <main class="w-full space-y-6 main-content-mobile">
            <!-- Header with Back Button and Title -->
            <section class="flex items-center gap-2 sm:gap-4 ml-12 sm:ml-0 applications-header-mobile">
                <h1 class="w-full max-w-full text-lg sm:text-4xl font-extrabold text-white font-montserrat flex items-center gap-3 bg-[#002C76] px-4 py-2 rounded-lg shadow-md">
                    <i data-feather="folder" class="w-6 h-6 text-white"></i> My Applications
                </h1>
            </section>
            
            <section class="flex flex-wrap gap-4 sort-section-mobile">
                <p class="text-lg font-bold font-montserrat text-black-600 ml-3 sm:ml-0">SORT</p>
                <select id="sortMyApplications" class="border border-gray-300 rounded-lg border-2 border-red-400 px-4 py-1 sm:py-2 text-xs sm:text-sm font-montserrat">
                    <option value="latest">LATEST</option>
                    <option value="oldest">OLDEST</option>
                </select>
            </section>
            
            <!-- Application List -->
            <section id="applicationListContainer" class="space-y-6 application-list-mobile">
                <?php echo $__env->make('partials.application_list_container', ['applications' => $applications], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </section>
            
            <?php echo $__env->make('partials.loader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </main>
    </div>
</body>
<?php $__env->stopSection(); ?>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script>
        document.getElementById('sortMyApplications').addEventListener('change', function () {
            const sortOrder = this.value;

            axios.get('<?php echo e(route("my_applications.sort")); ?>', {
                params: {
                    sort_order: sortOrder
                }
            })
            .then(response => {
                const container = document.getElementById('applicationListContainer');
                container.innerHTML = response.data;
                feather.replace(); // re-render feather icons
            })
            .catch(error => {
                console.error("Failed to sort applications:", error);
            });
        });
        </script>


<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\PDS-main\resources\views/dashboard_user/my_applications.blade.php ENDPATH**/ ?>