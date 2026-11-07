@extends('layouts.app')

@section('title', 'Detail Produk - ' . $product->name)
@section('page_title', 'Informasi Spesifikasi & Persediaan Produk')

@section('content')
<div class="space-y-6">

    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('products.index') }}" class="hover:underline">Katalog Produk</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">{{ $product->sku }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $product->name }}</h1>
                @if($product->is_active)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        Aktif
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                        Nonaktif
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">Merek: <b class="text-slate-700">{{ $product->brand ?? '-' }}</b> • Kategori: <b class="text-indigo-600">{{ $product->category?->name ?? 'Umum' }}</b></p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('products.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                &larr; Kembali
            </a>
            <a href="{{ route('products.edit', $product->id) }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition">
                Ubah Produk
            </a>
        </div>
    </div>

    @php
        $totalStockOnHand = $product->stocks->sum('qty_on_hand');
        $totalStockReserved = $product->stocks->sum('qty_reserved');
        $totalValuation = $totalStockOnHand * (float) $product->cost_price;
    @endphp

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Harga Modal Pokok (COGS)</span>
            <h3 class="text-2xl font-extrabold text-slate-900 mt-1">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Per 1 {{ $product->base_uom }}</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Total Stok Tersedia</span>
            <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($totalStockOnHand - $totalStockReserved, 0, ',', '.') }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">{{ $product->base_uom }} (Siap Jual)</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Stok Terikat Pesanan</span>
            <h3 class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($totalStockReserved, 0, ',', '.') }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">{{ $product->base_uom }} (Reserved SO)</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Total Nilai Valuasi Aset</span>
            <h3 class="text-xl font-extrabold text-blue-600 mt-1">Rp {{ number_format($totalValuation, 0, ',', '.') }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Fisik di Seluruh Gudang</span>
        </div>
    </div>

    <!-- Grid Detail & Multi-Satuan UOM -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Informasi Spesifikasi -->
        <div class="lg:col-span-5 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Spesifikasi Fisik Barang</h3>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Kode SKU:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $product->sku }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Barcode Utama:</span>
                    <span class="font-mono text-slate-700">{{ $product->barcode ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Kategori:</span>
                    <span class="font-bold text-indigo-700">{{ $product->category?->name ?? 'Umum' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Satuan Terkecil (Base UOM):</span>
                    <span class="font-bold text-blue-700 uppercase">{{ $product->base_uom }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Berat Barang:</span>
                    <span class="text-slate-800 font-semibold">{{ $product->weight_gram > 0 ? number_format($product->weight_gram, 2, ',', '.') . ' gram' : '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Volume Kemasan:</span>
                    <span class="text-slate-800 font-semibold">{{ $product->volume_cbm > 0 ? $product->volume_cbm . ' CBM' : '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-500">Tanggal Terdaftar:</span>
                    <span class="text-slate-700">{{ $product->created_at->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Hierarki Multi-Satuan UOM -->
        <div class="lg:col-span-7 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Hierarki Satuan Bertingkat (Multi-UOM)</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-2.5 px-3">Nama Satuan</th>
                            <th class="py-2.5 px-3">Faktor Konversi</th>
                            <th class="py-2.5 px-3">Barcode Satuan</th>
                            <th class="py-2.5 px-3 text-center">Peran Satuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($product->units as $u)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-3 font-bold text-slate-900">{{ $u->unit_name }}</td>
                            <td class="py-3 px-3 text-slate-700 font-semibold">
                                = {{ $u->conversion_factor }} {{ $product->base_uom }}
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-500">
                                {{ $u->barcode ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($u->is_base_unit)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                                        Satuan Dasar
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                        Grosir / Dus
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Sebaran Stok di Gudang & Batch FEFO -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Sebaran Stok Fisik Per Gudang & Batch FEFO</h3>
                <p class="text-xs text-slate-500">Monitoring saldo stok fisik berdasarkan nomor batch dan tanggal kedaluwarsa.</p>
            </div>
            <a href="{{ route('inventory.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                Buka Menu Gudang &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">Lokasi Gudang</th>
                        <th class="py-3.5 px-6">Nomor Lot / Batch</th>
                        <th class="py-3.5 px-6">Tanggal Kedaluwarsa</th>
                        <th class="py-3.5 px-6 text-right">Fisik (On Hand)</th>
                        <th class="py-3.5 px-6 text-right">Terikat (Reserved)</th>
                        <th class="py-3.5 px-6 text-right">Tersedia</th>
                        <th class="py-3.5 px-6 text-center">Status Gudang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($product->stocks as $stock)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3.5 px-6 font-bold text-slate-900">
                            {{ $stock->warehouse?->name ?? 'Gudang Pusat' }}
                        </td>
                        <td class="py-3.5 px-6 font-mono text-slate-700">
                            {{ $stock->batch_number }}
                        </td>
                        <td class="py-3.5 px-6 whitespace-nowrap text-slate-600">
                            {{ \Carbon\Carbon::parse($stock->expiry_date)->format('d M Y') }}
                        </td>
                        <td class="py-3.5 px-6 text-right font-bold text-slate-800">
                            {{ number_format($stock->qty_on_hand, 0, ',', '.') }} {{ $product->base_uom }}
                        </td>
                        <td class="py-3.5 px-6 text-right text-amber-600 font-semibold">
                            {{ number_format($stock->qty_reserved, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-6 text-right text-emerald-600 font-bold">
                            {{ number_format($stock->qty_on_hand - $stock->qty_reserved, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-6 text-center">
                            @if($stock->is_quarantine)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                    Karantina
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Siap Kirim
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">
                            Belum ada catatan persediaan fisik untuk produk ini di gudang manapun.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pemetaan Harga di Price List Aktif -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Harga Jual Aktif (Price Lists)</h3>
                <p class="text-xs text-slate-500">Harga jual produk ini yang berlaku di saluran pasar tradisional, ritel modern, dan grosir.</p>
            </div>
            <a href="{{ route('price-lists.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                Kelola Daftar Harga &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">Daftar Harga</th>
                        <th class="py-3.5 px-6">Saluran Pasar</th>
                        <th class="py-3.5 px-6">Satuan Jual</th>
                        <th class="py-3.5 px-6 text-center">Min. Pembelian</th>
                        <th class="py-3.5 px-6 text-right">Harga Jual Satuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($product->priceListItems as $pli)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3.5 px-6">
                            <span class="font-bold text-slate-900 block">{{ $pli->priceList?->name }}</span>
                            <span class="text-[10px] font-mono text-slate-400">{{ $pli->priceList?->code }}</span>
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                {{ $pli->priceList?->channel_type === 'MT' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $pli->priceList?->channel_type }}
                            </span>
                        </td>
                        <td class="py-3.5 px-6 font-bold text-slate-800">
                            {{ $pli->productUnit?->unit_name }}
                        </td>
                        <td class="py-3.5 px-6 text-center text-slate-600">
                            &ge; {{ $pli->min_quantity }}
                        </td>
                        <td class="py-3.5 px-6 text-right font-extrabold text-blue-600">
                            Rp {{ number_format($pli->unit_price, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500">
                            Produk ini belum dipetakan ke daftar harga manapun.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
