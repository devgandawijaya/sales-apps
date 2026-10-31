<?php $__env->startSection('title', 'Detail Transfer #' . $transfer->transfer_number); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><?php echo e($transfer->transfer_number); ?></h1>
                <?php if($transfer->status === 'COMPLETED'): ?>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">COMPLETED</span>
                <?php elseif($transfer->status === 'IN_TRANSIT'): ?>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 animate-pulse">IN_TRANSIT</span>
                <?php elseif($transfer->status === 'APPROVED'): ?>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">APPROVED</span>
                <?php elseif($transfer->status === 'SUBMITTED'): ?>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">SUBMITTED</span>
                <?php elseif($transfer->status === 'REJECTED'): ?>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">REJECTED</span>
                <?php else: ?>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700"><?php echo e($transfer->status); ?></span>
                <?php endif; ?>
            </div>
            <p class="text-sm text-slate-500 mt-1">Dibuat oleh <?php echo e($transfer->creator->name ?? 'User'); ?> pada <?php echo e($transfer->created_at->format('d M Y H:i')); ?>.</p>
        </div>
        <div>
            <a href="<?php echo e(route('transfers.index')); ?>" class="text-sm font-semibold text-slate-600 hover:text-slate-800">
                &larr; Kembali ke Daftar Transfer
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

    <!-- Workflow Action Panel -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Alur Kerja & Kontrol Otorisasi</h2>
        
        <div class="flex flex-wrap gap-3 items-center">
            <?php if($transfer->isSubmittable()): ?>
                <form action="<?php echo e(route('transfers.submit', $transfer->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        Ajukan untuk Approval &rarr;
                    </button>
                </form>
            <?php endif; ?>

            <?php if($transfer->isApprovable() && $transfer->status !== 'APPROVED'): ?>
                <form action="<?php echo e(route('transfers.approve', $transfer->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        ✓ Setujui Pengiriman (Approve)
                    </button>
                </form>

                <!-- Reject Modal/Form -->
                <form action="<?php echo e(route('transfers.reject', $transfer->id)); ?>" method="POST" class="flex gap-2">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="reason" placeholder="Alasan penolakan..." required class="text-xs px-3 py-1.5 border border-slate-200 rounded-lg w-48">
                    <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition">
                        ✕ Tolak
                    </button>
                </form>
            <?php endif; ?>

            <?php if($transfer->isDispatchable()): ?>
                <form action="<?php echo e(route('transfers.dispatch', $transfer->id)); ?>" method="POST" class="flex flex-wrap gap-2 items-center">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="driver_name" placeholder="Nama Supir" class="text-xs px-3 py-1.5 border border-slate-200 rounded-lg w-36">
                    <input type="text" name="vehicle_number" placeholder="No. Polisi Armada" class="text-xs px-3 py-1.5 border border-slate-200 rounded-lg w-36">
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        🚚 Berangkatkan (Dispatch & Potong Stok Asal)
                    </button>
                </form>
            <?php endif; ?>

            <?php if($transfer->isReceivable()): ?>
                <button onclick="document.getElementById('receive-form-panel').classList.toggle('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    📦 Proses Penerimaan Fisik di Tujuan &rarr;
                </button>
            <?php endif; ?>

            <?php if($transfer->status === 'COMPLETED'): ?>
                <div class="text-xs font-medium text-emerald-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Transfer telah selesai diterima oleh <?php echo e($transfer->receiver->name ?? 'Staff'); ?> pada <?php echo e($transfer->received_date?->format('d M Y H:i')); ?>.
                </div>
            <?php endif; ?>

            <?php if($transfer->status === 'REJECTED'): ?>
                <div class="text-xs font-medium text-rose-700">
                    Ditolak: <?php echo e($transfer->rejection_reason); ?>

                </div>
            <?php endif; ?>
        </div>

        <!-- Receive Form Panel (if In Transit) -->
        <?php if($transfer->isReceivable()): ?>
            <div id="receive-form-panel" class="hidden mt-6 p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                <h3 class="text-sm font-bold text-slate-800">Konfirmasi Penerimaan Barang di Gudang Tujuan</h3>
                <form action="<?php echo e(route('transfers.receive', $transfer->id)); ?>" method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-slate-500 font-semibold border-b border-slate-200">
                                    <th class="py-2">Produk</th>
                                    <th class="py-2">Batch</th>
                                    <th class="py-2 text-center">Qty Dikirim</th>
                                    <th class="py-2 text-center">Qty Diterima</th>
                                    <th class="py-2">Kondisi Fisik</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php $__currentLoopData = $transfer->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="py-2 font-medium"><?php echo e($item->product->name ?? '-'); ?></td>
                                        <td class="py-2"><?php echo e($item->batch_number); ?></td>
                                        <td class="py-2 text-center font-bold"><?php echo e($item->qty_sent); ?></td>
                                        <td class="py-2 text-center">
                                            <input type="hidden" name="items[<?php echo e($idx); ?>][item_id]" value="<?php echo e($item->id); ?>">
                                            <input type="number" name="items[<?php echo e($idx); ?>][qty_received]" value="<?php echo e($item->qty_sent); ?>" min="0" class="w-20 text-xs px-2 py-1 border border-slate-300 rounded text-center">
                                        </td>
                                        <td class="py-2">
                                            <select name="items[<?php echo e($idx); ?>][condition_status]" class="text-xs border-slate-300 rounded px-2 py-1">
                                                <option value="GOOD">BAIK (Good)</option>
                                                <option value="DAMAGED">RUSAK (Damaged)</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <input type="text" name="received_notes" placeholder="Catatan penerimaan..." class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold">
                        Konfirmasi & Tambah Stok Gudang Tujuan
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Details Card -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Gudang Asal & Tujuan</h3>
            <div class="space-y-2 text-sm">
                <div>
                    <span class="text-xs text-slate-500 block">Gudang Asal:</span>
                    <span class="font-bold text-slate-800"><?php echo e($transfer->fromWarehouse->name ?? '-'); ?></span>
                    <span class="text-xs text-slate-500">(<?php echo e($transfer->fromWarehouse->code ?? '-'); ?>)</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Gudang Tujuan:</span>
                    <span class="font-bold text-blue-700"><?php echo e($transfer->toWarehouse->name ?? '-'); ?></span>
                    <span class="text-xs text-slate-500">(<?php echo e($transfer->toWarehouse->code ?? '-'); ?>)</span>
                </div>
                <?php if($transfer->notes): ?>
                    <div>
                        <span class="text-xs text-slate-500 block">Catatan Pengirim:</span>
                        <span class="text-slate-700"><?php echo e($transfer->notes); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Armada & Tanggal</h3>
            <div class="space-y-2 text-sm">
                <div>
                    <span class="text-xs text-slate-500 block">Supir / Kendaraan:</span>
                    <span class="font-semibold text-slate-800"><?php echo e($transfer->driver_name ?? '-'); ?></span>
                    <span class="text-slate-500">/ <?php echo e($transfer->vehicle_number ?? '-'); ?></span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Waktu Diberangkatkan:</span>
                    <span class="text-slate-700"><?php echo e($transfer->dispatched_at ? $transfer->dispatched_at->format('d M Y H:i:s') : 'Belum Berangkat'); ?></span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Waktu Diterima di Tujuan:</span>
                    <span class="text-slate-700"><?php echo e($transfer->received_date ? $transfer->received_date->format('d M Y H:i:s') : 'Belum Diterima'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-800">Daftar Barang Transfer</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200">
                        <th class="py-3 px-4">SKU & Nama Produk</th>
                        <th class="py-3 px-4">Satuan UOM</th>
                        <th class="py-3 px-4">No. Batch & Kadaluwarsa</th>
                        <th class="py-3 px-4 text-center">Qty Kirim</th>
                        <th class="py-3 px-4 text-center">Qty Terima</th>
                        <th class="py-3 px-4 text-center">Selisih</th>
                        <th class="py-3 px-4 text-center">Kondisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php $__currentLoopData = $transfer->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block"><?php echo e($item->product->name ?? '-'); ?></span>
                                <span class="text-[11px] text-slate-400 font-mono"><?php echo e($item->product->sku ?? '-'); ?></span>
                            </td>
                            <td class="py-3 px-4"><?php echo e($item->productUnit->unit_name ?? 'BASE'); ?></td>
                            <td class="py-3 px-4 font-mono text-[11px]">
                                <?php echo e($item->batch_number); ?>

                                <span class="text-slate-400 block"><?php echo e($item->expiry_date?->format('d/m/Y')); ?></span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800"><?php echo e($item->qty_sent); ?></td>
                            <td class="py-3 px-4 text-center font-bold text-emerald-700"><?php echo e($item->qty_received); ?></td>
                            <td class="py-3 px-4 text-center font-bold <?php echo e($item->discrepancy_qty != 0 ? 'text-rose-600' : 'text-slate-400'); ?>">
                                <?php echo e($item->discrepancy_qty); ?>

                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if($item->condition_status === 'GOOD'): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">BAIK</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">RUSAK</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/inventory/transfers/show.blade.php ENDPATH**/ ?>