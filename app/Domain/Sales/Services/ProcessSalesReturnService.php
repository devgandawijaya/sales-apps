<?php

namespace App\Domain\Sales\Services;

use App\Domain\Customer\Models\Customer;
use App\Domain\Inventory\Services\StockLedgerService;
use App\Domain\Sales\Models\SalesReturn;
use App\Domain\Sales\Models\SalesReturnItem;
use App\Domain\Security\Services\AuditLoggerService;
use App\Domain\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessSalesReturnService
{
    public function __construct(
        protected StockLedgerService $stockLedgerService
    ) {}

    /**
     * Create a Sales Return request.
     *
     * @param array $items Array of [product_id, product_unit_id, batch_number, qty, unit_price, reason]
     */
    public function createReturn(
        int $branchId,
        int $customerId,
        int $warehouseId,
        string $returnType,
        array $items,
        ?int $invoiceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): SalesReturn {
        if (!in_array($returnType, ['GOOD_STOCK', 'BAD_STOCK_EXPIRED', 'BAD_STOCK_DAMAGED'])) {
            throw new InvalidArgumentException("Tipe retur tidak valid. Pilihan: GOOD_STOCK, BAD_STOCK_EXPIRED, BAD_STOCK_DAMAGED.");
        }

        if (empty($items)) {
            throw new InvalidArgumentException("Daftar barang yang diretur tidak boleh kosong.");
        }

        return DB::transaction(function () use (
            $branchId,
            $customerId,
            $warehouseId,
            $returnType,
            $items,
            $invoiceId,
            $notes,
            $userId
        ) {
            $tenantId = TenantContext::getTenantId();
            $returnNumber = $this->generateReturnNumber($branchId);

            $totalAmount = 0.0;
            foreach ($items as $item) {
                $subtotal = (float)$item['qty'] * (float)$item['unit_price'];
                $totalAmount += $subtotal;
            }

            $salesReturn = SalesReturn::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'warehouse_id' => $warehouseId,
                'invoice_id' => $invoiceId,
                'return_number' => $returnNumber,
                'return_date' => Carbon::now()->toDateString(),
                'return_type' => $returnType,
                'total_amount' => $totalAmount,
                'status' => 'PENDING',
                'notes' => $notes,
            ]);

            foreach ($items as $item) {
                $subtotal = (float)$item['qty'] * (float)$item['unit_price'];
                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'product_id' => $item['product_id'],
                    'product_unit_id' => $item['product_unit_id'],
                    'batch_number' => $item['batch_number'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            AuditLoggerService::log(
                action: 'SALES_RETURN_CREATED',
                entityType: 'SalesReturn',
                entityId: (string) $salesReturn->id,
                newValues: [
                    'return_number' => $salesReturn->return_number,
                    'return_type' => $returnType,
                    'total_amount' => $totalAmount,
                ]
            );

            return $salesReturn->fresh(['customer', 'branch', 'warehouse', 'items.product']);
        });
    }

    /**
     * Approve and post Sales Return into inventory and credit note.
     */
    public function approveReturn(int $returnId, int $approverId): SalesReturn
    {
        return DB::transaction(function () use ($returnId, $approverId) {
            /** @var SalesReturn $salesReturn */
            $salesReturn = SalesReturn::with(['items.product', 'customer'])
                ->where('id', $returnId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($salesReturn->status !== 'PENDING') {
                throw new InvalidArgumentException("Hanya retur berstatus 'PENDING' yang dapat disetujui. Status saat ini: {$salesReturn->status}");
            }

            // Post inventory back to warehouse
            $entryType = ($salesReturn->return_type === 'GOOD_STOCK') ? 'RETURN_GOOD_STOCK' : 'RETURN_BAD_STOCK';

            foreach ($salesReturn->items as $item) {
                $this->stockLedgerService->recordEntry(
                    warehouseId: $salesReturn->warehouse_id,
                    productId: $item->product_id,
                    batchNumber: $item->batch_number,
                    entryType: 'IN',
                    referenceType: 'SALES_RETURN',
                    referenceId: $salesReturn->id,
                    referenceNumber: $salesReturn->return_number,
                    qtyChange: $item->qty,
                    unitCost: (float) $item->unit_price,
                    notes: "Retur barang {$salesReturn->return_type} No. {$salesReturn->return_number}",
                    userId: $approverId
                );
            }

            // Restore customer's credit limit / deduct credit exposure
            $customer = $salesReturn->customer;
            if ($customer && (float)$customer->current_credit_used > 0) {
                $deductAmount = min((float)$customer->current_credit_used, (float)$salesReturn->total_amount);
                $customer->decrement('current_credit_used', $deductAmount);
            }

            $salesReturn->update([
                'status' => 'APPROVED',
                'approved_by' => $approverId,
            ]);

            AuditLoggerService::log(
                action: 'SALES_RETURN_APPROVED',
                entityType: 'SalesReturn',
                entityId: (string) $salesReturn->id,
                newValues: [
                    'status' => 'APPROVED',
                    'approved_by' => $approverId,
                ]
            );

            return $salesReturn->fresh(['customer', 'branch', 'warehouse', 'items.product', 'approver']);
        });
    }

    protected function generateReturnNumber(int $branchId): string
    {
        $prefix = 'SR-' . date('Ym') . '-';
        $count = SalesReturn::withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->where('return_number', 'like', $prefix . '%')
            ->count() + 1;

        return $prefix . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
    }
}

