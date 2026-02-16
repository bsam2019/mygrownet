<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Employee portal access
        Schema::create('cms_employee_portal_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('cms_workers')->cascadeOnDelete();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('reset_token')->nullable();
            $table->timestamp('reset_token_expires_at')->nullable();
            $table->timestamps();
            
            $table->index('email');
        });

        // Document requests
        Schema::create('cms_document_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('cms_workers')->cascadeOnDelete();
            $table->enum('document_type', ['payslip', 'employment_letter', 'tax_certificate', 'leave_balance', 'attendance_report', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
            $table->string('document_path')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('cms_users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index(['worker_id', 'status']);
        });

        // Profile update requests
        Schema::create('cms_profile_update_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('cms_workers')->cascadeOnDelete();
            $table->json('current_data');
            $table->json('requested_data');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('cms_users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            
            $table->index(['worker_id', 'status']);
        });

        // Announcements
        Schema::create('cms_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('target_audience', ['all', 'department', 'specific_workers'])->default('all');
            $table->foreignId('department_id')->nullable()->constrained('cms_departments')->nullOnDelete();
            $table->date('publish_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
            $table->timestamps();
            
            $table->index(['company_id', 'is_published']);
            $table->index('publish_date');
        });

        // Announcement recipients (for specific workers)
        Schema::create('cms_announcement_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('cms_announcements')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('cms_workers')->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->unique(['announcement_id', 'worker_id']);
            $table->index('is_read');
        });

        // Employee feedback/suggestions
        Schema::create('cms_employee_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('cms_workers')->cascadeOnDelete();
            $table->enum('type', ['feedback', 'suggestion', 'complaint', 'appreciation'])->default('feedback');
            $table->enum('category', ['workplace', 'management', 'facilities', 'benefits', 'training', 'other'])->default('other');
            $table->string('subject');
            $table->text('message');
            $table->boolean('is_anonymous')->default(false);
            $table->enum('status', ['submitted', 'under_review', 'resolved', 'closed'])->default('submitted');
            $table->foreignId('assigned_to')->nullable()->constrained('cms_users')->nullOnDelete();
            $table->text('response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            
            $table->index(['worker_id', 'status']);
            $table->index('type');
        });

        // Time-off balance summary (cached view)
        Schema::create('cms_time_off_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->unique()->constrained('cms_workers')->cascadeOnDelete();
            $table->integer('annual_leave_balance')->default(0);
            $table->integer('sick_leave_balance')->default(0);
            $table->integer('total_leave_taken')->default(0);
            $table->integer('pending_requests')->default(0);
            $table->date('last_updated');
            $table->timestamps();
        });

        // Personal documents storage
        Schema::create('cms_worker_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('cms_workers')->cascadeOnDelete();
            $table->string('document_name');
            $table->enum('document_type', ['contract', 'id_copy', 'certificate', 'payslip', 'tax_document', 'other'])->default('other');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->foreignId('uploaded_by')->constrained('cms_users')->cascadeOnDelete();
            $table->boolean('is_visible_to_employee')->default(true);
            $table->timestamps();
            
            $table->index(['worker_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_worker_documents');
        Schema::dropIfExists('cms_time_off_summary');
        Schema::dropIfExists('cms_employee_feedback');
        Schema::dropIfExists('cms_announcement_recipients');
        Schema::dropIfExists('cms_announcements');
        Schema::dropIfExists('cms_profile_update_requests');
        Schema::dropIfExists('cms_document_requests');
        Schema::dropIfExists('cms_employee_portal_access');
    }
};
