<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->string('name');
            $table->string('sku')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('currency')->default('ZMW');
            $table->string('category')->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->boolean('track_inventory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('attributes')->nullable(); // size, color, etc.
            $table->timestamps();
            
            $table->index(['business_id', 'is_active']);
            $table->index(['business_id', 'category']);
            $table->index(['business_id', 'is_featured']);
        });

        Schema::create('bizboost_product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('bizboost_products')->onDelete('cascade');
            $table->string('path');
            $table->string('filename');
            $table->integer('file_size')->default(0); // in bytes
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['product_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_product_images');
        Schema::dropIfExists('bizboost_products');
    }
};
