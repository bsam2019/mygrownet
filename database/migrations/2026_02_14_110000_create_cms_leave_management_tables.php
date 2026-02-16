<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Leave types
        Schema::create('cms_leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('leave_type_name');
            $table->string('leave_code')->unique();
            $table->text('description')->nullable();
            $table->integer('default_days_per_year')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->boolean('can_carry_forward')->default(false);
            $table->integer('max_carry_forward_days')->default(0);
            $table->integer('max_consecutive_days')->nullable();
            $table->integer('min_notice_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->unique(['company_id', 'leave_code']);
        });

        // Employee leave balances
        Schema::create('cms_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('cms_workers')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('cms_leave_types')->cascadeOnDelete();
            $table->integer('year');
            $table->decimal('total_days', 8, 2)->default(0);
            $table->decimal('used_days', 8, 2)->default(0);
            $table->decimal('pending_days', 8, 2)->default(0);
            $table->decimal('available_days', 8, 2)->default(0);
            $table->decimal('carried_forward_days', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['company_id', 'worker_id']);
            $table->index(['year']);
            $table->unique(['worker_id', 'leave_type_id', 'year']);
        });

        // Leave requests
        Schema::create('cms_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('leave_request_number')->unique();
            $table->foreignId('worker_id')->constrained('cms_workers')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('cms_leave_types')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 8, 2);
            $table->text('reason')->nullable();
            $table->string('contact_during_leave')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('cms_users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('cms_users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'worker_id']);
            $table->index(['company_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->unique(['company_id', 'leave_request_number']);
        });

        // Public holidays
        Schema::create('cms_public_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('holiday_name');
            $table->date('holiday_date');
            $table->integer('year');
            $table->text('description')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'year']);
            $table->index(['holiday_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_public_holidays');
        Schema::dropIfExists('cms_leave_requests');
        Schema::dropIfExists('cms_leave_balances');
        Schema::dropIfExists('cms_leave_types');
    }
};
