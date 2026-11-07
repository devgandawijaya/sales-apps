<?php

namespace Tests\Feature;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Pricing\Models\PriceList;
use App\Domain\Pricing\Models\PriceListItem;
use App\Domain\Product\Models\Category;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductAndPriceListManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'PT Distribusi Pangan Nasional',
            'code' => 'DPN-01',
            'subdomain' => 'dpn-distribusi',
            'status' => 'active',
            'plan_tier' => 'enterprise',
            'max_users' => 50,
            'max_branches' => 5,
            'max_warehouses' => 10,
        ]);

        TenantContext::setTenant($this->tenant);

        $branch = \App\Domain\Organization\Models\Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-JKT',
            'name' => 'Cabang Jakarta Pusat',
            'type' => 'HEAD_OFFICE',
            'address' => 'Jl. Kebon Sirih No. 10',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'is_active' => true,
        ]);

        $this->user = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Super Administrator',
            'email' => 'admin@sales.co.id',
            'password' => bcrypt('password123'),
            'role_title' => 'SUPERADMIN',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'code' => 'CAT-BEV',
            'name' => 'Minuman Kemasan',
            'slug' => 'minuman-kemasan',
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_requests_to_products_and_price_lists_redirect_to_login(): void
    {
        $protectedRoutes = [
            '/products',
            '/products/create',
            '/price-lists',
            '/price-lists/create',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertStatus(302);
            $response->assertRedirect('/login');
        }
    }

    public function test_can_list_products_and_search(): void
    {
        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'sku' => 'PRD-TEH-BOTOL-350',
            'name' => 'Teh Botol Melati 350ml',
            'brand' => 'Sosro',
            'base_uom' => 'BOTOL',
            'cost_price' => 3500.00,
            'is_active' => true,
        ]);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_name' => 'BOTOL',
            'conversion_factor' => 1,
            'is_base_unit' => true,
        ]);

        $response = $this->actingAs($this->user)->get('/products');
        $response->assertStatus(200);
        $response->assertSee('Teh Botol Melati 350ml');
        $response->assertSee('PRD-TEH-BOTOL-350');

        // Test Search Filter
        $searchRes = $this->actingAs($this->user)->get('/products?search=melati');
        $searchRes->assertStatus(200);
        $searchRes->assertSee('Teh Botol Melati 350ml');

        $notFoundRes = $this->actingAs($this->user)->get('/products?search=kripik_singkong_xyz');
        $notFoundRes->assertStatus(200);
        $notFoundRes->assertDontSee('Teh Botol Melati 350ml');
    }

    public function test_can_create_product_with_multi_uom_units(): void
    {
        $payload = [
            'name' => 'Kecap Manis Sedap 600ml',
            'sku' => 'PRD-KECAP-600',
            'barcode' => '8999999112233',
            'category_id' => $this->category->id,
            'brand' => 'Sedap',
            'base_uom' => 'POUCH',
            'cost_price' => 18000,
            'weight_gram' => 600,
            'volume_cbm' => 0.0012,
            'units' => [
                [
                    'unit_name' => 'DUS',
                    'conversion_factor' => 12,
                    'barcode' => '8999999112244',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post('/products', $payload);
        $response->assertStatus(302);
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $product = Product::withoutGlobalScopes()->where('sku', 'PRD-KECAP-600')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Kecap Manis Sedap 600ml', $product->name);
        $this->assertEquals('POUCH', $product->base_uom);
        $this->assertEquals(18000.00, (float) $product->cost_price);

        // Assert base unit auto-created with factor 1
        $baseUnit = ProductUnit::where('product_id', $product->id)->where('is_base_unit', true)->first();
        $this->assertNotNull($baseUnit);
        $this->assertEquals('POUCH', $baseUnit->unit_name);
        $this->assertEquals(1, $baseUnit->conversion_factor);

        // Assert secondary unit DUS created
        $secondaryUnit = ProductUnit::where('product_id', $product->id)->where('unit_name', 'DUS')->first();
        $this->assertNotNull($secondaryUnit);
        $this->assertEquals(12, $secondaryUnit->conversion_factor);
        $this->assertFalse((bool) $secondaryUnit->is_base_unit);
    }

    public function test_can_update_product_and_add_unit(): void
    {
        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'sku' => 'PRD-BISKUIT-ROMA',
            'name' => 'Biskuit Kelapa Roma 300g',
            'base_uom' => 'BKS',
            'cost_price' => 7500.00,
            'is_active' => true,
        ]);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_name' => 'BKS',
            'conversion_factor' => 1,
            'is_base_unit' => true,
        ]);

        $updatePayload = [
            'name' => 'Biskuit Kelapa Roma 300g Spesial',
            'sku' => 'PRD-BISKUIT-ROMA',
            'category_id' => $this->category->id,
            'base_uom' => 'BKS',
            'cost_price' => 8000.00,
            'is_active' => 1,
            'units' => [
                [
                    'unit_name' => 'KARTON',
                    'conversion_factor' => 24,
                    'barcode' => '8991000200030',
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->put('/products/' . $product->id, $updatePayload);
        $response->assertStatus(302);
        $response->assertRedirect(route('products.index'));

        $fresh = $product->fresh();
        $this->assertEquals('Biskuit Kelapa Roma 300g Spesial', $fresh->name);
        $this->assertEquals(8000.00, (float) $fresh->cost_price);

        $kartonUnit = ProductUnit::where('product_id', $product->id)->where('unit_name', 'KARTON')->first();
        $this->assertNotNull($kartonUnit);
        $this->assertEquals(24, $kartonUnit->conversion_factor);
    }

    public function test_cannot_hard_delete_product_with_stock_inactivates_safely(): void
    {
        $warehouse = \App\Domain\Organization\Models\Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->user->branch_id,
            'code' => 'WH-TEST',
            'name' => 'Gudang Uji Coba',
            'type' => 'CENTRAL_DC',
            'is_active' => true,
        ]);

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'sku' => 'PRD-STOK-ADA',
            'name' => 'Minyak Goreng 2L',
            'base_uom' => 'POUCH',
            'cost_price' => 32000.00,
            'is_active' => true,
        ]);

        Stock::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'batch_number' => 'LOT-001',
            'expiry_date' => now()->addYear()->toDateString(),
            'qty_on_hand' => 50,
            'qty_reserved' => 0,
            'qty_allocated' => 0,
            'cogs_unit' => 32000.00,
            'is_quarantine' => false,
        ]);

        $response = $this->actingAs($this->user)->delete('/products/' . $product->id);
        $response->assertStatus(302);
        $response->assertSessionHas('info');

        // Product must still exist in DB, but is_active = false
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);
    }

    public function test_can_create_and_manage_price_list_and_items(): void
    {
        // 1. Create Price List
        $plPayload = [
            'code' => 'PL-GROSIR-JATIM',
            'name' => 'Daftar Harga Grosir Jawa Timur',
            'channel_type' => 'WHOLESALE',
            'currency' => 'IDR',
            'valid_from' => now()->toDateString(),
            'valid_to' => now()->addMonths(6)->toDateString(),
        ];

        $response = $this->actingAs($this->user)->post('/price-lists', $plPayload);
        $response->assertStatus(302);

        $priceList = PriceList::where('code', 'PL-GROSIR-JATIM')->first();
        $this->assertNotNull($priceList);
        $this->assertEquals('WHOLESALE', $priceList->channel_type);

        // 2. Add Item to Price List
        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'sku' => 'PRD-SARDEN-155',
            'name' => 'Sarden Saus Tomat 155g',
            'base_uom' => 'KALENG',
            'cost_price' => 8500.00,
            'is_active' => true,
        ]);

        $unit = ProductUnit::create([
            'product_id' => $product->id,
            'unit_name' => 'DUS',
            'conversion_factor' => 50,
            'is_base_unit' => false,
        ]);

        $itemPayload = [
            'product_id' => $product->id,
            'product_unit_id' => $unit->id,
            'min_quantity' => 5,
            'unit_price' => 450000.00,
        ];

        $itemResponse = $this->actingAs($this->user)->post("/price-lists/{$priceList->id}/items", $itemPayload);
        $itemResponse->assertStatus(302);
        $itemResponse->assertSessionHas('success');

        $this->assertDatabaseHas('price_list_items', [
            'price_list_id' => $priceList->id,
            'product_id' => $product->id,
            'product_unit_id' => $unit->id,
            'min_quantity' => 5,
            'unit_price' => 450000.00,
        ]);

        // 3. View Price List Show
        $showRes = $this->actingAs($this->user)->get("/price-lists/{$priceList->id}");
        $showRes->assertStatus(200);
        $showRes->assertSee('Sarden Saus Tomat 155g');
        $showRes->assertSee('450.000');

        // 4. Delete Item from Price List
        $item = PriceListItem::where('price_list_id', $priceList->id)->first();
        $delItemRes = $this->actingAs($this->user)->delete("/price-lists/{$priceList->id}/items/{$item->id}");
        $delItemRes->assertStatus(302);
        $this->assertDatabaseMissing('price_list_items', ['id' => $item->id]);
    }
}
