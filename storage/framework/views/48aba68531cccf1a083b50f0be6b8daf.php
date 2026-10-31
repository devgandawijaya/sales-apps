<?php $__env->startSection('title', 'Gudang & Manajemen Stok FEFO'); ?>
<?php $__env->startSection('page_title', 'Persediaan Multi-Gudang & Alokasi FEFO'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Filter Gudang & Pencarian -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="<?php echo e(route('inventory.index')); ?>" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama barang, SKU, atau nomor batch lot..."
                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="sm:col-span-5">
                <select name="warehouse_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                    <option value="">Semua Gudang</option>
                    <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($wh->id); ?>" <?php echo e(request('warehouse_id') == $wh->id ? 'selected' : ''); ?>>
                        <?php echo e($wh->name); ?> (<?php echo e($wh->type); ?>)
                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="sm:col-span-1">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Kartu Stok FEFO -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Kartu Stok Gudang (Prioritas FEFO)</h3>
                <p class="text-xs text-slate-500">Urutan barang siap jual berdasarkan masa kedaluwarsa terdekat</p>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                FEFO Priority Engine
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">Gudang</th>
                        <th class="py-3.5 px-6">SKU & Nama Produk</th>
                        <th class="py-3.5 px-6">Nomor Lot / Batch</th>
                        <th class="py-3.5 px-6 text-center">Kedaluwarsa (Expiry)</th>
                        <th class="py-3.5 px-6 text-center">Status FEFO</th>
                        <th class="py-3.5 px-6 text-right">Stok Fisik</th>
                        <th class="py-3.5 px-6 text-right">Reserved</th>
                        <th class="py-3.5 px-6 text-right">Tersedia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $isExpired = strtotime($s->expiry_date) < time();
                    ?>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-800 block"><?php echo e($s->warehouse_name); ?></span>
                            <span class="text-[10px] text-slate-400 uppercase font-mono"><?php echo e($s->warehouse_type); ?></span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-semibold text-slate-900 block"><?php echo e($s->product_name); ?></span>
                            <span class="font-mono text-[11px] text-slate-400"><?php echo e($s->product_sku); ?></span>
                        </td>
                        <td class="py-4 px-6 font-mono text-slate-700"><?php echo e($s->batch_number); ?></td>
                        <td class="py-4 px-6 text-center whitespace-nowrap <?php echo e($isExpired ? 'text-rose-600 font-bold' : 'text-slate-700'); ?>">
                            <?php echo e($s->expiry_date); ?>

                        </td>
                        <td class="py-4 px-6 text-center">
                            <?php if($isExpired): ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                ⚠️ Kedaluwarsa (Bad Stock)
                            </span>
                            <?php else: ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                ✓ Siap Alokasi (Good Stock)
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-6 text-right font-bold text-slate-900"><?php echo e(number_format($s->qty_on_hand, 0, ',', '.')); ?> <?php echo e($s->base_uom); ?></td>
                        <td class="py-4 px-6 text-right text-amber-600 font-medium"><?php echo e(number_format($s->qty_reserved, 0, ',', '.')); ?></td>
                        <td class="py-4 px-6 text-right font-extrabold text-indigo-600">
                            <?php echo e(number_format($s->qty_on_hand - $s->qty_reserved, 0, ',', '.')); ?> <?php echo e($s->base_uom); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">Tidak ada stok yang sesuai.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($stocks->hasPages()): ?>
        <div class="p-4 border-t border-slate-100">
            <?php echo e($stocks->links()); ?>

        </div>
        <?php endif; ?>
    </div>

    <!-- Stock Transfers Antar Cabang -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Transfer Antar-Cabang & Status In-Transit</h3>
            <p class="text-xs text-slate-500">Pelacakan logistik armada kirim dari gudang asal menuju gudang tujuan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3 px-6">Nomor Transfer</th>
                        <th class="py-3 px-6">Gudang Asal</th>
                        <th class="py-3 px-6">Gudang Tujuan</th>
                        <th class="py-3 px-6">Armada / Pengemudi</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6">Waktu Kirim</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-6 font-bold text-slate-900"><?php echo e($t->transfer_number); ?></td>
                        <td class="py-3.5 px-6 text-slate-700"><?php echo e($t->from_warehouse_name); ?></td>
                        <td class="py-3.5 px-6 text-slate-700"><?php echo e($t->to_warehouse_name); ?></td>
                        <td class="py-3.5 px-6 text-slate-600"><?php echo e($t->driver_name); ?> (<?php echo e($t->vehicle_number); ?>)</td>
                        <td class="py-3.5 px-6 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                <?php echo e($t->status); ?>

                            </span>
                        </td>
                        <td class="py-3.5 px-6 text-slate-500"><?php echo e($t->sent_date); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-500">Belum ada mutasi transfer antar-cabang.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/inventory/index.blade.php ENDPATH**/ ?>