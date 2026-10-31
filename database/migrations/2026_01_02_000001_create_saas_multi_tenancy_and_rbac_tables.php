<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // 1. SAAS SUBSCRIPTION PLANS
        // =====================================================================
        if (!Schema::hasTable('subscription_plans')) {
            Schema::create('subscription_plans', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 100);
                $table->string('tier', 30); // trial, basic, professional, business, enterprise
                $table->decimal('price_monthly', 18, 2)->default(0);
                $table->decimal('price_annual', 18, 2)->default(0);
                $table->integer('max_users')->default(5);
                $table->integer('max_companies')->default(1);
                $table->integer('max_branches')->default(2);
                $table->integer('max_warehouses')->default(2);
                $table->integer('storage_limit_mb')->default(1000);
                $table->json('features')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // =====================================================================
        // 2. SAAS TENANTS
        // =====================================================================
        if (!Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code', 30)->unique();
                $table->string('name', 150);
                $table->string('subdomain', 60)->unique();
                $table->string('custom_domain', 150)->nullable()->unique();
                $table->string('status', 30)->default('trial'); // trial, active, past_due, suspended, canceled, expired
                $table->timestamp('trial_ends_at')->nullable();
                $table->string('plan_tier', 30)->default('trial');
                $table->integer('max_users')->default(3);
                $table->integer('max_companies')->default(1);
                $table->integer('max_branches')->default(1);
                $table->integer('max_warehouses')->default(1);
                $table->integer('storage_limit_mb')->default(500);
                $table->json('settings')->nullable();
                $table->timestamps();

                $table->index('status');
                $table->index('subdomain');
            });
        }

        // =====================================================================
        // 3. SAAS SUBSCRIPTIONS
        // =====================================================================
        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('plan_id')->constrained('subscription_plans')->restrictOnDelete();
                $table->string('status', 30)->default('active'); // active, past_due, canceled, expired
                $table->string('billing_cycle', 20)->default('monthly'); // monthly, annual
                $table->date('current_period_start');
                $table->date('current_period_end');
                $table->decimal('amount', 18, 2)->default(0);
                $table->boolean('cancel_at_period_end')->default(false);
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
            });
        }

        // =====================================================================
        // 4. ATTACH TENANT_ID TO COMPANIES (ALREADY CREATED IN WMS MIGRATION)
        // =====================================================================
        if (Schema::hasTable('companies')) {
            if (!Schema::hasColumn('companies', 'tenant_id')) {
                Schema::table('companies', function (Blueprint $table) {
                    $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                    $table->index('tenant_id');
                });
            }
        } else {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('code', 30);
                $table->string('name', 150);
                $table->string('tax_id_npwp', 50)->nullable();
                $table->string('base_currency', 3)->default('IDR');
                $table->text('address')->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('email', 100)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('tenant_id');
            });
        }

        // =====================================================================
        // 5. ATTACH TENANT_ID TO BRANCHES & WAREHOUSES & USERS
        // =====================================================================
        if (Schema::hasTable('branches') && !Schema::hasColumn('branches', 'tenant_id')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                $table->index('tenant_id');
            });
        }

        if (Schema::hasTable('warehouses') && !Schema::hasColumn('warehouses', 'tenant_id')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                $table->index('tenant_id');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                $table->string('status', 20)->default('active')->after('is_active');
                $table->boolean('mfa_enabled')->default(false)->after('status');
                $table->string('mfa_secret')->nullable()->after('mfa_enabled');
                $table->timestamp('last_login_at')->nullable()->after('mfa_secret');
                $table->timestamp('last_active_at')->nullable()->after('last_login_at');
                $table->index('tenant_id');
                $table->index('status');
            });
        }

        // =====================================================================
        // 6. GRANULAR RBAC: ROLES, PERMISSIONS, ROLE_PERMISSIONS, USER_ROLES
        // =====================================================================
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('slug', 100);
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->timestamps();

                $table->index(['tenant_id', 'slug']);
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('module', 50); // sales, inventory, customers, finance, reports, users, settings, tenants
                $table->string('action', 50); // view, create, edit, delete, approve, reject, export, print, cancel, adjust_stock, manage_settings
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['module', 'action']);
            });
        }

        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->primary(['role_id', 'permission_id']);
            });
        }

        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->primary(['user_id', 'role_id']);
            });
        }

        // =====================================================================
        // 7. ATTACH TENANT_ID TO BUSINESS ENTITIES
        // =====================================================================
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'tenant_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                $table->index('tenant_id');
            });
        }

        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'tenant_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                $table->index('tenant_id');
            });
        }

        if (Schema::hasTable('sales_orders') && !Schema::hasColumn('sales_orders', 'tenant_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                $table->index('tenant_id');
            });
        }

        if (Schema::hasTable('stocks') && !Schema::hasColumn('stocks', 'tenant_id')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                $table->index('tenant_id');
            });
        }

        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'tenant_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                $table->index('tenant_id');
            });
        }

        if (Schema::hasTable('payments') && !Schema::hasColumn('payments', 'tenant_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                $table->index('tenant_id');
            });
        }

        // =====================================================================
        // 8. AUDIT LOGS (ENTERPRISE AUDIT TRAIL)
        // =====================================================================
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 50); // CREATE, UPDATE, DELETE, LOGIN, APPROVE, REJECT, EXPORT, ADJUST_STOCK
                $table->string('entity_type', 100);
                $table->string('entity_id', 50)->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['tenant_id', 'created_at']);
                $table->index(['entity_type', 'entity_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'tenant_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'tenant_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('stocks') && Schema::hasColumn('stocks', 'tenant_id')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('sales_orders') && Schema::hasColumn('sales_orders', 'tenant_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'tenant_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'tenant_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['tenant_id', 'status', 'mfa_enabled', 'mfa_secret', 'last_login_at', 'last_active_at']);
            });
        }

        if (Schema::hasTable('warehouses') && Schema::hasColumn('warehouses', 'tenant_id')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('branches') && Schema::hasColumn('branches', 'tenant_id')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('companies') && Schema::hasColumn('companies', 'tenant_id')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }

        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('subscription_plans');
    }
};

