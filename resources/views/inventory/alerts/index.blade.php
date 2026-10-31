@extends('layouts.app')

@section('title', 'Peringatan Kadaluwarsa (Expiry Alerts FEFO)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Peringatan Kadaluwarsa & Audit FEFO</h1>
            <p class="text-sm text-slate-500 mt-1">Sistem deteksi dini stok mendekati expired (Near-Expiry & Dead Stock Monitoring).</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.ledger') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                &larr; Lihat Buku Besar Stok
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Fisik Stok On-Hand</span>
            <span class="text-xl font-bold text-slate-800 mt-1 block">{{ number_format($valuation['total_qty_on_hand']) }} Unit</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Valuasi Nilai Stok</span>
            <span class="text-xl font-bold text-blue-700 mt-1 block font-mono">Rp {{ number_format($valuation['total_valuation'], 0, ',', '.') }}</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Batch Kadaluwarsa / Kritis</span>
            <span class="text-xl font-bold text-rose-600 mt-1 block">
                {{ $alerts->whereIn('severity', ['EXPIRED', 'CRITICAL'])->count() }} Batch
            </span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Batch Peringatan (< 90 Hari)</span>
            <span class="text-xl font-bold text-amber-600 mt-1 block">
                {{ $alerts->count() }} Batch
            </span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('inventory.expiry-alerts') }}" method="GET" class="flex flex-wrap gap-3 items-center">
            <select name="warehouse_id" class="text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                <option value="">Semua Gudang</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>

            <select name="threshold" class="text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                <option value="30" {{ $threshold == 30 ? 'selected' : '' }}>Mendekati Expired (< 30 Hari)</option>
                <option value="60" {{ $threshold == 60 ? 'selected' : '' }}>Mendekati Expired (< 60 Hari)</option>
                <option value="90" {{ $threshold == 90 ? 'selected' : '' }}>Mendekati Expired (< 90 Hari)</option>
                <option value="180" {{ $threshold == 180 ? 'selected' : '' }}>Mendekati Expired (< 180 Hari)</option>
            </select>
        </form>
    </div>

    <!-- Alert Batches Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200">
                        <th class="py-3 px-4">Tingkat Urgensi</th>
                        <th class="py-3 px-4">Gudang</th>
                        <th class="py-3 px-4">SKU & Nama Produk</th>
                        <th class="py-3 px-4">No. Batch</th>
                        <th class="py-3 px-4">Tanggal Kadaluwarsa</th>
                        <th class="py-3 px-4 text-center">Sisa Hari</th>
                        <th class="py-3 px-4 text-center">Stok On Hand</th>
                        <th class="py-3 px-4 text-center">Stok Bebas</th>
                        <th class="py-3 px-4 text-right">Nilai Batch</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($alerts as $alert)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $alert['badge_class'] }}">
                                    {{ $alert['severity'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4">{{ $alert['warehouse_name'] }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $alert['product_name'] }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $alert['product_sku'] }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono font-medium">{{ $alert['batch_number'] }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $alert['expiry_date'] }}</td>
                            <td class="py-3 px-4 text-center font-bold {{ $alert['days_left'] <= 30 ? 'text-rose-600' : 'text-amber-600' }}">
                                {{ $alert['days_left'] <= 0 ? 'KADALUWARSA' : $alert['days_left'] . ' Hari' }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $alert['qty_on_hand'] }}</td>
                            <td class="py-3 px-4 text-center font-bold text-emerald-700">{{ $alert['qty_available'] }}</td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-slate-800">
                                Rp {{ number_format($alert['stock_value'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">Tidak ada batch produk mendekati masa kadaluwarsa pada kriteria ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

