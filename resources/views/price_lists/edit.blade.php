@extends('layouts.app')

@section('title', 'Ubah Daftar Harga - ' . $priceList->name)
@section('page_title', 'Ubah Skema Daftar Harga')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('price-lists.index') }}" class="hover:underline">Daftar Harga</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Ubah Informasi</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Perbarui Daftar Harga: {{ $priceList->name }}</h1>
            <p class="text-xs text-slate-500">Ubah parameter kanal distribusi, masa aktif, dan status berlakunya skema harga.</p>
        </div>
        <a href="{{ route('price-lists.show', $priceList->id) }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
            Lihat Matriks Harga &rarr;
        </a>
    </div>

    <form action="{{ route('price-lists.update', $priceList->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Skema Harga</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Daftar Harga <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $priceList->name) }}" required
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('name') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Daftar Harga <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $priceList->code) }}" required
                           class="w-full px-3.5 py-2 text-xs font-mono uppercase rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('code') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Saluran Distribusi (Channel) <span class="text-rose-500">*</span></label>
                    <select name="channel_type" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <option value="GT" {{ old('channel_type', $priceList->channel_type) === 'GT' ? 'selected' : '' }}>General Trade (GT - Pasar Tradisional / Warung)</option>
                        <option value="MT" {{ old('channel_type', $priceList->channel_type) === 'MT' ? 'selected' : '' }}>Modern Trade (MT - Supermarket / Minimarket)</option>
                        <option value="WHOLESALE" {{ old('channel_type', $priceList->channel_type) === 'WHOLESALE' ? 'selected' : '' }}>Grosir / Agen Besar</option>
                        <option value="RETAIL" {{ old('channel_type', $priceList->channel_type) === 'RETAIL' ? 'selected' : '' }}>Retail Langsung</option>
                        <option value="ALL" {{ old('channel_type', $priceList->channel_type) === 'ALL' ? 'selected' : '' }}>Berlaku Semua Saluran (Umum)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mata Uang <span class="text-rose-500">*</span></label>
                    <input type="text" name="currency" value="{{ old('currency', $priceList->currency) }}" required
                           class="w-full px-3.5 py-2 text-xs font-mono uppercase rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Keaktifan <span class="text-rose-500">*</span></label>
                    <select name="is_active" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <option value="1" {{ old('is_active', $priceList->is_active) ? 'selected' : '' }}>Aktif (Dapat Digunakan)</option>
                        <option value="0" {{ !old('is_active', $priceList->is_active) ? 'selected' : '' }}>Nonaktif (Ditutup)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Berlaku Mulai Tanggal <span class="text-rose-500">*</span></label>
                    <input type="date" name="valid_from" value="{{ old('valid_from', $priceList->valid_from ? \Carbon\Carbon::parse($priceList->valid_from)->format('Y-m-d') : '') }}" required
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Berlaku Sampai Tanggal (Opsional)</label>
                    <input type="date" name="valid_to" value="{{ old('valid_to', $priceList->valid_to ? \Carbon\Carbon::parse($priceList->valid_to)->format('Y-m-d') : '') }}"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('price-lists.show', $priceList->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-lg shadow-blue-500/20 transition">
                Simpan Perubahan
            </button>
        </div>
    </form>

</div>
@endsection
