<?php

use App\Models\Commission;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\MatrixPosition;
use App\Models\ProfitShare;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create minimal data without factories to avoid memory issues
    $this->tier = InvestmentTier::create([
        'name' => 'Basic',
        'minimum_investment' => 500,
        'fixed_profit_rate' => 3.0,
        'direct_referral_rate' => 5.0,
        'level2_referral_rate' => 0.0,
        'level3_referral_rate' => 0.0,
        'reinvestment_bonus_rate' => 0.0,
        'order' => 1
    ]);
    
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'current_investment_tier_id' => $this->tier->id
    ]);
});

describe('User Model', function () {
    it('has many investments', function () {
        // Create investments directly without factory
        Investment::create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'status' => 'active',
            'invested_at' => now()
        ]);
        
        Investment::create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1500,
            'status' => 'active',
            'invested_at' => now()
        ]);

        expect($this->user->investments)->toHaveCount(2);
        expect($this->user->investments->first())->toBeInstanceOf(Investment::class);
    });

    it('has many profit shares', function () {
        // Create profit shares directly without factory
        ProfitShare::create([
            'user_id' => $this->user->id,
            'amount' => 100.00,
            'distribution_date' => now(),
            'status' => 'paid'
        ]);

        expect($this->user->profitShares)->toHaveCount(1);
        expect($this->user->profitShares->first())->toBeInstanceOf(ProfitShare::class);
    });

    it('has many commissions', function () {
        // Create commission directly without factory
        Commission::create([
            'user_id' => $this->user->id,
            'referral_user_id' => $this->user->id,
            'amount' => 50.00,
            'level' => 1,
            'type' => 'referral',
            'status' => 'paid'
        ]);

        expect($this->user->commissions)->toHaveCount(1);
        expect($this->user->commissions->first())->toBeInstanceOf(Commission::class);
    });

    it('has many referrals made', function () {
        $referredUsers = User::factory()->count(3)->create();
        
        foreach ($referredUsers as $referredUser) {
            Referral::factory()->create([
                'referrer_id' => $this->user->id,
                'referred_id' => $referredUser->id,
            ]);
        }

        expect($this->user->referralsMade)->toHaveCount(3);
        expect($this->user->referralsMade->first())->toBeInstanceOf(Referral::class);
    });

    it('has matrix position', function () {
        MatrixPosition::factory()->create([
            'user_id' => $this->user->id,
            'level' => 1,
            'position' => 1,
        ]);

        expect($this->user->matrixPosition)->toBeInstanceOf(MatrixPosition::class);
        expect($this->user->matrixPosition->level)->toBe(1);
    });

    it('can calculate total investment amount', function () {
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'status' => 'active',
        ]);

        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1500,
            'status' => 'active',
        ]);

        // Inactive investment should not be counted
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 2000,
            'status' => 'inactive',
        ]);

        $totalAmount = $this->user->getTotalInvestmentAmount();

        expect($totalAmount)->toBe(2500.0);
    });

    it('can calculate total profit earned', function () {
        ProfitShare::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 150,
            'type' => 'annual',
        ]);

        ProfitShare::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 75,
            'type' => 'quarterly',
        ]);

        $totalProfit = $this->user->getTotalProfitEarned();

        expect($totalProfit)->toBe(225.0);
    });

    it('can calculate total commission earned', function () {
        Commission::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
            'type' => 'referral',
        ]);

        Commission::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 50,
            'type' => 'matrix',
        ]);

        $totalCommission = $this->user->getTotalCommissionEarned();

        expect($totalCommission)->toBe(150.0);
    });

    it('can calculate total earnings', function () {
        ProfitShare::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 200,
        ]);

        Commission::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
        ]);

        $totalEarnings = $this->user->getTotalEarnings();

        expect($totalEarnings)->toBe(300.0);
    });

    it('can get current investment tier', function () {
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'status' => 'active',
        ]);

        $currentTier = $this->user->getCurrentTier();

        expect($currentTier)->toBeInstanceOf(InvestmentTier::class);
        expect($currentTier->id)->toBe($this->tier->id);
    });

    it('can generate unique referral code', function () {
        $referralCode = $this->user->generateReferralCode();

        expect($referralCode)->toBeString();
        expect(strlen($referralCode))->toBe(8);
        expect($this->user->referral_code)->toBe($referralCode);
    });

    it('can get direct referrals count', function () {
        $referredUsers = User::factory()->count(3)->create();
        
        foreach ($referredUsers as $referredUser) {
            Referral::factory()->create([
                'referrer_id' => $this->user->id,
                'referred_id' => $referredUser->id,
                'level' => 1,
            ]);
        }

        // Add indirect referral (should not be counted)
        $indirectUser = User::factory()->create();
        Referral::factory()->create([
            'referrer_id' => $this->user->id,
            'referred_id' => $indirectUser->id,
            'level' => 2,
        ]);

        $directReferralsCount = $this->user->getDirectReferralsCount();

        expect($directReferralsCount)->toBe(3);
    });

    it('can get total referrals count', function () {
        $referredUsers = User::factory()->count(5)->create();
        
        foreach ($referredUsers as $index => $referredUser) {
            Referral::factory()->create([
                'referrer_id' => $this->user->id,
                'referred_id' => $referredUser->id,
                'level' => ($index % 3) + 1, // Mix of levels 1, 2, 3
            ]);
        }

        $totalReferralsCount = $this->user->getTotalReferralsCount();

        expect($totalReferralsCount)->toBe(5);
    });

    it('can determine if eligible for tier upgrade', function () {
        $higherTier = InvestmentTier::factory()->create([
            'name' => 'Starter',
            'minimum_amount' => 1000,
        ]);

        // User with enough investment
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1200,
            'status' => 'active',
        ]);

        expect($this->user->isEligibleForTierUpgrade($higherTier))->toBeTrue();

        // User without enough investment
        $poorUser = User::factory()->create();
        Investment::factory()->create([
            'user_id' => $poorUser->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 800,
            'status' => 'active',
        ]);

        expect($poorUser->isEligibleForTierUpgrade($higherTier))->toBeFalse();
    });

    it('can get investment pool percentage', function () {
        // Create total pool of 10,000
        Investment::factory()->count(9)->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'status' => 'active',
        ]);

        // User investment of 1,000
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'status' => 'active',
        ]);

        $poolPercentage = $this->user->getInvestmentPoolPercentage();

        expect($poolPercentage)->toBe(10.0);
    });

    it('can check if has active investments', function () {
        $userWithActiveInvestment = User::factory()->create();
        Investment::factory()->create([
            'user_id' => $userWithActiveInvestment->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'active',
        ]);

        $userWithoutActiveInvestment = User::factory()->create();
        Investment::factory()->create([
            'user_id' => $userWithoutActiveInvestment->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'inactive',
        ]);

        expect($userWithActiveInvestment->hasActiveInvestments())->toBeTrue();
        expect($userWithoutActiveInvestment->hasActiveInvestments())->toBeFalse();
    });

    it('can get matrix downline count', function () {
        // Create matrix position for user
        MatrixPosition::factory()->create([
            'user_id' => $this->user->id,
            'level' => 1,
            'position' => 1,
        ]);

        // Create downline positions
        $downlineUsers = User::factory()->count(5)->create();
        
        foreach ($downlineUsers as $index => $user) {
            MatrixPosition::factory()->create([
                'user_id' => $user->id,
                'level' => 2,
                'position' => $index + 1,
                'sponsor_id' => $this->user->id,
            ]);
        }

        $downlineCount = $this->user->getMatrixDownlineCount();

        expect($downlineCount)->toBe(5);
    });

    it('can get recent activity', function () {
        // Create various activities
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(2),
        ]);

        ProfitShare::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDay(),
        ]);

        Commission::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subHours(12),
        ]);

        $recentActivity = $this->user->getRecentActivity(10);

        expect($recentActivity)->toHaveCount(3);
        expect($recentActivity->first()['type'])->toBe('commission');
        expect($recentActivity->last()['type'])->toBe('investment');
    });

    it('can calculate ROI percentage', function () {
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'status' => 'active',
        ]);

        ProfitShare::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 200,
        ]);

        $roi = $this->user->getROIPercentage();

        expect($roi)->toBe(20.0); // 200/1000 * 100
    });

    it('can get withdrawal eligibility', function () {
        $recentInvestment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'created_at' => now()->subMonths(6),
        ]);

        $eligibility = $this->user->getWithdrawalEligibility();

        expect($eligibility)->toHaveKeys([
            'can_withdraw',
            'lock_period_remaining',
            'available_amount',
            'penalty_amount',
        ]);

        expect($eligibility['can_withdraw'])->toBeBool();
        expect($eligibility['lock_period_remaining'])->toBeInt();
    });

    it('handles users with no data gracefully', function () {
        $newUser = User::factory()->create();

        expect($newUser->getTotalInvestmentAmount())->toBe(0.0);
        expect($newUser->getTotalProfitEarned())->toBe(0.0);
        expect($newUser->getTotalCommissionEarned())->toBe(0.0);
        expect($newUser->getTotalEarnings())->toBe(0.0);
        expect($newUser->getDirectReferralsCount())->toBe(0);
        expect($newUser->getTotalReferralsCount())->toBe(0);
        expect($newUser->hasActiveInvestments())->toBeFalse();
        expect($newUser->getMatrixDownlineCount())->toBe(0);
        expect($newUser->getROIPercentage())->toBe(0.0);
    });

    it('can scope users by tier', function () {
        $starterTier = InvestmentTier::factory()->create([
            'name' => 'Starter',
            'minimum_amount' => 1000,
        ]);

        $basicUsers = User::factory()->count(2)->create();
        $starterUsers = User::factory()->count(3)->create();

        foreach ($basicUsers as $user) {
            Investment::factory()->create([
                'user_id' => $user->id,
                'investment_tier_id' => $this->tier->id,
                'status' => 'active',
            ]);
        }

        foreach ($starterUsers as $user) {
            Investment::factory()->create([
                'user_id' => $user->id,
                'investment_tier_id' => $starterTier->id,
                'status' => 'active',
            ]);
        }

        $basicTierUsers = User::byTier($this->tier)->get();
        $starterTierUsers = User::byTier($starterTier)->get();

        expect($basicTierUsers)->toHaveCount(2);
        expect($starterTierUsers)->toHaveCount(3);
    });

    it('can scope active investors', function () {
        $activeInvestor = User::factory()->create();
        Investment::factory()->create([
            'user_id' => $activeInvestor->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'active',
        ]);

        $inactiveUser = User::factory()->create();
        Investment::factory()->create([
            'user_id' => $inactiveUser->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'inactive',
        ]);

        $userWithoutInvestment = User::factory()->create();

        $activeInvestors = User::activeInvestors()->get();

        expect($activeInvestors)->toHaveCount(1);
        expect($activeInvestors->first()->id)->toBe($activeInvestor->id);
    });
});