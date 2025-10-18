<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            // Add columns if missing; avoid fragile after() placement
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'current_team_volume')) {
                    $table->decimal('current_team_volume', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('users', 'current_personal_volume')) {
                    $table->decimal('current_personal_volume', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('users', 'current_team_depth')) {
                    $table->integer('current_team_depth', false, true)->default(0);
                }
                if (!Schema::hasColumn('users', 'active_referrals_count')) {
                    $table->integer('active_referrals_count', false, true)->default(0);
                }

                if (!Schema::hasColumn('users', 'monthly_subscription_fee')) {
                    $table->decimal('monthly_subscription_fee', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('users', 'subscription_start_date')) {
                    $table->date('subscription_start_date')->nullable();
                }
                if (!Schema::hasColumn('users', 'subscription_end_date')) {
                    $table->date('subscription_end_date')->nullable();
                }
                if (!Schema::hasColumn('users', 'subscription_status')) {
                    $table->enum('subscription_status', ['active', 'inactive', 'suspended', 'cancelled'])->default('inactive');
                }

                // Use string with length instead of TEXT for indexability
                if (!Schema::hasColumn('users', 'network_path')) {
                    $table->string('network_path', 255)->nullable();
                }
                if (!Schema::hasColumn('users', 'network_level')) {
                    $table->integer('network_level')->default(0);
                }
            });

            // Add indexes with explicit names; guard column existence; ignore errors on reruns
            try { Schema::table('users', function (Blueprint $table) { if (Schema::hasColumn('users','current_team_volume') && Schema::hasColumn('users','subscription_status')) { $table->index(['current_team_volume', 'subscription_status'], 'users_ctv_substat_idx'); } }); } catch (Throwable $e) {}
            try { Schema::table('users', function (Blueprint $table) { if (Schema::hasColumn('users','referrer_id') && Schema::hasColumn('users','network_level')) { $table->index(['referrer_id', 'network_level'], 'users_ref_netlvl_idx'); } }); } catch (Throwable $e) {}
            try { Schema::table('users', function (Blueprint $table) { if (Schema::hasColumn('users','subscription_status') && Schema::hasColumn('users','subscription_end_date')) { $table->index(['subscription_status', 'subscription_end_date'], 'users_substat_end_idx'); } }); } catch (Throwable $e) {}
            try { Schema::table('users', function (Blueprint $table) { if (Schema::hasColumn('users','network_path')) { $table->index('network_path', 'users_netpath_idx'); } }); } catch (Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            // Drop indexes by explicit names; ignore if missing
            try { Schema::table('users', function (Blueprint $table) { $table->dropIndex('users_ctv_substat_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('users', function (Blueprint $table) { $table->dropIndex('users_ref_netlvl_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('users', function (Blueprint $table) { $table->dropIndex('users_substat_end_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('users', function (Blueprint $table) { $table->dropIndex('users_netpath_idx'); }); } catch (Throwable $e) {}

            // Drop columns best-effort
            try { Schema::table('users', function (Blueprint $table) { $table->dropColumn(['current_team_volume','current_personal_volume','current_team_depth','active_referrals_count','monthly_subscription_fee','subscription_start_date','subscription_end_date','subscription_status','network_path','network_level']); }); } catch (Throwable $e) {}
        }
    }
};