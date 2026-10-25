<?php $__env->startSection('title', 'Detail Sales Order ' . $order->so_number); ?>
<?php $__env->startSection('page_title', 'Rincian Pesanan Penjualan'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="<?php echo e(route('sales.index')); ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Daftar
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 transition">
                🖨️ Cetak Dokumen
            </button>
        </div>
    </div>

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
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/sales/show.blade.php ENDPATH**/ ?>