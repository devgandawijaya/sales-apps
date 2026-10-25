@extends('layouts.app')

@section('title', 'Gudang & Manajemen Stok FEFO')
@section('page_title', 'Persediaan Multi-Gudang & Alokasi FEFO')

@section('content')
<div class="space-y-6">

    <!-- Filter Gudang & Pencarian -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang, SKU, atau nomor batch lot..."
                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="sm:col-span-5">
                <select name="warehouse_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }} ({{ $wh->type }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-1">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Kartu Stok FEFO -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Kartu Stok Gudang (Prioritas FEFO)</h3>
                <p class="text-xs text-slate-500">Urutan barang siap jual berdasarkan masa kedaluwarsa terdekat</p>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                FEFO Priority Engine
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">Gudang</th>
                        <th class="py-3.5 px-6">SKU & Nama Produk</th>
                        <th class="py-3.5 px-6">Nomor Lot / Batch</th>
                        <th class="py-3.5 px-6 text-center">Kedaluwarsa (Expiry)</th>
                        <th class="py-3.5 px-6 text-center">Status FEFO</th>
                        <th class="py-3.5 px-6 text-right">Stok Fisik</th>
                        <th class="py-3.5 px-6 text-right">Reserved</th>
                        <th class="py-3.5 px-6 text-right">Tersedia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stocks as $s)
                    @php
                        $isExpired = strtotime($s->expiry_date) < time();
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-800 block">{{ $s->warehouse_name }}</span>
                            <span class="text-[10px] text-slate-400 uppercase font-mono">{{ $s->warehouse_type }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-semibold text-slate-900 block">{{ $s->product_name }}</span>
                            <span class="font-mono text-[11px] text-slate-400">{{ $s->product_sku }}</span>
                        </td>
                        <td class="py-4 px-6 font-mono text-slate-700">{{ $s->batch_number }}</td>
                        <td class="py-4 px-6 text-center whitespace-nowrap {{ $isExpired ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                            {{ $s->expiry_date }}
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if($isExpired)
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                ⚠️ Kedaluwarsa (Bad Stock)
                            </span>
                            @else
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                ✓ Siap Alokasi (Good Stock)
                            </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right font-bold text-slate-900">{{ number_format($s->qty_on_hand, 0, ',', '.') }} {{ $s->base_uom }}</td>
                        <td class="py-4 px-6 text-right text-amber-600 font-medium">{{ number_format($s->qty_reserved, 0, ',', '.') }}</td>
                        <td class="py-4 px-6 text-right font-extrabold text-indigo-600">
                            {{ number_format($s->qty_on_hand - $s->qty_reserved, 0, ',', '.') }} {{ $s->base_uom }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">Tidak ada stok yang sesuai.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stocks->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $stocks->links() }}
        </div>
        @endif
    </div>

    <!-- Stock Transfers Antar Cabang -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Transfer Antar-Cabang & Status In-Transit</h3>
            <p class="text-xs text-slate-500">Pelacakan logistik armada kirim dari gudang asal menuju gudang tujuan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3 px-6">Nomor Transfer</th>
                        <th class="py-3 px-6">Gudang Asal</th>
                        <th class="py-3 px-6">Gudang Tujuan</th>
                        <th class="py-3 px-6">Armada / Pengemudi</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6">Waktu Kirim</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transfers as $t)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-6 font-bold text-slate-900">{{ $t->transfer_number }}</td>
                        <td class="py-3.5 px-6 text-slate-700">{{ $t->from_warehouse_name }}</td>
                        <td class="py-3.5 px-6 text-slate-700">{{ $t->to_warehouse_name }}</td>
                        <td class="py-3.5 px-6 text-slate-600">{{ $t->driver_name }} ({{ $t->vehicle_number }})</td>
                        <td class="py-3.5 px-6 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                {{ $t->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-6 text-slate-500">{{ $t->sent_date }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-500">Belum ada mutasi transfer antar-cabang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

