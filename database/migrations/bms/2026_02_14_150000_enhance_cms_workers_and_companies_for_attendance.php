<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add attendance-related fields to cms_workers
        Schema::table('cms_workers', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_workers', 'default_shift_id')) {
                $table->foreignId('default_shift_id')->nullable()->after('employment_status')->constrained('cms_shifts')->onDelete('set null');
            }
            if (!Schema::hasColumn('cms_workers', 'track_attendance')) {
                $table->boolean('track_attendance')->default(true)->after('default_shift_id');
            }
            if (!Schema::hasColumn('cms_workers', 'requires_clock_photo')) {
                $table->boolean('requires_clock_photo')->default(false)->after('track_attendance');
            }
            if (!Schema::hasColumn('cms_workers', 'requires_location')) {
                $table->boolean('requires_location')->default(false)->after('requires_clock_photo');
            }
        });

        // Add attendance configuration to cms_companies
        Schema::table('cms_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_companies', 'attendance_grace_period_minutes')) {
                $table->integer('attendance_grace_period_minutes')->default(15);
            }
            if (!Schema::hasColumn('cms_companies', 'require_clock_photo')) {
                $table->boolean('require_clock_photo')->default(false);
            }
            if (!Schema::hasColumn('cms_companies', 'require_location')) {
                $table->boolean('require_location')->default(false);
            }
            if (!Schema::hasColumn('cms_companies', 'auto_clock_out_enabled')) {
                $table->boolean('auto_clock_out_enabled')->default(true);
            }
            if (!Schema::hasColumn('cms_companies', 'auto_clock_out_time')) {
                $table->time('auto_clock_out_time')->default('23:59:00');
            }
            if (!Schema::hasColumn('cms_companies', 'overtime_enabled')) {
                $table->boolean('overtime_enabled')->default(true);
            }
            if (!Schema::hasColumn('cms_companies', 'daily_overtime_threshold_hours')) {
                $table->decimal('daily_overtime_threshold_hours', 4, 2)->default(8.0);
            }
            if (!Schema::hasColumn('cms_companies', 'weekly_overtime_threshold_hours')) {
                $table->decimal('weekly_overtime_threshold_hours', 4, 2)->default(40.0);
            }
            if (!Schema::hasColumn('cms_companies', 'overtime_rate_multiplier')) {
                $table->decimal('overtime_rate_multiplier', 4, 2)->default(1.5);
            }
            if (!Schema::hasColumn('cms_companies', 'holiday_overtime_multiplier')) {
                $table->decimal('holiday_overtime_multiplier', 4, 2)->default(2.0);
            }
            if (!Schema::hasColumn('cms_companies', 'weekend_overtime_multiplier')) {
                $table->decimal('weekend_overtime_multiplier', 4, 2)->default(1.5);
            }
            if (!Schema::hasColumn('cms_companies', 'overtime_requires_approval')) {
                $table->boolean('overtime_requires_approval')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('cms_workers', function (Blueprint $table) {
            $table->dropForeign(['default_shift_id']);
            $table->dropColumn([
                'default_shift_id',
                'track_attendance',
                'requires_clock_photo',
                'requires_location',
            ]);
        });

        Schema::table('cms_companies', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_grace_period_minutes',
                'require_clock_photo',
                'require_location',
                'auto_clock_out_enabled',
                'auto_clock_out_time',
                'overtime_enabled',
                'daily_overtime_threshold_hours',
                'weekly_overtime_threshold_hours',
                'overtime_rate_multiplier',
                'holiday_overtime_multiplier',
                'weekend_overtime_multiplier',
                'overtime_requires_approval',
            ]);
        });
    }
};
