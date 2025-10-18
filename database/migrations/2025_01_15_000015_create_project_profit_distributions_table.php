<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('project_profit_distributions')) {
            Schema::create('project_profit_distributions', function (Blueprint $table) {
                $table->id();
                // define as plain columns first; add FKs conditionally after creation
                $table->unsignedBigInteger('community_project_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('project_contribution_id');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->unsignedBigInteger('paid_by')->nullable();

                // Distribution details
                $table->decimal('distribution_amount', 12, 2);
                $table->decimal('contribution_amount', 12, 2); // Contribution amount at time of distribution
                $table->decimal('contribution_percentage', 8, 4); // Percentage of total project funding
                $table->decimal('project_profit_amount', 15, 2); // Total project profit being distributed
                $table->decimal('distribution_rate', 5, 2); // Percentage rate for this distribution

                // Period information
                $table->enum('distribution_type', ['monthly', 'quarterly', 'annual', 'final', 'milestone']);
                $table->date('period_start');
                $table->date('period_end');
                $table->string('distribution_period_label'); // e.g., "Q1 2025", "January 2025"

                // Status and processing
                $table->enum('status', ['calculated', 'approved', 'paid', 'cancelled'])->default('calculated');
                $table->timestamp('calculated_at');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();

                // Administrative
                $table->string('payment_reference')->nullable();
                $table->enum('payment_method', ['bank_transfer', 'mobile_money', 'internal_balance'])->nullable();
                $table->text('notes')->nullable();

                // Tier information at distribution time
                $table->string('tier_at_distribution')->nullable();
                $table->decimal('tier_bonus_multiplier', 3, 2)->default(1.00); // Tier-based bonus

                $table->timestamps();

                // Indexes (use short names to avoid 64-char limit)
                $table->index(['community_project_id', 'distribution_type'], 'ppd_cp_id_dtype_idx');
                $table->index(['user_id', 'status'], 'ppd_user_status_idx');
                $table->index(['period_start', 'period_end'], 'ppd_period_idx');
                $table->index(['status', 'calculated_at'], 'ppd_status_calc_idx');
                $table->index(['approved_by'], 'ppd_approved_by_idx');
                $table->index(['paid_by'], 'ppd_paid_by_idx');

                // Unique constraint to prevent duplicate distributions (already short)
                $table->unique(['project_contribution_id', 'distribution_period_label', 'distribution_type'], 'unique_distribution_per_period');
            });
        }

        // Add foreign keys conditionally to avoid failures if referenced tables aren't ready yet
        try {
            if (Schema::hasTable('project_profit_distributions') && Schema::hasTable('community_projects')) {
                Schema::table('project_profit_distributions', function (Blueprint $table) {
                    $table->foreign('community_project_id')->references('id')->on('community_projects')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}
        try {
            if (Schema::hasTable('project_profit_distributions') && Schema::hasTable('users')) {
                Schema::table('project_profit_distributions', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                    $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
                    $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
                });
            }
        } catch (Throwable $e) {}
        try {
            if (Schema::hasTable('project_profit_distributions') && Schema::hasTable('project_contributions')) {
                Schema::table('project_profit_distributions', function (Blueprint $table) {
                    $table->foreign('project_contribution_id')->references('id')->on('project_contributions')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}
    }

    public function down(): void
    {
        Schema::dropIfExists('project_profit_distributions');
    }
};