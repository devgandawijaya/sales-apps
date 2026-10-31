<?php $__env->startSection('title', 'Terbitkan Surat Jalan Baru'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="<?php echo e(route('delivery-orders.index')); ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Daftar Surat Jalan
        </a>
    </div>

    <!-- Alert Error -->
    <?php if(session('error')): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pemenuhan Logistik Gudang</span>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Formulir Penerbitan Surat Jalan (DO)</h1>
            <p class="text-xs text-slate-500">Pilih Sales Order yang telah disetujui, alokasikan armada pengiriman, dan terbitkan dokumen resmi.</p>
        </div>

        <form action="<?php echo e(route('delivery-orders.store')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            <!-- Section 1: Select Sales Order -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                <h3 class="text-xs font-bold uppercase text-slate-600 tracking-wider">1. Pilih Pesanan Penjualan (Sales Order)</h3>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sales Order Disetujui *</label>
                    <select name="sales_order_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white" onchange="window.location.href='<?php echo e(route('delivery-orders.create')); ?>?sales_order_id=' + this.value">
                        <option value="">-- Pilih Sales Order --</option>
                        <?php $__currentLoopData = $approvedOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $so): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($so->id); ?>" <?php echo e((old('sales_order_id', $selectedOrder?->id) == $so->id) ? 'selected' : ''); ?>>
                                <?php echo e($so->so_number); ?> - <?php echo e($so->customer?->name); ?> (Rp <?php echo e(number_format($so->grand_total, 0, ',', '.')); ?>) [<?php echo e($so->channel); ?>]
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <?php if($selectedOrder): ?>
                    <div class="mt-3 p-3 bg-white rounded-lg border border-slate-200 text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Pelanggan:</span>
                            <span class="font-bold text-slate-900"><?php echo e($selectedOrder->customer?->name); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Alamat Kirim:</span>
                            <span class="text-slate-700"><?php echo e($selectedOrder->customer?->address); ?>, <?php echo e($selectedOrder->customer?->city); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Gudang Asal:</span>
                            <span class="font-semibold text-slate-800"><?php echo e($selectedOrder->warehouse?->name); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Nilai Pesanan:</span>
                            <span class="font-extrabold text-blue-600">Rp <?php echo e(number_format($selectedOrder->grand_total, 0, ',', '.')); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Section 2: Logistics & Dispatch Details -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                <h3 class="text-xs font-bold uppercase text-slate-600 tracking-wider">2. Rincian Pengiriman & Armada</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Supir / Kurir *</label>
                        <input type="text" name="driver_name" value="<?php echo e(old('driver_name')); ?>" required placeholder="Contoh: Budi Santoso"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Plat Kendaraan *</label>
                        <input type="text" name="vehicle_plate_number" value="<?php echo e(old('vehicle_plate_number')); ?>" required placeholder="Contoh: B 9482 SXZ"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white font-mono uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pengiriman *</label>
                        <select name="delivery_type" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                            <option value="DIRECT_STORE">Kirim Langsung ke Toko (Direct Store Delivery)</option>
                            <option value="CENTRAL_DC">Kirim ke Distribution Center (Central DC)</option>
                            <option value="CANVASS_DIRECT">Serah Terima Kanvas Mobil</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                        <input type="text" name="notes" value="<?php echo e(old('notes')); ?>" placeholder="Instruksi khusus bongkar muat..."
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="<?php echo e(route('delivery-orders.index')); ?>" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Terbitkan Surat Jalan (DO)</span>
                </button>
            </div>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/delivery_orders/create.blade.php ENDPATH**/ ?>