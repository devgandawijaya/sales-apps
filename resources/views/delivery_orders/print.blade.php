<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - {{ $deliveryOrder->do_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; color: #000; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 font-sans">

    <!-- Print Button Bar -->
    <div class="max-w-4xl mx-auto mb-4 no-print flex justify-between items-center bg-white p-3 rounded-xl border border-slate-200">
        <a href="{{ route('delivery-orders.show', $deliveryOrder->id) }}" class="text-xs text-blue-600 font-bold hover:underline">
            &larr; Kembali ke Detail
        </a>
        <button onclick="window.print()" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg shadow">
            🖨️ Cetak Dokumen (Print)
        </button>
    </div>

    <!-- Paper Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-xl border border-slate-300 shadow-sm space-y-6">
        
        <!-- Header Company & Title -->
        <div class="flex justify-between items-start border-b-2 border-slate-900 pb-4">
            <div>
                <h1 class="text-xl font-extrabold uppercase tracking-tight text-slate-900">
                    {{ $deliveryOrder->branch?->name ?? 'PT ENTERPRISE DISTRIBUTION INDONESIA' }}
                </h1>
                <p class="text-xs text-slate-600 max-w-sm mt-0.5">
                    {{ $deliveryOrder->branch?->address }}, {{ $deliveryOrder->branch?->city }}<br>
                    Telp: {{ $deliveryOrder->branch?->phone ?? '021-5550100' }} | Email: {{ $deliveryOrder->branch?->email ?? 'hq@enterprisesales.co.id' }}
                </p>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-slate-900 text-white font-extrabold text-sm uppercase tracking-wider rounded">
                    SURAT JALAN
                </span>
                <div class="font-mono font-bold text-sm text-slate-800 mt-2">{{ $deliveryOrder->do_number }}</div>
                <div class="text-xs text-slate-500">Tgl: {{ $deliveryOrder->delivery_date?->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Destination & Dispatch Info -->
        <div class="grid grid-cols-2 gap-6 text-xs">
            <div class="border border-slate-300 p-3 rounded">
                <strong class="block font-bold text-slate-700 uppercase mb-1">Kepada Yth. (Penerima):</strong>
                <div class="font-bold text-sm text-slate-900">{{ $deliveryOrder->salesOrder?->customer?->name }}</div>
                <div class="text-slate-700 mt-0.5">{{ $deliveryOrder->salesOrder?->customer?->address }}</div>
                <div class="text-slate-700">{{ $deliveryOrder->salesOrder?->customer?->city }} - {{ $deliveryOrder->salesOrder?->customer?->province }}</div>
                <div class="text-slate-500 mt-1">Telp: {{ $deliveryOrder->salesOrder?->customer?->phone ?? '-' }}</div>
            </div>

            <div class="border border-slate-300 p-3 rounded space-y-1">
                <strong class="block font-bold text-slate-700 uppercase mb-1">Rincian Pengiriman:</strong>
                <div class="flex justify-between"><span class="text-slate-600">No. Sales Order:</span> <span class="font-mono font-bold">{{ $deliveryOrder->salesOrder?->so_number }}</span></div>
                <div class="flex justify-between"><span class="text-slate-600">Gudang Asal:</span> <span>{{ $deliveryOrder->warehouse?->name }}</span></div>
                <div class="flex justify-between"><span class="text-slate-600">Nama Supir:</span> <span class="font-semibold">{{ $deliveryOrder->driver_name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-600">No. Kendaraan:</span> <span class="font-mono font-semibold">{{ $deliveryOrder->vehicle_plate_number ?? '-' }}</span></div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-left text-xs border-collapse border border-slate-300">
            <thead>
                <tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300">
                    <th class="border border-slate-300 p-2 text-center w-10">No</th>
                    <th class="border border-slate-300 p-2">Kode SKU</th>
                    <th class="border border-slate-300 p-2">Deskripsi Barang</th>
                    <th class="border border-slate-300 p-2 text-center">Batch / Lot</th>
                    <th class="border border-slate-300 p-2 text-center">Kedaluwarsa</th>
                    <th class="border border-slate-300 p-2 text-center w-24">Kuantitas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryOrder->items as $idx => $item)
                    <tr>
                        <td class="border border-slate-300 p-2 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-slate-300 p-2 font-mono text-[10px]">{{ $item->product?->sku }}</td>
                        <td class="border border-slate-300 p-2 font-semibold">{{ $item->product?->name }}</td>
                        <td class="border border-slate-300 p-2 font-mono text-center">{{ $item->batch_number }}</td>
                        <td class="border border-slate-300 p-2 text-center">{{ $item->expiry_date?->format('d/m/Y') }}</td>
                        <td class="border border-slate-300 p-2 text-center font-bold">{{ number_format($item->qty_shipped) }} Unit</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Signatures (3 Kolom: Gudang, Supir, Penerima) -->
        <div class="pt-8 grid grid-cols-3 gap-6 text-center text-xs">
            <div class="space-y-16">
                <div>Petugas Gudang (Pengirim)</div>
                <div class="border-t border-slate-900 mx-6 pt-1 font-semibold">( ................................ )</div>
            </div>
            <div class="space-y-16">
                <div>Supir / Ekspedisi</div>
                <div class="border-t border-slate-900 mx-6 pt-1 font-semibold">
                    ( {{ $deliveryOrder->driver_name ?? '................................' }} )
                </div>
            </div>
            <div class="space-y-16">
                <div>Tanda Terima (Toko / DC)</div>
                <div class="border-t border-slate-900 mx-6 pt-1 font-semibold">
                    ( {{ $deliveryOrder->recipient_name ?? '................................' }} )
                </div>
            </div>
        </div>

        <div class="text-[10px] text-slate-400 text-center pt-6 border-t border-slate-200">
            Harap periksa kembali barang yang diterima. Komplain atau retur barang fisik wajib menyertakan dokumen Surat Jalan asli dalam waktu 1x24 jam.
        </div>

    </div>

</body>
</html>

