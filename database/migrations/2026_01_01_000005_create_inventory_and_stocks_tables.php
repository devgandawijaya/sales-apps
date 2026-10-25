<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('batch_number', 50);
            $table->date('expiry_date');
            $table->integer('qty_on_hand')->default(0); // in Base UOM
            $table->integer('qty_reserved')->default(0);
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_id', 'batch_number'], 'uq_stock_wh_prod_batch');
            $table->index(['warehouse_id', 'product_id']);
            $table->index('expiry_date');
        });

        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('batch_number', 50);
            $table->string('transaction_type', 40); // INITIAL, RECEIVING, SO_DELIVERY, RETURN_GOOD, RETURN_BAD, TRANSFER_IN, TRANSFER_OUT, ADJUSTMENT
            $table->string('reference_type', 40); // SO, DO, TRANSFER, ADJUSTMENT, RETURN
            $table->unsignedBigInteger('reference_id');
            $table->integer('qty_in')->default(0);
            $table->integer('qty_out')->default(0);
            $table->integer('balance_after');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['warehouse_id', 'product_id']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 50)->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('transit_warehouse_id')->nullable()->constrained('warehouses')->restrictOnDelete();
            $table->string('driver_name', 100)->nullable();
            $table->string('vehicle_number', 30)->nullable();
            $table->string('status', 30)->default('DRAFT'); // DRAFT, PENDING_APPROVAL, IN_TRANSIT, RECEIVED_FULL, RECEIVED_PARTIAL, CANCELLED
            $table->timestamp('sent_date')->nullable();
            $table->timestamp('received_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_unit_id')->constrained('product_units')->restrictOnDelete();
            $table->string('batch_number', 50);
            $table->date('expiry_date');
            $table->integer('qty_sent');
            $table->integer('qty_received')->default(0);
            $table->integer('discrepancy_qty')->default(0);
            $table->string('condition_status', 30)->default('GOOD'); // GOOD, DAMAGED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_mutations');
        Schema::dropIfExists('stocks');
    }
};

