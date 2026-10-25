<?php

namespace App\Domain\Sales\DTO;

class CreateSalesOrderDTO
{
    /**
     * @param SalesOrderItemDTO[] $items
     */
    public function __construct(
        public readonly int $branchId,
        public readonly int $customerId,
        public readonly int $salesmanId,
        public readonly int $warehouseId,
        public readonly string $orderDate,
        public readonly string $orderType = 'TAKING_ORDER', // TAKING_ORDER, CANVASS, MODERN_TRADE_PO
        public readonly ?string $poNumberMt = null,
        public readonly ?string $notes = null,
        public readonly array $items = []
    ) {}

    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn(array $item) => SalesOrderItemDTO::fromArray($item),
            $data['items'] ?? []
        );

        return new self(
            branchId: (int) $data['branch_id'],
            customerId: (int) $data['customer_id'],
            salesmanId: (int) $data['salesman_id'],
            warehouseId: (int) $data['warehouse_id'],
            orderDate: $data['order_date'] ?? date('Y-m-d'),
            orderType: $data['order_type'] ?? 'TAKING_ORDER',
            poNumberMt: $data['po_number_mt'] ?? null,
            notes: $data['notes'] ?? null,
            items: $items
        );
    }
}

