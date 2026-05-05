<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Workflows - Define process structures
        Schema::create('cms_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('workflow_type', ['production', 'installation', 'general', 'maintenance', 'custom'])->default('general');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index(['company_id', 'is_active']);
        });

        // Workflow Stages - Define steps in a workflow
        Schema::create('cms_workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('cms_workflows')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sequence_order')->default(0);
            $table->boolean('requires_approval')->default(false);
            $table->string('color')->default('#3b82f6'); // For UI display
            $table->timestamps();
            
            $table->index(['workflow_id', 'sequence_order']);
        });

        // Tasks - Unified task system
        Schema::create('cms_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('task_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['task', 'order', 'job', 'project_task', 'maintenance', 'inspection'])->default('task');
            $table->enum('status', ['pending', 'in_progress', 'blocked', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('workflow_id')->nullable()->constrained('cms_workflows');
            $table->foreignId('workflow_stage_id')->nullable()->constrained('cms_workflow_stages');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('project_id')->nullable()->constrained('cms_projects')->nullOnDelete();
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs')->nullOnDelete();
            $table->foreignId('production_order_id')->nullable()->constrained('cms_production_orders')->nullOnDelete();
            $table->foreignId('installation_schedule_id')->nullable()->constrained('cms_installation_schedules')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('estimated_hours', 10, 2)->nullable();
            $table->decimal('actual_hours', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['workflow_stage_id']);
            $table->index(['due_date']);
        });

        // Task Logs - Activity tracking
        Schema::create('cms_task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('cms_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('action', ['created', 'started', 'paused', 'resumed', 'updated', 'completed', 'blocked', 'unblocked', 'cancelled', 'reassigned', 'stage_changed'])->default('updated');
            $table->text('note')->nullable();
            $table->json('changes')->nullable(); // Store what changed
            $table->timestamps();
            
            $table->index(['task_id', 'created_at']);
        });

        // Task Issues/Blockers
        Schema::create('cms_task_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('cms_tasks')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['minor', 'moderate', 'major', 'critical'])->default('moderate');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->foreignId('reported_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['task_id', 'status']);
        });

        // Task Checklists (reusable templates)
        Schema::create('cms_task_checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('checklist_type', ['pre_task', 'during_task', 'post_task', 'quality_check', 'safety_check'])->default('during_task');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['company_id', 'checklist_type']);
        });

        // Checklist Items
        Schema::create('cms_checklist_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_template_id')->constrained('cms_task_checklist_templates')->cascadeOnDelete();
            $table->string('item_text');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['checklist_template_id', 'sort_order']);
        });

        // Task Checklist Responses
        Schema::create('cms_task_checklist_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('cms_tasks')->cascadeOnDelete();
            $table->foreignId('checklist_template_id')->constrained('cms_task_checklist_templates');
            $table->foreignId('checklist_item_id')->constrained('cms_checklist_template_items');
            $table->enum('status', ['pending', 'passed', 'failed', 'na'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users');
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
            
            $table->index(['task_id']);
        });

        // Capacity Forecasts
        Schema::create('cms_capacity_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->date('forecast_date');
            $table->decimal('forecasted_demand_hours', 10, 2)->default(0);
            $table->decimal('available_capacity_hours', 10, 2)->default(0);
            $table->decimal('capacity_gap', 10, 2)->default(0);
            $table->text('recommendations')->nullable();
            $table->timestamps();
            
            $table->unique(['company_id', 'forecast_date']);
        });

        // Task Recommendations (Decision Support)
        Schema::create('cms_task_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('cms_tasks')->cascadeOnDelete();
            $table->enum('recommendation_type', ['priority', 'assignment', 'schedule', 'resource'])->default('priority');
            $table->text('recommended_action');
            $table->decimal('confidence_score', 5, 2)->default(0); // 0-100
            $table->text('reasoning')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->timestamps();
            
            $table->index(['task_id', 'status']);
        });

        // Planning Scenarios
        Schema::create('cms_planning_scenarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('scenario_name');
            $table->text('description')->nullable();
            $table->json('changes_json'); // Store scenario changes
            $table->json('impact_analysis_json')->nullable(); // Store impact analysis
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['company_id', 'created_by']);
        });

        // Workflow Bottlenecks
        Schema::create('cms_workflow_bottlenecks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('workflow_id')->constrained('cms_workflows');
            $table->foreignId('stage_id')->constrained('cms_workflow_stages');
            $table->date('detection_date');
            $table->decimal('avg_duration_days', 10, 2)->default(0);
            $table->integer('tasks_affected')->default(0);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['active', 'monitoring', 'resolved'])->default('active');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
            $table->index(['workflow_id', 'stage_id']);
        });

        // Workload Snapshots
        Schema::create('cms_workload_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->foreignId('user_id')->constrained('users');
            $table->integer('assigned_tasks_count')->default(0);
            $table->decimal('total_hours', 10, 2)->default(0);
            $table->decimal('utilization_percentage', 5, 2)->default(0);
            $table->timestamps();
            
            $table->index(['company_id', 'snapshot_date']);
            $table->index(['user_id', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_workload_snapshots');
        Schema::dropIfExists('cms_workflow_bottlenecks');
        Schema::dropIfExists('cms_planning_scenarios');
        Schema::dropIfExists('cms_task_recommendations');
        Schema::dropIfExists('cms_capacity_forecasts');
        Schema::dropIfExists('cms_task_checklist_responses');
        Schema::dropIfExists('cms_checklist_template_items');
        Schema::dropIfExists('cms_task_checklist_templates');
        Schema::dropIfExists('cms_task_issues');
        Schema::dropIfExists('cms_task_logs');
        Schema::dropIfExists('cms_tasks');
        Schema::dropIfExists('cms_workflow_stages');
        Schema::dropIfExists('cms_workflows');
    }
};
