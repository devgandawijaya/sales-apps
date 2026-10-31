<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockTransfer;
use App\Domain\Inventory\Models\StockTransferItem;
use App\Domain\Product\Models\Product;
use App\Domain\Security\Services\AuditLoggerService;
use App\Domain\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InterBranchTransferService
{
    public function __construct(
        protected StockLedgerService $stockLedgerService
    ) {}

    /**
     * Create an inter-warehouse transfer request in DRAFT status.
     *
     * @param array $items Array of [product_id, product_unit_id, batch_number, expiry_date, qty_sent]
     */
    public function createTransfer(
        int $fromWarehouseId,
        int $toWarehouseId,
        array $items,
        int $userId,
        ?string $notes = null,
        ?int $transitWarehouseId = null
    ): StockTransfer {
        if ($fromWarehouseId === $toWarehouseId) {
            throw new InvalidArgumentException("Gudang asal dan gudang tujuan transfer tidak boleh sama.");
        }

        if (empty($items)) {
            throw new InvalidArgumentException("Daftar barang transfer tidak boleh kosong.");
        }

        return DB::transaction(function () use (
            $fromWarehouseId,
            $toWarehouseId,
            $items,
            $userId,
            $notes,
            $transitWarehouseId
        ) {
            $tenantId = TenantContext::getTenantId();
            $transferNumber = $this->generateTransferNumber($fromWarehouseId);

            $transfer = StockTransfer::create([
                'tenant_id' => $tenantId,
                'transfer_number' => $transferNumber,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'transit_warehouse_id' => $transitWarehouseId,
                'status' => 'DRAFT',
                'approval_level' => 1,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            foreach ($items as $itemData) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $itemData['product_id'],
                    'product_unit_id' => $itemData['product_unit_id'],
                    'batch_number' => $itemData['batch_number'],
                    'expiry_date' => $itemData['expiry_date'],
                    'qty_sent' => (int) $itemData['qty_sent'],
                    'qty_received' => 0,
                    'discrepancy_qty' => 0,
                    'condition_status' => 'GOOD',
                ]);
            }

            AuditLoggerService::log(
                action: 'STOCK_TRANSFER_CREATED',
                entityType: 'StockTransfer',
                entityId: (string) $transfer->id,
                newValues: ['transfer_number' => $transferNumber, 'status' => 'DRAFT']
            );

            return $transfer->load('items.product');
        });
    }

    /**
     * Submit transfer request for branch manager / warehouse manager approval.
     */
    public function submitTransfer(int $transferId): StockTransfer
    {
        $transfer = StockTransfer::findOrFail($transferId);

        if (!$transfer->isSubmittable()) {
            throw new InvalidArgumentException("Hanya transfer berstatus DRAFT yang dapat diajukan.");
        }

        $transfer->update(['status' => 'SUBMITTED']);

        AuditLoggerService::log(
            action: 'STOCK_TRANSFER_SUBMITTED',
            entityType: 'StockTransfer',
            entityId: (string) $transfer->id,
            newValues: ['status' => 'SUBMITTED']
        );

        return $transfer;
    }

    /**
     * Approve transfer request.
     */
    public function approveTransfer(int $transferId, int $approverId): StockTransfer
    {
        $transfer = StockTransfer::findOrFail($transferId);

        if (!$transfer->isApprovable()) {
            throw new InvalidArgumentException("Transfer #{$transfer->transfer_number} tidak dalam status untuk disetujui (Status: {$transfer->status}).");
        }

        $transfer->update([
            'status' => 'APPROVED',
            'approved_by' => $approverId,
            'approved_at' => Carbon::now(),
        ]);

        AuditLoggerService::log(
            action: 'STOCK_TRANSFER_APPROVED',
            entityType: 'StockTransfer',
            entityId: (string) $transfer->id,
            newValues: ['status' => 'APPROVED', 'approved_by' => $approverId]
        );

        return $transfer;
    }

    /**
     * Dispatch stock transfer:
     * - Deducts physical stock from source warehouse and writes immutable ledger entry.
     * - Sets status to IN_TRANSIT.
     * - DOES NOT increment destination warehouse stock yet!
     */
    public function dispatchTransfer(
        int $transferId,
        ?string $driverName = null,
        ?string $vehicleNumber = null
    ): StockTransfer {
        return DB::transaction(function () use ($transferId, $driverName, $vehicleNumber) {
            $transfer = StockTransfer::where('id', $transferId)->lockForUpdate()->firstOrFail();

            if (!$transfer->isDispatchable()) {
                throw new InvalidArgumentException("Transfer #{$transfer->transfer_number} belum disetujui untuk dikirim (Status: {$transfer->status}).");
            }

            foreach ($transfer->items as $item) {
                // Deduct from source warehouse
                $product = Product::find($item->product_id);
                $unitCost = $product ? (float) $product->cost_price : 0.0;

                $this->stockLedgerService->recordEntry(
                    warehouseId: $transfer->from_warehouse_id,
                    productId: $item->product_id,
                    batchNumber: $item->batch_number,
                    entryType: 'TRANSFER_OUT',
                    referenceType: 'STOCK_TRANSFER',
                    referenceId: $transfer->id,
                    referenceNumber: $transfer->transfer_number,
                    qtyChange: -abs($item->qty_sent),
                    unitCost: $unitCost,
                    notes: "Pengiriman Transfer #{$transfer->transfer_number} menuju Gudang #{$transfer->to_warehouse_id}",
                    userId: $transfer->created_by,
                    expiryDate: $item->expiry_date?->toDateString()
                );
            }

            $transfer->update([
                'status' => 'IN_TRANSIT',
                'driver_name' => $driverName ?? $transfer->driver_name,
                'vehicle_number' => $vehicleNumber ?? $transfer->vehicle_number,
                'sent_date' => Carbon::now(),
                'dispatched_at' => Carbon::now(),
            ]);

            AuditLoggerService::log(
                action: 'STOCK_TRANSFER_DISPATCHED',
                entityType: 'StockTransfer',
                entityId: (string) $transfer->id,
                newValues: ['status' => 'IN_TRANSIT', 'dispatched_at' => Carbon::now()]
            );

            return $transfer;
        });
    }

    /**
     * Receive transfer at destination warehouse:
     * - Inspects received quantities and condition.
     * - Adds stock to destination warehouse and writes immutable ledger entry.
     * - Marks transfer as COMPLETED.
     */
    public function receiveTransfer(
        int $transferId,
        int $receiverId,
        array $receivedItems,
        ?string $receivedNotes = null
    ): StockTransfer {
        return DB::transaction(function () use ($transferId, $receiverId, $receivedItems, $receivedNotes) {
            $transfer = StockTransfer::where('id', $transferId)->lockForUpdate()->firstOrFail();

            if (!$transfer->isReceivable()) {
                throw new InvalidArgumentException("Transfer #{$transfer->transfer_number} tidak dalam status perjalanan / IN_TRANSIT (Status: {$transfer->status}).");
            }

            $hasDiscrepancy = false;

            // Map received items by item id or product_id
            $receivedMap = [];
            foreach ($receivedItems as $itemInput) {
                $itemId = $itemInput['item_id'] ?? null;
                if ($itemId) {
                    $receivedMap[$itemId] = $itemInput;
                }
            }

            foreach ($transfer->items as $transferItem) {
                $input = $receivedMap[$transferItem->id] ?? null;
                $qtyReceived = $input ? (int) $input['qty_received'] : $transferItem->qty_sent;
                $condition = $input['condition_status'] ?? 'GOOD';
                $discrepancy = $qtyReceived - $transferItem->qty_sent;

                if ($discrepancy !== 0 || $condition === 'DAMAGED') {
                    $hasDiscrepancy = true;
                }

                $transferItem->update([
                    'qty_received' => $qtyReceived,
                    'discrepancy_qty' => $discrepancy,
                    'condition_status' => $condition,
                ]);

                // Increment stock in destination warehouse for received quantity
                if ($qtyReceived > 0) {
                    $product = Product::find($transferItem->product_id);
                    $unitCost = $product ? (float) $product->cost_price : 0.0;

                    $this->stockLedgerService->recordEntry(
                        warehouseId: $transfer->to_warehouse_id,
                        productId: $transferItem->product_id,
                        batchNumber: $transferItem->batch_number,
                        entryType: 'TRANSFER_IN',
                        referenceType: 'STOCK_TRANSFER',
                        referenceId: $transfer->id,
                        referenceNumber: $transfer->transfer_number,
                        qtyChange: $qtyReceived,
                        unitCost: $unitCost,
                        notes: "Penerimaan Transfer #{$transfer->transfer_number} dari Gudang #{$transfer->from_warehouse_id}",
                        userId: $receiverId,
                        expiryDate: $transferItem->expiry_date?->toDateString()
                    );
                }
            }

            $status = $hasDiscrepancy ? 'RECEIVED_PARTIAL' : 'COMPLETED';

            $transfer->update([
                'status' => $status,
                'received_by' => $receiverId,
                'received_date' => Carbon::now(),
                'received_notes' => $receivedNotes,
            ]);

            AuditLoggerService::log(
                action: 'STOCK_TRANSFER_RECEIVED',
                entityType: 'StockTransfer',
                entityId: (string) $transfer->id,
                newValues: ['status' => $status, 'received_by' => $receiverId]
            );

            return $transfer;
        });
    }

    /**
     * Reject transfer request with mandatory reason.
     */
    public function rejectTransfer(int $transferId, int $approverId, string $reason): StockTransfer
    {
        $transfer = StockTransfer::findOrFail($transferId);

        if (!in_array($transfer->status, ['DRAFT', 'SUBMITTED', 'PENDING_APPROVAL'])) {
            throw new InvalidArgumentException("Transfer yang sudah dikirim atau selesai tidak dapat ditolak.");
        }

        $transfer->update([
            'status' => 'REJECTED',
            'rejection_reason' => $reason,
            'approved_by' => $approverId,
            'approved_at' => Carbon::now(),
        ]);

        AuditLoggerService::log(
            action: 'STOCK_TRANSFER_REJECTED',
            entityType: 'StockTransfer',
            entityId: (string) $transfer->id,
            newValues: ['status' => 'REJECTED', 'reason' => $reason]
        );

        return $transfer;
    }

    protected function generateTransferNumber(int $fromWarehouseId): string
    {
        $period = Carbon::now()->format('Ym');
        $random = strtoupper(substr(md5(uniqid((string) $fromWarehouseId, true)), 0, 5));
        return "TRF-{$period}-{$random}";
    }
}

