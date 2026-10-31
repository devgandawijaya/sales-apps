<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure delivery_orders has tenant_id
        if (Schema::hasTable('delivery_orders') && !Schema::hasColumn('delivery_orders', 'tenant_id')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                $table->index('tenant_id');
            });
        }

        // 2. Ensure sales_returns has tenant_id
        if (Schema::hasTable('sales_returns') && !Schema::hasColumn('sales_returns', 'tenant_id')) {
            Schema::table('sales_returns', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('delivery_orders') && Schema::hasColumn('delivery_orders', 'tenant_id')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('sales_returns') && Schema::hasColumn('sales_returns', 'tenant_id')) {
            Schema::table('sales_returns', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};

