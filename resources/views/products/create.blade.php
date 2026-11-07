@extends('layouts.app')

@section('title', 'Tambah Produk Baru')
@section('page_title', 'Registrasi Produk & Multi-UOM')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('products.index') }}" class="hover:underline">Katalog Produk</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Tambah Produk</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Formulir Pendaftaran Produk Baru</h1>
            <p class="text-xs text-slate-500">Lengkapi data pokok produk dan konfigurasi satuan bertingkat (multi-UOM).</p>
        </div>
        <a href="{{ route('products.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('products.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Informasi Utama Produk -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Utama Produk</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Kopi Susu Gula Aren 250ml"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('name') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode SKU Produk <span class="text-rose-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku') }}" required placeholder="Contoh: PRD-KOPI-AREN-250"
                           class="w-full px-3.5 py-2 text-xs font-mono uppercase rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('sku') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Barcode Utama (EAN/UPC)</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}" placeholder="Contoh: 8991234567890"
                           class="w-full px-3.5 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('barcode') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Barang <span class="text-rose-500">*</span></label>
                    <select name="category_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Merek / Brand</label>
                    <input type="text" name="brand" value="{{ old('brand') }}" placeholder="Contoh: Kenangan / Indofood"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Modal Pokok / COGS (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', 0) }}" required
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <span class="text-[10px] text-slate-400">Harga per satuan dasar (base UOM) untuk valuasi aset gudang.</span>
                    @error('cost_price') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan Dasar Terkecil (Base UOM) <span class="text-rose-500">*</span></label>
                    <input type="text" name="base_uom" id="base_uom_input" value="{{ old('base_uom', 'PCS') }}" required placeholder="Contoh: PCS, BOTOL, BUNGKUS, SAK"
                           class="w-full px-3.5 py-2 text-xs uppercase font-bold text-blue-700 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <span class="text-[10px] text-slate-400">Satuan terkecil yang digunakan untuk pencatatan mutasi stok.</span>
                    @error('base_uom') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Berat Per Satuan Dasar (Gram)</label>
                    <input type="number" step="0.01" name="weight_gram" value="{{ old('weight_gram', 0) }}"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Volume Per Satuan Dasar (CBM)</label>
                    <input type="number" step="0.0001" name="volume_cbm" value="{{ old('volume_cbm', 0) }}"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>
        </div>

        <!-- Hierarki Multi-Satuan UOM -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Satuan Bertingkat Tambahan (Multi-UOM)</h3>
                    <p class="text-xs text-slate-500">Daftarkan satuan penjualan grosir/retail seperti Dus, Karton, atau Pak beserta rasio konversinya.</p>
                </div>
                <button type="button" id="btn-add-unit" 
                        class="px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition">
                    + Tambah Satuan
                </button>
            </div>

            <!-- Satuan Dasar Info Box -->
            <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-200 text-xs text-blue-800 flex items-center justify-between">
                <div>
                    <span class="font-bold">Satuan Dasar Terdaftar:</span>
                    <span id="label-base-uom" class="font-mono font-bold text-blue-900 uppercase">PCS</span>
                    <span class="text-blue-600 ml-1">(Faktor Konversi: 1)</span>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-200 text-blue-900">Terkunci Otomatis</span>
            </div>

            <div id="units-container" class="space-y-3">
                <!-- Baris Satuan Dinamis -->
                <div class="unit-row grid grid-cols-1 sm:grid-cols-12 gap-3 items-center p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="sm:col-span-4">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">NAMA SATUAN (UOM)</label>
                        <input type="text" name="units[0][unit_name]" placeholder="Misal: DUS / KARTON"
                               class="w-full px-3 py-1.5 text-xs font-bold uppercase rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div class="sm:col-span-4">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">FAKTOR KONVERSI (KE BASE UOM)</label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-slate-500 font-bold">=</span>
                            <input type="number" name="units[0][conversion_factor]" min="2" placeholder="Misal: 24"
                                   class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <span class="text-xs text-slate-500 base-uom-suffix font-semibold">PCS</span>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">BARCODE SATUAN</label>
                        <input type="text" name="units[0][barcode]" placeholder="Barcode kardus (opsional)"
                               class="w-full px-3 py-1.5 text-xs font-mono rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div class="sm:col-span-1 text-center pt-4">
                        <button type="button" class="btn-remove-unit text-rose-500 hover:text-rose-700 text-xs font-bold" title="Hapus Satuan">
                            ✕
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-lg shadow-blue-500/20 transition">
                Simpan Produk Baru
            </button>
        </div>
    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const baseUomInput = document.getElementById('base_uom_input');
    const labelBaseUom = document.getElementById('label-base-uom');
    const container = document.getElementById('units-container');
    const btnAdd = document.getElementById('btn-add-unit');
    let unitIndex = 1;

    function updateBaseUomLabels() {
        const val = (baseUomInput.value || 'PCS').toUpperCase();
        labelBaseUom.textContent = val;
        document.querySelectorAll('.base-uom-suffix').forEach(el => el.textContent = val);
    }

    baseUomInput.addEventListener('input', updateBaseUomLabels);

    btnAdd.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'unit-row grid grid-cols-1 sm:grid-cols-12 gap-3 items-center p-3 rounded-xl bg-slate-50 border border-slate-200';
        row.innerHTML = `
            <div class="sm:col-span-4">
                <label class="block text-[10px] font-bold text-slate-500 mb-1">NAMA SATUAN (UOM)</label>
                <input type="text" name="units[${unitIndex}][unit_name]" placeholder="Misal: PAK / RENCENG"
                       class="w-full px-3 py-1.5 text-xs font-bold uppercase rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>
            <div class="sm:col-span-4">
                <label class="block text-[10px] font-bold text-slate-500 mb-1">FAKTOR KONVERSI (KE BASE UOM)</label>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs text-slate-500 font-bold">=</span>
                    <input type="number" name="units[${unitIndex}][conversion_factor]" min="2" placeholder="Misal: 6"
                           class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <span class="text-xs text-slate-500 base-uom-suffix font-semibold">${baseUomInput.value || 'PCS'}</span>
                </div>
            </div>
            <div class="sm:col-span-3">
                <label class="block text-[10px] font-bold text-slate-500 mb-1">BARCODE SATUAN</label>
                <input type="text" name="units[${unitIndex}][barcode]" placeholder="Barcode opsional"
                       class="w-full px-3 py-1.5 text-xs font-mono rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>
            <div class="sm:col-span-1 text-center pt-4">
                <button type="button" class="btn-remove-unit text-rose-500 hover:text-rose-700 text-xs font-bold" title="Hapus Satuan">
                    ✕
                </button>
            </div>
        `;
        container.appendChild(row);
        unitIndex++;
    });

    container.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-remove-unit')) {
            e.target.closest('.unit-row').remove();
        }
    });

    updateBaseUomLabels();
});
</script>
@endsection
