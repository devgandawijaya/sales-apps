<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Sales\Exceptions\OverdueInvoiceBlockedException;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Models\SalesOrderItem;
use App\Domain\Sales\Models\SalesOrderApprovalHistory;
use App\Domain\Sales\Services\ApproveSalesOrderService;
use App\Domain\Security\Models\AuditLog;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SalesApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Warehouse $warehouse;
    protected User $salesman;
    protected User $branchManager;
    protected Customer $customer;
    protected Product $product;
    protected ProductUnit $unit;
    protected Stock $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-APPR',
            'name' => 'PT Distribusi Approval Mandiri',
            'subdomain' => 'appr-test',
            'status' => 'active',
            'plan_tier' => 'business',
        ]);

        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-02',
            'name' => 'Cabang Surabaya Barat',
            'type' => 'BRANCH',
            'address' => 'Jl. Mayjen Sungkono',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
        ]);

        $this->warehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-SBY',
            'name' => 'Gudang Rungkut Surabaya',
            'type' => 'CENTRAL_DC',
            'is_active' => true,
        ]);

        $this->salesman = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'role_title' => 'SALESMAN_GT',
            'name' => 'Anton Sales',
            'email' => 'anton@distribusimandiri.co.id',
            'password' => bcrypt('password123'),
        ]);

        $this->branchManager = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'role_title' => 'BRANCH_MANAGER',
            'name' => 'Hendra BM',
            'email' => 'hendra.bm@distribusimandiri.co.id',
            'password' => bcrypt('password123'),
        ]);

        $this->customer = Customer::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'CUST-SBY-01',
            'name' => 'Toko Makmur Sentosa',
            'channel' => 'GT',
            'sub_channel' => 'RETAIL',
            'address' => 'Jl. Kertajaya Indah No. 12',
            'city' => 'Surabaya',
            'payment_type' => 'CREDIT',
            'term_of_payment' => 30,
            'credit_limit' => 50000000.00,
            'current_credit_used' => 10000000.00,
            'is_active' => true,
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'code' => 'CAT-SNACK',
            'name' => 'Snacks & Biscuits',
            'slug' => 'snacks-biscuits',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $categoryId,
            'sku' => 'PRD-BISKUIT-COKLAT',
            'barcode' => '8999876543210',
            'name' => 'Biskuit Coklat Renyah 150g',
            'cost_price' => 5000.00,
            'is_active' => true,
        ]);

        $this->unit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_name' => 'KARTON',
            'conversion_factor' => 20,
            'is_base_unit' => false,
        ]);

        $this->stock = Stock::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-BISKUIT-2026',
            'expiry_date' => Carbon::now()->addMonths(8)->toDateString(),
            'qty_on_hand' => 1000,
            'qty_reserved' => 100, // 100 pcs already reserved for pending SO
        ]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    public function test_approving_sales_order_transitions_status_and_records_history(): void
    {
        $so = SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->salesman->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-202610-0088',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'GT',
            'order_type' => 'TAKING_ORDER',
            'status' => 'PENDING_APPROVAL',
            'payment_term_days' => 30,
            'subtotal' => 1000000.00,
            'discount_on_invoice' => 50000.00,
            'tax_amount' => 104500.00,
            'grand_total' => 1054500.00,
            'approval_level' => 1,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so->id,
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unit->id,
            'conversion_factor' => 20,
            'qty_ordered' => 5,
            'qty_in_base_uom' => 100,
            'unit_price' => 200000.00,
            'gross_amount' => 1000000.00,
            'net_amount' => 950000.00,
        ]);

        $service = app(ApproveSalesOrderService::class);
        $approvedOrder = $service->execute($so->id, $this->branchManager->id, 'Disetujui untuk pengiriman express.');

        $this->assertEquals('APPROVED', $approvedOrder->status);
        $this->assertEquals($this->branchManager->id, $approvedOrder->approved_by);
        $this->assertNotNull($approvedOrder->approved_at);

        // Assert approval history
        $this->assertDatabaseHas('sales_order_approval_histories', [
            'sales_order_id' => $so->id,
            'user_id' => $this->branchManager->id,
            'action' => 'APPROVED',
            'reason' => 'Disetujui untuk pengiriman express.',
        ]);

        // Assert audit log
        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $this->tenant->id,
            'action' => 'SALES_ORDER_APPROVED',
            'entity_id' => (string) $so->id,
        ]);
    }

    public function test_rejecting_sales_order_releases_credit_and_reserved_stock(): void
    {
        // Set initial credit used on customer
        $orderGrandTotal = 2000000.00;
        $this->customer->update(['current_credit_used' => 10000000.00]);

        $so = SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->salesman->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-202610-0089',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'GT',
            'order_type' => 'TAKING_ORDER',
            'status' => 'PENDING_APPROVAL',
            'payment_term_days' => 30,
            'subtotal' => 2000000.00,
            'discount_on_invoice' => 200000.00,
            'tax_amount' => 198000.00,
            'grand_total' => $orderGrandTotal,
            'approval_level' => 1,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so->id,
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unit->id,
            'conversion_factor' => 20,
            'qty_ordered' => 5,
            'qty_in_base_uom' => 100,
            'unit_price' => 400000.00,
            'gross_amount' => 2000000.00,
            'net_amount' => 1800000.00,
        ]);

        $this->stock->update(['qty_reserved' => 100]);

        $service = app(ApproveSalesOrderService::class);
        $rejectedOrder = $service->reject($so->id, $this->branchManager->id, 'Diskon 10% melanggar batas otorisasi cabang.');

        $this->assertEquals('REJECTED', $rejectedOrder->status);
        $this->assertEquals('Diskon 10% melanggar batas otorisasi cabang.', $rejectedOrder->rejection_reason);

        // Verify credit was released
        $this->customer->refresh();
        $this->assertEquals(8000000.00, (float) $this->customer->current_credit_used);

        // Verify stock reservation was released back to 0
        $this->stock->refresh();
        $this->assertEquals(0, $this->stock->qty_reserved);

        // Verify rejection approval history
        $this->assertDatabaseHas('sales_order_approval_histories', [
            'sales_order_id' => $so->id,
            'user_id' => $this->branchManager->id,
            'action' => 'REJECTED',
            'reason' => 'Diskon 10% melanggar batas otorisasi cabang.',
        ]);
    }

    public function test_approving_sales_order_blocks_when_customer_has_overdue_invoices(): void
    {
        $this->expectException(OverdueInvoiceBlockedException::class);

        $priorSo = SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->salesman->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-202609-0001',
            'order_date' => Carbon::now()->subDays(50)->toDateString(),
            'channel' => 'GT',
            'status' => 'INVOICED',
            'grand_total' => 5550000.00,
        ]);

        // Create overdue unpaid invoice (15 days overdue > grace 7 days)
        DB::table('invoices')->insert([
            'tenant_id' => $this->tenant->id,
            'sales_order_id' => $priorSo->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-202609-0012',
            'invoice_date' => Carbon::now()->subDays(45)->toDateString(),
            'due_date' => Carbon::now()->subDays(15)->toDateString(),
            'status' => 'OVERDUE',
            'subtotal' => 5000000.00,
            'tax_amount' => 550000.00,
            'grand_total' => 5550000.00,
            'balance_due' => 5550000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $so = SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->salesman->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-202610-0090',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'GT',
            'status' => 'PENDING_APPROVAL',
            'grand_total' => 1000000.00,
        ]);

        $service = app(ApproveSalesOrderService::class);
        $service->execute($so->id, $this->branchManager->id);
    }
}
