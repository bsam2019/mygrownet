<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventory Categories
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->string('color', 20)->default('#6b7280');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'name']);
            $table->index(['user_id', 'sort_order']);
        });

        // Inventory Items
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 255);
            $table->string('sku', 100)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('inventory_categories')->onDelete('set null');
            $table->string('unit', 50)->default('piece');
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->integer('current_stock')->default(0);
            $table->integer('low_stock_threshold')->default(10);
            $table->string('location', 255)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->string('image_path', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('track_stock')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'category_id']);
            $table->index(['user_id', 'current_stock']);
            $table->index(['user_id', 'sku']);
            $table->index(['user_id', 'barcode']);
        });

        // Stock Movements (audit trail)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['purchase', 'sale', 'adjustment', 'transfer', 'return', 'damage', 'initial']);
            $table->integer('quantity'); // positive for in, negative for out
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_value', 12, 2)->nullable();
            $table->string('reference_type', 50)->nullable(); // invoice, order, manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('movement_date');
            $table->timestamps();

            $table->index(['item_id', 'created_at']);
            $table->index(['user_id', 'type']);
            $table->index(['movement_date']);
        });

        // Low Stock Alerts
        Schema::create('inventory_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['low_stock', 'out_of_stock', 'overstock']);
            $table->integer('threshold_value');
            $table->integer('current_value');
            $table->boolean('is_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_acknowledged']);
            $table->index(['item_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_alerts');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_categories');
    }
};
