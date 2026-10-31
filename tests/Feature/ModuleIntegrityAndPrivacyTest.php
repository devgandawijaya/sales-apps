<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Pricing\Models\PriceList;
use App\Domain\Pricing\Models\PriceListItem;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Sales\Models\DeliveryOrder;
use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\Payment;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Models\SalesQuotation;
use App\Domain\Sales\Models\SalesReturn;
use App\Domain\Sales\Services\ApproveSalesOrderService;
use App\Domain\Sales\Services\ConvertQuotationToSalesOrderService;
use App\Domain\Sales\Services\CreateDeliveryOrderService;
use App\Domain\Sales\Services\GenerateInvoiceService;
use App\Domain\Sales\Services\ProcessSalesReturnService;
use App\Domain\Sales\Services\RecordPaymentService;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModuleIntegrityAndPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Warehouse $warehouse;
    protected User $user;
    protected Product $product;
    protected ProductUnit $unit;
    protected PriceList $priceList;
    protected Customer $customer;

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

        $this->branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-JKT',
            'name' => 'Cabang Jakarta Pusat',
            'type' => 'HEAD_OFFICE',
            'address' => 'Jl. Kebon Sirih No. 10',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-CKG',
            'name' => 'Gudang DC Cakung',
            'type' => 'CENTRAL_DC',
            'address' => 'Kawasan Industri Cakung Blok B1',
            'is_active' => true,
        ]);

        $this->user = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Manager Operasional',
            'email' => 'manager.ops@dpn.co.id',
            'password' => bcrypt('password123'),
            'role_title' => 'SUPERADMIN',
            'is_active' => true,
        ]);

        $catId = DB::table('categories')->insertGetId([
            'code' => 'CAT-BEV',
            'slug' => 'minuman-kemasan',
            'name' => 'Minuman Kemasan',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $catId,
            'sku' => 'PRD-SUSU-UHT-250',
            'name' => 'Susu UHT Cokelat 250ml',
            'base_uom' => 'PCS',
            'cogs_standard' => 4500.00,
            'tax_type' => 'TAXABLE_11',
            'is_active' => true,
        ]);

        $this->unit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_name' => 'KARTON',
            'conversion_factor' => 24,
            'is_base' => false,
            'is_active' => true,
        ]);

        $this->priceList = PriceList::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'PL-GT-JAKARTA',
            'name' => 'Daftar Harga GT Jakarta',
            'channel_type' => 'GT',
            'currency' => 'IDR',
            'valid_from' => now()->subDay(),
            'is_active' => true,
        ]);

        PriceListItem::create([
            'price_list_id' => $this->priceList->id,
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unit->id,
            'unit_price' => 120000.00, // 1 karton (24 pcs)
            'min_quantity' => 1,
        ]);

        Stock::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-SUSU-2026',
            'expiry_date' => now()->addMonths(10)->toDateString(),
            'qty_on_hand' => 1000,
            'qty_reserved' => 0,
            'qty_allocated' => 0,
            'cogs_unit' => 4500.00,
            'is_quarantine' => false,
        ]);

        $this->customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'CUST-GT-JKT-001',
            'name' => 'Grosir Sembako Maju Jaya',
            'channel' => 'GT',
            'sub_channel' => 'GROSIR',
            'address' => 'Pasar Induk Kramat Jati No. 45',
            'city' => 'Jakarta Timur',
            'credit_limit' => 25000000,
            'current_credit_used' => 0,
            'term_of_payment' => 14,
            'payment_type' => 'CREDIT',
            'is_active' => true,
        ]);
    }

    public function test_privacy_unauthenticated_requests_redirect_to_login_without_data_exposure(): void
    {
        // Protected internal routes
        $routes = [
            '/dashboard',
            '/customers',
            '/sales-orders',
            '/finance',
            '/inventory',
            '/superadmin/dashboard',
            '/roles',
            '/users',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(302);
            $response->assertRedirect('/login');
            // Ensure no customer or financial data leaked in redirection
            $this->assertStringNotContainsString('Grosir Sembako Maju Jaya', $response->getContent());
            $this->assertStringNotContainsString('credit_limit', $response->getContent());
        }
    }

    public function test_privacy_unauthenticated_api_endpoints_return_401_unauthorized(): void
    {
        $apiEndpoints = [
            '/api/dashboard',
            '/api/customers',
            '/api/sales-orders',
            '/api/stocks',
            '/api/v1/tenant/current',
        ];

        foreach ($apiEndpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401);
            $response->assertJson(['message' => 'Unauthenticated.']);
            // Ensure customer details and balances are never leaked
            $this->assertStringNotContainsString('Grosir Sembako Maju Jaya', $response->getContent());
            $this->assertStringNotContainsString('PRD-SUSU-UHT-250', $response->getContent());
        }
    }

    public function test_module_flow_quotation_to_so_to_do_to_invoice_to_payment_and_return(): void
    {
        // 1. Module Sales Quotation
        $quotation = SalesQuotation::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->user->id,
            'quotation_number' => 'QUO-2026-0001',
            'quotation_date' => now()->toDateString(),
            'expiry_date' => now()->addDays(14)->toDateString(),
            'channel' => 'GT',
            'status' => 'ACCEPTED',
            'subtotal' => 1200000,
            'discount_amount' => 0,
            'tax_amount' => 132000,
            'grand_total' => 1332000,
            'notes' => 'Penawaran harga spesial grosir',
        ]);

        $quotationItem = \App\Domain\Sales\Models\SalesQuotationItem::create([
            'sales_quotation_id' => $quotation->id,
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unit->id,
            'conversion_factor' => 24,
            'qty_ordered' => 10,
            'qty_in_base_uom' => 240,
            'unit_price' => 120000,
            'gross_amount' => 1200000,
            'discount_nominal' => 0,
            'net_amount' => 1200000,
        ]);

        $this->assertEquals($this->customer->id, $quotation->customer->id);
        $this->assertEquals(1, $quotation->items->count());

        // 2. Convert Quotation to Sales Order
        $convertService = app(ConvertQuotationToSalesOrderService::class);
        $salesOrder = $convertService->execute($quotation, $this->warehouse->id);

        $this->assertNotNull($salesOrder);
        $this->assertEquals('QUO-2026-0001', $salesOrder->quotation->quotation_number);
        $this->assertEquals($this->customer->id, $salesOrder->customer->id);
        $this->assertEquals($this->branch->id, $salesOrder->branch->id);
        $this->assertEquals(1, $salesOrder->items->count());

        // 3. Approve Sales Order
        $approveService = app(ApproveSalesOrderService::class);
        $salesOrder = $approveService->execute($salesOrder->id, $this->user->id);
        $this->assertEquals('APPROVED', $salesOrder->status);
        $this->assertEquals($this->user->id, $salesOrder->approver->id);

        // 4. Create Delivery Order from Sales Order
        $doService = app(CreateDeliveryOrderService::class);
        $deliveryOrder = $doService->execute(
            salesOrderId: $salesOrder->id,
            driverName: 'Sopir Budi',
            vehiclePlateNumber: 'B 1234 CD',
            deliveryType: 'DIRECT_STORE',
            notes: 'Kirim langsung',
            userId: $this->user->id
        );

        $this->assertNotNull($deliveryOrder);
        $this->assertEquals($salesOrder->id, $deliveryOrder->salesOrder->id);
        $this->assertEquals($this->warehouse->id, $deliveryOrder->warehouse->id);
        $this->assertEquals('DISPATCHED', $deliveryOrder->status);

        // Deliver DO
        $deliveryOrder->update([
            'status' => 'DELIVERED',
            'delivered_at' => now(),
            'recipient_name' => 'Pak Joko Toko',
        ]);
        $this->assertEquals('DELIVERED', $deliveryOrder->fresh()->status);

        // 5. Generate Invoice from Delivery Order
        $invoiceService = app(GenerateInvoiceService::class);
        $invoice = $invoiceService->execute($deliveryOrder->id);

        $this->assertNotNull($invoice);
        $this->assertEquals($deliveryOrder->id, $invoice->deliveryOrder->id);
        $this->assertEquals($salesOrder->id, $invoice->salesOrder->id);
        $this->assertEquals($this->customer->id, $invoice->customer->id);
        $this->assertEquals('UNPAID', $invoice->status);
        $this->assertGreaterThan(0, (float) $invoice->balance_due);

        // 6. Record Payment and Allocate to Invoice
        $paymentService = app(RecordPaymentService::class);
        $payment = $paymentService->execute(
            branchId: $this->branch->id,
            customerId: $this->customer->id,
            amount: (float) $invoice->grand_total,
            paymentMethod: 'BANK_TRANSFER',
            bankName: 'BCA',
            referenceNoGiro: 'TRF-BCA-TEST',
            userId: $this->user->id
        );

        $this->assertNotNull($payment);
        $this->assertCount(1, $payment->allocations);
        $this->assertEquals($invoice->id, $payment->allocations->first()->invoice->id);
        $this->assertEquals('PAID', $invoice->fresh()->status);
        $this->assertEquals(0, (float) $invoice->fresh()->balance_due);

        // 7. Sales Return (Process Return on Good Stock)
        $returnService = app(ProcessSalesReturnService::class);
        $salesReturn = $returnService->createReturn(
            branchId: $this->branch->id,
            customerId: $this->customer->id,
            warehouseId: $this->warehouse->id,
            returnType: 'GOOD_STOCK',
            items: [
                [
                    'product_id' => $this->product->id,
                    'product_unit_id' => $this->unit->id,
                    'batch_number' => 'LOT-SUSU-2026',
                    'qty' => 1,
                    'unit_price' => 120000.00,
                    'reason' => 'Kelebihan kirim 1 karton kondisi sempurna',
                ]
            ],
            notes: 'Retur kelebihan kirim',
            userId: $this->user->id
        );

        $this->assertNotNull($salesReturn);
        $this->assertEquals($this->customer->id, $salesReturn->customer->id);
        $this->assertEquals($this->warehouse->id, $salesReturn->warehouse->id);
        $this->assertEquals(1, $salesReturn->items->count());
    }

    public function test_ui_contains_zero_technical_jargon(): void
    {
        $techForbidden = [
            'PostgreSQL',
            'Redis 7',
            'Docker Compose',
            'Laravel 11',
            'PHP 8.3 FPM',
            'Granular RBAC',
            'JSON API',
        ];

        // 1. Check Login View
        $loginRes = $this->get('/login');
        $loginRes->assertStatus(200);
        foreach ($techForbidden as $tech) {
            $this->assertStringNotContainsString($tech, $loginRes->getContent(), "Technical term '{$tech}' leaked on /login");
        }

        // 2. Check Landing View
        $landingRes = $this->get('/');
        $landingRes->assertStatus(200);
        foreach ($techForbidden as $tech) {
            $this->assertStringNotContainsString($tech, $landingRes->getContent(), "Technical term '{$tech}' leaked on /");
        }

        // 3. Check Authenticated Dashboard
        $dashRes = $this->actingAs($this->user)->get('/dashboard');
        $dashRes->assertStatus(200);
        foreach ($techForbidden as $tech) {
            $this->assertStringNotContainsString($tech, $dashRes->getContent(), "Technical term '{$tech}' leaked on /dashboard");
        }

        // 4. Check Authenticated Users
        $usersRes = $this->actingAs($this->user)->get('/users');
        $usersRes->assertStatus(200);
        $this->assertStringNotContainsString('RBAC Role Profil', $usersRes->getContent());
        $this->assertStringNotContainsString('Jabatan & RBAC Roles', $usersRes->getContent());

        // 5. Check Authenticated Superadmin Dashboard
        $superRes = $this->actingAs($this->user)->get('/superadmin/dashboard');
        $superRes->assertStatus(200);
        $this->assertStringNotContainsString('PostgreSQL RLS', $superRes->getContent());
        $this->assertStringNotContainsString('Granular RBAC', $superRes->getContent());
    }
}
