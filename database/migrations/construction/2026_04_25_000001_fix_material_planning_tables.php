<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Create missing material planning tables
     */
    public function up(): void
    {
        // Create cms_job_material_plans if it doesn't exist
        if (!Schema::hasTable('cms_job_material_plans')) {
            Schema::create('cms_job_material_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_id')->constrained('cms_jobs')->cascadeOnDelete();
                $table->foreignId('material_id')->constrained('cms_materials')->cascadeOnDelete();
                $table->decimal('planned_quantity', 10, 2);
                $table->decimal('unit_price', 15, 2);
                $table->decimal('total_cost', 15, 2);
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
        }

        // Create cms_purchase_order_items if it doesn't exist
        if (!Schema::hasTable('cms_purchase_order_items')) {
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
        }

        // Create cms_material_templates if it doesn't exist
        if (!Schema::hasTable('cms_material_templates')) {
            Schema::create('cms_material_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->string('name', 200);
                $table->string('job_type', 100);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
                $table->timestamps();
                
                $table->index(['company_id', 'job_type']);
            });
        }

        // Create cms_material_template_items if it doesn't exist
        if (!Schema::hasTable('cms_material_template_items')) {
            Schema::create('cms_material_template_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->constrained('cms_material_templates')->cascadeOnDelete();
                $table->foreignId('material_id')->constrained('cms_materials')->cascadeOnDelete();
                $table->decimal('quantity_per_unit', 10, 2);
                $table->decimal('wastage_percentage', 5, 2)->default(5);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_material_template_items');
        Schema::dropIfExists('cms_material_templates');
        Schema::dropIfExists('cms_purchase_order_items');
        Schema::dropIfExists('cms_job_material_plans');
    }
};
