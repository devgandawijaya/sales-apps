<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('type', 30)->default('BRANCH'); // HEAD_OFFICE, BRANCH, SUB_BRANCH
            $table->text('address');
            $table->string('city', 100);
            $table->string('province', 100);
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->string('type', 30)->default('BRANCH_MAIN'); // CENTRAL_DC, BRANCH_MAIN, CANVASS_VAN, BAD_STOCK, TRANSIT
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('branch_id');
            $table->index('type');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('phone', 30)->nullable();
            $table->string('role_title', 50); // SUPERADMIN, BRANCH_MANAGER, SALES_ADMIN, FINANCE, SALESMAN_GT, KAM_MT, WAREHOUSE_KEEPER
            $table->string('channel_assignment', 20)->default('ALL'); // GT, MT, ALL
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->index('branch_id');
            $table->index('role_title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('branches');
    }
};

