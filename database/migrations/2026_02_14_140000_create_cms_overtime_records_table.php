<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cms_overtime_records')) {
            Schema::create('cms_overtime_records', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->foreignId('worker_id')->constrained('cms_workers')->onDelete('cascade');
                        $table->foreignId('attendance_record_id')->nullable()->constrained('cms_attendance_records')->onDelete('set null');
                        
                        // Overtime details
                        $table->date('overtime_date');
                        $table->integer('overtime_minutes'); // Overtime duration in minutes
                        $table->enum('overtime_type', [
                            'daily',      // Daily overtime (>8 hours)
                            'weekly',     // Weekly overtime (>40 hours)
                            'holiday',    // Public holiday work
                            'weekend',    // Weekend work
                            'manual'      // Manually entered
                        ])->default('daily');
                        
                        // Rate calculation
                        $table->decimal('overtime_rate_multiplier', 4, 2)->default(1.5); // 1.5x, 2.0x, 3.0x
                        $table->decimal('base_hourly_rate', 10, 2)->nullable();
                        $table->decimal('overtime_amount', 10, 2)->nullable(); // Calculated amount
                        
                        // Details
                        $table->text('reason')->nullable();
                        $table->text('notes')->nullable();
                        
                        // Approval workflow
                        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                        $table->foreignId('approved_by')->nullable()->constrained('cms_users')->onDelete('set null');
                        $table->timestamp('approved_at')->nullable();
                        $table->text('rejection_reason')->nullable();
                        
                        // Payroll integration
                        $table->boolean('included_in_payroll')->default(false);
                        $table->foreignId('payroll_id')->nullable()->constrained('cms_payroll_runs')->onDelete('set null');
                        
                        $table->foreignId('created_by')->nullable()->constrained('cms_users')->onDelete('set null');
                        $table->timestamps();
                        
                        $table->index(['company_id', 'worker_id', 'overtime_date'], 'cms_ot_comp_work_date_idx');
                        $table->index(['company_id', 'status'], 'cms_ot_comp_status_idx');
                        $table->index(['company_id', 'overtime_type'], 'cms_ot_comp_type_idx');
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_overtime_records');
    }
};
