<?php

namespace Tests\Feature;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockLedgerEntry;
use App\Domain\Inventory\Models\StockOpname;
use App\Domain\Inventory\Services\StockOpnameService;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class StockOpnameWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Warehouse $warehouse;
    protected User $staff;
    protected User $manager;
    protected Product $product;
    protected Stock $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-OPN',
            'name' => 'PT Surya Makmur Logistik',
            'subdomain' => 'opn-test',
            'status' => 'active',
            'plan_tier' => 'enterprise',
        ]);

        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-BDG',
            'name' => 'Cabang Bandung',
            'type' => 'BRANCH',
            'address' => 'Jl. Soekarno Hatta No. 100',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
        ]);

        $this->warehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-BDG-01',
            'name' => 'Gudang Utama Bandung',
            'type' => 'BRANCH_MAIN',
            'is_active' => true,
        ]);

        $this->staff = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'role_title' => 'WAREHOUSE_KEEPER',
            'name' => 'Dani Staff',
            'email' => 'dani@suryamakmur.co.id',
            'password' => bcrypt('password123'),
        ]);

        $this->manager = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'role_title' => 'BRANCH_MANAGER',
            'name' => 'Agus BM',
            'email' => 'agus.bm@suryamakmur.co.id',
            'password' => bcrypt('password123'),
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'code' => 'CAT-OIL',
            'name' => 'Cooking Oil',
            'slug' => 'cooking-oil',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $categoryId,
            'sku' => 'PRD-MINYAK-2L',
            'barcode' => '8997766554433',
            'name' => 'Minyak Goreng Pouch 2L',
            'cost_price' => 32000.00,
            'is_active' => true,
        ]);

        // Sediakan 80 pcs stok buku awal
        $this->stock = Stock::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-OIL-2026-A',
            'expiry_date' => Carbon::now()->addYear()->toDateString(),
            'qty_on_hand' => 80,
            'qty_reserved' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    public function test_stock_opname_freezes_snapshot_and_adjusts_ledger_on_approval(): void
    {
        $opnameService = app(StockOpnameService::class);

        // 1. Start Opname Session
        $opname = $opnameService->startOpname(
            branchId: $this->branch->id,
            warehouseId: $this->warehouse->id,
            title: 'Cycle Count Minyak Goreng Q4',
            userId: $this->staff->id,
            countType: 'CYCLE_COUNT',
            blindCount: false
        );

        $this->assertEquals('IN_PROGRESS', $opname->status);
        $this->assertCount(1, $opname->items);

        $item = $opname->items()->first();
        $this->assertEquals(80, $item->system_qty); // Frozen snapshot

        // 2. Physical Count Submission (Found 75 pcs -> 5 missing)
        $opname = $opnameService->recordCount(
            opnameId: $opname->id,
            countedItems: [
                [
                    'item_id' => $item->id,
                    'physical_qty' => 75,
                    'reason_code' => 'DAMAGED',
                    'notes' => '5 pouch bocor saat handling.',
                ]
            ]
        );

        $this->assertEquals('SUBMITTED', $opname->status);
        $this->assertEquals(-5, $opname->total_discrepancy_qty);
        $this->assertEquals(-160000.00, (float) $opname->total_cogs_variance); // -5 * 32,000

        // 3. Manager Approval & Automatic Posting to Immutable Ledger
        $postedOpname = $opnameService->approveAndPost(
            opnameId: $opname->id,
            approverId: $this->manager->id
        );

        $this->assertEquals('POSTED', $postedOpname->status);
        $this->assertNotNull($postedOpname->approved_at);
        $this->assertNotNull($postedOpname->posted_at);

        // Physical stock on hand updated to 75
        $this->stock->refresh();
        $this->assertEquals(75, $this->stock->qty_on_hand);

        // Ledger has OPNAME_ADJUST_OUT entry
        $this->assertDatabaseHas('stock_ledger_entries', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'entry_type' => 'OPNAME_ADJUST_OUT',
            'qty_change' => -5,
            'qty_before' => 80,
            'qty_after' => 75,
        ]);
    }

    public function test_stock_opname_supports_blind_count_mode(): void
    {
        $opnameService = app(StockOpnameService::class);

        $opname = $opnameService->startOpname(
            branchId: $this->branch->id,
            warehouseId: $this->warehouse->id,
            title: 'Audit Blind Count',
            userId: $this->staff->id,
            countType: 'WALL_TO_WALL',
            blindCount: true
        );

        $this->assertTrue($opname->blind_count);
        $item = $opname->items()->first();
        $this->assertEquals(0, $item->physical_qty); // Masked to 0 for blind count
    }

    public function test_can_reject_opname_session(): void
    {
        $opnameService = app(StockOpnameService::class);

        $opname = $opnameService->startOpname(
            branchId: $this->branch->id,
            warehouseId: $this->warehouse->id,
            title: 'Opname Rejected',
            userId: $this->staff->id
        );

        $rejected = $opnameService->rejectOpname(
            opnameId: $opname->id,
            approverId: $this->manager->id,
            reason: 'Prosedur sampling tidak sesuai SOP audit internal.'
        );

        $this->assertEquals('REJECTED', $rejected->status);
        $this->assertEquals('Prosedur sampling tidak sesuai SOP audit internal.', $rejected->rejection_reason);
    }
}

