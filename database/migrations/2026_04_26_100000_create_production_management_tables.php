<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Production Orders
        Schema::create('cms_production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('cms_jobs')->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->date('required_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->decimal('estimated_hours', 10, 2)->nullable();
            $table->decimal('actual_hours', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'status']);
            $table->index(['job_id']);
        });

        // Cutting Lists
        Schema::create('cms_cutting_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('production_order_id')->constrained('cms_production_orders')->cascadeOnDelete();
            $table->string('list_number')->unique();
            $table->date('generated_date');
            $table->enum('status', ['draft', 'approved', 'in_progress', 'completed'])->default('draft');
            $table->decimal('total_length_required', 10, 2)->default(0);
            $table->decimal('total_length_used', 10, 2)->default(0);
            $table->decimal('waste_percentage', 5, 2)->default(0);
            $table->boolean('optimized')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'status']);
            $table->index(['production_order_id']);
        });

        // Cutting List Items
        Schema::create('cms_cutting_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cutting_list_id')->constrained('cms_cutting_lists')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('cms_materials');
            $table->string('item_code')->nullable();
            $table->string('description');
            $table->decimal('required_length', 10, 2);
            $table->integer('quantity');
            $table->decimal('total_length', 10, 2);
            $table->string('stock_length')->nullable(); // e.g., "6m"
            $table->integer('pieces_per_stock')->nullable();
            $table->decimal('waste_per_stock', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('cut')->default(false);
            $table->timestamp('cut_at')->nullable();
            $table->foreignId('cut_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['cutting_list_id']);
            $table->index(['material_id']);
        });

        // Production Tracking
        Schema::create('cms_production_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('cms_production_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('stage', ['cutting', 'assembly', 'finishing', 'quality_check', 'packaging'])->default('cutting');
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'on_hold'])->default('not_started');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('hours_spent', 10, 2)->nullable();
            $table->integer('quantity_completed')->default(0);
            $table->integer('quantity_rejected')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['production_order_id', 'stage']);
        });

        // Waste Tracking
        Schema::create('cms_waste_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('production_order_id')->nullable()->constrained('cms_production_orders')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('cms_materials');
            $table->date('waste_date');
            $table->enum('waste_type', ['offcut', 'damaged', 'defective', 'expired', 'other'])->default('offcut');
            $table->decimal('quantity', 10, 2);
            $table->string('unit');
            $table->decimal('value', 10, 2)->nullable();
            $table->enum('disposal_method', ['reuse', 'recycle', 'scrap', 'discard'])->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['company_id', 'waste_date']);
            $table->index(['production_order_id']);
            $table->index(['material_id']);
        });

        // Workshop Capacity Planning
        Schema::create('cms_workshop_capacity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->date('date');
            $table->integer('available_workers')->default(0);
            $table->decimal('available_hours', 10, 2)->default(0);
            $table->decimal('scheduled_hours', 10, 2)->default(0);
            $table->decimal('actual_hours', 10, 2)->default(0);
            $table->decimal('utilization_percentage', 5, 2)->default(0);
            $table->boolean('is_working_day')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['company_id', 'date']);
            $table->index(['company_id', 'date']);
        });

        // Quality Control Checkpoints
        Schema::create('cms_quality_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('cms_production_orders')->cascadeOnDelete();
            $table->string('checkpoint_name');
            $table->enum('stage', ['cutting', 'assembly', 'finishing', 'final_inspection'])->default('final_inspection');
            $table->enum('status', ['pending', 'passed', 'failed', 'waived'])->default('pending');
            $table->foreignId('inspector_id')->nullable()->constrained('users');
            $table->timestamp('inspected_at')->nullable();
            $table->text('findings')->nullable();
            $table->text('corrective_action')->nullable();
            $table->boolean('requires_rework')->default(false);
            $table->timestamps();
            
            $table->index(['production_order_id', 'stage']);
        });

        // Production Materials Usage
        Schema::create('cms_production_materials_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('cms_production_orders')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('cms_materials');
            $table->decimal('planned_quantity', 10, 2);
            $table->decimal('actual_quantity', 10, 2)->default(0);
            $table->decimal('variance', 10, 2)->default(0);
            $table->string('unit');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['production_order_id']);
            $table->index(['material_id']);
        });

        // Cutting Optimization Patterns
        Schema::create('cms_cutting_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('cms_materials');
            $table->string('pattern_name');
            $table->decimal('stock_length', 10, 2);
            $table->json('cuts'); // Array of cut lengths
            $table->decimal('total_used', 10, 2);
            $table->decimal('waste', 10, 2);
            $table->decimal('efficiency_percentage', 5, 2);
            $table->integer('usage_count')->default(0);
            $table->boolean('is_template')->default(false);
            $table->timestamps();
            
            $table->index(['company_id', 'material_id']);
        });

        // Production Schedule
        Schema::create('cms_production_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('production_order_id')->constrained('cms_production_orders')->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->foreignId('assigned_worker_id')->nullable()->constrained('users');
            $table->string('workstation')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'scheduled_date']);
            $table->index(['production_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_production_schedule');
        Schema::dropIfExists('cms_cutting_patterns');
        Schema::dropIfExists('cms_production_materials_usage');
        Schema::dropIfExists('cms_quality_checkpoints');
        Schema::dropIfExists('cms_workshop_capacity');
        Schema::dropIfExists('cms_waste_tracking');
        Schema::dropIfExists('cms_production_tracking');
        Schema::dropIfExists('cms_cutting_list_items');
        Schema::dropIfExists('cms_cutting_lists');
        Schema::dropIfExists('cms_production_orders');
    }
};
