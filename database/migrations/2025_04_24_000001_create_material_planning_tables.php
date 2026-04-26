<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Material Categories
        Schema::create('cms_material_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('icon', 50)->default('cube');
            $table->string('color', 20)->default('blue');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'is_active']);
        });

        // Materials Library
        Schema::create('cms_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('cms_material_categories')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('unit', 50); // m², kg, pcs, meters, etc.
            $table->decimal('current_price', 15, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->nullable();
            $table->decimal('reorder_level', 10, 2)->nullable();
            $table->string('supplier', 200)->nullable();
            $table->string('supplier_code', 100)->nullable();
            $table->integer('lead_time_days')->nullable();
            $table->json('specifications')->nullable(); // dimensions, grade, color, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'category_id']);
        });

        // Material Price History
        Schema::create('cms_material_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('cms_materials')->cascadeOnDelete();
            $table->decimal('old_price', 15, 2);
            $table->decimal('new_price', 15, 2);
            $table->decimal('change_percentage', 8, 2);
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->constrained('cms_users')->cascadeOnDelete();
            $table->timestamp('effective_date');
            $table->timestamps();
            
            $table->index(['material_id', 'effective_date']);
        });

        // Job Material Plans
        Schema::create('cms_job_material_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('cms_jobs')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('cms_materials')->cascadeOnDelete();
            $table->decimal('planned_quantity', 10, 2);
            $table->decimal('unit_price', 15, 2); // Price at time of planning
            $table->decimal('total_cost', 15, 2); // planned_quantity * unit_price
            $table->decimal('actual_quantity', 10, 2)->nullable();
            $table->decimal('actual_unit_price', 15, 2)->nullable();
            $table->decimal('actual_total_cost', 15, 2)->nullable();
            $table->decimal('wastage_percentage', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['planned', 'ordered', 'received', 'used'])->default('planned');
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
            $table->timestamps();
            
            $table->index(['job_id', 'status']);
            $table->index('material_id');
        });

        // Material Purchase Orders
        Schema::create('cms_material_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs')->nullOnDelete();
            $table->string('po_number', 50)->unique();
            $table->string('supplier_name', 200);
            $table->string('supplier_contact', 100)->nullable();
            $table->text('supplier_address')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'confirmed', 'received', 'cancelled'])->default('draft');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('cms_users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
            $table->index('job_id');
        });

        // Purchase Order Items
        Schema::create('cms_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('cms_material_purchase_orders')->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('cms_materials')->nullOnDelete();
            $table->foreignId('job_material_plan_id')->nullable()->constrained('cms_job_material_plans')->nullOnDelete();
            $table->string('description', 500);
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 50);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->decimal('received_quantity', 10, 2)->default(0);
            $table->timestamps();
            
            $table->index('purchase_order_id');
        });

        // Material Templates (for common job types)
        Schema::create('cms_material_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('job_type', 100); // matches job types
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
            $table->timestamps();
            
            $table->index(['company_id', 'job_type']);
        });

        // Material Template Items
        Schema::create('cms_material_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('cms_material_templates')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('cms_materials')->cascadeOnDelete();
            $table->decimal('quantity_per_unit', 10, 2); // e.g., per m² of window
            $table->decimal('wastage_percentage', 5, 2)->default(5);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_material_template_items');
        Schema::dropIfExists('cms_material_templates');
        Schema::dropIfExists('cms_purchase_order_items');
        Schema::dropIfExists('cms_material_purchase_orders');
        Schema::dropIfExists('cms_job_material_plans');
        Schema::dropIfExists('cms_material_price_history');
        Schema::dropIfExists('cms_materials');
        Schema::dropIfExists('cms_material_categories');
    }
};
