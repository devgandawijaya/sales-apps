<?php

namespace Tests\Feature;

use App\Domain\Tenant\Models\SubscriptionPlan;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Services\QuotaEnforcerService;
use App\Domain\Tenant\Services\TenantManagerService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class QuotaEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_quota_service_blocks_adding_users_when_limit_reached(): void
    {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-QUOTA',
            'name' => 'Quota Limited Tenant',
            'subdomain' => 'quota-test',
            'status' => 'active',
            'max_users' => 2, // Quota is 2
        ]);

        // Add 2 users
        User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'User 1',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
            'role_title' => 'SALES_ADMIN',
        ]);

        User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'User 2',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
            'role_title' => 'FINANCE',
        ]);

        $quotaService = new QuotaEnforcerService();

        // Adding 3rd user should throw ValidationException
        $this->expectException(ValidationException::class);
        $quotaService->validateCanAddUser($tenant);
    }

    public function test_tenant_manager_service_provisions_complete_tenant_hierarchy(): void
    {
        // Ensure plan exists
        SubscriptionPlan::create([
            'code' => 'TRIAL-14D',
            'name' => 'Free Trial',
            'tier' => 'trial',
            'max_users' => 3,
            'max_companies' => 1,
            'max_branches' => 1,
            'max_warehouses' => 1,
        ]);

        $service = new TenantManagerService();

        $result = $service->registerTenant([
            'company_name' => 'PT Distribusi Unggul Nusantara',
            'subdomain' => 'unggul-nusantara',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'address' => 'Jl. Rungkut Industri No. 8',
            'admin_name' => 'Hendra Setiawan',
            'admin_email' => 'hendra@unggul.co.id',
            'admin_password' => 'SecretPass2026!',
            'plan_code' => 'TRIAL-14D',
        ]);

        $this->assertNotNull($result['tenant']);
        $this->assertEquals('PT Distribusi Unggul Nusantara', $result['tenant']->name);
        $this->assertEquals('unggul-nusantara', $result['tenant']->subdomain);
        $this->assertNotNull($result['company']);
        $this->assertNotNull($result['branch']);
        $this->assertNotNull($result['warehouse']);
        $this->assertNotNull($result['user']);
        $this->assertEquals('hendra@unggul.co.id', $result['user']->email);
        $this->assertEquals('COMPANY_OWNER', $result['user']->role_title);
    }
}

