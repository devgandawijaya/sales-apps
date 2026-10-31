<?php $__env->startSection('title', 'Target & Leaderboard Penjualan'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <span>Penjualan & Distribusi</span>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Target & Leaderboard</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kinerja Penjualan & Leaderboard Salesman</h1>
            <p class="text-xs text-slate-500">Pemantauan target omzet bulanan, tingkat pencapaian (*achievement*), dan peringkat salesmen.</p>
        </div>

        <div class="flex items-center space-x-3">
            <button onclick="document.getElementById('modalAddTarget').classList.remove('hidden')"
               class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Atur Target Salesman</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if(session('success')): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Month KPI Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Card 1: Actual Omzet -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Realisasi Omzet (Bulan Ini)</span>
            <div class="flex items-baseline space-x-1">
                <span class="text-xs font-semibold text-slate-500">Rp</span>
                <span class="text-2xl font-extrabold text-slate-900"><?php echo e(number_format($performance['actual_omzet'], 0, ',', '.')); ?></span>
            </div>
            <span class="text-[11px] text-slate-500 block">Total <?php echo e($performance['total_orders']); ?> pesanan disetujui</span>
        </div>

        <!-- Card 2: Target Omzet -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Target Omzet Tim</span>
            <div class="flex items-baseline space-x-1">
                <span class="text-xs font-semibold text-slate-500">Rp</span>
                <span class="text-2xl font-extrabold text-blue-600"><?php echo e(number_format($performance['target_omzet'], 0, ',', '.')); ?></span>
            </div>
            <span class="text-[11px] text-slate-500 block">Periode <?php echo e($performance['period']); ?></span>
        </div>

        <!-- Card 3: Target Achievement % -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tingkat Pencapaian (KPI)</span>
            <div class="flex items-baseline space-x-1">
                <span class="text-2xl font-extrabold <?php echo e($performance['achievement_percent'] >= 100 ? 'text-emerald-600' : 'text-amber-600'); ?>">
                    <?php echo e($performance['achievement_percent']); ?>%
                </span>
            </div>
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mt-1">
                <div class="h-2 rounded-full <?php echo e($performance['achievement_percent'] >= 100 ? 'bg-emerald-500' : 'bg-blue-600'); ?>" 
                     style="width: <?php echo e(min(100, $performance['achievement_percent'])); ?>%"></div>
            </div>
        </div>

        <!-- Card 4: Quotation Conversion -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Konversi Penawaran (Pipeline)</span>
            <div class="flex items-baseline space-x-1">
                <span class="text-2xl font-extrabold text-purple-600"><?php echo e($performance['quotation_stats']['conversion_rate']); ?>%</span>
                <span class="text-xs text-slate-400 font-medium">sukses SO</span>
            </div>
            <span class="text-[11px] text-slate-500 block">
                <?php echo e($performance['quotation_stats']['converted']); ?> dari <?php echo e($performance['quotation_stats']['total']); ?> penawaran
            </span>
        </div>

    </div>

    <!-- Leaderboard Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900">🏆 Peringkat Salesman Terbaik (Leaderboard)</h2>
                <p class="text-[11px] text-slate-500">Berdasarkan total omzet pesanan disetujui bulan <?php echo e($performance['period']); ?>.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">Peringkat</th>
                        <th class="py-3 px-4">Nama Salesman</th>
                        <th class="py-3 px-4">Jumlah Pesanan</th>
                        <th class="py-3 px-4">Omzet Riil (IDR)</th>
                        <th class="py-3 px-4">Target (IDR)</th>
                        <th class="py-3 px-4 text-right">Pencapaian %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $performance['leaderboard']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-center font-bold">
                                <?php if($idx === 0): ?>
                                    <span class="text-base">🥇</span>
                                <?php elseif($idx === 1): ?>
                                    <span class="text-base">🥈</span>
                                <?php elseif($idx === 2): ?>
                                    <span class="text-base">🥉</span>
                                <?php else: ?>
                                    <span class="text-slate-500">#<?php echo e($idx + 1); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <?php echo e($lead['salesman_name']); ?>

                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">
                                <?php echo e($lead['total_orders']); ?> SO
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                Rp <?php echo e(number_format($lead['total_omzet'], 0, ',', '.')); ?>

                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">
                                Rp <?php echo e(number_format($lead['target_omzet'], 0, ',', '.')); ?>

                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px]
                                    <?php echo e($lead['achievement_percent'] >= 100 ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700'); ?>">
                                    <?php echo e($lead['achievement_percent']); ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada transaksi penjualan yang tercatat pada periode ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Add / Set Target -->
<div id="modalAddTarget" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-extrabold text-slate-900">Tetapkan Target Salesman</h3>
            <button onclick="document.getElementById('modalAddTarget').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400">✕</button>
        </div>

        <form action="<?php echo e(route('sales.targets.store')); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div class="text-xs space-y-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Salesman *</label>
                    <select name="user_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <?php $__currentLoopData = $salesmen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?> (<?php echo e($s->role_title); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Bulan & Tahun Periode *</label>
                    <input type="date" name="period_date" value="<?php echo e(date('Y-m-01')); ?>" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Target Omzet Penjualan (IDR) *</label>
                    <input type="number" name="target_omset" required min="0" step="100000" placeholder="100000000"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Target Akuisisi Toko Baru</label>
                    <input type="number" name="target_new_customers" value="5" min="0"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Target</label>
                    <input type="text" name="notes" placeholder="Contoh: Fokus penetrasi produk RTD di pasar tradisional..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalAddTarget').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition">
                    Simpan Target &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/sales/targets/index.blade.php ENDPATH**/ ?>