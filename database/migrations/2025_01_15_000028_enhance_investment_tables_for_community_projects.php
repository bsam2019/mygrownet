<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enhance investment_opportunities table for community project tracking
        if (Schema::hasTable('investment_opportunities')) {
            Schema::table('investment_opportunities', function (Blueprint $table) {
                // Community project integration
                if (!Schema::hasColumn('investment_opportunities', 'community_project_id')) {
                    // Create column first; add FK later conditionally
                    $table->unsignedBigInteger('community_project_id')->nullable()->after('category_id');
                }
                if (!Schema::hasColumn('investment_opportunities', 'is_community_project')) {
                    $table->boolean('is_community_project')->default(false)->after('community_project_id');
                }
                if (!Schema::hasColumn('investment_opportunities', 'project_type')) {
                    $table->enum('project_type', ['traditional', 'community', 'hybrid'])->default('traditional')->after('is_community_project');
                }
                
                // Voting and governance fields
                if (!Schema::hasColumn('investment_opportunities', 'requires_community_voting')) {
                    $table->boolean('requires_community_voting')->default(false)->after('project_type');
                }
                if (!Schema::hasColumn('investment_opportunities', 'voting_threshold_percentage')) {
                    $table->decimal('voting_threshold_percentage', 5, 2)->default(50.00)->after('requires_community_voting');
                }
                if (!Schema::hasColumn('investment_opportunities', 'minimum_voters_required')) {
                    $table->integer('minimum_voters_required')->default(1)->after('voting_threshold_percentage');
                }
                if (!Schema::hasColumn('investment_opportunities', 'voting_criteria')) {
                    $table->json('voting_criteria')->nullable()->after('minimum_voters_required');
                }
                if (!Schema::hasColumn('investment_opportunities', 'governance_rules')) {
                    $table->json('governance_rules')->nullable()->after('voting_criteria');
                }
                
                // Community participation requirements
                if (!Schema::hasColumn('investment_opportunities', 'required_membership_tiers')) {
                    $table->json('required_membership_tiers')->nullable()->after('governance_rules');
                }
                if (!Schema::hasColumn('investment_opportunities', 'minimum_community_contribution')) {
                    $table->decimal('minimum_community_contribution', 12, 2)->nullable()->after('required_membership_tiers');
                }
                if (!Schema::hasColumn('investment_opportunities', 'maximum_community_contribution')) {
                    $table->decimal('maximum_community_contribution', 12, 2)->nullable()->after('minimum_community_contribution');
                }
                if (!Schema::hasColumn('investment_opportunities', 'tier_contribution_limits')) {
                    $table->json('tier_contribution_limits')->nullable()->after('maximum_community_contribution');
                }
                if (!Schema::hasColumn('investment_opportunities', 'tier_voting_weights')) {
                    $table->json('tier_voting_weights')->nullable()->after('tier_contribution_limits');
                }
                
                // Project timeline and milestones
                if (!Schema::hasColumn('investment_opportunities', 'community_voting_start')) {
                    $table->date('community_voting_start')->nullable()->after('tier_voting_weights');
                }
                if (!Schema::hasColumn('investment_opportunities', 'community_voting_end')) {
                    $table->date('community_voting_end')->nullable()->after('community_voting_start');
                }
                if (!Schema::hasColumn('investment_opportunities', 'funding_deadline')) {
                    $table->date('funding_deadline')->nullable()->after('community_voting_end');
                }
                if (!Schema::hasColumn('investment_opportunities', 'project_milestones')) {
                    $table->json('project_milestones')->nullable()->after('funding_deadline');
                }
                if (!Schema::hasColumn('investment_opportunities', 'success_metrics')) {
                    $table->json('success_metrics')->nullable()->after('project_milestones');
                }
                
                // Community engagement tracking
                if (!Schema::hasColumn('investment_opportunities', 'total_community_votes')) {
                    $table->integer('total_community_votes')->default(0)->after('success_metrics');
                }
                if (!Schema::hasColumn('investment_opportunities', 'community_approval_rating')) {
                    $table->decimal('community_approval_rating', 5, 2)->default(0)->after('total_community_votes');
                }
                if (!Schema::hasColumn('investment_opportunities', 'community_contributors_count')) {
                    $table->integer('community_contributors_count')->default(0)->after('community_approval_rating');
                }
                if (!Schema::hasColumn('investment_opportunities', 'community_funding_raised')) {
                    $table->decimal('community_funding_raised', 15, 2)->default(0)->after('community_contributors_count');
                }
                
                // Profit sharing configuration
                if (!Schema::hasColumn('investment_opportunities', 'community_profit_share_percentage')) {
                    $table->decimal('community_profit_share_percentage', 5, 2)->default(0)->after('community_funding_raised');
                }
                if (!Schema::hasColumn('investment_opportunities', 'includes_quarterly_distributions')) {
                    $table->boolean('includes_quarterly_distributions')->default(false)->after('community_profit_share_percentage');
                }
                if (!Schema::hasColumn('investment_opportunities', 'includes_annual_distributions')) {
                    $table->boolean('includes_annual_distributions')->default(false)->after('includes_quarterly_distributions');
                }
                if (!Schema::hasColumn('investment_opportunities', 'profit_distribution_rules')) {
                    $table->json('profit_distribution_rules')->nullable()->after('includes_annual_distributions');
                }
                
            });
            // Add indexes for efficient queries (explicit short names), guarded for reruns
            try {
                Schema::table('investment_opportunities', function (Blueprint $table) {
                    $table->index(['is_community_project', 'status'], 'io_commproj_status_idx');
                    $table->index(['project_type', 'requires_community_voting'], 'io_projtype_reqvote_idx');
                    $table->index(['community_voting_start', 'community_voting_end'], 'io_vote_window_idx');
                    $table->index(['community_project_id', 'status'], 'io_commprojid_status_idx');
                });
            } catch (Throwable $e) {}
            // Add foreign keys conditionally (short names)
            try {
                if (Schema::hasTable('investment_opportunities') && Schema::hasTable('community_projects')) {
                    Schema::table('investment_opportunities', function (Blueprint $table) {
                        try { $table->foreign('community_project_id', 'io_cpid_fk')->references('id')->on('community_projects')->onDelete('set null'); } catch (Throwable $e) {}
                    });
                }
            } catch (Throwable $e) {}
        }

        // Enhance investments table for community project integration
        if (Schema::hasTable('investments')) {
            Schema::table('investments', function (Blueprint $table) {
                // Community project relationship
                if (!Schema::hasColumn('investments', 'community_project_id')) {
                    // Create column first; add FK later conditionally
                    $table->unsignedBigInteger('community_project_id')->nullable()->after('category_id');
                }
                if (!Schema::hasColumn('investments', 'is_community_investment')) {
                    $table->boolean('is_community_investment')->default(false)->after('community_project_id');
                }
                if (!Schema::hasColumn('investments', 'investment_source')) {
                    $table->enum('investment_source', ['traditional', 'community_project', 'hybrid'])->default('traditional')->after('is_community_investment');
                }
                
                // Community participation tracking
                if (!Schema::hasColumn('investments', 'community_contribution_amount')) {
                    $table->decimal('community_contribution_amount', 12, 2)->default(0)->after('investment_source');
                }
                if (!Schema::hasColumn('investments', 'tier_at_community_investment')) {
                    $table->string('tier_at_community_investment')->nullable()->after('community_contribution_amount');
                }
                if (!Schema::hasColumn('investments', 'voting_power_weight')) {
                    $table->decimal('voting_power_weight', 8, 4)->default(1.0000)->after('tier_at_community_investment');
                }
                if (!Schema::hasColumn('investments', 'participated_in_voting')) {
                    $table->boolean('participated_in_voting')->default(false)->after('voting_power_weight');
                }
                
                // Community profit sharing eligibility
                if (!Schema::hasColumn('investments', 'eligible_for_community_profits')) {
                    $table->boolean('eligible_for_community_profits')->default(false)->after('participated_in_voting');
                }
                if (!Schema::hasColumn('investments', 'community_profit_share_percentage')) {
                    $table->decimal('community_profit_share_percentage', 8, 4)->default(0)->after('eligible_for_community_profits');
                }
                if (!Schema::hasColumn('investments', 'community_investment_date')) {
                    $table->timestamp('community_investment_date')->nullable()->after('community_profit_share_percentage');
                }
                
            });

            // Add indexes for community project queries (explicit short names), guarded for reruns
            try {
                Schema::table('investments', function (Blueprint $table) {
                    $table->index(['community_project_id', 'is_community_investment'], 'inv_commproj_iscomm_idx');
                    $table->index(['investment_source', 'status'], 'inv_source_status_idx');
                    $table->index(['eligible_for_community_profits', 'status'], 'inv_eligible_status_idx');
                });
            } catch (Throwable $e) {}
            // Add foreign keys conditionally (short names)
            try {
                if (Schema::hasTable('investments') && Schema::hasTable('community_projects')) {
                    Schema::table('investments', function (Blueprint $table) {
                        try { $table->foreign('community_project_id', 'inv_cpid_fk')->references('id')->on('community_projects')->onDelete('set null'); } catch (Throwable $e) {}
                    });
                }
            } catch (Throwable $e) {}
        }

        // Create investment_opportunity_votes table for governance (create without FKs first)
        if (!Schema::hasTable('investment_opportunity_votes')) {
        Schema::create('investment_opportunity_votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('investment_opportunity_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('investment_id')->nullable();
            
            // Vote details
            $table->enum('vote_type', ['approve', 'reject', 'abstain'])->default('approve');
            $table->decimal('voting_power', 10, 4)->default(1.0000);
            $table->string('tier_at_vote')->nullable();
            $table->decimal('investment_amount_at_vote', 12, 2)->default(0);
            
            // Vote reasoning and comments
            $table->text('vote_reason')->nullable();
            $table->json('vote_criteria_scores')->nullable(); // Scoring against different criteria
            $table->text('comments')->nullable();
            
            // Vote metadata
            $table->timestamp('voted_at');
            $table->ipAddress('voter_ip')->nullable();
            $table->string('voter_user_agent')->nullable();
            
            $table->timestamps();
            
            // Ensure one vote per user per opportunity
            $table->unique(['investment_opportunity_id', 'user_id'], 'iov_ioid_user_uniq');
            
            // Indexes for efficient queries (explicit short names)
            $table->index(['investment_opportunity_id', 'vote_type'], 'iov_ioid_vtype_idx');
            $table->index(['user_id', 'voted_at'], 'iov_user_voted_idx');
            $table->index(['tier_at_vote', 'vote_type'], 'iov_tier_vtype_idx');
        });
        }
        // Add FKs for investment_opportunity_votes conditionally
        try {
            if (Schema::hasTable('investment_opportunity_votes')) {
                Schema::table('investment_opportunity_votes', function (Blueprint $table) {
                    if (Schema::hasTable('investment_opportunities')) { try { $table->foreign('investment_opportunity_id', 'iov_ioid_fk')->references('id')->on('investment_opportunities')->onDelete('cascade'); } catch (Throwable $e) {} }
                    if (Schema::hasTable('users')) { try { $table->foreign('user_id', 'iov_user_fk')->references('id')->on('users')->onDelete('cascade'); } catch (Throwable $e) {} }
                    if (Schema::hasTable('investments')) { try { $table->foreign('investment_id', 'iov_inv_fk')->references('id')->on('investments')->onDelete('set null'); } catch (Throwable $e) {} }
                });
            }
        } catch (Throwable $e) {}

        // Create community_investment_distributions table for profit sharing (create without FKs first)
        if (!Schema::hasTable('community_investment_distributions')) {
            Schema::create('community_investment_distributions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('investment_opportunity_id');
                $table->unsignedBigInteger('community_project_id')->nullable();
                $table->unsignedBigInteger('profit_distribution_id');
                
                // Distribution details
                $table->string('distribution_period_label'); // e.g., "Q1 2025", "2024 Annual"
                $table->date('period_start');
                $table->date('period_end');
                $table->enum('distribution_type', ['quarterly', 'annual', 'milestone', 'project_completion']);
                
                // Financial details
                $table->decimal('total_profit_pool', 15, 2);
                $table->decimal('community_allocation_percentage', 5, 2);
                $table->decimal('community_allocation_amount', 15, 2);
                $table->decimal('total_distributed_amount', 15, 2)->default(0);
                
                // Distribution status
                $table->enum('status', ['calculated', 'approved', 'distributed', 'cancelled'])->default('calculated');
                $table->timestamp('calculated_at');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('distributed_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->unsignedBigInteger('distributed_by')->nullable();
                
                // Timestamps
                $table->timestamps();

            // Indexes for efficient queries (explicit short names)
            $table->index(['investment_opportunity_id', 'distribution_type'], 'cid_ioid_disttype_idx');
            $table->index(['community_project_id', 'status'], 'cid_cpid_status_idx');
            $table->index(['profit_distribution_id', 'status'], 'cid_pdid_status_idx');
            $table->index(['period_start', 'period_end'], 'cid_period_idx');
        });
        }
        // Add FKs for community_investment_distributions conditionally
        try {
            if (Schema::hasTable('community_investment_distributions')) {
                Schema::table('community_investment_distributions', function (Blueprint $table) {
                    if (Schema::hasTable('investment_opportunities')) { try { $table->foreign('investment_opportunity_id', 'cid_ioid_fk')->references('id')->on('investment_opportunities')->onDelete('cascade'); } catch (Throwable $e) {} }
                    if (Schema::hasTable('community_projects')) { try { $table->foreign('community_project_id', 'cid_cpid_fk')->references('id')->on('community_projects')->onDelete('set null'); } catch (Throwable $e) {} }
                    if (Schema::hasTable('profit_distributions')) { try { $table->foreign('profit_distribution_id', 'cid_pdid_fk')->references('id')->on('profit_distributions')->onDelete('cascade'); } catch (Throwable $e) {} }
                    if (Schema::hasTable('users')) {
                        try { $table->foreign('approved_by', 'cid_approved_by_fk')->references('id')->on('users')->onDelete('set null'); } catch (Throwable $e) {}
                        try { $table->foreign('distributed_by', 'cid_distributed_by_fk')->references('id')->on('users')->onDelete('set null'); } catch (Throwable $e) {}
                    }
                });
            }
        } catch (Throwable $e) {}
        // Create individual community investment profit shares (create without FKs first)
        if (!Schema::hasTable('community_investment_profit_shares')) {
        Schema::create('community_investment_profit_shares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_investment_distribution_id');
            $table->unsignedBigInteger('investment_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('investment_opportunity_id');
            
            // Share calculation details
            $table->decimal('investment_amount', 12, 2);
            $table->decimal('community_contribution_amount', 12, 2);
            $table->string('tier_at_distribution');
            $table->decimal('tier_bonus_multiplier', 4, 2)->default(1.00);
            $table->decimal('voting_participation_bonus', 4, 2)->default(0.00);
            
            // Profit share amounts
            $table->decimal('base_profit_share', 12, 2);
            $table->decimal('tier_bonus_amount', 12, 2)->default(0);
            $table->decimal('participation_bonus_amount', 12, 2)->default(0);
            $table->decimal('total_profit_share', 12, 2);
            
            // Payment details
            $table->enum('payment_status', ['pending', 'processed', 'failed', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['bank_transfer', 'mobile_money', 'internal_balance'])->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('payment_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for efficient queries (explicit short names)
            $table->index(['community_investment_distribution_id', 'payment_status'], 'cips_cid_paystat_idx');
            $table->index(['user_id', 'payment_status'], 'cips_user_paystat_idx');
            $table->index(['investment_opportunity_id', 'tier_at_distribution'], 'cips_ioid_tier_idx');
            $table->index(['payment_date', 'payment_status'], 'cips_paydate_status_idx');
        });
    }
        // Add FKs for community_investment_profit_shares conditionally
        try {
            if (Schema::hasTable('community_investment_profit_shares')) {
                Schema::table('community_investment_profit_shares', function (Blueprint $table) {
                    if (Schema::hasTable('community_investment_distributions')) { try { $table->foreign('community_investment_distribution_id', 'cips_cid_fk')->references('id')->on('community_investment_distributions')->onDelete('cascade'); } catch (Throwable $e) {} }
                    if (Schema::hasTable('investments')) { try { $table->foreign('investment_id', 'cips_inv_fk')->references('id')->on('investments')->onDelete('cascade'); } catch (Throwable $e) {} }
                    if (Schema::hasTable('users')) { try { $table->foreign('user_id', 'cips_user_fk')->references('id')->on('users')->onDelete('cascade'); } catch (Throwable $e) {} }
                    if (Schema::hasTable('investment_opportunities')) { try { $table->foreign('investment_opportunity_id', 'cips_ioid_fk')->references('id')->on('investment_opportunities')->onDelete('cascade'); } catch (Throwable $e) {} }
                });
            }
        } catch (Throwable $e) {}

    }

    public function down(): void
    {
        // Drop new tables first
        Schema::dropIfExists('community_investment_profit_shares');
        Schema::dropIfExists('community_investment_distributions');
        Schema::dropIfExists('investment_opportunity_votes');

        // Remove enhancements from investments table
        if (Schema::hasTable('investments')) {
            Schema::table('investments', function (Blueprint $table) {
                // Drop indexes first (by name)
                try { $table->dropIndex('inv_commproj_iscomm_idx'); } catch (Throwable $e) {}
                try { $table->dropIndex('inv_source_status_idx'); } catch (Throwable $e) {}
                try { $table->dropIndex('inv_eligible_status_idx'); } catch (Throwable $e) {}
                
                // Drop foreign key constraints
                try { $table->dropForeign('inv_cpid_fk'); } catch (Throwable $e) {}
                try { $table->dropForeign(['community_project_id']); } catch (Throwable $e) {}
                
                // Drop columns
                $table->dropColumn([
                    'community_project_id',
                    'is_community_investment',
                    'investment_source',
                    'community_contribution_amount',
                    'tier_at_community_investment',
                    'voting_power_weight',
                    'participated_in_voting',
                    'eligible_for_community_profits',
                    'community_profit_share_percentage',
                    'community_investment_date'
                ]);
            });
        }

        // Remove enhancements from investment_opportunities table
        if (Schema::hasTable('investment_opportunities')) {
            Schema::table('investment_opportunities', function (Blueprint $table) {
                // Drop indexes first (by name)
                try { $table->dropIndex('io_commproj_status_idx'); } catch (Throwable $e) {}
                try { $table->dropIndex('io_projtype_reqvote_idx'); } catch (Throwable $e) {}
                try { $table->dropIndex('io_vote_window_idx'); } catch (Throwable $e) {}
                try { $table->dropIndex('io_commprojid_status_idx'); } catch (Throwable $e) {}
                
                // Drop foreign key constraints
                try { $table->dropForeign('io_cpid_fk'); } catch (Throwable $e) {}
                try { $table->dropForeign(['community_project_id']); } catch (Throwable $e) {}
                
                // Drop columns
                $table->dropColumn([
                    'community_project_id',
                    'is_community_project',
                    'project_type',
                    'requires_community_voting',
                    'voting_threshold_percentage',
                    'minimum_voters_required',
                    'voting_criteria',
                    'governance_rules',
                    'required_membership_tiers',
                    'minimum_community_contribution',
                    'maximum_community_contribution',
                    'tier_contribution_limits',
                    'tier_voting_weights',
                    'community_voting_start',
                    'community_voting_end',
                    'funding_deadline',
                    'project_milestones',
                    'success_metrics',
                    'total_community_votes',
                    'community_approval_rating',
                    'community_contributors_count',
                    'community_funding_raised',
                    'community_profit_share_percentage',
                    'includes_quarterly_distributions',
                    'includes_annual_distributions',
                    'profit_distribution_rules'
                ]);
            });
        }
    }
};