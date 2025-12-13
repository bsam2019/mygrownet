<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Standalone POS (Point of Sale) System
     * 
     * Generic tables that can be used by any module (GrowBiz, BizBoost, etc.)
     * Uses module_context to track which module is using the POS
     */
    public function up(): void
    {
        // POS Shifts - Track cashier sessions
        Schema::create('pos_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('module_context', 50)->default('pos'); // pos, growbiz, bizboost, etc.
            $table->string('shift_number', 20)->unique();
            $table->decimal('opening_cash', 12, 2)->default(0);
            $table->decimal('closing_cash', 12, 2)->nullable();
            $table->decimal('expected_cash', 12, 2)->nullable();
            $table->decimal('cash_difference', 12, 2)->nullable();
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->decimal('total_cash_sales', 12, 2)->default(0);
            $table->decimal('total_mobile_sales', 12, 2)->default(0);
            $table->decimal('total_card_sales', 12, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            // Polymorphic relation to operator (employee, user, etc.)
            $table->nullableMorphs('operator');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'module_context']);
            $table->index(['user_id', 'started_at']);
        });

        // POS Sales - Main sales transactions
        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained('pos_shifts')->onDelete('set null');
            $table->string('module_context', 50)->default('pos');
            $table->string('sale_number', 20)->unique();
            // Polymorphic relation to customer (can be from any module)
            $table->nullableMorphs('customer');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->integer('item_count')->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('payment_method', ['cash', 'mobile_money', 'card', 'credit', 'split'])->default('cash');
            $table->string('payment_reference')->nullable();
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('change_given', 12, 2)->default(0);
            $table->enum('status', ['completed', 'refunded', 'partial_refund', 'voided'])->default('completed');
            $table->text('notes')->nullable();
            $table->string('currency', 3)->default('ZMW');
            // Polymorphic relation to served_by (employee from any module)
            $table->nullableMorphs('served_by');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'module_context']);
            $table->index(['user_id', 'status']);
            $table->index(['shift_id', 'status']);
            $table->index('sale_number');
        });

        // POS Sale Items - Line items for each sale
        Schema::create('pos_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('pos_sales')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->onDelete('set null');
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->default('piece');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->default(0); // For profit tracking
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();

            $table->index('sale_id');
            $table->index('inventory_item_id');
        });

        // POS Settings - Per-user/module configuration
        Schema::create('pos_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('module_context', 50)->default('pos');
            $table->string('receipt_header')->nullable();
            $table->string('receipt_footer')->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_address')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('tax_id')->nullable();
            $table->decimal('default_tax_rate', 5, 2)->default(0);
            $table->boolean('enable_tax')->default(false);
            $table->boolean('require_customer')->default(false);
            $table->boolean('allow_credit_sales')->default(false);
            $table->boolean('auto_print_receipt')->default(false);
            $table->boolean('track_inventory')->default(true);
            $table->string('currency', 3)->default('ZMW');
            $table->string('currency_symbol', 5)->default('K');
            $table->json('payment_methods')->nullable();
            $table->json('quick_amounts')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'module_context'], 'pos_settings_unique');
        });

        // POS Quick Products - Frequently sold items for quick access
        Schema::create('pos_quick_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('module_context', 50)->default('pos');
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('color', 7)->default('#3b82f6');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'module_context', 'is_active', 'sort_order'], 'pos_quick_products_idx');
        });

        // Module Integration Settings - Track which modules have POS/Inventory enabled
        Schema::create('module_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('parent_module', 50); // growbiz, bizboost, ecommerce
            $table->string('integrated_module', 50); // pos, inventory
            $table->boolean('is_enabled')->default(true);
            $table->json('settings')->nullable(); // Module-specific integration settings
            $table->timestamp('enabled_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'parent_module', 'integrated_module'], 'module_integrations_unique');
            $table->index(['user_id', 'parent_module'], 'module_integrations_parent_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_integrations');
        Schema::dropIfExists('pos_quick_products');
        Schema::dropIfExists('pos_settings');
        Schema::dropIfExists('pos_sale_items');
        Schema::dropIfExists('pos_sales');
        Schema::dropIfExists('pos_shifts');
    }
};
