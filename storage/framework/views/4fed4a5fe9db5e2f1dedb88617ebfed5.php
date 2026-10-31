<?php $__env->startSection('title', 'Buat Penawaran Harga Baru'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="<?php echo e(route('quotations.index')); ?>" class="hover:text-blue-600 font-semibold">Penawaran Harga</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Buat Penawaran Baru</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Formulir Penawaran Harga (Sales Quotation)</h1>
            <p class="text-xs text-slate-500">Harga dan diskon yang disepakati akan disimpan sebagai snapshot beku (*frozen price*).</p>
        </div>

        <div>
            <a href="<?php echo e(route('quotations.index')); ?>" 
               class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                &larr; Batal & Kembali
            </a>
        </div>
    </div>

    <?php if($errors->any()): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('quotations.store')); ?>" method="POST" class="space-y-6">
        <?php echo csrf_field(); ?>

        <!-- Card 1: Informasi Header Penawaran -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider text-blue-600 border-b border-slate-100 pb-2">
                1. Parameter Penawaran & Pelanggan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kantor Cabang Pengaju *</label>
                    <select name="branch_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b->id); ?>"><?php echo e($b->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Target Pelanggan / Toko *</label>
                    <select name="customer_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>">
                                <?php echo e($c->code); ?> - <?php echo e($c->name); ?> (<?php echo e($c->channel); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Penawaran *</label>
                    <input type="date" name="quotation_date" value="<?php echo e(date('Y-m-d')); ?>" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Batas Berlaku (Valid Until) *</label>
                    <input type="date" name="expiry_date" value="<?php echo e(date('Y-m-d', strtotime('+14 days'))); ?>" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="block font-bold text-slate-700 mb-1">Catatan Khusus / Terms</label>
                    <input type="text" name="notes" placeholder="Contoh: Franco gudang pembeli, pembayaran bilyet giro 30 hari..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Card 2: Item Produk Penawaran -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider text-blue-600 border-b border-slate-100 pb-2">
                2. Rincian Item Produk & Harga Satuan
            </h2>

            <div class="space-y-3" id="itemsContainer">
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs items-end">
                    <div class="sm:col-span-4">
                        <label class="block font-bold text-slate-700 mb-1">Pilih Produk *</label>
                        <select name="items[0][product_id]" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>"><?php echo e($p->sku); ?> - <?php echo e($p->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">Satuan UOM *</label>
                        <select name="items[0][product_unit_id]" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <?php $__currentLoopData = $products->first()->units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($u->id); ?>"><?php echo e($u->unit_name); ?> (x<?php echo e($u->conversion_factor); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">Jumlah Qty *</label>
                        <input type="number" name="items[0][qty_ordered]" value="10" min="1" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">Diskon 1 (%)</label>
                        <input type="number" step="0.1" name="items[0][discount_percent_1]" value="0" min="0" max="100"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">Diskon 2 (%)</label>
                        <input type="number" step="0.1" name="items[0][discount_percent_2]" value="0" min="0" max="100"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <span class="text-[11px] text-slate-400">Kalkulasi diskon bertingkat dan PPN 11% akan dihitung otomatis oleh Pricing Engine.</span>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end space-x-3">
            <button type="submit" 
                    class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-lg shadow-blue-500/25 transition">
                Simpan & Terbitkan Penawaran Harga &rarr;
            </button>
        </div>
    </form>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/sales/quotations/create.blade.php ENDPATH**/ ?>