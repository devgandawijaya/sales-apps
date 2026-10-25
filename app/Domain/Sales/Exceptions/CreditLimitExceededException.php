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
                "Plafon kredit pelanggan '%s' terlampaui. Limit: Rp %s, Terpakai: Rp %s, Nilai Order: Rp %s. Melebihi plafon sebesar Rp %s.",
                $customerName,
                number_format($creditLimit, 2, ',', '.'),
                number_format($currentUsed, 2, ',', '.'),
                number_format($orderAmount, 2, ',', '.'),
                number_format($exceededBy, 2, ',', '.')
            )
        );
    }
}

