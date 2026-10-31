<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Sales\Models\DeliveryOrder;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Models\SalesOrderItem;
use App\Domain\Sales\Services\CreateDeliveryOrderService;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

class DeliveryOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Warehouse $warehouse;
    protected Customer $customer;
    protected User $user;
    protected Product $product;
    protected ProductUnit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-LOG',
            'name' => 'PT Logistik Distribusi Jaya',
            'subdomain' => 'logistik-test',
            'status' => 'active',
            'plan_tier' => 'enterprise',
        ]);

        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-LOG-01',
            'name' => 'Cabang Logistik Jakarta',
            'type' => 'BRANCH',
            'address' => 'Jl. Daan Mogot KM 12',
            'city' => 'Jakarta Barat',
            'province' => 'DKI Jakarta',
        ]);

        $this->warehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-LOG-01',
            'name' => 'Gudang Utama Daan Mogot',
            'type' => 'CENTRAL_DC',
            'is_active' => true,
        ]);

        $this->customer = Customer::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'CUST-LOG-01',
            'name' => 'Super Indo Daan Mogot',
            'channel' => 'MT',
            'sub_channel' => 'SUPERMARKET',
            'address' => 'Jl. Daan Mogot No. 50',
            'city' => 'Jakarta Barat',
            'term_of_payment' => 30,
            'credit_limit' => 100000000.00,
            'current_credit_used' => 20000000.00,
            'is_active' => true,
        ]);

        $this->user = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'role_title' => 'WAREHOUSE_KEEPER',
            'name' => 'Agus Logistik',
            'email' => 'agus@logistik.co.id',
            'password' => bcrypt('password123'),
        ]);

        $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
            'code' => 'CAT-LOG',
            'name' => 'Beverages Logistik',
            'slug' => 'beverages-logistik',
            'is_active' => true,
            'created_at' => now(),
        ]);

        $this->product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $categoryId,
            'sku' => 'BEV-001',
            'name' => 'Kopi Susu UHT 250ml',
            'brand' => 'KopiBrand',
            'base_uom' => 'Pcs',
            'is_active' => true,
        ]);

        $this->unit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_name' => 'Dus',
            'conversion_factor' => 24,
            'is_base_unit' => false,
        ]);

        // Seed stock batch
        Stock::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-KOPI-2026-A1',
            'expiry_date' => Carbon::now()->addMonths(6)->toDateString(),
            'qty_on_hand' => 500,
            'qty_reserved' => 48,
        ]);
    }

    public function test_can_create_delivery_order_from_approved_sales_order(): void
    {
        // 1. Create Approved Sales Order
        $salesOrder = SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-LOG-202610-0001',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'MT',
            'status' => 'APPROVED',
            'grand_total' => 5000000.00,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unit->id,
            'conversion_factor' => 24,
            'qty_ordered' => 2,
            'qty_in_base_uom' => 48,
            'qty_delivered_base_uom' => 0,
            'unit_price' => 120000.00,
            'gross_amount' => 240000.00,
            'net_amount' => 240000.00,
        ]);

        // 2. Execute DO creation
        $service = app(CreateDeliveryOrderService::class);
        $do = $service->execute(
            salesOrderId: $salesOrder->id,
            driverName: 'Bambang Supir',
            vehiclePlateNumber: 'B 1234 SXY',
            deliveryType: 'DIRECT_STORE',
            notes: 'Kirim pagi',
            userId: $this->user->id
        );

        $this->assertInstanceOf(DeliveryOrder::class, $do);
        $this->assertStringStartsWith('DO-', $do->do_number);
        $this->assertEquals('DISPATCHED', $do->status);
        $this->assertEquals('Bambang Supir', $do->driver_name);
        $this->assertCount(1, $do->items);
        $this->assertEquals(48, $do->items->first()->qty_shipped);

        // Sales order should now be DELIVERED
        $salesOrder->refresh();
        $this->assertEquals('DELIVERED', $salesOrder->status);

        // Stock reserved should be cleared
        $stock = Stock::where('batch_number', 'LOT-KOPI-2026-A1')->first();
        $this->assertEquals(0, $stock->qty_reserved);
        $this->assertEquals(452, $stock->qty_on_hand); // 500 - 48
    }

    public function test_cannot_create_delivery_order_for_draft_or_pending_order(): void
    {
        $salesOrder = SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-LOG-202610-0002',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'MT',
            'status' => 'PENDING_APPROVAL',
            'grand_total' => 2000000.00,
        ]);

        $this->expectException(InvalidArgumentException::class);

        $service = app(CreateDeliveryOrderService::class);
        $service->execute(salesOrderId: $salesOrder->id);
    }

    public function test_can_confirm_delivery_reception_by_customer(): void
    {
        $salesOrder = SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-LOG-202610-0003',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'MT',
            'status' => 'APPROVED',
            'grand_total' => 1000000.00,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unit->id,
            'conversion_factor' => 24,
            'qty_ordered' => 1,
            'qty_in_base_uom' => 24,
            'unit_price' => 100000.00,
            'gross_amount' => 100000.00,
            'net_amount' => 100000.00,
        ]);

        $service = app(CreateDeliveryOrderService::class);
        $do = $service->execute(
            salesOrderId: $salesOrder->id,
            driverName: 'Pak Dedi',
            vehiclePlateNumber: 'B 9999 SXX'
        );

        $this->actingAs($this->user);
        $response = $this->post(route('delivery-orders.deliver', $do->id), [
            'recipient_name' => 'Ibu Rina (Kepala Toko)',
        ]);

        $response->assertSessionHas('success');
        $do->refresh();
        $this->assertEquals('DELIVERED', $do->status);
        $this->assertEquals('Ibu Rina (Kepala Toko)', $do->recipient_name);
        $this->assertNotNull($do->delivered_at);
        $this->assertEquals(24, $do->items->first()->qty_received);
    }
}
