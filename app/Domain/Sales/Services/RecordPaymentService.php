<?php

namespace App\Domain\Sales\Services;

use App\Domain\Customer\Models\Customer;
use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\Payment;
use App\Domain\Sales\Models\PaymentAllocation;
use App\Domain\Security\Services\AuditLoggerService;
use App\Domain\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordPaymentService
{
    /**
     * Record a customer payment and allocate across outstanding invoices.
     */
    public function execute(
        int $branchId,
        int $customerId,
        float $amount,
        string $paymentMethod = 'BANK_TRANSFER',
        ?string $bankName = null,
        ?string $referenceNoGiro = null,
        ?string $giroDueDate = null,
        ?string $notes = null,
        ?int $userId = null,
        ?array $specificInvoiceIds = null
    ): Payment {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Nominal pembayaran harus lebih besar dari nol.");
        }

        return DB::transaction(function () use (
            $branchId,
            $customerId,
            $amount,
            $paymentMethod,
            $bankName,
            $referenceNoGiro,
            $giroDueDate,
            $notes,
            $userId,
            $specificInvoiceIds
        ) {
            $tenantId = TenantContext::getTenantId();

            /** @var Customer $customer */
            $customer = Customer::where('id', $customerId)->lockForUpdate()->firstOrFail();

            $paymentNumber = $this->generatePaymentNumber($branchId);

            $payment = Payment::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'payment_number' => $paymentNumber,
                'payment_date' => Carbon::now()->toDateString(),
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                'bank_name' => $bankName,
                'reference_no_giro' => $referenceNoGiro,
                'giro_due_date' => $giroDueDate,
                'giro_status' => ($paymentMethod === 'GIRO') ? 'PENDING' : 'CLEARED',
                'notes' => $notes,
                'created_by' => $userId ?? 1,
            ]);

            // Query outstanding invoices
            $invQuery = Invoice::where('customer_id', $customerId)
                ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
                ->where('balance_due', '>', 0)
                ->orderBy('due_date', 'asc');

            if (!empty($specificInvoiceIds)) {
                $invQuery->whereIn('id', $specificInvoiceIds);
            }

            $invoices = $invQuery->lockForUpdate()->get();

            $remainingAmount = $amount;
            $totalAllocated = 0.0;

            foreach ($invoices as $inv) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $balance = (float) $inv->balance_due;
                $allocation = min($remainingAmount, $balance);

                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $inv->id,
                    'allocated_amount' => $allocation,
                ]);

                $newPaid = (float) $inv->paid_amount + $allocation;
                $newBalance = max(0, $balance - $allocation);
                $newStatus = ($newBalance <= 0) ? 'PAID' : 'PARTIAL';

                $inv->update([
                    'paid_amount' => $newPaid,
                    'balance_due' => $newBalance,
                    'status' => $newStatus,
                ]);

                $remainingAmount -= $allocation;
                $totalAllocated += $allocation;
            }

            // Restore customer's credit limit
            if ($totalAllocated > 0) {
                $currentCredit = (float) $customer->current_credit_used;
                $decrementAmount = min($currentCredit, $totalAllocated);
                if ($decrementAmount > 0) {
                    $customer->decrement('current_credit_used', $decrementAmount);
                }
            }

            AuditLoggerService::log(
                action: 'PAYMENT_RECORDED',
                entityType: 'Payment',
                entityId: (string) $payment->id,
                newValues: [
                    'payment_number' => $payment->payment_number,
                    'amount' => $payment->amount,
                    'customer_id' => $payment->customer_id,
                    'allocated_amount' => $totalAllocated,
                ]
            );

            return $payment->fresh(['customer', 'branch', 'allocations.invoice']);
        });
    }

    protected function generatePaymentNumber(int $branchId): string
    {
        $prefix = 'PAY-' . date('Ym') . '-';
        $count = Payment::withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->where('payment_number', 'like', $prefix . '%')
            ->count() + 1;

        return $prefix . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
    }
}

