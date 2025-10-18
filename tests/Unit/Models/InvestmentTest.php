<?php

use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ProfitShare;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tier = InvestmentTier::factory()->create([
        'name' => 'Basic',
        'minimum_amount' => 500,
        'fixed_profit_rate' => 3.0,
    ]);
    
    $this->user = User::factory()->create();
});

describe('Investment Model', function () {
    it('belongs to a user', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
        ]);

        expect($investment->user)->toBeInstanceOf(User::class);
        expect($investment->user->id)->toBe($this->user->id);
    });

    it('belongs to an investment tier', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
        ]);

        expect($investment->investmentTier)->toBeInstanceOf(InvestmentTier::class);
        expect($investment->investmentTier->id)->toBe($this->tier->id);
    });

    it('has many profit shares', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
        ]);

        ProfitShare::factory()->count(3)->create([
            'investment_id' => $investment->id,
            'user_id' => $this->user->id,
        ]);

        expect($investment->profitShares)->toHaveCount(3);
        expect($investment->profitShares->first())->toBeInstanceOf(ProfitShare::class);
    });

    it('can calculate current value with profits', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
        ]);

        ProfitShare::factory()->create([
            'investment_id' => $investment->id,
            'user_id' => $this->user->id,
            'amount' => 150,
        ]);

        ProfitShare::factory()->create([
            'investment_id' => $investment->id,
            'user_id' => $this->user->id,
            'amount' => 100,
        ]);

        $currentValue = $investment->getCurrentValue();

        expect($currentValue)->toBe(1250.0); // 1000 + 150 + 100
    });

    it('can calculate ROI percentage', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
        ]);

        ProfitShare::factory()->create([
            'investment_id' => $investment->id,
            'user_id' => $this->user->id,
            'amount' => 200,
        ]);

        $roi = $investment->getROIPercentage();

        expect($roi)->toBe(20.0); // 200/1000 * 100
    });

    it('can determine if investment is in lock-in period', function () {
        $recentInvestment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'created_at' => now()->subMonths(6),
        ]);

        $oldInvestment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'created_at' => now()->subMonths(15),
        ]);

        expect($recentInvestment->isInLockInPeriod())->toBeTrue();
        expect($oldInvestment->isInLockInPeriod())->toBeFalse();
    });

    it('can calculate remaining lock-in days', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'created_at' => now()->subMonths(6),
        ]);

        $remainingDays = $investment->getRemainingLockInDays();

        expect($remainingDays)->toBeInt();
        expect($remainingDays)->toBeGreaterThan(0);
        expect($remainingDays)->toBeLessThanOrEqual(365);
    });

    it('can calculate investment age in months', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'created_at' => now()->subMonths(8),
        ]);

        $ageInMonths = $investment->getAgeInMonths();

        expect($ageInMonths)->toBe(8);
    });

    it('can determine if eligible for tier upgrade', function () {
        $higherTier = InvestmentTier::factory()->create([
            'name' => 'Starter',
            'minimum_amount' => 1000,
        ]);

        $eligibleInvestment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1200, // Above starter tier minimum
        ]);

        $ineligibleInvestment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 800, // Below starter tier minimum
        ]);

        expect($eligibleInvestment->isEligibleForTierUpgrade($higherTier))->toBeTrue();
        expect($ineligibleInvestment->isEligibleForTierUpgrade($higherTier))->toBeFalse();
    });

    it('can calculate expected annual profit', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
        ]);

        $expectedProfit = $investment->getExpectedAnnualProfit();

        expect($expectedProfit)->toBe(30.0); // 1000 * 3%
    });

    it('can calculate prorated profit for partial year', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'created_at' => now()->subMonths(6),
        ]);

        $proratedProfit = $investment->getProratedAnnualProfit();

        expect($proratedProfit)->toBe(15.0); // 1000 * 3% * (6/12)
    });

    it('can determine if reinvestment', function () {
        $regularInvestment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'is_reinvestment' => false,
        ]);

        $reinvestment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'is_reinvestment' => true,
        ]);

        expect($regularInvestment->isReinvestment())->toBeFalse();
        expect($reinvestment->isReinvestment())->toBeTrue();
    });

    it('can get total profit earned', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
        ]);

        ProfitShare::factory()->create([
            'investment_id' => $investment->id,
            'user_id' => $this->user->id,
            'amount' => 100,
            'type' => 'annual',
        ]);

        ProfitShare::factory()->create([
            'investment_id' => $investment->id,
            'user_id' => $this->user->id,
            'amount' => 50,
            'type' => 'quarterly',
        ]);

        $totalProfit = $investment->getTotalProfitEarned();

        expect($totalProfit)->toBe(150.0);
    });

    it('can scope active investments', function () {
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'active',
        ]);

        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'inactive',
        ]);

        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'pending',
        ]);

        $activeInvestments = Investment::active()->get();

        expect($activeInvestments)->toHaveCount(1);
        expect($activeInvestments->first()->status)->toBe('active');
    });

    it('can scope investments by tier', function () {
        $starterTier = InvestmentTier::factory()->create([
            'name' => 'Starter',
            'minimum_amount' => 1000,
        ]);

        Investment::factory()->count(2)->create([
            'investment_tier_id' => $this->tier->id,
        ]);

        Investment::factory()->count(3)->create([
            'investment_tier_id' => $starterTier->id,
        ]);

        $basicInvestments = Investment::byTier($this->tier)->get();
        $starterInvestments = Investment::byTier($starterTier)->get();

        expect($basicInvestments)->toHaveCount(2);
        expect($starterInvestments)->toHaveCount(3);
    });

    it('can scope investments by amount range', function () {
        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 500,
        ]);

        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
        ]);

        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 1500,
        ]);

        $investmentsInRange = Investment::amountBetween(750, 1250)->get();

        expect($investmentsInRange)->toHaveCount(1);
        expect($investmentsInRange->first()->amount)->toBe(1000.0);
    });

    it('can calculate withdrawal penalty', function () {
        $earlyInvestment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'created_at' => now()->subMonths(3),
        ]);

        $matureInvestment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'created_at' => now()->subMonths(15),
        ]);

        $earlyPenalty = $earlyInvestment->calculateWithdrawalPenalty();
        $maturePenalty = $matureInvestment->calculateWithdrawalPenalty();

        expect($earlyPenalty)->toBeGreaterThan(0);
        expect($maturePenalty)->toBe(0.0);
    });

    it('handles zero amounts gracefully', function () {
        $zeroInvestment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 0,
        ]);

        expect($zeroInvestment->getCurrentValue())->toBe(0.0);
        expect($zeroInvestment->getROIPercentage())->toBe(0.0);
        expect($zeroInvestment->getExpectedAnnualProfit())->toBe(0.0);
    });
});