@extends('layouts.app')

@section('title', 'Pencatatan Pembayaran Pelanggan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('finance.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
            &larr; Kembali ke Keuangan & Piutang
        </a>
    </div>

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pelunasan Piutang & Rekonsiliasi Kas</span>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Formulir Penerimaan Pembayaran Pelanggan</h1>
            <p class="text-xs text-slate-500">Catat penerimaan pembayaran via Tunai, Transfer Bank, atau Giro/Cek untuk mengurangi saldo piutang dan memulihkan plafon kredit pelanggan.</p>
        </div>

        <form action="{{ route('finance.payments.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Customer Selection & Branch -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                <h3 class="text-xs font-bold uppercase text-slate-600 tracking-wider">1. Pelanggan & Cabang</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kantor Cabang *</label>
                        <select name="branch_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pelanggan (Memiliki Faktur Terbuka) *</label>
                        <select name="customer_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white" onchange="window.location.href='{{ route('finance.payments.create') }}?customer_id=' + this.value">
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ (old('customer_id', $selectedCustomer?->id) == $c->id) ? 'selected' : '' }}>
                                    {{ $c->name }} (Piutang: Rp {{ number_format($c->invoices->sum('balance_due'), 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($selectedCustomer)
                    <div class="mt-3 p-3 bg-white rounded-lg border border-slate-200 text-xs space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Plafon Kredit Terpakai:</span>
                            <span class="font-bold text-rose-600">Rp {{ number_format($selectedCustomer->current_credit_used, 0, ',', '.') }}</span>
                        </div>
                        <div class="font-bold text-slate-700 pt-1">Daftar Faktur Belum Lunas:</div>
                        <div class="space-y-1">
                            @foreach($selectedCustomer->invoices as $inv)
                                <div class="flex justify-between text-[11px] bg-slate-50 p-2 rounded">
                                    <span class="font-mono">{{ $inv->invoice_number }} (Jatuh tempo: {{ $inv->due_date?->format('d/m/Y') }})</span>
                                    <span class="font-bold text-slate-900">Sisa: Rp {{ number_format($inv->balance_due, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Section 2: Payment Details -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                <h3 class="text-xs font-bold uppercase text-slate-600 tracking-wider">2. Rincian Pembayaran</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal Pembayaran (IDR) *</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount', $selectedCustomer?->invoices->sum('balance_due')) }}" required
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white font-mono text-base font-bold text-emerald-700">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pembayaran *</label>
                        <select name="payment_method" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white font-semibold">
                            <option value="BANK_TRANSFER">Transfer Bank</option>
                            <option value="CASH">Kas Tunai (Cash on Delivery)</option>
                            <option value="GIRO">Bilyet Giro</option>
                            <option value="CHECK">Cek Bank</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Bank Pengirim / Penerima</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', 'Bank Central Asia (BCA)') }}" placeholder="Contoh: BCA / Mandiri / BRI"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Referensi Transfer / No. Giro</label>
                        <input type="text" name="reference_no_giro" value="{{ old('reference_no_giro') }}" placeholder="Contoh: TRF-83921839 / BG-001293"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Jatuh Tempo Giro (Khusus Giro)</label>
                        <input type="date" name="giro_due_date" value="{{ old('giro_due_date') }}"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Keterangan tambahan..."
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('finance.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan & Alokasikan Pembayaran</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

