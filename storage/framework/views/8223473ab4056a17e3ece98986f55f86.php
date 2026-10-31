<?php $__env->startSection('title', 'Keuangan & Piutang Usaha (AR Aging)'); ?>
<?php $__env->startSection('page_title', 'Faktur Penjualan & Pemantauan Piutang'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Piutang & Penagihan (AR)</h1>
            <p class="text-xs text-slate-500">Penerbitan faktur pajak, rekonsiliasi kas/bank, dan pemantauan jatuh tempo tagihan pelanggan.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="<?php echo e(route('finance.ar-aging.export')); ?>"
               class="px-3.5 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Unduh CSV Aging</span>
            </a>

            <a href="<?php echo e(route('finance.invoices.create')); ?>"
               class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-blue-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Terbitkan Faktur</span>
            </a>

            <a href="<?php echo e(route('finance.payments.create')); ?>"
               class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-emerald-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>+ Catat Pembayaran</span>
            </a>
        </div>
    </div>

    <!-- Alerts -->
    <?php if(session('success')): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

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
                <span class="text-lg font-bold text-slate-800 mt-1 block font-mono">Rp <?php echo e(number_format($aging['current_0_to_30_days'], 0, ',', '.')); ?></span>
                <span class="text-[11px] text-emerald-600 font-semibold">Dalam batas wajar TOP</span>
            </div>
            <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200/70">
                <span class="text-xs font-medium text-amber-800 block">Jatuh Tempo (31 - 60 Hari)</span>
                <span class="text-lg font-bold text-amber-900 mt-1 block font-mono">Rp <?php echo e(number_format($aging['overdue_31_to_60_days'], 0, ',', '.')); ?></span>
                <span class="text-[11px] text-amber-700 font-semibold">Follow up penagih</span>
            </div>
            <div class="p-4 rounded-xl bg-orange-50/60 border border-orange-200/70">
                <span class="text-xs font-medium text-orange-800 block">Kritis (61 - 90 Hari)</span>
                <span class="text-lg font-bold text-orange-900 mt-1 block font-mono">Rp <?php echo e(number_format($aging['overdue_61_to_90_days'], 0, ',', '.')); ?></span>
                <span class="text-[11px] text-orange-700 font-semibold">Peringatan kredit toko</span>
            </div>
            <div class="p-4 rounded-xl bg-rose-50/60 border border-rose-200/70">
                <span class="text-xs font-medium text-rose-800 block">Macet (> 90 Hari)</span>
                <span class="text-lg font-bold text-rose-900 mt-1 block font-mono">Rp <?php echo e(number_format($aging['overdue_over_90_days'], 0, ',', '.')); ?></span>
                <span class="text-[11px] text-rose-700 font-semibold">Kunci pesanan (Hold)</span>
            </div>
        </div>
    </div>

    <!-- Tabel Faktur Penjualan (Invoices) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Faktur Penjualan (Invoices)</h3>
                <p class="text-xs text-slate-500">Mencakup faktur komersial dan faktur pajak saluran GT & MT</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Nomor Faktur</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4 text-center">Saluran</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-right">Nilai Faktur</th>
                        <th class="py-3 px-4 text-right">Sisa Tagihan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4 font-bold text-blue-600 font-mono">
                            <a href="<?php echo e(route('finance.invoices.show', $inv->id)); ?>" class="hover:underline">
                                <?php echo e($inv->invoice_number); ?>

                            </a>
                            <span class="block text-[10px] text-slate-400 font-normal">NSFP: <?php echo e($inv->tax_invoice_number ?? '-'); ?></span>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-800">
                            <?php echo e($inv->customer?->name ?? $inv->customer_name); ?>

                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <?php if(($inv->customer?->channel ?? $inv->customer_channel) === 'GT'): ?>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">GT</span>
                            <?php else: ?>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300">MT</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">
                            <?php echo e(is_string($inv->due_date) ? $inv->due_date : $inv->due_date?->format('d M Y')); ?>

                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-700 font-mono">Rp <?php echo e(number_format($inv->grand_total, 0, ',', '.')); ?></td>
                        <td class="py-3.5 px-4 text-right font-bold text-rose-600 font-mono">Rp <?php echo e(number_format($inv->balance_due, 0, ',', '.')); ?></td>
                        <td class="py-3.5 px-4 text-center">
                            <?php if($inv->status === 'PAID'): ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Lunas</span>
                            <?php elseif($inv->status === 'OVERDUE'): ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">Jatuh Tempo</span>
                            <?php else: ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Belum Lunas</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-1">
                            <a href="<?php echo e(route('finance.invoices.show', $inv->id)); ?>" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                                Detail
                            </a>
                            <a href="<?php echo e(route('finance.invoices.print', $inv->id)); ?>" target="_blank" class="px-2 py-1 rounded border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold">
                                🖨️ Cetak
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">Tidak ada data faktur.</td>
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
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Riwayat Pembayaran & Rekonsiliasi Kas</h3>
                <p class="text-xs text-slate-500">Catatan penerimaan pembayaran tunai, transfer bank, dan warkat giro</p>
            </div>
            <a href="<?php echo e(route('finance.payments.create')); ?>" class="text-xs text-emerald-600 font-bold hover:underline">
                + Tambah Pembayaran
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Nomor Kuitansi</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4 text-center">Metode</th>
                        <th class="py-3 px-4 text-right">Jumlah Bayar</th>
                        <th class="py-3 px-4">Referensi Bank / Cek</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-900 font-mono"><?php echo e($p->payment_number); ?></td>
                        <td class="py-3.5 px-4 font-semibold text-slate-800"><?php echo e($p->customer?->name ?? $p->customer_name); ?></td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                <?php echo e($p->payment_method); ?>

                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-emerald-600 font-mono">Rp <?php echo e(number_format($p->amount, 0, ',', '.')); ?></td>
                        <td class="py-3.5 px-4 text-slate-600"><?php echo e($p->reference_no_giro ?? $p->bank_name ?? '-'); ?></td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <?php echo e($p->giro_status ?? 'CLEARED'); ?>

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