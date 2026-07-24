<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Performance Review Cycles
        if (!Schema::hasTable('cms_performance_cycles')) {
            Schema::create('cms_performance_cycles', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('cycle_name');
                        $table->enum('cycle_type', ['annual', 'semi_annual', 'quarterly', 'probation', 'project']);
                        $table->date('start_date');
                        $table->date('end_date');
                        $table->date('review_deadline')->nullable();
                        $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
                        $table->text('description')->nullable();
                        $table->timestamps();
                        
                        $table->index(['company_id', 'status']);
                    });
        }

        // Performance Reviews
        if (!Schema::hasTable('cms_performance_reviews')) {
            Schema::create('cms_performance_reviews', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('cycle_id')->constrained('cms_performance_cycles')->onDelete('cascade');
                        $table->foreignId('worker_id')->constrained('cms_workers')->onDelete('cascade');
                        $table->foreignId('reviewer_id')->constrained('cms_users')->onDelete('cascade');
                        $table->enum('review_type', ['self', 'manager', 'peer', '360']);
                        $table->date('due_date');
                        $table->date('submitted_date')->nullable();
                        $table->enum('status', ['pending', 'in_progress', 'submitted', 'completed'])->default('pending');
                        $table->decimal('overall_rating', 3, 2)->nullable();
                        $table->text('strengths')->nullable();
                        $table->text('areas_for_improvement')->nullable();
                        $table->text('achievements')->nullable();
                        $table->text('goals_met')->nullable();
                        $table->text('reviewer_comments')->nullable();
                        $table->text('employee_comments')->nullable();
                        $table->timestamps();
                        
                        $table->index(['worker_id', 'status']);
                        $table->index(['reviewer_id', 'status']);
                    });
        }

        // Performance Criteria
        if (!Schema::hasTable('cms_performance_criteria')) {
            Schema::create('cms_performance_criteria', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('criteria_name');
                        $table->text('description')->nullable();
                        $table->enum('category', ['technical', 'behavioral', 'leadership', 'communication', 'teamwork', 'productivity', 'quality', 'other']);
                        $table->integer('weight_percentage')->default(10);
                        $table->boolean('is_active')->default(true);
                        $table->timestamps();
                        
                        $table->index(['company_id', 'is_active']);
                    });
        }

        // Performance Ratings
        if (!Schema::hasTable('cms_performance_ratings')) {
            Schema::create('cms_performance_ratings', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('review_id')->constrained('cms_performance_reviews')->onDelete('cascade');
                        $table->foreignId('criteria_id')->constrained('cms_performance_criteria')->onDelete('cascade');
                        $table->decimal('rating', 3, 2);
                        $table->text('comments')->nullable();
                        $table->timestamps();
                        
                        $table->index('review_id');
                    });
        }

        // Goals and Objectives
        if (!Schema::hasTable('cms_goals')) {
            Schema::create('cms_goals', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->foreignId('worker_id')->constrained('cms_workers')->onDelete('cascade');
                        $table->foreignId('set_by_user_id')->constrained('cms_users')->onDelete('cascade');
                        $table->string('goal_title');
                        $table->text('description');
                        $table->enum('goal_type', ['individual', 'team', 'department', 'company']);
                        $table->enum('category', ['performance', 'development', 'project', 'behavioral', 'other']);
                        $table->date('start_date');
                        $table->date('target_date');
                        $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
                        $table->enum('status', ['not_started', 'in_progress', 'completed', 'cancelled', 'overdue'])->default('not_started');
                        $table->integer('progress_percentage')->default(0);
                        $table->text('success_criteria')->nullable();
                        $table->text('notes')->nullable();
                        $table->date('completed_date')->nullable();
                        $table->timestamps();
                        
                        $table->index(['worker_id', 'status']);
                        $table->index(['company_id', 'status']);
                    });
        }

        // Goal Progress Updates
        if (!Schema::hasTable('cms_goal_progress')) {
            Schema::create('cms_goal_progress', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('goal_id')->constrained('cms_goals')->onDelete('cascade');
                        $table->foreignId('updated_by_user_id')->constrained('cms_users')->onDelete('cascade');
                        $table->integer('progress_percentage');
                        $table->text('update_notes');
                        $table->date('update_date');
                        $table->timestamps();
                        
                        $table->index('goal_id');
                    });
        }

        // Performance Improvement Plans (PIP)
        if (!Schema::hasTable('cms_improvement_plans')) {
            Schema::create('cms_improvement_plans', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->foreignId('worker_id')->constrained('cms_workers')->onDelete('cascade');
                        $table->foreignId('created_by_user_id')->constrained('cms_users')->onDelete('cascade');
                        $table->string('plan_title');
                        $table->text('performance_issues');
                        $table->text('improvement_actions');
                        $table->text('support_provided')->nullable();
                        $table->date('start_date');
                        $table->date('review_date');
                        $table->date('end_date');
                        $table->enum('status', ['active', 'successful', 'unsuccessful', 'extended', 'cancelled'])->default('active');
                        $table->text('outcome_notes')->nullable();
                        $table->timestamps();
                        
                        $table->index(['worker_id', 'status']);
                    });
        }

        // PIP Milestones
        if (!Schema::hasTable('cms_pip_milestones')) {
            Schema::create('cms_pip_milestones', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('improvement_plan_id')->constrained('cms_improvement_plans')->onDelete('cascade');
                        $table->string('milestone_title');
                        $table->text('description');
                        $table->date('target_date');
                        $table->boolean('is_completed')->default(false);
                        $table->date('completed_date')->nullable();
                        $table->text('completion_notes')->nullable();
                        $table->timestamps();
                        
                        $table->index('improvement_plan_id');
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pip_milestones');
        Schema::dropIfExists('cms_improvement_plans');
        Schema::dropIfExists('cms_goal_progress');
        Schema::dropIfExists('cms_goals');
        Schema::dropIfExists('cms_performance_ratings');
        Schema::dropIfExists('cms_performance_criteria');
        Schema::dropIfExists('cms_performance_reviews');
        Schema::dropIfExists('cms_performance_cycles');
    }
};
