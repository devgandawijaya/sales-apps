<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockLedgerEntry;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Sales\Models\SalesReturn;
use App\Domain\Sales\Services\ProcessSalesReturnService;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SalesReturnWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Warehouse $goodWarehouse;
    protected Warehouse $badWarehouse;
    protected Customer $customer;
    protected User $user;
    protected Product $product;
    protected ProductUnit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-RET',
            'name' => 'PT Retur Distribusi Mandiri',
            'subdomain' => 'retur-test',
            'status' => 'active',
            'plan_tier' => 'enterprise',
        ]);

        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-RET-01',
            'name' => 'Cabang Retur Surabaya',
            'type' => 'BRANCH',
            'address' => 'Jl. Rungkut Industri No. 20',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
        ]);

        $this->goodWarehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-RET-GOOD',
            'name' => 'Gudang Utama Surabaya',
            'type' => 'CENTRAL_DC',
            'is_active' => true,
        ]);

        $this->badWarehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-RET-BAD',
            'name' => 'Gudang Karantina Bad Stock',
            'type' => 'BAD_STOCK',
            'is_active' => true,
        ]);

        $this->customer = Customer::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'CUST-RET-01',
            'name' => 'Toko Barokah Surabaya',
            'channel' => 'GT',
            'sub_channel' => 'WARUNG',
            'address' => 'Jl. Rungkut No. 88',
            'city' => 'Surabaya',
            'term_of_payment' => 7,
            'credit_limit' => 25000000.00,
            'current_credit_used' => 5000000.00,
            'is_active' => true,
        ]);

        $this->user = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'role_title' => 'BRANCH_MANAGER',
            'name' => 'Hendra BM',
            'email' => 'hendra@retur.co.id',
            'password' => bcrypt('password123'),
        ]);

        $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
            'code' => 'CAT-RET',
            'name' => 'Snacks Retur',
            'slug' => 'snacks-retur',
            'is_active' => true,
            'created_at' => now(),
        ]);

        $this->product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $categoryId,
            'sku' => 'SNK-001',
            'name' => 'Wafer Coklat 100g',
            'brand' => 'WaferBrand',
            'base_uom' => 'Pcs',
            'is_active' => true,
        ]);

        $this->unit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_name' => 'Dus',
            'conversion_factor' => 20,
            'is_base_unit' => false,
        ]);

        // Seed stock entry in bad stock warehouse
        Stock::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->badWarehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-WAF-2026-X1',
            'expiry_date' => Carbon::now()->addMonths(2)->toDateString(),
            'qty_on_hand' => 0,
            'qty_reserved' => 0,
        ]);
    }

    public function test_can_create_and_approve_bad_stock_return_to_quarantine_warehouse(): void
    {
        $service = app(ProcessSalesReturnService::class);

        // 1. Create Return
        $return = $service->createReturn(
            branchId: $this->branch->id,
            customerId: $this->customer->id,
            warehouseId: $this->badWarehouse->id,
            returnType: 'BAD_STOCK_DAMAGED',
            items: [
                [
                    'product_id' => $this->product->id,
                    'product_unit_id' => $this->unit->id,
                    'batch_number' => 'LOT-WAF-2026-X1',
                    'qty' => 5,
                    'unit_price' => 150000.00,
                    'reason' => 'Karton basah dan penyok saat pengiriman',
                ]
            ],
            notes: 'Retur 5 dus wafer rusak',
            userId: $this->user->id
        );

        $this->assertInstanceOf(SalesReturn::class, $return);
        $this->assertStringStartsWith('SR-', $return->return_number);
        $this->assertEquals('PENDING', $return->status);
        $this->assertEquals(750000.00, $return->total_amount); // 5 * 150,000

        // 2. Approve Return
        $approvedReturn = $service->approveReturn($return->id, $this->user->id);

        $this->assertEquals('APPROVED', $approvedReturn->status);
        $this->assertEquals($this->user->id, $approvedReturn->approved_by);

        // Check that stock in quarantine warehouse increased by 5
        $stock = Stock::where('warehouse_id', $this->badWarehouse->id)
            ->where('product_id', $this->product->id)
            ->where('batch_number', 'LOT-WAF-2026-X1')
            ->first();

        $this->assertEquals(5, $stock->qty_on_hand);

        // Check stock ledger recorded an IN entry
        $ledgerEntry = StockLedgerEntry::where('reference_type', 'SALES_RETURN')
            ->where('reference_id', $return->id)
            ->first();

        $this->assertNotNull($ledgerEntry);
        $this->assertEquals('IN', $ledgerEntry->entry_type);
        $this->assertEquals(5, $ledgerEntry->qty_change);

        // Customer's credit used should be decremented by 750,000 (credit note)
        $this->customer->refresh();
        $this->assertEquals(4250000.00, (float)$this->customer->current_credit_used); // 5,000,000 - 750,000
    }
}
