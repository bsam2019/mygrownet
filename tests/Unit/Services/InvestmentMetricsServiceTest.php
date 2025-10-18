<?php

use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ProfitShare;
use App\Models\User;
use App\Services\InvestmentMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new InvestmentMetricsService();
    
    $this->tier = InvestmentTier::factory()->create([
        'name' => 'Basic',
        'minimum_amount' => 500,
        'fixed_profit_rate' => 3.0,
    ]);
    
    $this->user = User::factory()->create();
});

describe('InvestmentMetricsService', function () {
    it('can calculate total investment pool', function () {
        Investment::factory()->count(3)->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'status' => 'active',
        ]);

        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 500,
            'status' => 'inactive',
        ]);

        $totalPool = $this->service->getTotalInvestmentPool();

        expect($totalPool)->toBe(3000.0); // Only active investments
    });

    it('can calculate user pool percentage', function () {
        // Create total pool of 10,000
        Investment::factory()->count(9)->create([
            'amount' => 1000,
            'status' => 'active',
        ]);

        // User investment of 1,000
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000,
            'status' => 'active',
        ]);

        $percentage = $this->service->getUserPoolPercentage($this->user);

        expect($percentage)->toBe(10.0); // 1000/10000 * 100
    });

    it('can calculate investment growth rate', function () {
        // Create investments over time
        Investment::factory()->create([
            'amount' => 1000,
            'created_at' => now()->subDays(30),
        ]);

        Investment::factory()->create([
            'amount' => 1500,
            'created_at' => now()->subDays(15),
        ]);

        Investment::factory()->create([
            'amount' => 2000,
            'created_at' => now()->subDays(5),
        ]);

        $growthRate = $this->service->getInvestmentGrowthRate(30);

        expect($growthRate)->toBeFloat();
        expect($growthRate)->toBeGreaterThan(0);
    });

    it('can get investment distribution by tier', function () {
        $starterTier = InvestmentTier::factory()->create([
            'name' => 'Starter',
            'minimum_amount' => 1000,
        ]);

        Investment::factory()->count(2)->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 500,
        ]);

        Investment::factory()->count(3)->create([
            'investment_tier_id' => $starterTier->id,
            'amount' => 1000,
        ]);

        $distribution = $this->service->getInvestmentDistributionByTier();

        expect($distribution)->toHaveCount(2);
        
        $basicTierData = $distribution->where('tier_name', 'Basic')->first();
        expect($basicTierData['count'])->toBe(2);
        expect($basicTierData['total_amount'])->toBe(1000.0);
        expect($basicTierData['percentage'])->toBe(40.0); // 2/5 * 100
    });

    it('can calculate average investment amount', function () {
        Investment::factory()->create(['amount' => 500]);
        Investment::factory()->create(['amount' => 1000]);
        Investment::factory()->create(['amount' => 1500]);

        $average = $this->service->getAverageInvestmentAmount();

        expect($average)->toBe(1000.0);
    });

    it('can get investment performance metrics', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000,
            'created_at' => now()->subMonths(6),
        ]);

        ProfitShare::factory()->create([
            'user_id' => $this->user->id,
            'investment_id' => $investment->id,
            'amount' => 150,
        ]);

        $metrics = $this->service->getInvestmentPerformanceMetrics($investment);

        expect($metrics)->toHaveKeys([
            'roi_percentage',
            'monthly_return',
            'total_returns',
            'investment_age_months',
        ]);

        expect($metrics['roi_percentage'])->toBe(15.0);
        expect($metrics['total_returns'])->toBe(150.0);
        expect($metrics['investment_age_months'])->toBe(6);
    });

    it('can get top performing investments', function () {
        $investment1 = Investment::factory()->create([
            'amount' => 1000,
            'created_at' => now()->subMonths(12),
        ]);

        $investment2 = Investment::factory()->create([
            'amount' => 2000,
            'created_at' => now()->subMonths(6),
        ]);

        // Create profit shares
        ProfitShare::factory()->create([
            'investment_id' => $investment1->id,
            'amount' => 300, // 30% ROI
        ]);

        ProfitShare::factory()->create([
            'investment_id' => $investment2->id,
            'amount' => 200, // 10% ROI
        ]);

        $topPerformers = $this->service->getTopPerformingInvestments(5);

        expect($topPerformers)->toHaveCount(2);
        expect($topPerformers->first()['roi_percentage'])->toBe(30.0);
        expect($topPerformers->last()['roi_percentage'])->toBe(10.0);
    });

    it('can calculate monthly investment trends', function () {
        // Create investments across different months
        Investment::factory()->create([
            'amount' => 1000,
            'created_at' => now()->subMonths(2)->startOfMonth(),
        ]);

        Investment::factory()->count(2)->create([
            'amount' => 1500,
            'created_at' => now()->subMonth()->startOfMonth(),
        ]);

        Investment::factory()->count(3)->create([
            'amount' => 2000,
            'created_at' => now()->startOfMonth(),
        ]);

        $trends = $this->service->getMonthlyInvestmentTrends(3);

        expect($trends)->toHaveCount(3);
        expect($trends->first()['total_amount'])->toBe(6000.0); // Current month
        expect($trends->first()['investment_count'])->toBe(3);
    });

    it('can get investment maturity analysis', function () {
        Investment::factory()->create([
            'created_at' => now()->subMonths(18), // Mature
            'amount' => 1000,
        ]);

        Investment::factory()->create([
            'created_at' => now()->subMonths(6), // Mid-term
            'amount' => 1500,
        ]);

        Investment::factory()->create([
            'created_at' => now()->subMonths(2), // New
            'amount' => 2000,
        ]);

        $analysis = $this->service->getInvestmentMaturityAnalysis();

        expect($analysis)->toHaveKeys([
            'new_investments', // < 6 months
            'mid_term_investments', // 6-12 months
            'mature_investments', // > 12 months
        ]);

        expect($analysis['new_investments']['count'])->toBe(1);
        expect($analysis['mid_term_investments']['count'])->toBe(1);
        expect($analysis['mature_investments']['count'])->toBe(1);
    });

    it('handles empty investment pool gracefully', function () {
        $totalPool = $this->service->getTotalInvestmentPool();
        expect($totalPool)->toBe(0.0);

        $userPercentage = $this->service->getUserPoolPercentage($this->user);
        expect($userPercentage)->toBe(0.0);

        $average = $this->service->getAverageInvestmentAmount();
        expect($average)->toBe(0.0);
    });

    it('can calculate compound growth projections', function () {
        $investment = Investment::factory()->create([
            'amount' => 1000,
            'investment_tier_id' => $this->tier->id,
        ]);

        $projection = $this->service->calculateCompoundGrowthProjection(
            $investment,
            12 // months
        );

        expect($projection)->toHaveKeys([
            'projected_value',
            'total_growth',
            'monthly_breakdown',
        ]);

        expect($projection['projected_value'])->toBeGreaterThan(1000);
        expect($projection['total_growth'])->toBeGreaterThan(0);
        expect($projection['monthly_breakdown'])->toBeArray();
    });
});