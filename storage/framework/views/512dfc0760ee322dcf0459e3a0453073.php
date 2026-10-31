<?php $__env->startSection('title', 'Edit Pelanggan - ' . $customer->name); ?>
<?php $__env->startSection('page_title', 'Perbarui Data Pelanggan'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Perbarui Pelanggan: <?php echo e($customer->name); ?></h3>
            <p class="text-xs text-slate-500">Kode: <span class="font-mono font-bold"><?php echo e($customer->code); ?></span> • Terdaftar sejak <?php echo e($customer->created_at?->format('d M Y')); ?></p>
        </div>
        <a href="<?php echo e(route('customers.index')); ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Daftar
        </a>
    </div>

    <?php if($errors->any()): ?>
        <div class="p-4 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs space-y-1">
            <b class="font-bold">Gagal memperbarui data:</b>
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('customers.update', $customer->id)); ?>" method="POST" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-sm space-y-6">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <!-- Bagian 1: Identitas & Saluran Bisnis -->
        <div>
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">1. Identitas & Saluran Bisnis</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Toko / Perusahaan <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="<?php echo e(old('name', $customer->name)); ?>" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pemilik / PIC Toko</label>
                    <input type="text" name="owner_name" value="<?php echo e(old('owner_name', $customer->owner_name)); ?>"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Saluran Bisnis (Channel) <span class="text-rose-500">*</span></label>
                    <select name="channel" id="channelSelect" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white" onchange="toggleMtaSection()">
                        <option value="GT" <?php echo e(old('channel', $customer->channel) === 'GT' ? 'selected' : ''); ?>>General Trade (GT) - Warung / Toko Kelontong / Grosir</option>
                        <option value="MT" <?php echo e(old('channel', $customer->channel) === 'MT' ? 'selected' : ''); ?>>Modern Trade (MT) - Supermarket / Minimarket / DC</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sub-Saluran (Sub-Channel) <span class="text-rose-500">*</span></label>
                    <input type="text" name="sub_channel" value="<?php echo e(old('sub_channel', $customer->sub_channel)); ?>" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 uppercase">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cabang Penanggung Jawab (Branch) <span class="text-rose-500">*</span></label>
                    <select name="branch_id" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($b->id); ?>" <?php echo e(old('branch_id', $customer->branch_id) == $b->id ? 'selected' : ''); ?>><?php echo e($b->name); ?> (<?php echo e($b->code); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Keaktifan Pelanggan <span class="text-rose-500">*</span></label>
                    <select name="is_active" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <option value="1" <?php echo e(old('is_active', $customer->is_active) ? 'selected' : ''); ?>>Aktif (Dapat Bertransaksi)</option>
                        <option value="0" <?php echo e(!old('is_active', $customer->is_active) ? 'selected' : ''); ?>>Non-Aktif (Diblokir dari SO)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Alamat & Kontak -->
        <div class="pt-4 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">2. Alamat & Kontak</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap Toko / Gudang <span class="text-rose-500">*</span></label>
                    <textarea name="address" rows="2" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600"><?php echo e(old('address', $customer->address)); ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                    <input type="text" name="city" value="<?php echo e(old('city', $customer->city)); ?>" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" value="<?php echo e(old('phone', $customer->phone)); ?>"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email</label>
                    <input type="email" name="email" value="<?php echo e(old('email', $customer->email)); ?>"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NPWP Perusahaan / Toko</label>
                    <input type="text" name="tax_id_npwp" value="<?php echo e(old('tax_id_npwp', $customer->tax_id_npwp)); ?>"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>
        </div>

        <!-- Bagian 3: Kebijakan Kredit & Pembayaran -->
        <div class="pt-4 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">3. Batas Plafon Kredit & Ketentuan Pembayaran</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Plafon Kredit (Credit Limit Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" step="100000" name="credit_limit" value="<?php echo e(old('credit_limit', $customer->credit_limit)); ?>" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <span class="text-[11px] text-amber-600 mt-1 block">Saat ini terpakai: Rp <?php echo e(number_format($customer->current_credit_used, 0, ',', '.')); ?></span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tenor Pembayaran (TOP Hari) <span class="text-rose-500">*</span></label>
                    <input type="number" name="term_of_payment" value="<?php echo e(old('term_of_payment', $customer->term_of_payment)); ?>" required min="0" max="180"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pembayaran Default <span class="text-rose-500">*</span></label>
                    <select name="payment_type" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <option value="CREDIT" <?php echo e(old('payment_type', $customer->payment_type) === 'CREDIT' ? 'selected' : ''); ?>>Kredit / Tempo</option>
                        <option value="CASH" <?php echo e(old('payment_type', $customer->payment_type) === 'CASH' ? 'selected' : ''); ?>>Tunai / COD</option>
                        <option value="GIRO" <?php echo e(old('payment_type', $customer->payment_type) === 'GIRO' ? 'selected' : ''); ?>>Giro / Cek Mundur</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Bagian 4: Parameter Kontrak Modern Trade (MTA) -->
        <div id="mtaSection" class="pt-4 border-t border-slate-100 hidden">
            <h4 class="text-xs font-bold text-blue-700 uppercase tracking-wider mb-3">4. Kontrak Dagang Modern Trade Agreement (MTA)</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-blue-50/50 border border-blue-100">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Biaya Pendaftaran SKU (Listing Fee Rp)</label>
                    <input type="number" step="10000" name="listing_fee" value="<?php echo e(old('listing_fee', $customer->modernTradeAgreement?->listing_fee_total ?? 0)); ?>"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sewa Rak Display (Slotting Fee Rp)</label>
                    <input type="number" step="10000" name="slotting_fee" value="<?php echo e(old('slotting_fee', $customer->modernTradeAgreement?->slotting_fee_total ?? 0)); ?>"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rabat Target Tahunan (Rebate %)</label>
                    <input type="number" step="0.1" min="0" max="100" name="target_rebate_percent" value="<?php echo e(old('target_rebate_percent', $customer->modernTradeAgreement?->off_invoice_rebate_rate ?? 0)); ?>"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                </div>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end space-x-3">
            <a href="<?php echo e(route('customers.index')); ?>" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-lg shadow-blue-500/20 transition">
                Simpan Perubahan &rarr;
            </button>
        </div>
    </form>

</div>

<script>
    function toggleMtaSection() {
        const channel = document.getElementById('channelSelect').value;
        const mta = document.getElementById('mtaSection');
        if (channel === 'MT') {
            mta.classList.remove('hidden');
        } else {
            mta.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        toggleMtaSection();
    });
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/customers/edit.blade.php ENDPATH**/ ?>