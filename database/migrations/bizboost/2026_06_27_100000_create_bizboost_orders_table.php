<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->string('order_number', 50)->unique();
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('currency', 10)->default('ZMW');
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_status', 30)->default('pending');
            $table->string('order_status', 30)->default('pending');
            $table->string('source', 30)->default('direct_link');
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'order_status']);
            $table->index(['business_id', 'payment_status']);
            $table->index('order_number');
        });

        Schema::create('bizboost_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('bizboost_orders')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('bizboost_products')->onDelete('set null');
            $table->string('product_name');
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_order_items');
        Schema::dropIfExists('bizboost_orders');
    }
};
