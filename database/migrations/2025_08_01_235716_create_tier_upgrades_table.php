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
        Schema::create('tier_upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_tier_id')->constrained('investment_tiers')->onDelete('cascade');
            $table->foreignId('to_tier_id')->constrained('investment_tiers')->onDelete('cascade');
            $table->decimal('total_investment_amount', 15, 2);
            $table->string('upgrade_reason')->default('automatic_investment_threshold');
            $table->timestamp('processed_at');
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tier_upgrades');
    }
};
