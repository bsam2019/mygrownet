<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('minimum_investment', 15, 2);
            $table->decimal('fixed_profit_rate', 5, 2); // Annual fixed profit rate
            $table->decimal('direct_referral_rate', 5, 2); // Direct referral commission
            $table->decimal('level2_referral_rate', 5, 2)->nullable(); // Level 2 referral commission
            $table->decimal('level3_referral_rate', 5, 2)->nullable(); // Level 3 referral commission
            $table->json('benefits')->nullable(); // Additional tier benefits
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->integer('order')->default(0); // For displaying tiers in order
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_tiers');
    }
}; 