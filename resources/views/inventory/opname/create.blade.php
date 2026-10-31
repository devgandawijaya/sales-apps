@extends('layouts.app')

@section('title', 'Mulai Sesi Stock Opname')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Mulai Sesi Stock Opname Baru</h1>
            <p class="text-sm text-slate-500 mt-1">Sistem akan membekukan snapshot persediaan buku saat ini.</p>
        </div>
        <a href="{{ route('opname.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-800">
            &larr; Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('opname.store') }}" method="POST" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-5">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Judul / Keterangan Sesi <span class="text-red-500">*</span></label>
            <input type="text" name="title" required placeholder="Contoh: Cycle Count Akhir Bulan Q4 2026 Gudang Pusat" class="w-full text-sm border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kantor Cabang <span class="text-red-500">*</span></label>
                <select name="branch_id" required class="w-full text-sm border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->code }} - {{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Gudang Audit <span class="text-red-500">*</span></label>
                <select name="warehouse_id" required class="w-full text-sm border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Gudang --</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->code }} - {{ $wh->name }} ({{ $wh->type }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Opname</label>
                <select name="count_type" class="w-full text-sm border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                    <option value="CYCLE_COUNT">Cycle Count (Sampling Berkala)</option>
                    <option value="WALL_TO_WALL">Wall-to-Wall Opname (Seluruh Gudang)</option>
                    <option value="SPOT_CHECK">Spot Check Insidentil</option>
                </select>
            </div>
            <div class="flex items-center pt-5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="blind_count" value="1" class="rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-xs font-semibold text-slate-700">Blind Count (Sembunyikan Stok Sistem dari Petugas)</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
            <textarea name="notes" rows="2" placeholder="Catatan tim pemeriksa..." class="w-full text-sm border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('opname.index') }}" class="px-5 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-blue-500/20">Mulai Sesi & Bekukan Stok</button>
        </div>
    </form>
</div>
@endsection

