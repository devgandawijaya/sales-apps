@extends('layouts.app')

@section('title', 'Tambah Pelanggan Baru')
@section('page_title', 'Registrasi Pelanggan Baru (GT & MT)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Form Pendaftaran Toko / Ritel</h3>
            <p class="text-xs text-slate-500">Pemberian kode unik otomatis, penetapan plafon kredit, dan parameter Modern Trade.</p>
        </div>
        <a href="{{ route('customers.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Daftar
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs space-y-1">
            <b class="font-bold">Gagal menyimpan data:</b>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customers.store') }}" method="POST" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-sm space-y-6">
        @csrf

        <!-- Bagian 1: Identitas & Saluran Bisnis -->
        <div>
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">1. Identitas & Saluran Bisnis</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Toko / Perusahaan <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Toko Sembako Makmur / PT Indomarco"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pemilik / PIC Toko</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name') }}" placeholder="Contoh: H. Ahmad Subarjo"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Saluran Bisnis (Channel) <span class="text-rose-500">*</span></label>
                    <select name="channel" id="channelSelect" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white" onchange="toggleMtaSection()">
                        <option value="GT" {{ old('channel') === 'GT' ? 'selected' : '' }}>General Trade (GT) - Warung / Toko Kelontong / Grosir</option>
                        <option value="MT" {{ old('channel') === 'MT' ? 'selected' : '' }}>Modern Trade (MT) - Supermarket / Minimarket / DC</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sub-Saluran (Sub-Channel) <span class="text-rose-500">*</span></label>
                    <input type="text" name="sub_channel" value="{{ old('sub_channel', 'WARUNG') }}" required placeholder="Contoh: WARUNG, GROSIR, MINIMARKET, HYPERMARKET"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 uppercase">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cabang Penanggung Jawab (Branch) <span class="text-rose-500">*</span></label>
                    <select name="branch_id" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Daftar Harga Khusus (Price List)</label>
                    <select name="price_list_id" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <option value="">-- Gunakan Harga Standar Saluran --</option>
                        @foreach($priceLists as $pl)
                        <option value="{{ $pl->id }}" {{ old('price_list_id') == $pl->id ? 'selected' : '' }}>{{ $pl->name }} ({{ $pl->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Alamat & Kontak -->
        <div class="pt-4 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">2. Alamat & Kontak</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap Toko / Gudang <span class="text-rose-500">*</span></label>
                    <textarea name="address" rows="2" required placeholder="Jl. Pasar Baru No. 12, RT 02/05..."
                              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                    <input type="text" name="city" value="{{ old('city') }}" required placeholder="Contoh: Jakarta Barat"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 081234567890"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="toko@domain.com"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NPWP Perusahaan / Toko</label>
                    <input type="text" name="tax_id_npwp" value="{{ old('tax_id_npwp') }}" placeholder="01.234.567.8-901.000"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>
        </div>

        <!-- Bagian 3: Kebijakan Kredit & Pembayaran -->
        <div class="pt-4 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">3. Batas Plafon Kredit & Ketentuan Pembayaran</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Plafon Kredit (Credit Limit Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" step="100000" name="credit_limit" value="{{ old('credit_limit', 10000000) }}" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Isi 0 jika transaksi hanya boleh Tunai / COD</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tenor Pembayaran (TOP Hari) <span class="text-rose-500">*</span></label>
                    <input type="number" name="term_of_payment" value="{{ old('term_of_payment', 14) }}" required min="0" max="180"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">GT: 7–14 hari; MT: 30–60 hari</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pembayaran Default <span class="text-rose-500">*</span></label>
                    <select name="payment_type" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <option value="CREDIT" {{ old('payment_type') === 'CREDIT' ? 'selected' : '' }}>Kredit / Tempo</option>
                        <option value="CASH" {{ old('payment_type') === 'CASH' ? 'selected' : '' }}>Tunai / COD</option>
                        <option value="GIRO" {{ old('payment_type') === 'GIRO' ? 'selected' : '' }}>Giro / Cek Mundur</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Bagian 4: Parameter Kontrak Modern Trade (MTA) -->
        <div id="mtaSection" class="pt-4 border-t border-slate-100 hidden">
            <h4 class="text-xs font-bold text-blue-700 uppercase tracking-wider mb-3">4. Kontrak Dagang Modern Trade Agreement (MTA)</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-blue-50/50 border border-blue-100">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Biaya Pendaftaran SKU (Listing Fee Rp)</label>
                    <input type="number" step="10000" name="listing_fee" value="{{ old('listing_fee', 0) }}"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sewa Rak Display (Slotting Fee Rp)</label>
                    <input type="number" step="10000" name="slotting_fee" value="{{ old('slotting_fee', 0) }}"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rabat Target Tahunan (Rebate %)</label>
                    <input type="number" step="0.1" min="0" max="100" name="target_rebate_percent" value="{{ old('target_rebate_percent', 0) }}"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                </div>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end space-x-3">
            <a href="{{ route('customers.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-lg shadow-blue-500/20 transition">
                Simpan Pelanggan Baru &rarr;
            </button>
        </div>
    </form>

</div>

<script>
    function toggleMtaSection() {
        const channel = document.getElementById('channelSelect').value;
        const mta = document.getElementById('mtaSection');
        if (channel === 'MT') {
            mta.classList.remove('hidden');
        } else {
            mta.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        toggleMtaSection();
    });
</script>
@endsection

