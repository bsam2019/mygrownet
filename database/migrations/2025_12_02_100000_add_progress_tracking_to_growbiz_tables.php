<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add progress tracking columns to GrowBiz tables
     */
    public function up(): void
    {
        // Add progress tracking columns to tasks table
        if (!Schema::hasColumn('growbiz_tasks', 'progress_percentage')) {
            Schema::table('growbiz_tasks', function (Blueprint $table) {
                $table->unsignedTinyInteger('progress_percentage')->default(0)->after('actual_hours');
            });
        }
        
        if (!Schema::hasColumn('growbiz_tasks', 'started_at')) {
            Schema::table('growbiz_tasks', function (Blueprint $table) {
                $table->timestamp('started_at')->nullable()->after('progress_percentage');
            });
        }
        
        if (!Schema::hasColumn('growbiz_tasks', 'completed_at')) {
            Schema::table('growbiz_tasks', function (Blueprint $table) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            });
        }

        // Update task_updates table for better progress tracking
        if (!Schema::hasColumn('growbiz_task_updates', 'update_type')) {
            Schema::table('growbiz_task_updates', function (Blueprint $table) {
                $table->enum('update_type', ['status_change', 'progress_update', 'time_log', 'note'])
                    ->default('note')
                    ->after('user_id');
            });
        }
        
        if (!Schema::hasColumn('growbiz_task_updates', 'old_status')) {
            Schema::table('growbiz_task_updates', function (Blueprint $table) {
                $table->string('old_status')->nullable()->after('update_type');
            });
        }
        
        if (!Schema::hasColumn('growbiz_task_updates', 'new_status')) {
            Schema::table('growbiz_task_updates', function (Blueprint $table) {
                $table->string('new_status')->nullable()->after('old_status');
            });
        }
        
        if (!Schema::hasColumn('growbiz_task_updates', 'old_progress')) {
            Schema::table('growbiz_task_updates', function (Blueprint $table) {
                $table->unsignedTinyInteger('old_progress')->nullable()->after('new_status');
            });
        }
        
        if (!Schema::hasColumn('growbiz_task_updates', 'new_progress')) {
            Schema::table('growbiz_task_updates', function (Blueprint $table) {
                $table->unsignedTinyInteger('new_progress')->nullable()->after('old_progress');
            });
        }
        
        if (!Schema::hasColumn('growbiz_task_updates', 'hours_logged')) {
            Schema::table('growbiz_task_updates', function (Blueprint $table) {
                $table->decimal('hours_logged', 8, 2)->nullable()->after('new_progress');
            });
        }
        
        if (!Schema::hasColumn('growbiz_task_updates', 'notes')) {
            Schema::table('growbiz_task_updates', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('hours_logged');
            });
        }

        // Add index for update_type
        try {
            Schema::table('growbiz_task_updates', function (Blueprint $table) {
                $table->index(['task_id', 'update_type'], 'growbiz_task_updates_task_id_update_type_index');
            });
        } catch (\Exception $e) {
            // Index may already exist
        }
    }

    public function down(): void
    {
        Schema::table('growbiz_tasks', function (Blueprint $table) {
            $columns = ['progress_percentage', 'started_at', 'completed_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('growbiz_tasks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('growbiz_task_updates', function (Blueprint $table) {
            $columns = ['update_type', 'old_status', 'new_status', 'old_progress', 'new_progress', 'hours_logged', 'notes'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('growbiz_task_updates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
