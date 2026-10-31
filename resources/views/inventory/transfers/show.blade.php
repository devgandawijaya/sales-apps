@extends('layouts.app')

@section('title', 'Detail Transfer #' . $transfer->transfer_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $transfer->transfer_number }}</h1>
                @if($transfer->status === 'COMPLETED')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">COMPLETED</span>
                @elseif($transfer->status === 'IN_TRANSIT')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 animate-pulse">IN_TRANSIT</span>
                @elseif($transfer->status === 'APPROVED')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">APPROVED</span>
                @elseif($transfer->status === 'SUBMITTED')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">SUBMITTED</span>
                @elseif($transfer->status === 'REJECTED')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">REJECTED</span>
                @else
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">{{ $transfer->status }}</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 mt-1">Dibuat oleh {{ $transfer->creator->name ?? 'User' }} pada {{ $transfer->created_at->format('d M Y H:i') }}.</p>
        </div>
        <div>
            <a href="{{ route('transfers.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-800">
                &larr; Kembali ke Daftar Transfer
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

    <!-- Workflow Action Panel -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Alur Kerja & Kontrol Otorisasi</h2>
        
        <div class="flex flex-wrap gap-3 items-center">
            @if($transfer->isSubmittable())
                <form action="{{ route('transfers.submit', $transfer->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        Ajukan untuk Approval &rarr;
                    </button>
                </form>
            @endif

            @if($transfer->isApprovable() && $transfer->status !== 'APPROVED')
                <form action="{{ route('transfers.approve', $transfer->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        ✓ Setujui Pengiriman (Approve)
                    </button>
                </form>

                <!-- Reject Modal/Form -->
                <form action="{{ route('transfers.reject', $transfer->id) }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="text" name="reason" placeholder="Alasan penolakan..." required class="text-xs px-3 py-1.5 border border-slate-200 rounded-lg w-48">
                    <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition">
                        ✕ Tolak
                    </button>
                </form>
            @endif

            @if($transfer->isDispatchable())
                <form action="{{ route('transfers.dispatch', $transfer->id) }}" method="POST" class="flex flex-wrap gap-2 items-center">
                    @csrf
                    <input type="text" name="driver_name" placeholder="Nama Supir" class="text-xs px-3 py-1.5 border border-slate-200 rounded-lg w-36">
                    <input type="text" name="vehicle_number" placeholder="No. Polisi Armada" class="text-xs px-3 py-1.5 border border-slate-200 rounded-lg w-36">
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        🚚 Berangkatkan (Dispatch & Potong Stok Asal)
                    </button>
                </form>
            @endif

            @if($transfer->isReceivable())
                <button onclick="document.getElementById('receive-form-panel').classList.toggle('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    📦 Proses Penerimaan Fisik di Tujuan &rarr;
                </button>
            @endif

            @if($transfer->status === 'COMPLETED')
                <div class="text-xs font-medium text-emerald-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Transfer telah selesai diterima oleh {{ $transfer->receiver->name ?? 'Staff' }} pada {{ $transfer->received_date?->format('d M Y H:i') }}.
                </div>
            @endif

            @if($transfer->status === 'REJECTED')
                <div class="text-xs font-medium text-rose-700">
                    Ditolak: {{ $transfer->rejection_reason }}
                </div>
            @endif
        </div>

        <!-- Receive Form Panel (if In Transit) -->
        @if($transfer->isReceivable())
            <div id="receive-form-panel" class="hidden mt-6 p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                <h3 class="text-sm font-bold text-slate-800">Konfirmasi Penerimaan Barang di Gudang Tujuan</h3>
                <form action="{{ route('transfers.receive', $transfer->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-slate-500 font-semibold border-b border-slate-200">
                                    <th class="py-2">Produk</th>
                                    <th class="py-2">Batch</th>
                                    <th class="py-2 text-center">Qty Dikirim</th>
                                    <th class="py-2 text-center">Qty Diterima</th>
                                    <th class="py-2">Kondisi Fisik</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($transfer->items as $idx => $item)
                                    <tr>
                                        <td class="py-2 font-medium">{{ $item->product->name ?? '-' }}</td>
                                        <td class="py-2">{{ $item->batch_number }}</td>
                                        <td class="py-2 text-center font-bold">{{ $item->qty_sent }}</td>
                                        <td class="py-2 text-center">
                                            <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                            <input type="number" name="items[{{ $idx }}][qty_received]" value="{{ $item->qty_sent }}" min="0" class="w-20 text-xs px-2 py-1 border border-slate-300 rounded text-center">
                                        </td>
                                        <td class="py-2">
                                            <select name="items[{{ $idx }}][condition_status]" class="text-xs border-slate-300 rounded px-2 py-1">
                                                <option value="GOOD">BAIK (Good)</option>
                                                <option value="DAMAGED">RUSAK (Damaged)</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <input type="text" name="received_notes" placeholder="Catatan penerimaan..." class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold">
                        Konfirmasi & Tambah Stok Gudang Tujuan
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Details Card -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Gudang Asal & Tujuan</h3>
            <div class="space-y-2 text-sm">
                <div>
                    <span class="text-xs text-slate-500 block">Gudang Asal:</span>
                    <span class="font-bold text-slate-800">{{ $transfer->fromWarehouse->name ?? '-' }}</span>
                    <span class="text-xs text-slate-500">({{ $transfer->fromWarehouse->code ?? '-' }})</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Gudang Tujuan:</span>
                    <span class="font-bold text-blue-700">{{ $transfer->toWarehouse->name ?? '-' }}</span>
                    <span class="text-xs text-slate-500">({{ $transfer->toWarehouse->code ?? '-' }})</span>
                </div>
                @if($transfer->notes)
                    <div>
                        <span class="text-xs text-slate-500 block">Catatan Pengirim:</span>
                        <span class="text-slate-700">{{ $transfer->notes }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Armada & Tanggal</h3>
            <div class="space-y-2 text-sm">
                <div>
                    <span class="text-xs text-slate-500 block">Supir / Kendaraan:</span>
                    <span class="font-semibold text-slate-800">{{ $transfer->driver_name ?? '-' }}</span>
                    <span class="text-slate-500">/ {{ $transfer->vehicle_number ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Waktu Diberangkatkan:</span>
                    <span class="text-slate-700">{{ $transfer->dispatched_at ? $transfer->dispatched_at->format('d M Y H:i:s') : 'Belum Berangkat' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Waktu Diterima di Tujuan:</span>
                    <span class="text-slate-700">{{ $transfer->received_date ? $transfer->received_date->format('d M Y H:i:s') : 'Belum Diterima' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-800">Daftar Barang Transfer</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200">
                        <th class="py-3 px-4">SKU & Nama Produk</th>
                        <th class="py-3 px-4">Satuan UOM</th>
                        <th class="py-3 px-4">No. Batch & Kadaluwarsa</th>
                        <th class="py-3 px-4 text-center">Qty Kirim</th>
                        <th class="py-3 px-4 text-center">Qty Terima</th>
                        <th class="py-3 px-4 text-center">Selisih</th>
                        <th class="py-3 px-4 text-center">Kondisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($transfer->items as $item)
                        <tr>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $item->product->name ?? '-' }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $item->product->sku ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4">{{ $item->productUnit->unit_name ?? 'BASE' }}</td>
                            <td class="py-3 px-4 font-mono text-[11px]">
                                {{ $item->batch_number }}
                                <span class="text-slate-400 block">{{ $item->expiry_date?->format('d/m/Y') }}</span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $item->qty_sent }}</td>
                            <td class="py-3 px-4 text-center font-bold text-emerald-700">{{ $item->qty_received }}</td>
                            <td class="py-3 px-4 text-center font-bold {{ $item->discrepancy_qty != 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ $item->discrepancy_qty }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($item->condition_status === 'GOOD')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">BAIK</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">RUSAK</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

