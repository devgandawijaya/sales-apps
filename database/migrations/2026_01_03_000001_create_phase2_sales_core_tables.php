<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // 1. SALES QUOTATIONS (PENAWARAN HARGA)
        // =====================================================================
        Schema::create('sales_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('salesman_id')->constrained('users')->restrictOnDelete();
            $table->string('quotation_number', 50);
            $table->date('quotation_date');
            $table->date('expiry_date');
            $table->string('channel', 20)->default('GT'); // GT, MT
            $table->string('status', 30)->default('DRAFT'); // DRAFT, SENT, ACCEPTED, REJECTED, EXPIRED, CONVERTED_TO_SO
            $table->decimal('subtotal', 18, 2)->default(0.00);
            $table->decimal('discount_amount', 18, 2)->default(0.00);
            $table->decimal('tax_amount', 18, 2)->default(0.00);
            $table->decimal('grand_total', 18, 2)->default(0.00);
            $table->foreignId('converted_sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'quotation_number']);
            $table->index('status');
        });

        Schema::create('sales_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_quotation_id')->constrained('sales_quotations')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_unit_id')->constrained('product_units')->restrictOnDelete();
            $table->unsignedInteger('conversion_factor')->default(1);
            $table->integer('qty_ordered');
            $table->integer('qty_in_base_uom');
            $table->decimal('unit_price', 18, 4);
            $table->decimal('gross_amount', 18, 2);
            $table->decimal('discount_percent_1', 5, 2)->default(0.00);
            $table->decimal('discount_percent_2', 5, 2)->default(0.00);
            $table->decimal('discount_nominal', 18, 2)->default(0.00);
            $table->decimal('net_amount', 18, 2);
            $table->timestamps();

            $table->index('sales_quotation_id');
        });

        // Add quotation_id & rejection_reason to sales_orders if not present
        if (!Schema::hasColumn('sales_orders', 'quotation_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->foreignId('quotation_id')->nullable()->after('warehouse_id')->constrained('sales_quotations')->nullOnDelete();
                $table->text('rejection_reason')->nullable()->after('notes');
            });
        }

        // =====================================================================
        // 2. SALES ORDER APPROVAL HISTORIES
        // =====================================================================
        Schema::create('sales_order_approval_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 30); // SUBMITTED, APPROVED, REJECTED, ON_HOLD
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['sales_order_id', 'created_at']);
        });

        // =====================================================================
        // 3. SALES TARGETS (TARGET & KPI SALESMAN / CABANG)
        // =====================================================================
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('period_type', 20)->default('MONTHLY'); // DAILY, WEEKLY, MONTHLY, YEARLY
            $table->date('period_date'); // Tanggal awal periode (e.g. 2026-10-01)
            $table->decimal('target_omset', 18, 2)->default(0.00);
            $table->decimal('target_qty', 18, 4)->default(0.00);
            $table->integer('target_new_customers')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'period_date']);
        });

        // =====================================================================
        // 4. SALES LEADS & PIPELINE (CRM PIPELINE)
        // =====================================================================
        Schema::create('sales_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('lead_name', 150);
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('stage', 30)->default('NEW'); // NEW, CONTACTED, QUALIFIED, PROPOSAL_SENT, NEGOTIATION, WON, LOST
            $table->decimal('estimated_value', 18, 2)->default(0.00);
            $table->text('lost_reason')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'salesman_id', 'stage']);
        });

        // =====================================================================
        // 5. SALES ACTIVITIES & LOG KUNJUNGAN / INTERAKSI
        // =====================================================================
        Schema::create('sales_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('sales_lead_id')->nullable()->constrained('sales_leads')->nullOnDelete();
            $table->string('activity_type', 30)->default('VISIT'); // VISIT, CALL, MEETING, EMAIL, FOLLOW_UP
            $table->dateTime('activity_date');
            $table->text('notes');
            $table->string('result_status', 30)->default('COMPLETED'); // SCHEDULED, COMPLETED, CANCELLED
            $table->dateTime('follow_up_date')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_activities');
        Schema::dropIfExists('sales_leads');
        Schema::dropIfExists('sales_targets');
        Schema::dropIfExists('sales_order_approval_histories');

        if (Schema::hasColumn('sales_orders', 'quotation_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropForeign(['quotation_id']);
                $table->dropColumn(['quotation_id', 'rejection_reason']);
            });
        }

        Schema::dropIfExists('sales_quotation_items');
        Schema::dropIfExists('sales_quotations');
    }
};

