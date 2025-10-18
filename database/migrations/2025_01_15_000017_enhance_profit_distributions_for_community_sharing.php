<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enhance profit_distributions table (guarded)
        if (Schema::hasTable('profit_distributions')) {
            Schema::table('profit_distributions', function (Blueprint $table) {
                // Community project allocation fields
                if (!Schema::hasColumn('profit_distributions', 'community_project_allocation')) {
                    $table->decimal('community_project_allocation', 15, 2)->default(0)->after('total_distributed');
                }
                if (!Schema::hasColumn('profit_distributions', 'community_project_percentage')) {
                    $table->decimal('community_project_percentage', 5, 2)->default(0)->after('community_project_allocation');
                }
                if (!Schema::hasColumn('profit_distributions', 'tier_based_bonuses')) {
                    $table->decimal('tier_based_bonuses', 15, 2)->default(0)->after('community_project_percentage');
                }
                // Distribution source and type
                if (!Schema::hasColumn('profit_distributions', 'distribution_source')) {
                    $table->enum('distribution_source', ['investment_profits', 'community_projects', 'mixed'])->default('investment_profits')->after('tier_based_bonuses');
                }
                if (!Schema::hasColumn('profit_distributions', 'includes_community_projects')) {
                    $table->boolean('includes_community_projects')->default(false)->after('distribution_source');
                }
                if (!Schema::hasColumn('profit_distributions', 'community_profit_pool')) {
                    $table->decimal('community_profit_pool', 15, 2)->default(0)->after('includes_community_projects');
                }
                // Tier bonus multipliers
                if (!Schema::hasColumn('profit_distributions', 'tier_bonus_multipliers')) {
                    $table->json('tier_bonus_multipliers')->nullable()->after('community_profit_pool');
                }
            });
            // Indexes (explicit short names)
            try { Schema::table('profit_distributions', function (Blueprint $table) { $table->index(['period_type', 'includes_community_projects'], 'pd_period_incl_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('profit_distributions', function (Blueprint $table) { $table->index(['status', 'processed_at'], 'pd_status_proc_idx'); }); } catch (Throwable $e) {}
        }

        // Enhance profit_shares table (guarded)
        if (Schema::hasTable('profit_shares')) {
            Schema::table('profit_shares', function (Blueprint $table) {
                // Create columns first; add FKs separately and conditionally
                if (!Schema::hasColumn('profit_shares', 'profit_distribution_id')) {
                    $table->unsignedBigInteger('profit_distribution_id')->nullable()->after('investment_id');
                }
                // Tier and bonus information
                if (!Schema::hasColumn('profit_shares', 'tier_at_distribution')) {
                    $table->string('tier_at_distribution')->nullable();
                }
                if (!Schema::hasColumn('profit_shares', 'tier_bonus_applied')) {
                    $table->decimal('tier_bonus_applied', 3, 2)->default(1.00)->after('tier_at_distribution');
                }
                if (!Schema::hasColumn('profit_shares', 'distribution_type')) {
                    $table->enum('distribution_type', ['monthly', 'quarterly', 'annual'])->nullable()->after('tier_bonus_applied');
                }
                // Amount breakdown
                if (!Schema::hasColumn('profit_shares', 'base_amount')) {
                    $table->decimal('base_amount', 12, 2)->nullable()->after('amount');
                }
                if (!Schema::hasColumn('profit_shares', 'bonus_amount')) {
                    $table->decimal('bonus_amount', 12, 2)->default(0)->after('base_amount');
                }
                if (!Schema::hasColumn('profit_shares', 'community_project_bonus')) {
                    $table->decimal('community_project_bonus', 12, 2)->default(0)->after('bonus_amount');
                }
                // Community project integration
                if (!Schema::hasColumn('profit_shares', 'includes_community_allocation')) {
                    $table->boolean('includes_community_allocation')->default(false)->after('community_project_bonus');
                }
                // Processing information
                if (!Schema::hasColumn('profit_shares', 'payment_method')) {
                    $table->enum('payment_method', ['bank_transfer', 'mobile_money', 'internal_balance'])->nullable()->after('includes_community_allocation');
                }
                if (!Schema::hasColumn('profit_shares', 'processed_at')) {
                    $table->timestamp('processed_at')->nullable()->after('payment_method');
                }
                if (!Schema::hasColumn('profit_shares', 'processed_by')) {
                    $table->unsignedBigInteger('processed_by')->nullable()->after('processed_at');
                }
            });
            // Indexes (explicit short names)
            try { Schema::table('profit_shares', function (Blueprint $table) { $table->index(['tier_at_distribution', 'distribution_type'], 'ps_tier_dist_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('profit_shares', function (Blueprint $table) { $table->index(['status', 'payment_date'], 'ps_status_pay_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('profit_shares', function (Blueprint $table) { $table->index(['profit_distribution_id', 'status'], 'ps_pd_status_idx'); }); } catch (Throwable $e) {}

            // Foreign keys (conditionally)
            try {
                if (Schema::hasTable('profit_distributions')) {
                    Schema::table('profit_shares', function (Blueprint $table) {
                        if (Schema::hasColumn('profit_shares', 'profit_distribution_id')) {
                            $table->foreign('profit_distribution_id')->references('id')->on('profit_distributions')->onDelete('set null');
                        }
                    });
                }
            } catch (Throwable $e) {}
            try {
                if (Schema::hasTable('users')) {
                    Schema::table('profit_shares', function (Blueprint $table) {
                        if (Schema::hasColumn('profit_shares', 'processed_by')) {
                            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
                        }
                    });
                }
            } catch (Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('profit_shares')) {
            // Drop indexes by name; ignore if missing
            try { Schema::table('profit_shares', function (Blueprint $table) { $table->dropIndex('ps_tier_dist_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('profit_shares', function (Blueprint $table) { $table->dropIndex('ps_status_pay_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('profit_shares', function (Blueprint $table) { $table->dropIndex('ps_pd_status_idx'); }); } catch (Throwable $e) {}
            // Drop foreign keys if present
            try { Schema::table('profit_shares', function (Blueprint $table) { $table->dropForeign(['profit_distribution_id']); }); } catch (Throwable $e) {}
            try { Schema::table('profit_shares', function (Blueprint $table) { $table->dropForeign(['processed_by']); }); } catch (Throwable $e) {}
            // Drop columns (best-effort)
            try { Schema::table('profit_shares', function (Blueprint $table) { $table->dropColumn(['profit_distribution_id','tier_at_distribution','tier_bonus_applied','distribution_type','base_amount','bonus_amount','community_project_bonus','includes_community_allocation','payment_method','processed_at','processed_by']); }); } catch (Throwable $e) {}
        }

        if (Schema::hasTable('profit_distributions')) {
            // Drop indexes by name; ignore if missing
            try { Schema::table('profit_distributions', function (Blueprint $table) { $table->dropIndex('pd_period_incl_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('profit_distributions', function (Blueprint $table) { $table->dropIndex('pd_status_proc_idx'); }); } catch (Throwable $e) {}
            // Drop columns (best-effort)
            try { Schema::table('profit_distributions', function (Blueprint $table) { $table->dropColumn(['community_project_allocation','community_project_percentage','tier_based_bonuses','distribution_source','includes_community_projects','community_profit_pool','tier_bonus_multipliers']); }); } catch (Throwable $e) {}
        }
    }
};