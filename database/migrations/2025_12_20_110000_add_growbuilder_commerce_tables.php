<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Products
        Schema::create('growbuilder_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->integer('price'); // In Ngwee (smallest unit)
            $table->integer('compare_price')->nullable(); // Original price for discounts
            $table->json('images')->nullable(); // Array of image paths
            $table->integer('stock_quantity')->default(0);
            $table->boolean('track_stock')->default(true);
            $table->string('sku')->nullable();
            $table->string('category')->nullable();
            $table->json('variants')->nullable(); // Size, color, etc.
            $table->json('attributes')->nullable(); // Custom attributes
            $table->decimal('weight', 8, 2)->nullable(); // For shipping
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['site_id', 'slug']);
            $table->index(['site_id', 'is_active']);
            $table->index(['site_id', 'category']);
        });

        // Product Categories
        Schema::create('growbuilder_product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
        });

        // Orders
        Schema::create('growbuilder_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_city')->nullable();
            $table->json('items'); // Order items snapshot
            $table->integer('subtotal');
            $table->integer('shipping_cost')->default(0);
            $table->integer('discount_amount')->default(0);
            $table->string('discount_code')->nullable();
            $table->integer('total');
            $table->enum('status', [
                'pending',
                'payment_pending',
                'paid',
                'processing',
                'shipped',
                'delivered',
                'completed',
                'cancelled',
                'refunded'
            ])->default('pending');
            $table->string('payment_method')->nullable(); // momo, airtel, cash, whatsapp
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index(['site_id', 'created_at']);
        });

        // Payments
        Schema::create('growbuilder_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('growbuilder_orders')->onDelete('cascade');
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->enum('provider', ['momo', 'airtel', 'cash', 'bank'])->default('momo');
            $table->string('transaction_id')->nullable();
            $table->string('external_reference')->nullable(); // Provider's reference
            $table->integer('amount');
            $table->string('currency')->default('ZMW');
            $table->string('phone_number')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->text('status_message')->nullable();
            $table->json('provider_response')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index('transaction_id');
        });

        // Invoices
        Schema::create('growbuilder_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('growbuilder_orders')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            $table->json('items'); // Invoice line items
            $table->integer('subtotal');
            $table->integer('tax_amount')->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->integer('discount_amount')->default(0);
            $table->integer('total');
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status']);
        });

        // Coupons/Discounts
        Schema::create('growbuilder_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->integer('value'); // Percentage or fixed amount
            $table->integer('min_order_amount')->nullable();
            $table->integer('max_discount_amount')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->date('starts_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['site_id', 'code']);
        });

        // Payment Settings per site
        Schema::create('growbuilder_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            
            // MTN MoMo
            $table->boolean('momo_enabled')->default(false);
            $table->string('momo_phone')->nullable();
            $table->string('momo_api_user')->nullable();
            $table->text('momo_api_key')->nullable(); // Encrypted
            $table->string('momo_subscription_key')->nullable();
            $table->boolean('momo_sandbox')->default(true);
            
            // Airtel Money
            $table->boolean('airtel_enabled')->default(false);
            $table->string('airtel_phone')->nullable();
            $table->string('airtel_client_id')->nullable();
            $table->text('airtel_client_secret')->nullable(); // Encrypted
            $table->boolean('airtel_sandbox')->default(true);
            
            // Cash on Delivery
            $table->boolean('cod_enabled')->default(true);
            
            // WhatsApp Orders
            $table->boolean('whatsapp_enabled')->default(true);
            $table->string('whatsapp_number')->nullable();
            
            // Bank Transfer
            $table->boolean('bank_enabled')->default(false);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch')->nullable();
            
            $table->timestamps();

            $table->unique('site_id');
        });

        // Shipping Settings
        Schema::create('growbuilder_shipping_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->boolean('shipping_enabled')->default(true);
            $table->boolean('pickup_enabled')->default(true);
            $table->text('pickup_address')->nullable();
            $table->json('shipping_zones')->nullable(); // Zone-based pricing
            $table->integer('flat_rate')->nullable(); // Flat shipping rate
            $table->integer('free_shipping_threshold')->nullable();
            $table->timestamps();

            $table->unique('site_id');
        });

        // Notifications/Alerts
        Schema::create('growbuilder_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->string('type'); // order_placed, payment_received, low_stock, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['site_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growbuilder_notifications');
        Schema::dropIfExists('growbuilder_shipping_settings');
        Schema::dropIfExists('growbuilder_payment_settings');
        Schema::dropIfExists('growbuilder_coupons');
        Schema::dropIfExists('growbuilder_invoices');
        Schema::dropIfExists('growbuilder_payments');
        Schema::dropIfExists('growbuilder_orders');
        Schema::dropIfExists('growbuilder_product_categories');
        Schema::dropIfExists('growbuilder_products');
    }
};
