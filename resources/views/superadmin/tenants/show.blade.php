@extends('layouts.app')

@section('title', 'Detail Tenant - ' . $tenant->name)

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('superadmin.dashboard') }}" class="hover:text-blue-600 font-semibold">Superadmin</a>
                <span>&rsaquo;</span>
                <a href="{{ route('superadmin.tenants.index') }}" class="hover:text-blue-600 font-semibold">Daftar Tenant</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">{{ $tenant->name }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $tenant->name }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                    {{ $t->plan_tier ?? $tenant->plan_tier === 'business' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                    Tier {{ $tenant->plan_tier }}
                </span>
            </div>
            <p class="text-xs text-slate-500">ID: <code class="font-mono text-slate-600">{{ $tenant->id }}</code> • Subdomain: <strong class="text-slate-800">{{ $tenant->subdomain }}</strong></p>
        </div>

        <div class="flex items-center space-x-3">
            <form action="{{ route('superadmin.tenants.toggle-status', $tenant->id) }}" method="POST">
                @csrf
                @if($tenant->status === 'suspended')
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                        ✓ Aktifkan Layanan Tenant
                    </button>
                @else
                    <input type="hidden" name="status" value="suspended">
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menangguhkan layanan tenant ini?')"
                            class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition">
                        ✕ Tangguhkan (Suspend) Tenant
                    </button>
                @endif
            </form>
        </div>
    </div>

    <!-- Feedback Message -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Tenant Entities & Users (7 cols) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Companies & Branches Card -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-extrabold text-slate-900">Entitas Organisasi & Cabang</h2>
                    <span class="text-xs text-slate-500">{{ $tenant->branches->count() }} Cabang terdaftar</span>
                </div>

                <div class="space-y-3">
                    @forelse($tenant->branches as $branch)
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-slate-900 text-xs">{{ $branch->name }}</span>
                                <div class="text-[11px] text-slate-500">{{ $branch->code }} • {{ $branch->city }}, {{ $branch->province }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-200 text-slate-700">
                                {{ $branch->type }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada cabang terdaftar.</p>
                    @endforelse
                </div>
            </div>

            <!-- Warehouses Card -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-extrabold text-slate-900">Gudang Penyimpanan (Warehouses)</h2>
                    <span class="text-xs text-slate-500">{{ $tenant->warehouses->count() }} Gudang terdaftar</span>
                </div>

                <div class="space-y-3">
                    @forelse($tenant->warehouses as $wh)
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-slate-900 text-xs">{{ $wh->name }}</span>
                                <div class="text-[11px] text-slate-500">{{ $wh->code }} • Cabang: {{ $wh->branch?->name }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-700">
                                {{ $wh->type }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada gudang terdaftar.</p>
                    @endforelse
                </div>
            </div>

            <!-- Users in Tenant -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-extrabold text-slate-900">Daftar Pengguna Tenant</h2>
                    <span class="text-xs text-slate-500">{{ $tenant->users->count() }} User aktif</span>
                </div>

                <div class="space-y-2">
                    @foreach($tenant->users as $u)
                        <div class="p-3 rounded-xl border border-slate-100 flex items-center justify-between text-xs hover:bg-slate-50 transition">
                            <div>
                                <span class="font-bold text-slate-900">{{ $u->name }}</span>
                                <span class="block text-[11px] text-slate-500">{{ $u->email }} • {{ $u->role_title }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $u->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Right Column: Quota Adjustment & Subscription (5 cols) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Edit Quota Form -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-extrabold text-slate-900">Atur Batas Kuota Paket</h2>
                    <p class="text-[11px] text-slate-500">Sesuaikan batasan sumber daya khusus untuk tenant ini.</p>
                </div>

                <form action="{{ route('superadmin.tenants.update-quota', $tenant->id) }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Maksimal User</label>
                        <input type="number" name="max_users" value="{{ old('max_users', $tenant->max_users) }}" required min="1"
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Maksimal Perusahaan (Holding/Company)</label>
                        <input type="number" name="max_companies" value="{{ old('max_companies', $tenant->max_companies) }}" required min="1"
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Maksimal Cabang (Branches)</label>
                        <input type="number" name="max_branches" value="{{ old('max_branches', $tenant->max_branches) }}" required min="1"
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Maksimal Gudang (Warehouses)</label>
                        <input type="number" name="max_warehouses" value="{{ old('max_warehouses', $tenant->max_warehouses) }}" required min="1"
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Batas Penyimpanan Dokumen / File (MB)</label>
                        <input type="number" name="storage_limit_mb" value="{{ old('storage_limit_mb', $tenant->storage_limit_mb) }}" required min="100"
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition shadow-md">
                        Simpan Penyesuaian Kuota &rarr;
                    </button>
                </form>
            </div>

            <!-- Subscription Contract Summary -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
                <h2 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-2">Kontrak Berlangganan</h2>
                @if($tenant->currentSubscription)
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Paket:</span>
                            <strong class="text-slate-800">{{ $tenant->currentSubscription->plan?->name }}</strong>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Siklus Penagihan:</span>
                            <span class="font-bold text-slate-800 uppercase">{{ $tenant->currentSubscription->billing_cycle }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Nilai Tagihan:</span>
                            <strong class="text-emerald-600">Rp {{ number_format($tenant->currentSubscription->amount, 0, ',', '.') }}</strong>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Periode Aktif:</span>
                            <span class="text-slate-700">{{ $tenant->currentSubscription->current_period_start }} s/d {{ $tenant->currentSubscription->current_period_end }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-400">Tidak ada riwayat langganan tercatat.</p>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection

