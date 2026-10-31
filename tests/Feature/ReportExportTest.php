<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\Inventory\Models\StockLedgerEntry;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Warehouse $warehouse;
    protected User $user;
    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-EXP',
            'name' => 'PT Ekspor Data Distribusi',
            'subdomain' => 'export-test',
            'status' => 'active',
            'plan_tier' => 'enterprise',
        ]);

        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-EXP-01',
            'name' => 'Cabang Ekspor Jakarta',
            'type' => 'BRANCH',
            'address' => 'Jl. Merdeka No. 1',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
        ]);

        $this->warehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-EXP-01',
            'name' => 'Gudang Ekspor DC',
            'type' => 'CENTRAL_DC',
            'is_active' => true,
        ]);

        $this->customer = Customer::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'CUST-EXP-01',
            'name' => 'Toko Ekspor Sukses',
            'channel' => 'GT',
            'sub_channel' => 'GROSIR',
            'address' => 'Jl. Merdeka No. 50',
            'city' => 'Jakarta Pusat',
            'term_of_payment' => 30,
            'credit_limit' => 10000000.00,
            'current_credit_used' => 2000000.00,
            'is_active' => true,
        ]);

        $this->user = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'role_title' => 'SUPERADMIN',
            'name' => 'Superadmin Export',
            'email' => 'admin@export.co.id',
            'password' => bcrypt('password123'),
        ]);

        $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
            'code' => 'CAT-EXP',
            'name' => 'Minuman Ekspor',
            'slug' => 'minuman-ekspor',
            'is_active' => true,
            'created_at' => now(),
        ]);

        $this->product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $categoryId,
            'sku' => 'EXP-001',
            'name' => 'Produk Ekspor Minuman',
            'brand' => 'MinumBrand',
            'base_uom' => 'Pcs',
            'is_active' => true,
        ]);
    }

    public function test_can_export_ar_aging_csv(): void
    {
        $salesOrder = SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-EXP-AR-001',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'GT',
            'status' => 'DELIVERED',
            'grand_total' => 1110000.00,
        ]);

        Invoice::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'sales_order_id' => $salesOrder->id,
            'invoice_number' => 'INV-EXP-001',
            'invoice_date' => Carbon::now()->subDays(15)->toDateString(),
            'due_date' => Carbon::now()->addDays(15)->toDateString(),
            'status' => 'UNPAID',
            'subtotal' => 1000000.00,
            'tax_amount' => 110000.00,
            'grand_total' => 1110000.00,
            'paid_amount' => 0.00,
            'balance_due' => 1110000.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('finance.ar-aging.export'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'Laporan_AR_Aging_'));
    }

    public function test_can_export_stock_ledger_csv(): void
    {
        StockLedgerEntry::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-EXP-001',
            'entry_type' => 'IN',
            'reference_type' => 'GOODS_RECEIPT',
            'reference_id' => 101,
            'reference_number' => 'GRN-001',
            'qty_change' => 100,
            'qty_before' => 0,
            'qty_after' => 100,
            'unit_cost' => 15000.00,
            'total_cost' => 1500000.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('inventory.ledger.export'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'Kartu_Stok_Ledger_'));
    }

    public function test_can_export_sales_orders_csv(): void
    {
        SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-EXP-001',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'GT',
            'status' => 'DELIVERED',
            'subtotal' => 2000000.00,
            'tax_amount' => 220000.00,
            'grand_total' => 2220000.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.export'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'Rekap_Sales_Orders_'));
    }
}
