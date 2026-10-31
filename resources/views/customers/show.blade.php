@extends('layouts.app')

@section('title', 'Detail Pelanggan - ' . $customer->name)
@section('page_title', 'Profil Pelanggan & Rekam Transaksi')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h3 class="text-xl font-bold text-slate-900">{{ $customer->name }}</h3>
                @if($customer->channel === 'GT')
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">General Trade (GT)</span>
                @else
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">Modern Trade (MT)</span>
                @endif
                @if($customer->is_active)
                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                @else
                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Non-Aktif</span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">Kode: <span class="font-mono font-bold">{{ $customer->code }}</span> • Cabang: <b>{{ $customer->branch?->name }}</b></p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('customers.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition">
                &larr; Kembali
            </a>
            <a href="{{ route('customers.edit', $customer->id) }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition">
                Edit Pelanggan
            </a>
        </div>
    </div>

    <!-- KPI Credit & Exposure Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        @php
            $availCredit = max(0, $customer->credit_limit - $customer->current_credit_used);
            $pctUsed = $customer->credit_limit > 0 ? min(100, round(($customer->current_credit_used / $customer->credit_limit) * 100)) : 0;
        @endphp
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Plafon Kredit</span>
            <h3 class="text-xl font-extrabold text-slate-900 mt-1">Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Tenor: {{ $customer->term_of_payment }} Hari ({{ $customer->payment_type }})</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Kredit Terpakai (Piutang)</span>
            <h3 class="text-xl font-extrabold text-amber-600 mt-1">Rp {{ number_format($customer->current_credit_used, 0, ',', '.') }}</h3>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                <div class="h-1.5 rounded-full {{ $pctUsed > 80 ? 'bg-rose-500' : ($pctUsed > 50 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $pctUsed }}%"></div>
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">{{ $pctUsed }}% dari batas plafon</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Sisa Plafon Tersedia</span>
            <h3 class="text-xl font-extrabold text-emerald-600 mt-1">Rp {{ number_format($availCredit, 0, ',', '.') }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Dapat digunakan untuk SO baru</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Total Sales Order</span>
            <h3 class="text-xl font-extrabold text-blue-600 mt-1">{{ $customer->salesOrders->count() }} Transaksi</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Faktur terbit: {{ $customer->invoices->count() }}</span>
        </div>
    </div>

    <!-- Informasi Profil Lengkap & MTA -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Profil -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Informasi Alamat & Kontak</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block">Nama Pemilik / PIC:</span>
                    <span class="font-semibold text-slate-800">{{ $customer->owner_name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Sub-Saluran Bisnis:</span>
                    <span class="font-semibold text-slate-800">{{ $customer->sub_channel }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Nomor Telepon:</span>
                    <span class="font-semibold text-slate-800">{{ $customer->phone ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Email:</span>
                    <span class="font-semibold text-slate-800">{{ $customer->email ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">NPWP:</span>
                    <span class="font-mono text-slate-800">{{ $customer->tax_id_npwp ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Kota / Wilayah:</span>
                    <span class="font-semibold text-slate-800">{{ $customer->city }}</span>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-slate-400 block">Alamat Pengiriman Lengkap:</span>
                    <span class="font-medium text-slate-800">{{ $customer->address }}</span>
                </div>
            </div>
        </div>

        <!-- Kolom Modern Trade Agreement (Jika MT) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <h4 class="text-xs font-bold text-blue-700 uppercase tracking-wider">Kontrak Modern Trade (MTA)</h4>
            @if($customer->isModernTrade() && $customer->modernTradeAgreement)
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-500">Nomor Kontrak:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $customer->modernTradeAgreement->contract_number }}</span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-500">Listing Fee:</span>
                    <span class="font-semibold text-slate-800">Rp {{ number_format($customer->modernTradeAgreement->listing_fee_total, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-500">Slotting Fee:</span>
                    <span class="font-semibold text-slate-800">Rp {{ number_format($customer->modernTradeAgreement->slotting_fee_total, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-500">Rabat Tahunan:</span>
                    <span class="font-bold text-emerald-600">{{ $customer->modernTradeAgreement->off_invoice_rebate_rate }}%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Status Kontrak:</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">AKTIF</span>
                </div>
            </div>
            @elseif($customer->isModernTrade())
            <div class="p-4 rounded-xl bg-amber-50 text-amber-800 text-xs">
                Belum ada kontrak MTA resmi yang terdaftar untuk pelanggan Modern Trade ini.
            </div>
            @else
            <div class="p-4 rounded-xl bg-slate-50 text-slate-500 text-xs">
                Pelanggan General Trade (GT) bertransaksi menggunakan skema harga grosir standar / kanvas tanpa kontrak MTA ritel modern.
            </div>
            @endif
        </div>
    </div>

    <!-- Relasi: Pesanan Penjualan Terbaru -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Riwayat Pesanan Penjualan (Sales Orders)</h4>
            <a href="{{ route('sales.create') }}" class="text-xs font-semibold text-blue-600 hover:underline">+ Buat SO Baru</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3 px-6">Nomor SO</th>
                        <th class="py-3 px-6">Tanggal</th>
                        <th class="py-3 px-6">Tipe Pesanan</th>
                        <th class="py-3 px-6 text-right">Grand Total</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customer->salesOrders as $so)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-6 font-bold text-slate-900">{{ $so->so_number }}</td>
                        <td class="py-3 px-6 text-slate-600">{{ $so->order_date }}</td>
                        <td class="py-3 px-6 text-slate-600">{{ $so->order_type }}</td>
                        <td class="py-3 px-6 text-right font-bold text-slate-900">Rp {{ number_format($so->grand_total, 0, ',', '.') }}</td>
                        <td class="py-3 px-6 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">{{ $so->status }}</span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <a href="{{ route('sales.show', $so->id) }}" class="text-blue-600 hover:underline font-semibold">Lihat SO</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-400">Belum ada transaksi Sales Order untuk pelanggan ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Relasi: Faktur Penjualan & Piutang -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Faktur Penjualan & Status Pelunasan</h4>
            <a href="{{ route('finance.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">Dasbor Keuangan &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3 px-6">Nomor Faktur</th>
                        <th class="py-3 px-6">NSFP Pajak</th>
                        <th class="py-3 px-6">Jatuh Tempo</th>
                        <th class="py-3 px-6 text-right">Total Tagihan</th>
                        <th class="py-3 px-6 text-right">Sisa Tagihan</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customer->invoices as $inv)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-6 font-bold text-slate-900">{{ $inv->invoice_number }}</td>
                        <td class="py-3 px-6 font-mono text-slate-500">{{ $inv->tax_invoice_number ?? '-' }}</td>
                        <td class="py-3 px-6 text-slate-600">{{ $inv->due_date }}</td>
                        <td class="py-3 px-6 text-right font-semibold text-slate-800">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-6 text-right font-bold {{ $inv->balance_due > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                            Rp {{ number_format($inv->balance_due, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-6 text-center">
                            @if($inv->status === 'PAID')
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">LUNAS</span>
                            @elseif($inv->status === 'PARTIAL')
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">SEBAGIAN</span>
                            @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">TERTUNGGAK</span>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-center">
                            <a href="{{ route('finance.invoices.show', $inv->id) }}" class="text-blue-600 hover:underline font-semibold">Detail Faktur</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-400">Belum ada faktur yang diterbitkan untuk pelanggan ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

