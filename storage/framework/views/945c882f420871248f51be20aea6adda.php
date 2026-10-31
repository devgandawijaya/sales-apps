<?php $__env->startSection('title', 'Sesi Opname #' . $opname->opname_number); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><?php echo e($opname->opname_number); ?></h1>
                <?php if($opname->status === 'POSTED'): ?>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">POSTED & ADJUSTED</span>
                <?php elseif($opname->status === 'SUBMITTED'): ?>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">SUBMITTED (MENUNGGU APPROVAL)</span>
                <?php elseif($opname->status === 'IN_PROGRESS'): ?>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 animate-pulse">COUNTING FISIK</span>
                <?php elseif($opname->status === 'REJECTED'): ?>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">REJECTED</span>
                <?php endif; ?>
            </div>
            <p class="text-sm text-slate-500 mt-1"><?php echo e($opname->title); ?> &bull; <?php echo e($opname->warehouse->name ?? '-'); ?> (<?php echo e($opname->branch->name ?? '-'); ?>)</p>
        </div>
        <div>
            <a href="<?php echo e(route('opname.index')); ?>" class="text-sm font-semibold text-slate-600 hover:text-slate-800">
                &larr; Kembali ke Daftar Opname
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('warning')): ?>
        <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm font-medium">
            <?php echo e(session('warning')); ?>

        </div>
    <?php endif; ?>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Batch Diperiksa</span>
            <span class="text-xl font-bold text-slate-800 mt-1 block"><?php echo e($opname->items->count()); ?> Item Batch</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Item Sudah Dihitung</span>
            <span class="text-xl font-bold text-blue-600 mt-1 block"><?php echo e($opname->total_items_counted); ?> Item</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Selisih Unit</span>
            <span class="text-xl font-bold mt-1 block <?php echo e($opname->total_discrepancy_qty != 0 ? 'text-rose-600' : 'text-emerald-600'); ?>">
                <?php echo e($opname->total_discrepancy_qty > 0 ? '+' : ''); ?><?php echo e($opname->total_discrepancy_qty); ?>

            </span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Variansi Nilai COGS</span>
            <span class="text-xl font-bold mt-1 block font-mono <?php echo e($opname->total_cogs_variance < 0 ? 'text-rose-600' : 'text-slate-800'); ?>">
                Rp <?php echo e(number_format($opname->total_cogs_variance, 0, ',', '.')); ?>

            </span>
        </div>
    </div>

    <!-- Manager Approval Panel (if SUBMITTED) -->
    <?php if($opname->isPostable() && $opname->status !== 'POSTED'): ?>
        <div class="bg-purple-50 border border-purple-200 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-purple-900">Otorisasi & Penyesuaian Nilai Persediaan</h3>
                <p class="text-xs text-purple-700 mt-1">Menyetujui sesi ini akan memposting entri penyesuaian (adjustment) ke dalam Buku Besar Stok (Immutable Ledger) secara otomatis.</p>
            </div>
            <div class="flex items-center gap-3">
                <form action="<?php echo e(route('opname.post', $opname->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin memposting penyesuaian stok ini ke Buku Besar?')" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-md shadow-purple-500/20 transition">
                        ✓ Setujui & Posting ke Ledger
                    </button>
                </form>

                <form action="<?php echo e(route('opname.reject', $opname->id)); ?>" method="POST" class="flex gap-2">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="reason" placeholder="Alasan penolakan..." required class="text-xs px-3 py-1.5 border border-purple-300 rounded-lg w-40">
                    <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition">
                        ✕ Tolak
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Count Sheet Form Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-800">Lembar Kerja Hitung Fisik (Count Sheet)</h3>
            <?php if($opname->isEditable()): ?>
                <span class="text-xs text-blue-600 font-semibold">Mode Input Aktif</span>
            <?php endif; ?>
        </div>

        <?php if($opname->isEditable()): ?>
            <form action="<?php echo e(route('opname.count', $opname->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200">
                        <th class="py-3 px-4">SKU & Nama Produk</th>
                        <th class="py-3 px-4">No. Batch</th>
                        <th class="py-3 px-4">Kadaluwarsa</th>
                        <?php if(!$opname->blind_count || $opname->status !== 'IN_PROGRESS'): ?>
                            <th class="py-3 px-4 text-center">Stok Sistem</th>
                        <?php endif; ?>
                        <th class="py-3 px-4 text-center">Hitung Fisik</th>
                        <?php if(!$opname->blind_count || $opname->status !== 'IN_PROGRESS'): ?>
                            <th class="py-3 px-4 text-center">Selisih</th>
                            <th class="py-3 px-4 text-right">Variansi COGS</th>
                        <?php endif; ?>
                        <th class="py-3 px-4">Alasan Selisih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php $__currentLoopData = $opname->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block"><?php echo e($item->product->name ?? '-'); ?></span>
                                <span class="text-[11px] text-slate-400 font-mono"><?php echo e($item->product->sku ?? '-'); ?></span>
                            </td>
                            <td class="py-3 px-4 font-mono font-medium"><?php echo e($item->batch_number); ?></td>
                            <td class="py-3 px-4"><?php echo e($item->expiry_date?->format('d/m/Y')); ?></td>
                            
                            <?php if(!$opname->blind_count || $opname->status !== 'IN_PROGRESS'): ?>
                                <td class="py-3 px-4 text-center font-bold text-slate-800"><?php echo e($item->system_qty); ?></td>
                            <?php endif; ?>

                            <td class="py-3 px-4 text-center">
                                <?php if($opname->isEditable()): ?>
                                    <input type="hidden" name="items[<?php echo e($idx); ?>][item_id]" value="<?php echo e($item->id); ?>">
                                    <input type="number" name="items[<?php echo e($idx); ?>][physical_qty]" value="<?php echo e($item->physical_qty); ?>" min="0" required class="w-20 text-xs px-2 py-1 border border-slate-300 rounded text-center font-bold focus:ring-2 focus:ring-blue-500">
                                <?php else: ?>
                                    <span class="font-bold text-slate-800"><?php echo e($item->physical_qty); ?></span>
                                <?php endif; ?>
                            </td>

                            <?php if(!$opname->blind_count || $opname->status !== 'IN_PROGRESS'): ?>
                                <td class="py-3 px-4 text-center font-bold <?php echo e($item->discrepancy_qty != 0 ? 'text-rose-600' : 'text-slate-400'); ?>">
                                    <?php echo e($item->discrepancy_qty > 0 ? '+' : ''); ?><?php echo e($item->discrepancy_qty); ?>

                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold <?php echo e($item->discrepancy_cogs_value < 0 ? 'text-rose-600' : 'text-slate-800'); ?>">
                                    Rp <?php echo e(number_format($item->discrepancy_cogs_value, 0, ',', '.')); ?>

                                </td>
                            <?php endif; ?>

                            <td class="py-3 px-4">
                                <?php if($opname->isEditable()): ?>
                                    <select name="items[<?php echo e($idx); ?>][reason_code]" class="text-xs border-slate-300 rounded px-2 py-1">
                                        <option value="">-- Alasan (Bila Selisih) --</option>
                                        <option value="MISCOUNT" <?php echo e($item->reason_code == 'MISCOUNT' ? 'selected' : ''); ?>>Salah Hitung Awal</option>
                                        <option value="DAMAGED" <?php echo e($item->reason_code == 'DAMAGED' ? 'selected' : ''); ?>>Barang Rusak</option>
                                        <option value="EXPIRED" <?php echo e($item->reason_code == 'EXPIRED' ? 'selected' : ''); ?>>Kadaluwarsa</option>
                                        <option value="THEFT" <?php echo e($item->reason_code == 'THEFT' ? 'selected' : ''); ?>>Kehilangan Fisik</option>
                                        <option value="FOUND" <?php echo e($item->reason_code == 'FOUND' ? 'selected' : ''); ?>>Barang Ditemukan (+)</option>
                                    </select>
                                <?php else: ?>
                                    <span class="text-xs text-slate-600"><?php echo e($item->reason_code ?? '-'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <?php if($opname->isEditable()): ?>
            <div class="p-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-500/20">
                    Simpan Perhitungan Fisik & Ajukan Verifikasi (SUBMIT)
                </button>
            </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/inventory/opname/show.blade.php ENDPATH**/ ?>