<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'current_investment_tier_id')) {
                $table->foreignId('current_investment_tier_id')->nullable()->constrained('investment_tiers')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'total_investment_amount')) {
                $table->decimal('total_investment_amount', 15, 2)->default(0.00);
            }
            if (!Schema::hasColumn('users', 'tier_upgraded_at')) {
                $table->timestamp('tier_upgraded_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'tier_history')) {
                $table->json('tier_history')->nullable(); // Track tier changes
            }
            if (!Schema::hasColumn('users', 'total_earnings')) {
                $table->decimal('total_earnings', 15, 2)->default(0.00);
            }
            if (!Schema::hasColumn('users', 'total_referral_earnings')) {
                $table->decimal('total_referral_earnings', 15, 2)->default(0.00);
            }
            if (!Schema::hasColumn('users', 'total_profit_earnings')) {
                $table->decimal('total_profit_earnings', 15, 2)->default(0.00);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'current_investment_tier_id',
                'total_investment_amount',
                'tier_upgraded_at',
                'tier_history',
                'total_earnings',
                'total_referral_earnings',
                'total_profit_earnings'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    if ($column === 'current_investment_tier_id') {
                        $table->dropForeign(['current_investment_tier_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
}; 