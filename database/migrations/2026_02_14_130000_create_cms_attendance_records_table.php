<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('worker_id')->constrained('cms_workers')->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained('cms_shifts')->onDelete('set null');
            
            // Attendance date
            $table->date('attendance_date');
            
            // Clock in/out
            $table->dateTime('clock_in_time')->nullable();
            $table->json('clock_in_location')->nullable(); // {lat, lng, address}
            $table->string('clock_in_photo_path')->nullable();
            $table->string('clock_in_device')->nullable(); // web, mobile, kiosk
            
            $table->dateTime('clock_out_time')->nullable();
            $table->json('clock_out_location')->nullable();
            $table->string('clock_out_photo_path')->nullable();
            $table->string('clock_out_device')->nullable();
            
            // Hours calculation
            $table->integer('total_minutes')->nullable(); // Total time worked
            $table->integer('regular_minutes')->nullable(); // Regular hours
            $table->integer('overtime_minutes')->nullable(); // Overtime hours
            $table->integer('break_minutes')->default(0); // Break time
            
            // Status
            $table->enum('status', [
                'present',
                'absent',
                'late',
                'half_day',
                'on_leave',
                'holiday',
                'weekend'
            ])->default('absent');
            
            // Late/Early tracking
            $table->boolean('is_late')->default(false);
            $table->integer('late_by_minutes')->nullable();
            $table->boolean('is_early_departure')->default(false);
            $table->integer('early_by_minutes')->nullable();
            
            // Notes and approval
            $table->text('notes')->nullable();
            $table->text('worker_notes')->nullable(); // Worker can add notes
            $table->boolean('is_manual_entry')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('cms_users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('cms_users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['company_id', 'worker_id', 'attendance_date'], 'cms_att_comp_work_date_idx');
            $table->index(['company_id', 'attendance_date'], 'cms_att_comp_date_idx');
            $table->index(['company_id', 'status'], 'cms_att_comp_status_idx');
            $table->unique(['company_id', 'worker_id', 'attendance_date'], 'cms_att_unique_daily');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_attendance_records');
    }
};
