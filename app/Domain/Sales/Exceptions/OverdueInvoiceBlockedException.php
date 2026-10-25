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
            "Persetujuan Sales Order DIBLOKIR: Pelanggan '{$customerName}' memiliki faktur jatuh tempo '{$overdueInvoiceNumber}' yang telah menunggak {$overdueDays} hari sebesar Rp " . number_format($outstandingAmount, 0, ',', '.') . ". Harap lunasi atau minta dispensasi Direksi."
        );
    }
}

