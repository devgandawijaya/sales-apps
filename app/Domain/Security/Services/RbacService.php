<?php

namespace App\Domain\Security\Services;

use App\Domain\Security\Models\Permission;
use App\Domain\Security\Models\Role;
use App\Models\User;

class RbacService
{
    /**
     * Modules available in the system.
     */
    public const MODULES = [
        'tenants' => 'SaaS Tenant Management',
        'organization' => 'Company, Branch & Warehouse',
        'users' => 'User & Access Control',
        'crm' => 'Customer & Agreement',
        'products' => 'Products & Price Lists',
        'sales' => 'Sales Orders & Pipeline',
        'inventory' => 'Inventory & Stock FEFO',
        'warehouse' => 'WMS & Inbound Receiving',
        'finance' => 'Invoices, AR Aging & Payments',
        'reports' => 'Reports & Business Intelligence',
        'audit' => 'Audit Trail & Compliance',
    ];

    /**
     * Standard granular actions.
     */
    public const ACTIONS = [
        'view' => 'Melihat data modul',
        'create' => 'Membuat data baru',
        'edit' => 'Mengubah data',
        'delete' => 'Menghapus data',
        'approve' => 'Menyetujui transaksi',
        'reject' => 'Menolak transaksi',
        'export' => 'Mengekspor data ke Excel/PDF',
        'print' => 'Mencetak dokumen',
        'cancel' => 'Membatalkan transaksi',
        'adjust_stock' => 'Melakukan penyesuaian stok',
        'manage_settings' => 'Mengelola pengaturan konfigurasi',
    ];

    /**
     * Seed or sync all standard system permissions.
     */
    public function syncStandardPermissions(): void
    {
        foreach (self::MODULES as $module => $moduleDesc) {
            foreach (self::ACTIONS as $action => $actionDesc) {
                Permission::firstOrCreate(
                    ['module' => $module, 'action' => $action],
                    [
                        'name' => ucfirst($action) . " {$moduleDesc}",
                        'description' => "Hak akses untuk {$actionDesc} pada modul {$moduleDesc}",
                    ]
                );
            }
        }
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(User $user, string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        $user->roles()->syncWithoutDetaching([$role->id]);
    }

    /**
     * Remove a role from a user.
     */
    public function removeRole(User $user, string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->first();
        if ($role) {
            $user->roles()->detach($role->id);
        }
    }

    /**
     * Check if a user has a specific permission.
     */
    public function userHasPermission(User $user, string $module, string $action): bool
    {
        // Superadmin bypass
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Check across all user's assigned roles
        foreach ($user->roles as $role) {
            if ($role->hasPermission($module, $action)) {
                return true;
            }
        }

        // Check fallback role_title for backward compatibility
        if ($this->checkLegacyRolePermission($user->role_title, $module, $action)) {
            return true;
        }

        return false;
    }

    /**
     * Fallback permission check based on legacy role_title.
     */
    protected function checkLegacyRolePermission(?string $roleTitle, string $module, string $action): bool
    {
        if (!$roleTitle) {
            return false;
        }

        $role = strtoupper($roleTitle);

        if ($role === 'SUPERADMIN') {
            return true;
        }

        if ($role === 'COMPANY_OWNER') {
            return true;
        }

        if ($role === 'BRANCH_MANAGER') {
            return in_array($module, ['organization', 'sales', 'inventory', 'customers', 'reports', 'finance'])
                && in_array($action, ['view', 'create', 'edit', 'approve', 'reject', 'export', 'print']);
        }

        if ($role === 'SALES_ADMIN' || $role === 'KAM_MT' || $role === 'SALESMAN_GT') {
            return in_array($module, ['sales', 'customers', 'products', 'reports'])
                && in_array($action, ['view', 'create', 'edit', 'print', 'export']);
        }

        if ($role === 'WAREHOUSE_KEEPER') {
            return in_array($module, ['inventory', 'warehouse', 'products', 'reports'])
                && in_array($action, ['view', 'create', 'edit', 'adjust_stock', 'print']);
        }

        if ($role === 'FINANCE') {
            return in_array($module, ['finance', 'sales', 'customers', 'reports'])
                && in_array($action, ['view', 'create', 'edit', 'approve', 'reject', 'print', 'export']);
        }

        return false;
    }
}

