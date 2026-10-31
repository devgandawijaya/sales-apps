<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // 1. IMMUTABLE STOCK LEDGER ENTRIES (DOUBLE-ENTRY AUDITABLE INVENTORY)
        // =====================================================================
        Schema::create('stock_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('batch_number', 50);
            $table->string('entry_type', 30); // IN, OUT, TRANSFER_OUT, TRANSFER_IN, OPNAME_ADJUST_IN, OPNAME_ADJUST_OUT, RESERVE, RELEASE
            $table->string('reference_type', 40); // STOCK_TRANSFER, STOCK_OPNAME, STOCK_ADJUSTMENT, SALES_ORDER, GOODS_RECEIPT, INITIAL_COUNT
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number', 60)->nullable();
            $table->integer('qty_change'); // positive (in) or negative (out)
            $table->integer('qty_before');
            $table->integer('qty_after');
            $table->decimal('unit_cost', 18, 4)->default(0.0000);
            $table->decimal('total_cost', 18, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'warehouse_id', 'product_id'], 'idx_ledger_wh_prod');
            $table->index(['reference_type', 'reference_id'], 'idx_ledger_ref');
            $table->index('created_at');
        });

        // =====================================================================
        // 2. ENHANCE STOCK TRANSFERS FOR ENTERPRISE INTER-BRANCH WORKFLOW
        // =====================================================================
        if (Schema::hasTable('stock_transfers')) {
            Schema::table('stock_transfers', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_transfers', 'tenant_id')) {
                    $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                    $table->index('tenant_id');
                }
                if (!Schema::hasColumn('stock_transfers', 'approval_level')) {
                    $table->integer('approval_level')->default(1)->after('status');
                }
                if (!Schema::hasColumn('stock_transfers', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('approval_level')->constrained('users')->nullOnDelete();
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('stock_transfers', 'dispatched_at')) {
                    $table->timestamp('dispatched_at')->nullable()->after('sent_date');
                }
                if (!Schema::hasColumn('stock_transfers', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('notes');
                }
                if (!Schema::hasColumn('stock_transfers', 'received_notes')) {
                    $table->text('received_notes')->nullable()->after('rejection_reason');
                }
            });
        }

        // =====================================================================
        // 3. STOCK OPNAMES & SESSIONS (WALL-TO-WALL & CYCLE COUNT)
        // =====================================================================
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('opname_number', 50)->unique();
            $table->string('title', 150);
            $table->date('period_date');
            $table->string('status', 30)->default('DRAFT'); // DRAFT, IN_PROGRESS, SUBMITTED, APPROVED, POSTED, REJECTED
            $table->string('count_type', 30)->default('CYCLE_COUNT'); // CYCLE_COUNT, WALL_TO_WALL, SPOT_CHECK
            $table->boolean('blind_count')->default(false);
            $table->integer('total_items_counted')->default(0);
            $table->integer('total_discrepancy_qty')->default(0);
            $table->decimal('total_cogs_variance', 18, 2)->default(0.00);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'warehouse_id', 'status']);
            $table->index('opname_number');
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('batch_number', 50);
            $table->date('expiry_date');
            $table->integer('system_qty');
            $table->integer('physical_qty')->default(0);
            $table->integer('discrepancy_qty')->default(0); // physical - system
            $table->decimal('unit_cost', 18, 4)->default(0.0000);
            $table->decimal('discrepancy_cogs_value', 18, 2)->default(0.00);
            $table->string('reason_code', 50)->nullable(); // DAMAGED, EXPIRED, MISCOUNT, THEFT, FOUND
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['stock_opname_id', 'product_id'], 'idx_opname_prod');
        });

        // =====================================================================
        // 4. WAREHOUSE ZONES (TOPOLOGY: WAREHOUSE -> ZONE -> RACK -> BIN)
        // =====================================================================
        if (!Schema::hasTable('warehouse_zones')) {
            Schema::create('warehouse_zones', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->string('code', 30);
                $table->string('name', 100);
                $table->string('zone_type', 30)->default('GENERAL'); // GENERAL, FAST_MOVING, COLD_ROOM, QUARANTINE, DAMAGED
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['warehouse_id', 'code']);
                $table->index(['tenant_id', 'warehouse_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_zones');
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');

        if (Schema::hasTable('stock_transfers')) {
            Schema::table('stock_transfers', function (Blueprint $table) {
                if (Schema::hasColumn('stock_transfers', 'tenant_id')) {
                    $table->dropColumn(['tenant_id', 'approval_level', 'approved_by', 'approved_at', 'dispatched_at', 'rejection_reason', 'received_notes']);
                }
            });
        }

        Schema::dropIfExists('stock_ledger_entries');
    }
};

