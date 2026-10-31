<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur Penjualan - <?php echo e($invoice->invoice_number); ?></title>
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
        <a href="<?php echo e(route('finance.invoices.show', $invoice->id)); ?>" class="text-xs text-blue-600 font-bold hover:underline">
            &larr; Kembali ke Detail Faktur
        </a>
        <button onclick="window.print()" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg shadow">
            🖨️ Cetak Faktur (Print)
        </button>
    </div>

    <!-- Paper Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-xl border border-slate-300 shadow-sm space-y-6">
        
        <!-- Header Company & Title -->
        <div class="flex justify-between items-start border-b-2 border-slate-900 pb-4">
            <div>
                <h1 class="text-xl font-extrabold uppercase tracking-tight text-slate-900">
                    <?php echo e($invoice->branch?->name ?? 'PT ENTERPRISE DISTRIBUTION INDONESIA'); ?>

                </h1>
                <p class="text-xs text-slate-600 max-w-sm mt-0.5">
                    <?php echo e($invoice->branch?->address); ?>, <?php echo e($invoice->branch?->city); ?><br>
                    NPWP: 01.345.678.9-021.000 | Telp: <?php echo e($invoice->branch?->phone ?? '021-5550100'); ?>

                </p>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-slate-900 text-white font-extrabold text-sm uppercase tracking-wider rounded">
                    FAKTUR PENJUALAN
                </span>
                <div class="font-mono font-bold text-sm text-slate-800 mt-2"><?php echo e($invoice->invoice_number); ?></div>
                <div class="text-[11px] font-mono text-slate-600">NSFP: <?php echo e($invoice->tax_invoice_number ?? '-'); ?></div>
                <div class="text-xs text-slate-500">Tgl: <?php echo e($invoice->invoice_date?->format('d/m/Y')); ?></div>
            </div>
        </div>

        <!-- Customer & Payment Terms -->
        <div class="grid grid-cols-2 gap-6 text-xs">
            <div class="border border-slate-300 p-3 rounded">
                <strong class="block font-bold text-slate-700 uppercase mb-1">Tagihan Kepada (Pelanggan):</strong>
                <div class="font-bold text-sm text-slate-900"><?php echo e($invoice->customer?->name); ?></div>
                <div class="text-slate-700 mt-0.5"><?php echo e($invoice->customer?->address); ?></div>
                <div class="text-slate-700"><?php echo e($invoice->customer?->city); ?> - <?php echo e($invoice->customer?->province); ?></div>
                <div class="text-slate-500 mt-1">NPWP: <?php echo e($invoice->customer?->tax_id_npwp ?? '00.000.000.0-000.000'); ?></div>
            </div>

            <div class="border border-slate-300 p-3 rounded space-y-1">
                <strong class="block font-bold text-slate-700 uppercase mb-1">Ketentuan Pembayaran:</strong>
                <div class="flex justify-between"><span class="text-slate-600">Jatuh Tempo:</span> <span class="font-bold text-rose-600"><?php echo e($invoice->due_date?->format('d/m/Y')); ?></span></div>
                <div class="flex justify-between"><span class="text-slate-600">Syarat Pembayaran (TOP):</span> <span><?php echo e($invoice->customer?->term_of_payment); ?> Hari</span></div>
                <div class="flex justify-between"><span class="text-slate-600">Ref. Sales Order:</span> <span class="font-mono font-semibold"><?php echo e($invoice->salesOrder?->so_number); ?></span></div>
                <div class="flex justify-between"><span class="text-slate-600">Ref. Surat Jalan (DO):</span> <span class="font-mono font-semibold"><?php echo e($invoice->deliveryOrder?->do_number ?? '-'); ?></span></div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-left text-xs border-collapse border border-slate-300">
            <thead>
                <tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300">
                    <th class="border border-slate-300 p-2 text-center w-10">No</th>
                    <th class="border border-slate-300 p-2">Nama Barang & Deskripsi</th>
                    <th class="border border-slate-300 p-2 text-center w-20">Kuantitas</th>
                    <th class="border border-slate-300 p-2 text-right w-28">Harga Satuan</th>
                    <th class="border border-slate-300 p-2 text-right w-28">Diskon</th>
                    <th class="border border-slate-300 p-2 text-right w-32">Total (IDR)</th>
                </tr>
            </thead>
            <tbody>
                <?php if($invoice->salesOrder && $invoice->salesOrder->items): ?>
                    <?php $__currentLoopData = $invoice->salesOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="border border-slate-300 p-2 text-center"><?php echo e($idx + 1); ?></td>
                            <td class="border border-slate-300 p-2 font-semibold">
                                <?php echo e($item->product?->name); ?>

                                <span class="block text-[10px] text-slate-500 font-mono"><?php echo e($item->product?->sku); ?></span>
                            </td>
                            <td class="border border-slate-300 p-2 text-center"><?php echo e(number_format($item->qty_ordered)); ?></td>
                            <td class="border border-slate-300 p-2 text-right font-mono"><?php echo e(number_format($item->unit_price, 0, ',', '.')); ?></td>
                            <td class="border border-slate-300 p-2 text-right font-mono"><?php echo e(number_format($item->discount_nominal, 0, ',', '.')); ?></td>
                            <td class="border border-slate-300 p-2 text-right font-mono font-bold"><?php echo e(number_format($item->net_amount, 0, ',', '.')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </tbody>
            <tfoot class="font-semibold text-slate-800">
                <tr>
                    <td colspan="5" class="border border-slate-300 p-2 text-right">Subtotal (DPP Nilai Kena Pajak):</td>
                    <td class="border border-slate-300 p-2 text-right font-mono">Rp <?php echo e(number_format($invoice->subtotal, 0, ',', '.')); ?></td>
                </tr>
                <tr>
                    <td colspan="5" class="border border-slate-300 p-2 text-right">PPN 11%:</td>
                    <td class="border border-slate-300 p-2 text-right font-mono">Rp <?php echo e(number_format($invoice->tax_amount, 0, ',', '.')); ?></td>
                </tr>
                <tr class="bg-slate-100 font-extrabold text-slate-900 text-sm">
                    <td colspan="5" class="border border-slate-300 p-2 text-right">TOTAL TAGIHAN (GRAND TOTAL):</td>
                    <td class="border border-slate-300 p-2 text-right font-mono">Rp <?php echo e(number_format($invoice->grand_total, 0, ',', '.')); ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Bank Payment Instructions & Signature -->
        <div class="grid grid-cols-2 gap-6 pt-4 text-xs">
            <div class="border border-slate-300 p-3 rounded space-y-1">
                <strong class="font-bold text-slate-800 block">Informasi Pembayaran Rekening:</strong>
                <p class="text-slate-600">Pembayaran transfer dapat ditujukan ke rekening resmi:</p>
                <div>Bank: <strong>BCA Cabang Sudirman</strong></div>
                <div>No. Rekening: <strong class="font-mono text-sm">521-098-8888</strong></div>
                <div>Atas Nama: <strong>PT ENTERPRISE DISTRIBUTION INDONESIA</strong></div>
                <p class="text-[10px] text-slate-500 pt-1">Harap cantumkan nomor faktur pada berita transfer.</p>
            </div>

            <div class="text-center space-y-16">
                <div>Bagian Keuangan & Penagihan,</div>
                <div class="border-t border-slate-900 mx-12 pt-1 font-semibold">( Finance & Billing Dept )</div>
            </div>
        </div>

    </div>

</body>
</html>

<?php /**PATH /var/www/html/resources/views/finance/invoices/print.blade.php ENDPATH**/ ?>