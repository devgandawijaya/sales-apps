<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Company;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Security\Models\AuditLog;
use App\Domain\Security\Models\Role;
use App\Domain\Tenant\Models\Subscription;
use App\Domain\Tenant\Models\SubscriptionPlan;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantManagerService
{
    /**
     * Register a new Tenant with automated initial provisioning.
     */
    public function registerTenant(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $plan = SubscriptionPlan::where('code', $data['plan_code'] ?? 'TRIAL-14D')->first()
                ?? SubscriptionPlan::first();

            // 1. Create Tenant
            $tenant = Tenant::create([
                'code' => $data['code'] ?? 'TEN-' . strtoupper(Str::random(6)),
                'name' => $data['company_name'],
                'subdomain' => Str::slug($data['subdomain']),
                'custom_domain' => $data['custom_domain'] ?? null,
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
                'plan_tier' => $plan ? $plan->tier : 'trial',
                'max_users' => $plan ? $plan->max_users : 3,
                'max_companies' => $plan ? $plan->max_companies : 1,
                'max_branches' => $plan ? $plan->max_branches : 1,
                'max_warehouses' => $plan ? $plan->max_warehouses : 1,
                'storage_limit_mb' => $plan ? $plan->storage_limit_mb : 500,
                'settings' => [
                    'currency' => 'IDR',
                    'timezone' => 'Asia/Jakarta',
                    'date_format' => 'd/m/Y',
                ],
            ]);

            // 2. Create Initial Subscription
            if ($plan) {
                Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'billing_cycle' => 'monthly',
                    'current_period_start' => now()->toDateString(),
                    'current_period_end' => now()->addDays(14)->toDateString(),
                    'amount' => $plan->price_monthly,
                ]);
            }

            // 3. Create Initial Holding Company
            $company = Company::create([
                'tenant_id' => $tenant->id,
                'code' => 'CMP-' . strtoupper(Str::random(4)),
                'name' => $data['company_name'],
                'tax_id_npwp' => $data['tax_id_npwp'] ?? null,
                'currency' => 'IDR',
                'address' => $data['address'] ?? 'Kantor Pusat',
                'city' => $data['city'] ?? 'Jakarta',
                'is_active' => true,
            ]);

            // 4. Create Initial Head Office Branch
            $branch = Branch::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'company_id' => $company->id,
                'code' => 'BR-HQ-' . strtoupper(Str::random(3)),
                'name' => 'Kantor Pusat ' . $data['company_name'],
                'type' => 'HEAD_OFFICE',
                'address' => $data['address'] ?? 'Kantor Pusat',
                'city' => $data['city'] ?? 'Jakarta',
                'province' => $data['province'] ?? 'DKI Jakarta',
                'is_active' => true,
            ]);

            // 5. Create Initial Main Warehouse
            $warehouse = Warehouse::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'code' => 'WH-MAIN-' . strtoupper(Str::random(3)),
                'name' => 'Gudang Utama Pusat',
                'type' => 'CENTRAL_DC',
                'address' => $data['address'] ?? 'Gudang Utama Pusat',
                'is_active' => true,
            ]);

            // 6. Create Owner User
            $owner = User::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'phone' => $data['admin_phone'] ?? null,
                'role_title' => 'COMPANY_OWNER',
                'channel_assignment' => 'ALL',
                'status' => 'active',
                'is_active' => true,
            ]);

            // Assign Owner Role if exists
            $ownerRole = Role::where('slug', 'company-owner')->first();
            if ($ownerRole) {
                $owner->roles()->syncWithoutDetaching([$ownerRole->id]);
            }

            // 7. Audit Log
            AuditLog::create([
                'tenant_id' => $tenant->id,
                'user_id' => $owner->id,
                'action' => 'TENANT_ONBOARDED',
                'entity_type' => 'Tenant',
                'entity_id' => (string) $tenant->id,
                'new_values' => [
                    'tenant_name' => $tenant->name,
                    'subdomain' => $tenant->subdomain,
                    'plan' => $tenant->plan_tier,
                ],
                'created_at' => now(),
            ]);

            return [
                'tenant' => $tenant,
                'company' => $company,
                'branch' => $branch,
                'warehouse' => $warehouse,
                'user' => $owner,
            ];
        });
    }
}

