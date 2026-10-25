<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('owner_name', 100)->nullable();
            $table->string('channel', 20); // GT, MT
            $table->string('sub_channel', 50); // WARUNG, TOKO_KELONTONG, GROSIR, MINIMARKET_CHAIN, HYPERMARKET
            $table->string('tax_id_npwp', 50)->nullable();
            $table->string('nik', 30)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address');
            $table->string('city', 100);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('geofence_radius_meters')->default(100);
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->decimal('current_credit_used', 15, 2)->default(0.00);
            $table->unsignedInteger('term_of_payment')->default(0); // 0=COD, 7, 14, 30, 60, 90
            $table->string('payment_type', 20)->default('COD'); // COD, CREDIT
            $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('branch_id');
            $table->index('channel');
            $table->index(['latitude', 'longitude']);
        });

        Schema::create('modern_trade_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('contract_number', 100)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('listing_fee_total', 15, 2)->default(0.00);
            $table->decimal('slotting_fee_total', 15, 2)->default(0.00);
            $table->decimal('on_invoice_discount_rate', 5, 2)->default(0.00);
            $table->decimal('off_invoice_rebate_rate', 5, 2)->default(0.00);
            $table->unsignedInteger('payment_terms_days')->default(30);
            $table->text('penalty_terms')->nullable();
            $table->string('document_attachment_path', 255)->nullable();
            $table->string('status', 20)->default('ACTIVE'); // DRAFT, ACTIVE, EXPIRED, TERMINATED
            $table->timestamps();

            $table->index('customer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modern_trade_agreements');
        Schema::dropIfExists('customers');
    }
};

