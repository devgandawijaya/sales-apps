<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\Organization\Models\Branch;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_context_filters_customer_data_strictly(): void
    {
        // 1. Create Tenant A & Branch A
        $tenantA = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-A',
            'name' => 'Perusahaan Tenant A',
            'subdomain' => 'tenant-a',
            'status' => 'active',
            'plan_tier' => 'basic',
        ]);

        $branchA = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $tenantA->id,
            'code' => 'BR-A-01',
            'name' => 'Cabang Tenant A',
            'type' => 'BRANCH',
            'address' => 'Jl. A',
            'city' => 'Jakarta',
            'province' => 'DKI',
        ]);

        // 2. Create Tenant B & Branch B
        $tenantB = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-B',
            'name' => 'Perusahaan Tenant B',
            'subdomain' => 'tenant-b',
            'status' => 'active',
            'plan_tier' => 'basic',
        ]);

        $branchB = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $tenantB->id,
            'code' => 'BR-B-01',
            'name' => 'Cabang Tenant B',
            'type' => 'BRANCH',
            'address' => 'Jl. B',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
        ]);

        // 3. Create Customers belonging to Tenant A and Tenant B
        Customer::withoutGlobalScopes()->create([
            'tenant_id' => $tenantA->id,
            'branch_id' => $branchA->id,
            'code' => 'CUST-A-01',
            'name' => 'Pelanggan Rahasia Milik Tenant A',
            'channel' => 'GT',
            'sub_channel' => 'WARUNG',
            'address' => 'Jl. Kebon Jeruk No. 5',
            'city' => 'Jakarta Barat',
            'credit_limit' => 10000000,
        ]);

        Customer::withoutGlobalScopes()->create([
            'tenant_id' => $tenantB->id,
            'branch_id' => $branchB->id,
            'code' => 'CUST-B-01',
            'name' => 'Pelanggan Rahasia Milik Tenant B',
            'channel' => 'MT',
            'sub_channel' => 'SUPERMARKET',
            'address' => 'Jl. Darmo No. 100',
            'city' => 'Surabaya',
            'credit_limit' => 50000000,
        ]);

        // 4. Set Context to Tenant A
        TenantContext::setTenant($tenantA);

        $visibleCustomersTenantA = Customer::all();
        $this->assertCount(1, $visibleCustomersTenantA);
        $this->assertEquals('CUST-A-01', $visibleCustomersTenantA->first()->code);
        $this->assertFalse($visibleCustomersTenantA->contains('code', 'CUST-B-01'));

        // 5. Switch Context to Tenant B
        TenantContext::setTenant($tenantB);

        $visibleCustomersTenantB = Customer::all();
        $this->assertCount(1, $visibleCustomersTenantB);
        $this->assertEquals('CUST-B-01', $visibleCustomersTenantB->first()->code);
        $this->assertFalse($visibleCustomersTenantB->contains('code', 'CUST-A-01'));

        // Reset Context
        TenantContext::clear();
    }

    public function test_belongs_to_tenant_trait_auto_injects_tenant_id(): void
    {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-AUTO',
            'name' => 'Auto Tenant Corp',
            'subdomain' => 'auto-tenant',
            'status' => 'active',
            'plan_tier' => 'basic',
        ]);

        $branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'code' => 'BR-AUTO-01',
            'name' => 'Cabang Auto',
            'type' => 'BRANCH',
            'address' => 'Jl. Auto',
            'city' => 'Bandung',
            'province' => 'Jabar',
        ]);

        TenantContext::setTenant($tenant);

        $customer = Customer::create([
            'branch_id' => $branch->id,
            'code' => 'CUST-NEW-01',
            'name' => 'Toko Sembako Baru',
            'channel' => 'GT',
            'sub_channel' => 'WARUNG',
            'address' => 'Jl. Braga No. 12',
            'city' => 'Bandung',
            'credit_limit' => 5000000,
        ]);

        $this->assertEquals($tenant->id, $customer->tenant_id);

        TenantContext::clear();
    }
}

