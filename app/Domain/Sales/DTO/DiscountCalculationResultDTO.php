<?php

namespace App\Domain\Sales\DTO;

class DiscountCalculationResultDTO
{
    public function __construct(
        public readonly float $unitPrice,
        public readonly float $grossAmount,
        public readonly float $itemDiscountTotal,
        public readonly float $netAmount,
        public readonly float $onInvoiceDiscountRate,
        public readonly float $offInvoiceRebateRate
    ) {}
}

