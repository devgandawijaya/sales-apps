<?php

namespace Tests\Feature;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Services\InventoryAnalyticsService;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class InventoryAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Warehouse $warehouse;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-ANL',
            'name' => 'PT Analitik Logistik Terpadu',
            'subdomain' => 'anl-test',
            'status' => 'active',
            'plan_tier' => 'enterprise',
        ]);

        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-ANL-01',
            'name' => 'Cabang Medan Hub',
            'type' => 'BRANCH',
            'address' => 'Jl. Gatot Subroto No. 77',
            'city' => 'Medan',
            'province' => 'Sumatera Utara',
        ]);

        $this->warehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-MDN-01',
            'name' => 'Gudang Hub Medan',
            'type' => 'CENTRAL_DC',
            'is_active' => true,
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'code' => 'CAT-DAIRY',
            'name' => 'Dairy & Milk',
            'slug' => 'dairy-milk',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $categoryId,
            'sku' => 'PRD-YOGURT-100',
            'barcode' => '8995544332211',
            'name' => 'Yogurt Strawberry Fresh 100ml',
            'cost_price' => 8000.00,
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    public function test_detects_near_expiry_batches_with_proper_urgency_severity(): void
    {
        // 1. Batch expired 5 days ago (EXPIRED)
        Stock::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-EXP-01',
            'expiry_date' => Carbon::today()->subDays(5)->toDateString(),
            'qty_on_hand' => 10,
            'qty_reserved' => 0,
        ]);

        // 2. Batch expires in 15 days (CRITICAL <= 30 days)
        Stock::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-CRIT-02',
            'expiry_date' => Carbon::today()->addDays(15)->toDateString(),
            'qty_on_hand' => 20,
            'qty_reserved' => 5,
        ]);

        // 3. Batch expires in 45 days (WARNING <= 60 days)
        Stock::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-WARN-03',
            'expiry_date' => Carbon::today()->addDays(45)->toDateString(),
            'qty_on_hand' => 50,
            'qty_reserved' => 0,
        ]);

        // 4. Batch expires in 80 days (NOTICE <= 90 days)
        Stock::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-NOTC-04',
            'expiry_date' => Carbon::today()->addDays(80)->toDateString(),
            'qty_on_hand' => 100,
            'qty_reserved' => 0,
        ]);

        $analytics = app(InventoryAnalyticsService::class);
        $alerts = $analytics->getNearExpiryStock(90, $this->warehouse->id);

        $this->assertCount(4, $alerts);

        $expired = $alerts->firstWhere('batch_number', 'LOT-EXP-01');
        $this->assertEquals('EXPIRED', $expired['severity']);

        $critical = $alerts->firstWhere('batch_number', 'LOT-CRIT-02');
        $this->assertEquals('CRITICAL', $critical['severity']);
        $this->assertEquals(15, $critical['qty_available']); // 20 - 5

        $warning = $alerts->firstWhere('batch_number', 'LOT-WARN-03');
        $this->assertEquals('WARNING', $warning['severity']);

        $notice = $alerts->firstWhere('batch_number', 'LOT-NOTC-04');
        $this->assertEquals('NOTICE', $notice['severity']);
    }

    public function test_computes_inventory_valuation_and_quantity_breakdown(): void
    {
        Stock::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-VAL-01',
            'expiry_date' => Carbon::today()->addMonths(6)->toDateString(),
            'qty_on_hand' => 50,
            'qty_reserved' => 10,
        ]);

        $analytics = app(InventoryAnalyticsService::class);
        $summary = $analytics->getInventoryValuationSummary($this->warehouse->id);

        $this->assertEquals(50, $summary['total_qty_on_hand']);
        $this->assertEquals(10, $summary['total_qty_reserved']);
        $this->assertEquals(40, $summary['total_qty_available']);
        $this->assertEquals(400000.00, $summary['total_valuation']); // 50 * 8,000
    }
}

