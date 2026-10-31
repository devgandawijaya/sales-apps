@extends('layouts.app')

@section('title', 'Daftar Seluruh Tenant')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('superadmin.dashboard') }}" class="hover:text-blue-600 font-semibold">Superadmin</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Daftar Tenant</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Seluruh Tenant Perusahaan</h1>
            <p class="text-xs text-slate-500">Kelola status aktif/suspend, alokasi batas kuota, dan monitoring utilisasi.</p>
        </div>

        <div>
            <a href="{{ route('tenant.register') }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Daftarkan Tenant Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="p-4 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('superadmin.tenants.index') }}" class="flex flex-1 items-center gap-2 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama perusahaan, subdomain, atau kode..." 
                   class="flex-1 max-w-md px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
            
            <select name="status" class="px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Trial</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                Filter
            </button>
        </form>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Nama Perusahaan</th>
                        <th class="py-3 px-4">Subdomain</th>
                        <th class="py-3 px-4">Paket Tier</th>
                        <th class="py-3 px-4">Utilisasi (User/Cabang/Gudang)</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tenants as $t)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-extrabold text-slate-900">{{ $t->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $t->code }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold">{{ $t->subdomain }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    {{ $t->plan_tier === 'enterprise' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                    {{ $t->plan_tier === 'business' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $t->plan_tier === 'professional' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $t->plan_tier === 'basic' ? 'bg-slate-100 text-slate-700' : '' }}
                                    {{ $t->plan_tier === 'trial' ? 'bg-amber-100 text-amber-700' : '' }}">
                                    {{ $t->plan_tier }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <span class="font-bold text-slate-900">{{ $t->users->count() }}/{{ $t->max_users }}</span> user •
                                <span class="font-bold text-slate-900">{{ $t->branches->count() }}/{{ $t->max_branches }}</span> cabang •
                                <span class="font-bold text-slate-900">{{ $t->warehouses->count() }}/{{ $t->max_warehouses }}</span> gudang
                            </td>
                            <td class="py-3.5 px-4">
                                @if($t->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @elseif($t->status === 'trial')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Trial
                                    </span>
                                @elseif($t->status === 'suspended')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Suspended
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('superadmin.tenants.show', $t->id) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                                        Kelola
                                    </a>

                                    <form action="{{ route('superadmin.tenants.toggle-status', $t->id) }}" method="POST">
                                        @csrf
                                        @if($t->status === 'suspended')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold text-xs transition">
                                                Aktifkan
                                            </button>
                                        @else
                                            <input type="hidden" name="status" value="suspended">
                                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menangguhkan (suspend) tenant ini?')"
                                                    class="px-2.5 py-1.5 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-800 font-bold text-xs transition">
                                                Suspend
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Tidak ada tenant yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

