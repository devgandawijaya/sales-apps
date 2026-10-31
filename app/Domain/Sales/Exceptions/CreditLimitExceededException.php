<?php

namespace App\Domain\Sales\Exceptions;

use Exception;

class CreditLimitExceededException extends Exception
{
    public function __construct(
        public readonly string $customerName,
        public readonly float $currentUsed,
        public readonly float $orderAmount,
        public readonly float $creditLimit
    ) {
        $totalProjected = $currentUsed + $orderAmount;
        $exceededBy = $totalProjected - $creditLimit;
        
        parent::__construct(
            sprintf(
                "Total pesanan pelanggan '%s' melebihi batas kredit. Batas kredit: Rp %s, terpakai: Rp %s, pesanan saat ini: Rp %s (melebihi batas Rp %s).",
                $customerName,
                number_format($creditLimit, 0, ',', '.'),
                number_format($currentUsed, 0, ',', '.'),
                number_format($orderAmount, 0, ',', '.'),
                number_format($exceededBy, 0, ',', '.')
            )
        );
    }
}

