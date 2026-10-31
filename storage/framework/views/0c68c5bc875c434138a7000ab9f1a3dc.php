<?php $__env->startSection('title', 'Surat Jalan (Delivery Orders)'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <span>Logistik & Pergudangan</span>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Surat Jalan (DO)</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Katalog Surat Jalan & Pengiriman (Delivery Orders)</h1>
            <p class="text-xs text-slate-500">Kelola pemenuhan pesanan penjualan, penerbitan surat jalan, penugasan armada dan bukti serah terima (BAST).</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="<?php echo e(route('delivery-orders.create')); ?>" 
               class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Terbitkan Surat Jalan Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if(session('success')): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="p-4 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="<?php echo e(route('delivery-orders.index')); ?>" class="flex flex-1 items-center gap-2 w-full sm:w-auto">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari No. DO, No. SO, atau Pelanggan..." 
                   class="flex-1 max-w-xs px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
            
            <select name="status" class="px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="PREPARED" <?php echo e(request('status') === 'PREPARED' ? 'selected' : ''); ?>>Dipersiapkan (PREPARED)</option>
                <option value="DISPATCHED" <?php echo e(request('status') === 'DISPATCHED' ? 'selected' : ''); ?>>Diberangkatkan (DISPATCHED)</option>
                <option value="DELIVERED" <?php echo e(request('status') === 'DELIVERED' ? 'selected' : ''); ?>>Terkirim (DELIVERED)</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                Filter
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">No. Surat Jalan</th>
                        <th class="py-3 px-4">Referensi Sales Order</th>
                        <th class="py-3 px-4">Pelanggan & Tujuan</th>
                        <th class="py-3 px-4">Armada & Supir</th>
                        <th class="py-3 px-4">Tanggal Kirim</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $deliveryOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $do): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-600">
                                <a href="<?php echo e(route('delivery-orders.show', $do->id)); ?>" class="hover:underline">
                                    <?php echo e($do->do_number); ?>

                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">
                                <a href="<?php echo e(route('sales.show', $do->sales_order_id)); ?>" class="hover:underline text-slate-600">
                                    <?php echo e($do->salesOrder?->so_number ?? '-'); ?>

                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 block"><?php echo e($do->salesOrder?->customer?->name ?? 'N/A'); ?></span>
                                <span class="text-[10px] text-slate-400 block"><?php echo e($do->delivery_type); ?></span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block"><?php echo e($do->driver_name ?? '-'); ?></span>
                                <span class="text-[10px] text-slate-500 font-mono block"><?php echo e($do->vehicle_plate_number ?? '-'); ?></span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <?php echo e($do->delivery_date?->format('d M Y') ?? '-'); ?>

                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <?php if($do->status === 'DELIVERED'): ?>
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        DELIVERED
                                    </span>
                                <?php elseif($do->status === 'DISPATCHED'): ?>
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        DISPATCHED
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                        <?php echo e($do->status); ?>

                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="<?php echo e(route('delivery-orders.show', $do->id)); ?>" 
                                   class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                    Detail
                                </a>
                                <a href="<?php echo e(route('delivery-orders.print', $do->id)); ?>" target="_blank"
                                   class="px-2.5 py-1 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                                    🖨️ Cetak
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada Surat Jalan (Delivery Order) yang diterbitkan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($deliveryOrders->hasPages()): ?>
            <div class="p-4 border-t border-slate-100">
                <?php echo e($deliveryOrders->links()); ?>

            </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/delivery_orders/index.blade.php ENDPATH**/ ?>