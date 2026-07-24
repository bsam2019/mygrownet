<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sa_categories')) {
            Schema::create('sa_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->foreignId('parent_id')->nullable()->constrained('sa_categories')->cascadeOnDelete();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['sa_company_id', 'slug']);
            });
        }

        if (!Schema::hasColumn('sa_items', 'sa_category_id')) {
            Schema::table('sa_items', function (Blueprint $table) {
                $table->foreignId('sa_category_id')->nullable()->constrained('sa_categories')->nullOnDelete();
                $table->string('barcode', 100)->nullable()->after('sku');
                $table->string('brand', 255)->nullable()->after('name');
                $table->decimal('wholesale_price', 14, 2)->nullable()->after('unit_price');
                $table->decimal('vip_price', 14, 2)->nullable()->after('wholesale_price');
            });
        }

        if (!Schema::hasTable('sa_product_variants')) {
            Schema::create('sa_product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->foreignId('parent_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->string('variant_name');
                $table->string('sku', 100)->nullable();
                $table->string('barcode', 100)->nullable();
                $table->decimal('unit_price', 14, 2)->nullable();
                $table->decimal('wholesale_price', 14, 2)->nullable();
                $table->decimal('vip_price', 14, 2)->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sa_controlled_medicines')) {
            Schema::create('sa_controlled_medicines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->foreignId('sa_lot_id')->nullable()->constrained('sa_lots')->nullOnDelete();
                $table->string('transaction_type');
                $table->decimal('quantity', 12, 2);
                $table->decimal('balance_after', 12, 2);
                $table->string('patient_name')->nullable();
                $table->string('patient_id_number')->nullable();
                $table->string('prescription_number')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('staff_user_id')->constrained('sa_users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sa_sale_returns')) {
            Schema::create('sa_sale_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->foreignId('sa_sale_id')->constrained('sa_sales')->cascadeOnDelete();
                $table->string('return_number');
                $table->date('return_date');
                $table->string('reason');
                $table->decimal('total_refund', 14, 2)->default(0);
                $table->string('refund_method')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('sa_users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sa_sale_return_items')) {
            Schema::create('sa_sale_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_sale_return_id')->constrained('sa_sale_returns')->cascadeOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->decimal('quantity', 12, 2);
                $table->decimal('unit_price', 14, 2);
                $table->decimal('subtotal', 14, 2);
                $table->text('condition')->nullable();
            });
        }

        if (!Schema::hasTable('sa_supplier_returns')) {
            Schema::create('sa_supplier_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->foreignId('sa_supplier_id')->constrained('sa_suppliers')->cascadeOnDelete();
                $table->foreignId('sa_purchase_order_id')->nullable()->constrained('sa_purchase_orders')->nullOnDelete();
                $table->string('return_number');
                $table->date('return_date');
                $table->string('reason');
                $table->decimal('total_refund', 14, 2)->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('sa_users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sa_supplier_return_items')) {
            Schema::create('sa_supplier_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_supplier_return_id')->constrained('sa_supplier_returns')->cascadeOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->decimal('quantity', 12, 2);
                $table->decimal('unit_cost', 14, 2);
                $table->decimal('subtotal', 14, 2);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_supplier_return_items');
        Schema::dropIfExists('sa_supplier_returns');
        Schema::dropIfExists('sa_sale_return_items');
        Schema::dropIfExists('sa_sale_returns');
        Schema::dropIfExists('sa_controlled_medicines');
        Schema::dropIfExists('sa_product_variants');
        Schema::table('sa_items', fn($t) => $t->dropColumn(['sa_category_id', 'barcode', 'brand', 'wholesale_price', 'vip_price']));
        Schema::dropIfExists('sa_categories');
    }
};
