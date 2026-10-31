@extends('layouts.app')

@section('title', 'Daftar Penawaran Harga (Quotations)')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <span>Penjualan & Distribusi</span>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Penawaran Harga (Quotations)</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Katalog Penawaran Harga (Sales Quotations)</h1>
            <p class="text-xs text-slate-500">Kelola estimasi harga, penawaran pelanggan B2B, dan konversi instan ke Sales Order.</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('sales.index') }}" 
               class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                Daftar Sales Order &rarr;
            </a>
            <a href="{{ route('quotations.create') }}" 
               class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Buat Penawaran Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="p-4 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('quotations.index') }}" class="flex flex-1 items-center gap-2 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor penawaran atau pelanggan..." 
                   class="flex-1 max-w-xs px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
            
            <select name="status" class="px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="SENT" {{ request('status') === 'SENT' ? 'selected' : '' }}>Terkirim (SENT)</option>
                <option value="ACCEPTED" {{ request('status') === 'ACCEPTED' ? 'selected' : '' }}>Disetujui (ACCEPTED)</option>
                <option value="CONVERTED_TO_SO" {{ request('status') === 'CONVERTED_TO_SO' ? 'selected' : '' }}>Sudah Jadi SO</option>
                <option value="EXPIRED" {{ request('status') === 'EXPIRED' ? 'selected' : '' }}>Kedaluwarsa</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                Filter
            </button>
        </form>
    </div>

    <!-- Quotations Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">No. Penawaran</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Tanggal & Berlaku</th>
                        <th class="py-3 px-4">Nilai Total (IDR)</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($quotations as $q)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-blue-600 block">{{ $q->quotation_number }}</span>
                                <span class="text-[10px] text-slate-400">Sales: {{ $q->salesman?->name }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-extrabold text-slate-900 block">{{ $q->customer?->name }}</span>
                                <span class="text-[10px] text-slate-500">{{ $q->branch?->name }} • Kanal {{ $q->channel }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <span class="block">{{ $q->quotation_date->format('d/m/Y') }}</span>
                                <span class="text-[10px] text-slate-400">s/d {{ $q->expiry_date->format('d/m/Y') }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                Rp {{ number_format($q->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($q->status === 'CONVERTED_TO_SO')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Dikonversi ke SO
                                    </span>
                                @elseif($q->status === 'SENT')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Terkirim
                                    </span>
                                @elseif($q->status === 'ACCEPTED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Diterima Toko
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        {{ $q->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('quotations.show', $q->id) }}" 
                                   class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                                    Detail &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada penawaran harga yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($quotations->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $quotations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

