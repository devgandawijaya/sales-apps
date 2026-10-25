@extends('layouts.app')

@section('title', 'Dashboard Eksekutif')
@section('page_title', 'Ringkasan & Analisis Distribusi')

@section('content')
<div class="space-y-6">

    <!-- Saluran GT vs MT Banner -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gradient-to-br from-emerald-900 to-teal-950 p-5 rounded-2xl border border-emerald-800/40 text-white shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-wider">Saluran General Trade (GT)</span>
                    <span class="text-xs text-emerald-300 font-semibold">{{ $kpi['channel_breakdown']['general_trade']['share_percent'] }}% Share</span>
                </div>
                <p class="text-xs text-emerald-100/80 leading-relaxed">
                    Operasional kunjungan toko dengan Beat Plan, pengambilan pesanan Taking Order (TO), penjualan Kanvas armada mobil box, kredit toko tradisional (TOP 0-14 hari / COD).
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-emerald-800/60 flex items-center justify-between text-xs">
                <span class="text-emerald-300">Total Omset GT:</span>
                <span class="font-extrabold text-white text-base">Rp {{ number_format($kpi['channel_breakdown']['general_trade']['omset'], 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-900 to-indigo-950 p-5 rounded-2xl border border-blue-800/40 text-white shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30 uppercase tracking-wider">Saluran Modern Trade (MT)</span>
                    <span class="text-xs text-blue-300 font-semibold">{{ $kpi['channel_breakdown']['modern_trade']['share_percent'] }}% Share</span>
                </div>
                <p class="text-xs text-blue-100/80 leading-relaxed">
                    Integrasi Purchase Order ritel modern (Super Indo, Alfamart), perjanjian MTA rebate & listing fee, tukar faktur pajak, dan terms of payment (TOP 30-60 hari).
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-blue-800/60 flex items-center justify-between text-xs">
                <span class="text-blue-300">Total Omset MT:</span>
                <span class="font-extrabold text-white text-base">Rp {{ number_format($kpi['channel_breakdown']['modern_trade']['omset'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Sales Order</span>
            <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $kpi['total_sales_orders'] }} <span class="text-xs font-semibold text-slate-500">Transaksi</span></h3>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Multi-Cabang</span>
                <span class="text-emerald-600 font-semibold">Aktif</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Omset Penjualan</span>
            <h3 class="text-2xl font-extrabold text-indigo-600 mt-2">Rp {{ number_format($kpi['total_omset'], 0, ',', '.') }}</h3>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Bruto + PPN 11%</span>
                <span class="text-indigo-600 font-semibold">IDR</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Piutang (AR)</span>
            <h3 class="text-2xl font-extrabold text-rose-600 mt-2">Rp {{ number_format($aging['total_outstanding_ar'], 0, ',', '.') }}</h3>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Belum Terbayar</span>
                <span class="text-rose-600 font-semibold">Outstanding</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Entitas Distribusi</span>
            <div class="mt-2 flex items-center gap-3">
                <div>
                    <span class="text-xl font-extrabold text-slate-900">{{ $warehouses }}</span>
                    <span class="text-[10px] text-slate-500 block">Gudang</span>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div>
                    <span class="text-xl font-extrabold text-slate-900">{{ $customersCount }}</span>
                    <span class="text-[10px] text-slate-500 block">Pelanggan</span>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div>
                    <span class="text-xl font-extrabold text-slate-900">{{ $productsCount }}</span>
                    <span class="text-[10px] text-slate-500 block">SKU</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Database Terpadu</span>
                <span class="text-blue-600 font-semibold">PostgreSQL</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions Panel -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <h4 class="text-sm font-bold text-slate-800">Aksi Cepat Operasional</h4>
            <p class="text-xs text-slate-500">Pintasan ke modul utama sistem penjualan dan distribusi</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('sales.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Sales Order Baru
            </a>
            <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Data Pelanggan
            </a>
            <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Cek Stok FEFO
            </a>
            <a href="{{ route('finance.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Piutang & AR Aging
            </a>
        </div>
    </div>

    <!-- AR Aging Schedule Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Jadwal Penuaan Piutang Usaha (AR Aging Breakdown)</h3>
                <p class="text-xs text-slate-500">Status penagihan faktur penjualan per kategori hari</p>
            </div>
            <a href="{{ route('finance.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                Lihat Detail Piutang &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70">
                <span class="text-xs font-medium text-slate-500 block">Lancar (0 - 30 Hari)</span>
                <span class="text-lg font-bold text-slate-800 mt-1 block">Rp {{ number_format($aging['current_0_to_30_days'], 0, ',', '.') }}</span>
                <span class="text-[11px] text-emerald-600 font-semibold">Dalam batas jatuh tempo</span>
            </div>
            <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200/70">
                <span class="text-xs font-medium text-amber-800 block">Jatuh Tempo (31 - 60 Hari)</span>
                <span class="text-lg font-bold text-amber-900 mt-1 block">Rp {{ number_format($aging['overdue_31_to_60_days'], 0, ',', '.') }}</span>
                <span class="text-[11px] text-amber-700 font-semibold">Perlu penagihan aktif</span>
            </div>
            <div class="p-4 rounded-xl bg-orange-50/60 border border-orange-200/70">
                <span class="text-xs font-medium text-orange-800 block">Kritis (61 - 90 Hari)</span>
                <span class="text-lg font-bold text-orange-900 mt-1 block">Rp {{ number_format($aging['overdue_61_to_90_days'], 0, ',', '.') }}</span>
                <span class="text-[11px] text-orange-700 font-semibold">Peringatan kredit</span>
            </div>
            <div class="p-4 rounded-xl bg-rose-50/60 border border-rose-200/70">
                <span class="text-xs font-medium text-rose-800 block">Macet (> 90 Hari)</span>
                <span class="text-lg font-bold text-rose-900 mt-1 block">Rp {{ number_format($aging['overdue_over_90_days'], 0, ',', '.') }}</span>
                <span class="text-[11px] text-rose-700 font-semibold">Kunci pesanan (Hold)</span>
            </div>
        </div>
    </div>

    <!-- Recent Sales Orders -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Pesanan Penjualan Terbaru</h3>
                <p class="text-xs text-slate-500">Transaksi terakhir dari kanal General Trade dan Modern Trade</p>
            </div>
            <a href="{{ route('sales.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200 transition">
                Lihat Semua Transaksi &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 uppercase text-[11px] tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-6">Nomor SO</th>
                        <th class="py-3 px-6">Tanggal</th>
                        <th class="py-3 px-6">Pelanggan</th>
                        <th class="py-3 px-6 text-center">Saluran</th>
                        <th class="py-3 px-6 text-right">Grand Total</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentOrders as $order)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-6 font-semibold text-slate-900">{{ $order->so_number }}</td>
                        <td class="py-3.5 px-6 text-slate-600">{{ $order->order_date }}</td>
                        <td class="py-3.5 px-6 font-medium text-slate-800">{{ $order->customer_name }}</td>
                        <td class="py-3.5 px-6 text-center">
                            @if($order->channel === 'GT')
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">GT</span>
                            @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300">MT</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 text-right font-bold text-slate-900">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-6 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-6 text-center">
                            <a href="{{ route('sales.show', $order->id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                Detail &rarr;
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

