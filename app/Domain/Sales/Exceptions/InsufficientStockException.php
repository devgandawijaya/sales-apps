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
                "Stok tidak mencukupi untuk item [%s] %s. Diminta: %d unit (base UOM), Tersedia: %d unit.",
                $productSku,
                $productName,
                $requestedQty,
                $availableQty
            )
        );
    }
}

