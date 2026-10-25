<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. COMPANIES (MULTI-COMPANY / HOLDING)
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('tax_id_npwp', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->default('Indonesia');
            $table->string('currency', 10)->default('IDR');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        // Add company_id to branches if not exists
        if (!Schema::hasColumn('branches', 'company_id')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
                $table->softDeletes()->after('is_active');
            });
        }

        // 2. WAREHOUSE BINS (AISLE, RACK, TIER, BIN LOCATION WMS)
        Schema::create('warehouse_bins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('bin_code', 50)->unique(); // e.g., WH1-A01-R02-T03-B04
            $table->string('zone', 30)->default('GENERAL'); // FAST_MOVING, COLD_ROOM, QUARANTINE, GENERAL
            $table->string('aisle', 10);
            $table->string('rack', 10);
            $table->string('tier', 10);
            $table->string('bin', 10);
            $table->decimal('max_weight_kg', 10, 2)->default(1000.00);
            $table->decimal('max_volume_cbm', 10, 4)->default(5.0000);
            $table->boolean('is_occupied')->default(false);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['warehouse_id', 'zone']);
            $table->index(['aisle', 'rack']);
        });

        // 3. BATCHES (CENTRALIZED LOT & EXPIRY MASTER)
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('batch_number', 50);
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date');
            $table->string('qc_status', 30)->default('PASSED'); // PENDING_QC, PASSED, REJECTED, QUARANTINE
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['product_id', 'batch_number']);
            $table->index(['product_id', 'expiry_date']);
        });

        // 4. PURCHASE ORDERS (INBOUND SUPPLIER)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('po_number', 50)->unique();
            $table->string('supplier_name', 150);
            $table->string('supplier_code', 50)->nullable();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->string('status', 30)->default('APPROVED'); // DRAFT, APPROVED, PARTIAL_RECEIVED, COMPLETED, CANCELLED
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index('order_date');
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_unit_id')->constrained('product_units')->restrictOnDelete();
            $table->unsignedInteger('conversion_factor')->default(1);
            $table->integer('qty_ordered');
            $table->integer('qty_in_base_uom');
            $table->integer('qty_received_base_uom')->default(0);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->index(['purchase_order_id', 'product_id']);
        });

        // 5. GOODS RECEIPT NOTES (INBOUND GRN / BUKTI PENERIMAAN BARANG)
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->string('grn_number', 50)->unique();
            $table->string('supplier_delivery_note', 100); // Nomor Surat Jalan Supplier
            $table->date('receive_date');
            $table->string('receipt_type', 30)->default('STANDARD'); // STANDARD, BLIND_RECEIPT
            $table->string('status', 30)->default('COMPLETED'); // DRAFT, VERIFIED, COMPLETED, DISCREPANCY_HOLD
            $table->string('driver_name', 100)->nullable();
            $table->string('vehicle_plate_number', 30)->nullable();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['branch_id', 'receive_date']);
            $table->index('grn_number');
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_unit_id')->constrained('product_units')->restrictOnDelete();
            $table->foreignId('warehouse_bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
            $table->string('batch_number', 50);
            $table->date('expiry_date');
            $table->integer('qty_expected_base_uom')->default(0);
            $table->integer('qty_received_base_uom');
            $table->integer('qty_discrepancy_base_uom')->default(0); // received - expected
            $table->string('condition_status', 30)->default('GOOD'); // GOOD, QC_PENDING, DAMAGED
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->text('discrepancy_reason')->nullable();
            $table->timestamps();

            $table->index(['goods_receipt_id', 'product_id']);
            $table->index(['batch_number', 'expiry_date']);
        });

        // 6. STOCK ADJUSTMENTS & PHYSICAL COUNT (CYCLE COUNT & OPNAME)
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('adjustment_number', 50)->unique();
            $table->date('adjustment_date');
            $table->string('adjustment_type', 30)->default('CYCLE_COUNT'); // CYCLE_COUNT, WALL_TO_WALL_OPNAME, DAMAGE_WRITE_OFF
            $table->string('status', 30)->default('PENDING_APPROVAL'); // PENDING_APPROVAL, APPROVED, REJECTED
            $table->decimal('total_cogs_loss', 15, 2)->default(0.00);
            $table->foreignId('conducted_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('reason')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['warehouse_id', 'status']);
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('batch_number', 50);
            $table->integer('system_qty_base_uom');
            $table->integer('physical_qty_base_uom');
            $table->integer('discrepancy_qty_base_uom'); // physical - system
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('discrepancy_cogs_value', 15, 2);
            $table->text('item_notes')->nullable();
            $table->timestamps();

            $table->index(['stock_adjustment_id', 'product_id']);
        });

        // 7. FLEET & TRIP MANIFEST (LOADING SHEET & DIGITAL POD)
        Schema::create('trip_manifests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('manifest_number', 50)->unique();
            $table->date('trip_date');
            $table->string('vehicle_plate_number', 30);
            $table->string('driver_name', 100);
            $table->string('driver_phone', 30)->nullable();
            $table->string('fleet_type', 50)->default('INTERNAL_BOX_COLT');
            $table->decimal('max_payload_kg', 10, 2)->default(3000.00);
            $table->decimal('max_volume_cbm', 10, 4)->default(14.0000);
            $table->decimal('utilized_weight_kg', 10, 2)->default(0.00);
            $table->decimal('utilized_volume_cbm', 10, 4)->default(0.0000);
            $table->string('status', 30)->default('ASSIGNED'); // ASSIGNED, LOADING, IN_TRANSIT, COMPLETED
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['branch_id', 'trip_date']);
            $table->index('status');
        });

        Schema::create('trip_manifest_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_manifest_id')->constrained('trip_manifests')->cascadeOnDelete();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->restrictOnDelete();
            $table->unsignedInteger('stop_sequence')->default(1);
            $table->string('pod_status', 30)->default('PENDING'); // PENDING, DELIVERED, PARTIAL, FAILED
            $table->decimal('pod_latitude', 10, 7)->nullable();
            $table->decimal('pod_longitude', 10, 7)->nullable();
            $table->string('pod_signature_path', 255)->nullable();
            $table->string('pod_document_photo_path', 255)->nullable();
            $table->timestamp('pod_delivered_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->unique(['trip_manifest_id', 'delivery_order_id']);
        });

        // 8. TUKAR FAKTUR (INVOICE EXCHANGE RECEIPTS - TTF)
        Schema::create('invoice_exchange_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('receipt_number', 50)->unique(); // Nomor TTF (Tanda Terima Faktur)
            $table->date('submission_date'); // Tanggal tukar faktur diserahkan ke MT
            $table->date('received_date');   // Tanggal dicap & disetujui bagian AP MT
            $table->date('payment_due_date'); // Jatuh tempo dihitung dari received_date + TOP
            $table->integer('terms_of_payment_days');
            $table->decimal('total_invoice_amount', 15, 2);
            $table->string('status', 30)->default('ACCEPTED'); // SUBMITTED, ACCEPTED, REJECTED, PAID
            $table->string('document_attachment_path', 255)->nullable();
            $table->foreignId('handled_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index('payment_due_date');
        });

        // 9. PAYMENT GIROS (GIRO BILYET LIFECYCLE MANAGEMENT)
        Schema::create('payment_giros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('giro_number', 50);
            $table->string('bank_name', 100);
            $table->decimal('amount', 15, 2);
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('deposited_date')->nullable();
            $table->date('cleared_date')->nullable();
            $table->date('rejected_date')->nullable();
            $table->string('status', 30)->default('RECEIVED'); // RECEIVED, DEPOSITED, CLEARED, BOUNCED_REJECTED
            $table->string('rejection_reason', 150)->nullable();
            $table->string('giro_photo_path', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['giro_number', 'due_date']);
        });

        // 10. INVOICE ITEMS (LINE ITEM BREAKDOWN PER INVOICE)
        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->foreignId('product_unit_id')->constrained('product_units')->restrictOnDelete();
                $table->integer('qty_invoiced');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('discount_amount', 15, 2)->default(0.00);
                $table->decimal('taxable_base', 15, 2);
                $table->decimal('tax_amount', 15, 2);
                $table->decimal('line_total', 15, 2);
                $table->timestamps();

                $table->index(['invoice_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('payment_giros');
        Schema::dropIfExists('invoice_exchange_receipts');
        Schema::dropIfExists('trip_manifest_orders');
        Schema::dropIfExists('trip_manifests');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('warehouse_bins');
        if (Schema::hasColumn('branches', 'company_id')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn(['company_id', 'deleted_at']);
            });
        }
        Schema::dropIfExists('companies');
    }
};

