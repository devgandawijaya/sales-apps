<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Validation\ValidationException;

class QuotaEnforcerService
{
    /**
     * Check if a tenant can add more users.
     *
     * @throws ValidationException
     */
    public function validateCanAddUser(Tenant $tenant): void
    {
        $currentCount = $tenant->users()->count();
        if ($currentCount >= $tenant->max_users) {
            throw ValidationException::withMessages([
                'quota' => "Batas kuota user untuk paket langganan Anda telah tercapai (Maksimal {$tenant->max_users} user). Silakan upgrade paket langganan Anda."
            ]);
        }
    }

    /**
     * Check if a tenant can add more branches.
     *
     * @throws ValidationException
     */
    public function validateCanAddBranch(Tenant $tenant): void
    {
        $currentCount = $tenant->branches()->count();
        if ($currentCount >= $tenant->max_branches) {
            throw ValidationException::withMessages([
                'quota' => "Batas kuota cabang untuk paket langganan Anda telah tercapai (Maksimal {$tenant->max_branches} cabang). Silakan upgrade paket langganan Anda."
            ]);
        }
    }

    /**
     * Check if a tenant can add more warehouses.
     *
     * @throws ValidationException
     */
    public function validateCanAddWarehouse(Tenant $tenant): void
    {
        $currentCount = $tenant->warehouses()->count();
        if ($currentCount >= $tenant->max_warehouses) {
            throw ValidationException::withMessages([
                'quota' => "Batas kuota gudang untuk paket langganan Anda telah tercapai (Maksimal {$tenant->max_warehouses} gudang). Silakan upgrade paket langganan Anda."
            ]);
        }
    }

    /**
     * Check if a tenant can add more companies.
     *
     * @throws ValidationException
     */
    public function validateCanAddCompany(Tenant $tenant): void
    {
        $currentCount = $tenant->companies()->count();
        if ($currentCount >= $tenant->max_companies) {
            throw ValidationException::withMessages([
                'quota' => "Batas kuota entitas perusahaan untuk paket langganan Anda telah tercapai (Maksimal {$tenant->max_companies} perusahaan). Silakan upgrade paket langganan Anda."
            ]);
        }
    }

    /**
     * Get usage summary for a tenant.
     */
    public function getUsageSummary(Tenant $tenant): array
    {
        $usersCount = $tenant->users()->count();
        $branchesCount = $tenant->branches()->count();
        $warehousesCount = $tenant->warehouses()->count();
        $companiesCount = $tenant->companies()->count();

        return [
            'users' => [
                'used' => $usersCount,
                'limit' => $tenant->max_users,
                'percentage' => $tenant->max_users > 0 ? round(($usersCount / $tenant->max_users) * 100, 1) : 0,
            ],
            'companies' => [
                'used' => $companiesCount,
                'limit' => $tenant->max_companies,
                'percentage' => $tenant->max_companies > 0 ? round(($companiesCount / $tenant->max_companies) * 100, 1) : 0,
            ],
            'branches' => [
                'used' => $branchesCount,
                'limit' => $tenant->max_branches,
                'percentage' => $tenant->max_branches > 0 ? round(($branchesCount / $tenant->max_branches) * 100, 1) : 0,
            ],
            'warehouses' => [
                'used' => $warehousesCount,
                'limit' => $tenant->max_warehouses,
                'percentage' => $tenant->max_warehouses > 0 ? round(($warehousesCount / $tenant->max_warehouses) * 100, 1) : 0,
            ],
        ];
    }
}

