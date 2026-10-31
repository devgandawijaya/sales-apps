<?php $__env->startSection('title', 'Buat Transfer Antar-Gudang'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Buat Pengajuan Transfer Stok</h1>
            <p class="text-sm text-slate-500 mt-1">Stok hanya akan dipindahkan setelah melalui alur Approval dan Dispatch.</p>
        </div>
        <a href="<?php echo e(route('transfers.index')); ?>" class="text-sm font-semibold text-slate-600 hover:text-slate-800">
            &larr; Kembali
        </a>
    </div>

    <?php if($errors->any()): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">
            <ul class="list-disc pl-5 space-y-1">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('transfers.store')); ?>" method="POST" class="space-y-6">
        <?php echo csrf_field(); ?>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Informasi Lokasi Gudang</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gudang Asal (Source) <span class="text-red-500">*</span></label>
                    <select name="from_warehouse_id" required class="w-full text-sm border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Gudang Asal --</option>
                        <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($wh->id); ?>"><?php echo e($wh->code); ?> - <?php echo e($wh->name); ?> (<?php echo e($wh->type); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gudang Tujuan (Destination) <span class="text-red-500">*</span></label>
                    <select name="to_warehouse_id" required class="w-full text-sm border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Gudang Tujuan --</option>
                        <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($wh->id); ?>"><?php echo e($wh->code); ?> - <?php echo e($wh->name); ?> (<?php echo e($wh->type); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Alasan Transfer</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Pemenuhan lonjakan permintaan kanvas cabang Bandung." class="w-full text-sm border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Item Barang yang Ditransfer</h2>

            <div class="space-y-4">
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Produk</label>
                        <select name="items[0][product_id]" required class="w-full text-xs border-slate-200 rounded-lg">
                            <option value="">-- Pilih Produk --</option>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($prod->id); ?>"><?php echo e($prod->sku); ?> - <?php echo e($prod->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Satuan UOM</label>
                        <select name="items[0][product_unit_id]" required class="w-full text-xs border-slate-200 rounded-lg">
                            <?php $__currentLoopData = $products->first()?->units ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($u->id); ?>"><?php echo e($u->unit_name); ?> (x<?php echo e($u->conversion_factor); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">No. Batch</label>
                        <input type="text" name="items[0][batch_number]" value="LOT-DEFAULT" required class="w-full text-xs border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Qty Kirim</label>
                        <input type="number" name="items[0][qty_sent]" value="10" min="1" required class="w-full text-xs border-slate-200 rounded-lg">
                    </div>
                    <input type="hidden" name="items[0][expiry_date]" value="<?php echo e(date('Y-m-d', strtotime('+6 months'))); ?>">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="<?php echo e(route('transfers.index')); ?>" class="px-5 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-blue-500/20">Simpan Draft Transfer</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/inventory/transfers/create.blade.php ENDPATH**/ ?>