<?php

namespace Tests\Feature;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockLedgerEntry;
use App\Domain\Inventory\Models\StockTransfer;
use App\Domain\Inventory\Services\InterBranchTransferService;
use App\Domain\Inventory\Services\StockLedgerService;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

class InterBranchTransferWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branchA;
    protected Branch $branchB;
    protected Warehouse $warehouseA;
    protected Warehouse $warehouseB;
    protected User $operator;
    protected User $approver;
    protected Product $product;
    protected ProductUnit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-TRF',
            'name' => 'PT Distribusi Antar Pulau',
            'subdomain' => 'trf-test',
            'status' => 'active',
            'plan_tier' => 'enterprise',
        ]);

        TenantContext::setTenant($this->tenant);

        // Branch & Warehouse A (Jakarta Central DC)
        $this->branchA = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-JKT',
            'name' => 'Kantor Cabang Jakarta',
            'type' => 'BRANCH',
            'address' => 'Jl. Cakung Cilincing',
            'city' => 'Jakarta Timur',
            'province' => 'DKI Jakarta',
        ]);

        $this->warehouseA = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'code' => 'WH-JKT-01',
            'name' => 'Central DC Cakung',
            'type' => 'CENTRAL_DC',
            'is_active' => true,
        ]);

        // Branch & Warehouse B (Surabaya Branch Hub)
        $this->branchB = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-SBY',
            'name' => 'Kantor Cabang Surabaya',
            'type' => 'BRANCH',
            'address' => 'Jl. Rungkut Industri',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
        ]);

        $this->warehouseB = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchB->id,
            'code' => 'WH-SBY-01',
            'name' => 'Gudang Hub Rungkut',
            'type' => 'BRANCH_MAIN',
            'is_active' => true,
        ]);

        $this->operator = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'role_title' => 'WAREHOUSE_KEEPER',
            'name' => 'Rudi Gudang',
            'email' => 'rudi@distribusilintas.co.id',
            'password' => bcrypt('password123'),
        ]);

        $this->approver = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'role_title' => 'BRANCH_MANAGER',
            'name' => 'Bambang BM',
            'email' => 'bambang.bm@distribusilintas.co.id',
            'password' => bcrypt('password123'),
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'code' => 'CAT-BEV',
            'name' => 'Minuman Rasa',
            'slug' => 'minuman-rasa',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $categoryId,
            'sku' => 'PRD-SUSU-UHT-250',
            'barcode' => '8998877665544',
            'name' => 'Susu UHT Cokelat 250ml',
            'cost_price' => 4500.00,
            'is_active' => true,
        ]);

        $this->unit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_name' => 'KARTON',
            'conversion_factor' => 24,
            'is_base_unit' => false,
        ]);

        // Berikan stok awal 100 pcs di Gudang A
        Stock::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouseA->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-SUSU-2026-X',
            'expiry_date' => Carbon::now()->addMonths(9)->toDateString(),
            'qty_on_hand' => 100,
            'qty_reserved' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    public function test_inter_branch_transfer_full_lifecycle_with_in_transit_isolation(): void
    {
        $transferService = app(InterBranchTransferService::class);

        // 1. Create Transfer Request (Draft)
        $transfer = $transferService->createTransfer(
            fromWarehouseId: $this->warehouseA->id,
            toWarehouseId: $this->warehouseB->id,
            items: [
                [
                    'product_id' => $this->product->id,
                    'product_unit_id' => $this->unit->id,
                    'batch_number' => 'LOT-SUSU-2026-X',
                    'expiry_date' => Carbon::now()->addMonths(9)->toDateString(),
                    'qty_sent' => 40,
                ]
            ],
            userId: $this->operator->id,
            notes: 'Pemindahan pasokan darurat ke Surabaya.'
        );

        $this->assertEquals('DRAFT', $transfer->status);
        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transfer->id,
            'status' => 'DRAFT',
        ]);

        // 2. Submit Transfer
        $transfer = $transferService->submitTransfer($transfer->id);
        $this->assertEquals('SUBMITTED', $transfer->status);

        // 3. Approve Transfer
        $transfer = $transferService->approveTransfer($transfer->id, $this->approver->id);
        $this->assertEquals('APPROVED', $transfer->status);
        $this->assertEquals($this->approver->id, $transfer->approved_by);

        // 4. Dispatch Transfer (Truck Leaves Warehouse A)
        $transfer = $transferService->dispatchTransfer(
            transferId: $transfer->id,
            driverName: 'Pak Joko',
            vehicleNumber: 'B 9123 XYZ'
        );

        $this->assertEquals('IN_TRANSIT', $transfer->status);
        $this->assertNotNull($transfer->dispatched_at);

        // Check Wh A stock is decremented
        $stockA = Stock::where('warehouse_id', $this->warehouseA->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(60, $stockA->qty_on_hand); // 100 - 40 = 60

        // Check Wh B stock is NOT YET credited! (CRITICAL ISOLATION RULE)
        $stockB = Stock::where('warehouse_id', $this->warehouseB->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertNull($stockB);

        // Check Wh A has TRANSFER_OUT ledger entry
        $this->assertDatabaseHas('stock_ledger_entries', [
            'warehouse_id' => $this->warehouseA->id,
            'product_id' => $this->product->id,
            'entry_type' => 'TRANSFER_OUT',
            'qty_change' => -40,
        ]);

        // 5. Receive Transfer at Destination Warehouse B
        $transferItem = $transfer->items()->first();

        $transfer = $transferService->receiveTransfer(
            transferId: $transfer->id,
            receiverId: $this->operator->id,
            receivedItems: [
                [
                    'item_id' => $transferItem->id,
                    'qty_received' => 40,
                    'condition_status' => 'GOOD',
                ]
            ],
            receivedNotes: 'Barang tiba lengkap dan segel utuh.'
        );

        $this->assertEquals('COMPLETED', $transfer->status);
        $this->assertNotNull($transfer->received_date);

        // Check Wh B stock is now credited
        $stockB = Stock::where('warehouse_id', $this->warehouseB->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertNotNull($stockB);
        $this->assertEquals(40, $stockB->qty_on_hand);

        // Check Wh B has TRANSFER_IN ledger entry
        $this->assertDatabaseHas('stock_ledger_entries', [
            'warehouse_id' => $this->warehouseB->id,
            'product_id' => $this->product->id,
            'entry_type' => 'TRANSFER_IN',
            'qty_change' => 40,
        ]);
    }

    public function test_inter_branch_transfer_records_partial_receipt_and_discrepancy(): void
    {
        $transferService = app(InterBranchTransferService::class);

        $transfer = $transferService->createTransfer(
            fromWarehouseId: $this->warehouseA->id,
            toWarehouseId: $this->warehouseB->id,
            items: [
                [
                    'product_id' => $this->product->id,
                    'product_unit_id' => $this->unit->id,
                    'batch_number' => 'LOT-SUSU-2026-X',
                    'expiry_date' => Carbon::now()->addMonths(9)->toDateString(),
                    'qty_sent' => 50,
                ]
            ],
            userId: $this->operator->id
        );

        $transferService->submitTransfer($transfer->id);
        $transferService->approveTransfer($transfer->id, $this->approver->id);
        $transferService->dispatchTransfer($transfer->id);

        $transferItem = $transfer->items()->first();

        // Arrived with 45 units (5 units lost/damaged)
        $completedTransfer = $transferService->receiveTransfer(
            transferId: $transfer->id,
            receiverId: $this->operator->id,
            receivedItems: [
                [
                    'item_id' => $transferItem->id,
                    'qty_received' => 45,
                    'condition_status' => 'GOOD',
                ]
            ],
            receivedNotes: 'Terjadi selisih 5 botol rusak saat transit.'
        );

        $this->assertEquals('RECEIVED_PARTIAL', $completedTransfer->status);

        $transferItem->refresh();
        $this->assertEquals(45, $transferItem->qty_received);
        $this->assertEquals(-5, $transferItem->discrepancy_qty);

        // Only 45 units credited to warehouse B
        $stockB = Stock::where('warehouse_id', $this->warehouseB->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(45, $stockB->qty_on_hand);
    }

    public function test_can_reject_transfer_with_mandatory_reason(): void
    {
        $transferService = app(InterBranchTransferService::class);

        $transfer = $transferService->createTransfer(
            fromWarehouseId: $this->warehouseA->id,
            toWarehouseId: $this->warehouseB->id,
            items: [
                [
                    'product_id' => $this->product->id,
                    'product_unit_id' => $this->unit->id,
                    'batch_number' => 'LOT-SUSU-2026-X',
                    'expiry_date' => Carbon::now()->addMonths(9)->toDateString(),
                    'qty_sent' => 20,
                ]
            ],
            userId: $this->operator->id
        );

        $rejectedTransfer = $transferService->rejectTransfer(
            transferId: $transfer->id,
            approverId: $this->approver->id,
            reason: 'Kapasitas gudang tujuan penuh tidak mencukupi.'
        );

        $this->assertEquals('REJECTED', $rejectedTransfer->status);
        $this->assertEquals('Kapasitas gudang tujuan penuh tidak mencukupi.', $rejectedTransfer->rejection_reason);
    }
}

