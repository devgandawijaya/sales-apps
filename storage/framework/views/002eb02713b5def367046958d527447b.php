<?php $__env->startSection('title', 'Terbitkan Faktur Penjualan Baru'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="<?php echo e(route('finance.index')); ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Keuangan & Faktur
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
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Billing & Administrasi Piutang</span>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Formulir Penerbitan Faktur Penjualan & Pajak</h1>
            <p class="text-xs text-slate-500">Pilih Surat Jalan (Delivery Order) yang telah dikirim, tetapkan tanggal jatuh tempo, dan generate nomor NSFP Faktur Pajak.</p>
        </div>

        <form action="<?php echo e(route('finance.invoices.store')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            <!-- Section 1: Choose Delivery Order -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                <h3 class="text-xs font-bold uppercase text-slate-600 tracking-wider">1. Pilih Surat Jalan (Delivery Order)</h3>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Surat Jalan Siap Ditagihkan *</label>
                    <select name="delivery_order_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white" onchange="window.location.href='<?php echo e(route('finance.invoices.create')); ?>?delivery_order_id=' + this.value">
                        <option value="">-- Pilih Surat Jalan --</option>
                        <?php $__currentLoopData = $deliveryOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $do): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($do->id); ?>" <?php echo e((old('delivery_order_id', $selectedDo?->id) == $do->id) ? 'selected' : ''); ?>>
                                <?php echo e($do->do_number); ?> - <?php echo e($do->salesOrder?->customer?->name); ?> (SO: <?php echo e($do->salesOrder?->so_number); ?>) - Kirim: <?php echo e($do->delivery_date?->format('d/m/Y')); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <?php if($selectedDo): ?>
                    <div class="mt-3 p-3 bg-white rounded-lg border border-slate-200 text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Pelanggan:</span>
                            <span class="font-bold text-slate-900"><?php echo e($selectedDo->salesOrder?->customer?->name); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kanal Penjualan:</span>
                            <span class="font-bold text-blue-700"><?php echo e($selectedDo->salesOrder?->customer?->channel); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Syarat Pembayaran (TOP):</span>
                            <span class="font-semibold text-slate-800"><?php echo e($selectedDo->salesOrder?->customer?->term_of_payment); ?> Hari</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Grand Total Tagihan:</span>
                            <span class="font-extrabold text-blue-600">Rp <?php echo e(number_format($selectedDo->salesOrder?->grand_total, 0, ',', '.')); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Section 2: Invoice Dates -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                <h3 class="text-xs font-bold uppercase text-slate-600 tracking-wider">2. Rincian Tanggal & Ketentuan Faktur</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Faktur</label>
                        <input type="date" name="invoice_date" value="<?php echo e(old('invoice_date', date('Y-m-d'))); ?>"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tagihan</label>
                        <input type="text" name="notes" value="<?php echo e(old('notes')); ?>" placeholder="Catatan tukar faktur atau kontra bon..."
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="<?php echo e(route('finance.index')); ?>" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Terbitkan Faktur & NSFP Pajak</span>
                </button>
            </div>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/finance/invoices/create.blade.php ENDPATH**/ ?>