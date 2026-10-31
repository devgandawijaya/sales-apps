@extends('layouts.app')

@section('title', 'Retur Penjualan (Sales Returns)')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <span>Penjualan & Distribusi</span>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Retur Penjualan</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Katalog Retur Penjualan (Good vs Bad Stock)</h1>
            <p class="text-xs text-slate-500">Kelola klaim pengembalian barang dari pelanggan, pemisahan stok baik (*Good Stock*) dan barang rusak/kadaluwarsa (*Bad Stock*) ke gudang karantina.</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('returns.create') }}" 
               class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Buat Retur Penjualan</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="p-4 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('returns.index') }}" class="flex flex-1 items-center gap-2 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Retur atau Pelanggan..." 
                   class="flex-1 max-w-xs px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
            
            <select name="return_type" class="px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <option value="">Semua Tipe Retur</option>
                <option value="GOOD_STOCK" {{ request('return_type') === 'GOOD_STOCK' ? 'selected' : '' }}>Stok Bagus (GOOD_STOCK)</option>
                <option value="BAD_STOCK_DAMAGED" {{ request('return_type') === 'BAD_STOCK_DAMAGED' ? 'selected' : '' }}>Barang Rusak (DAMAGED)</option>
                <option value="BAD_STOCK_EXPIRED" {{ request('return_type') === 'BAD_STOCK_EXPIRED' ? 'selected' : '' }}>Kedaluwarsa (EXPIRED)</option>
            </select>

            <select name="status" class="px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Menunggu Approval (PENDING)</option>
                <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Disetujui (APPROVED)</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                Filter
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">No. Retur</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Tipe Barang</th>
                        <th class="py-3 px-4">Gudang Alokasi</th>
                        <th class="py-3 px-4 text-right">Total Nilai Retur</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($returns as $r)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-600">
                                <a href="{{ route('returns.show', $r->id) }}" class="hover:underline">
                                    {{ $r->return_number }}
                                </a>
                                <span class="block text-[10px] text-slate-400 font-normal">{{ $r->return_date?->format('d M Y') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 block">{{ $r->customer?->name }}</span>
                                <span class="text-[10px] text-slate-500 font-mono block">{{ $r->customer?->code }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($r->return_type === 'GOOD_STOCK')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        Good Stock (Layak Jual)
                                    </span>
                                @elseif($r->return_type === 'BAD_STOCK_DAMAGED')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        Bad Stock (Rusak)
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        Bad Stock (Expired)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $r->warehouse?->name }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($r->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($r->status === 'APPROVED')
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        APPROVED
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        PENDING
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('returns.show', $r->id) }}" 
                                   class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                    Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada data retur penjualan yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $returns->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

