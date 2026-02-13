<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventory Items
        Schema::create('cms_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('item_code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category', 100);
            $table->string('unit', 50); // e.g., pieces, kg, liters, meters
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->integer('current_stock')->default(0);
            $table->integer('minimum_stock')->default(0); // For low stock alerts
            $table->integer('reorder_quantity')->default(0);
            $table->string('supplier')->nullable();
            $table->string('location')->nullable(); // Storage location
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('cms_users')->onDelete('restrict');
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'category']);
            $table->index('current_stock');
        });

        // Stock Movements (for tracking all stock changes)
        Schema::create('cms_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('cms_inventory_items')->onDelete('cascade');
            $table->enum('movement_type', ['purchase', 'usage', 'adjustment', 'return', 'damage', 'transfer']);
            $table->integer('quantity'); // Positive for additions, negative for reductions
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs')->onDelete('set null');
            $table->string('reference_number')->nullable(); // PO number, invoice number, etc.
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('cms_users')->onDelete('restrict');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['company_id', 'inventory_item_id']);
            $table->index(['company_id', 'movement_type']);
            $table->index('job_id');
            $table->index('created_at');
        });

        // Job Inventory Usage (linking inventory to jobs)
        Schema::create('cms_job_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('job_id')->constrained('cms_jobs')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('cms_inventory_items')->onDelete('restrict');
            $table->integer('quantity_used');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total_cost', 15, 2);
            $table->foreignId('created_by')->constrained('cms_users')->onDelete('restrict');
            $table->timestamps();

            $table->index(['company_id', 'job_id']);
            $table->index(['company_id', 'inventory_item_id']);
        });

        // Low Stock Alerts
        Schema::create('cms_low_stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('cms_inventory_items')->onDelete('cascade');
            $table->integer('current_stock');
            $table->integer('minimum_stock');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('cms_users')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['company_id', 'is_resolved']);
            $table->index('inventory_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_low_stock_alerts');
        Schema::dropIfExists('cms_job_inventory');
        Schema::dropIfExists('cms_stock_movements');
        Schema::dropIfExists('cms_inventory_items');
    }
};
