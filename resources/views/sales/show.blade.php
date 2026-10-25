@extends('layouts.app')

@section('title', 'Detail Sales Order ' . $order->so_number)
@section('page_title', 'Rincian Pesanan Penjualan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Daftar
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 transition">
                🖨️ Cetak Dokumen
            </button>
        </div>
    </div>

    <!-- Main Order Invoice Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-sm space-y-6">
        <!-- Invoice Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-6">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Dokumen Resmi Sales Order</span>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $order->so_number }}</h3>
                <p class="text-xs text-slate-500 mt-1">Tanggal Pesanan: <b class="text-slate-700">{{ $order->order_date }}</b></p>
            </div>
            <div class="text-left sm:text-right space-y-1">
                <div>
                    @if($order->channel === 'GT')
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">General Trade (GT)</span>
                    @else
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">Modern Trade (MT)</span>
                    @endif
                </div>
                <div>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        Status: {{ $order->status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 2 Kolom: Pelanggan & Pengiriman -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1.5">
                <span class="font-bold text-slate-500 uppercase text-[10px] tracking-wider block">Informasi Pelanggan</span>
                <p class="text-sm font-bold text-slate-900">{{ $order->customer_name }}</p>
                <p class="text-slate-500">Kode: <span class="font-mono text-slate-700">{{ $order->customer_code }}</span></p>
                <p class="text-slate-500">Terms of Payment (TOP): <b class="text-slate-800">{{ $order->term_of_payment }} Hari</b></p>
                @if($order->po_number_mt)
                <p class="text-blue-700 font-semibold">Nomor PO Resmi: {{ $order->po_number_mt }}</p>
                @endif
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1.5">
                <span class="font-bold text-slate-500 uppercase text-[10px] tracking-wider block">Logistik & Cabang</span>
                <p class="text-sm font-bold text-slate-900">{{ $order->branch_name }}</p>
                <p class="text-slate-500">Gudang Alokasi: <b class="text-slate-800">{{ $order->warehouse_name }}</b></p>
                <p class="text-slate-500">Tipe Pesanan: <span class="font-semibold text-slate-700">{{ $order->order_type }}</span></p>
                @if($order->notes)
                <p class="text-slate-500">Catatan: <i class="text-slate-700">{{ $order->notes }}</i></p>
                @endif
            </div>
        </div>

        <!-- Item Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3 px-4">SKU & Produk</th>
                        <th class="py-3 px-4">Satuan (UOM)</th>
                        <th class="py-3 px-4 text-center">Kuantitas</th>
                        <th class="py-3 px-4 text-right">Harga Satuan</th>
                        <th class="py-3 px-4 text-right">Diskon</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($items as $item)
                    <tr>
                        <td class="py-4 px-4">
                            <span class="font-bold text-slate-900 block">{{ $item->product_name }}</span>
                            <span class="font-mono text-[11px] text-slate-500">{{ $item->product_sku }}</span>
                        </td>
                        <td class="py-4 px-4 text-slate-600">{{ $item->unit_name }} (x{{ $item->conversion_factor }})</td>
                        <td class="py-4 px-4 text-center font-bold text-slate-800">{{ $item->qty_ordered }}</td>
                        <td class="py-4 px-4 text-slate-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-right text-rose-600">
                            @php $itemDisc = $item->gross_amount - $item->net_amount; @endphp
                            {{ $itemDisc > 0 ? '-Rp ' . number_format($itemDisc, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-4 px-4 text-right font-bold text-slate-900">Rp {{ number_format($item->net_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Perhitungan Total & PPN -->
        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <div class="w-full sm:w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Kotor:</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-rose-600">
                    <span>Diskon On-Invoice:</span>
                    <span>{{ $order->discount_on_invoice > 0 ? '-Rp ' . number_format($order->discount_on_invoice, 0, ',', '.') : 'Rp 0' }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Dasar Pengenaan Pajak (DPP):</span>
                    <span>Rp {{ number_format($order->subtotal - $order->discount_on_invoice, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>PPN 11%:</span>
                    <span>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-extrabold text-slate-900">
                    <span>Grand Total:</span>
                    <span class="text-indigo-600">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
