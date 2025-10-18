<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

trait UsesMinimalDatabase
{
    /**
     * Essential migrations that are required for most tests
     */
    protected array $essentialMigrations = [
        '2014_10_12_000000_create_users_table.php',
        '2024_04_17_000001_create_investment_tiers_table.php',
        '2024_02_19_000001_create_investments_table.php',
        '2024_02_20_000003_create_referral_commissions_table.php',
    ];

    protected function setUpMinimalDatabase(): void
    {
        $this->createEssentialTables();
        $this->seedEssentialData();
    }

    protected function createEssentialTables(): void
    {
        // Create users table
        if (!Schema::hasTable('users')) {
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->unsignedBigInteger('current_investment_tier_id')->nullable();
                $table->decimal('total_investment_amount', 15, 2)->default(0);
                $table->timestamp('tier_upgraded_at')->nullable();
                $table->json('tier_history')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // Create investment_tiers table
        if (!Schema::hasTable('investment_tiers')) {
            Schema::create('investment_tiers', function ($table) {
                $table->id();
                $table->string('name');
                $table->decimal('minimum_investment', 10, 2);
                $table->decimal('monthly_fee', 8, 2)->nullable();
                $table->decimal('monthly_share', 8, 2)->nullable();
                $table->decimal('required_team_volume', 12, 2)->nullable();
                $table->integer('required_active_referrals')->nullable();
                $table->integer('consecutive_months_required')->nullable();
                $table->decimal('achievement_bonus', 10, 2)->nullable();
                $table->decimal('monthly_team_volume_bonus_rate', 5, 2)->nullable();
                $table->decimal('fixed_profit_rate', 5, 2);
                $table->decimal('direct_referral_rate', 5, 2);
                $table->decimal('level2_referral_rate', 5, 2)->default(0);
                $table->decimal('level3_referral_rate', 5, 2)->default(0);
                $table->decimal('reinvestment_bonus_rate', 5, 2)->default(0);
                $table->boolean('quarterly_profit_sharing_eligible')->default(false);
                $table->boolean('annual_profit_sharing_eligible')->default(false);
                $table->decimal('profit_sharing_percentage', 5, 2)->nullable();
                $table->boolean('leadership_bonus_eligible')->default(false);
                $table->boolean('business_facilitation_eligible')->default(false);
                $table->json('benefits')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_archived')->default(false);
                $table->text('description')->nullable();
                $table->integer('order')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create team_volumes table (essential for MyGrowNet tests)
        if (!Schema::hasTable('team_volumes')) {
            Schema::create('team_volumes', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->decimal('team_volume', 15, 2)->default(0);
                $table->decimal('personal_volume', 15, 2)->default(0);
                $table->integer('active_referrals_count')->default(0);
                $table->integer('team_depth')->default(0);
                $table->date('period_start');
                $table->date('period_end');
                $table->timestamps();
                
                $table->index(['user_id', 'period_start', 'period_end']);
            });
        }

        // Create physical_rewards table (for reward allocation tests)
        if (!Schema::hasTable('physical_rewards')) {
            Schema::create('physical_rewards', function ($table) {
                $table->id();
                $table->string('name');
                $table->text('description');
                $table->enum('category', ['electronics', 'property', 'vehicle', 'business_kit', 'merchandise']);
                $table->decimal('estimated_value', 10, 2);
                $table->json('required_membership_tiers');
                $table->integer('required_referrals')->default(0);
                $table->decimal('required_subscription_amount', 10, 2)->default(0);
                $table->integer('required_sustained_months')->default(0);
                $table->integer('available_quantity')->default(1);
                $table->integer('allocated_quantity')->default(0);
                $table->string('image_url')->nullable();
                $table->json('specifications')->nullable();
                $table->text('terms_and_conditions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Create tier_qualifications table (for sustained months tracking)
        if (!Schema::hasTable('tier_qualifications')) {
            Schema::create('tier_qualifications', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('tier_id');
                $table->date('qualification_month');
                $table->boolean('qualifies')->default(false);
                $table->integer('consecutive_months')->default(0);
                $table->boolean('permanent_status')->default(false);
                $table->decimal('team_volume', 15, 2)->default(0);
                $table->integer('active_referrals')->default(0);
                $table->decimal('required_team_volume', 15, 2)->default(0);
                $table->integer('required_active_referrals')->default(0);
                $table->timestamps();
            });
        }
    }

    protected function seedEssentialData(): void
    {
        // Create basic investment tiers
        if (DB::table('investment_tiers')->count() === 0) {
            DB::table('investment_tiers')->insert([
                [
                    'name' => 'Basic',
                    'minimum_investment' => 500,
                    'fixed_profit_rate' => 3.0,
                    'direct_referral_rate' => 5.0,
                    'level2_referral_rate' => 0.0,
                    'level3_referral_rate' => 0.0,
                    'reinvestment_bonus_rate' => 0.0,
                    'order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Starter',
                    'minimum_investment' => 1000,
                    'fixed_profit_rate' => 5.0,
                    'direct_referral_rate' => 7.0,
                    'level2_referral_rate' => 2.0,
                    'level3_referral_rate' => 0.0,
                    'reinvestment_bonus_rate' => 8.0,
                    'order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    protected function tearDownMinimalDatabase(): void
    {
        // Clean up test data but keep tables for performance
        DB::table('users')->where('email', 'like', '%@example.com')->delete();
        DB::table('team_volumes')->where('user_id', '>', 1000)->delete();
        DB::table('physical_rewards')->where('name', 'like', 'Test%')->delete();
        if (Schema::hasTable('tier_qualifications')) {
            DB::table('tier_qualifications')->where('user_id', '>', 1000)->delete();
        }
    }
}