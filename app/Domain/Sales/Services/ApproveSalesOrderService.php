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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Enterprise Service for validating and approving Sales Orders (SO).
 * Encapsulates:
 * 1. Pessimistic row locking on Sales Order, Customer, and Inventory Batches.
 * 2. Overdue Invoice Check: Blocks approval if customer has overdue invoices beyond grace period.
 * 3. Credit Limit Check: Verifies combined exposure (outstanding AR + new SO amount).
 * 4. FEFO Stock Reservation: Allocates batches with nearest expiry dates and updates qty_reserved.
 * 5. State Transition & Event Dispatching for background pick-pack-ship orchestration.
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
     * @throws CustomerInactiveException
     * @throws OverdueInvoiceBlockedException
     * @throws CreditLimitExceededException
     * @throws InsufficientStockException
     * @throws \Throwable
     */
    public function execute(int $salesOrderId, int $approverUserId, ?string $approvalNotes = null): SalesOrder
    {
        return DB::transaction(function () use ($salesOrderId, $approverUserId, $approvalNotes) {
            // -----------------------------------------------------------------
            // 1. Lock and validate Sales Order
            // -----------------------------------------------------------------
            /** @var SalesOrder $order */
            $order = SalesOrder::where('id', $salesOrderId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($order->status, ['PENDING_APPROVAL', 'DRAFT'])) {
                throw new InvalidArgumentException("Hanya Sales Order berstatus 'PENDING_APPROVAL' atau 'DRAFT' yang dapat disetujui. Status saat ini: {$order->status}");
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

            if ($blockingInvoice) {
                $daysOverdue = Carbon::parse($blockingInvoice->due_date)->diffInDays(Carbon::now());
                throw new OverdueInvoiceBlockedException(
                    customerName: $customer->name,
                    overdueInvoiceNumber: $blockingInvoice->invoice_number,
                    overdueDays: (int) $daysOverdue,
                    outstandingAmount: (float) $blockingInvoice->balance_due
                );
            }

            // -----------------------------------------------------------------
            // 4. Validation: Credit Limit Exposure Check (Unless COD)
            // -----------------------------------------------------------------
            $isCredit = strtoupper($customer->payment_type) === 'CREDIT' || $customer->term_of_payment > 0;
            if ($isCredit) {
                $projectedCreditExposure = (float) $customer->current_credit_used + (float) $order->grand_total;
                $creditLimit = (float) $customer->credit_limit;

                if ($projectedCreditExposure > $creditLimit) {
                    throw new CreditLimitExceededException(
                        customerName: $customer->name,
                        currentUsed: (float) $customer->current_credit_used,
                        orderAmount: (float) $order->grand_total,
                        creditLimit: $creditLimit
                    );
                }
            }

            // -----------------------------------------------------------------
            // 5. FEFO Stock Reservation with Pessimistic Row Locking
            // -----------------------------------------------------------------
            $orderItems = DB::table('sales_order_items')
                ->where('sales_order_id', $order->id)
                ->get();

            if ($orderItems->isEmpty()) {
                throw new InvalidArgumentException("Sales Order tidak memiliki rincian barang.");
            }

            foreach ($orderItems as $item) {
                $qtyNeeded = $item->qty_in_base_uom;

                // Fetch available stocks ordered by FEFO (Earliest Expiry First)
                $batches = Stock::where('warehouse_id', $order->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->where('expiry_date', '>', Carbon::today()->toDateString()) // Exclude already expired goods
                    ->orderBy('expiry_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                $totalAvailableInWh = $batches->sum(fn ($b) => $b->qty_on_hand - $b->qty_reserved);

                if ($totalAvailableInWh < $qtyNeeded) {
                    $product = DB::table('products')->where('id', $item->product_id)->first();
                    throw new InsufficientStockException(
                        productName: $product?->name ?? 'SKU #' . $item->product_id,
                        qtyRequested: (int) $qtyNeeded,
                        qtyAvailable: (int) max(0, $totalAvailableInWh)
                    );
                }

                // Reserve quantities across FEFO batches
                $qtyRemainingToReserve = $qtyNeeded;
                foreach ($batches as $batch) {
                    if ($qtyRemainingToReserve <= 0) {
                        break;
                    }

                    $batchAvailable = $batch->qty_on_hand - $batch->qty_reserved;
                    if ($batchAvailable <= 0) {
                        continue;
                    }

                    $reserveFromBatch = min($batchAvailable, $qtyRemainingToReserve);
                    $batch->qty_reserved += $reserveFromBatch;
                    $batch->save();

                    $qtyRemainingToReserve -= $reserveFromBatch;
                }
            }

            // -----------------------------------------------------------------
            // 6. Update Customer Credit Used & Transition SO Status
            // -----------------------------------------------------------------
            if ($isCredit) {
                $customer->increment('current_credit_used', (float) $order->grand_total);
            }

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

            // -----------------------------------------------------------------
            // 7. Event Dispatching for Pick/Pack/Dispatch Orchestration
            // -----------------------------------------------------------------
            SalesOrderApprovedEvent::dispatch(
                $order,
                $approverUserId,
                $nowTimestamp->toIso8601String()
            );

            return $order->fresh(['items', 'customer', 'branch', 'warehouse']);
        });
    }
}

