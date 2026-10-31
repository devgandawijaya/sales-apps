@extends('layouts.app')

@section('title', 'Buku Besar Stok (Immutable Ledger)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Buku Besar Stok (Inventory Ledger)</h1>
            <p class="text-sm text-slate-500 mt-1">Audit log persediaan fisik permanen (Append-Only) dengan pencatatan mutasi double-entry.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.ledger.export') }}" class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold px-3.5 py-2 rounded-xl shadow-sm transition">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Unduh CSV Kartu Stok</span>
            </a>
            <div class="inline-flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold px-3 py-2 rounded-xl">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Integritas Ledger 100%
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('inventory.ledger') }}" method="GET" class="flex flex-wrap gap-3 items-center">
            <select name="warehouse_id" class="text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                <option value="">Semua Gudang</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>

            <select name="entry_type" class="text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                <option value="">Semua Jenis Mutasi</option>
                <option value="IN" {{ request('entry_type') == 'IN' ? 'selected' : '' }}>IN (Penerimaan)</option>
                <option value="OUT" {{ request('entry_type') == 'OUT' ? 'selected' : '' }}>OUT (Pengeluaran SO)</option>
                <option value="TRANSFER_OUT" {{ request('entry_type') == 'TRANSFER_OUT' ? 'selected' : '' }}>TRANSFER_OUT (Kirim Gudang)</option>
                <option value="TRANSFER_IN" {{ request('entry_type') == 'TRANSFER_IN' ? 'selected' : '' }}>TRANSFER_IN (Terima Gudang)</option>
                <option value="OPNAME_ADJUST_IN" {{ request('entry_type') == 'OPNAME_ADJUST_IN' ? 'selected' : '' }}>OPNAME_ADJUST_IN (Selisih +)</option>
                <option value="OPNAME_ADJUST_OUT" {{ request('entry_type') == 'OPNAME_ADJUST_OUT' ? 'selected' : '' }}>OPNAME_ADJUST_OUT (Selisih -)</option>
            </select>

            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Batch / Referensi..." class="text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 w-52">
            <button type="submit" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 rounded-lg text-xs font-semibold text-slate-700">Filter</button>
        </form>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200">
                        <th class="py-3 px-4">Waktu Transaksi</th>
                        <th class="py-3 px-4">Gudang</th>
                        <th class="py-3 px-4">SKU & Nama Produk</th>
                        <th class="py-3 px-4">No. Batch</th>
                        <th class="py-3 px-4">Tipe Mutasi</th>
                        <th class="py-3 px-4">Referensi Dokumen</th>
                        <th class="py-3 px-4 text-center">Perubahan</th>
                        <th class="py-3 px-4 text-center">Stok Awal</th>
                        <th class="py-3 px-4 text-center">Stok Akhir</th>
                        <th class="py-3 px-4 text-right">Nilai Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($entries as $entry)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                                {{ $entry->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">{{ $entry->warehouse->name ?? '-' }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $entry->product->name ?? '-' }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $entry->product->sku ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono font-medium">{{ $entry->batch_number }}</td>
                            <td class="py-3 px-4">
                                @if(str_contains($entry->entry_type, 'IN'))
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">{{ $entry->entry_type }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">{{ $entry->entry_type }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-mono text-slate-800 font-semibold block">{{ $entry->reference_number ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400 uppercase">{{ $entry->reference_type }}</span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold font-mono {{ $entry->qty_change > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $entry->qty_change > 0 ? '+' : '' }}{{ $entry->qty_change }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono text-slate-500">{{ $entry->qty_before }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">{{ $entry->qty_after }}</td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-slate-800">
                                Rp {{ number_format($entry->total_cost, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400">Belum ada transaksi pada buku besar stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $entries->links() }}
        </div>
    </div>
</div>
@endsection

