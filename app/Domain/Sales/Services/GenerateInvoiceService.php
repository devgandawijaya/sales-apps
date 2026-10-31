<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Models\DeliveryOrder;
use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Security\Services\AuditLoggerService;
use App\Domain\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GenerateInvoiceService
{
    /**
     * Generate Invoice from a Delivery Order or Sales Order.
     */
    public function execute(
        int $deliveryOrderId,
        ?string $customInvoiceDate = null,
        ?string $notes = null
    ): Invoice {
        return DB::transaction(function () use ($deliveryOrderId, $customInvoiceDate, $notes) {
            $tenantId = TenantContext::getTenantId();

            /** @var DeliveryOrder $deliveryOrder */
            $deliveryOrder = DeliveryOrder::with(['salesOrder.customer', 'branch'])
                ->where('id', $deliveryOrderId)
                ->firstOrFail();

            $order = $deliveryOrder->salesOrder;
            if (!$order) {
                throw new InvalidArgumentException("Surat Jalan tidak memiliki referensi Sales Order valid.");
            }

            // Check if already invoiced
            $existingInvoice = Invoice::where('delivery_order_id', $deliveryOrderId)->first();
            if ($existingInvoice) {
                return $existingInvoice;
            }

            $customer = $order->customer;
            $invoiceDate = $customInvoiceDate ? Carbon::parse($customInvoiceDate) : Carbon::now();
            $termDays = $customer ? (int) $customer->term_of_payment : 0;
            $dueDate = $invoiceDate->copy()->addDays($termDays)->toDateString();

            $invoiceNumber = $this->generateInvoiceNumber($deliveryOrder->branch_id);
            $taxInvoiceNumber = $this->generateTaxInvoiceNumber();

            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'branch_id' => $deliveryOrder->branch_id,
                'customer_id' => $order->customer_id,
                'sales_order_id' => $order->id,
                'delivery_order_id' => $deliveryOrder->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate->toDateString(),
                'due_date' => $dueDate,
                'tax_invoice_number' => $taxInvoiceNumber,
                'tax_invoice_date' => $invoiceDate->toDateString(),
                'status' => 'UNPAID',
                'subtotal' => (float) $order->subtotal - (float) $order->discount_on_invoice,
                'tax_amount' => (float) $order->tax_amount,
                'grand_total' => (float) $order->grand_total,
                'paid_amount' => 0.00,
                'balance_due' => (float) $order->grand_total,
            ]);

            AuditLoggerService::log(
                action: 'INVOICE_GENERATED',
                entityType: 'Invoice',
                entityId: (string) $invoice->id,
                newValues: [
                    'invoice_number' => $invoice->invoice_number,
                    'grand_total' => $invoice->grand_total,
                    'customer_id' => $invoice->customer_id,
                ]
            );

            return $invoice->fresh(['customer', 'branch', 'salesOrder', 'deliveryOrder']);
        });
    }

    protected function generateInvoiceNumber(int $branchId): string
    {
        $prefix = 'INV-' . date('Ym') . '-';
        $count = Invoice::withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->where('invoice_number', 'like', $prefix . '%')
            ->count() + 1;

        return $prefix . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
    }

    protected function generateTaxInvoiceNumber(): string
    {
        // Indonesian Standard e-Faktur NSFP format: 010.000-26.XXXXXXXX
        $randSuffix = str_pad((string) mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        return '010.000-26.' . $randSuffix;
    }
}

