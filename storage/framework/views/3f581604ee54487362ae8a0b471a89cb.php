<?php $__env->startSection('title', 'Pengaturan & Kuota Tenant'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <span>Workspace</span>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Pengaturan Tenant</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Informasi & Kuota Paket Perusahaan</h1>
            <p class="text-xs text-slate-500">Monitor penggunaan kapasitas paket langganan SaaS perusahaan Anda.</p>
        </div>

        <div class="flex items-center space-x-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                <?php echo e($tenant->plan_tier === 'business' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200'); ?>">
                Tier: <?php echo e($tenant->plan_tier); ?>

            </span>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                Status: <?php echo e($tenant->status); ?>

            </span>
        </div>
    </div>

    <!-- Quota Utilization Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- User Quota -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase">
                <span>Pengguna (Users)</span>
                <span><?php echo e($usage['users']['used']); ?> / <?php echo e($usage['users']['limit']); ?></span>
            </div>
            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: <?php echo e(min(100, $usage['users']['percentage'])); ?>%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500">
                <span>Terpakai: <strong><?php echo e($usage['users']['percentage']); ?>%</strong></span>
                <span>Sisa: <strong><?php echo e(max(0, $usage['users']['limit'] - $usage['users']['used'])); ?> slot</strong></span>
            </div>
        </div>

        <!-- Branch Quota -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase">
                <span>Cabang (Branches)</span>
                <span><?php echo e($usage['branches']['used']); ?> / <?php echo e($usage['branches']['limit']); ?></span>
            </div>
            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                <div class="bg-purple-600 h-2.5 rounded-full transition-all duration-500" style="width: <?php echo e(min(100, $usage['branches']['percentage'])); ?>%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500">
                <span>Terpakai: <strong><?php echo e($usage['branches']['percentage']); ?>%</strong></span>
                <span>Sisa: <strong><?php echo e(max(0, $usage['branches']['limit'] - $usage['branches']['used'])); ?> cabang</strong></span>
            </div>
        </div>

        <!-- Warehouse Quota -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase">
                <span>Gudang (Warehouses)</span>
                <span><?php echo e($usage['warehouses']['used']); ?> / <?php echo e($usage['warehouses']['limit']); ?></span>
            </div>
            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                <div class="bg-emerald-600 h-2.5 rounded-full transition-all duration-500" style="width: <?php echo e(min(100, $usage['warehouses']['percentage'])); ?>%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500">
                <span>Terpakai: <strong><?php echo e($usage['warehouses']['percentage']); ?>%</strong></span>
                <span>Sisa: <strong><?php echo e(max(0, $usage['warehouses']['limit'] - $usage['warehouses']['used'])); ?> gudang</strong></span>
            </div>
        </div>

        <!-- Holding Company Quota -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase">
                <span>Badan Usaha (Company)</span>
                <span><?php echo e($usage['companies']['used']); ?> / <?php echo e($usage['companies']['limit']); ?></span>
            </div>
            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                <div class="bg-amber-600 h-2.5 rounded-full transition-all duration-500" style="width: <?php echo e(min(100, $usage['companies']['percentage'])); ?>%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500">
                <span>Terpakai: <strong><?php echo e($usage['companies']['percentage']); ?>%</strong></span>
                <span>Sisa: <strong><?php echo e(max(0, $usage['companies']['limit'] - $usage['companies']['used'])); ?> entitas</strong></span>
            </div>
        </div>

    </div>

    <!-- Grid Details -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Company Profile Info -->
        <div class="lg:col-span-6 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-3">Profil Perusahaan</h2>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Nama Perusahaan:</span>
                    <strong class="text-slate-900"><?php echo e($tenant->name); ?></strong>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Kode Tenant:</span>
                    <span class="font-mono font-bold text-slate-700"><?php echo e($tenant->code); ?></span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Subdomain Workspace:</span>
                    <span class="font-mono text-blue-600 font-bold"><?php echo e($tenant->subdomain); ?>.app</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Custom Domain:</span>
                    <span class="text-slate-700"><?php echo e($tenant->custom_domain ?? '-'); ?></span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Tanggal Terdaftar:</span>
                    <span class="text-slate-700"><?php echo e($tenant->created_at->format('d M Y')); ?></span>
                </div>
            </div>
        </div>

        <!-- Subscription & Billing Summary -->
        <div class="lg:col-span-6 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-3">Informasi Berlangganan SaaS</h2>

            <?php if($subscription): ?>
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Paket Terdaftar:</span>
                        <strong class="text-slate-900"><?php echo e($subscription->plan?->name); ?></strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Siklus Penagihan:</span>
                        <span class="font-bold text-slate-700 uppercase"><?php echo e($subscription->billing_cycle); ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Biaya Langganan:</span>
                        <strong class="text-emerald-600">Rp <?php echo e(number_format($subscription->amount, 0, ',', '.')); ?></strong>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Periode Aktif:</span>
                        <span class="font-bold text-slate-800"><?php echo e($subscription->current_period_start); ?> s/d <?php echo e($subscription->current_period_end); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-xs text-slate-400">Tidak ada data langganan.</p>
            <?php endif; ?>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500">Ingin menambah kuota user / cabang?</span>
                <a href="mailto:support@enterprisesales.co.id?subject=Upgrade Paket Tenant <?php echo e($tenant->name); ?>" 
                   class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                    Hubungi Dukungan &rarr;
                </a>
            </div>
        </div>

    </div>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/tenant/settings.blade.php ENDPATH**/ ?>