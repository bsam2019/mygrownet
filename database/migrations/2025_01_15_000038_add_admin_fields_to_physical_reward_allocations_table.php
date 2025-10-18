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
        if (Schema::hasTable('physical_reward_allocations')) {
            Schema::table('physical_reward_allocations', function (Blueprint $table) {
                // Admin allocation tracking
                if (!Schema::hasColumn('physical_reward_allocations', 'allocated_by')) {
                    $table->unsignedBigInteger('allocated_by')->nullable()->after('allocated_at');
                }
                if (!Schema::hasColumn('physical_reward_allocations', 'allocation_reason')) {
                    $table->text('allocation_reason')->nullable()->after('allocated_by');
                }

                // Ownership transfer tracking
                if (!Schema::hasColumn('physical_reward_allocations', 'transferred_by')) {
                    if (Schema::hasColumn('physical_reward_allocations', 'completed_at')) {
                        $table->unsignedBigInteger('transferred_by')->nullable()->after('completed_at');
                    } else {
                        $table->unsignedBigInteger('transferred_by')->nullable();
                    }
                }
                if (!Schema::hasColumn('physical_reward_allocations', 'transfer_reason')) {
                    $table->text('transfer_reason')->nullable()->after('transferred_by');
                }

                // Maintenance violation tracking
                if (!Schema::hasColumn('physical_reward_allocations', 'violation_warnings')) {
                    $table->integer('violation_warnings')->default(0)->after('transfer_reason');
                }
                if (!Schema::hasColumn('physical_reward_allocations', 'last_warning_at')) {
                    $table->timestamp('last_warning_at')->nullable()->after('violation_warnings');
                }
                if (!Schema::hasColumn('physical_reward_allocations', 'warning_reason')) {
                    $table->text('warning_reason')->nullable()->after('last_warning_at');
                }
                if (!Schema::hasColumn('physical_reward_allocations', 'warned_by')) {
                    $table->unsignedBigInteger('warned_by')->nullable()->after('warning_reason');
                }

                // Maintenance extension tracking
                if (!Schema::hasColumn('physical_reward_allocations', 'extension_granted')) {
                    $table->integer('extension_granted')->nullable()->after('warned_by');
                }
                if (!Schema::hasColumn('physical_reward_allocations', 'extension_reason')) {
                    $table->text('extension_reason')->nullable()->after('extension_granted');
                }
                if (!Schema::hasColumn('physical_reward_allocations', 'extended_by')) {
                    $table->unsignedBigInteger('extended_by')->nullable()->after('extension_reason');
                }
                if (!Schema::hasColumn('physical_reward_allocations', 'extended_at')) {
                    $table->timestamp('extended_at')->nullable()->after('extended_by');
                }

                // Asset recovery tracking
                if (!Schema::hasColumn('physical_reward_allocations', 'forfeited_at')) {
                    $table->timestamp('forfeited_at')->nullable()->after('extended_at');
                }
                if (!Schema::hasColumn('physical_reward_allocations', 'forfeit_reason')) {
                    $table->text('forfeit_reason')->nullable()->after('forfeited_at');
                }
                if (!Schema::hasColumn('physical_reward_allocations', 'forfeited_by')) {
                    $table->unsignedBigInteger('forfeited_by')->nullable()->after('forfeit_reason');
                }
            });

            // Add foreign key constraints and indexes with explicit names, safe on reruns
            try {
                Schema::table('physical_reward_allocations', function (Blueprint $table) {
                    if (Schema::hasTable('users')) {
                        try { $table->foreign('allocated_by', 'pra_alloc_by_fk')->references('id')->on('users')->onDelete('set null'); } catch (Throwable $e) {}
                        try { $table->foreign('transferred_by', 'pra_trans_by_fk')->references('id')->on('users')->onDelete('set null'); } catch (Throwable $e) {}
                        try { $table->foreign('warned_by', 'pra_warned_by_fk')->references('id')->on('users')->onDelete('set null'); } catch (Throwable $e) {}
                        try { $table->foreign('extended_by', 'pra_ext_by_fk')->references('id')->on('users')->onDelete('set null'); } catch (Throwable $e) {}
                        try { $table->foreign('forfeited_by', 'pra_forfeit_by_fk')->references('id')->on('users')->onDelete('set null'); } catch (Throwable $e) {}
                    }

                    try { $table->index(['status', 'allocated_at'], 'pra_status_alloc_idx'); } catch (Throwable $e) {}
                    try { $table->index(['user_id', 'status'], 'pra_user_status_idx'); } catch (Throwable $e) {}
                    try { $table->index(['allocated_by', 'allocated_at'], 'pra_allocby_allocat_idx'); } catch (Throwable $e) {}
                });
            } catch (Throwable $e) {
                // ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('physical_reward_allocations')) {
            try {
                Schema::table('physical_reward_allocations', function (Blueprint $table) {
                    try { $table->dropForeign('pra_alloc_by_fk'); } catch (Throwable $e) {}
                    try { $table->dropForeign('pra_trans_by_fk'); } catch (Throwable $e) {}
                    try { $table->dropForeign('pra_warned_by_fk'); } catch (Throwable $e) {}
                    try { $table->dropForeign('pra_ext_by_fk'); } catch (Throwable $e) {}
                    try { $table->dropForeign('pra_forfeit_by_fk'); } catch (Throwable $e) {}

                    try { $table->dropIndex('pra_status_alloc_idx'); } catch (Throwable $e) {}
                    try { $table->dropIndex('pra_user_status_idx'); } catch (Throwable $e) {}
                    try { $table->dropIndex('pra_allocby_allocat_idx'); } catch (Throwable $e) {}
                });
            } catch (Throwable $e) {}

            Schema::table('physical_reward_allocations', function (Blueprint $table) {
                $cols = [
                    'allocated_by','allocation_reason','transferred_by','transfer_reason',
                    'violation_warnings','last_warning_at','warning_reason','warned_by',
                    'extension_granted','extension_reason','extended_by','extended_at',
                    'forfeited_at','forfeit_reason','forfeited_by'
                ];
                $drop = [];
                foreach ($cols as $c) { if (Schema::hasColumn('physical_reward_allocations', $c)) { $drop[] = $c; } }
                if (!empty($drop)) { $table->dropColumn($drop); }
            });
        }
    }
};