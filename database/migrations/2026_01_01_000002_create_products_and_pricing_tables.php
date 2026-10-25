<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('barcode', 50)->nullable()->unique();
            $table->string('name', 200);
            $table->string('brand', 100)->nullable();
            $table->string('base_uom', 20)->default('PCS');
            $table->decimal('cost_price', 15, 2)->default(0.00);
            $table->decimal('weight_gram', 10, 2)->default(0.00);
            $table->decimal('volume_cbm', 10, 4)->default(0.0000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category_id');
            $table->index('is_active');
        });

        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('unit_name', 30); // DUS, KARTON, PAK, PCS
            $table->unsignedInteger('conversion_factor')->default(1);
            $table->string('barcode', 50)->nullable();
            $table->boolean('is_base_unit')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'unit_name']);
        });

        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->string('channel_type', 30); // GT, MT, WHOLESALE, RETAIL, ALL
            $table->string('currency', 10)->default('IDR');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('channel_type');
        });

        Schema::create('price_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_unit_id')->constrained('product_units')->restrictOnDelete();
            $table->unsignedInteger('min_quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->timestamps();

            $table->unique(['price_list_id', 'product_id', 'product_unit_id', 'min_quantity'], 'uq_price_list_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
        Schema::dropIfExists('price_lists');
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};

