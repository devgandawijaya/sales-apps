<?php

namespace App\Domain\Sales\Services;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Services\StockLedgerService;
use App\Domain\Sales\Models\DeliveryOrder;
use App\Domain\Sales\Models\DeliveryOrderItem;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Security\Services\AuditLoggerService;
use App\Domain\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateDeliveryOrderService
{
    public function __construct(
        protected StockLedgerService $stockLedgerService
    ) {}

    /**
     * Create Delivery Order from an approved Sales Order.
     */
    public function execute(
        int $salesOrderId,
        ?string $driverName = null,
        ?string $vehiclePlateNumber = null,
        string $deliveryType = 'DIRECT_STORE',
        ?string $notes = null,
        ?int $userId = null
    ): DeliveryOrder {
        return DB::transaction(function () use (
            $salesOrderId,
            $driverName,
            $vehiclePlateNumber,
            $deliveryType,
            $notes,
            $userId
        ) {
            $tenantId = TenantContext::getTenantId();

            /** @var SalesOrder $order */
            $order = SalesOrder::with(['items', 'customer', 'branch', 'warehouse'])
                ->where('id', $salesOrderId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($order->status, ['APPROVED', 'PROCESSING'])) {
                throw new InvalidArgumentException("Surat Jalan hanya dapat dibuat jika pesanan telah disetujui atau sedang diproses (Status saat ini: {$order->status}).");
            }

            $doNumber = $this->generateDoNumber($order->branch_id);

            $deliveryOrder = DeliveryOrder::create([
                'tenant_id' => $tenantId,
                'branch_id' => $order->branch_id,
                'warehouse_id' => $order->warehouse_id,
                'sales_order_id' => $order->id,
                'do_number' => $doNumber,
                'delivery_date' => Carbon::now()->toDateString(),
                'delivery_type' => $deliveryType,
                'status' => 'DISPATCHED',
                'driver_name' => $driverName,
                'vehicle_plate_number' => $vehiclePlateNumber,
                'notes' => $notes ?? 'Pengiriman resmi untuk Sales Order ' . $order->so_number,
            ]);

            foreach ($order->items as $item) {
                $qtyNeeded = $item->qty_in_base_uom;

                // Find batches reserved for this product in warehouse
                $batches = Stock::where('warehouse_id', $order->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->where('qty_on_hand', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                $remainingToShip = $qtyNeeded;

                foreach ($batches as $batch) {
                    if ($remainingToShip <= 0) {
                        break;
                    }

                    $shipFromBatch = min($remainingToShip, $batch->qty_on_hand);

                    // Decrement reserved if any
                    if ($batch->qty_reserved > 0) {
                        $batch->decrement('qty_reserved', min($batch->qty_reserved, $shipFromBatch));
                    }

                    // Record double-entry stock ledger mutasi OUT
                    $this->stockLedgerService->recordEntry(
                        warehouseId: $order->warehouse_id,
                        productId: $item->product_id,
                        batchNumber: $batch->batch_number,
                        entryType: 'OUT',
                        referenceType: 'DELIVERY_ORDER',
                        referenceId: $deliveryOrder->id,
                        referenceNumber: $deliveryOrder->do_number,
                        qtyChange: -$shipFromBatch,
                        unitCost: (float) $item->unit_price,
                        notes: "Pengiriman Surat Jalan {$deliveryOrder->do_number} untuk SO {$order->so_number}",
                        userId: $userId,
                        expiryDate: $batch->expiry_date
                    );

                    DeliveryOrderItem::create([
                        'delivery_order_id' => $deliveryOrder->id,
                        'sales_order_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'batch_number' => $batch->batch_number,
                        'expiry_date' => $batch->expiry_date,
                        'qty_shipped' => $shipFromBatch,
                        'qty_received' => 0,
                    ]);

                    $remainingToShip -= $shipFromBatch;
                }

                // Update delivered qty on sales order item
                $item->increment('qty_delivered_base_uom', $qtyNeeded - $remainingToShip);
            }

            // Update sales order status to DELIVERED or PROCESSING
            $order->update([
                'status' => 'DELIVERED',
            ]);

            AuditLoggerService::log(
                action: 'DELIVERY_ORDER_CREATED',
                entityType: 'DeliveryOrder',
                entityId: (string) $deliveryOrder->id,
                newValues: [
                    'do_number' => $deliveryOrder->do_number,
                    'sales_order_id' => $order->id,
                    'status' => 'DISPATCHED'
                ]
            );

            return $deliveryOrder->fresh(['items.product', 'salesOrder.customer', 'branch', 'warehouse']);
        });
    }

    protected function generateDoNumber(int $branchId): string
    {
        $prefix = 'DO-' . date('Ym') . '-';
        $count = DeliveryOrder::withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->where('do_number', 'like', $prefix . '%')
            ->count() + 1;

        return $prefix . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
    }
}

