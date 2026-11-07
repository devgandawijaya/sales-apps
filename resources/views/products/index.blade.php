@extends('layouts.app')

@section('title', 'Katalog Master Produk')
@section('page_title', 'Master Produk & Satuan Multi-UOM')

@section('content')
<div class="space-y-6">

    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Katalog Produk & Satuan Bertingkat</h3>
            <p class="text-xs text-slate-500">Kelola master barang distribusi FMCG, kategori produk, harga modal standar, dan hierarki multi-satuan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('price-lists.index') }}" 
               class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                Daftar Harga Jual &rarr;
            </a>
            <a href="{{ route('products.create') }}" 
               class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-lg shadow-blue-500/20 transition">
                + Tambah Produk Baru
            </a>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Produk</span>
            <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">SKU Terdaftar</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Produk Aktif</span>
            <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['active'] }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Siap Diperjualbelikan</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Kategori Barang</span>
            <h3 class="text-2xl font-extrabold text-indigo-600 mt-1">{{ $stats['categories'] }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Klasifikasi Pasar</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Total Stok Fisik</span>
            <h3 class="text-2xl font-extrabold text-blue-600 mt-1">{{ number_format($stats['total_stock_units'], 0, ',', '.') }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Unit di Seluruh Gudang</span>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang, SKU, barcode, atau merek..."
                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="sm:col-span-3">
                <select name="category_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'category_id', 'status']))
                    <a href="{{ route('products.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Katalog Produk -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">SKU & Barcode</th>
                        <th class="py-3.5 px-6">Nama Produk & Merek</th>
                        <th class="py-3.5 px-6">Kategori</th>
                        <th class="py-3.5 px-6">Satuan & Konversi</th>
                        <th class="py-3.5 px-6 text-right">Harga Modal</th>
                        <th class="py-3.5 px-6 text-center">Stok Fisik</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $p)
                    @php
                        $totalStock = $p->stocks->sum('qty_on_hand');
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 px-6 whitespace-nowrap">
                            <span class="font-mono font-bold text-slate-900 block">{{ $p->sku }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $p->barcode ?? 'Tanpa Barcode' }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-900 block">{{ $p->name }}</span>
                            <span class="text-[11px] text-slate-500">Merek: {{ $p->brand ?? '-' }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ $p->category?->name ?? 'Umum' }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex flex-wrap gap-1 items-center">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    {{ $p->base_uom }} (Dasar)
                                </span>
                                @foreach($p->units->where('is_base_unit', false) as $u)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $u->unit_name }} (x{{ $u->conversion_factor }})
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="py-4 px-6 text-right font-semibold text-slate-800 whitespace-nowrap">
                            Rp {{ number_format($p->cost_price, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-6 text-center whitespace-nowrap">
                            @if($totalStock > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ number_format($totalStock, 0, ',', '.') }} {{ $p->base_uom }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Kosong
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if($p->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('products.show', $p->id) }}" 
                                   title="Lihat Detail Produk"
                                   class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('products.edit', $p->id) }}" 
                                   title="Ubah Produk"
                                   class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('products.destroy', $p->id) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus atau menonaktifkan produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus / Nonaktifkan" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-500">
                            Tidak ada produk ditemukan sesuai kriteria pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
