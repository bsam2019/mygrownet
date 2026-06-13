<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growmart_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('province');
            $table->string('city');
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('growmart_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('growmart_categories')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('growmart_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('unit'); // kg, piece, litre, bundle, etc.
            $table->integer('price'); // stored in ngwee
            $table->integer('compare_price')->nullable(); // in ngwee
            $table->foreignId('category_id')->constrained('growmart_categories')->cascadeOnDelete();
            $table->string('status')->default('active'); // active, out_of_stock, discontinued
            $table->timestamps();
        });

        Schema::create('growmart_product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('growmart_products')->cascadeOnDelete();
            $table->string('path');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('growmart_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('growmart_warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('growmart_products')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->default(10);
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_id']);
        });

        Schema::create('growmart_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('growmart_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('growmart_carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('growmart_products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();

            $table->unique(['cart_id', 'product_id']);
        });

        Schema::create('growmart_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, confirmed, processing, out_for_delivery, delivered, cancelled
            $table->string('payment_status')->default('pending'); // pending, paid, failed, refunded
            $table->string('delivery_method'); // own_vehicle, yango, pickup
            $table->string('delivery_zone')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('special_instructions')->nullable();
            $table->integer('subtotal'); // ngwee
            $table->integer('delivery_fee')->default(0); // ngwee
            $table->integer('total'); // ngwee
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('growmart_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('growmart_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('growmart_products')->cascadeOnDelete();
            $table->string('product_name');
            $table->integer('quantity');
            $table->integer('unit_price'); // ngwee
            $table->integer('subtotal'); // ngwee
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growmart_order_items');
        Schema::dropIfExists('growmart_orders');
        Schema::dropIfExists('growmart_cart_items');
        Schema::dropIfExists('growmart_carts');
        Schema::dropIfExists('growmart_inventory');
        Schema::dropIfExists('growmart_product_images');
        Schema::dropIfExists('growmart_products');
        Schema::dropIfExists('growmart_categories');
        Schema::dropIfExists('growmart_warehouses');
    }
};
