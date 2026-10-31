<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\Pricing\Exceptions\PriceNotFoundException;
use App\Domain\Product\Models\Product;
use App\Domain\Sales\Exceptions\CreditLimitExceededException;
use App\Domain\Sales\Exceptions\CustomerInactiveException;
use App\Domain\Sales\Exceptions\InsufficientStockException;
use App\Domain\Sales\Exceptions\OverdueInvoiceBlockedException;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use App\Support\HumanError;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteAuditAndHumanErrorTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'PT Nusantara Pangan Mandiri',
            'code' => 'NPM-01',
            'subdomain' => 'nusantara-pangan',
            'status' => 'active',
            'plan_tier' => 'enterprise',
            'max_users' => 100,
            'max_branches' => 10,
            'max_warehouses' => 20,
        ]);

        TenantContext::setTenant($this->tenant);

        $branch = \App\Domain\Organization\Models\Branch::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-TEST',
            'name' => 'Cabang Uji Coba',
            'address' => 'Jl. Uji Coba No. 1',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Super Administrator',
            'email' => 'admin.test@sales.co.id',
            'password' => bcrypt('password'),
            'role_title' => 'SUPERADMIN',
            'is_active' => true,
        ]);
    }

    public function test_human_error_translates_model_not_found_cleanly(): void
    {
        $e = new ModelNotFoundException();
        $e->setModel(Customer::class);

        $translated = HumanError::translate($e);
        $this->assertStringContainsString('Data pelanggan tidak ditemukan', $translated);
        $this->assertStringNotContainsString('App\\Domain', $translated);
        $this->assertStringNotContainsString('No query results', $translated);
    }

    public function test_human_error_translates_domain_exceptions_cleanly(): void
    {
        $stockEx = new InsufficientStockException('PRD-KOPI', 'Kopi Susu Aren', 10, 5);
        $this->assertStringContainsString('Stok barang \'Kopi Susu Aren\'', HumanError::translate($stockEx));
        $this->assertStringNotContainsString('base UOM', HumanError::translate($stockEx));

        $creditEx = new CreditLimitExceededException('Toko Berkah', 5000000, 2000000, 6000000);
        $this->assertStringContainsString('melebihi batas kredit', HumanError::translate($creditEx));
        $this->assertStringNotContainsString('Plafon kredit', HumanError::translate($creditEx));

        $inactiveEx = new CustomerInactiveException('CUST-001');
        $this->assertStringContainsString('sedang tidak aktif', HumanError::translate($inactiveEx));

        $overdueEx = new OverdueInvoiceBlockedException('Toko Berkah', 'INV-001', 15, 1000000);
        $this->assertStringContainsString('tagihan lewat jatuh tempo', HumanError::translate($overdueEx));
        $this->assertStringNotContainsString('DIBLOKIR', HumanError::translate($overdueEx));
    }

    public function test_human_error_sanitizes_raw_sqlstate_and_internal_errors(): void
    {
        $sqlEx = new \RuntimeException('SQLSTATE[42P01]: Undefined table: relation "customers" does not exist');
        $translated = HumanError::translate($sqlEx);

        $this->assertStringNotContainsString('SQLSTATE', $translated);
        $this->assertStringContainsString('Terjadi kendala saat memproses permintaan', $translated);
    }

    public function test_sales_approvals_route_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get('/sales-approvals');
        $response->assertStatus(200);
        $response->assertSee('Persetujuan Sales Order');
    }

    public function test_landing_page_renders_successfully_before_login(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Sales & Distribution', false);
        $response->assertSee('Masuk ke Sistem');
        $response->assertSee('phobehub.com');
    }

    public function test_login_page_renders_and_links_back_to_landing_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk ke Akun Anda');
        $response->assertSee('Kembali ke Beranda Utama');
        $response->assertSee('phobehub.com');
    }
}
