<?php

namespace Database\Seeders;

use App\Domain\Security\Models\Permission;
use App\Domain\Security\Models\Role;
use App\Domain\Security\Services\RbacService;
use App\Domain\Tenant\Models\Subscription;
use App\Domain\Tenant\Models\SubscriptionPlan;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaasFoundationSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // 1. SAAS SUBSCRIPTION PLANS
        // =====================================================================
        $plans = [
            [
                'code' => 'TRIAL-14D',
                'name' => 'Free Trial (14 Hari)',
                'tier' => 'trial',
                'price_monthly' => 0.00,
                'price_annual' => 0.00,
                'max_users' => 3,
                'max_companies' => 1,
                'max_branches' => 1,
                'max_warehouses' => 1,
                'storage_limit_mb' => 500,
                'features' => json_encode(['basic_sales', 'basic_inventory', 'standard_reports']),
                'is_active' => true,
            ],
            [
                'code' => 'PLAN-BASIC',
                'name' => 'Basic Tier',
                'tier' => 'basic',
                'price_monthly' => 499000.00,
                'price_annual' => 4990000.00,
                'max_users' => 5,
                'max_companies' => 1,
                'max_branches' => 2,
                'max_warehouses' => 2,
                'storage_limit_mb' => 2000,
                'features' => json_encode(['basic_sales', 'basic_inventory', 'standard_reports', 'export_excel']),
                'is_active' => true,
            ],
            [
                'code' => 'PLAN-PRO',
                'name' => 'Professional Tier',
                'tier' => 'professional',
                'price_monthly' => 1499000.00,
                'price_annual' => 14990000.00,
                'max_users' => 20,
                'max_companies' => 1,
                'max_branches' => 5,
                'max_warehouses' => 5,
                'storage_limit_mb' => 10000,
                'features' => json_encode(['all_basic', 'modern_trade', 'commission_engine', 'approval_single_level', 'api_access']),
                'is_active' => true,
            ],
            [
                'code' => 'PLAN-BIZ',
                'name' => 'Business Tier',
                'tier' => 'business',
                'price_monthly' => 3999000.00,
                'price_annual' => 39990000.00,
                'max_users' => 50,
                'max_companies' => 3,
                'max_branches' => 15,
                'max_warehouses' => 20,
                'storage_limit_mb' => 50000,
                'features' => json_encode(['all_pro', 'multi_level_approval', 'advanced_wms', 'full_audit_trail', 'priority_queue']),
                'is_active' => true,
            ],
            [
                'code' => 'PLAN-ENT',
                'name' => 'Enterprise Custom',
                'tier' => 'enterprise',
                'price_monthly' => 9999000.00,
                'price_annual' => 99990000.00,
                'max_users' => 9999,
                'max_companies' => 9999,
                'max_branches' => 9999,
                'max_warehouses' => 9999,
                'storage_limit_mb' => 500000,
                'features' => json_encode(['unlimited', 'dedicated_database_option', 'sla_99_95', 'custom_integration', 'dedicated_support']),
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            DB::table('subscription_plans')->updateOrInsert(
                ['code' => $planData['code']],
                array_merge($planData, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // =====================================================================
        // 2. STANDARD PERMISSIONS & 19 ROLES
        // =====================================================================
        $rbacService = app(RbacService::class);
        $rbacService->syncStandardPermissions();

        // 19 Standard Roles
        $standardRoles = [
            ['name' => 'Super Admin SaaS', 'slug' => 'super-admin', 'desc' => 'Pengelola platform SaaS global'],
            ['name' => 'Company Owner', 'slug' => 'company-owner', 'desc' => 'Pemilik perusahaan tenant dengan akses penuh bisnis'],
            ['name' => 'Director', 'slug' => 'director', 'desc' => 'Direksi eksekutif pemantau laporan finansial & strategi'],
            ['name' => 'Administrator', 'slug' => 'administrator', 'desc' => 'Administrator sistem di tingkat tenant'],
            ['name' => 'General Manager', 'slug' => 'general-manager', 'desc' => 'Pimpinan operasional umum lintas cabang'],
            ['name' => 'Branch Manager', 'slug' => 'branch-manager', 'desc' => 'Kepala kantor cabang operasional'],
            ['name' => 'Sales Manager', 'slug' => 'sales-manager', 'desc' => 'Manajer divisi penjualan'],
            ['name' => 'Sales Supervisor', 'slug' => 'sales-supervisor', 'desc' => 'Pengawas tim sales dan target lapangan'],
            ['name' => 'Sales Representative', 'slug' => 'sales-representative', 'desc' => 'Staf sales Taking Order & Kanvas'],
            ['name' => 'Warehouse Manager', 'slug' => 'warehouse-manager', 'desc' => 'Kepala pergudangan & logistik'],
            ['name' => 'Warehouse Staff', 'slug' => 'warehouse-staff', 'desc' => 'Staf gudang (receiving, picking, packing)'],
            ['name' => 'Purchasing Staff', 'slug' => 'purchasing-staff', 'desc' => 'Staf pengadaan barang dan vendor PO'],
            ['name' => 'Finance Manager', 'slug' => 'finance-manager', 'desc' => 'Manajer keuangan, penagihan AR dan perpajakan'],
            ['name' => 'Finance Staff', 'slug' => 'finance-staff', 'desc' => 'Staf billing faktur dan rekonsiliasi kas/bank'],
            ['name' => 'Delivery Manager', 'slug' => 'delivery-manager', 'desc' => 'Manajer pengiriman dan armada ekspedisi'],
            ['name' => 'Driver / Kurir', 'slug' => 'driver', 'desc' => 'Kurir armada pengantar barang dan upload POD'],
            ['name' => 'HR / Admin Karyawan', 'slug' => 'hr-admin', 'desc' => 'Pengelola data karyawan dan tim sales'],
            ['name' => 'Auditor Internal', 'slug' => 'auditor', 'desc' => 'Auditor kepatuhan dan review riwayat transaksi'],
            ['name' => 'Viewer / Read Only', 'slug' => 'viewer', 'desc' => 'Akses hanya melihat data tanpa wewenang mutasi'],
        ];

        foreach ($standardRoles as $r) {
            $role = Role::firstOrCreate(
                ['slug' => $r['slug'], 'tenant_id' => null],
                [
                    'name' => $r['name'],
                    'description' => $r['desc'],
                    'is_system' => true,
                ]
            );

            // Assign standard permissions to role
            $this->assignDefaultPermissionsToRole($role, $r['slug']);
        }

        // =====================================================================
        // 3. SEED DEFAULT DEMO TENANTS
        // =====================================================================
        // Tenant A: PT Nusantara Pangan Mandiri (Business Tier)
        $bizPlan = DB::table('subscription_plans')->where('code', 'PLAN-BIZ')->first();
        $tenantAId = (string) Str::uuid();

        DB::table('tenants')->updateOrInsert(
            ['subdomain' => 'nusantara-pangan'],
            [
                'id' => $tenantAId,
                'code' => 'TEN-NUSANTARA-01',
                'name' => 'PT Nusantara Pangan Mandiri',
                'subdomain' => 'nusantara-pangan',
                'custom_domain' => 'pangan.enterprisesales.co.id',
                'status' => 'active',
                'trial_ends_at' => now()->addMonths(6),
                'plan_tier' => 'business',
                'max_users' => 50,
                'max_companies' => 3,
                'max_branches' => 15,
                'max_warehouses' => 20,
                'storage_limit_mb' => 50000,
                'settings' => json_encode(['currency' => 'IDR', 'timezone' => 'Asia/Jakarta']),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Fetch real ID in case it already existed
        $tenantA = DB::table('tenants')->where('subdomain', 'nusantara-pangan')->first();
        $tenantAId = $tenantA->id;

        // Subscription A
        if ($bizPlan) {
            DB::table('subscriptions')->updateOrInsert(
                ['tenant_id' => $tenantAId],
                [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantAId,
                    'plan_id' => $bizPlan->id,
                    'status' => 'active',
                    'billing_cycle' => 'annual',
                    'current_period_start' => now()->startOfYear()->toDateString(),
                    'current_period_end' => now()->endOfYear()->toDateString(),
                    'amount' => $bizPlan->price_annual,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Holding Company A
        $companyAId = DB::table('companies')->insertGetId([
            'tenant_id' => $tenantAId,
            'code' => 'CMP-NPM-HOLDING',
            'name' => 'PT Nusantara Pangan Mandiri Holding',
            'tax_id_npwp' => '01.234.567.8-012.000',
            'currency' => 'IDR',
            'address' => 'Gedung Menara Sudirman Lt. 28, SCBD',
            'city' => 'Jakarta Selatan',
            'country' => 'Indonesia',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tenant B: PT Java Logistik Distribusi (Basic Tier - for isolation testing)
        $basicPlan = DB::table('subscription_plans')->where('code', 'PLAN-BASIC')->first();
        $tenantBId = (string) Str::uuid();

        DB::table('tenants')->updateOrInsert(
            ['subdomain' => 'java-logistik'],
            [
                'id' => $tenantBId,
                'code' => 'TEN-JAVA-02',
                'name' => 'PT Java Logistik Distribusi',
                'subdomain' => 'java-logistik',
                'status' => 'active',
                'trial_ends_at' => now()->addDays(14),
                'plan_tier' => 'basic',
                'max_users' => 5,
                'max_companies' => 1,
                'max_branches' => 2,
                'max_warehouses' => 2,
                'storage_limit_mb' => 2000,
                'settings' => json_encode(['currency' => 'IDR', 'timezone' => 'Asia/Jakarta']),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $tenantB = DB::table('tenants')->where('subdomain', 'java-logistik')->first();
        $tenantBId = $tenantB->id;

        if ($basicPlan) {
            DB::table('subscriptions')->updateOrInsert(
                ['tenant_id' => $tenantBId],
                [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantBId,
                    'plan_id' => $basicPlan->id,
                    'status' => 'active',
                    'billing_cycle' => 'monthly',
                    'current_period_start' => now()->toDateString(),
                    'current_period_end' => now()->addMonth()->toDateString(),
                    'amount' => $basicPlan->price_monthly,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // =====================================================================
        // 4. ATTACH TENANT_ID TO EXISTING SEEDED BUSINESS DATA
        // =====================================================================
        DB::table('branches')->whereNull('tenant_id')->update([
            'tenant_id' => $tenantAId,
            'company_id' => $companyAId,
        ]);

        DB::table('warehouses')->whereNull('tenant_id')->update([
            'tenant_id' => $tenantAId,
        ]);

        DB::table('users')->whereNull('tenant_id')->update([
            'tenant_id' => $tenantAId,
        ]);

        DB::table('customers')->whereNull('tenant_id')->update([
            'tenant_id' => $tenantAId,
        ]);

        DB::table('products')->whereNull('tenant_id')->update([
            'tenant_id' => $tenantAId,
        ]);

        DB::table('sales_orders')->whereNull('tenant_id')->update([
            'tenant_id' => $tenantAId,
        ]);

        DB::table('stocks')->whereNull('tenant_id')->update([
            'tenant_id' => $tenantAId,
        ]);

        DB::table('invoices')->whereNull('tenant_id')->update([
            'tenant_id' => $tenantAId,
        ]);

        DB::table('payments')->whereNull('tenant_id')->update([
            'tenant_id' => $tenantAId,
        ]);

        // Map seeded users to their RBAC Roles
        $this->mapUsersToRoles();

        // Phase 2: Seed Demo Sales Targets, Quotation & Approval Orders
        $this->seedPhase2SalesDemo($tenantAId);

        // Phase 3: Seed Demo Multi-Warehouse Transfers, Opname & Immutable Ledger
        $this->seedPhase3InventoryDemo($tenantAId);
    }

    /**
     * Seed Phase 3 demo records for Transfers, Opname, and Stock Ledger.
     */
    protected function seedPhase3InventoryDemo(string $tenantId): void
    {
        $whSource = DB::table('warehouses')->first();
        $whDest = DB::table('warehouses')->where('id', '!=', $whSource?->id)->first();
        $product = DB::table('products')->first();
        $unit = $product ? DB::table('product_units')->where('product_id', $product->id)->first() : null;
        $user = DB::table('users')->where('email', 'warehouse.jkt@enterprisesales.co.id')->first();

        if ($whSource && $whDest && $product && $unit && $user) {
            // 1. Sample Inter-Branch Transfer (IN_TRANSIT)
            $trfNumber = 'TRF-202610-00101';
            $existingTrf = DB::table('stock_transfers')->where('transfer_number', $trfNumber)->first();

            if (!$existingTrf) {
                $trfId = DB::table('stock_transfers')->insertGetId([
                    'tenant_id' => $tenantId,
                    'transfer_number' => $trfNumber,
                    'from_warehouse_id' => $whSource->id,
                    'to_warehouse_id' => $whDest->id,
                    'driver_name' => 'Pak Joko Widodo',
                    'vehicle_number' => 'B 9876 CKG',
                    'status' => 'IN_TRANSIT',
                    'approval_level' => 1,
                    'approved_by' => $user->id,
                    'approved_at' => Carbon::now()->subHours(4),
                    'sent_date' => Carbon::now()->subHours(3),
                    'dispatched_at' => Carbon::now()->subHours(3),
                    'notes' => 'Pengiriman reguler replenishment stok cabang Surabaya.',
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('stock_transfer_items')->insert([
                    'stock_transfer_id' => $trfId,
                    'product_id' => $product->id,
                    'product_unit_id' => $unit->id,
                    'batch_number' => 'LOT-2026-FMCG',
                    'expiry_date' => Carbon::now()->addMonths(8)->toDateString(),
                    'qty_sent' => 50,
                    'qty_received' => 0,
                    'discrepancy_qty' => 0,
                    'condition_status' => 'GOOD',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 2. Sample Stock Opname (SUBMITTED)
            $opnNumber = 'OPN-202610-00101';
            $existingOpn = DB::table('stock_opnames')->where('opname_number', $opnNumber)->first();

            if (!$existingOpn) {
                $opnId = DB::table('stock_opnames')->insertGetId([
                    'tenant_id' => $tenantId,
                    'branch_id' => $whSource->branch_id ?? 1,
                    'warehouse_id' => $whSource->id,
                    'opname_number' => $opnNumber,
                    'title' => 'Cycle Count Triwulan IV 2026 - Minuman & FMCG',
                    'period_date' => Carbon::now()->toDateString(),
                    'status' => 'SUBMITTED',
                    'count_type' => 'CYCLE_COUNT',
                    'blind_count' => false,
                    'total_items_counted' => 1,
                    'total_discrepancy_qty' => -2,
                    'total_cogs_variance' => -6000.00,
                    'created_by' => $user->id,
                    'notes' => 'Pemeriksaan fisik rak Aisle 01-03.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('stock_opname_items')->insert([
                    'stock_opname_id' => $opnId,
                    'product_id' => $product->id,
                    'batch_number' => 'LOT-2026-FMCG',
                    'expiry_date' => Carbon::now()->addMonths(8)->toDateString(),
                    'system_qty' => 100,
                    'physical_qty' => 98,
                    'discrepancy_qty' => -2,
                    'unit_cost' => 3000.00,
                    'discrepancy_cogs_value' => -6000.00,
                    'reason_code' => 'DAMAGED',
                    'notes' => '2 kaleng bocor saat handling forklift.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 3. Sample Immutable Stock Ledger Entries
            $existingLedger = DB::table('stock_ledger_entries')->where('tenant_id', $tenantId)->first();
            if (!$existingLedger) {
                DB::table('stock_ledger_entries')->insert([
                    [
                        'tenant_id' => $tenantId,
                        'warehouse_id' => $whSource->id,
                        'product_id' => $product->id,
                        'batch_number' => 'LOT-2026-FMCG',
                        'entry_type' => 'IN',
                        'reference_type' => 'GOODS_RECEIPT',
                        'reference_id' => 1,
                        'reference_number' => 'GRN-202610-0001',
                        'qty_change' => 200,
                        'qty_before' => 0,
                        'qty_after' => 200,
                        'unit_cost' => 3000.00,
                        'total_cost' => 600000.00,
                        'notes' => 'Penerimaan Inbound Pabrik',
                        'created_by' => $user->id,
                        'created_at' => Carbon::now()->subDays(2),
                    ],
                    [
                        'tenant_id' => $tenantId,
                        'warehouse_id' => $whSource->id,
                        'product_id' => $product->id,
                        'batch_number' => 'LOT-2026-FMCG',
                        'entry_type' => 'TRANSFER_OUT',
                        'reference_type' => 'STOCK_TRANSFER',
                        'reference_id' => 1,
                        'reference_number' => 'TRF-202610-00101',
                        'qty_change' => -50,
                        'qty_before' => 200,
                        'qty_after' => 150,
                        'unit_cost' => 3000.00,
                        'total_cost' => 150000.00,
                        'notes' => 'Pengiriman Transfer Antar-Gudang ke Surabaya',
                        'created_by' => $user->id,
                        'created_at' => Carbon::now()->subHours(3),
                    ],
                ]);
            }
        }
    }

    /**
     * Seed Phase 2 demo records for Sales Quotations, Targets, and Approvals.
     */
    protected function seedPhase2SalesDemo(string $tenantId): void
    {
        $salesman = DB::table('users')->where('email', 'salesman.to@enterprisesales.co.id')->first();
        $branch = DB::table('branches')->first();
        $customer = DB::table('customers')->where('branch_id', $branch?->id)->first();
        $product = DB::table('products')->first();
        $unit = $product ? DB::table('product_units')->where('product_id', $product->id)->first() : null;

        if ($salesman && $branch) {
            // 1. Sales Target
            DB::table('sales_targets')->updateOrInsert(
                [
                    'tenant_id' => $tenantId,
                    'user_id' => $salesman->id,
                    'period_date' => Carbon::now()->startOfMonth()->toDateString(),
                ],
                [
                    'branch_id' => $branch->id,
                    'period_type' => 'MONTHLY',
                    'target_omset' => 150000000.00,
                    'target_qty' => 500.0000,
                    'target_new_customers' => 10,
                    'notes' => 'Target omzet Q4 2026 Salesman TO wilayah Jakarta Barat.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // 2. Sample Sales Quotation
            if ($customer && $product && $unit) {
                $quotationNumber = 'QUO-202610-00101';
                $existingQuotation = DB::table('sales_quotations')->where('quotation_number', $quotationNumber)->first();

                if (!$existingQuotation) {
                    $unitPrice = 50000.0000;
                    $qty = 20;
                    $gross = $unitPrice * $qty;
                    $disc = 50000.00;
                    $taxable = $gross - $disc;
                    $tax = round($taxable * 0.11, 2);
                    $grandTotal = $taxable + $tax;

                    $quoId = DB::table('sales_quotations')->insertGetId([
                        'tenant_id' => $tenantId,
                        'branch_id' => $branch->id,
                        'customer_id' => $customer->id,
                        'salesman_id' => $salesman->id,
                        'quotation_number' => $quotationNumber,
                        'quotation_date' => Carbon::now()->toDateString(),
                        'expiry_date' => Carbon::now()->addDays(14)->toDateString(),
                        'channel' => 'GT',
                        'status' => 'SENT',
                        'subtotal' => $gross,
                        'discount_amount' => $disc,
                        'tax_amount' => $tax,
                        'grand_total' => $grandTotal,
                        'notes' => 'Penawaran paket promo volume untuk Toko Kelontong Berkah.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('sales_quotation_items')->insert([
                        'sales_quotation_id' => $quoId,
                        'product_id' => $product->id,
                        'product_unit_id' => $unit->id,
                        'conversion_factor' => 1,
                        'qty_ordered' => $qty,
                        'qty_in_base_uom' => $qty,
                        'unit_price' => $unitPrice,
                        'gross_amount' => $gross,
                        'discount_percent_1' => 5.00,
                        'discount_percent_2' => 0.00,
                        'discount_nominal' => $disc,
                        'net_amount' => $taxable,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Map existing seed users to their corresponding RBAC roles.
     */
    protected function mapUsersToRoles(): void
    {
        $roleMap = [
            'superadmin@enterprisesales.co.id' => 'super-admin',
            'bm.jkt@enterprisesales.co.id' => 'branch-manager',
            'bm.sby@enterprisesales.co.id' => 'branch-manager',
            'admin.sales@enterprisesales.co.id' => 'sales-supervisor',
            'kam.mt@enterprisesales.co.id' => 'sales-manager',
            'salesman.to@enterprisesales.co.id' => 'sales-representative',
            'salesman.kanvas@enterprisesales.co.id' => 'sales-representative',
            'warehouse.jkt@enterprisesales.co.id' => 'warehouse-manager',
            'finance.ho@enterprisesales.co.id' => 'finance-manager',
            'billing.staff@enterprisesales.co.id' => 'finance-staff',
            'collector.ar@enterprisesales.co.id' => 'finance-staff',
        ];

        foreach ($roleMap as $email => $roleSlug) {
            $user = DB::table('users')->where('email', $email)->first();
            $role = DB::table('roles')->where('slug', $roleSlug)->first();

            if ($user && $role) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $user->id, 'role_id' => $role->id],
                    ['user_id' => $user->id, 'role_id' => $role->id]
                );
            }
        }
    }

    /**
     * Assign permissions based on role slug.
     */
    protected function assignDefaultPermissionsToRole(Role $role, string $slug): void
    {
        $allPermissions = Permission::all();

        if ($slug === 'super-admin' || $slug === 'company-owner') {
            $role->permissions()->sync($allPermissions->pluck('id'));
            return;
        }

        if ($slug === 'director') {
            $perms = $allPermissions->filter(fn($p) => in_array($p->action, ['view', 'export', 'print']));
            $role->permissions()->sync($perms->pluck('id'));
            return;
        }

        if ($slug === 'branch-manager') {
            $perms = $allPermissions->filter(fn($p) => in_array($p->module, ['sales', 'inventory', 'customers', 'reports', 'organization'])
                && in_array($p->action, ['view', 'create', 'edit', 'approve', 'reject', 'export', 'print']));
            $role->permissions()->sync($perms->pluck('id'));
            return;
        }

        if (in_array($slug, ['sales-manager', 'sales-supervisor', 'sales-representative'])) {
            $perms = $allPermissions->filter(fn($p) => in_array($p->module, ['sales', 'customers', 'products', 'reports'])
                && in_array($p->action, ['view', 'create', 'edit', 'print', 'export']));
            $role->permissions()->sync($perms->pluck('id'));
            return;
        }

        if (in_array($slug, ['warehouse-manager', 'warehouse-staff'])) {
            $perms = $allPermissions->filter(fn($p) => in_array($p->module, ['inventory', 'warehouse', 'products', 'reports'])
                && in_array($p->action, ['view', 'create', 'edit', 'adjust_stock', 'print', 'export']));
            $role->permissions()->sync($perms->pluck('id'));
            return;
        }

        if (in_array($slug, ['finance-manager', 'finance-staff'])) {
            $perms = $allPermissions->filter(fn($p) => in_array($p->module, ['finance', 'sales', 'customers', 'reports'])
                && in_array($p->action, ['view', 'create', 'edit', 'approve', 'reject', 'print', 'export']));
            $role->permissions()->sync($perms->pluck('id'));
            return;
        }
    }
}

