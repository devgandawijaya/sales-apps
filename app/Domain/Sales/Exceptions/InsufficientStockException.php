<?php

namespace App\Domain\Sales\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly string $productSku,
        public readonly string $productName,
        public readonly int $requestedQty,
        public readonly int $availableQty
    ) {
        parent::__construct(
            sprintf(
                "Stok barang '%s' (%s) tidak mencukupi. Dipesan: %d, tersedia: %d.",
                $productName,
                $productSku,
                $requestedQty,
                $availableQty
            )
        );
    }
}

