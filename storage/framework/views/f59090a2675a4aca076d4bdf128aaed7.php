<?php $__env->startSection('title', 'Detail Surat Jalan ' . $deliveryOrder->do_number); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="<?php echo e(route('delivery-orders.index')); ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Daftar Surat Jalan
        </a>

        <div class="flex items-center gap-2">
            <?php if($deliveryOrder->status === 'PREPARED'): ?>
                <form action="<?php echo e(route('delivery-orders.dispatch', $deliveryOrder->id)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-xs font-bold text-white shadow-sm transition">
                        🚚 Berangkatkan Armada
                    </button>
                </form>
            <?php endif; ?>

            <?php if($deliveryOrder->status === 'DISPATCHED'): ?>
                <button type="button" onclick="document.getElementById('modalDeliver').classList.remove('hidden')"
                        class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-sm transition">
                    ✓ Konfirmasi Penerimaan Toko
                </button>
            <?php endif; ?>

            <?php if(!$deliveryOrder->invoice): ?>
                <a href="<?php echo e(route('finance.invoices.create')); ?>?delivery_order_id=<?php echo e($deliveryOrder->id); ?>" 
                   class="px-4 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-xs font-bold text-white shadow-sm transition">
                    📄 Terbitkan Faktur
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('finance.invoices.show', $deliveryOrder->invoice->id)); ?>" 
                   class="px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold transition">
                    Lihat Faktur (<?php echo e($deliveryOrder->invoice->invoice_number); ?>)
                </a>
            <?php endif; ?>

            <a href="<?php echo e(route('delivery-orders.print', $deliveryOrder->id)); ?>" target="_blank"
               class="px-4 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 transition">
                🖨️ Cetak Surat Jalan
            </a>
        </div>
    </div>

    <!-- Alerts -->
    <?php if(session('success')): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Main Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-6">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Dokumen Resmi Pengiriman (Surat Jalan)</span>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-1"><?php echo e($deliveryOrder->do_number); ?></h2>
                <p class="text-xs text-slate-500 mt-1">Tanggal Kirim: <b class="text-slate-700"><?php echo e($deliveryOrder->delivery_date?->format('d M Y')); ?></b></p>
            </div>

            <div class="text-left sm:text-right space-y-1">
                <div>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                        <?php echo e($deliveryOrder->status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ''); ?>

                        <?php echo e($deliveryOrder->status === 'DISPATCHED' ? 'bg-amber-100 text-amber-800 border border-amber-300' : ''); ?>

                        <?php echo e(!in_array($deliveryOrder->status, ['DELIVERED', 'DISPATCHED']) ? 'bg-slate-100 text-slate-700 border border-slate-300' : ''); ?>">
                        Status: <?php echo e($deliveryOrder->status); ?>

                    </span>
                </div>
                <div class="text-xs text-slate-500">
                    Ref SO: <a href="<?php echo e(route('sales.show', $deliveryOrder->sales_order_id)); ?>" class="font-mono text-blue-600 font-bold hover:underline"><?php echo e($deliveryOrder->salesOrder?->so_number); ?></a>
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            <div class="p-4 bg-slate-50 rounded-xl space-y-2 border border-slate-200">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Tujuan Pengiriman & Pelanggan:</span>
                <div class="text-sm font-bold text-slate-900"><?php echo e($deliveryOrder->salesOrder?->customer?->name); ?></div>
                <div class="text-slate-600"><?php echo e($deliveryOrder->salesOrder?->customer?->address); ?></div>
                <div class="text-slate-600"><?php echo e($deliveryOrder->salesOrder?->customer?->city); ?>, <?php echo e($deliveryOrder->salesOrder?->customer?->province); ?></div>
                <div class="pt-2 text-slate-500">Kanal: <span class="font-bold text-slate-800"><?php echo e($deliveryOrder->salesOrder?->customer?->channel); ?></span></div>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl space-y-2 border border-slate-200">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Informasi Armada & Gudang:</span>
                <div>Gudang Asal: <strong class="text-slate-900"><?php echo e($deliveryOrder->warehouse?->name); ?></strong></div>
                <div>Cabang: <strong class="text-slate-900"><?php echo e($deliveryOrder->branch?->name); ?></strong></div>
                <div>Nama Supir: <strong class="text-slate-900"><?php echo e($deliveryOrder->driver_name ?? '-'); ?></strong></div>
                <div>Plat Kendaraan: <strong class="text-slate-900 font-mono"><?php echo e($deliveryOrder->vehicle_plate_number ?? '-'); ?></strong></div>
                <div>Penerima: <strong class="text-emerald-700"><?php echo e($deliveryOrder->recipient_name ?? 'Belum diterima'); ?></strong></div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                Daftar Barang & Alokasi Batch Nomor FEFO
            </div>
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-500 font-bold uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-4">SKU & Nama Produk</th>
                        <th class="py-2.5 px-4">No. Batch / Lot</th>
                        <th class="py-2.5 px-4">Tgl. Kadaluwarsa</th>
                        <th class="py-2.5 px-4 text-center">Qty Dikirim</th>
                        <th class="py-2.5 px-4 text-center">Qty Diterima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__currentLoopData = $deliveryOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block"><?php echo e($item->product?->name); ?></span>
                                <span class="font-mono text-[10px] text-slate-400 block"><?php echo e($item->product?->sku); ?></span>
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-blue-700">
                                <?php echo e($item->batch_number); ?>

                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                <?php echo e($item->expiry_date?->format('d M Y')); ?>

                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-900">
                                <?php echo e(number_format($item->qty_shipped)); ?> Unit
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-emerald-700">
                                <?php echo e(number_format($item->qty_received)); ?> Unit
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- Modal Deliver -->
<div id="modalDeliver" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
        <h3 class="text-base font-bold text-slate-900">Konfirmasi Penerimaan Toko (BAST)</h3>
        <p class="text-xs text-slate-500">Masukkan nama penanggung jawab atau staf toko yang menerima fisik barang kiriman.</p>

        <form action="<?php echo e(route('delivery-orders.deliver', $deliveryOrder->id)); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Penerima Toko *</label>
                <input type="text" name="recipient_name" required placeholder="Contoh: Pak Haji Ahmad (Pemilik)"
                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('modalDeliver').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md">Simpan Penerimaan</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/delivery_orders/show.blade.php ENDPATH**/ ?>