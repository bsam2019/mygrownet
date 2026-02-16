<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Onboarding Templates
        Schema::create('cms_onboarding_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('template_name');
            $table->foreignId('department_id')->nullable()->constrained('cms_departments')->onDelete('set null');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index(['company_id', 'is_default']);
        });

        // Onboarding Tasks
        Schema::create('cms_onboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('cms_onboarding_templates')->onDelete('cascade');
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->enum('task_category', ['documentation', 'system_access', 'training', 'equipment', 'introduction', 'other']);
            $table->enum('assigned_to_role', ['hr', 'it', 'manager', 'employee']);
            $table->integer('due_days_after_start');
            $table->boolean('is_mandatory')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index(['template_id', 'display_order']);
        });

        // Employee Onboarding
        Schema::create('cms_employee_onboarding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('cms_workers')->onDelete('cascade');
            $table->foreignId('template_id')->constrained('cms_onboarding_templates')->onDelete('cascade');
            $table->date('start_date');
            $table->date('expected_completion_date');
            $table->date('actual_completion_date')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->timestamps();
            
            $table->index(['worker_id', 'status']);
        });

        // Onboarding Task Progress
        Schema::create('cms_onboarding_task_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarding_id')->constrained('cms_employee_onboarding')->onDelete('cascade');
            $table->foreignId('task_id')->constrained('cms_onboarding_tasks')->onDelete('cascade');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('cms_users')->onDelete('set null');
            $table->date('due_date');
            $table->date('completed_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['onboarding_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_onboarding_task_progress');
        Schema::dropIfExists('cms_employee_onboarding');
        Schema::dropIfExists('cms_onboarding_tasks');
        Schema::dropIfExists('cms_onboarding_templates');
    }
};
