<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tier_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_tier_id')->constrained()->onDelete('cascade');
            $table->decimal('early_withdrawal_penalty', 5, 2)->default(50.00); // Percentage
            $table->decimal('partial_withdrawal_limit', 5, 2)->default(50.00); // Percentage of profits
            $table->integer('minimum_lock_in_period')->default(12); // Months
            $table->decimal('performance_bonus_rate', 5, 2)->nullable(); // Additional bonus rate
            $table->json('additional_rules')->nullable(); // Any additional tier-specific rules
            $table->boolean('requires_kyc')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_settings');
    }
}; 