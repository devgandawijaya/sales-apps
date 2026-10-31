@extends('layouts.app')

@section('title', 'Stock Opname & Cycle Count')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Stock Opname & Cycle Counting</h1>
            <p class="text-sm text-slate-500 mt-1">Audit fisik persediaan gudang dengan pembekuan stok sistem, blind count, dan persetujuan penyesuaian COGS.</p>
        </div>
        <div>
            <a href="{{ route('opname.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-md shadow-blue-500/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Mulai Sesi Opname Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap gap-3 justify-between items-center bg-slate-50/50">
            <form action="{{ route('opname.index') }}" method="GET" class="flex flex-wrap gap-3 items-center">
                <select name="warehouse_id" class="text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="IN_PROGRESS" {{ request('status') == 'IN_PROGRESS' ? 'selected' : '' }}>IN_PROGRESS</option>
                    <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED</option>
                    <option value="POSTED" {{ request('status') == 'POSTED' ? 'selected' : '' }}>POSTED</option>
                    <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200">
                        <th class="py-3 px-4">No. Opname</th>
                        <th class="py-3 px-4">Judul Sesi</th>
                        <th class="py-3 px-4">Gudang</th>
                        <th class="py-3 px-4">Metode</th>
                        <th class="py-3 px-4 text-center">Selisih Fisik</th>
                        <th class="py-3 px-4 text-right">Variansi COGS</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($opnames as $opn)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-blue-600">
                                <a href="{{ route('opname.show', $opn->id) }}">{{ $opn->opname_number }}</a>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $opn->title }}</td>
                            <td class="py-3 px-4">{{ $opn->warehouse->name ?? '-' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $opn->count_type }}
                                </span>
                                @if($opn->blind_count)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-purple-100 text-purple-700">BLIND</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-bold {{ $opn->total_discrepancy_qty != 0 ? 'text-rose-600' : 'text-slate-600' }}">
                                {{ $opn->total_discrepancy_qty > 0 ? '+' : '' }}{{ $opn->total_discrepancy_qty }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold {{ $opn->total_cogs_variance < 0 ? 'text-rose-600' : 'text-slate-800' }}">
                                Rp {{ number_format($opn->total_cogs_variance, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($opn->status === 'POSTED')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">POSTED</span>
                                @elseif($opn->status === 'SUBMITTED')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">SUBMITTED</span>
                                @elseif($opn->status === 'IN_PROGRESS')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">COUNTING</span>
                                @elseif($opn->status === 'REJECTED')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">REJECTED</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">{{ $opn->status }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('opname.show', $opn->id) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold">
                                    Sheet &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada sesi stock opname.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $opnames->links() }}
        </div>
    </div>
</div>
@endsection

