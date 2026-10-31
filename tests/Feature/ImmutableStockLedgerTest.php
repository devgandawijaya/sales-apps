<?php

namespace Tests\Feature;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockLedgerEntry;
use App\Domain\Inventory\Services\StockLedgerService;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class ImmutableStockLedgerTest extends TestCase
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
            'code' => 'TEN-WMS',
            'name' => 'PT WMS Logistik Mandiri',
            'subdomain' => 'wms-test',
            'status' => 'active',
            'plan_tier' => 'business',
        ]);

        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-WMS-01',
            'name' => 'Cabang Cakung DC',
            'type' => 'BRANCH',
            'address' => 'Kawasan Industri Pulogadung',
            'city' => 'Jakarta Timur',
            'province' => 'DKI Jakarta',
        ]);

        $this->warehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-CKG-01',
            'name' => 'Gudang DC Cakung',
            'type' => 'CENTRAL_DC',
            'is_active' => true,
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'code' => 'CAT-FMCG',
            'name' => 'FMCG Goods',
            'slug' => 'fmcg-goods',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $categoryId,
            'sku' => 'PRD-MIE-GORENG',
            'barcode' => '8991234000111',
            'name' => 'Mie Instan Goreng Spesial 85g',
            'cost_price' => 2500.00,
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    public function test_stock_ledger_service_records_accurate_double_entry_balances(): void
    {
        $service = app(StockLedgerService::class);
        $batchNumber = 'LOT-MIE-2026-01';

        // 1. Initial receiving +100 units
        $entryIn = $service->recordEntry(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            batchNumber: $batchNumber,
            entryType: 'IN',
            referenceType: 'GOODS_RECEIPT',
            referenceId: 101,
            referenceNumber: 'GRN-202610-0001',
            qtyChange: 100,
            unitCost: 2500.00,
            notes: 'Penerimaan PO dari Pabrik'
        );

        $this->assertEquals(0, $entryIn->qty_before);
        $this->assertEquals(100, $entryIn->qty_after);
        $this->assertEquals(100, $entryIn->qty_change);
        $this->assertEquals(250000.00, (float) $entryIn->total_cost);

        $stock = Stock::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->where('batch_number', $batchNumber)
            ->first();

        $this->assertNotNull($stock);
        $this->assertEquals(100, $stock->qty_on_hand);

        // 2. Dispatch / Issue -30 units
        $entryOut = $service->recordEntry(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            batchNumber: $batchNumber,
            entryType: 'OUT',
            referenceType: 'SALES_ORDER',
            referenceId: 202,
            referenceNumber: 'SO-202610-0001',
            qtyChange: -30,
            unitCost: 2500.00,
            notes: 'Pengeluaran untuk Sales Order #SO-202610-0001'
        );

        $this->assertEquals(100, $entryOut->qty_before);
        $this->assertEquals(70, $entryOut->qty_after);
        $this->assertEquals(-30, $entryOut->qty_change);

        $stock->refresh();
        $this->assertEquals(70, $stock->qty_on_hand);

        // Verify both entries persisted in stock_ledger_entries
        $this->assertDatabaseCount('stock_ledger_entries', 2);
    }

    public function test_stock_ledger_entries_are_immutable_and_cannot_be_updated_or_deleted(): void
    {
        $service = app(StockLedgerService::class);

        $entry = $service->recordEntry(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            batchNumber: 'LOT-IMMUTABLE-01',
            entryType: 'IN',
            referenceType: 'INITIAL_COUNT',
            referenceId: 1,
            referenceNumber: 'INIT-001',
            qtyChange: 50,
            unitCost: 2500.00
        );

        // Attempt to update ledger entry must throw RuntimeException
        try {
            $entry->update(['qty_change' => 999]);
            $this->fail("Buku besar stok seharusnya tidak boleh diubah (updating).");
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Catatan mutasi stok bersifat permanen', $e->getMessage());
        }

        // Attempt to delete ledger entry must throw RuntimeException
        try {
            $entry->delete();
            $this->fail("Buku besar stok seharusnya tidak boleh dihapus (deleting).");
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Catatan mutasi stok bersifat permanen', $e->getMessage());
        }
    }

    public function test_stock_ledger_service_prevents_negative_inventory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Stok tidak mencukupi");

        $service = app(StockLedgerService::class);

        // Try to deduct 50 units when stock is 0
        $service->recordEntry(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            batchNumber: 'LOT-EMPTY',
            entryType: 'OUT',
            referenceType: 'SALES_ORDER',
            referenceId: 1,
            referenceNumber: 'SO-TEST',
            qtyChange: -50
        );
    }
}

