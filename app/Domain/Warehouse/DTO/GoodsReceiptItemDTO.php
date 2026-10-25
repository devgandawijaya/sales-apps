<?php

namespace App\Domain\Warehouse\DTO;

class GoodsReceiptItemDTO
{
    public function __construct(
        public readonly int $productId,
        public readonly int $productUnitId,
        public readonly float $qtyReceived,
        public readonly ?float $qtyExpected = null,
        public readonly ?string $batchNumber = null,
        public readonly ?string $expiryDate = null,
        public readonly ?int $warehouseBinId = null,
        public readonly string $conditionStatus = 'GOOD', // GOOD, QC_PENDING, DAMAGED
        public readonly float $unitCost = 0.00,
        public readonly ?string $discrepancyReason = null,
        public readonly ?int $purchaseOrderItemId = null
    ) {}
}

