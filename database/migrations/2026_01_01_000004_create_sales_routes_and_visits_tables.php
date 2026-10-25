<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('salesman_id')->constrained('users')->restrictOnDelete();
            $table->string('route_code', 30);
            $table->string('route_name', 100);
            $table->string('day_of_week', 15); // MONDAY, TUESDAY, WEDNESDAY, THURSDAY, FRIDAY, SATURDAY
            $table->string('visit_frequency', 20)->default('WEEKLY'); // WEEKLY, BIWEEKLY, MONTHLY
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['salesman_id', 'route_code']);
        });

        Schema::create('route_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('sales_routes')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->unsignedInteger('sequence_order')->default(1);
            $table->timestamps();

            $table->unique(['route_id', 'customer_id']);
        });

        Schema::create('customer_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('route_id')->nullable()->constrained('sales_routes')->nullOnDelete();
            $table->date('visit_date');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->decimal('latitude_in', 10, 7)->nullable();
            $table->decimal('longitude_in', 10, 7)->nullable();
            $table->decimal('latitude_out', 10, 7)->nullable();
            $table->decimal('longitude_out', 10, 7)->nullable();
            $table->boolean('is_in_radius')->default(false);
            $table->string('selfie_photo_path', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('PLANNED'); // PLANNED, VISITED, NOT_VISITED, SKIP
            $table->string('reason_code', 50)->nullable(); // TOKO_TUTUP, OWNER_TIDAK_ADA, STOK_LENGKAP
            $table->timestamps();

            $table->index(['salesman_id', 'visit_date']);
            $table->index('customer_id');
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('promo_type', 30); // DISCOUNT_PERCENT, DISCOUNT_NOMINAL, BUY_X_GET_Y, BUNDLE
            $table->string('channel', 20)->default('ALL'); // GT, MT, ALL
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('min_purchase_amount', 15, 2)->default(0.00);
            $table->unsignedInteger('min_purchase_qty')->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0.00);
            $table->decimal('discount_nominal', 15, 2)->default(0.00);
            $table->foreignId('free_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->unsignedInteger('free_qty')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('customer_visits');
        Schema::dropIfExists('route_customers');
        Schema::dropIfExists('sales_routes');
    }
};

