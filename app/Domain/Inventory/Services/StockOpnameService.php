<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockOpname;
use App\Domain\Inventory\Models\StockOpnameItem;
use App\Domain\Product\Models\Product;
use App\Domain\Security\Services\AuditLoggerService;
use App\Domain\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockOpnameService
{
    public function __construct(
        protected StockLedgerService $stockLedgerService
    ) {}

    /**
     * Start a new Stock Opname / Cycle Count session.
     * Freezes and snapshots current system stock on hand.
     */
    public function startOpname(
        int $branchId,
        int $warehouseId,
        string $title,
        int $userId,
        string $countType = 'CYCLE_COUNT',
        bool $blindCount = false,
        ?string $notes = null
    ): StockOpname {
        return DB::transaction(function () use (
            $branchId,
            $warehouseId,
            $title,
            $userId,
            $countType,
            $blindCount,
            $notes
        ) {
            $tenantId = TenantContext::getTenantId();
            $opnameNumber = $this->generateOpnameNumber($warehouseId);

            $opname = StockOpname::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId,
                'opname_number' => $opnameNumber,
                'title' => $title,
                'period_date' => Carbon::now()->toDateString(),
                'status' => 'IN_PROGRESS',
                'count_type' => $countType,
                'blind_count' => $blindCount,
                'total_items_counted' => 0,
                'total_discrepancy_qty' => 0,
                'total_cogs_variance' => 0.00,
                'created_by' => $userId,
                'notes' => $notes,
            ]);

            // Snapshot existing stocks in warehouse
            $stocks = Stock::where('warehouse_id', $warehouseId)
                ->where('qty_on_hand', '>', 0)
                ->get();

            foreach ($stocks as $stock) {
                $product = Product::find($stock->product_id);
                $unitCost = $product ? (float) $product->cost_price : 0.0;

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $stock->product_id,
                    'batch_number' => $stock->batch_number,
                    'expiry_date' => $stock->expiry_date ?? Carbon::now()->addYear()->toDateString(),
                    'system_qty' => $stock->qty_on_hand,
                    'physical_qty' => $blindCount ? 0 : $stock->qty_on_hand,
                    'discrepancy_qty' => 0,
                    'unit_cost' => $unitCost,
                    'discrepancy_cogs_value' => 0.00,
                ]);
            }

            AuditLoggerService::log(
                action: 'STOCK_OPNAME_STARTED',
                entityType: 'StockOpname',
                entityId: (string) $opname->id,
                newValues: ['opname_number' => $opnameNumber, 'warehouse_id' => $warehouseId]
            );

            return $opname->load('items.product');
        });
    }

    /**
     * Submit physical count results for discrepancy calculation.
     *
     * @param array $countedItems Array of [item_id => int, physical_qty => int, reason_code => ?string, notes => ?string]
     */
    public function recordCount(int $opnameId, array $countedItems): StockOpname
    {
        return DB::transaction(function () use ($opnameId, $countedItems) {
            $opname = StockOpname::where('id', $opnameId)->lockForUpdate()->firstOrFail();

            if (!$opname->isEditable()) {
                throw new InvalidArgumentException("Pemeriksaan stok #{$opname->opname_number} sudah tidak dapat diubah (Status: {$opname->status}).");
            }

            $totalCounted = 0;
            $totalDiscrepancyQty = 0;
            $totalCogsVariance = 0.00;

            foreach ($countedItems as $countData) {
                $itemId = $countData['item_id'] ?? null;
                $physicalQty = (int) ($countData['physical_qty'] ?? 0);
                $reasonCode = $countData['reason_code'] ?? null;
                $notes = $countData['notes'] ?? null;

                $item = StockOpnameItem::where('id', $itemId)
                    ->where('stock_opname_id', $opname->id)
                    ->first();

                if ($item) {
                    $discrepancy = $physicalQty - $item->system_qty;
                    $cogsVariance = round($discrepancy * (float) $item->unit_cost, 2);

                    $item->update([
                        'physical_qty' => $physicalQty,
                        'discrepancy_qty' => $discrepancy,
                        'discrepancy_cogs_value' => $cogsVariance,
                        'reason_code' => $reasonCode,
                        'notes' => $notes,
                    ]);

                    $totalCounted++;
                    $totalDiscrepancyQty += $discrepancy;
                    $totalCogsVariance += $cogsVariance;
                }
            }

            $opname->update([
                'status' => 'SUBMITTED',
                'total_items_counted' => $totalCounted,
                'total_discrepancy_qty' => $totalDiscrepancyQty,
                'total_cogs_variance' => $totalCogsVariance,
            ]);

            AuditLoggerService::log(
                action: 'STOCK_OPNAME_COUNT_SUBMITTED',
                entityType: 'StockOpname',
                entityId: (string) $opname->id,
                newValues: [
                    'status' => 'SUBMITTED',
                    'total_discrepancy_qty' => $totalDiscrepancyQty,
                    'total_cogs_variance' => $totalCogsVariance,
                ]
            );

            return $opname;
        });
    }

    /**
     * Manager approves and posts the stock opname adjustments into the immutable ledger.
     */
    public function approveAndPost(int $opnameId, int $approverId): StockOpname
    {
        return DB::transaction(function () use ($opnameId, $approverId) {
            $opname = StockOpname::where('id', $opnameId)->lockForUpdate()->firstOrFail();

            if (!$opname->isPostable()) {
                throw new InvalidArgumentException("Hanya hasil hitung fisik yang sudah diajukan yang dapat disetujui (Status: {$opname->status}).");
            }

            foreach ($opname->items as $item) {
                if ($item->discrepancy_qty !== 0) {
                    $entryType = $item->discrepancy_qty > 0 ? 'OPNAME_ADJUST_IN' : 'OPNAME_ADJUST_OUT';
                    $note = "Penyesuaian Opname #{$opname->opname_number}. Alasan: " . ($item->reason_code ?? 'HASIL_COUNT_FISIK');

                    $this->stockLedgerService->recordEntry(
                        warehouseId: $opname->warehouse_id,
                        productId: $item->product_id,
                        batchNumber: $item->batch_number,
                        entryType: $entryType,
                        referenceType: 'STOCK_OPNAME',
                        referenceId: $opname->id,
                        referenceNumber: $opname->opname_number,
                        qtyChange: $item->discrepancy_qty,
                        unitCost: (float) $item->unit_cost,
                        notes: $note,
                        userId: $approverId,
                        expiryDate: $item->expiry_date?->toDateString()
                    );
                }
            }

            $now = Carbon::now();
            $opname->update([
                'status' => 'POSTED',
                'approved_by' => $approverId,
                'approved_at' => $now,
                'posted_at' => $now,
            ]);

            AuditLoggerService::log(
                action: 'STOCK_OPNAME_POSTED',
                entityType: 'StockOpname',
                entityId: (string) $opname->id,
                newValues: ['status' => 'POSTED', 'approved_by' => $approverId]
            );

            return $opname;
        });
    }

    /**
     * Reject stock opname results with mandatory reason.
     */
    public function rejectOpname(int $opnameId, int $approverId, string $reason): StockOpname
    {
        $opname = StockOpname::findOrFail($opnameId);

        if ($opname->status === 'POSTED') {
            throw new InvalidArgumentException("Pemeriksaan stok yang sudah disetujui tidak dapat ditolak.");
        }

        $opname->update([
            'status' => 'REJECTED',
            'rejection_reason' => $reason,
            'approved_by' => $approverId,
            'approved_at' => Carbon::now(),
        ]);

        AuditLoggerService::log(
            action: 'STOCK_OPNAME_REJECTED',
            entityType: 'StockOpname',
            entityId: (string) $opname->id,
            newValues: ['status' => 'REJECTED', 'reason' => $reason]
        );

        return $opname;
    }

    protected function generateOpnameNumber(int $warehouseId): string
    {
        $period = Carbon::now()->format('Ym');
        $random = strtoupper(substr(md5(uniqid((string) $warehouseId, true)), 0, 5));
        return "OPN-{$period}-{$random}";
    }
}

