<?php $__env->startSection('title', 'Detail Sales Order ' . $order->so_number); ?>
<?php $__env->startSection('page_title', 'Rincian Pesanan Penjualan'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="<?php echo e(route('sales.index')); ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Daftar
        </a>
        <div class="flex items-center gap-2">
            <?php if($order->status === 'PENDING_APPROVAL'): ?>
                <form action="<?php echo e(route('sales.approvals.approve', $order->id)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-sm transition">
                        ✓ Setujui SO
                    </button>
                </form>

                <button type="button" onclick="document.getElementById('modalRejectDetail').classList.remove('hidden')"
                        class="px-4 py-1.5 rounded-xl bg-rose-100 hover:bg-rose-200 text-xs font-bold text-rose-800 transition">
                    ✕ Tolak SO
                </button>
            <?php endif; ?>

            <?php if(in_array($order->status, ['APPROVED', 'PROCESSING'])): ?>
                <a href="<?php echo e(route('delivery-orders.create')); ?>?sales_order_id=<?php echo e($order->id); ?>" 
                   class="px-4 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-xs font-bold text-white shadow-sm transition">
                    📦 Terbitkan Surat Jalan (DO)
                </a>
            <?php endif; ?>

            <button onclick="window.print()" class="px-4 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 transition">
                🖨️ Cetak Dokumen
            </button>
        </div>
    </div>

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

    <?php if($order->status === 'REJECTED' && !empty($order->rejection_reason)): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            <strong>Alasan Penolakan:</strong> <?php echo e($order->rejection_reason); ?>

        </div>
    <?php endif; ?>

    <!-- Main Order Invoice Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-sm space-y-6">
        <!-- Invoice Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-6">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Dokumen Resmi Sales Order</span>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1"><?php echo e($order->so_number); ?></h3>
                <p class="text-xs text-slate-500 mt-1">Tanggal Pesanan: <b class="text-slate-700"><?php echo e($order->order_date); ?></b></p>
            </div>
            <div class="text-left sm:text-right space-y-1">
                <div>
                    <?php if($order->channel === 'GT'): ?>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">General Trade (GT)</span>
                    <?php else: ?>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">Modern Trade (MT)</span>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                        <?php echo e($order->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ''); ?>

                        <?php echo e($order->status === 'PENDING_APPROVAL' ? 'bg-amber-100 text-amber-800 border border-amber-200' : ''); ?>

                        <?php echo e($order->status === 'REJECTED' ? 'bg-rose-100 text-rose-800 border border-rose-200' : ''); ?>

                        <?php echo e(!in_array($order->status, ['APPROVED', 'PENDING_APPROVAL', 'REJECTED']) ? 'bg-slate-100 text-slate-700 border border-slate-200' : ''); ?>">
                        Status: <?php echo e($order->status); ?>

                    </span>
                </div>
            </div>
        </div>

        <!-- 2 Kolom: Pelanggan & Pengiriman -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1.5">
                <span class="font-bold text-slate-500 uppercase text-[10px] tracking-wider block">Informasi Pelanggan</span>
                <p class="text-sm font-bold text-slate-900"><?php echo e($order->customer_name); ?></p>
                <p class="text-slate-500">Kode: <span class="font-mono text-slate-700"><?php echo e($order->customer_code); ?></span></p>
                <p class="text-slate-500">Terms of Payment (TOP): <b class="text-slate-800"><?php echo e($order->term_of_payment); ?> Hari</b></p>
                <?php if($order->po_number_mt): ?>
                <p class="text-blue-700 font-semibold">Nomor PO Resmi: <?php echo e($order->po_number_mt); ?></p>
                <?php endif; ?>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1.5">
                <span class="font-bold text-slate-500 uppercase text-[10px] tracking-wider block">Logistik & Cabang</span>
                <p class="text-sm font-bold text-slate-900"><?php echo e($order->branch_name); ?></p>
                <p class="text-slate-500">Gudang Alokasi: <b class="text-slate-800"><?php echo e($order->warehouse_name); ?></b></p>
                <p class="text-slate-500">Tipe Pesanan: <span class="font-semibold text-slate-700"><?php echo e($order->order_type); ?></span></p>
                <?php if($order->notes): ?>
                <p class="text-slate-500">Catatan: <i class="text-slate-700"><?php echo e($order->notes); ?></i></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Item Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3 px-4">SKU & Produk</th>
                        <th class="py-3 px-4">Satuan (UOM)</th>
                        <th class="py-3 px-4 text-center">Kuantitas</th>
                        <th class="py-3 px-4 text-right">Harga Satuan</th>
                        <th class="py-3 px-4 text-right">Diskon</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="py-4 px-4">
                            <span class="font-bold text-slate-900 block"><?php echo e($item->product_name); ?></span>
                            <span class="font-mono text-[11px] text-slate-500"><?php echo e($item->product_sku); ?></span>
                        </td>
                        <td class="py-4 px-4 text-slate-600"><?php echo e($item->unit_name); ?> (x<?php echo e($item->conversion_factor); ?>)</td>
                        <td class="py-4 px-4 text-center font-bold text-slate-800"><?php echo e($item->qty_ordered); ?></td>
                        <td class="py-4 px-4 text-slate-600">Rp <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?></td>
                        <td class="py-4 px-4 text-right text-rose-600">
                            <?php $itemDisc = $item->gross_amount - $item->net_amount; ?>
                            <?php echo e($itemDisc > 0 ? '-Rp ' . number_format($itemDisc, 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="py-4 px-4 text-right font-bold text-slate-900">Rp <?php echo e(number_format($item->net_amount, 0, ',', '.')); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <!-- Perhitungan Total & PPN -->
        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <div class="w-full sm:w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Kotor:</span>
                    <span>Rp <?php echo e(number_format($order->subtotal, 0, ',', '.')); ?></span>
                </div>
                <div class="flex justify-between text-rose-600">
                    <span>Diskon On-Invoice:</span>
                    <span><?php echo e($order->discount_on_invoice > 0 ? '-Rp ' . number_format($order->discount_on_invoice, 0, ',', '.') : 'Rp 0'); ?></span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Dasar Pengenaan Pajak (DPP):</span>
                    <span>Rp <?php echo e(number_format($order->subtotal - $order->discount_on_invoice, 0, ',', '.')); ?></span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>PPN 11%:</span>
                    <span>Rp <?php echo e(number_format($order->tax_amount, 0, ',', '.')); ?></span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-extrabold text-slate-900">
                    <span>Grand Total:</span>
                    <span class="text-indigo-600">Rp <?php echo e(number_format($order->grand_total, 0, ',', '.')); ?></span>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Reject SO -->
<div id="modalRejectDetail" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-extrabold text-slate-900">Tolak Sales Order</h3>
            <button onclick="document.getElementById('modalRejectDetail').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400">✕</button>
        </div>

        <form action="<?php echo e(route('sales.approvals.reject', $order->id)); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penolakan *</label>
                <textarea name="reason" rows="3" required placeholder="Contoh: Plafon kredit toko habis, diskon melebihi toleransi maksimal 5%..."
                          class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-rose-600 focus:outline-none"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalRejectDetail').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-500/20 transition">
                    Konfirmasi Penolakan &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/sales/show.blade.php ENDPATH**/ ?>