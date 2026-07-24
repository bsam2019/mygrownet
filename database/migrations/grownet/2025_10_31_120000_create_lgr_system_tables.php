<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // LGR Cycles - tracks each member's 70-day reward cycle
        Schema::create('lgr_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'completed', 'suspended', 'terminated'])->default('active');
            $table->integer('active_days')->default(0);
            $table->decimal('total_earned_lgc', 10, 2)->default(0);
            $table->decimal('daily_rate', 10, 2)->default(25.00);
            $table->text('suspension_reason')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('start_date');
            $table->index('end_date');
        });

        // LGR Activities - tracks daily activities for reward eligibility
        Schema::create('lgr_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lgr_cycle_id')->constrained()->onDelete('cascade');
            $table->date('activity_date');
            $table->enum('activity_type', [
                'learning_module',
                'marketplace_purchase',
                'marketplace_sale',
                'event_attendance',
                'platform_task',
                'community_engagement',
                'business_plan',
                'quiz_completion'
            ]);
            $table->string('activity_description');
            $table->json('activity_metadata')->nullable();
            $table->decimal('lgc_earned', 10, 2)->default(0);
            $table->boolean('verified')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'activity_date']);
            $table->index(['lgr_cycle_id', 'activity_date']);
            $table->unique(['user_id', 'activity_date', 'activity_type']);
        });

        // LGR Pool - tracks the reward pool balance and allocations
        Schema::create('lgr_pools', function (Blueprint $table) {
            $table->id();
            $table->date('pool_date');
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('contributions', 12, 2)->default(0);
            $table->decimal('allocations', 12, 2)->default(0);
            $table->decimal('closing_balance', 12, 2)->default(0);
            $table->decimal('reserve_amount', 12, 2)->default(0);
            $table->decimal('available_for_distribution', 12, 2)->default(0);
            $table->json('contribution_sources')->nullable();
            $table->timestamps();
            
            $table->unique('pool_date');
            $table->index('pool_date');
        });

        // LGR Pool Contributions - tracks revenue sources contributing to pool
        Schema::create('lgr_pool_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lgr_pool_id')->constrained()->onDelete('cascade');
            $table->enum('source_type', [
                'registration_fee',
                'product_sale',
                'marketplace_fee',
                'venture_fee',
                'subscription_renewal',
                'other'
            ]);
            $table->string('source_reference')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('percentage', 5, 2)->comment('Percentage of source allocated to pool');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['lgr_pool_id', 'source_type']);
        });

        // LGR Qualifications - tracks member qualification status
        Schema::create('lgr_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('starter_package_completed')->default(false);
            $table->timestamp('starter_package_completed_at')->nullable();
            $table->boolean('training_completed')->default(false);
            $table->timestamp('training_completed_at')->nullable();
            $table->integer('first_level_members')->default(0);
            $table->boolean('network_requirement_met')->default(false);
            $table->timestamp('network_requirement_met_at')->nullable();
            $table->integer('activities_completed')->default(0);
            $table->boolean('activity_requirement_met')->default(false);
            $table->timestamp('activity_requirement_met_at')->nullable();
            $table->boolean('fully_qualified')->default(false);
            $table->timestamp('fully_qualified_at')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
            $table->index('fully_qualified');
        });

        // LGR Payouts - tracks LGC credit distributions
        Schema::create('lgr_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lgr_cycle_id')->constrained()->onDelete('cascade');
            $table->foreignId('lgr_pool_id')->nullable()->constrained()->onDelete('set null');
            $table->date('payout_date');
            $table->decimal('lgc_amount', 10, 2);
            $table->decimal('pool_balance_before', 12, 2);
            $table->decimal('pool_balance_after', 12, 2);
            $table->boolean('proportional_adjustment')->default(false);
            $table->decimal('adjustment_factor', 5, 4)->nullable()->comment('Multiplier if pool insufficient');
            $table->enum('status', ['pending', 'credited', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'payout_date']);
            $table->index(['lgr_cycle_id', 'status']);
        });

        // LGR Settings - system-wide configuration
        Schema::create('lgr_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lgr_payouts');
        Schema::dropIfExists('lgr_qualifications');
        Schema::dropIfExists('lgr_pool_contributions');
        Schema::dropIfExists('lgr_pools');
        Schema::dropIfExists('lgr_activities');
        Schema::dropIfExists('lgr_cycles');
        Schema::dropIfExists('lgr_settings');
    }
};
