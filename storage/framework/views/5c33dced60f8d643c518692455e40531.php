<?php $__env->startSection('title', 'Buat Sales Order Baru'); ?>
<?php $__env->startSection('page_title', 'Form Pembuatan Pesanan Penjualan'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Form Transaksi Sales Order</h3>
            <p class="text-xs text-slate-500">Otomatisasi validasi limit kredit, FEFO row-locking, dan kalkulasi PPN 11%</p>
        </div>
        <a href="<?php echo e(route('sales.index')); ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Daftar
        </a>
    </div>

    <form action="<?php echo e(route('sales.store')); ?>" method="POST" id="soForm" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-sm space-y-6">
        <?php echo csrf_field(); ?>

        <!-- Bagian 1: Entitas Cabang & Gudang -->
        <div>
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">1. Organisasi & Pengiriman</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Cabang (Branch)</label>
                    <select name="branch_id" id="branchSelect" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($b->id); ?>"><?php echo e($b->name); ?> (<?php echo e($b->code); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gudang Pengirim (Warehouse)</label>
                    <select name="warehouse_id" id="warehouseSelect" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($w->id); ?>" data-branch="<?php echo e($w->branch_id); ?>"><?php echo e($w->name); ?> [<?php echo e($w->type); ?>]</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Data Pelanggan & Saluran -->
        <div class="pt-4 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">2. Pelanggan & Validasi Kredit</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Pelanggan (Customer GT / MT)</label>
                    <select name="customer_id" id="customerSelect" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white" onchange="updateCustomerInfo()">
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>" 
                                data-channel="<?php echo e($c->channel); ?>" 
                                data-limit="<?php echo e($c->credit_limit); ?>" 
                                data-used="<?php echo e($c->current_credit_used); ?>"
                                data-top="<?php echo e($c->term_of_payment); ?>"
                                data-name="<?php echo e($c->name); ?>">
                            [<?php echo e($c->channel); ?>] <?php echo e($c->name); ?> (Limit: Rp <?php echo e(number_format($c->credit_limit, 0, ',', '.')); ?>)
                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Info Box Pelanggan Terpilih -->
                <div id="customerInfoBox" class="sm:col-span-2 hidden p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Saluran: <b id="infoChannel" class="text-slate-800"></b></span>
                        <span class="text-slate-500">Terms of Payment (TOP): <b id="infoTop" class="text-slate-800"></b> Hari</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Plafon Kredit: <b id="infoLimit" class="text-slate-800"></b></span>
                        <span class="text-slate-500">Kredit Terpakai: <b id="infoUsed" class="text-amber-600"></b></span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Pesanan (Order Type)</label>
                    <select name="order_type" id="orderTypeSelect" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <option value="TAKING_ORDER">Taking Order (TO Salesman)</option>
                        <option value="CANVASS">Kanvas (Direct Delivery Mobil)</option>
                        <option value="MODERN_TRADE_PO">Modern Trade Official PO</option>
                    </select>
                </div>

                <div id="poNumberMtContainer">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nomor PO Resmi MT <span id="poRequiredMark" class="text-rose-500 hidden">* (Wajib untuk MT)</span>
                    </label>
                    <input type="text" name="po_number_mt" id="poNumberInput" placeholder="Contoh: PO-SI-2026-X88"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>
        </div>

        <!-- Bagian 3: Rincian Produk & Kuantitas -->
        <div class="pt-4 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">3. Pemilihan Produk & Satuan</h4>
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-5">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Produk</label>
                    <select name="product_id" id="productSelect" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white" onchange="updateUnits()">
                        <option value="">-- Pilih Produk --</option>
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>" data-sku="<?php echo e($p->sku); ?>"><?php echo e($p->name); ?> (<?php echo e($p->sku); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan (UOM)</label>
                    <select name="product_unit_id" id="unitSelect" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <!-- Populated by JS -->
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kuantitas (Qty)</label>
                    <input type="number" name="qty_ordered" id="qtyInput" min="1" value="1" required
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Diskon (%)</label>
                    <input type="number" step="0.1" name="discount_percent1" id="discountInput" min="0" max="100" value="0"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>
        </div>

        <!-- Bagian 4: Catatan & Submit -->
        <div class="pt-4 border-t border-slate-100 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan Pesanan</label>
                <textarea name="notes" rows="2" placeholder="Catatan instruksi armada kirim atau rujukan promo..."
                          class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="<?php echo e(route('sales.index')); ?>" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold shadow-lg shadow-blue-500/25 transition">
                    Simpan & Buat Sales Order &rarr;
                </button>
            </div>
        </div>
    </form>

</div>

<script>
    const productsData = <?php echo json_encode($products, 15, 512) ?>;

    function updateCustomerInfo() {
        const select = document.getElementById('customerSelect');
        const selected = select.options[select.selectedIndex];
        const box = document.getElementById('customerInfoBox');
        const poMark = document.getElementById('poRequiredMark');

        if (!selected.value) {
            box.classList.add('hidden');
            poMark.classList.add('hidden');
            return;
        }

        const channel = selected.getAttribute('data-channel');
        const limit = parseFloat(selected.getAttribute('data-limit'));
        const used = parseFloat(selected.getAttribute('data-used'));
        const top = selected.getAttribute('data-top');

        document.getElementById('infoChannel').innerText = channel;
        document.getElementById('infoTop').innerText = top;
        document.getElementById('infoLimit').innerText = 'Rp ' + limit.toLocaleString('id-ID');
        document.getElementById('infoUsed').innerText = 'Rp ' + used.toLocaleString('id-ID');

        box.classList.remove('hidden');

        if (channel === 'MT') {
            poMark.classList.remove('hidden');
            document.getElementById('orderTypeSelect').value = 'MODERN_TRADE_PO';
        } else {
            poMark.classList.add('hidden');
            document.getElementById('orderTypeSelect').value = 'TAKING_ORDER';
        }
    }

    function updateUnits() {
        const prodId = document.getElementById('productSelect').value;
        const unitSelect = document.getElementById('unitSelect');
        unitSelect.innerHTML = '';

        if (!prodId) return;

        const product = productsData.find(p => p.id == prodId);
        if (product && product.units) {
            product.units.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.text = u.unit_name + ' (Isi: ' + u.conversion_factor + ' pcs)';
                unitSelect.appendChild(opt);
            });
        }
    }

    // Initialize units for first product
    document.addEventListener('DOMContentLoaded', () => {
        updateUnits();
    });
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/sales/create.blade.php ENDPATH**/ ?>