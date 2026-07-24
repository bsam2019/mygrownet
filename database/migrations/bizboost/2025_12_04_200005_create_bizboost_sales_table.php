<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('bizboost_customers')->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained('bizboost_products')->onDelete('set null');
            $table->string('product_name'); // Store name in case product is deleted
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->string('currency')->default('ZMW');
            $table->date('sale_date');
            $table->string('payment_method')->nullable(); // cash, mobile_money, card, etc.
            $table->string('source')->default('manual'); // manual, linked_post, etc.
            $table->foreignId('linked_post_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['business_id', 'sale_date']);
            $table->index(['business_id', 'product_id']);
            $table->index(['business_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_sales');
    }
};
