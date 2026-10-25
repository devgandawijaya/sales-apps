<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('salesman_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('so_number', 50)->unique();
            $table->string('po_number_mt', 100)->nullable();
            $table->date('order_date');
            $table->string('channel', 20); // GT, MT
            $table->string('order_type', 30)->default('TAKING_ORDER'); // TAKING_ORDER, CANVASS, MODERN_TRADE_PO
            $table->string('status', 30)->default('DRAFT'); // DRAFT, PENDING_APPROVAL, APPROVED, REJECTED, PROCESSING, PARTIAL_DELIVERED, DELIVERED, CANCELLED
            $table->unsignedInteger('payment_term_days')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount_on_invoice', 15, 2)->default(0.00);
            $table->decimal('discount_off_invoice', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00); // PPN 11%
            $table->decimal('grand_total', 15, 2)->default(0.00);
            $table->unsignedInteger('approval_level')->default(0);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('branch_id');
            $table->index('customer_id');
            $table->index('order_date');
            $table->index('status');
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_unit_id')->constrained('product_units')->restrictOnDelete();
            $table->unsignedInteger('conversion_factor')->default(1);
            $table->integer('qty_ordered');
            $table->integer('qty_in_base_uom');
            $table->integer('qty_delivered_base_uom')->default(0);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('discount_percent_1', 5, 2)->default(0.00);
            $table->decimal('discount_percent_2', 5, 2)->default(0.00);
            $table->decimal('discount_nominal', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2);
            $table->timestamps();

            $table->index('sales_order_id');
            $table->index('product_id');
        });

        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->restrictOnDelete();
            $table->string('do_number', 50)->unique();
            $table->date('delivery_date');
            $table->string('delivery_type', 30)->default('DIRECT_STORE'); // DIRECT_STORE, CENTRAL_DC, CANVASS_DIRECT
            $table->string('status', 30)->default('PREPARED'); // PREPARED, DISPATCHED, DELIVERED, RETURNED, CANCELLED
            $table->string('driver_name', 100)->nullable();
            $table->string('vehicle_plate_number', 30)->nullable();
            $table->string('recipient_name', 100)->nullable();
            $table->string('proof_of_delivery_path', 255)->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sales_order_id');
            $table->index('status');
        });

        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->cascadeOnDelete();
            $table->foreignId('sales_order_item_id')->constrained('sales_order_items')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('batch_number', 50);
            $table->date('expiry_date');
            $table->integer('qty_shipped');
            $table->integer('qty_received')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_order_items');
        Schema::dropIfExists('delivery_orders');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};

