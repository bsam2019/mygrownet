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
        if (!Schema::hasTable('achievement_bonuses')) {
            Schema::create('achievement_bonuses', function (Blueprint $table) {
                $table->id();
                // Define FK columns without constraints first
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('achievement_id')->nullable();
                $table->unsignedBigInteger('payment_transaction_id')->nullable();

                $table->string('bonus_type'); // tier_advancement, performance, leadership, milestone, special
                $table->decimal('amount', 10, 2);
                $table->string('status')->default('pending'); // pending, paid, cancelled, expired
                $table->timestamp('earned_at');
                $table->timestamp('paid_at')->nullable();
                $table->string('payment_method')->nullable(); // wallet, mobile_money, bank_transfer
                
                // Context fields
                $table->string('tier_at_earning')->nullable();
                $table->decimal('team_volume_at_earning', 12, 2)->nullable();
                $table->integer('active_referrals_at_earning')->nullable();
                
                // Description and metadata
                $table->text('description')->nullable();
                $table->json('metadata')->nullable(); // Additional context data
                
                $table->timestamps();

                // Indexes for performance
                $table->index(['user_id', 'status']);
                $table->index(['bonus_type', 'status']);
                $table->index(['earned_at', 'status']);
                $table->index('achievement_id');
                $table->index('payment_transaction_id');
            });
        }

        // Add foreign keys conditionally only if referenced tables exist
        try {
            if (Schema::hasTable('achievement_bonuses')) {
                Schema::table('achievement_bonuses', function (Blueprint $table) {
                    if (Schema::hasTable('users')) {
                        $table->foreign('user_id', 'ab_user_fk')->references('id')->on('users')->onDelete('cascade');
                    }
                    if (Schema::hasTable('achievements')) {
                        $table->foreign('achievement_id', 'ab_ach_fk')->references('id')->on('achievements')->onDelete('set null');
                    }
                    if (Schema::hasTable('payment_transactions')) {
                        $table->foreign('payment_transaction_id', 'ab_paytxn_fk')->references('id')->on('payment_transactions')->onDelete('set null');
                    }
                });
            }
        } catch (Throwable $e) {
            // ignore if constraints already exist or referenced tables missing
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_bonuses');
    }
};