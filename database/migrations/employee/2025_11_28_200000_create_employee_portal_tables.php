<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Employee Tasks
        Schema::create('employee_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->constrained('employees')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('employees')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['todo', 'in_progress', 'review', 'completed', 'cancelled'])->default('todo');
            $table->date('due_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['assigned_to', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('priority');
        });

        // Task Comments
        Schema::create('employee_task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('employee_tasks')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
        });

        // Task Attachments
        Schema::create('employee_task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('employee_tasks')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('employees')->onDelete('cascade');
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('size');
            $table->timestamps();
        });


        // Time Off Requests
        Schema::create('employee_time_off_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('type', ['annual', 'sick', 'personal', 'bereavement', 'maternity', 'paternity', 'unpaid', 'study'])->default('annual');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_requested', 4, 1);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // Time Off Balances
        Schema::create('employee_time_off_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->year('year');
            $table->decimal('vacation_days', 4, 1)->default(0);
            $table->decimal('vacation_used', 4, 1)->default(0);
            $table->decimal('sick_days', 4, 1)->default(0);
            $table->decimal('sick_used', 4, 1)->default(0);
            $table->decimal('personal_days', 4, 1)->default(0);
            $table->decimal('personal_used', 4, 1)->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'year']);
        });

        // Employee Documents
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('category', ['contract', 'payslip', 'tax', 'certificate', 'policy', 'other'])->default('other');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('size');
            $table->boolean('is_confidential')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'category']);
        });

        // Employee Notifications
        Schema::create('employee_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'read_at']);
        });

        // Employee Goals (enhanced)
        Schema::create('employee_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['performance', 'development', 'project', 'personal'])->default('performance');
            $table->integer('progress')->default(0);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->date('start_date')->nullable();
            $table->date('due_date');
            $table->timestamp('completed_at')->nullable();
            $table->json('milestones')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });

        // Attendance/Time Tracking
        Schema::create('employee_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->decimal('hours_worked', 4, 2)->nullable();
            $table->decimal('overtime_hours', 4, 2)->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'remote', 'holiday'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index(['date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_attendance');
        Schema::dropIfExists('employee_goals');
        Schema::dropIfExists('employee_notifications');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employee_time_off_balances');
        Schema::dropIfExists('employee_time_off_requests');
        Schema::dropIfExists('employee_task_attachments');
        Schema::dropIfExists('employee_task_comments');
        Schema::dropIfExists('employee_tasks');
    }
};
