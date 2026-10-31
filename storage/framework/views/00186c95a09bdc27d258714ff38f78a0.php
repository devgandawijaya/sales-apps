<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> - Enterprise Sales SaaS</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex">

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 text-slate-300 flex flex-col shrink-0 sticky top-0 h-screen hidden md:flex">
        <!-- Logo & Branding -->
        <div class="h-16 flex items-center px-6 border-b border-slate-800 space-x-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/30 text-white font-extrabold text-base">
                SD
            </div>
            <div class="truncate">
                <h1 class="text-sm font-bold tracking-tight text-white leading-tight truncate">
                    <?php echo e(\App\Domain\Tenant\TenantContext::getTenant()?->name ?? 'Enterprise Sales'); ?>

                </h1>
                <p class="text-[10px] text-blue-400 font-semibold uppercase tracking-wider">
                    <?php echo e(\App\Domain\Tenant\TenantContext::getTenant()?->plan_tier ?? 'Multi-Tenant SaaS'); ?>

                </p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto text-xs font-semibold">
            <div class="text-[10px] uppercase font-bold text-slate-500 px-3 pb-2 tracking-wider">Navigasi Utama</div>
            
            <a href="<?php echo e(route('dashboard')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard & KPI</span>
            </a>

            <div class="pt-4 text-[10px] uppercase font-bold text-slate-500 px-3 pb-2 tracking-wider">Penjualan & Distribusi</div>

            <a href="<?php echo e(route('sales.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('sales.index') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>Pesanan (Sales Orders)</span>
            </a>

            <a href="<?php echo e(route('delivery-orders.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('delivery-orders.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Surat Jalan (DO)</span>
            </a>

            <a href="<?php echo e(route('returns.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('returns.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Retur Penjualan</span>
            </a>

            <a href="<?php echo e(route('quotations.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('quotations.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Penawaran (Quotations)</span>
            </a>

            <a href="<?php echo e(route('sales.approvals.index')); ?>" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('sales.approvals.*') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Antrean Approval SO</span>
                </div>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-500/30 text-amber-300">Auth</span>
            </a>

            <a href="<?php echo e(route('sales.targets.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('sales.targets.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span>Target & Leaderboard</span>
            </a>

            <a href="<?php echo e(route('sales.create')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('sales.create') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Buat Sales Order</span>
            </a>

            <a href="<?php echo e(route('customers.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('customers.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Pelanggan & Kredit</span>
            </a>

            <a href="<?php echo e(route('routes.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('routes.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span>Rute & Kunjungan GT</span>
            </a>

            <div class="pt-4 text-[10px] uppercase font-bold text-slate-500 px-3 pb-2 tracking-wider">Logistik & Pergudangan</div>

            <a href="<?php echo e(route('inventory.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('inventory.index') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Gudang & Stok FEFO</span>
            </a>

            <a href="<?php echo e(route('transfers.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('transfers.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Transfer Antar-Gudang</span>
            </a>

            <a href="<?php echo e(route('opname.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('opname.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Stock Opname & Count</span>
            </a>

            <a href="<?php echo e(route('inventory.ledger')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('inventory.ledger') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Buku Besar Stok (Ledger)</span>
            </a>

            <a href="<?php echo e(route('inventory.expiry-alerts')); ?>" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('inventory.expiry-alerts') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Peringatan Kadaluwarsa</span>
                </div>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-red-500/30 text-red-300">FEFO</span>
            </a>

            <div class="pt-4 text-[10px] uppercase font-bold text-slate-500 px-3 pb-2 tracking-wider">Keuangan & Piutang</div>

            <a href="<?php echo e(route('finance.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('finance.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Piutang & AR Aging</span>
            </a>

            <div class="pt-4 text-[10px] uppercase font-bold text-slate-500 px-3 pb-2 tracking-wider">Keamanan & SaaS</div>

            <a href="<?php echo e(route('tenant.settings')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('tenant.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Kuota & Info Tenant</span>
            </a>

            <a href="<?php echo e(route('users.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Pengguna & RBAC</span>
            </a>

            <a href="<?php echo e(route('audit.index')); ?>" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('audit.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-white'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Jejak Audit (Audit Log)</span>
            </a>

            <?php if(auth()->check() && auth()->user()->isSuperAdmin()): ?>
                <div class="pt-4 text-[10px] uppercase font-bold text-purple-400 px-3 pb-2 tracking-wider">Super Admin SaaS</div>
                <a href="<?php echo e(route('superadmin.dashboard')); ?>" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition bg-purple-900/40 hover:bg-purple-800/60 text-purple-200 border border-purple-800/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>SaaS Platform Control</span>
                </a>
            <?php endif; ?>
        </nav>

        <!-- Current User Profile in Sidebar -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60">
            <?php if(auth()->guard()->check()): ?>
            <div class="flex items-center justify-between">
                <div class="truncate">
                    <span class="block text-xs font-bold text-white truncate"><?php echo e(auth()->user()->name); ?></span>
                    <span class="block text-[10px] text-slate-400 truncate"><?php echo e(auth()->user()->role_title); ?> • <?php echo e(auth()->user()->branch?->name ?? 'Pusat'); ?></span>
                </div>
                <form action="<?php echo e(route('logout')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" title="Keluar / Logout" class="p-1.5 rounded-lg bg-slate-800 hover:bg-rose-900/60 hover:text-rose-300 text-slate-400 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
            <?php else: ?>
            <a href="<?php echo e(route('login')); ?>" class="block text-center py-2 px-3 rounded-lg bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                Masuk / Login
            </a>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 sticky top-0 z-40 shadow-sm">
            <div class="flex items-center space-x-3">
                <h2 class="text-base sm:text-lg font-bold text-slate-800"><?php echo $__env->yieldContent('page_title', 'SaaS Distribution'); ?></h2>
            </div>

            <div class="flex items-center space-x-3">
                <?php if(\App\Domain\Tenant\TenantContext::hasTenant()): ?>
                    <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <?php echo e(\App\Domain\Tenant\TenantContext::getTenant()->name); ?>

                    </span>
                <?php endif; ?>

                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    PostgreSQL 16 RLS
                </span>

                <?php if(auth()->guard()->check()): ?>
                <div class="hidden sm:flex items-center gap-2 pl-3 border-l border-slate-200">
                    <span class="text-xs text-slate-500">User:</span>
                    <span class="text-xs font-bold text-slate-800"><?php echo e(auth()->user()->name); ?></span>
                    <form action="<?php echo e(route('logout')); ?>" method="POST" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800 ml-2">Logout</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="max-w-7xl w-full mx-auto px-4 sm:px-8 pt-4">
            <?php if(session('success')): ?>
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-medium flex items-center justify-between shadow-sm">
                <span><?php echo e(session('success')); ?></span>
                <span class="font-bold text-emerald-600 cursor-pointer" onclick="this.parentElement.remove()">✕</span>
            </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-medium flex items-center justify-between shadow-sm">
                <span><?php echo e(session('error')); ?></span>
                <span class="font-bold text-rose-600 cursor-pointer" onclick="this.parentElement.remove()">✕</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Body Section -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-8 py-6 space-y-6">
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 px-4 sm:px-8 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>&copy; 2026 Enterprise Multi-Branch Sales & Distribution SaaS.</span>
            <span>Dockerized Laravel 11 • PostgreSQL 16 • Redis 7 • Granular RBAC</span>
        </footer>
    </div>

</body>
</html>
<?php /**PATH /Users/legacygandawijaya/Documents/SALES/resources/views/layouts/app.blade.php ENDPATH**/ ?>