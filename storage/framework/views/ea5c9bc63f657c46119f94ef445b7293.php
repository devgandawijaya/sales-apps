<?php $__env->startSection('title', 'Master Pelanggan & Kredit'); ?>
<?php $__env->startSection('page_title', 'Segmentasi Pelanggan GT & MT'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Direktori Pelanggan Distribusi</h3>
            <p class="text-xs text-slate-500">Kelola segmentasi General Trade (GT), Modern Trade (MT), kontrak MTA, dan limit kredit.</p>
        </div>
        <a href="<?php echo e(route('customers.create')); ?>" 
           class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-lg shadow-blue-500/20 transition">
            + Tambah Pelanggan Baru
        </a>
    </div>

    <!-- Top KPI Cards Pelanggan -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pelanggan</span>
            <h3 class="text-2xl font-extrabold text-slate-900 mt-1"><?php echo e($stats['total']); ?></h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Aktif Terdaftar</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">General Trade (GT)</span>
            <h3 class="text-2xl font-extrabold text-emerald-600 mt-1"><?php echo e($stats['gt_count']); ?> <span class="text-xs font-normal text-slate-500">Toko/Warung</span></h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Taking Order & Kanvas</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Modern Trade (MT)</span>
            <h3 class="text-2xl font-extrabold text-blue-600 mt-1"><?php echo e($stats['mt_count']); ?> <span class="text-xs font-normal text-slate-500">DC / Chains</span></h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Super Indo, Alfamart, dll.</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Plafon Kredit</span>
            <h3 class="text-xl font-extrabold text-indigo-600 mt-1">Rp <?php echo e(number_format($stats['total_credit_limit'], 0, ',', '.')); ?></h3>
            <span class="text-[11px] text-amber-600 font-semibold mt-1 block">Terpakai: Rp <?php echo e(number_format($stats['total_credit_used'], 0, ',', '.')); ?></span>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="<?php echo e(route('customers.index')); ?>" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama toko, kode pelanggan, kota, atau pemilik..."
                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="sm:col-span-3">
                <select name="channel" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                    <option value="">Semua Saluran</option>
                    <option value="GT" <?php echo e(request('channel') === 'GT' ? 'selected' : ''); ?>>General Trade (GT)</option>
                    <option value="MT" <?php echo e(request('channel') === 'MT' ? 'selected' : ''); ?>>Modern Trade (MT)</option>
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="branch_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                    <option value="">Semua Cabang</option>
                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($b->id); ?>" <?php echo e(request('branch_id') == $b->id ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="sm:col-span-1">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition">
                    Cari
                </button>
            </div>
        </form>
    </div>

    <!-- Table Pelanggan -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">Pelanggan</th>
                        <th class="py-3.5 px-6 text-center">Saluran</th>
                        <th class="py-3.5 px-6">Sub-Saluran</th>
                        <th class="py-3.5 px-6">Kota / Cabang</th>
                        <th class="py-3.5 px-6 text-center">TOP</th>
                        <th class="py-3.5 px-6 text-right">Plafon Kredit</th>
                        <th class="py-3.5 px-6 text-right">Kredit Terpakai</th>
                        <th class="py-3.5 px-6">Pemanfaatan</th>
                        <th class="py-3.5 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $pct = $c->credit_limit > 0 ? min(100, round(($c->current_credit_used / $c->credit_limit) * 100)) : 0;
                    ?>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 px-6">
                            <a href="<?php echo e(route('customers.show', $c->id)); ?>" class="font-bold text-slate-900 hover:text-blue-600 block">
                                <?php echo e($c->name); ?>

                            </a>
                            <div class="flex items-center space-x-2 mt-0.5">
                                <span class="font-mono text-[11px] text-slate-400"><?php echo e($c->code); ?></span>
                                <?php if(!$c->is_active): ?>
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-rose-100 text-rose-700">Non-Aktif</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <?php if($c->channel === 'GT'): ?>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">GT</span>
                            <?php else: ?>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300">MT</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-6 text-slate-600"><?php echo e($c->sub_channel); ?></td>
                        <td class="py-4 px-6 text-slate-600"><?php echo e($c->city); ?> • <span class="text-[11px] text-slate-400"><?php echo e($c->branch_name); ?></span></td>
                        <td class="py-4 px-6 text-center font-semibold text-slate-800"><?php echo e($c->term_of_payment); ?> Hari</td>
                        <td class="py-4 px-6 text-right font-medium text-slate-700">Rp <?php echo e(number_format($c->credit_limit, 0, ',', '.')); ?></td>
                        <td class="py-4 px-6 text-right font-bold <?php echo e($c->current_credit_used > 0 ? 'text-amber-600' : 'text-slate-600'); ?>">
                            Rp <?php echo e(number_format($c->current_credit_used, 0, ',', '.')); ?>

                        </td>
                        <td class="py-4 px-6">
                            <div class="w-24 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full <?php echo e($pct > 80 ? 'bg-rose-500' : ($pct > 50 ? 'bg-amber-500' : 'bg-emerald-500')); ?>" style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                            <span class="text-[10px] text-slate-500 mt-1 block"><?php echo e($pct); ?>% terpakai</span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <div class="flex items-center justify-center space-x-1.5">
                                <a href="<?php echo e(route('customers.show', $c->id)); ?>" 
                                   class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                    Detail
                                </a>
                                <a href="<?php echo e(route('customers.edit', $c->id)); ?>" 
                                   class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                    Edit
                                </a>
                                <form action="<?php echo e(route('customers.destroy', $c->id)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus/menonaktifkan pelanggan <?php echo e($c->name); ?>?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-500">Tidak ada data pelanggan yang cocok dengan kriteria pencarian.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($customers->hasPages()): ?>
        <div class="p-4 border-t border-slate-100">
            <?php echo e($customers->links()); ?>

        </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/legacygandawijaya/Documents/SALES/resources/views/customers/index.blade.php ENDPATH**/ ?>