<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Time entries table
        Schema::create('cms_time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('worker_id')->constrained('cms_workers')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs')->onDelete('set null');
            $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
            
            // Time tracking
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_minutes')->nullable(); // Calculated on stop
            $table->boolean('is_running')->default(false);
            
            // Billing
            $table->boolean('is_billable')->default(true);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            
            // Details
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            
            // Approval
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('cms_users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Payroll integration
            $table->boolean('included_in_payroll')->default(false);
            $table->foreignId('payroll_id')->nullable()->constrained('cms_payroll_runs')->onDelete('set null');
            
            $table->timestamps();
            
            $table->index(['company_id', 'worker_id', 'start_time']);
            $table->index(['company_id', 'job_id']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'is_running']);
        });

        // Timesheets table (weekly/monthly grouping)
        Schema::create('cms_timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('worker_id')->constrained('cms_workers')->onDelete('cascade');
            
            // Period
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('period_type', ['weekly', 'biweekly', 'monthly'])->default('weekly');
            
            // Totals
            $table->integer('total_hours')->default(0);
            $table->integer('billable_hours')->default(0);
            $table->integer('non_billable_hours')->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            
            // Status
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->foreignId('submitted_by')->nullable()->constrained('cms_users')->onDelete('set null');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('cms_users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            
            $table->index(['company_id', 'worker_id', 'start_date']);
            $table->index(['company_id', 'status']);
            $table->unique(['company_id', 'worker_id', 'start_date', 'end_date'], 'unique_timesheet_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_timesheets');
        Schema::dropIfExists('cms_time_entries');
    }
};
