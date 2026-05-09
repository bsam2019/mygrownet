<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Resource Allocation Tables
        Schema::create('cms_task_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('task_id')->constrained('cms_tasks')->onDelete('cascade');
            $table->string('resource_type'); // 'employee', 'vehicle', 'equipment'
            $table->unsignedBigInteger('resource_id');
            $table->dateTime('allocated_from');
            $table->dateTime('allocated_to');
            $table->enum('status', ['allocated', 'in_use', 'completed', 'cancelled'])->default('allocated');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['resource_type', 'resource_id']);
            $table->index(['allocated_from', 'allocated_to']);
        });

        // Resource Availability
        Schema::create('cms_resource_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('resource_type'); // 'employee', 'vehicle', 'equipment'
            $table->unsignedBigInteger('resource_id');
            $table->date('date');
            $table->time('available_from')->nullable();
            $table->time('available_to')->nullable();
            $table->boolean('is_available')->default(true);
            $table->string('unavailability_reason')->nullable();
            $table->timestamps();

            $table->unique(['resource_type', 'resource_id', 'date']);
        });

        // Workload Snapshots (for historical tracking)
        if (!Schema::hasColumn('cms_workload_snapshots', 'completed_tasks_count')) {
            Schema::table('cms_workload_snapshots', function (Blueprint $table) {
                $table->integer('completed_tasks_count')->default(0)->after('assigned_tasks_count');
                $table->integer('overdue_tasks_count')->default(0)->after('completed_tasks_count');
                $table->decimal('average_completion_time_hours', 8, 2)->nullable()->after('utilization_percentage');
            });
        }

        // Task Estimates vs Actuals
        if (!Schema::hasColumn('cms_tasks', 'estimated_cost')) {
            Schema::table('cms_tasks', function (Blueprint $table) {
                $table->decimal('estimated_cost', 10, 2)->nullable()->after('estimated_hours');
                $table->decimal('actual_cost', 10, 2)->nullable()->after('actual_hours');
                $table->integer('estimated_story_points')->nullable()->after('estimated_cost');
            });
        }

        // Bulk Operations Log
        Schema::create('cms_bulk_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('operation_type'); // 'reassign', 'change_priority', 'change_status', 'reschedule'
            $table->integer('tasks_affected');
            $table->json('operation_data');
            $table->json('task_ids');
            $table->timestamp('executed_at');
            $table->timestamps();
        });

        // Task Scheduling (for calendar view)
        if (!Schema::hasColumn('cms_tasks', 'scheduled_start')) {
            Schema::table('cms_tasks', function (Blueprint $table) {
                $table->dateTime('scheduled_start')->nullable()->after('due_date');
                $table->dateTime('scheduled_end')->nullable()->after('scheduled_start');
                $table->integer('duration_minutes')->nullable()->after('scheduled_end');
            });
        }

        // Integration Triggers
        Schema::create('cms_task_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('trigger_type'); // 'crm_lead', 'task_completed', 'task_overdue'
            $table->string('action_type'); // 'create_task', 'create_invoice', 'send_notification'
            $table->json('trigger_conditions');
            $table->json('action_config');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Task Trigger Executions (log)
        Schema::create('cms_task_trigger_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trigger_id')->constrained('cms_task_triggers')->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained('cms_tasks')->onDelete('set null');
            $table->json('trigger_data');
            $table->json('action_result');
            $table->enum('status', ['success', 'failed', 'skipped']);
            $table->text('error_message')->nullable();
            $table->timestamp('executed_at');
            $table->timestamps();
        });

        // Productivity Metrics
        Schema::create('cms_user_productivity_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('metric_date');
            $table->integer('tasks_completed')->default(0);
            $table->integer('tasks_started')->default(0);
            $table->decimal('hours_logged', 8, 2)->default(0);
            $table->decimal('estimated_hours', 8, 2)->default(0);
            $table->decimal('efficiency_percentage', 5, 2)->nullable(); // actual vs estimated
            $table->integer('on_time_completions')->default(0);
            $table->integer('late_completions')->default(0);
            $table->decimal('average_task_duration_hours', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'user_id', 'metric_date'], 'cms_user_prod_metrics_unique');
        });

        // Task Completion Trends
        Schema::create('cms_task_completion_trends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->date('trend_date');
            $table->string('period_type'); // 'daily', 'weekly', 'monthly'
            $table->integer('tasks_created')->default(0);
            $table->integer('tasks_completed')->default(0);
            $table->integer('tasks_overdue')->default(0);
            $table->decimal('average_completion_time_hours', 8, 2)->nullable();
            $table->decimal('completion_rate_percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'trend_date', 'period_type']);
        });

        // Gantt Chart Dependencies (enhanced)
        if (Schema::hasTable('cms_task_dependencies') && !Schema::hasColumn('cms_task_dependencies', 'lead_days')) {
            Schema::table('cms_task_dependencies', function (Blueprint $table) {
                $table->integer('lead_days')->default(0)->after('lag_days');
                $table->boolean('is_critical_path')->default(false)->after('lead_days');
            });
        }

        // Mobile Sync Queue
        Schema::create('cms_mobile_sync_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('entity_type'); // 'task', 'comment', 'attachment', 'time_entry'
            $table->unsignedBigInteger('entity_id');
            $table->string('action'); // 'create', 'update', 'delete'
            $table->json('data');
            $table->boolean('synced')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'synced']);
        });

        // Offline Changes (from mobile)
        Schema::create('cms_offline_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('device_id');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('action');
            $table->json('data');
            $table->enum('sync_status', ['pending', 'synced', 'conflict', 'failed'])->default('pending');
            $table->text('conflict_reason')->nullable();
            $table->timestamp('created_at_device');
            $table->timestamps();

            $table->index(['user_id', 'sync_status']);
        });

        // What-If Scenarios
        if (!Schema::hasColumn('cms_planning_scenarios', 'scenario_type')) {
            Schema::table('cms_planning_scenarios', function (Blueprint $table) {
                $table->enum('scenario_type', ['workload_balance', 'resource_allocation', 'deadline_adjustment', 'custom'])->default('custom')->after('description');
                $table->json('original_state')->nullable()->after('changes_json');
                $table->json('metrics_before')->nullable()->after('original_state');
                $table->json('metrics_after')->nullable()->after('metrics_before');
            });
        }

        // Bottleneck Recommendations
        if (!Schema::hasColumn('cms_workflow_bottlenecks', 'recommendations')) {
            Schema::table('cms_workflow_bottlenecks', function (Blueprint $table) {
                $table->json('recommendations')->nullable()->after('severity');
                $table->boolean('auto_detected')->default(true)->after('recommendations');
                $table->foreignId('detected_by')->nullable()->constrained('users')->onDelete('set null')->after('auto_detected');
            });
        }

        // Task Recommendations (enhanced)
        if (!Schema::hasColumn('cms_task_recommendations', 'category')) {
            Schema::table('cms_task_recommendations', function (Blueprint $table) {
                $table->string('category')->default('general')->after('recommendation_type'); // 'workload', 'deadline', 'resource', 'priority'
                $table->integer('priority_score')->default(50)->after('confidence_score'); // 0-100
                $table->timestamp('expires_at')->nullable()->after('status');
                $table->foreignId('applied_by')->nullable()->constrained('users')->onDelete('set null')->after('expires_at');
                $table->timestamp('applied_at')->nullable()->after('applied_by');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_offline_changes');
        Schema::dropIfExists('cms_mobile_sync_queue');
        Schema::dropIfExists('cms_task_completion_trends');
        Schema::dropIfExists('cms_user_productivity_metrics');
        Schema::dropIfExists('cms_task_trigger_executions');
        Schema::dropIfExists('cms_task_triggers');
        Schema::dropIfExists('cms_bulk_operations');
        Schema::dropIfExists('cms_task_resources');
        Schema::dropIfExists('cms_resource_availability');

        Schema::table('cms_tasks', function (Blueprint $table) {
            $table->dropColumn(['estimated_cost', 'actual_cost', 'estimated_story_points', 'scheduled_start', 'scheduled_end', 'duration_minutes']);
        });

        Schema::table('cms_workload_snapshots', function (Blueprint $table) {
            $table->dropColumn(['completed_tasks_count', 'overdue_tasks_count', 'average_completion_time_hours']);
        });

        Schema::table('cms_task_dependencies', function (Blueprint $table) {
            $table->dropColumn(['lead_days', 'is_critical_path']);
        });

        Schema::table('cms_planning_scenarios', function (Blueprint $table) {
            $table->dropColumn(['scenario_type', 'original_state', 'metrics_before', 'metrics_after']);
        });

        Schema::table('cms_workflow_bottlenecks', function (Blueprint $table) {
            $table->dropColumn(['recommendations', 'auto_detected', 'detected_by']);
        });

        Schema::table('cms_task_recommendations', function (Blueprint $table) {
            $table->dropColumn(['category', 'priority_score', 'expires_at', 'applied_by', 'applied_at']);
        });
    }
};
