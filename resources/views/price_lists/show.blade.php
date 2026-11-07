@extends('layouts.app')

@section('title', 'Matriks Harga - ' . $priceList->name)
@section('page_title', 'Kelola Matriks Harga Jual Barang')

@section('content')
<div class="space-y-6">

    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('price-lists.index') }}" class="hover:underline">Daftar Harga</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">{{ $priceList->code }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $priceList->name }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold 
                    {{ $priceList->channel_type === 'MT' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                    Kanal: {{ $priceList->channel_type }}
                </span>
                @if($priceList->is_active)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        Aktif
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                        Nonaktif
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Mata Uang: <b class="text-slate-700">{{ $priceList->currency }}</b> • 
                Masa Berlaku: {{ \Carbon\Carbon::parse($priceList->valid_from)->format('d M Y') }}
                @if($priceList->valid_to)
                    s/d {{ \Carbon\Carbon::parse($priceList->valid_to)->format('d M Y') }}
                @else
                    <span class="text-emerald-600 font-semibold">(Tanpa Batas Akhir)</span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('price-lists.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                &larr; Kembali
            </a>
            <a href="{{ route('price-lists.edit', $priceList->id) }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition">
                Ubah Skema
            </a>
        </div>
    </div>

    <!-- Form Tambah / Update Harga Item Produk -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">+ Tetapkan / Tambah Harga Jual Produk</h3>
                <p class="text-xs text-slate-500">Pilih produk dan satuan yang ingin ditetapkan tarif harganya pada daftar harga ini.</p>
            </div>
        </div>

        <form action="{{ route('price-lists.items.store', $priceList->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            @csrf

            <div class="sm:col-span-4">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Produk <span class="text-rose-500">*</span></label>
                <select name="product_id" id="select-product" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                    <option value="">-- Pilih Produk Katalog --</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}">
                            {{ $prod->sku }} - {{ $prod->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan Jual (UOM) <span class="text-rose-500">*</span></label>
                <select name="product_unit_id" id="select-unit" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                    <option value="">-- Pilih Satuan --</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Min. Pembelian <span class="text-rose-500">*</span></label>
                <input type="number" name="min_quantity" value="1" min="1" required
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" name="unit_price" required placeholder="Contoh: 125000"
                       class="w-full px-3 py-2 text-xs font-semibold text-slate-900 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="sm:col-span-1">
                <button type="submit" class="w-full py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    <!-- Filter & Table Matriks Harga -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden space-y-4">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Item & Harga Terpetakan (Total: {{ $priceList->items_count }} Item)</h3>
                <p class="text-xs text-slate-500">Harga per satuan barang yang berlaku saat pembuatan penawaran atau pesanan penjualan.</p>
            </div>

            <form method="GET" action="{{ route('price-lists.show', $priceList->id) }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau SKU..."
                       class="px-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('price-lists.show', $priceList->id) }}" class="px-2.5 py-1.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">SKU & Barcode</th>
                        <th class="py-3.5 px-6">Nama Produk Katalog</th>
                        <th class="py-3.5 px-6">Kategori</th>
                        <th class="py-3.5 px-6">Satuan Jual (UOM)</th>
                        <th class="py-3.5 px-6 text-center">Min. Qty</th>
                        <th class="py-3.5 px-6 text-right">Harga Jual Satuan</th>
                        <th class="py-3.5 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $it)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 px-6 whitespace-nowrap">
                            <span class="font-mono font-bold text-slate-900 block">{{ $it->product?->sku }}</span>
                            <span class="text-[10px] font-mono text-slate-400">{{ $it->productUnit?->barcode ?? $it->product?->barcode ?? '-' }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-900 block">{{ $it->product?->name }}</span>
                            <span class="text-[11px] text-slate-500">Merek: {{ $it->product?->brand ?? '-' }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700">
                                {{ $it->product?->category?->name ?? 'Umum' }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-bold text-blue-700 block">{{ $it->productUnit?->unit_name }}</span>
                            <span class="text-[10px] text-slate-400">= {{ $it->productUnit?->conversion_factor }} {{ $it->product?->base_uom }}</span>
                        </td>
                        <td class="py-4 px-6 text-center font-semibold text-slate-700">
                            &ge; {{ $it->min_quantity }}
                        </td>
                        <td class="py-4 px-6 text-right font-extrabold text-blue-600 text-sm whitespace-nowrap">
                            Rp {{ number_format($it->unit_price, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-6 text-center">
                            <form action="{{ route('price-lists.items.destroy', [$priceList->id, $it->id]) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus item harga ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus Harga" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500">
                            Belum ada item harga terdaftar pada daftar harga ini. Silakan gunakan formulir di atas untuk menambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $items->links() }}
        </div>
        @endif
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const productsData = @json($products);
    const selectProduct = document.getElementById('select-product');
    const selectUnit = document.getElementById('select-unit');

    selectProduct.addEventListener('change', function () {
        const prodId = parseInt(this.value);
        selectUnit.innerHTML = '<option value="">-- Pilih Satuan --</option>';

        if (!prodId) return;

        const product = productsData.find(p => p.id === prodId);
        if (product && product.units) {
            product.units.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = `${u.unit_name} (Konversi: ${u.conversion_factor} ${product.base_uom})`;
                selectUnit.appendChild(opt);
            });
        }
    });
});
</script>
@endsection
