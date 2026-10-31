@extends('layouts.app')

@section('title', 'Buat Pengajuan Retur Penjualan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('returns.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Daftar Retur
        </a>
    </div>

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Manajemen Pengembalian Barang</span>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Formulir Klaim Retur Penjualan (Sales Return)</h1>
            <p class="text-xs text-slate-500">Pilih pelanggan, tentukan kondisi fisik barang (Good Stock atau Bad Stock/Rusak), dan arahkan barang ke gudang penampungan.</p>
        </div>

        <form action="{{ route('returns.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Customer & Branch Info -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                <h3 class="text-xs font-bold uppercase text-slate-600 tracking-wider">1. Pelanggan & Entitas</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kantor Cabang *</label>
                        <select name="branch_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pelanggan Pengaju *</label>
                        <select name="customer_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->channel }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Kondisi Retur *</label>
                        <select name="return_type" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white font-semibold">
                            <option value="BAD_STOCK_DAMAGED">Bad Stock - Rusak Fisik / Bocor / Pecah</option>
                            <option value="BAD_STOCK_EXPIRED">Bad Stock - Kedaluwarsa (Expired)</option>
                            <option value="GOOD_STOCK">Good Stock - Barang Masih Bagus / Batal Toko</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Gudang Alokasi Masuk *</label>
                        <select name="warehouse_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->type }})</option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Saran: Pilih Gudang Karantina Bad Stock jika barang rusak.</span>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Referensi Faktur Penjualan Asal (Opsional)</label>
                        <select name="invoice_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                            <option value="">-- Tanpa Referensi Faktur Spesifik --</option>
                            @foreach($invoices as $inv)
                                <option value="{{ $inv->id }}">{{ $inv->invoice_number }} - {{ $inv->customer?->name }} (Rp {{ number_format($inv->grand_total, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Product & Batch -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                <h3 class="text-xs font-bold uppercase text-slate-600 tracking-wider">2. Rincian Barang yang Diretur</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Produk *</label>
                        <select name="product_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->sku }} - {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan UOM *</label>
                        <select name="product_unit_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                            @foreach($products->flatMap->units->unique('id') as $u)
                                <option value="{{ $u->id }}">{{ $u->unit_name }} (Isi: {{ $u->conversion_factor }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Batch / Lot *</label>
                        <input type="text" name="batch_number" value="{{ old('batch_number', 'LOT-' . date('Ym') . '-RET') }}" required
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah (Kuantitas) *</label>
                        <input type="number" name="qty" value="{{ old('qty', 1) }}" min="1" required
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Satuan Pengembalian (IDR) *</label>
                        <input type="number" step="0.01" name="unit_price" value="{{ old('unit_price', 100000) }}" min="0" required
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white font-mono font-bold">
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Retur</label>
                        <input type="text" name="reason" value="{{ old('reason') }}" placeholder="Contoh: Kemasan pecah saat penurunan muatan armada kirim"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('returns.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Pengajuan Retur</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

