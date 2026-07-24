<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Payslips
        if (!Schema::hasTable('employee_payslips')) {
            Schema::create('employee_payslips', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->string('pay_period');
                $table->date('pay_date');
                $table->decimal('gross_salary', 12, 2);
                $table->decimal('basic_salary', 12, 2);
                $table->decimal('allowances', 12, 2)->default(0);
                $table->decimal('overtime_pay', 12, 2)->default(0);
                $table->decimal('bonus', 12, 2)->default(0);
                $table->decimal('commission', 12, 2)->default(0);
                $table->decimal('deductions', 12, 2)->default(0);
                $table->decimal('tax', 12, 2)->default(0);
                $table->decimal('pension', 12, 2)->default(0);
                $table->decimal('insurance', 12, 2)->default(0);
                $table->decimal('net_salary', 12, 2);
                $table->json('breakdown')->nullable();
                $table->string('pdf_path')->nullable();
                $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
                $table->timestamps();
                
                $table->unique(['employee_id', 'pay_period']);
                $table->index(['employee_id', 'pay_date']);
            });
        }

        // Tax Documents
        if (!Schema::hasTable('employee_tax_documents')) {
            Schema::create('employee_tax_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->string('document_type');
                $table->string('tax_year');
                $table->string('title');
                $table->string('file_path');
                $table->date('issue_date');
                $table->timestamps();
                
                $table->index(['employee_id', 'tax_year']);
            });
        }

        // Performance Reviews
        if (!Schema::hasTable('employee_performance_reviews')) {
            Schema::create('employee_performance_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('reviewer_id')->nullable()->constrained('employees')->onDelete('set null');
                $table->string('review_period');
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

        // Employee Certifications
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
                $table->index(['status', 'submitted_at']);
            });
        }

        // Company Announcements
        if (!Schema::hasTable('company_announcements')) {
            Schema::create('company_announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->enum('type', ['general', 'policy', 'event', 'hr', 'it'])->default('general');
                $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
                $table->boolean('is_published')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('requires_acknowledgment')->default(false);
                $table->timestamps();
                
                $table->index(['is_published', 'published_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_announcements');
        Schema::dropIfExists('employee_certifications');
        Schema::dropIfExists('employee_course_enrollments');
        Schema::dropIfExists('employee_training_courses');
        Schema::dropIfExists('employee_performance_reviews');
        Schema::dropIfExists('employee_tax_documents');
        Schema::dropIfExists('employee_payslips');
        Schema::dropIfExists('employee_expenses');
    }
};
