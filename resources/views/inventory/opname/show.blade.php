@extends('layouts.app')

@section('title', 'Sesi Opname #' . $opname->opname_number)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $opname->opname_number }}</h1>
                @if($opname->status === 'POSTED')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">POSTED & ADJUSTED</span>
                @elseif($opname->status === 'SUBMITTED')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">SUBMITTED (MENUNGGU APPROVAL)</span>
                @elseif($opname->status === 'IN_PROGRESS')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 animate-pulse">COUNTING FISIK</span>
                @elseif($opname->status === 'REJECTED')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">REJECTED</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 mt-1">{{ $opname->title }} &bull; {{ $opname->warehouse->name ?? '-' }} ({{ $opname->branch->name ?? '-' }})</p>
        </div>
        <div>
            <a href="{{ route('opname.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-800">
                &larr; Kembali ke Daftar Opname
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm font-medium">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Batch Diperiksa</span>
            <span class="text-xl font-bold text-slate-800 mt-1 block">{{ $opname->items->count() }} Item Batch</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Item Sudah Dihitung</span>
            <span class="text-xl font-bold text-blue-600 mt-1 block">{{ $opname->total_items_counted }} Item</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Selisih Unit</span>
            <span class="text-xl font-bold mt-1 block {{ $opname->total_discrepancy_qty != 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                {{ $opname->total_discrepancy_qty > 0 ? '+' : '' }}{{ $opname->total_discrepancy_qty }}
            </span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Variansi Nilai COGS</span>
            <span class="text-xl font-bold mt-1 block font-mono {{ $opname->total_cogs_variance < 0 ? 'text-rose-600' : 'text-slate-800' }}">
                Rp {{ number_format($opname->total_cogs_variance, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Manager Approval Panel (if SUBMITTED) -->
    @if($opname->isPostable() && $opname->status !== 'POSTED')
        <div class="bg-purple-50 border border-purple-200 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-purple-900">Otorisasi & Penyesuaian Nilai Persediaan</h3>
                <p class="text-xs text-purple-700 mt-1">Menyetujui sesi ini akan memposting entri penyesuaian (adjustment) ke dalam Buku Besar Stok (Immutable Ledger) secara otomatis.</p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('opname.post', $opname->id) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin memposting penyesuaian stok ini ke Buku Besar?')" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-md shadow-purple-500/20 transition">
                        ✓ Setujui & Posting ke Ledger
                    </button>
                </form>

                <form action="{{ route('opname.reject', $opname->id) }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="text" name="reason" placeholder="Alasan penolakan..." required class="text-xs px-3 py-1.5 border border-purple-300 rounded-lg w-40">
                    <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition">
                        ✕ Tolak
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Count Sheet Form Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-800">Lembar Kerja Hitung Fisik (Count Sheet)</h3>
            @if($opname->isEditable())
                <span class="text-xs text-blue-600 font-semibold">Mode Input Aktif</span>
            @endif
        </div>

        @if($opname->isEditable())
            <form action="{{ route('opname.count', $opname->id) }}" method="POST">
                @csrf
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200">
                        <th class="py-3 px-4">SKU & Nama Produk</th>
                        <th class="py-3 px-4">No. Batch</th>
                        <th class="py-3 px-4">Kadaluwarsa</th>
                        @if(!$opname->blind_count || $opname->status !== 'IN_PROGRESS')
                            <th class="py-3 px-4 text-center">Stok Sistem</th>
                        @endif
                        <th class="py-3 px-4 text-center">Hitung Fisik</th>
                        @if(!$opname->blind_count || $opname->status !== 'IN_PROGRESS')
                            <th class="py-3 px-4 text-center">Selisih</th>
                            <th class="py-3 px-4 text-right">Variansi COGS</th>
                        @endif
                        <th class="py-3 px-4">Alasan Selisih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($opname->items as $idx => $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $item->product->name ?? '-' }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $item->product->sku ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono font-medium">{{ $item->batch_number }}</td>
                            <td class="py-3 px-4">{{ $item->expiry_date?->format('d/m/Y') }}</td>
                            
                            @if(!$opname->blind_count || $opname->status !== 'IN_PROGRESS')
                                <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $item->system_qty }}</td>
                            @endif

                            <td class="py-3 px-4 text-center">
                                @if($opname->isEditable())
                                    <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                    <input type="number" name="items[{{ $idx }}][physical_qty]" value="{{ $item->physical_qty }}" min="0" required class="w-20 text-xs px-2 py-1 border border-slate-300 rounded text-center font-bold focus:ring-2 focus:ring-blue-500">
                                @else
                                    <span class="font-bold text-slate-800">{{ $item->physical_qty }}</span>
                                @endif
                            </td>

                            @if(!$opname->blind_count || $opname->status !== 'IN_PROGRESS')
                                <td class="py-3 px-4 text-center font-bold {{ $item->discrepancy_qty != 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    {{ $item->discrepancy_qty > 0 ? '+' : '' }}{{ $item->discrepancy_qty }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold {{ $item->discrepancy_cogs_value < 0 ? 'text-rose-600' : 'text-slate-800' }}">
                                    Rp {{ number_format($item->discrepancy_cogs_value, 0, ',', '.') }}
                                </td>
                            @endif

                            <td class="py-3 px-4">
                                @if($opname->isEditable())
                                    <select name="items[{{ $idx }}][reason_code]" class="text-xs border-slate-300 rounded px-2 py-1">
                                        <option value="">-- Alasan (Bila Selisih) --</option>
                                        <option value="MISCOUNT" {{ $item->reason_code == 'MISCOUNT' ? 'selected' : '' }}>Salah Hitung Awal</option>
                                        <option value="DAMAGED" {{ $item->reason_code == 'DAMAGED' ? 'selected' : '' }}>Barang Rusak</option>
                                        <option value="EXPIRED" {{ $item->reason_code == 'EXPIRED' ? 'selected' : '' }}>Kadaluwarsa</option>
                                        <option value="THEFT" {{ $item->reason_code == 'THEFT' ? 'selected' : '' }}>Kehilangan Fisik</option>
                                        <option value="FOUND" {{ $item->reason_code == 'FOUND' ? 'selected' : '' }}>Barang Ditemukan (+)</option>
                                    </select>
                                @else
                                    <span class="text-xs text-slate-600">{{ $item->reason_code ?? '-' }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($opname->isEditable())
            <div class="p-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-500/20">
                    Simpan Perhitungan Fisik & Ajukan Verifikasi (SUBMIT)
                </button>
            </div>
            </form>
        @endif
    </div>
</div>
@endsection

