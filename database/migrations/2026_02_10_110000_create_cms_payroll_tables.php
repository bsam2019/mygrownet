<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Workers table (casual workers, contractors, etc.)
        if (!Schema::hasTable('cms_workers')) {
            Schema::create('cms_workers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->string('worker_number')->unique(); // WKR-0001
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('id_number')->nullable();
                $table->string('worker_type')->default('casual'); // casual, contractor, commission_only
                $table->decimal('hourly_rate', 15, 2)->default(0);
                $table->decimal('daily_rate', 15, 2)->default(0);
                $table->decimal('commission_rate', 5, 2)->default(0); // Percentage
                $table->string('payment_method')->default('cash'); // cash, mobile_money, bank
                $table->string('mobile_money_number')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('status')->default('active'); // active, inactive
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['company_id', 'status']);
                $table->index(['company_id', 'worker_type']);
                $table->unique(['company_id', 'worker_number']);
            });
        }

        // Worker attendance/work records
        if (!Schema::hasTable('cms_worker_attendance')) {
            Schema::create('cms_worker_attendance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->foreignId('worker_id')->constrained('cms_workers')->cascadeOnDelete();
                $table->foreignId('job_id')->nullable()->constrained('cms_jobs')->nullOnDelete();
                $table->date('work_date');
                $table->decimal('hours_worked', 8, 2)->default(0);
                $table->decimal('days_worked', 8, 2)->default(0);
                $table->decimal('amount_earned', 15, 2)->default(0);
                $table->text('work_description')->nullable();
                $table->string('status')->default('pending'); // pending, approved, paid
                $table->foreignId('approved_by')->nullable()->constrained('cms_users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['company_id', 'worker_id']);
                $table->index(['company_id', 'work_date']);
                $table->index(['company_id', 'status']);
            });
        }

        // Commission records
        if (!Schema::hasTable('cms_commissions')) {
            Schema::create('cms_commissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->foreignId('worker_id')->nullable()->constrained('cms_workers')->nullOnDelete();
                $table->foreignId('cms_user_id')->nullable()->constrained('cms_users')->nullOnDelete();
                $table->foreignId('job_id')->nullable()->constrained('cms_jobs')->nullOnDelete();
                $table->foreignId('invoice_id')->nullable()->constrained('cms_invoices')->nullOnDelete();
                $table->string('commission_type'); // job_completion, sales, custom
                $table->decimal('base_amount', 15, 2)->default(0); // Job value or sale amount
                $table->decimal('commission_rate', 5, 2)->default(0); // Percentage
                $table->decimal('commission_amount', 15, 2)->default(0);
                $table->text('description')->nullable();
                $table->string('status')->default('pending'); // pending, approved, paid
                $table->foreignId('approved_by')->nullable()->constrained('cms_users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['company_id', 'worker_id']);
                $table->index(['company_id', 'cms_user_id']);
                $table->index(['company_id', 'status']);
            });
        }

        // Payroll runs
        if (!Schema::hasTable('cms_payroll_runs')) {
            Schema::create('cms_payroll_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->string('payroll_number')->unique(); // PAY-2026-001
                $table->string('period_type')->default('weekly'); // weekly, biweekly, monthly
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('total_wages', 15, 2)->default(0);
                $table->decimal('total_commissions', 15, 2)->default(0);
                $table->decimal('total_deductions', 15, 2)->default(0);
                $table->decimal('total_net_pay', 15, 2)->default(0);
                $table->string('status')->default('draft'); // draft, approved, paid
                $table->foreignId('approved_by')->nullable()->constrained('cms_users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->date('paid_date')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['company_id', 'status']);
                $table->index(['period_start', 'period_end']);
                $table->unique(['company_id', 'payroll_number']);
            });
        }

        // Payroll items (individual worker payments in a payroll run)
        if (!Schema::hasTable('cms_payroll_items')) {
            Schema::create('cms_payroll_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->foreignId('payroll_run_id')->constrained('cms_payroll_runs')->cascadeOnDelete();
                $table->foreignId('worker_id')->nullable()->constrained('cms_workers')->nullOnDelete();
                $table->foreignId('cms_user_id')->nullable()->constrained('cms_users')->nullOnDelete();
                $table->decimal('wages', 15, 2)->default(0);
                $table->decimal('commissions', 15, 2)->default(0);
                $table->decimal('bonuses', 15, 2)->default(0);
                $table->decimal('deductions', 15, 2)->default(0);
                $table->decimal('net_pay', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'payroll_run_id']);
                $table->index(['worker_id']);
                $table->index(['cms_user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_payroll_items');
        Schema::dropIfExists('cms_payroll_runs');
        Schema::dropIfExists('cms_commissions');
        Schema::dropIfExists('cms_worker_attendance');
        Schema::dropIfExists('cms_workers');
    }
};
