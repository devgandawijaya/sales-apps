<?php $__env->startSection('title', 'Matriks 19 Role & Hak Akses RBAC'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="<?php echo e(route('users.index')); ?>" class="hover:text-blue-600 font-semibold">Pengguna</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Role & Permission Matrix</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Katalog 19 Peran & Matriks Otorisasi Granular</h1>
            <p class="text-xs text-slate-500">Prinsip hak akses minimum (Least Privilege) yang ditegakkan langsung di backend.</p>
        </div>

        <div>
            <a href="<?php echo e(route('users.index')); ?>" 
               class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                &larr; Kembali ke Daftar Pengguna
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    <?php if(session('success')): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between hover:border-blue-400 transition space-y-4">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?php echo e($role->is_system ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-700'); ?>">
                            <?php echo e($role->is_system ? 'System Role' : 'Custom Role'); ?>

                        </span>
                        <span class="text-[11px] font-semibold text-slate-500"><?php echo e($role->users->count()); ?> User</span>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 mt-2"><?php echo e($role->name); ?></h3>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-2"><?php echo e($role->description ?? 'Peran standar sistem distribusi.'); ?></p>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 text-[11px]">
                        <strong><?php echo e($role->permissions->count()); ?></strong> Hak Akses Aktif
                    </span>
                    <a href="<?php echo e(route('roles.show', $role->id)); ?>" 
                       class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-[11px] transition">
                        Atur Akses &rarr;
                    </a>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/roles/index.blade.php ENDPATH**/ ?>