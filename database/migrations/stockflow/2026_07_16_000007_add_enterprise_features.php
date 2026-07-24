<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sa_exchange_rates')) {
            Schema::create('sa_exchange_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->string('from_currency', 10);
                $table->string('to_currency', 10);
                $table->decimal('rate', 14, 6);
                $table->date('effective_date');
                $table->timestamps();
                $table->unique(['sa_company_id', 'from_currency', 'to_currency', 'effective_date']);
            });
        }

        if (!Schema::hasTable('sa_warehouses')) {
            Schema::create('sa_warehouses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->string('name');
                $table->string('code', 50)->nullable();
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('country')->nullable();
                $table->string('contact_person')->nullable();
                $table->string('phone', 50)->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('sa_departments', 'sa_warehouse_id')) {
            Schema::table('sa_departments', function (Blueprint $table) {
                $table->foreignId('sa_warehouse_id')->nullable()->constrained('sa_warehouses')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('sa_items', 'sa_warehouse_id')) {
            Schema::table('sa_items', function (Blueprint $table) {
                $table->foreignId('sa_warehouse_id')->nullable()->constrained('sa_warehouses')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('sa_lots')) {
            Schema::create('sa_lots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->string('lot_number');
                $table->date('manufacturing_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->date('received_date')->nullable();
                $table->decimal('initial_quantity', 12, 2)->default(0);
                $table->decimal('current_quantity', 12, 2)->default(0);
                $table->string('status')->default('active');
                $table->timestamps();
                $table->unique(['sa_company_id', 'sa_item_id', 'lot_number']);
            });
        }

        if (!Schema::hasColumn('sa_stock_movements', 'sa_lot_id')) {
            Schema::table('sa_stock_movements', function (Blueprint $table) {
                $table->foreignId('sa_lot_id')->nullable()->constrained('sa_lots')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('sa_purchase_requisitions')) {
            Schema::create('sa_purchase_requisitions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->string('requisition_number');
                $table->foreignId('requested_by')->constrained('sa_users')->cascadeOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('sa_users')->nullOnDelete();
                $table->date('date_required')->nullable();
                $table->string('status')->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sa_requisition_items')) {
            Schema::create('sa_requisition_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_purchase_requisition_id')->constrained('sa_purchase_requisitions')->cascadeOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->decimal('quantity', 12, 2);
                $table->decimal('estimated_unit_price', 14, 2)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sa_payment_transactions')) {
            Schema::create('sa_payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->morphs('payable');
                $table->string('gateway')->default('cash');
                $table->string('transaction_id')->nullable()->unique();
                $table->decimal('amount', 14, 2);
                $table->string('currency', 10)->default('ZMW');
                $table->string('status')->default('pending');
                $table->json('gateway_response')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_payment_transactions');
        Schema::dropIfExists('sa_requisition_items');
        Schema::dropIfExists('sa_purchase_requisitions');
        Schema::table('sa_stock_movements', fn($t) => $t->dropColumn('sa_lot_id'));
        Schema::dropIfExists('sa_lots');
        Schema::table('sa_items', fn($t) => $t->dropForeign(['sa_warehouse_id']));
        Schema::table('sa_departments', fn($t) => $t->dropForeign(['sa_warehouse_id']));
        Schema::table('sa_items', fn($t) => $t->dropColumn('sa_warehouse_id'));
        Schema::table('sa_departments', fn($t) => $t->dropColumn('sa_warehouse_id'));
        Schema::dropIfExists('sa_warehouses');
        Schema::dropIfExists('sa_exchange_rates');
    }
};
