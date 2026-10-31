<?php

namespace App\Domain\Sales\Services;

use App\Domain\Customer\Models\Customer;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Sales\Events\SalesOrderApprovedEvent;
use App\Domain\Sales\Exceptions\CreditLimitExceededException;
use App\Domain\Sales\Exceptions\CustomerInactiveException;
use App\Domain\Sales\Exceptions\InsufficientStockException;
use App\Domain\Sales\Exceptions\OverdueInvoiceBlockedException;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Models\SalesOrderApprovalHistory;
use App\Domain\Security\Services\AuditLoggerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Enterprise Service for validating and approving / rejecting Sales Orders (SO).
 */
class ApproveSalesOrderService
{
    public const MAX_OVERDUE_GRACE_DAYS = 7; // Toleransi maksimal keterlambatan faktur

    /**
     * Execute Sales Order approval atomically.
     *
     * @param int $salesOrderId
     * @param int $approverUserId
     * @param string|null $approvalNotes
     * @return SalesOrder
     */
    public function execute(int $salesOrderId, int $approverUserId, ?string $approvalNotes = null, bool $forceOverride = false): SalesOrder
    {
        return DB::transaction(function () use ($salesOrderId, $approverUserId, $approvalNotes, $forceOverride) {
            // -----------------------------------------------------------------
            // 1. Lock and validate Sales Order
            // -----------------------------------------------------------------
            /** @var SalesOrder $order */
            $order = SalesOrder::where('id', $salesOrderId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($order->status, ['PENDING_APPROVAL', 'DRAFT'])) {
                throw new InvalidArgumentException("Hanya pesanan yang berstatus Draf atau Menunggu Persetujuan yang dapat disetujui (Status saat ini: {$order->status}).");
            }

            // -----------------------------------------------------------------
            // 2. Lock and validate Customer & Credit Limits
            // -----------------------------------------------------------------
            /** @var Customer $customer */
            $customer = Customer::where('id', $order->customer_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$customer->is_active) {
                throw new CustomerInactiveException($customer->code);
            }

            // -----------------------------------------------------------------
            // 3. Validation: Overdue Invoices Beyond Grace Period
            // -----------------------------------------------------------------
            $overdueThreshold = Carbon::now()->subDays(self::MAX_OVERDUE_GRACE_DAYS)->toDateString();

            $blockingInvoice = DB::table('invoices')
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['UNPAID', 'OVERDUE', 'PARTIAL'])
                ->where('due_date', '<', $overdueThreshold)
                ->where('balance_due', '>', 0)
                ->orderBy('due_date', 'asc')
                ->first();

            if ($blockingInvoice && !$forceOverride) {
                $daysOverdue = Carbon::parse($blockingInvoice->due_date)->diffInDays(Carbon::now());
                throw new OverdueInvoiceBlockedException(
                    customerName: $customer->name,
                    overdueInvoiceNumber: $blockingInvoice->invoice_number,
                    overdueDays: (int)$daysOverdue,
                    outstandingAmount: (float)$blockingInvoice->balance_due
                );
            }

            // -----------------------------------------------------------------
            // 4. Validation: Credit Exposure Check (for DRAFT orders needing new credit allocation)
            // -----------------------------------------------------------------
            $isCredit = strtoupper((string)$customer->payment_type) === 'CREDIT' || $customer->term_of_payment > 0;
            $wasPendingApproval = ($order->status === 'PENDING_APPROVAL');

            // If order was DRAFT (not already allocated in PENDING_APPROVAL), check and increment credit
            if (!$wasPendingApproval && $isCredit && !$forceOverride) {
                $newProjectedCredit = (float)$customer->current_credit_used + (float)$order->grand_total;
                $creditLimit = (float)$customer->credit_limit;

                if ($newProjectedCredit > $creditLimit) {
                    throw new CreditLimitExceededException(
                        customerName: $customer->name,
                        currentUsed: (float)$customer->current_credit_used,
                        orderAmount: (float)$order->grand_total,
                        creditLimit: $creditLimit
                    );
                }

                $customer->increment('current_credit_used', (float) $order->grand_total);
            }

            // -----------------------------------------------------------------
            // 5. FEFO Stock Allocation & Locking (if not already reserved)
            // -----------------------------------------------------------------
            if (!$wasPendingApproval) {
                foreach ($order->items as $item) {
                    $qtyRemainingToReserve = $item->qty_in_base_uom;

                    $stockBatches = Stock::where('warehouse_id', $order->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->whereRaw('qty_on_hand > qty_reserved')
                        ->where('expiry_date', '>', Carbon::today()->toDateString())
                        ->orderBy('expiry_date', 'asc')
                        ->lockForUpdate()
                        ->get();

                    $totalAvailable = $stockBatches->sum(fn(Stock $stock) => $stock->qty_on_hand - $stock->qty_reserved);

                    if ($totalAvailable < $qtyRemainingToReserve) {
                        throw new InsufficientStockException(
                            productSku: $item->product->sku ?? "PRD-{$item->product_id}",
                            productName: $item->product->name ?? "Product #{$item->product_id}",
                            requestedQty: $qtyRemainingToReserve,
                            availableQty: $totalAvailable
                        );
                    }

                    foreach ($stockBatches as $stockBatch) {
                        if ($qtyRemainingToReserve <= 0) {
                            break;
                        }

                        $batchAvailable = $stockBatch->qty_on_hand - $stockBatch->qty_reserved;
                        $reserveFromBatch = min($qtyRemainingToReserve, $batchAvailable);

                        $stockBatch->increment('qty_reserved', $reserveFromBatch);
                        $qtyRemainingToReserve -= $reserveFromBatch;
                    }
                }
            }

            // -----------------------------------------------------------------
            // 6. Transition SO Status & Record Approval History
            // -----------------------------------------------------------------

            $nowTimestamp = Carbon::now();
            $order->update([
                'status' => 'APPROVED',
                'approved_by' => $approverUserId,
                'approved_at' => $nowTimestamp,
                'approval_level' => max(1, (int) $order->approval_level),
                'notes' => $approvalNotes 
                    ? ($order->notes ? $order->notes . ' | ' . $approvalNotes : $approvalNotes)
                    : $order->notes,
            ]);

            // Record approval history
            SalesOrderApprovalHistory::create([
                'sales_order_id' => $order->id,
                'user_id' => $approverUserId,
                'action' => 'APPROVED',
                'reason' => $approvalNotes ?? 'Sales order disetujui.',
                'created_at' => $nowTimestamp,
            ]);

            AuditLoggerService::log(
                action: 'SALES_ORDER_APPROVED',
                entityType: 'SalesOrder',
                entityId: (string) $order->id,
                newValues: ['status' => 'APPROVED', 'approved_by' => $approverUserId]
            );

            // -----------------------------------------------------------------
            // 7. Event Dispatching
            // -----------------------------------------------------------------
            SalesOrderApprovedEvent::dispatch(
                $order,
                $approverUserId,
                $nowTimestamp->toIso8601String()
            );

            return $order->fresh(['items', 'customer', 'branch', 'warehouse']);
        });
    }

    /**
     * Reject a Sales Order with mandatory reason.
     */
    public function reject(int $salesOrderId, int $approverUserId, string $reason): SalesOrder
    {
        return DB::transaction(function () use ($salesOrderId, $approverUserId, $reason) {
            /** @var SalesOrder $order */
            $order = SalesOrder::where('id', $salesOrderId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($order->status, ['PENDING_APPROVAL', 'DRAFT'])) {
                throw new InvalidArgumentException("Hanya pesanan yang sedang menunggu persetujuan yang dapat ditolak.");
            }

            // Release customer credit and stock reservation if previously reserved
            if ($order->status === 'PENDING_APPROVAL') {
                $customer = Customer::where('id', $order->customer_id)->lockForUpdate()->first();
                if ($customer && (float) $customer->current_credit_used >= (float) $order->grand_total) {
                    $customer->decrement('current_credit_used', (float) $order->grand_total);
                }

                // Release reserved stock batches
                foreach ($order->items as $item) {
                    $qtyToRelease = $item->qty_in_base_uom;
                    $stockBatches = Stock::where('warehouse_id', $order->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->where('qty_reserved', '>', 0)
                        ->orderBy('expiry_date', 'desc')
                        ->lockForUpdate()
                        ->get();

                    foreach ($stockBatches as $batch) {
                        if ($qtyToRelease <= 0) break;
                        $rel = min($qtyToRelease, $batch->qty_reserved);
                        $batch->decrement('qty_reserved', $rel);
                        $qtyToRelease -= $rel;
                    }
                }
            }

            $order->update([
                'status' => 'REJECTED',
                'rejection_reason' => $reason,
                'approved_by' => $approverUserId,
                'approved_at' => Carbon::now(),
            ]);

            SalesOrderApprovalHistory::create([
                'sales_order_id' => $order->id,
                'user_id' => $approverUserId,
                'action' => 'REJECTED',
                'reason' => $reason,
                'created_at' => Carbon::now(),
            ]);

            AuditLoggerService::log(
                action: 'SALES_ORDER_REJECTED',
                entityType: 'SalesOrder',
                entityId: (string) $order->id,
                newValues: ['status' => 'REJECTED', 'reason' => $reason]
            );

            return $order;
        });
    }
}
