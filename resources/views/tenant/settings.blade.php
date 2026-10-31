@extends('layouts.app')

@section('title', 'Pengaturan & Kuota Tenant')

@section('content')
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
                {{ $tenant->plan_tier === 'business' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200' }}">
                Tier: {{ $tenant->plan_tier }}
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                Status: {{ $tenant->status }}
            </span>
        </div>
    </div>

    <!-- Quota Utilization Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- User Quota -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase">
                <span>Pengguna (Users)</span>
                <span>{{ $usage['users']['used'] }} / {{ $usage['users']['limit'] }}</span>
            </div>
            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ min(100, $usage['users']['percentage']) }}%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500">
                <span>Terpakai: <strong>{{ $usage['users']['percentage'] }}%</strong></span>
                <span>Sisa: <strong>{{ max(0, $usage['users']['limit'] - $usage['users']['used']) }} slot</strong></span>
            </div>
        </div>

        <!-- Branch Quota -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase">
                <span>Cabang (Branches)</span>
                <span>{{ $usage['branches']['used'] }} / {{ $usage['branches']['limit'] }}</span>
            </div>
            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                <div class="bg-purple-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ min(100, $usage['branches']['percentage']) }}%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500">
                <span>Terpakai: <strong>{{ $usage['branches']['percentage'] }}%</strong></span>
                <span>Sisa: <strong>{{ max(0, $usage['branches']['limit'] - $usage['branches']['used']) }} cabang</strong></span>
            </div>
        </div>

        <!-- Warehouse Quota -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase">
                <span>Gudang (Warehouses)</span>
                <span>{{ $usage['warehouses']['used'] }} / {{ $usage['warehouses']['limit'] }}</span>
            </div>
            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                <div class="bg-emerald-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ min(100, $usage['warehouses']['percentage']) }}%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500">
                <span>Terpakai: <strong>{{ $usage['warehouses']['percentage'] }}%</strong></span>
                <span>Sisa: <strong>{{ max(0, $usage['warehouses']['limit'] - $usage['warehouses']['used']) }} gudang</strong></span>
            </div>
        </div>

        <!-- Holding Company Quota -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase">
                <span>Badan Usaha (Company)</span>
                <span>{{ $usage['companies']['used'] }} / {{ $usage['companies']['limit'] }}</span>
            </div>
            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                <div class="bg-amber-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ min(100, $usage['companies']['percentage']) }}%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500">
                <span>Terpakai: <strong>{{ $usage['companies']['percentage'] }}%</strong></span>
                <span>Sisa: <strong>{{ max(0, $usage['companies']['limit'] - $usage['companies']['used']) }} entitas</strong></span>
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
                    <strong class="text-slate-900">{{ $tenant->name }}</strong>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Kode Tenant:</span>
                    <span class="font-mono font-bold text-slate-700">{{ $tenant->code }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Subdomain Workspace:</span>
                    <span class="font-mono text-blue-600 font-bold">{{ $tenant->subdomain }}.app</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Custom Domain:</span>
                    <span class="text-slate-700">{{ $tenant->custom_domain ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Tanggal Terdaftar:</span>
                    <span class="text-slate-700">{{ $tenant->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Subscription & Billing Summary -->
        <div class="lg:col-span-6 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-3">Informasi Berlangganan SaaS</h2>

            @if($subscription)
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Paket Terdaftar:</span>
                        <strong class="text-slate-900">{{ $subscription->plan?->name }}</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Siklus Penagihan:</span>
                        <span class="font-bold text-slate-700 uppercase">{{ $subscription->billing_cycle }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Biaya Langganan:</span>
                        <strong class="text-emerald-600">Rp {{ number_format($subscription->amount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Periode Aktif:</span>
                        <span class="font-bold text-slate-800">{{ $subscription->current_period_start }} s/d {{ $subscription->current_period_end }}</span>
                    </div>
                </div>
            @else
                <p class="text-xs text-slate-400">Tidak ada data langganan.</p>
            @endif

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500">Ingin menambah kuota user / cabang?</span>
                <a href="mailto:support@enterprisesales.co.id?subject=Upgrade Paket Tenant {{ $tenant->name }}" 
                   class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                    Hubungi Dukungan &rarr;
                </a>
            </div>
        </div>

    </div>

</div>
@endsection

