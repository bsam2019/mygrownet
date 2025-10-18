<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('physical_reward_allocations')) {
        Schema::create('physical_reward_allocations', function (Blueprint $table) {
            $table->id();
            // define as unsigned big integers first; add FKs conditionally after table exists
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('physical_reward_id');
            $table->unsignedBigInteger('tier_id');
            
            // Performance metrics at time of allocation
            $table->decimal('team_volume_at_allocation', 15, 2)->default(0);
            $table->integer('active_referrals_at_allocation')->default(0);
            $table->integer('team_depth_at_allocation')->default(0);
            
            // Allocation status and tracking
            $table->enum('status', ['allocated', 'delivered', 'ownership_transferred', 'forfeited', 'recovered'])->default('allocated');
            $table->timestamp('allocated_at');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('ownership_transferred_at')->nullable();
            $table->timestamp('forfeited_at')->nullable();
            
            // Maintenance tracking
            $table->boolean('maintenance_compliant')->default(true);
            $table->integer('maintenance_months_completed')->default(0);
            $table->timestamp('last_maintenance_check')->nullable();
            $table->text('maintenance_notes')->nullable();
            
            // Income generation tracking
            $table->decimal('total_income_generated', 12, 2)->default(0);
            $table->decimal('monthly_income_average', 10, 2)->default(0);
            $table->timestamp('income_tracking_started')->nullable();
            
            // Asset management
            $table->json('asset_management_details')->nullable();
            $table->string('asset_manager')->nullable();
            $table->text('special_conditions')->nullable();
            
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->index(['user_id', 'status']);
            $table->index(['physical_reward_id', 'status']);
            $table->index(['tier_id', 'allocated_at']);
            $table->index(['status', 'allocated_at']);
        });
        }

        // Add foreign keys conditionally to avoid issues if referenced tables aren't ready yet
        try {
            if (Schema::hasTable('physical_reward_allocations') && Schema::hasTable('users')) {
                Schema::table('physical_reward_allocations', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}

        try {
            if (Schema::hasTable('physical_reward_allocations') && Schema::hasTable('physical_rewards')) {
                Schema::table('physical_reward_allocations', function (Blueprint $table) {
                    $table->foreign('physical_reward_id', 'pra_physical_reward_id_fk')
                          ->references('id')->on('physical_rewards')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}

        try {
            if (Schema::hasTable('physical_reward_allocations') && Schema::hasTable('investment_tiers')) {
                Schema::table('physical_reward_allocations', function (Blueprint $table) {
                    $table->foreign('tier_id')->references('id')->on('investment_tiers')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_reward_allocations');
    }
};