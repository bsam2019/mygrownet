<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Task Dependencies
        Schema::create('cms_task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('cms_tasks')->onDelete('cascade');
            $table->foreignId('depends_on_task_id')->constrained('cms_tasks')->onDelete('cascade');
            $table->enum('dependency_type', ['finish_to_start', 'start_to_start', 'finish_to_finish', 'start_to_finish'])->default('finish_to_start');
            $table->integer('lag_days')->default(0);
            $table->timestamps();

            $table->unique(['task_id', 'depends_on_task_id']);
            $table->index('task_id');
            $table->index('depends_on_task_id');
        });

        // Task Attachments
        Schema::create('cms_task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('cms_tasks')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('task_id');
        });

        // Task Comments
        Schema::create('cms_task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('cms_tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('cms_task_comments')->onDelete('cascade');
            $table->text('comment');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();

            $table->index('task_id');
            $table->index('user_id');
        });

        // Task Templates
        Schema::create('cms_task_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['task', 'order', 'job', 'project_task', 'maintenance', 'inspection'])->default('task');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('workflow_id')->nullable()->constrained('cms_workflows')->onDelete('set null');
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->json('checklist_items')->nullable();
            $table->json('default_assignees')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('company_id');
        });

        // Recurring Tasks
        Schema::create('cms_recurring_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('cms_task_templates')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['task', 'order', 'job', 'project_task', 'maintenance', 'inspection'])->default('task');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('workflow_id')->nullable()->constrained('cms_workflows')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('recurrence_pattern', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('weekly');
            $table->integer('recurrence_interval')->default(1);
            $table->json('recurrence_days')->nullable(); // For weekly: [1,3,5] = Mon, Wed, Fri
            $table->integer('recurrence_day_of_month')->nullable(); // For monthly: 15 = 15th of month
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamp('next_generation_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('company_id');
            $table->index('next_generation_at');
        });

        // Task Watchers (users following a task)
        Schema::create('cms_task_watchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('cms_tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['task_id', 'user_id']);
            $table->index('task_id');
            $table->index('user_id');
        });

        // Task Time Entries (detailed time tracking)
        Schema::create('cms_task_time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('cms_tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->decimal('hours', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->timestamps();

            $table->index('task_id');
            $table->index('user_id');
        });

        // Add fields to existing tasks table
        Schema::table('cms_tasks', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->after('workflow_stage_id')->constrained('cms_task_templates')->onDelete('set null');
            $table->foreignId('recurring_task_id')->nullable()->after('template_id')->constrained('cms_recurring_tasks')->onDelete('set null');
            $table->json('tags')->nullable()->after('description');
            $table->decimal('progress_percentage', 5, 2)->default(0)->after('status');
            $table->timestamp('last_activity_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('cms_tasks', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropForeign(['recurring_task_id']);
            $table->dropColumn(['template_id', 'recurring_task_id', 'tags', 'progress_percentage', 'last_activity_at']);
        });

        Schema::dropIfExists('cms_task_time_entries');
        Schema::dropIfExists('cms_task_watchers');
        Schema::dropIfExists('cms_recurring_tasks');
        Schema::dropIfExists('cms_task_templates');
        Schema::dropIfExists('cms_task_comments');
        Schema::dropIfExists('cms_task_attachments');
        Schema::dropIfExists('cms_task_dependencies');
    }
};
