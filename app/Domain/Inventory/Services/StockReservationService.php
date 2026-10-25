<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Product\Models\Product;
use App\Domain\Sales\Exceptions\InsufficientStockException;
use Illuminate\Support\Collection;

class StockReservationService
{
    /**
     * Reserve stock using FEFO (First Expired First Out) with pessimistic row locking.
     * Must be called within an active DB::transaction!
     *
     * @throws InsufficientStockException
     */
    public function reserveStock(int $warehouseId, Product $product, int $totalQtyBaseUom): Collection
    {
        // Pessimistic Row Lock with FEFO ordering
        // In PostgreSQL, FOR UPDATE locks rows against concurrent SO allocations
        $stockBatches = Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $product->id)
            ->whereRaw('qty_on_hand > qty_reserved')
            ->where('expiry_date', '>', now()->toDateString())
            ->orderBy('expiry_date', 'asc')
            ->lockForUpdate()
            ->get();

        $totalAvailable = $stockBatches->sum(fn(Stock $stock) => $stock->qty_on_hand - $stock->qty_reserved);

        if ($totalAvailable < $totalQtyBaseUom) {
            throw new InsufficientStockException(
                productSku: $product->sku,
                productName: $product->name,
                requestedQty: $totalQtyBaseUom,
                availableQty: $totalAvailable
            );
        }

        $remainingToAllocate = $totalQtyBaseUom;
        $allocations = collect();

        foreach ($stockBatches as $stock) {
            if ($remainingToAllocate <= 0) {
                break;
            }

            $availableInBatch = $stock->qty_on_hand - $stock->qty_reserved;
            $allocateFromBatch = min($remainingToAllocate, $availableInBatch);

            // Update reserved quantity atomically
            $stock->increment('qty_reserved', $allocateFromBatch);

            $allocations->push([
                'stock_id' => $stock->id,
                'batch_number' => $stock->batch_number,
                'expiry_date' => $stock->expiry_date,
                'allocated_qty' => $allocateFromBatch,
            ]);

            $remainingToAllocate -= $allocateFromBatch;
        }

        return $allocations;
    }

    /**
     * Release previously reserved stock (e.g. if SO cancelled or rejected).
     */
    public function releaseReservation(int $warehouseId, int $productId, string $batchNumber, int $qtyToRelease): void
    {
        $stock = Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('batch_number', $batchNumber)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            $stock->decrement('qty_reserved', min($stock->qty_reserved, $qtyToRelease));
        }
    }
}

