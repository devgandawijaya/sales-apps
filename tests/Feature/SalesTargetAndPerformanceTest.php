<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Models\SalesQuotation;
use App\Domain\Sales\Models\SalesTarget;
use App\Domain\Sales\Services\SalesPerformanceService;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SalesTargetAndPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Warehouse $warehouse;
    protected User $salesman;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-PERF',
            'name' => 'PT Surya Distribusi Perkasa',
            'subdomain' => 'perf-test',
            'status' => 'active',
            'plan_tier' => 'enterprise',
        ]);

        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-03',
            'name' => 'Cabang Semarang',
            'type' => 'BRANCH',
            'address' => 'Jl. Pandanaran No. 50',
            'city' => 'Semarang',
            'province' => 'Jawa Tengah',
        ]);

        $this->warehouse = Warehouse::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-SMG',
            'name' => 'Gudang Semarang Kaligawe',
            'type' => 'CENTRAL_DC',
            'is_active' => true,
        ]);

        $this->salesman = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'role_title' => 'SALESMAN_GT',
            'name' => 'Dani Sales Pro',
            'email' => 'dani@suryadistribusi.co.id',
            'password' => bcrypt('password123'),
        ]);

        $this->customer = Customer::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'CUST-SMG-01',
            'name' => 'Toko Laris Manis Semarang',
            'channel' => 'GT',
            'sub_channel' => 'GROSIR',
            'address' => 'Jl. Pemuda No. 88',
            'city' => 'Semarang',
            'payment_type' => 'CREDIT',
            'term_of_payment' => 14,
            'credit_limit' => 200000000.00,
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    public function test_can_create_and_manage_sales_targets(): void
    {
        $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();

        $target = SalesTarget::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->salesman->id,
            'period_type' => 'MONTHLY',
            'period_date' => $currentMonthStart,
            'target_omset' => 120000000.00,
            'target_qty' => 800.0000,
            'target_new_customers' => 15,
            'notes' => 'Target ekspansi Jawa Tengah Q4.',
        ]);

        $this->assertDatabaseHas('sales_targets', [
            'id' => $target->id,
            'user_id' => $this->salesman->id,
            'target_omset' => 120000000.00,
            'target_new_customers' => 15,
        ]);
    }

    public function test_monthly_performance_computes_omzet_achievement_and_conversion_rate(): void
    {
        $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();

        // 1. Set Target Rp 100,000,000
        SalesTarget::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->salesman->id,
            'period_type' => 'MONTHLY',
            'period_date' => $currentMonthStart,
            'target_omset' => 100000000.00,
            'target_qty' => 500.0000,
            'target_new_customers' => 10,
        ]);

        // 2. Create 2 Approved Sales Orders totaling Rp 60,000,000
        SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->salesman->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-202610-0031',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'GT',
            'status' => 'APPROVED',
            'grand_total' => 25000000.00,
        ]);

        SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->salesman->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-202610-0032',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'GT',
            'status' => 'DELIVERED',
            'grand_total' => 35000000.00,
        ]);

        // Pending / draft order should not count into actual omzet
        SalesOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->salesman->id,
            'warehouse_id' => $this->warehouse->id,
            'so_number' => 'SO-202610-0033',
            'order_date' => Carbon::now()->toDateString(),
            'channel' => 'GT',
            'status' => 'PENDING_APPROVAL',
            'grand_total' => 15000000.00,
        ]);

        // 3. Create Quotations: 1 converted, 1 sent
        SalesQuotation::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->salesman->id,
            'quotation_number' => 'QUO-202610-0051',
            'quotation_date' => Carbon::now()->toDateString(),
            'expiry_date' => Carbon::now()->addDays(7)->toDateString(),
            'status' => 'CONVERTED_TO_SO',
            'grand_total' => 25000000.00,
        ]);

        SalesQuotation::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'salesman_id' => $this->salesman->id,
            'quotation_number' => 'QUO-202610-0052',
            'quotation_date' => Carbon::now()->toDateString(),
            'expiry_date' => Carbon::now()->addDays(7)->toDateString(),
            'status' => 'SENT',
            'grand_total' => 10000000.00,
        ]);

        // 4. Compute performance
        $perfService = app(SalesPerformanceService::class);
        $summary = $perfService->getMonthlyPerformance(Carbon::now()->toDateString());

        $this->assertEquals(60000000.00, $summary['actual_omzet']);
        $this->assertEquals(100000000.00, $summary['target_omzet']);
        $this->assertEquals(60.00, $summary['achievement_percent']);
        $this->assertEquals(2, $summary['total_orders']);

        // Quotation conversion rate: 1 of 2 = 50.0%
        $this->assertEquals(2, $summary['quotation_stats']['total']);
        $this->assertEquals(1, $summary['quotation_stats']['converted']);
        $this->assertEquals(50.0, $summary['quotation_stats']['conversion_rate']);

        // Leaderboard check
        $this->assertNotEmpty($summary['leaderboard']);
        $topSalesman = $summary['leaderboard']->first();
        $this->assertEquals($this->salesman->id, $topSalesman['salesman_id']);
        $this->assertEquals(60000000.00, $topSalesman['total_omzet']);
        $this->assertEquals(60.0, $topSalesman['achievement_percent']);
    }
}

