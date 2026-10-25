<?php

namespace App\Domain\Sales\DTO;

class SalesOrderItemDTO
{
    public function __construct(
        public readonly int $productId,
        public readonly int $productUnitId,
        public readonly int $qtyOrdered,
        public readonly ?float $customUnitPrice = null,
        public readonly float $discountPercent1 = 0.0,
        public readonly float $discountPercent2 = 0.0,
        public readonly float $discountNominal = 0.0
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['product_id'],
            productUnitId: (int) $data['product_unit_id'],
            qtyOrdered: (int) $data['qty_ordered'],
            customUnitPrice: isset($data['custom_unit_price']) ? (float) $data['custom_unit_price'] : null,
            discountPercent1: (float) ($data['discount_percent_1'] ?? 0.0),
            discountPercent2: (float) ($data['discount_percent_2'] ?? 0.0),
            discountNominal: (float) ($data['discount_nominal'] ?? 0.0)
        );
    }
}

