<?php

use App\Models\Commission;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\MatrixPosition;
use App\Models\Referral;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new ReferralService();
    
    $this->tier = InvestmentTier::factory()->create([
        'name' => 'Basic',
        'minimum_amount' => 500,
        'fixed_profit_rate' => 3.0,
        'direct_referral_rate' => 5.0,
    ]);
    
    $this->sponsor = User::factory()->create();
    $this->referredUser = User::factory()->create();
});

describe('ReferralService', function () {
    it('can create referral relationship', function () {
        $referral = $this->service->createReferral(
            $this->sponsor,
            $this->referredUser,
            1 // Level 1
        );

        expect($referral)->toBeInstanceOf(Referral::class);
        expect($referral->referrer_id)->toBe($this->sponsor->id);
        expect($referral->referred_id)->toBe($this->referredUser->id);
        expect($referral->level)->toBe(1);

        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $this->sponsor->id,
            'referred_id' => $this->referredUser->id,
            'level' => 1,
        ]);
    });

    it('can build referral tree structure', function () {
        // Create multi-level referral structure
        $level2User = User::factory()->create();
        $level3User = User::factory()->create();

        Referral::factory()->create([
            'referrer_id' => $this->sponsor->id,
            'referred_id' => $this->referredUser->id,
            'level' => 1,
        ]);

        Referral::factory()->create([
            'referrer_id' => $this->referredUser->id,
            'referred_id' => $level2User->id,
            'level' => 1,
        ]);

        Referral::factory()->create([
            'referrer_id' => $this->sponsor->id,
            'referred_id' => $level2User->id,
            'level' => 2,
        ]);

        $tree = $this->service->buildReferralTree($this->sponsor, 3);

        expect($tree)->toHaveKeys(['user', 'level', 'children']);
        expect($tree['user']['id'])->toBe($this->sponsor->id);
        expect($tree['children'])->toHaveCount(1);
        expect($tree['children'][0]['level'])->toBe(1);
    });

    it('can calculate multi-level commissions', function () {
        $sponsorInvestment = Investment::factory()->create([
            'user_id' => $this->sponsor->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
        ]);

        $referredInvestment = Investment::factory()->create([
            'user_id' => $this->referredUser->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 500,
        ]);

        Referral::factory()->create([
            'referrer_id' => $this->sponsor->id,
            'referred_id' => $this->referredUser->id,
            'level' => 1,
        ]);

        $commissions = $this->service->calculateMultiLevelCommissions($referredInvestment);

        expect($commissions)->toHaveCount(1);
        expect($commissions[0]['user_id'])->toBe($this->sponsor->id);
        expect($commissions[0]['amount'])->toBe(25.0); // 500 * 5% = 25
        expect($commissions[0]['level'])->toBe(1);
    });

    it('can find next available matrix position', function () {
        // Create matrix positions
        MatrixPosition::factory()->create([
            'user_id' => $this->sponsor->id,
            'level' => 1,
            'position' => 1,
            'sponsor_id' => null,
        ]);

        // Fill first two positions
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        MatrixPosition::factory()->create([
            'user_id' => $user1->id,
            'level' => 2,
            'position' => 1,
            'sponsor_id' => $this->sponsor->id,
        ]);

        MatrixPosition::factory()->create([
            'user_id' => $user2->id,
            'level' => 2,
            'position' => 2,
            'sponsor_id' => $this->sponsor->id,
        ]);

        $nextPosition = $this->service->findNextAvailableMatrixPosition($this->sponsor);

        expect($nextPosition)->toHaveKeys(['level', 'position', 'sponsor_id']);
        expect($nextPosition['level'])->toBe(2);
        expect($nextPosition['position'])->toBe(3);
        expect($nextPosition['sponsor_id'])->toBe($this->sponsor->id);
    });

    it('can handle matrix spillover', function () {
        // Fill sponsor's direct positions (3 positions)
        $directUsers = User::factory()->count(3)->create();
        
        foreach ($directUsers as $index => $user) {
            MatrixPosition::factory()->create([
                'user_id' => $user->id,
                'level' => 2,
                'position' => $index + 1,
                'sponsor_id' => $this->sponsor->id,
            ]);
        }

        $spilloverUser = User::factory()->create();
        
        $spilloverPosition = $this->service->processMatrixSpillover(
            $spilloverUser,
            $this->sponsor
        );

        expect($spilloverPosition)->not->toBeNull();
        expect($spilloverPosition['sponsor_id'])->not->toBe($this->sponsor->id);
    });

    it('can calculate matrix commissions', function () {
        $matrixPosition = MatrixPosition::factory()->create([
            'user_id' => $this->sponsor->id,
            'level' => 1,
            'position' => 1,
        ]);

        $investment = Investment::factory()->create([
            'user_id' => $this->referredUser->id,
            'amount' => 1000,
        ]);

        $commission = $this->service->calculateMatrixCommission(
            $matrixPosition,
            $investment
        );

        expect($commission)->toBeFloat();
        expect($commission)->toBeGreaterThan(0);
    });

    it('can get referral statistics', function () {
        // Create referrals at different levels
        $level1Users = User::factory()->count(2)->create();
        $level2Users = User::factory()->count(3)->create();

        foreach ($level1Users as $user) {
            Referral::factory()->create([
                'referrer_id' => $this->sponsor->id,
                'referred_id' => $user->id,
                'level' => 1,
            ]);
        }

        foreach ($level2Users as $user) {
            Referral::factory()->create([
                'referrer_id' => $this->sponsor->id,
                'referred_id' => $user->id,
                'level' => 2,
            ]);
        }

        // Create commissions
        Commission::factory()->count(3)->create([
            'user_id' => $this->sponsor->id,
            'amount' => 50,
        ]);

        $stats = $this->service->getReferralStatistics($this->sponsor);

        expect($stats)->toHaveKeys([
            'total_referrals',
            'direct_referrals',
            'level_2_referrals',
            'level_3_referrals',
            'total_commission_earned',
            'active_referrals',
        ]);

        expect($stats['total_referrals'])->toBe(5);
        expect($stats['direct_referrals'])->toBe(2);
        expect($stats['level_2_referrals'])->toBe(3);
        expect($stats['total_commission_earned'])->toBe(150.0);
    });

    it('can validate referral eligibility', function () {
        // Test valid referral
        $isEligible = $this->service->validateReferralEligibility(
            $this->sponsor,
            $this->referredUser
        );

        expect($isEligible)->toBeTrue();

        // Test self-referral (should be invalid)
        $isSelfReferralEligible = $this->service->validateReferralEligibility(
            $this->sponsor,
            $this->sponsor
        );

        expect($isSelfReferralEligible)->toBeFalse();
    });

    it('can process referral commission distribution', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->referredUser->id,
            'amount' => 1000,
        ]);

        Referral::factory()->create([
            'referrer_id' => $this->sponsor->id,
            'referred_id' => $this->referredUser->id,
            'level' => 1,
        ]);

        $this->service->processReferralCommissionDistribution($investment);

        $this->assertDatabaseHas('commissions', [
            'user_id' => $this->sponsor->id,
            'referral_id' => $this->referredUser->id,
            'type' => 'referral',
        ]);
    });

    it('can get matrix visualization data', function () {
        // Create matrix structure
        MatrixPosition::factory()->create([
            'user_id' => $this->sponsor->id,
            'level' => 1,
            'position' => 1,
        ]);

        $childUsers = User::factory()->count(3)->create();
        
        foreach ($childUsers as $index => $user) {
            MatrixPosition::factory()->create([
                'user_id' => $user->id,
                'level' => 2,
                'position' => $index + 1,
                'sponsor_id' => $this->sponsor->id,
            ]);
        }

        $matrixData = $this->service->getMatrixVisualizationData($this->sponsor);

        expect($matrixData)->toHaveKeys(['root', 'levels', 'total_positions']);
        expect($matrixData['root']['user_id'])->toBe($this->sponsor->id);
        expect($matrixData['levels'])->toHaveCount(2);
    });

    it('can calculate spillover benefits', function () {
        $spilloverUser = User::factory()->create();
        
        $spilloverBenefit = $this->service->calculateSpilloverBenefit(
            $this->sponsor,
            $spilloverUser,
            1000 // Investment amount
        );

        expect($spilloverBenefit)->toBeFloat();
        expect($spilloverBenefit)->toBeGreaterThanOrEqual(0);
    });

    it('handles empty referral networks gracefully', function () {
        $newUser = User::factory()->create();

        $tree = $this->service->buildReferralTree($newUser, 3);
        expect($tree['children'])->toBeEmpty();

        $stats = $this->service->getReferralStatistics($newUser);
        expect($stats['total_referrals'])->toBe(0);
        expect($stats['total_commission_earned'])->toBe(0.0);

        $matrixData = $this->service->getMatrixVisualizationData($newUser);
        expect($matrixData['total_positions'])->toBe(0);
    });

    it('can track referral performance over time', function () {
        // Create referrals over different time periods
        Referral::factory()->create([
            'referrer_id' => $this->sponsor->id,
            'referred_id' => $this->referredUser->id,
            'level' => 1,
            'created_at' => now()->subDays(30),
        ]);

        Commission::factory()->create([
            'user_id' => $this->sponsor->id,
            'amount' => 100,
            'created_at' => now()->subDays(25),
        ]);

        Commission::factory()->create([
            'user_id' => $this->sponsor->id,
            'amount' => 150,
            'created_at' => now()->subDays(10),
        ]);

        $performance = $this->service->getReferralPerformanceOverTime($this->sponsor, 30);

        expect($performance)->toHaveKeys(['total_commissions', 'commission_trend', 'referral_growth']);
        expect($performance['total_commissions'])->toBe(250.0);
        expect($performance['commission_trend'])->toBeArray();
    });
});