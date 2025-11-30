<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Payslips table
        Schema::create('employee_payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('payslip_number')->unique();
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->date('payment_date');
            
            // Earnings
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('commission', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2);
            
            // Deductions
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('pension', 12, 2)->default(0);
            $table->decimal('health_insurance', 12, 2)->default(0);
            $table->decimal('loan_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2);
            
            // Net
            $table->decimal('net_pay', 12, 2);
            
            // Details
            $table->json('earnings_breakdown')->nullable();
            $table->json('deductions_breakdown')->nullable();
            $table->text('notes')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->string('pdf_path')->nullable();
            
            $table->timestamps();
            
            $table->index(['employee_id', 'payment_date']);
            $table->index('pay_period_start');
        });

        // Company announcements for employees (if not exists)
        if (!Schema::hasTable('employee_announcements')) {
            Schema::create('employee_announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->enum('type', ['general', 'policy', 'event', 'urgent', 'hr'])->default('general');
                $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
                $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
                $table->foreignId('created_by')->nullable()->constrained('employees')->onDelete('set null');
                $table->date('publish_date');
                $table->date('expiry_date')->nullable();
                $table->boolean('is_pinned')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['is_active', 'publish_date']);
                $table->index('department_id');
            });
        }

        // Announcement read tracking
        if (!Schema::hasTable('employee_announcement_reads')) {
            Schema::create('employee_announcement_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained('employee_announcements')->onDelete('cascade');
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->timestamp('read_at');
                
                $table->unique(['announcement_id', 'employee_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_announcement_reads');
        Schema::dropIfExists('employee_announcements');
        Schema::dropIfExists('employee_payslips');
    }
};
