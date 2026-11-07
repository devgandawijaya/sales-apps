@extends('layouts.app')

@section('title', 'Master Daftar Harga (Price Lists)')
@section('page_title', 'Daftar Harga Jual & Matriks Diskon')

@section('content')
<div class="space-y-6">

    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Struktur Daftar Harga Jual Barang</h3>
            <p class="text-xs text-slate-500">Kelola matriks harga jual per kanal distribusi (General Trade, Modern Trade, Grosir) dan penetapan harga per satuan bertingkat.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('products.index') }}" 
               class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                &larr; Katalog Produk
            </a>
            <a href="{{ route('price-lists.create') }}" 
               class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-lg shadow-blue-500/20 transition">
                + Buat Daftar Harga Baru
            </a>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Daftar Harga</span>
            <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Struktur Harga Terdaftar</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Daftar Harga Aktif</span>
            <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['active'] }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Berlaku untuk Order</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Total Item & Satuan</span>
            <h3 class="text-2xl font-extrabold text-indigo-600 mt-1">{{ number_format($stats['total_items'], 0, ',', '.') }}</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Kombinasi Produk & UOM</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Saluran Tercover</span>
            <h3 class="text-xl font-extrabold text-blue-600 mt-1">{{ $stats['gt_count'] }} GT • {{ $stats['mt_count'] }} MT</h3>
            <span class="text-[11px] text-slate-400 mt-1 block">Pembedaan Kanal Sesuai Kontrak</span>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('price-lists.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode daftar harga..."
                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="sm:col-span-4">
                <select name="channel_type" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                    <option value="">Semua Saluran Distribusi</option>
                    <option value="GT" {{ request('channel_type') === 'GT' ? 'selected' : '' }}>General Trade (GT)</option>
                    <option value="MT" {{ request('channel_type') === 'MT' ? 'selected' : '' }}>Modern Trade (MT)</option>
                    <option value="WHOLESALE" {{ request('channel_type') === 'WHOLESALE' ? 'selected' : '' }}>Grosir / Agen Besar</option>
                    <option value="RETAIL" {{ request('channel_type') === 'RETAIL' ? 'selected' : '' }}>Retail / Eceran</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'channel_type']))
                    <a href="{{ route('price-lists.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Daftar Harga -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">Kode & Nama Daftar Harga</th>
                        <th class="py-3.5 px-6">Saluran Pasar</th>
                        <th class="py-3.5 px-6">Mata Uang</th>
                        <th class="py-3.5 px-6">Masa Berlaku</th>
                        <th class="py-3.5 px-6 text-center">Jumlah Item</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($priceLists as $pl)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-900 block">{{ $pl->name }}</span>
                            <span class="font-mono text-[11px] text-slate-400">{{ $pl->code }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                {{ $pl->channel_type === 'MT' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                                {{ $pl->channel_type }}
                            </span>
                        </td>
                        <td class="py-4 px-6 font-mono font-semibold text-slate-700">
                            {{ $pl->currency }}
                        </td>
                        <td class="py-4 px-6 text-slate-600 text-xs whitespace-nowrap">
                            <span>Mulai: {{ \Carbon\Carbon::parse($pl->valid_from)->format('d M Y') }}</span>
                            @if($pl->valid_to)
                                <span class="block text-slate-400 text-[11px]">s/d {{ \Carbon\Carbon::parse($pl->valid_to)->format('d M Y') }}</span>
                            @else
                                <span class="block text-emerald-600 text-[11px] font-semibold">Tidak Ada Batas Akhir</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ $pl->items_count }} Item
                            </span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if($pl->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('price-lists.show', $pl->id) }}" 
                                   class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition">
                                    Atur Harga &rarr;
                                </a>
                                <a href="{{ route('price-lists.edit', $pl->id) }}" 
                                   title="Ubah Informasi"
                                   class="p-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('price-lists.destroy', $pl->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus atau menonaktifkan daftar harga ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus / Nonaktifkan" class="p-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500">
                            Belum ada daftar harga dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($priceLists->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $priceLists->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
