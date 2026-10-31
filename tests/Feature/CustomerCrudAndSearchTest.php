<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerCrudAndSearchTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch1;
    protected Branch $branch2;
    protected Warehouse $warehouse;
    protected User $user;
    protected Product $product;
    protected ProductUnit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-CRUD',
            'name' => 'PT Distribusi Jaya CRUD',
            'subdomain' => 'crud-test',
            'status' => 'active',
            'plan_tier' => 'enterprise',
        ]);

        TenantContext::setTenant($this->tenant);

        $this->branch1 = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-01',
            'name' => 'Cabang Jakarta Pusat',
            'type' => 'HEAD_OFFICE',
            'address' => 'Jl. Sudirman No. 1',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
        ]);

        $this->branch2 = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-02',
            'name' => 'Cabang Bandung',
            'type' => 'BRANCH',
            'address' => 'Jl. Asia Afrika No. 10',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
        ]);

        $this->warehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'code' => 'WH-01',
            'name' => 'Gudang Utama Cakung',
            'type' => 'CENTRAL_DC',
            'is_active' => true,
        ]);

        $this->user = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'role_title' => 'SUPERADMIN',
            'name' => 'Admin CRUD',
            'email' => 'admin@crud.co.id',
            'password' => 'secret123',
        ]);

        $catId = DB::table('categories')->insertGetId([
            'code' => 'CAT-BEV',
            'slug' => 'beverages',
            'name' => 'Beverages',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $catId,
            'sku' => 'BEV-KOP-001',
            'name' => 'Kopi Robusta 250g',
            'base_uom' => 'PCS',
            'cogs_standard' => 15000.00,
            'tax_type' => 'TAXABLE_11',
            'is_active' => true,
        ]);

        $this->unit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_name' => 'DUS',
            'conversion_factor' => 24,
            'is_base' => false,
            'is_active' => true,
        ]);

        Stock::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-2026-001',
            'expiry_date' => now()->addMonths(12)->toDateString(),
            'qty_on_hand' => 500,
            'qty_reserved' => 0,
            'qty_allocated' => 0,
            'cogs_unit' => 15000.00,
            'is_quarantine' => false,
        ]);

        $pl = DB::table('price_lists')->insertGetId([
            'code' => 'PL-GT-DEFAULT',
            'name' => 'Default GT Price List',
            'channel_type' => 'GT',
            'currency' => 'IDR',
            'valid_from' => now()->subDay(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('price_list_items')->insert([
            'price_list_id' => $pl,
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unit->id,
            'unit_price' => 360000.00,
            'min_quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_can_list_and_search_customers()
    {
        Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'code' => 'CUST-GT-001',
            'name' => 'Toko Subur Sentosa',
            'channel' => 'GT',
            'sub_channel' => 'WARUNG',
            'address' => 'Jl. Kebon Jeruk No. 5',
            'city' => 'Jakarta Barat',
            'credit_limit' => 5000000,
            'term_of_payment' => 7,
            'payment_type' => 'CREDIT',
            'is_active' => true,
        ]);

        Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch2->id,
            'code' => 'CUST-MT-002',
            'name' => 'Super Indo Dago',
            'channel' => 'MT',
            'sub_channel' => 'SUPERMARKET',
            'address' => 'Jl. Ir. H. Djuanda No. 80',
            'city' => 'Bandung',
            'credit_limit' => 50000000,
            'term_of_payment' => 30,
            'payment_type' => 'CREDIT',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);

        // 1. List all
        $response = $this->get(route('customers.index'));
        $response->assertStatus(200);
        $response->assertSee('Toko Subur Sentosa');
        $response->assertSee('Super Indo Dago');

        // 2. Search by name
        $searchResponse = $this->get(route('customers.index', ['search' => 'subur']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Toko Subur Sentosa');
        $searchResponse->assertDontSee('Super Indo Dago');

        // 3. Filter by channel
        $channelResponse = $this->get(route('customers.index', ['channel' => 'MT']));
        $channelResponse->assertStatus(200);
        $channelResponse->assertSee('Super Indo Dago');
        $channelResponse->assertDontSee('Toko Subur Sentosa');
    }

    public function test_can_create_customer_via_form()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('customers.store'), [
            'name' => 'Warung Pojok Barokah',
            'channel' => 'GT',
            'sub_channel' => 'WARUNG',
            'branch_id' => $this->branch1->id,
            'owner_name' => 'Pak Joko',
            'address' => 'Jl. Melati No. 12',
            'city' => 'Jakarta Selatan',
            'credit_limit' => 15000000,
            'term_of_payment' => 14,
            'payment_type' => 'CREDIT',
        ]);

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'name' => 'Warung Pojok Barokah',
            'channel' => 'GT',
            'city' => 'Jakarta Selatan',
            'owner_name' => 'Pak Joko',
        ]);
    }

    public function test_can_update_customer_data()
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'code' => 'CUST-GT-999',
            'name' => 'Toko Awal',
            'channel' => 'GT',
            'sub_channel' => 'WARUNG',
            'address' => 'Jl. Awal No. 1',
            'city' => 'Jakarta Pusat',
            'credit_limit' => 5000000,
            'term_of_payment' => 7,
            'payment_type' => 'CREDIT',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);

        $response = $this->put(route('customers.update', $customer->id), [
            'name' => 'Toko Berubah Nama',
            'channel' => 'GT',
            'sub_channel' => 'GROSIR',
            'branch_id' => $this->branch1->id,
            'address' => 'Jl. Baru No. 99',
            'city' => 'Jakarta Barat',
            'credit_limit' => 25000000,
            'term_of_payment' => 14,
            'payment_type' => 'CREDIT',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');

        $customer->refresh();
        $this->assertEquals('Toko Berubah Nama', $customer->name);
        $this->assertEquals('GROSIR', $customer->sub_channel);
        $this->assertEquals(25000000, (float)$customer->credit_limit);
    }

    public function test_cannot_hard_delete_customer_with_orders_marks_inactive_instead()
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'code' => 'CUST-GT-DEL',
            'name' => 'Toko Memiliki Transaksi',
            'channel' => 'GT',
            'sub_channel' => 'WARUNG',
            'address' => 'Jl. Transaksi No. 1',
            'city' => 'Jakarta Pusat',
            'credit_limit' => 5000000,
            'term_of_payment' => 7,
            'payment_type' => 'CREDIT',
            'is_active' => true,
        ]);

        SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'customer_id' => $customer->id,
            'warehouse_id' => $this->warehouse->id,
            'salesman_id' => $this->user->id,
            'so_number' => 'SO-TEST-001',
            'order_date' => now()->toDateString(),
            'channel' => 'GT',
            'order_type' => 'TAKING_ORDER',
            'status' => 'APPROVED',
            'subtotal' => 1000000,
            'grand_total' => 1110000,
        ]);

        $this->actingAs($this->user);

        $response = $this->delete(route('customers.destroy', $customer->id));
        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('info');

        $customer->refresh();
        $this->assertFalse((bool)$customer->is_active);
    }

    public function test_can_create_sales_order_for_customer_across_branches_without_model_not_found_exception()
    {
        // Customer registered under Branch 2
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch2->id,
            'code' => 'CUST-GT-XBR',
            'name' => 'Toko Lintas Cabang',
            'channel' => 'GT',
            'sub_channel' => 'WARUNG',
            'address' => 'Jl. Lintas Wilayah No. 5',
            'city' => 'Bandung',
            'credit_limit' => 50000000,
            'term_of_payment' => 14,
            'payment_type' => 'CREDIT',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);

        // Ordering fulfilled from Central DC Cakung (Branch 1)
        $response = $this->post(route('sales.store'), [
            'branch_id' => $this->branch1->id,
            'customer_id' => $customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_type' => 'TAKING_ORDER',
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unit->id,
            'qty_ordered' => 2,
            'notes' => 'Uji pesanan lintas cabang tanpa kesalahan sistem',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sales_orders', [
            'customer_id' => $customer->id,
            'branch_id' => $this->branch1->id,
            'warehouse_id' => $this->warehouse->id,
        ]);
    }
}
