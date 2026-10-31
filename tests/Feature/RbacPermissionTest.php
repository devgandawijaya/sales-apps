<?php

namespace Tests\Feature;

use App\Domain\Security\Models\Permission;
use App\Domain\Security\Models\Role;
use App\Domain\Security\Services\RbacService;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RbacPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_permission_evaluates_correctly(): void
    {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-RBAC',
            'name' => 'RBAC Test Tenant',
            'subdomain' => 'rbac-test',
            'status' => 'active',
        ]);

        $permSalesCreate = Permission::create([
            'module' => 'sales',
            'action' => 'create',
            'name' => 'Create Sales Order',
        ]);

        $permInventoryAdjust = Permission::create([
            'module' => 'inventory',
            'action' => 'adjust_stock',
            'name' => 'Adjust Stock',
        ]);

        $roleSales = Role::create([
            'name' => 'Sales Role',
            'slug' => 'sales-role',
        ]);
        $roleSales->permissions()->sync([$permSalesCreate->id]);

        $salesUser = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Salesman Budi',
            'email' => 'budi@rbac.com',
            'password' => bcrypt('password'),
            'role_title' => 'SALESMAN_GT',
        ]);
        $salesUser->roles()->sync([$roleSales->id]);

        $rbac = app(RbacService::class);

        // Salesman has permission to create sales order
        $this->assertTrue($rbac->userHasPermission($salesUser, 'sales', 'create'));

        // Salesman does NOT have permission to adjust inventory stock
        $this->assertFalse($rbac->userHasPermission($salesUser, 'inventory', 'adjust_stock'));
    }

    public function test_superadmin_bypasses_all_permission_checks(): void
    {
        $superadmin = User::withoutGlobalScopes()->create([
            'name' => 'Global Superadmin',
            'email' => 'super@saas.com',
            'password' => bcrypt('password'),
            'role_title' => 'SUPERADMIN',
        ]);

        $rbac = app(RbacService::class);

        $this->assertTrue($superadmin->isSuperAdmin());
        $this->assertTrue($rbac->userHasPermission($superadmin, 'any_module', 'any_action'));
    }
}

