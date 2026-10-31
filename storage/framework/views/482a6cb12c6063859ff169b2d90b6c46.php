<?php $__env->startSection('title', 'SaaS Super Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-purple-100 text-purple-700 border border-purple-200">
                    SaaS Platform Control
                </span>
                <span class="text-xs text-slate-400">Multi-Tenant Engine v1.0</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Platform Super Admin</h1>
            <p class="text-xs text-slate-500">Pemantauan kesehatan seluruh tenant, pendapatan bulanan (MRR), dan kapasitas sistem.</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="<?php echo e(route('superadmin.tenants.index')); ?>" 
               class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-md transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Kelola Seluruh Tenant &rarr;</span>
            </a>
            <a href="<?php echo e(route('tenant.register')); ?>" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Onboarding Tenant Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    <?php if(session('success')): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span><?php echo e(session('success')); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- High-Level KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Tenants -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Perusahaan (Tenants)</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">🏢</span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-extrabold text-slate-900"><?php echo e(number_format($totalTenants)); ?></span>
                <span class="text-xs font-semibold text-emerald-600">Terdaftar</span>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500 pt-2 border-t border-slate-100">
                <span>Aktif: <strong class="text-emerald-600"><?php echo e($activeTenants); ?></strong></span>
                <span>Trial: <strong class="text-amber-600"><?php echo e($trialTenants); ?></strong></span>
                <span>Suspended: <strong class="text-rose-600"><?php echo e($suspendedTenants); ?></strong></span>
            </div>
        </div>

        <!-- Card 2: MRR (Monthly Recurring Revenue) -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Monthly Recurring Revenue (MRR)</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">💵</span>
            </div>
            <div class="flex items-baseline space-x-1">
                <span class="text-xs font-semibold text-slate-500">Rp</span>
                <span class="text-3xl font-extrabold text-slate-900"><?php echo e(number_format($mrr, 0, ',', '.')); ?></span>
            </div>
            <p class="text-[11px] text-slate-500 pt-2 border-t border-slate-100">
                Annual Run Rate (ARR): <strong class="text-slate-800">Rp <?php echo e(number_format($arr, 0, ',', '.')); ?></strong>
            </p>
        </div>

        <!-- Card 3: Subscription Plans Health -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Distribusi Paket Langganan</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs">📦</span>
            </div>
            <div class="space-y-1.5 pt-1">
                <?php $__currentLoopData = $plans->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-medium"><?php echo e($plan->name); ?></span>
                        <span class="font-bold text-slate-900"><?php echo e($plan->subscriptions_count); ?> Tenant</span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Card 4: Platform Security & Isolation -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-indigo-950 text-white shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-300">Data Isolation Engine</span>
                <span class="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 text-[10px] font-bold">RLS ACTIVE</span>
            </div>
            <div>
                <span class="text-lg font-extrabold text-white">PostgreSQL RLS + Eloquent</span>
                <p class="text-[11px] text-slate-300 mt-1">Isolasi data ketat otomatis memproteksi kebocoran antar tenant.</p>
            </div>
            <div class="pt-2 border-t border-indigo-900/60 text-[10px] text-indigo-300 flex items-center justify-between">
                <span>Granular RBAC: <strong>19 Roles</strong></span>
                <span>Audit Trail: <strong>Immutable</strong></span>
            </div>
        </div>
    </div>

    <!-- Grid: Recent Onboarded Tenants & Subscription Tier Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Recent Tenants (8 cols) -->
        <div class="lg:col-span-8 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Perusahaan Terbaru Terdaftar</h2>
                    <p class="text-[11px] text-slate-500">Tenant aktif dan status langganan saat ini.</p>
                </div>
                <a href="<?php echo e(route('superadmin.tenants.index')); ?>" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Nama Perusahaan</th>
                            <th class="py-3 px-4">Subdomain / Domain</th>
                            <th class="py-3 px-4">Paket Tier</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__empty_1 = true; $__currentLoopData = $recentTenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900"><?php echo e($t->name); ?></div>
                                    <div class="text-[11px] text-slate-400 font-mono"><?php echo e($t->code); ?></div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600 text-[11px]">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold"><?php echo e($t->subdomain); ?></span>.app
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        <?php echo e($t->plan_tier === 'enterprise' ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : ''); ?>

                                        <?php echo e($t->plan_tier === 'business' ? 'bg-purple-100 text-purple-700 border border-purple-200' : ''); ?>

                                        <?php echo e($t->plan_tier === 'professional' ? 'bg-blue-100 text-blue-700 border border-blue-200' : ''); ?>

                                        <?php echo e($t->plan_tier === 'basic' ? 'bg-slate-100 text-slate-700 border border-slate-200' : ''); ?>

                                        <?php echo e($t->plan_tier === 'trial' ? 'bg-amber-100 text-amber-700 border border-amber-200' : ''); ?>">
                                        <?php echo e($t->plan_tier); ?>

                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if($t->status === 'active'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    <?php elseif($t->status === 'trial'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Trial
                                        </span>
                                    <?php elseif($t->status === 'suspended'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Ditangguhkan
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                                            <?php echo e(ucfirst($t->status)); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="<?php echo e(route('superadmin.tenants.show', $t->id)); ?>" 
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    Belum ada tenant yang terdaftar.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column: Subscription Plans & Quota Specs (4 cols) -->
        <div class="lg:col-span-4 bg-white border border-slate-200 rounded-2xl shadow-sm p-5 space-y-4">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900">Paket Langganan SaaS</h2>
                <p class="text-[11px] text-slate-500">Daftar paket aktif & kuota maksimal.</p>
            </div>

            <div class="space-y-3">
                <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-3.5 rounded-xl border border-slate-200 hover:border-blue-400 transition space-y-2 bg-slate-50/50">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-slate-800 text-xs"><?php echo e($plan->name); ?></span>
                            <span class="text-[11px] font-bold text-blue-600">
                                Rp <?php echo e(number_format($plan->price_monthly / 1000, 0)); ?>k/bln
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-500 pt-1 border-t border-slate-200/60">
                            <div>User: <strong class="text-slate-800"><?php echo e($plan->max_users); ?></strong></div>
                            <div>Cabang: <strong class="text-slate-800"><?php echo e($plan->max_branches); ?></strong></div>
                            <div>Gudang: <strong class="text-slate-800"><?php echo e($plan->max_warehouses); ?></strong></div>
                            <div>Storage: <strong class="text-slate-800"><?php echo e(round($plan->storage_limit_mb / 1000, 1)); ?> GB</strong></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

    </div>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/superadmin/dashboard.blade.php ENDPATH**/ ?>