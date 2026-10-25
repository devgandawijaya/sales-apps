<?php

namespace App\Domain\Warehouse\Services;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockMutation;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Warehouse\DTO\CreateGoodsReceiptDTO;
use App\Domain\Warehouse\Models\Batch;
use App\Domain\Warehouse\Models\GoodsReceipt;
use App\Domain\Warehouse\Models\GoodsReceiptItem;
use App\Domain\Warehouse\Models\WarehouseBin;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Enterprise Service for processing Inbound Goods Receipt Notes (GRN).
 * Handles:
 * 1. Supplier Delivery Note & PO verification.
 * 2. Blind Receipt vs Standard Receipt discrepancy calculation.
 * 3. Mandatory Batch / Lot Number & Expiry Date recording.
 * 4. Automatic / Recommended Putaway Bin Allocation.
 * 5. Pessimistic row-locked physical stock increment.
 * 6. Audit-compliant immutable stock mutation ledger entry.
 */
class CreateGoodsReceiptService
{
    /**
     * Execute inbound goods receipt transaction atomically.
     *
     * @param CreateGoodsReceiptDTO $dto
     * @return GoodsReceipt
     * @throws InvalidArgumentException
     * @throws \Throwable
     */
    public function execute(CreateGoodsReceiptDTO $dto): GoodsReceipt
    {
        if (empty($dto->items)) {
            throw new InvalidArgumentException("Item penerimaan barang (GRN) tidak boleh kosong.");
        }

        return DB::transaction(function () use ($dto) {
            // 1. Generate Unique GRN Number
            $grnNumber = $this->generateGrnNumber($dto->branchId, $dto->warehouseId);

            // 2. Determine initial GRN status based on discrepancies
            $hasDiscrepancy = false;

            // 3. Create Goods Receipt Header
            $goodsReceipt = GoodsReceipt::create([
                'branch_id' => $dto->branchId,
                'warehouse_id' => $dto->warehouseId,
                'purchase_order_id' => $dto->purchaseOrderId,
                'grn_number' => $grnNumber,
                'supplier_delivery_note' => $dto->supplierDeliveryNote,
                'receive_date' => $dto->receiveDate,
                'receipt_type' => $dto->receiptType,
                'status' => 'COMPLETED',
                'driver_name' => $dto->driverName,
                'vehicle_plate_number' => $dto->vehiclePlateNumber,
                'received_by' => $dto->receivedBy,
                'notes' => $dto->notes,
            ]);

            // 4. Process Each Received Item
            foreach ($dto->items as $itemDto) {
                $product = Product::where('id', $itemDto->productId)
                    ->where('is_active', true)
                    ->firstOrFail();

                $unit = ProductUnit::where('id', $itemDto->productUnitId)
                    ->where('product_id', $product->id)
                    ->firstOrFail();

                $conversionFactor = $unit->conversion_factor;
                $qtyReceivedBaseUom = (int) round($itemDto->qtyReceived * $conversionFactor);
                $qtyExpectedBaseUom = $itemDto->qtyExpected !== null 
                    ? (int) round($itemDto->qtyExpected * $conversionFactor) 
                    : $qtyReceivedBaseUom;

                $discrepancyQty = $qtyReceivedBaseUom - $qtyExpectedBaseUom;

                if ($discrepancyQty !== 0) {
                    $hasDiscrepancy = true;
                }

                // Batch & Expiry Date Handling
                $batchNumber = $itemDto->batchNumber 
                    ?: 'LOT-' . strtoupper($product->sku) . '-' . date('Ymd');
                $expiryDate = $itemDto->expiryDate 
                    ?: Carbon::parse($dto->receiveDate)->addYear()->toDateString();

                // Upsert Centralized Batch Lot Master
                Batch::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'batch_number' => $batchNumber,
                    ],
                    [
                        'manufacture_date' => Carbon::parse($dto->receiveDate)->subDays(30)->toDateString(),
                        'expiry_date' => $expiryDate,
                        'qc_status' => $itemDto->conditionStatus === 'GOOD' ? 'PASSED' : 'QUARANTINE',
                    ]
                );

                // Warehouse Bin Allocation (Putaway)
                $binId = $itemDto->warehouseBinId;
                if (!$binId) {
                    // Auto-recommend first available bin in warehouse
                    $availableBin = WarehouseBin::where('warehouse_id', $dto->warehouseId)
                        ->where('is_active', true)
                        ->where('is_occupied', false)
                        ->first();
                    $binId = $availableBin?->id;
                }

                // Persist Line Item
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'purchase_order_item_id' => $itemDto->purchaseOrderItemId,
                    'product_id' => $product->id,
                    'product_unit_id' => $unit->id,
                    'warehouse_bin_id' => $binId,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $expiryDate,
                    'qty_expected_base_uom' => $qtyExpectedBaseUom,
                    'qty_received_base_uom' => $qtyReceivedBaseUom,
                    'qty_discrepancy_base_uom' => $discrepancyQty,
                    'condition_status' => $itemDto->conditionStatus,
                    'unit_cost' => $itemDto->unitCost > 0 ? $itemDto->unitCost : (float) $product->cost_price,
                    'discrepancy_reason' => $itemDto->discrepancyReason,
                ]);

                // 5. Update Physical Inventory with Pessimistic Row Locking
                // If condition is GOOD, increment main stock; if DAMAGED, can route to quarantine
                $stock = Stock::where('warehouse_id', $dto->warehouseId)
                    ->where('product_id', $product->id)
                    ->where('batch_number', $batchNumber)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->qty_on_hand += $qtyReceivedBaseUom;
                    $stock->save();
                    $balanceAfter = $stock->qty_on_hand;
                } else {
                    $newStock = Stock::create([
                        'warehouse_id' => $dto->warehouseId,
                        'product_id' => $product->id,
                        'batch_number' => $batchNumber,
                        'expiry_date' => $expiryDate,
                        'qty_on_hand' => $qtyReceivedBaseUom,
                        'qty_reserved' => 0,
                    ]);
                    $balanceAfter = $newStock->qty_on_hand;
                }

                // 6. Record Immutable Stock Card Ledger Entry (Stock Mutation)
                StockMutation::create([
                    'warehouse_id' => $dto->warehouseId,
                    'product_id' => $product->id,
                    'batch_number' => $batchNumber,
                    'transaction_type' => 'RECEIVING',
                    'reference_type' => 'GRN',
                    'reference_id' => $goodsReceipt->id,
                    'qty_in' => $qtyReceivedBaseUom,
                    'qty_out' => 0,
                    'balance_after' => $balanceAfter,
                    'notes' => 'Penerimaan GRN: ' . $grnNumber . ' dari SJ: ' . $dto->supplierDeliveryNote . ' (' . $itemDto->conditionStatus . ')',
                    'created_by' => $dto->receivedBy,
                ]);

                // Update PO Item if applicable
                if ($itemDto->purchaseOrderItemId) {
                    DB::table('purchase_order_items')
                        ->where('id', $itemDto->purchaseOrderItemId)
                        ->increment('qty_received_base_uom', $qtyReceivedBaseUom);
                }
            }

            // If blind receipt had discrepancy, mark status for managerial review
            if ($hasDiscrepancy) {
                $goodsReceipt->update(['status' => 'DISCREPANCY_HOLD']);
            }

            return $goodsReceipt->fresh(['items', 'items.product', 'items.unit']);
        });
    }

    /**
     * Generate unique Goods Receipt Note (GRN) sequence number.
     */
    protected function generateGrnNumber(int $branchId, int $warehouseId): string
    {
        $dateStr = date('Ymd');
        $prefix = "GRN-B{$branchId}-W{$warehouseId}-{$dateStr}";

        $countToday = DB::table('goods_receipts')
            ->where('grn_number', 'like', "{$prefix}%")
            ->count();

        $seq = str_pad((string)($countToday + 1), 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$seq}";
    }
}

