<?php $__env->startSection('title', 'Daftar Pesanan Penjualan (Sales Orders)'); ?>
<?php $__env->startSection('page_title', 'Manajemen Sales Orders (GT & MT)'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header Actions & Search Filter -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Daftar Transaksi Pesanan Penjualan</h3>
                <p class="text-xs text-slate-500">Mencakup saluran General Trade (Kanvas & TO) serta Modern Trade (PO Ritel)</p>
            </div>
            <a href="<?php echo e(route('sales.create')); ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Buat Sales Order Baru
            </a>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="<?php echo e(route('sales.index')); ?>" class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2 border-t border-slate-100">
            <div class="sm:col-span-5">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor SO atau nama pelanggan..."
                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
            </div>

            <div class="sm:col-span-3">
                <select name="channel" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent bg-white">
                    <option value="">Semua Saluran (GT & MT)</option>
                    <option value="GT" <?php echo e(request('channel') === 'GT' ? 'selected' : ''); ?>>General Trade (GT)</option>
                    <option value="MT" <?php echo e(request('channel') === 'MT' ? 'selected' : ''); ?>>Modern Trade (MT)</option>
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent bg-white">
                    <option value="">Semua Status</option>
                    <option value="PENDING_APPROVAL" <?php echo e(request('status') === 'PENDING_APPROVAL' ? 'selected' : ''); ?>>Pending Approval</option>
                    <option value="APPROVED" <?php echo e(request('status') === 'APPROVED' ? 'selected' : ''); ?>>Approved</option>
                    <option value="DELIVERED" <?php echo e(request('status') === 'DELIVERED' ? 'selected' : ''); ?>>Delivered</option>
                </select>
            </div>

            <div class="sm:col-span-1 flex gap-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table of Sales Orders -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 uppercase text-[11px] tracking-wider font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">Nomor SO</th>
                        <th class="py-3.5 px-6">Tanggal</th>
                        <th class="py-3.5 px-6">Cabang</th>
                        <th class="py-3.5 px-6">Pelanggan</th>
                        <th class="py-3.5 px-6 text-center">Saluran</th>
                        <th class="py-3.5 px-6 text-right">Subtotal</th>
                        <th class="py-3.5 px-6 text-right">Diskon</th>
                        <th class="py-3.5 px-6 text-right">Grand Total</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 px-6 font-bold text-slate-900"><?php echo e($order->so_number); ?></td>
                        <td class="py-4 px-6 text-slate-600 whitespace-nowrap"><?php echo e($order->order_date); ?></td>
                        <td class="py-4 px-6 text-slate-600"><?php echo e($order->branch_name); ?></td>
                        <td class="py-4 px-6 font-medium text-slate-800"><?php echo e($order->customer_name); ?></td>
                        <td class="py-4 px-6 text-center">
                            <?php if($order->channel === 'GT'): ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">GT</span>
                            <?php else: ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">MT</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-6 text-right text-slate-600">Rp <?php echo e(number_format($order->subtotal, 0, ',', '.')); ?></td>
                        <td class="py-4 px-6 text-right text-rose-600">
                            <?php echo e($order->discount_on_invoice > 0 ? '-Rp ' . number_format($order->discount_on_invoice, 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="py-4 px-6 text-right font-bold text-slate-900">Rp <?php echo e(number_format($order->grand_total, 0, ',', '.')); ?></td>
                        <td class="py-4 px-6 text-center">
                            <?php if($order->status === 'DELIVERED'): ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Selesai Kirim
                            </span>
                            <?php elseif($order->status === 'APPROVED'): ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                Disetujui
                            </span>
                            <?php else: ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                Menunggu Approval
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <a href="<?php echo e(route('sales.show', $order->id)); ?>" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition">
                                Rincian &rarr;
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="10" class="py-8 text-center text-slate-500">
                            Tidak ada data pesanan penjualan yang sesuai dengan filter pencarian.
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
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/sales/index.blade.php ENDPATH**/ ?>