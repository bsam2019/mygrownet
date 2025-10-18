<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('physical_rewards')) {
            // Add columns if missing
            Schema::table('physical_rewards', function (Blueprint $table) {
                if (!Schema::hasColumn('physical_rewards', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('transferred_at');
                }
                if (!Schema::hasColumn('physical_rewards', 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                }
                if (!Schema::hasColumn('physical_rewards', 'serial_number')) {
                    $table->string('serial_number')->nullable()->after('model');
                }
                if (!Schema::hasColumn('physical_rewards', 'description')) {
                    $table->text('description')->nullable()->after('serial_number');
                }
            });

            // Add foreign keys and indexes in a safe, idempotent way
            try {
                Schema::table('physical_rewards', function (Blueprint $table) {
                    if (Schema::hasTable('users')) {
                        // Explicit short FK names
                        if (!Schema::hasColumn('physical_rewards', 'created_by')) { /* column just added above if missing */ }
                        try { $table->foreign('created_by', 'pr_created_by_fk')->references('id')->on('users')->onDelete('set null'); } catch (Throwable $e) {}
                        try { $table->foreign('updated_by', 'pr_updated_by_fk')->references('id')->on('users')->onDelete('set null'); } catch (Throwable $e) {}
                    }

                    // Explicit short index names
                    try { $table->index(['type', 'status'], 'pr_type_status_idx'); } catch (Throwable $e) {}
                    try { $table->index(['status', 'created_at'], 'pr_status_created_idx'); } catch (Throwable $e) {}
                    try { $table->index('serial_number', 'pr_serial_idx'); } catch (Throwable $e) {}
                });
            } catch (Throwable $e) {
                // ignore on rerun
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('physical_rewards')) {
            // Drop constraints and indexes safely
            try {
                Schema::table('physical_rewards', function (Blueprint $table) {
                    try { $table->dropForeign('pr_created_by_fk'); } catch (Throwable $e) {}
                    try { $table->dropForeign('pr_updated_by_fk'); } catch (Throwable $e) {}

                    try { $table->dropIndex('pr_type_status_idx'); } catch (Throwable $e) {}
                    try { $table->dropIndex('pr_status_created_idx'); } catch (Throwable $e) {}
                    try { $table->dropIndex('pr_serial_idx'); } catch (Throwable $e) {}
                });
            } catch (Throwable $e) {}

            // Drop columns if they exist
            Schema::table('physical_rewards', function (Blueprint $table) {
                $columnsToDrop = [];
                foreach (['created_by','updated_by','serial_number','description'] as $col) {
                    if (Schema::hasColumn('physical_rewards', $col)) { $columnsToDrop[] = $col; }
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};