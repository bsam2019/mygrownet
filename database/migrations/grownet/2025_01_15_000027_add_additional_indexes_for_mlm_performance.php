<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('referral_commissions')) {
            try { Schema::table('referral_commissions', function (Blueprint $table) { $table->index(['referred_id', 'level', 'status'], 'rc_ref_lvl_stat_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('referral_commissions', function (Blueprint $table) { $table->index(['created_at', 'commission_type'], 'rc_created_type_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('referral_commissions', function (Blueprint $table) { $table->index(['team_volume', 'level'], 'rc_team_lvl_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('referral_commissions', function (Blueprint $table) { $table->index(['package_type', 'status'], 'rc_pkg_stat_idx'); }); } catch (Throwable $e) {}
        }

        if (Schema::hasTable('team_volumes')) {
            try { Schema::table('team_volumes', function (Blueprint $table) { $table->index(['team_volume', 'period_start'], 'tv_team_period_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('team_volumes', function (Blueprint $table) { $table->index(['active_referrals_count', 'period_start'], 'tv_active_period_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('team_volumes', function (Blueprint $table) { $table->index(['team_depth', 'user_id'], 'tv_depth_user_idx'); }); } catch (Throwable $e) {}
        }

        if (Schema::hasTable('user_networks')) {
            try { Schema::table('user_networks', function (Blueprint $table) { $table->index(['referrer_id', 'level', 'created_at'], 'un_ref_lvl_created_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('user_networks', function (Blueprint $table) { $table->index(['user_id', 'referrer_id'], 'un_user_ref_idx'); }); } catch (Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('referral_commissions')) {
            try { Schema::table('referral_commissions', function (Blueprint $table) { $table->dropIndex('rc_ref_lvl_stat_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('referral_commissions', function (Blueprint $table) { $table->dropIndex('rc_created_type_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('referral_commissions', function (Blueprint $table) { $table->dropIndex('rc_team_lvl_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('referral_commissions', function (Blueprint $table) { $table->dropIndex('rc_pkg_stat_idx'); }); } catch (Throwable $e) {}
        }

        if (Schema::hasTable('team_volumes')) {
            try { Schema::table('team_volumes', function (Blueprint $table) { $table->dropIndex('tv_team_period_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('team_volumes', function (Blueprint $table) { $table->dropIndex('tv_active_period_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('team_volumes', function (Blueprint $table) { $table->dropIndex('tv_depth_user_idx'); }); } catch (Throwable $e) {}
        }

        if (Schema::hasTable('user_networks')) {
            try { Schema::table('user_networks', function (Blueprint $table) { $table->dropIndex('un_ref_lvl_created_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('user_networks', function (Blueprint $table) { $table->dropIndex('un_user_ref_idx'); }); } catch (Throwable $e) {}
        }
    }
};