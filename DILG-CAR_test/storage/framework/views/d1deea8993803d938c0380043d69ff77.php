<!-- resources/views/partials/mobile-sidebar.blade.php -->
<div 
    x-show="mobileSidebarOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed inset-0 z-50 flex lg:hidden"
    style="display: none;"
>
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="mobileSidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside class="relative w-72 bg-white h-full shadow-xl flex flex-col justify-between z-50">
        <!-- Header -->
        <div class="flex items-center gap-3 p-4 border-b border-gray-200">
            <img src="<?php echo e(asset('images/dilg_logo.png')); ?>" alt="DILG Logo" class="h-10 w-10 rounded-full" />
            <div class="font-bold text-sm text-[#002C76] font-montserrat leading-tight">
                DILG - CAR <br>
                <span class="text-xs font-medium tracking-tight">RECRUITMENT SELECTION AND PLACEMENT PORTAL</span>
            </div>
            <button @click="mobileSidebarOpen = false" class="ml-auto text-gray-600 hover:text-gray-800">
                <i data-feather="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Links -->
        <nav class="flex-1 overflow-y-auto font-montserrat p-4 space-y-2">
            <?php if (isset($component)) { $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-nav-link','data' => ['icon' => 'home','label' => 'Home','active' => request()->routeIs('dashboard_user'),'href' => ''.e(route('dashboard_user')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','label' => 'Home','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard_user')),'href' => ''.e(route('dashboard_user')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $attributes = $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $component = $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-nav-link','data' => ['icon' => 'archive','label' => 'Job Vacancies','active' => request()->routeIs('job_vacancy'),'href' => ''.e(route('job_vacancy')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'archive','label' => 'Job Vacancies','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('job_vacancy')),'href' => ''.e(route('job_vacancy')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $attributes = $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $component = $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-nav-link','data' => ['icon' => 'user','label' => 'My Applications','active' => request()->routeIs('my_applications'),'href' => ''.e(route('my_applications')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'user','label' => 'My Applications','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('my_applications')),'href' => ''.e(route('my_applications')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $attributes = $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $component = $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-nav-link','data' => ['icon' => 'file-text','label' => 'Personal Data Sheet','active' => request()->routeIs('display_c1'),'href' => ''.e(route('display_c1')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'file-text','label' => 'Personal Data Sheet','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('display_c1')),'href' => ''.e(route('display_c1')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $attributes = $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $component = $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-nav-link','data' => ['icon' => 'info','label' => 'About This Website','active' => request()->routeIs('about'),'href' => ''.e(route('about')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'info','label' => 'About This Website','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('about')),'href' => ''.e(route('about')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $attributes = $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $component = $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
        </nav>
        
    </aside>
</div>

<!-- No mobile logout; manage from profile menu -->
<?php /**PATH C:\xampp\htdocs\DILG-CAR_pp\resources\views/partials/mobile-sidebar.blade.php ENDPATH**/ ?>