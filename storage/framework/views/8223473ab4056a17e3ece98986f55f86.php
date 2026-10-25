<?php $__env->startSection('title', 'Keuangan & Piutang Usaha (AR Aging)'); ?>
<?php $__env->startSection('page_title', 'Faktur Penjualan & Pemantauan Piutang'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- AR Aging Breakdown Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Jadwal Penuaan Piutang Usaha (AR Aging Schedule)</h3>
                <p class="text-xs text-slate-500">Klasifikasi risiko penagihan piutang pelanggan berdasarkan tanggal jatuh tempo</p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-500 block">Total Piutang Belum Terbayar:</span>
                <span class="text-xl font-extrabold text-rose-600">Rp <?php echo e(number_format($aging['total_outstanding_ar'], 0, ',', '.')); ?></span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70">
                <span class="text-xs font-medium text-slate-500 block">Lancar (0 - 30 Hari)</span>
                <span class="text-lg font-bold text-slate-800 mt-1 block">Rp <?php echo e(number_format($aging['current_0_to_30_days'], 0, ',', '.')); ?></span>
                <span class="text-[11px] text-emerald-600 font-semibold">Dalam batas wajar TOP</span>
            </div>
            <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200/70">
                <span class="text-xs font-medium text-amber-800 block">Jatuh Tempo (31 - 60 Hari)</span>
                <span class="text-lg font-bold text-amber-900 mt-1 block">Rp <?php echo e(number_format($aging['overdue_31_to_60_days'], 0, ',', '.')); ?></span>
                <span class="text-[11px] text-amber-700 font-semibold">Follow up penagih</span>
            </div>
            <div class="p-4 rounded-xl bg-orange-50/60 border border-orange-200/70">
                <span class="text-xs font-medium text-orange-800 block">Kritis (61 - 90 Hari)</span>
                <span class="text-lg font-bold text-orange-900 mt-1 block">Rp <?php echo e(number_format($aging['overdue_61_to_90_days'], 0, ',', '.')); ?></span>
                <span class="text-[11px] text-orange-700 font-semibold">Peringatan kredit toko</span>
            </div>
            <div class="p-4 rounded-xl bg-rose-50/60 border border-rose-200/70">
                <span class="text-xs font-medium text-rose-800 block">Macet (> 90 Hari)</span>
                <span class="text-lg font-bold text-rose-900 mt-1 block">Rp <?php echo e(number_format($aging['overdue_over_90_days'], 0, ',', '.')); ?></span>
                <span class="text-[11px] text-rose-700 font-semibold">Kunci pesanan (Hold)</span>
            </div>
        </div>
    </div>

    <!-- Tabel Faktur Penjualan (Invoices) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Daftar Faktur Penjualan (Invoices)</h3>
            <p class="text-xs text-slate-500">Mencakup faktur komersial dan faktur pajak saluran GT & MT</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">Nomor Faktur</th>
                        <th class="py-3.5 px-6">Pelanggan</th>
                        <th class="py-3.5 px-6 text-center">Saluran</th>
                        <th class="py-3.5 px-6">Jatuh Tempo</th>
                        <th class="py-3.5 px-6 text-right">Nilai Faktur</th>
                        <th class="py-3.5 px-6 text-right">Sisa Tagihan</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 px-6 font-bold text-slate-900"><?php echo e($inv->invoice_number); ?></td>
                        <td class="py-4 px-6 font-medium text-slate-800"><?php echo e($inv->customer_name); ?></td>
                        <td class="py-4 px-6 text-center">
                            <?php if($inv->customer_channel === 'GT'): ?>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">GT</span>
                            <?php else: ?>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300">MT</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-6 text-slate-600 whitespace-nowrap"><?php echo e($inv->due_date); ?></td>
                        <td class="py-4 px-6 text-right text-slate-700">Rp <?php echo e(number_format($inv->grand_total, 0, ',', '.')); ?></td>
                        <td class="py-4 px-6 text-right font-bold text-rose-600">Rp <?php echo e(number_format($inv->balance_due, 0, ',', '.')); ?></td>
                        <td class="py-4 px-6 text-center">
                            <?php if($inv->status === 'PAID'): ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Lunas</span>
                            <?php elseif($inv->status === 'OVERDUE'): ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">Jatuh Tempo</span>
                            <?php else: ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Belum Lunas</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">Tidak ada data faktur.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($invoices->hasPages()): ?>
        <div class="p-4 border-t border-slate-100">
            <?php echo e($invoices->links()); ?>

        </div>
        <?php endif; ?>
    </div>

    <!-- Tabel Riwayat Pembayaran -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Riwayat Pembayaran & Rekonsiliasi Kas</h3>
            <p class="text-xs text-slate-500">Catatan penerimaan pembayaran tunai, transfer bank, dan warkat giro</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3 px-6">Nomor Kuitansi</th>
                        <th class="py-3 px-6">Pelanggan</th>
                        <th class="py-3 px-6 text-center">Metode</th>
                        <th class="py-3 px-6 text-right">Jumlah Bayar</th>
                        <th class="py-3 px-6">Referensi Bank / Cek</th>
                        <th class="py-3 px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-6 font-bold text-slate-900"><?php echo e($p->payment_number); ?></td>
                        <td class="py-3.5 px-6 text-slate-800"><?php echo e($p->customer_name); ?></td>
                        <td class="py-3.5 px-6 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                <?php echo e($p->payment_method); ?>

                            </span>
                        </td>
                        <td class="py-3.5 px-6 text-right font-bold text-emerald-600">Rp <?php echo e(number_format($p->amount, 0, ',', '.')); ?></td>
                        <td class="py-3.5 px-6 text-slate-600"><?php echo e($p->reference_no_giro ?? $p->bank_name ?? '-'); ?></td>
                        <td class="py-3.5 px-6 text-center">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <?php echo e($p->giro_status ?? 'Diterima'); ?>

                            </span>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-500">Belum ada transaksi pembayaran tercatat.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/finance/index.blade.php ENDPATH**/ ?>