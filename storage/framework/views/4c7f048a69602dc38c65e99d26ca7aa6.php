<?php $__env->startSection('title', 'Atur Hak Akses: ' . $role->name); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="<?php echo e(route('roles.index')); ?>" class="hover:text-blue-600 font-semibold">Katalog Role</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold"><?php echo e($role->name); ?></span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan Hak Akses: <?php echo e($role->name); ?></h1>
            <p class="text-xs text-slate-500"><?php echo e($role->description); ?></p>
        </div>

        <div>
            <a href="<?php echo e(route('roles.index')); ?>" 
               class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                &larr; Kembali ke Daftar Role
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    <?php if(session('success')): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('roles.permissions.update', $role->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <!-- Permission Matrix Modules -->
        <div class="space-y-6">
            <?php $__currentLoopData = $permissionsByModule; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $permissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span>Modul: <?php echo e(strtoupper($module)); ?></span>
                        </h3>
                        <span class="text-[11px] text-slate-500"><?php echo e($permissions->count()); ?> Izin Tindakan</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 pt-1">
                        <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isChecked = $role->permissions->contains('id', $perm->id);
                            ?>
                            <label class="flex items-start space-x-2.5 p-3 rounded-xl border <?php echo e($isChecked ? 'border-blue-400 bg-blue-50/40' : 'border-slate-200 hover:bg-slate-50'); ?> transition cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="<?php echo e($perm->id); ?>" <?php echo e($isChecked ? 'checked' : ''); ?>

                                       class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <div class="text-xs">
                                    <span class="font-extrabold text-slate-800 block"><?php echo e($perm->action); ?></span>
                                    <span class="text-[10px] text-slate-500 leading-tight block mt-0.5"><?php echo e($perm->description); ?></span>
                                </div>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="sticky bottom-4 z-20 mt-6 p-4 rounded-2xl bg-slate-900/90 backdrop-blur-md text-white flex items-center justify-between shadow-2xl">
            <span class="text-xs text-slate-300">Pastikan pembatasan hak akses sesuai dengan deskripsi tugas staf.</span>
            <button type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-lg shadow-blue-500/30 transition">
                Simpan Perubahan Hak Akses &rarr;
            </button>
        </div>
    </form>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/roles/show.blade.php ENDPATH**/ ?>