<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * GrowBiz Task Management System
     * 
     * Tables for the GrowBiz Business Tools module - Task & Employee Management
     */
    public function up(): void
    {
        // GrowBiz Employees - Staff managed by business owners
        Schema::create('growbiz_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('position', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');
            $table->date('hire_date')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['manager_id', 'status']);
            $table->index(['manager_id', 'department']);
            $table->index('user_id');
        });

        // GrowBiz Tasks
        Schema::create('growbiz_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'on_hold', 'completed', 'cancelled'])->default('pending');
            $table->string('category', 100)->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['manager_id', 'status']);
            $table->index(['manager_id', 'due_date']);
            $table->index('priority');
        });

        // Task Assignments (many-to-many: tasks can have multiple assignees)
        Schema::create('growbiz_task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('growbiz_tasks')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('growbiz_employees')->onDelete('cascade');
            $table->timestamp('assigned_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['task_id', 'employee_id']);
            $table->index('employee_id');
        });

        // Task Updates (history/audit trail with progress and time tracking)
        Schema::create('growbiz_task_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('growbiz_tasks')->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained('growbiz_employees')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('update_type', ['status_change', 'progress_update', 'time_log', 'note'])->default('note');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->unsignedTinyInteger('old_progress')->nullable();
            $table->unsignedTinyInteger('new_progress')->nullable();
            $table->decimal('hours_logged', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['task_id', 'created_at']);
            $table->index(['task_id', 'update_type']);
        });

        // Task Comments (threaded communication)
        Schema::create('growbiz_task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('growbiz_tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['task_id', 'created_at']);
        });

        // Task Attachments (proof of work)
        Schema::create('growbiz_task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('growbiz_tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_name');
            $table->enum('file_type', ['image', 'document', 'text'])->default('document');
            $table->integer('file_size')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('task_id');
        });

        // Task Categories (optional organization)
        Schema::create('growbiz_task_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('color', 20)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['manager_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growbiz_task_categories');
        Schema::dropIfExists('growbiz_task_attachments');
        Schema::dropIfExists('growbiz_task_comments');
        Schema::dropIfExists('growbiz_task_updates');
        Schema::dropIfExists('growbiz_task_assignments');
        Schema::dropIfExists('growbiz_tasks');
        Schema::dropIfExists('growbiz_employees');
    }
};
