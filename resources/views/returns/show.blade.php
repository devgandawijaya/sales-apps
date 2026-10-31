@extends('layouts.app')

@section('title', 'Detail Retur Penjualan ' . $salesReturn->return_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('returns.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Daftar Retur
        </a>

        <div class="flex items-center gap-2">
            @if($salesReturn->status === 'PENDING')
                <form action="{{ route('returns.approve', $salesReturn->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-sm transition">
                        ✓ Setujui Retur & Masukkan ke Buku Besar Stok
                    </button>
                </form>
            @endif

            <button onclick="window.print()" class="px-4 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 transition">
                🖨️ Cetak Berita Acara
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-6">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Berita Acara Retur Penjualan</span>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $salesReturn->return_number }}</h2>
                <div class="text-xs text-slate-500 mt-1">
                    Tanggal Retur: <b class="text-slate-700">{{ $salesReturn->return_date?->format('d M Y') }}</b>
                </div>
            </div>

            <div class="text-left sm:text-right space-y-1">
                <div>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                        {{ $salesReturn->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                        Status: {{ $salesReturn->status }}
                    </span>
                </div>
                <div class="text-xs text-slate-500">
                    Disetujui Oleh: <strong class="text-slate-800">{{ $salesReturn->approver?->name ?? 'Menunggu Persetujuan' }}</strong>
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            <div class="p-4 bg-slate-50 rounded-xl space-y-1.5 border border-slate-200">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Pelanggan Pengembalian:</span>
                <div class="text-sm font-bold text-slate-900">{{ $salesReturn->customer?->name }}</div>
                <div class="text-slate-600">{{ $salesReturn->customer?->address }}</div>
                <div class="text-slate-600">{{ $salesReturn->customer?->city }} - {{ $salesReturn->customer?->province }}</div>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl space-y-1.5 border border-slate-200">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Rincian Gudang & Kondisi:</span>
                <div>Gudang Penampung: <strong class="text-slate-900">{{ $salesReturn->warehouse?->name }}</strong></div>
                <div>Kondisi Fisik: 
                    <strong class="text-rose-700">
                        {{ $salesReturn->return_type === 'GOOD_STOCK' ? 'Good Stock (Layak Jual)' : 'Bad Stock (Rusak/Expired)' }}
                    </strong>
                </div>
                <div>Faktur Asal: <strong class="text-slate-800">{{ $salesReturn->invoice?->invoice_number ?? 'Non-Faktur Spesifik' }}</strong></div>
                <div>Total Nilai Retur: <strong class="font-mono text-blue-700 font-bold">Rp {{ number_format($salesReturn->total_amount, 0, ',', '.') }}</strong></div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                Daftar Barang Retur
            </div>
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-500 font-bold uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-4">SKU & Nama Produk</th>
                        <th class="py-2.5 px-4">No. Batch</th>
                        <th class="py-2.5 px-4 text-center">Jumlah</th>
                        <th class="py-2.5 px-4 text-right">Harga Satuan</th>
                        <th class="py-2.5 px-4 text-right">Subtotal Nilai</th>
                        <th class="py-2.5 px-4">Alasan Retur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($salesReturn->items as $item)
                        <tr>
                            <td class="py-3 px-4 font-semibold text-slate-900">
                                {{ $item->product?->name }}
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $item->product?->sku }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-blue-600">
                                {{ $item->batch_number }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-900">
                                {{ number_format($item->qty) }} Unit
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-700">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $item->reason ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection

