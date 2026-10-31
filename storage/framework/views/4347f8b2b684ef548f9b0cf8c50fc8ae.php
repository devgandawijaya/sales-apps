<?php $__env->startSection('title', 'Persetujuan Pesanan (Sales Approvals)'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <span>Otorisasi & Kontrol</span>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Persetujuan Sales Order</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Antrean Persetujuan Pesanan Penjualan</h1>
            <p class="text-xs text-slate-500">Otorisasi pesanan dengan diskon khusus, plafon kredit tinggi, atau kanal Modern Trade sebelum proses pemenuhan gudang.</p>
        </div>

        <div>
            <a href="<?php echo e(route('sales.index')); ?>" 
               class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                &larr; Kembali ke Seluruh Sales Order
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

    <!-- Orders Awaiting Approval Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">No. Sales Order</th>
                        <th class="py-3 px-4">Pelanggan & Kanal</th>
                        <th class="py-3 px-4">Salesman Pengaju</th>
                        <th class="py-3 px-4">Total Pesanan (IDR)</th>
                        <th class="py-3 px-4">Status Paparan Kredit</th>
                        <th class="py-3 px-4 text-right">Keputusan Approval</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <a href="<?php echo e(route('sales.show', $order->id)); ?>" class="font-mono font-bold text-blue-600 hover:underline block">
                                    <?php echo e($order->so_number); ?>

                                </a>
                                <span class="text-[10px] text-slate-400"><?php echo e($order->order_date); ?></span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-extrabold text-slate-900 block"><?php echo e($order->customer?->name); ?></span>
                                <span class="text-[10px] font-semibold text-slate-500">
                                    <?php echo e($order->branch?->name); ?> • Kanal <?php echo e($order->channel); ?>

                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                <?php echo e($order->salesman?->name ?? 'Salesman Lapangan'); ?>

                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                Rp <?php echo e(number_format($order->grand_total, 0, ',', '.')); ?>

                            </td>
                            <td class="py-3.5 px-4">
                                <?php
                                    $used = $order->customer?->current_credit_used ?? 0;
                                    $limit = $order->customer?->credit_limit ?? 0;
                                    $isExceeded = $limit > 0 && ($used + $order->grand_total) > $limit;
                                ?>
                                <?php if($isExceeded): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        ⚠️ Melebihi Plafon Kredit
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        ✓ Dalam Batas Kredit
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <form action="<?php echo e(route('sales.approvals.approve', $order->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="force_override" value="1">
                                        <?php if($isExceeded): ?>
                                            <button type="submit" 
                                                    title="Setujui dengan Otorisasi / Dispensasi Pimpinan"
                                                    class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm transition">
                                                ✓ Setujui (Dispensasi)
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" 
                                                    class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                                                ✓ Setujui
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                    <button type="button" 
                                            onclick="openRejectModal(<?php echo e($order->id); ?>, '<?php echo e($order->so_number); ?>')"
                                            class="px-3 py-1.5 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-800 font-bold text-xs transition">
                                        ✕ Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Tidak ada Sales Order yang sedang menunggu persetujuan (Antrean bersih).
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($orders->hasPages()): ?>
            <div class="p-4 border-t border-slate-100">
                <?php echo e($orders->links()); ?>

            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal Reject Decision -->
<div id="modalReject" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-extrabold text-slate-900">Alasan Penolakan Pesanan</h3>
            <button onclick="document.getElementById('modalReject').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400">✕</button>
        </div>

        <p class="text-xs text-slate-500" id="rejectSubtitle">
            Masukkan alasan penolakan secara jelas untuk diarsipkan ke jejak audit.
        </p>

        <form id="rejectForm" action="" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penolakan *</label>
                <textarea name="reason" rows="3" required placeholder="Contoh: Plafon kredit toko habis, diskon melebihi toleransi maksimal 5%..."
                          class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-rose-600 focus:outline-none"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalReject').classList.add('hidden')"
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

<script>
function openRejectModal(id, soNumber) {
    document.getElementById('rejectSubtitle').innerText = 'Menolak Sales Order #' + soNumber;
    document.getElementById('rejectForm').action = '/sales-orders/' + id + '/reject';
    document.getElementById('modalReject').classList.remove('hidden');
}
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/sales/approvals/index.blade.php ENDPATH**/ ?>