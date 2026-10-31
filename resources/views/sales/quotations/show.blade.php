@extends('layouts.app')

@section('title', 'Penawaran #' . $quotation->quotation_number)

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('quotations.index') }}" class="hover:text-blue-600 font-semibold">Penawaran Harga</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">#{{ $quotation->quotation_number }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $quotation->quotation_number }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase
                    {{ $quotation->status === 'CONVERTED_TO_SO' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                    Status: {{ $quotation->status }}
                </span>
            </div>
            <p class="text-xs text-slate-500">Pelanggan: <strong class="text-slate-800">{{ $quotation->customer?->name }}</strong> • Diterbitkan: {{ $quotation->quotation_date->format('d/m/Y') }}</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('quotations.index') }}" 
               class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                &larr; Kembali
            </a>

            @if($quotation->isConvertible())
                <button onclick="document.getElementById('modalConvertSO').classList.remove('hidden')"
                   class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-500/20 transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                    <span>Konversi Jadi Sales Order (1-Klik)</span>
                </button>
            @elseif($quotation->convertedSalesOrder)
                <a href="{{ route('sales.show', $quotation->convertedSalesOrder->id) }}" 
                   class="px-4 py-2 rounded-xl bg-purple-100 text-purple-800 font-bold text-xs hover:bg-purple-200 transition">
                    Lihat Sales Order #{{ $quotation->convertedSalesOrder->so_number }} &rarr;
                </a>
            @endif
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Item List (8 cols) -->
        <div class="lg:col-span-8 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-slate-900">Rincian Produk Penawaran</h2>
                <span class="text-xs text-slate-500">{{ $quotation->items->count() }} Item terdaftar</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Produk</th>
                            <th class="py-3 px-4">Qty & Satuan</th>
                            <th class="py-3 px-4">Harga Satuan</th>
                            <th class="py-3 px-4">Diskon</th>
                            <th class="py-3 px-4 text-right">Subtotal Bersih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($quotation->items as $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-slate-900">{{ $item->product?->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $item->product?->sku }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono">
                                    <span class="font-bold text-slate-900">{{ $item->qty_ordered }}</span> {{ $item->productUnit?->unit_name }}
                                    <span class="block text-[10px] text-slate-400">({{ $item->qty_in_base_uom }} pcs base)</span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-700">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($item->discount_percent_1 > 0 || $item->discount_percent_2 > 0)
                                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-mono text-[10px] font-bold">
                                            {{ $item->discount_percent_1 }}% + {{ $item->discount_percent_2 }}%
                                        </span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900 text-right">
                                    Rp {{ number_format($item->net_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column: Summary & Pricing Breakdown (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                <h2 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-3">Ringkasan Nilai Finansial</h2>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>Subtotal Kotor:</span>
                        <span class="font-mono text-slate-800">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-slate-500">
                        <span>Total Diskon:</span>
                        <span class="font-mono text-rose-600">- Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-slate-500 pt-2 border-t border-slate-100">
                        <span>Dasar Pengenaan Pajak (DPP):</span>
                        <span class="font-mono text-slate-800">Rp {{ number_format($quotation->subtotal - $quotation->discount_amount, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-slate-500">
                        <span>PPN (11%):</span>
                        <span class="font-mono text-slate-800">Rp {{ number_format($quotation->tax_amount, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center text-sm font-extrabold text-slate-900 pt-3 border-t border-slate-200">
                        <span>Grand Total:</span>
                        <span class="font-mono text-base text-blue-600">Rp {{ number_format($quotation->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes & Terms -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-2">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Syarat & Ketentuan</h3>
                <p class="text-xs text-slate-600">{{ $quotation->notes ?? 'Tidak ada catatan tambahan.' }}</p>
                <div class="pt-2 text-[11px] text-slate-400">
                    Berlaku sampai: <strong>{{ $quotation->expiry_date->format('d F Y') }}</strong>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modal Convert to Sales Order -->
<div id="modalConvertSO" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-extrabold text-slate-900">Konversi ke Sales Order</h3>
            <button onclick="document.getElementById('modalConvertSO').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400">✕</button>
        </div>

        <p class="text-xs text-slate-500">
            Pilih gudang pemenuhan stok untuk memproses alokasi batch FEFO dan menerbitkan pesanan resmi.
        </p>

        <form action="{{ route('quotations.convert', $quotation->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Gudang Pengeluaran Barang *</label>
                <select name="warehouse_id" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-600 focus:outline-none">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->type }})</option>
                    @endforeach
                </select>
            </div>

            @if($quotation->channel === 'MT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor PO Resmi Retail (Modern Trade) *</label>
                    <input type="text" name="po_number_mt" required placeholder="Contoh: PO-SUPERINDO-88912"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-600 focus:outline-none font-mono">
                </div>
            @endif

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalConvertSO').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-500/20 transition">
                    Konfirmasi & Buat SO &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

