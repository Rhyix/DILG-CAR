<?php $__env->startSection('title', '500 Internal Server Error'); ?>

<?php $__env->startSection('code', '500'); ?>
<?php $__env->startSection('heading', 'INTERNAL SERVER ERROR'); ?>
<?php $__env->startSection('message'); ?>
An unexpected error has occurred on the server.<br>
Please try again later or contact the Administrator.
<?php $__env->stopSection(); ?>

<?php echo $__env->make('errors.minimal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\PDS-main\resources\views/errors/500.blade.php ENDPATH**/ ?>