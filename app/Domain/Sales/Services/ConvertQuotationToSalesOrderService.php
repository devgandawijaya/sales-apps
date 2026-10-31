<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\DTO\CreateSalesOrderDTO;
use App\Domain\Sales\DTO\SalesOrderItemDTO;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Models\SalesQuotation;
use App\Domain\Security\Services\AuditLoggerService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ConvertQuotationToSalesOrderService
{
    public function __construct(
        protected CreateSalesOrderService $createSalesOrderService
    ) {}

    /**
     * Convert an approved / accepted quotation directly into a Sales Order.
     *
     * @throws InvalidArgumentException
     */
    public function execute(SalesQuotation $quotation, int $warehouseId, ?string $poNumberMt = null): SalesOrder
    {
        if (!$quotation->isConvertible()) {
            throw new InvalidArgumentException("Penawaran #{$quotation->quotation_number} tidak dapat dikonversi (Status saat ini: {$quotation->status}).");
        }

        return DB::transaction(function () use ($quotation, $warehouseId, $poNumberMt) {
            // Build item DTOs with frozen price snapshot from quotation
            $itemDTOs = [];
            foreach ($quotation->items as $item) {
                $itemDTOs[] = new SalesOrderItemDTO(
                    productId: $item->product_id,
                    productUnitId: $item->product_unit_id,
                    qtyOrdered: $item->qty_ordered,
                    customUnitPrice: (float) $item->unit_price, // Preserves exact snapshot price
                    discountPercent1: (float) $item->discount_percent_1,
                    discountPercent2: (float) $item->discount_percent_2,
                    discountNominal: (float) $item->discount_nominal
                );
            }

            $orderType = $quotation->channel === 'MT' ? 'MODERN_TRADE_PO' : 'TAKING_ORDER';

            $createOrderDto = new CreateSalesOrderDTO(
                branchId: $quotation->branch_id,
                customerId: $quotation->customer_id,
                salesmanId: $quotation->salesman_id,
                warehouseId: $warehouseId,
                orderDate: now()->toDateString(),
                orderType: $orderType,
                poNumberMt: $poNumberMt,
                notes: "Dikonversi dari Penawaran Harga #{$quotation->quotation_number}. " . ($quotation->notes ?? ''),
                items: $itemDTOs
            );

            // Execute SO creation
            $salesOrder = $this->createSalesOrderService->execute($createOrderDto);

            // Link quotation to sales order
            $quotation->update([
                'status' => 'CONVERTED_TO_SO',
                'converted_sales_order_id' => $salesOrder->id,
            ]);

            $salesOrder->update([
                'quotation_id' => $quotation->id,
            ]);

            AuditLoggerService::log(
                action: 'QUOTATION_CONVERTED_TO_SO',
                entityType: 'SalesQuotation',
                entityId: (string) $quotation->id,
                newValues: [
                    'quotation_number' => $quotation->quotation_number,
                    'sales_order_id' => $salesOrder->id,
                    'so_number' => $salesOrder->so_number,
                ]
            );

            return $salesOrder;
        });
    }
}

