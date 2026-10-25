<?php

namespace App\Domain\Warehouse\DTO;

class CreateGoodsReceiptDTO
{
    /**
     * @param int $branchId
     * @param int $warehouseId
     * @param string $supplierDeliveryNote
     * @param string $receiveDate
     * @param string $receiptType STANDARD or BLIND_RECEIPT
     * @param int $receivedBy
     * @param GoodsReceiptItemDTO[] $items
     * @param int|null $purchaseOrderId
     * @param string|null $driverName
     * @param string|null $vehiclePlateNumber
     * @param string|null $notes
     */
    public function __construct(
        public readonly int $branchId,
        public readonly int $warehouseId,
        public readonly string $supplierDeliveryNote,
        public readonly string $receiveDate,
        public readonly string $receiptType,
        public readonly int $receivedBy,
        public readonly array $items,
        public readonly ?int $purchaseOrderId = null,
        public readonly ?string $driverName = null,
        public readonly ?string $vehiclePlateNumber = null,
        public readonly ?string $notes = null
    ) {}
}

