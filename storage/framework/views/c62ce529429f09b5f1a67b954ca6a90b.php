<?php $__env->startSection('title', 'Detail Faktur Penjualan ' . $invoice->invoice_number); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="<?php echo e(route('finance.index')); ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Keuangan & Faktur
        </a>

        <div class="flex items-center gap-2">
            <?php if(in_array($invoice->status, ['UNPAID', 'PARTIAL', 'OVERDUE'])): ?>
                <a href="<?php echo e(route('finance.payments.create')); ?>?customer_id=<?php echo e($invoice->customer_id); ?>"
                   class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-sm transition">
                    💰 Catat Pembayaran
                </a>
            <?php endif; ?>

            <a href="<?php echo e(route('finance.invoices.print', $invoice->id)); ?>" target="_blank"
               class="px-4 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 transition">
                🖨️ Cetak Faktur & Pajak
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-6">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Faktur Penjualan Resmi & Pajak</span>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-1"><?php echo e($invoice->invoice_number); ?></h2>
                <div class="text-xs text-slate-500 mt-1 space-x-2">
                    <span>Tgl Faktur: <b class="text-slate-700"><?php echo e($invoice->invoice_date?->format('d M Y')); ?></b></span>
                    <span>•</span>
                    <span>Jatuh Tempo: <b class="text-rose-600"><?php echo e($invoice->due_date?->format('d M Y')); ?></b></span>
                </div>
            </div>

            <div class="text-left sm:text-right space-y-1">
                <div>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                        <?php echo e($invoice->status === 'PAID' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ''); ?>

                        <?php echo e($invoice->status === 'PARTIAL' ? 'bg-blue-100 text-blue-800 border border-blue-300' : ''); ?>

                        <?php echo e(in_array($invoice->status, ['UNPAID', 'OVERDUE']) ? 'bg-amber-100 text-amber-800 border border-amber-300' : ''); ?>">
                        Status: <?php echo e($invoice->status); ?>

                    </span>
                </div>
                <div class="text-[11px] font-mono text-slate-500">
                    NSFP: <strong class="text-slate-800"><?php echo e($invoice->tax_invoice_number ?? '-'); ?></strong>
                </div>
            </div>
        </div>

        <!-- Customer & Reference Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            <div class="p-4 bg-slate-50 rounded-xl space-y-1.5 border border-slate-200">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Ditagihkan Kepada:</span>
                <div class="text-sm font-bold text-slate-900"><?php echo e($invoice->customer?->name); ?></div>
                <div class="text-slate-600"><?php echo e($invoice->customer?->address); ?></div>
                <div class="text-slate-600"><?php echo e($invoice->customer?->city); ?> - <?php echo e($invoice->customer?->province); ?></div>
                <div class="text-slate-500 pt-1">NPWP: <span class="font-mono"><?php echo e($invoice->customer?->tax_id_npwp ?? '00.000.000.0-000.000'); ?></span></div>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl space-y-1.5 border border-slate-200">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Referensi Dokumen:</span>
                <div>Cabang Penerbit: <strong class="text-slate-900"><?php echo e($invoice->branch?->name); ?></strong></div>
                <div>No. Sales Order: <a href="<?php echo e(route('sales.show', $invoice->sales_order_id)); ?>" class="font-mono text-blue-600 font-bold hover:underline"><?php echo e($invoice->salesOrder?->so_number); ?></a></div>
                <div>No. Surat Jalan: 
                    <?php if($invoice->deliveryOrder): ?>
                        <a href="<?php echo e(route('delivery-orders.show', $invoice->deliveryOrder->id)); ?>" class="font-mono text-blue-600 font-bold hover:underline"><?php echo e($invoice->deliveryOrder->do_number); ?></a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
                <div>Syarat Pembayaran: <strong class="text-slate-800"><?php echo e($invoice->customer?->term_of_payment); ?> Hari (TOP)</strong></div>
            </div>
        </div>

        <!-- Financial Summary Cards -->
        <div class="grid grid-cols-3 gap-4">
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-center">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Tagihan</span>
                <span class="text-base font-extrabold text-slate-900 font-mono mt-1 block">Rp <?php echo e(number_format($invoice->grand_total, 0, ',', '.')); ?></span>
            </div>
            <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-center">
                <span class="text-[10px] uppercase font-bold text-emerald-700 block">Sudah Dibayar</span>
                <span class="text-base font-extrabold text-emerald-800 font-mono mt-1 block">Rp <?php echo e(number_format($invoice->paid_amount, 0, ',', '.')); ?></span>
            </div>
            <div class="p-4 bg-rose-50 rounded-xl border border-rose-200 text-center">
                <span class="text-[10px] uppercase font-bold text-rose-700 block">Sisa Piutang (Saldo)</span>
                <span class="text-base font-extrabold text-rose-800 font-mono mt-1 block">Rp <?php echo e(number_format($invoice->balance_due, 0, ',', '.')); ?></span>
            </div>
        </div>

        <!-- Payments History Allocation Table -->
        <?php if($invoice->allocations->isNotEmpty()): ?>
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                    Riwayat Pembayaran Masuk
                </div>
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-500 font-bold uppercase text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4">No. Pembayaran</th>
                            <th class="py-2.5 px-4">Tanggal Bayar</th>
                            <th class="py-2.5 px-4">Metode</th>
                            <th class="py-2.5 px-4 text-right">Nominal Teralokasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__currentLoopData = $invoice->allocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alloc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="py-3 px-4 font-mono font-bold text-blue-600">
                                    <?php echo e($alloc->payment?->payment_number); ?>

                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    <?php echo e($alloc->payment?->payment_date?->format('d M Y')); ?>

                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                        <?php echo e($alloc->payment?->payment_method); ?>

                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                                    Rp <?php echo e(number_format($alloc->allocated_amount, 0, ',', '.')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/finance/invoices/show.blade.php ENDPATH**/ ?>