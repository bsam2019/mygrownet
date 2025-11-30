<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Performance Reviews
        if (!Schema::hasTable('employee_performance_reviews')) {
            Schema::create('employee_performance_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('reviewer_id')->nullable()->constrained('employees')->onDelete('set null');
                $table->string('review_period'); // e.g., "Q1 2025", "Annual 2024"
                $table->enum('review_type', ['self', 'manager', 'peer', '360'])->default('manager');
                $table->enum('status', ['draft', 'submitted', 'in_review', 'completed'])->default('draft');
                $table->decimal('overall_rating', 3, 2)->nullable();
                $table->json('ratings')->nullable();
                $table->text('strengths')->nullable();
                $table->text('improvements')->nullable();
                $table->text('goals_for_next_period')->nullable();
                $table->text('employee_comments')->nullable();
                $table->text('manager_comments')->nullable();
                $table->date('due_date')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                
                $table->index(['employee_id', 'review_period']);
                $table->index(['status', 'due_date']);
            });
        }

        // Training Courses
        if (!Schema::hasTable('employee_training_courses')) {
            Schema::create('employee_training_courses', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('category');
                $table->enum('type', ['online', 'classroom', 'workshop', 'certification'])->default('online');
                $table->integer('duration_hours')->default(1);
                $table->string('provider')->nullable();
                $table->string('url')->nullable();
                $table->boolean('is_mandatory')->default(false);
                $table->boolean('is_active')->default(true);
                $table->json('skills')->nullable();
                $table->timestamps();
            });
        }

        // Course Enrollments
        if (!Schema::hasTable('employee_course_enrollments')) {
            Schema::create('employee_course_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('course_id')->constrained('employee_training_courses')->onDelete('cascade');
                $table->enum('status', ['assigned', 'in_progress', 'completed', 'expired'])->default('assigned');
                $table->integer('progress')->default(0);
                $table->date('assigned_date');
                $table->date('due_date')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->decimal('score', 5, 2)->nullable();
                $table->string('certificate_path')->nullable();
                $table->timestamps();
                
                $table->unique(['employee_id', 'course_id']);
                $table->index(['employee_id', 'status']);
            });
        }

        // Certifications
        if (!Schema::hasTable('employee_certifications')) {
            Schema::create('employee_certifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->string('name');
                $table->string('issuing_organization');
                $table->string('credential_id')->nullable();
                $table->date('issue_date');
                $table->date('expiry_date')->nullable();
                $table->string('certificate_path')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->timestamps();
                
                $table->index(['employee_id', 'expiry_date']);
            });
        }

        // Expenses
        if (!Schema::hasTable('employee_expenses')) {
            Schema::create('employee_expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('category');
                $table->decimal('amount', 12, 2);
                $table->string('currency', 3)->default('ZMW');
                $table->date('expense_date');
                $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'reimbursed'])->default('draft');
                $table->json('receipts')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('reimbursed_at')->nullable();
                $table->string('reimbursement_reference')->nullable();
                $table->timestamps();
                
                $table->index(['employee_id', 'status']);
            });
        }

        // Support Tickets (Help Desk)
        if (!Schema::hasTable('employee_support_tickets')) {
            Schema::create('employee_support_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number')->unique();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('assigned_to')->nullable()->constrained('employees')->onDelete('set null');
                $table->string('subject');
                $table->text('description');
                $table->string('category'); // IT, HR, Facilities, etc.
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->enum('status', ['open', 'in_progress', 'waiting', 'resolved', 'closed'])->default('open');
                $table->json('attachments')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();
                
                $table->index(['employee_id', 'status']);
                $table->index(['status', 'priority']);
            });
        }

        // Support Ticket Comments
        if (!Schema::hasTable('employee_support_ticket_comments')) {
            Schema::create('employee_support_ticket_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('employee_support_tickets')->onDelete('cascade');
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->text('content');
                $table->boolean('is_internal')->default(false);
                $table->json('attachments')->nullable();
                $table->timestamps();
            });
        }

        // Calendar Events
        if (!Schema::hasTable('employee_calendar_events')) {
            Schema::create('employee_calendar_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('created_by')->nullable()->constrained('employees')->onDelete('set null');
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('type', ['meeting', 'training', 'deadline', 'holiday', 'personal', 'team'])->default('meeting');
                $table->datetime('start_time');
                $table->datetime('end_time');
                $table->boolean('is_all_day')->default(false);
                $table->string('location')->nullable();
                $table->string('meeting_link')->nullable();
                $table->enum('status', ['scheduled', 'cancelled', 'completed'])->default('scheduled');
                $table->json('attendees')->nullable();
                $table->json('reminders')->nullable();
                $table->timestamps();
                
                $table->index(['employee_id', 'start_time']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_calendar_events');
        Schema::dropIfExists('employee_support_ticket_comments');
        Schema::dropIfExists('employee_support_tickets');
        Schema::dropIfExists('employee_expenses');
        Schema::dropIfExists('employee_certifications');
        Schema::dropIfExists('employee_course_enrollments');
        Schema::dropIfExists('employee_training_courses');
        Schema::dropIfExists('employee_performance_reviews');
    }
};
