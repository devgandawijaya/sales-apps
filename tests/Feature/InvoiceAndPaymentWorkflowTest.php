<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Sales\Models\DeliveryOrder;
use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\Payment;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Services\GenerateInvoiceService;
use App\Domain\Sales\Services\RecordPaymentService;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoiceAndPaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Warehouse $warehouse;
    protected Customer $customer;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-FIN',
            'name' => 'PT Keuangan Distribusi Prima',
            'subdomain' => 'fin-test',
            'status' => 'active',
            'plan_tier' => 'enterprise',
        ]);

        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-FIN-01',
            'name' => 'Cabang Finance Jakarta',
            'type' => 'BRANCH',
            'address' => 'Jl. Thamrin No. 10',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
        ]);

        $this->warehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-FIN-01',
            'name' => 'Gudang Finance DC',
            'type' => 'CENTRAL_DC',
            'is_active' => true,
        ]);

        $this->customer = Customer::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'CUST-FIN-01',
            'name' => 'Toko Sembako Berkah Finance',
            'channel' => 'GT',
            'sub_channel' => 'GROSIR',
            'address' => 'Jl. Thamrin No. 100',
            'city' => 'Jakarta Pusat',
            'term_of_payment' => 14,
            'credit_limit' => 50000000.00,
            'current_credit_used' => 30000000.00,
            'is_active' => true,
        ]);

        $this->user = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'role_title' => 'FINANCE_HO',
            'name' => 'Sari Finance',
            'email' => 'sari@finance.co.id',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_can_generate_invoice_from_delivery_order_with_accurate_due_date_and_nsfp(): void
    {
        $salesOrder = SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-FIN-202610-0010',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'GT',
            'status' => 'DELIVERED',
            'subtotal' => 10000000.00,
            'tax_amount' => 1100000.00,
            'grand_total' => 11100000.00,
        ]);

        $deliveryOrder = DeliveryOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'sales_order_id' => $salesOrder->id,
            'do_number' => 'DO-FIN-202610-0010',
            'delivery_date' => Carbon::now()->toDateString(),
            'status' => 'DELIVERED',
        ]);

        $service = app(GenerateInvoiceService::class);
        $invoice = $service->execute($deliveryOrder->id);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
        $this->assertStringStartsWith('010.000-26.', $invoice->tax_invoice_number);
        $this->assertEquals(11100000.00, $invoice->grand_total);
        $this->assertEquals(11100000.00, $invoice->balance_due);
        $this->assertEquals(0.00, $invoice->paid_amount);
        $this->assertEquals('UNPAID', $invoice->status);

        // Term of payment is 14 days
        $expectedDueDate = Carbon::now()->addDays(14)->toDateString();
        $this->assertEquals($expectedDueDate, $invoice->due_date->toDateString());
    }

    public function test_recording_payment_allocates_to_invoices_and_restores_customer_credit(): void
    {
        $salesOrder = SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-PAY-001',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'GT',
            'status' => 'DELIVERED',
            'grand_total' => 15000000.00,
        ]);

        // Create 2 open invoices totaling Rp 15,000,000
        $inv1 = Invoice::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'sales_order_id' => $salesOrder->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => Carbon::now()->subDays(10)->toDateString(),
            'due_date' => Carbon::now()->addDays(4)->toDateString(),
            'status' => 'UNPAID',
            'subtotal' => 9000000.00,
            'tax_amount' => 1000000.00,
            'grand_total' => 10000000.00,
            'paid_amount' => 0.00,
            'balance_due' => 10000000.00,
        ]);

        $inv2 = Invoice::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'sales_order_id' => $salesOrder->id,
            'invoice_number' => 'INV-TEST-002',
            'invoice_date' => Carbon::now()->subDays(5)->toDateString(),
            'due_date' => Carbon::now()->addDays(9)->toDateString(),
            'status' => 'UNPAID',
            'subtotal' => 4500000.00,
            'tax_amount' => 500000.00,
            'grand_total' => 5000000.00,
            'paid_amount' => 0.00,
            'balance_due' => 5000000.00,
        ]);

        $initialCreditUsed = (float) $this->customer->current_credit_used; // 30,000,000

        // Pay Rp 12,000,000 via Bank Transfer
        // Should fully pay inv1 (10,000,000) and partially pay inv2 (2,000,000, balance remaining 3,000,000)
        $paymentService = app(RecordPaymentService::class);
        $payment = $paymentService->execute(
            branchId: $this->branch->id,
            customerId: $this->customer->id,
            amount: 12000000.00,
            paymentMethod: 'BANK_TRANSFER',
            bankName: 'BCA',
            referenceNoGiro: 'TRF-BCA-88992',
            userId: $this->user->id
        );

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals(12000000.00, $payment->amount);
        $this->assertCount(2, $payment->allocations);

        // Check inv1 (fully paid)
        $inv1->refresh();
        $this->assertEquals(10000000.00, $inv1->paid_amount);
        $this->assertEquals(0.00, $inv1->balance_due);
        $this->assertEquals('PAID', $inv1->status);

        // Check inv2 (partial)
        $inv2->refresh();
        $this->assertEquals(2000000.00, $inv2->paid_amount);
        $this->assertEquals(3000000.00, $inv2->balance_due);
        $this->assertEquals('PARTIAL', $inv2->status);

        // Customer's credit limit exposure should be reduced by Rp 12,000,000
        $this->customer->refresh();
        $expectedCreditUsed = $initialCreditUsed - 12000000.00; // 18,000,000
        $this->assertEquals($expectedCreditUsed, (float)$this->customer->current_credit_used);
    }
}
