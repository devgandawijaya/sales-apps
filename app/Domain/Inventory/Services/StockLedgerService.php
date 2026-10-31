<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockLedgerEntry;
use App\Domain\Inventory\Models\StockMutation;
use App\Domain\Product\Models\Product;
use App\Domain\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockLedgerService
{
    /**
     * Record a verified stock transaction in the immutable double-entry ledger.
     * Updates physical stock on hand atomically with pessimistic locking.
     *
     * @throws InvalidArgumentException
     */
    public function recordEntry(
        int $warehouseId,
        int $productId,
        string $batchNumber,
        string $entryType,
        string $referenceType,
        ?int $referenceId,
        ?string $referenceNumber,
        int $qtyChange, // positive (in) or negative (out)
        float $unitCost = 0.0,
        ?string $notes = null,
        ?int $userId = null,
        ?string $expiryDate = null
    ): StockLedgerEntry {
        if ($qtyChange === 0) {
            throw new InvalidArgumentException("Perubahan kuantitas stok tidak boleh bernilai nol (0).");
        }

        return DB::transaction(function () use (
            $warehouseId,
            $productId,
            $batchNumber,
            $entryType,
            $referenceType,
            $referenceId,
            $referenceNumber,
            $qtyChange,
            $unitCost,
            $notes,
            $userId,
            $expiryDate
        ) {
            $tenantId = TenantContext::getTenantId();

            // Lock physical stock row
            $stock = Stock::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->where('batch_number', $batchNumber)
                ->lockForUpdate()
                ->first();

            $qtyBefore = $stock ? (int) $stock->qty_on_hand : 0;
            $qtyAfter = $qtyBefore + $qtyChange;

            if ($qtyAfter < 0) {
                $product = Product::find($productId);
                $sku = $product ? $product->sku : "PRD-{$productId}";
                throw new InvalidArgumentException(
                    "Stok tidak mencukupi untuk {$sku} (Batch: {$batchNumber}) di gudang #{$warehouseId}. Sisa: {$qtyBefore}, diminta: " . abs($qtyChange)
                );
            }

            // Update or create physical Stock record
            if ($stock) {
                $stock->qty_on_hand = $qtyAfter;
                if ($expiryDate && !$stock->expiry_date) {
                    $stock->expiry_date = $expiryDate;
                }
                $stock->save();
            } else {
                $stock = Stock::create([
                    'tenant_id' => $tenantId,
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $expiryDate ?? Carbon::now()->addYear()->toDateString(),
                    'qty_on_hand' => $qtyAfter,
                    'qty_reserved' => 0,
                ]);
            }

            // Compute total cost
            $totalCost = round(abs($qtyChange) * $unitCost, 2);

            // Create immutable stock ledger entry
            $ledgerEntry = StockLedgerEntry::create([
                'tenant_id' => $tenantId,
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'batch_number' => $batchNumber,
                'entry_type' => $entryType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reference_number' => $referenceNumber,
                'qty_change' => $qtyChange,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'notes' => $notes,
                'created_by' => $userId,
                'created_at' => Carbon::now(),
            ]);

            // Synchronize with legacy StockMutation if a valid user is present
            $creatorId = $userId ?: (DB::table('users')->value('id') ?? null);
            if ($creatorId) {
                StockMutation::create([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'batch_number' => $batchNumber,
                    'transaction_type' => $entryType,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId ?? 0,
                    'qty_in' => $qtyChange > 0 ? $qtyChange : 0,
                    'qty_out' => $qtyChange < 0 ? abs($qtyChange) : 0,
                    'balance_after' => $qtyAfter,
                    'notes' => $notes,
                    'created_by' => $creatorId,
                    'created_at' => Carbon::now(),
                ]);
            }

            return $ledgerEntry;
        });
    }
}
