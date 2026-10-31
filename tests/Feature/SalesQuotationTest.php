<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Models\SalesQuotation;
use App\Domain\Sales\Models\SalesQuotationItem;
use App\Domain\Sales\Services\ConvertQuotationToSalesOrderService;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

class SalesQuotationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Warehouse $warehouse;
    protected User $salesman;
    protected Customer $customer;
    protected Product $product;
    protected ProductUnit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-QUO',
            'name' => 'PT Distribusi Mandiri',
            'subdomain' => 'quo-test',
            'status' => 'active',
            'plan_tier' => 'professional',
        ]);

        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-01',
            'name' => 'Cabang Jakarta Pusat',
            'type' => 'BRANCH',
            'address' => 'Jl. Thamrin No. 10',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
        ]);

        $this->warehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-01',
            'name' => 'Gudang Utama Thamrin',
            'type' => 'CENTRAL_DC',
            'is_active' => true,
        ]);

        $this->salesman = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'role_title' => 'SALESMAN_GT',
            'name' => 'Budi Salesman',
            'email' => 'budi@distribusimandiri.co.id',
            'password' => bcrypt('password123'),
        ]);

        $this->customer = Customer::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'CUST-001',
            'name' => 'Toko Sembako Berkah',
            'channel' => 'GT',
            'sub_channel' => 'GROSIR',
            'address' => 'Jl. Senen Raya No. 45',
            'city' => 'Jakarta Pusat',
            'payment_type' => 'CREDIT',
            'term_of_payment' => 30,
            'credit_limit' => 100000000.00,
            'current_credit_used' => 0.00,
            'is_active' => true,
        ]);

        $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
            'code' => 'CAT-BEV',
            'name' => 'Beverages',
            'slug' => 'beverages',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $categoryId,
            'sku' => 'PRD-TEH-BOTOL',
            'barcode' => '8991234567890',
            'name' => 'Teh Melati Wangi Botol 350ml',
            'cost_price' => 3000.00,
            'is_active' => true,
        ]);

        $this->unit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_name' => 'DUS',
            'conversion_factor' => 24,
            'is_base_unit' => false,
        ]);

        // Sediakan batch stok FEFO
        Stock::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-2026-A',
            'expiry_date' => Carbon::now()->addMonths(6)->toDateString(),
            'qty_on_hand' => 5000,
            'qty_reserved' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    public function test_can_create_sales_quotation_with_accurate_computations(): void
    {
        $unitPrice = 72000.0000; // Harga per Dus
        $qty = 10;
        $gross = $unitPrice * $qty; // 720,000
        $discountAmount = 20000.00;
        $taxable = $gross - $discountAmount; // 700,000
        $tax = round($taxable * 0.11, 2); // 77,000
        $grandTotal = $taxable + $tax; // 777,000

        $quotation = SalesQuotation::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->salesman->id,
            'quotation_number' => 'QUO-202610-0001',
            'quotation_date' => Carbon::now()->toDateString(),
            'expiry_date' => Carbon::now()->addDays(7)->toDateString(),
            'channel' => 'GT',
            'status' => 'SENT',
            'subtotal' => $gross,
            'discount_amount' => $discountAmount,
            'tax_amount' => $tax,
            'grand_total' => $grandTotal,
            'notes' => 'Penawaran khusus harga promosi Q4.',
        ]);

        $item = SalesQuotationItem::create([
            'sales_quotation_id' => $quotation->id,
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unit->id,
            'conversion_factor' => 24,
            'qty_ordered' => $qty,
            'qty_in_base_uom' => $qty * 24,
            'unit_price' => $unitPrice,
            'gross_amount' => $gross,
            'discount_percent_1' => 0.00,
            'discount_percent_2' => 0.00,
            'discount_nominal' => $discountAmount,
            'net_amount' => $taxable,
        ]);

        $this->assertDatabaseHas('sales_quotations', [
            'quotation_number' => 'QUO-202610-0001',
            'status' => 'SENT',
            'grand_total' => 777000.00,
        ]);

        $this->assertDatabaseHas('sales_quotation_items', [
            'sales_quotation_id' => $quotation->id,
            'product_id' => $this->product->id,
            'unit_price' => 72000.0000,
            'qty_ordered' => 10,
        ]);

        $this->assertTrue($quotation->isConvertible());
        $this->assertFalse($quotation->isExpired());
    }

    public function test_can_convert_quotation_to_sales_order_with_frozen_prices(): void
    {
        $unitPrice = 85000.0000;
        $qty = 5;
        $gross = $unitPrice * $qty; // 425,000
        $discountAmount = 25000.00;
        $taxable = $gross - $discountAmount; // 400,000
        $tax = round($taxable * 0.11, 2); // 44,000
        $grandTotal = $taxable + $tax; // 444,000

        $quotation = SalesQuotation::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->salesman->id,
            'quotation_number' => 'QUO-202610-0002',
            'quotation_date' => Carbon::now()->toDateString(),
            'expiry_date' => Carbon::now()->addDays(7)->toDateString(),
            'channel' => 'GT',
            'status' => 'ACCEPTED',
            'subtotal' => $gross,
            'discount_amount' => $discountAmount,
            'tax_amount' => $tax,
            'grand_total' => $grandTotal,
        ]);

        SalesQuotationItem::create([
            'sales_quotation_id' => $quotation->id,
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unit->id,
            'conversion_factor' => 24,
            'qty_ordered' => $qty,
            'qty_in_base_uom' => $qty * 24,
            'unit_price' => $unitPrice,
            'gross_amount' => $gross,
            'discount_nominal' => $discountAmount,
            'net_amount' => $taxable,
        ]);

        // Convert using service
        $convertService = app(ConvertQuotationToSalesOrderService::class);
        $salesOrder = $convertService->execute($quotation, $this->warehouse->id);

        $this->assertInstanceOf(SalesOrder::class, $salesOrder);
        $this->assertEquals('PENDING_APPROVAL', $salesOrder->status);
        $this->assertEquals($quotation->id, $salesOrder->quotation_id);

        // Check quotation updated status
        $quotation->refresh();
        $this->assertEquals('CONVERTED_TO_SO', $quotation->status);
        $this->assertEquals($salesOrder->id, $quotation->converted_sales_order_id);
        $this->assertFalse($quotation->isConvertible());

        // Check SO items preserved exact snapshot price
        $this->assertDatabaseHas('sales_order_items', [
            'sales_order_id' => $salesOrder->id,
            'product_id' => $this->product->id,
            'unit_price' => 85000.0000,
        ]);

        // Check stock was reserved
        $stock = Stock::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(5 * 24, $stock->qty_reserved);
    }

    public function test_cannot_convert_already_converted_or_rejected_quotation(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $quotation = SalesQuotation::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->salesman->id,
            'quotation_number' => 'QUO-202610-0003',
            'quotation_date' => Carbon::now()->toDateString(),
            'expiry_date' => Carbon::now()->addDays(7)->toDateString(),
            'channel' => 'GT',
            'status' => 'REJECTED',
        ]);

        $convertService = app(ConvertQuotationToSalesOrderService::class);
        $convertService->execute($quotation, $this->warehouse->id);
    }
}
