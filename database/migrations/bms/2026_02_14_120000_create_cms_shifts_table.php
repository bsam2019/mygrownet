<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Shifts table - Define work shift templates
        if (!Schema::hasTable('cms_shifts')) {
            Schema::create('cms_shifts', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        
                        // Shift details
                        $table->string('shift_name'); // Morning, Evening, Night, etc.
                        $table->string('shift_code')->unique(); // MORNING, EVENING, NIGHT
                        $table->time('start_time'); // 08:00:00
                        $table->time('end_time'); // 17:00:00
                        $table->integer('break_duration_minutes')->default(60); // Lunch break
                        
                        // Attendance rules
                        $table->integer('grace_period_minutes')->default(15); // Late tolerance
                        $table->decimal('minimum_hours_full_day', 4, 2)->default(7.5); // 7.5 hours for full day
                        $table->decimal('minimum_hours_half_day', 4, 2)->default(4.0); // 4 hours for half day
                        
                        // Overtime
                        $table->integer('overtime_threshold_minutes')->default(480); // 8 hours = 480 minutes
                        
                        // Shift differentials
                        $table->boolean('is_night_shift')->default(false);
                        $table->decimal('night_shift_differential_percent', 5, 2)->nullable(); // 20% extra
                        $table->boolean('is_weekend_shift')->default(false);
                        $table->decimal('weekend_differential_percent', 5, 2)->nullable(); // 15% extra
                        
                        // Status
                        $table->boolean('is_active')->default(true);
                        $table->text('description')->nullable();
                        
                        $table->timestamps();
                        
                        $table->index(['company_id', 'is_active']);
                        $table->unique(['company_id', 'shift_code']);
                    });
        }

        // Worker shift assignments
        if (!Schema::hasTable('cms_worker_shifts')) {
            Schema::create('cms_worker_shifts', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->foreignId('worker_id')->constrained('cms_workers')->onDelete('cascade');
                        $table->foreignId('shift_id')->constrained('cms_shifts')->onDelete('cascade');
                        
                        // Effective period
                        $table->date('effective_from');
                        $table->date('effective_to')->nullable(); // Null = ongoing
                        
                        // Days of week (JSON array: [1,2,3,4,5] for Mon-Fri)
                        $table->json('days_of_week')->nullable(); // null = all days
                        
                        // Status
                        $table->boolean('is_active')->default(true);
                        $table->text('notes')->nullable();
                        
                        $table->timestamps();
                        
                        $table->index(['company_id', 'worker_id', 'effective_from']);
                        $table->index(['company_id', 'shift_id']);
                        $table->index(['company_id', 'is_active']);
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_worker_shifts');
        Schema::dropIfExists('cms_shifts');
    }
};
