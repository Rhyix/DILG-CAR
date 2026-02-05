<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $__env->yieldContent('title', 'DILG Dashboard'); ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('dilg_logo.png')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <?php
        $metaTitle = 'DILG - CAR Recruitment Selection and Placement Portal';
        $metaDescription = 'Isa ka bang "Matino, Mahusay, at Maaasahan" na manggagawang Pilipino?';
        $metaImage = asset('images/dilg_rsp_thumbnail.png');
        $metaUrl = url()->current();

        if (request()->is('login')) {
            $metaTitle = 'Login - DILG Recruitment Portal';
            $metaDescription = 'Access your account to view job vacancies and submit your application.';
            $metaImage = asset('images/dilg_login_thumbnail.png');
        } elseif (request()->is('jobs/*')) {
            $metaTitle = 'View Job Vacancy - DILG CAR';
            $metaDescription = 'Explore available job opportunities and join our team at DILG CAR.';
        }
    ?>

    <!-- Open Graph Meta -->
    <meta property="og:title" content="<?php echo e($metaTitle); ?>" />
    <meta property="og:description" content="<?php echo e($metaDescription); ?>" />
    <meta property="og:image" content="<?php echo e($metaImage); ?>" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:type" content="image/png" />
    <meta property="og:url" content="<?php echo e($metaUrl); ?>" />
    <meta property="og:type" content="website" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo e($metaTitle); ?>" />
    <meta name="twitter:description" content="<?php echo e($metaDescription); ?>" />
    <meta name="twitter:image" content="<?php echo e($metaImage); ?>" />

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <!-- Libraries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        .font-montserrat {
            font-family: 'Montserrat', sans-serif;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-track {
            background-color: transparent;
        }

        .sidebar-transition {
            transition: width 0.4s ease, padding 0.4s ease;
        }

        .sidebar-text-hidden {
            opacity: 0;
            pointer-events: none;
            width: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-text-visible {
            opacity: 1;
            pointer-events: auto;
            width: auto;
            transition: all 0.3s ease;
        }

        .logo-transition {
            transition: all 0.3s ease;
        }

        .logo-small {
            max-width: 48px;
            max-height: 48px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
        }

        /* Remove number input arrows */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        @media (max-width: 1024px) {
            .sidebar-desktop {
                display: none;
            }
        }
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body x-data="{ mobileSidebarOpen: false, showLogoutModal: false }" class="bg-[#F3F8FF] min-h-screen font-montserrat text-gray-900 overflow-x-hidden">

    <!-- 🔥 Mobile Toggle Button -->
        <button @click="mobileSidebarOpen = true"
                class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-white rounded-full shadow-md mt-4">
            <i data-feather="menu" class="w-5 h-5"></i>
        </button>

    <!-- 📱 Mobile Sidebar (only visible on small screens) -->
    <div class="lg:hidden">
        <?php echo $__env->make('partials.mobile-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <!-- 💻 Main Layout -->
    <div class="flex h-screen w-full overflow-hidden">

        <!-- 🖥️ Desktop Sidebar (hidden on mobile) -->
        <div class="sidebar-desktop">
            <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto ml-2 pt-0 md:ml-20 transition-all duration-300" style="margin-left: 0; padding-left: 18px;">
            <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-200 px-4 sm:px-8 py-3 flex items-center justify-end gap-6">
                <div id="notifBell" class="relative">
                    <button id="notifToggle" aria-label="Notifications" class="relative p-2 rounded hover:bg-gray-100">
                        <i data-feather="bell" class="w-5 h-5"></i>
                        <span id="notifBadge" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center">0</span>
                    </button>
                    <div id="notifMenu" class="hidden absolute right-0 mt-2 w-80 max-h-[360px] overflow-y-auto bg-white shadow-lg rounded-lg border border-gray-200 p-2">
                        <div class="flex items-center justify-between px-2 py-1">
                            <div class="font-semibold">Notifications</div>
                            <button id="notifMarkAll" class="text-xs text-blue-600 hover:underline">Mark all as read</button>
                        </div>
                        <ul id="notifList" class="mt-1"></ul>
                        <button id="notifLoadMore" class="w-full mt-2 text-xs py-2 rounded bg-gray-100 hover:bg-gray-200">Load more</button>
                    </div>
                </div>
                <div class="relative">
                    <button id="profileToggle" aria-label="Profile menu" class="flex items-center gap-2 p-2 rounded hover:bg-gray-100">
                        <?php
                            $u = Auth::user();
                            $avatar = $u->avatar_path ? asset('storage/'.$u->avatar_path) : null;
                            $initials = collect(explode(' ', $u->name))->map(fn($p)=>mb_substr($p,0,1))->join('');
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($avatar): ?>
                            <img src="<?php echo e($avatar); ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold"><?php echo e($initials); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="text-sm font-semibold"><?php echo e($u->name); ?></span>
                        <i data-feather="chevron-down" class="w-4 h-4"></i>
                    </button>
                    <div id="profileMenu" class="hidden absolute right-0 mt-2 w-56 bg-white shadow-lg rounded-lg border border-gray-200 p-2">
                        <a href="<?php echo e(route('profile.show')); ?>" class="block px-3 py-2 text-sm rounded hover:bg-gray-100">View Profile</a>
                        <a href="<?php echo e(route('profile.edit')); ?>" class="block px-3 py-2 text-sm rounded hover:bg-gray-100">Edit Profile</a>
                        <a href="<?php echo e(route('profile.password.form')); ?>" class="block px-3 py-2 text-sm rounded hover:bg-gray-100">Change Password</a>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="w-full text-left px-3 py-2 text-sm rounded hover:bg-gray-100">Logout</button>
                        </form>
                    </div>
                </div>
            </header>
            <div class="p-3 sm:p-10 pt-8 mt-0 sm:mt-1 space-y-10">
            <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>

        <!-- Chatbot -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!request()->routeIs('about')): ?>
    <?php echo $__env->make('partials.chat_ai', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Feather + Sidebar Script -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.effect(() => {
                feather.replace();
            });
        });
        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();
            const notifToggle = document.getElementById('notifToggle');
            const notifMenu = document.getElementById('notifMenu');
            const notifBadge = document.getElementById('notifBadge');
            const notifList = document.getElementById('notifList');
            const notifLoadMore = document.getElementById('notifLoadMore');
            const notifMarkAll = document.getElementById('notifMarkAll');
            let page = 1;
            let loading = false;
            function fetchCount(){
                fetch("<?php echo e(route('notifications.count')); ?>", { headers: {'X-Requested-With':'XMLHttpRequest'} })
                    .then(r=>r.json()).then(d=>{ notifBadge.textContent = d.count; notifBadge.style.display = d.count>0?'flex':'none'; });
            }
            function fetchItems(reset=false){
                if (loading) return; loading = true;
                if (reset) { page = 1; notifList.innerHTML = ''; }
                fetch("<?php echo e(route('notifications.fetch')); ?>?page="+page, { headers: {'X-Requested-With':'XMLHttpRequest'} })
                    .then(r=>r.json()).then(d=>{
                        d.data.forEach(n => {
                            const li = document.createElement('li');
                            li.innerHTML = `<?php echo str_replace("\n",'', view('components.notification-item', ['notification' => (object) ['id'=>'__ID__','data'=>[],'created_at'=>now(),'read_at'=>null]])->render()); ?>`;
                            li.setAttribute('data-id', n.id);
                            li.querySelector('.font-semibold').textContent = n.data.title ?? 'Notification';
                            li.querySelector('.text-sm').textContent = n.data.message ?? '';
                            page = d.current_page + 1;
                            li.addEventListener('click', () => {
                                fetch("<?php echo e(url('/notifications')); ?>/"+n.id+"/read", { method:'POST', headers:{'X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>' } })
                                    .then(()=>{ li.querySelector('span')?.remove(); fetchCount(); });
                                if (n.data.action_url) { window.location.href = n.data.action_url; }
                            });
                            notifList.appendChild(li);
                        });
                        notifLoadMore.style.display = d.next_page_url ? 'block':'none';
                    }).finally(()=>{ loading=false; });
            }
            notifToggle?.addEventListener('click', ()=>{
                notifMenu.classList.toggle('hidden');
                if (!notifMenu.classList.contains('hidden')) { fetchItems(true); fetchCount(); }
            });
            notifLoadMore?.addEventListener('click', ()=> fetchItems(false));
            notifMarkAll?.addEventListener('click', ()=> {
                fetch("<?php echo e(route('notifications.mark_all')); ?>", { method:'POST', headers:{'X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>' } })
                    .then(()=>{ fetchCount(); notifMenu.classList.add('hidden'); });
            });
            const profileToggle = document.getElementById('profileToggle');
            const profileMenu = document.getElementById('profileMenu');
            profileToggle?.addEventListener('click', ()=> profileMenu.classList.toggle('hidden'));
            setInterval(fetchCount, 15000);
            fetchCount();
            const isAuthed = <?php echo json_encode(auth()->check(), 15, 512) ?>;
            const channelId = <?php echo json_encode(auth()->id(), 15, 512) ?>;
            if (window.Echo && isAuthed && channelId) {
                window.Echo.private('notifications.' + channelId).listen('.NewSystemNotification', () => {
                    fetchCount();
                });
            }
        });

        const sidebar = document.getElementById('sidebar');
        const textElements = [
            "sidebarText",
            "textHome",
            "textJobVacancies",
            "textMyApplications",
            "textPersonalDataSheet",
            "textAboutWebsite",
            "textWorkExperience",
            "textLogOut"
        ].map(id => document.getElementById(id));

        const logo = document.querySelector('img[alt="DILG Logo"]');
        const toggleButton = document.getElementById('toggleSidebar');
        let isOpen = localStorage.getItem('sidebarOpen') === 'true';

        function openSidebar() {
            sidebar?.classList.remove('w-16');
            sidebar?.classList.add('w-72');
            logo?.classList.remove('logo-small');
            textElements.forEach(el => {
                el?.classList.remove('sidebar-text-hidden');
                el?.classList.add('sidebar-text-visible');
            });
            isOpen = true;
            localStorage.setItem('sidebarOpen', 'true');
        }

        function closeSidebar() {
            sidebar?.classList.remove('w-72');
            sidebar?.classList.add('w-16');
            logo?.classList.add('logo-small');
            textElements.forEach(el => {
                el?.classList.remove('sidebar-text-visible');
                el?.classList.add('sidebar-text-hidden');
            });
            isOpen = false;
            localStorage.setItem('sidebarOpen', 'false');
        }

        toggleButton?.addEventListener('click', () => {
            isOpen ? closeSidebar() : openSidebar();
        });

        window.addEventListener('DOMContentLoaded', () => {
            if (window.innerWidth >= 1024) {
                // Open sidebar by default if not set in localStorage
                if (isOpen === null) {
                    openSidebar();
                } else {
                    isOpen ? openSidebar() : closeSidebar();
                }
            }
        });
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\DILG-CAR_pp\resources\views/layout/app.blade.php ENDPATH**/ ?>