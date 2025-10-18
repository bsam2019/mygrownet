<?php

use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ProfitDistribution;
use App\Models\User;
use App\Services\ProfitCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new ProfitCalculationService();
    
    $this->basicTier = InvestmentTier::factory()->create([
        'name' => 'Basic',
        'minimum_amount' => 500,
        'fixed_profit_rate' => 3.0,
    ]);
    
    $this->starterTier = InvestmentTier::factory()->create([
        'name' => 'Starter',
        'minimum_amount' => 1000,
        'fixed_profit_rate' => 5.0,
    ]);
    
    $this->user = User::factory()->create();
});

describe('ProfitCalculationService', function () {
    it('can calculate annual profit share for single tier', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->basicTier->id,
            'amount' => 1000,
            'created_at' => now()->subYear(),
        ]);

        $profitShare = $this->service->calculateAnnualProfitShare($investment);

        expect($profitShare)->toBe(30.0); // 1000 * 3% = 30
    });

    it('can calculate weighted profit share for tier upgrades', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->basicTier->id,
            'amount' => 1000,
            'created_at' => now()->subYear(),
        ]);

        // Simulate tier upgrade after 6 months
        $upgradeDate = now()->subMonths(6);
        
        $profitShare = $this->service->calculateWeightedProfitShare(
            $investment,
            $this->starterTier,
            $upgradeDate
        );

        // 6 months at 3% + 6 months at 5% = (1000 * 0.03 * 0.5) + (1000 * 0.05 * 0.5) = 15 + 25 = 40
        expect($profitShare)->toBe(40.0);
    });

    it('can calculate quarterly bonus based on performance', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000,
        ]);

        // Create other investments for total pool calculation
        Investment::factory()->count(9)->create([
            'amount' => 1000,
        ]);

        $totalQuarterlyProfit = 50000; // Total fund profit
        $bonusPercentage = 8; // 8% of profit goes to bonus pool

        $bonus = $this->service->calculateQuarterlyBonus(
            $investment,
            $totalQuarterlyProfit,
            $bonusPercentage
        );

        // User has 10% of pool (1000/10000), bonus pool is 4000 (50000 * 8%), so user gets 400
        expect($bonus)->toBe(400.0);
    });

    it('can calculate reinvestment bonus rates', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->starterTier->id,
            'amount' => 1000,
            'is_reinvestment' => true,
        ]);

        $bonusRate = $this->service->calculateReinvestmentBonusRate($investment);

        // Starter tier (5%) + reinvestment bonus (3%) = 8%
        expect($bonusRate)->toBe(8.0);
    });

    it('can calculate profit distribution for all users', function () {
        // Create multiple users with investments
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Investment::factory()->create([
            'user_id' => $user1->id,
            'investment_tier_id' => $this->basicTier->id,
            'amount' => 1000,
        ]);

        Investment::factory()->create([
            'user_id' => $user2->id,
            'investment_tier_id' => $this->starterTier->id,
            'amount' => 2000,
        ]);

        $distributions = $this->service->calculateProfitDistributionForAllUsers();

        expect($distributions)->toHaveCount(2);
        expect($distributions->where('user_id', $user1->id)->first()['profit_share'])->toBe(30.0);
        expect($distributions->where('user_id', $user2->id)->first()['profit_share'])->toBe(100.0);
    });

    it('can calculate compound interest projections', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->basicTier->id,
            'amount' => 1000,
        ]);

        $projection = $this->service->calculateCompoundInterestProjection(
            $investment,
            24, // 24 months
            true // compound monthly
        );

        expect($projection)->toHaveKeys([
            'final_amount',
            'total_interest',
            'monthly_breakdown',
        ]);

        expect($projection['final_amount'])->toBeGreaterThan(1000);
        expect($projection['total_interest'])->toBeGreaterThan(0);
        expect($projection['monthly_breakdown'])->toHaveCount(24);
    });

    it('can calculate profit share with lock-in period consideration', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->basicTier->id,
            'amount' => 1000,
            'created_at' => now()->subMonths(6), // 6 months old
        ]);

        $profitShare = $this->service->calculateProfitShareWithLockIn($investment);

        // Should be prorated for 6 months: 1000 * 3% * (6/12) = 15
        expect($profitShare)->toBe(15.0);
    });

    it('can calculate bonus pool allocation', function () {
        $totalFundProfit = 100000;
        $bonusPercentage = 10;

        $bonusPool = $this->service->calculateBonusPoolAllocation(
            $totalFundProfit,
            $bonusPercentage
        );

        expect($bonusPool)->toBe(10000.0);
    });

    it('can calculate individual investor bonus share', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 2000,
        ]);

        // Create total pool of 10,000
        Investment::factory()->count(4)->create([
            'amount' => 2000,
        ]);

        $bonusPool = 5000;
        $bonusShare = $this->service->calculateInvestorBonusShare(
            $investment,
            $bonusPool
        );

        // User has 20% of pool (2000/10000), so gets 20% of bonus pool = 1000
        expect($bonusShare)->toBe(1000.0);
    });

    it('can calculate profit with penalty deductions', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->basicTier->id,
            'amount' => 1000,
            'created_at' => now()->subMonths(3), // Early withdrawal scenario
        ]);

        $grossProfit = 30; // 3% annual profit
        $penaltyPercentage = 50; // 50% penalty for early withdrawal

        $netProfit = $this->service->calculateProfitWithPenalty(
            $grossProfit,
            $penaltyPercentage
        );

        expect($netProfit)->toBe(15.0); // 30 - (30 * 50%) = 15
    });

    it('can calculate tier-based commission rates', function () {
        $referrerInvestment = Investment::factory()->create([
            'investment_tier_id' => $this->starterTier->id,
            'amount' => 1000,
        ]);

        $referredInvestment = Investment::factory()->create([
            'amount' => 500,
        ]);

        $commissionRate = $this->service->calculateTierBasedCommissionRate(
            $referrerInvestment,
            1 // Level 1 referral
        );

        // Starter tier level 1 commission rate should be 7%
        expect($commissionRate)->toBe(7.0);
    });

    it('handles zero amounts gracefully', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->basicTier->id,
            'amount' => 0,
        ]);

        $profitShare = $this->service->calculateAnnualProfitShare($investment);
        expect($profitShare)->toBe(0.0);

        $bonus = $this->service->calculateQuarterlyBonus($investment, 10000, 10);
        expect($bonus)->toBe(0.0);
    });

    it('can calculate profit distribution history', function () {
        ProfitDistribution::factory()->create([
            'period_type' => 'annual',
            'total_profit' => 50000,
            'distribution_percentage' => 100,
            'period_start' => now()->subYear()->startOfYear(),
            'period_end' => now()->subYear()->endOfYear(),
        ]);

        ProfitDistribution::factory()->create([
            'period_type' => 'quarterly',
            'total_profit' => 15000,
            'distribution_percentage' => 80,
            'period_start' => now()->subQuarter()->startOfQuarter(),
            'period_end' => now()->subQuarter()->endOfQuarter(),
        ]);

        $history = $this->service->getProfitDistributionHistory(12);

        expect($history)->toHaveCount(2);
        expect($history->where('period_type', 'annual')->first()['total_profit'])->toBe(50000.0);
        expect($history->where('period_type', 'quarterly')->first()['total_profit'])->toBe(15000.0);
    });
});