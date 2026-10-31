<?php

namespace App\Domain\Sales\Exceptions;

use RuntimeException;

class OverdueInvoiceBlockedException extends RuntimeException
{
    public function __construct(
        public readonly string $customerName,
        public readonly string $overdueInvoiceNumber,
        public readonly int $overdueDays,
        public readonly float $outstandingAmount
    ) {
        parent::__construct(
            "Pesanan pelanggan '{$customerName}' belum dapat disetujui karena ada tagihan lewat jatuh tempo (Faktur #{$overdueInvoiceNumber}, terlambat {$overdueDays} hari senilai Rp " . number_format($outstandingAmount, 0, ',', '.') . "). Anda dapat menggunakan persetujuan dispensasi jika pesanan ini diizinkan untuk diproses."
        );
    }
}

