<?php $__env->startSection('title', 'Transfer Antar-Gudang (Inter-Branch)'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Transfer Antar-Gudang & Cabang</h1>
            <p class="text-sm text-slate-500 mt-1">Pengiriman multi-gudang dengan kontrol transit (In-Transit Ledger & Strict Arrival Audit).</p>
        </div>
        <div>
            <a href="<?php echo e(route('transfers.create')); ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-md shadow-blue-500/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Buat Transfer Baru
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap gap-3 justify-between items-center bg-slate-50/50">
            <form action="<?php echo e(route('transfers.index')); ?>" method="GET" class="flex flex-wrap gap-3 items-center">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari No. Transfer..." class="text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 w-48">
                <select name="status" class="text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="DRAFT" <?php echo e(request('status') == 'DRAFT' ? 'selected' : ''); ?>>DRAFT</option>
                    <option value="SUBMITTED" <?php echo e(request('status') == 'SUBMITTED' ? 'selected' : ''); ?>>SUBMITTED</option>
                    <option value="APPROVED" <?php echo e(request('status') == 'APPROVED' ? 'selected' : ''); ?>>APPROVED</option>
                    <option value="IN_TRANSIT" <?php echo e(request('status') == 'IN_TRANSIT' ? 'selected' : ''); ?>>IN_TRANSIT</option>
                    <option value="COMPLETED" <?php echo e(request('status') == 'COMPLETED' ? 'selected' : ''); ?>>COMPLETED</option>
                    <option value="REJECTED" <?php echo e(request('status') == 'REJECTED' ? 'selected' : ''); ?>>REJECTED</option>
                </select>
                <button type="submit" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 rounded-lg text-xs font-semibold text-slate-700">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200">
                        <th class="py-3 px-4">No. Transfer</th>
                        <th class="py-3 px-4">Gudang Asal &rarr; Tujuan</th>
                        <th class="py-3 px-4">Armada & Supir</th>
                        <th class="py-3 px-4">Tanggal Pengiriman</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php $__empty_1 = true; $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-blue-600">
                                <a href="<?php echo e(route('transfers.show', $trf->id)); ?>"><?php echo e($trf->transfer_number); ?></a>
                            </td>
                            <td class="py-3 px-4 font-medium">
                                <span class="text-slate-800"><?php echo e($trf->fromWarehouse->name ?? '-'); ?></span>
                                <span class="text-slate-400 mx-1">&rarr;</span>
                                <span class="text-blue-700 font-semibold"><?php echo e($trf->toWarehouse->name ?? '-'); ?></span>
                            </td>
                            <td class="py-3 px-4">
                                <?php echo e($trf->driver_name ?? '-'); ?> 
                                <?php if($trf->vehicle_number): ?>
                                    <span class="text-slate-400">(<?php echo e($trf->vehicle_number); ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                <?php echo e($trf->sent_date ? $trf->sent_date->format('d M Y H:i') : '-'); ?>

                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if($trf->status === 'COMPLETED'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">COMPLETED</span>
                                <?php elseif($trf->status === 'IN_TRANSIT'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">IN_TRANSIT</span>
                                <?php elseif($trf->status === 'APPROVED'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">APPROVED</span>
                                <?php elseif($trf->status === 'SUBMITTED'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">SUBMITTED</span>
                                <?php elseif($trf->status === 'REJECTED'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">REJECTED</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700"><?php echo e($trf->status); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="<?php echo e(route('transfers.show', $trf->id)); ?>" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-semibold">
                                    Detail &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data transfer antar-gudang.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            <?php echo e($transfers->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/inventory/transfers/index.blade.php ENDPATH**/ ?>