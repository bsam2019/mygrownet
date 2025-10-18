<?php

use App\Models\Commission;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\MatrixPosition;
use App\Models\ProfitShare;
use App\Models\Referral;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new DashboardService();
    
    $this->tier = InvestmentTier::factory()->create([
        'name' => 'Basic',
        'minimum_amount' => 500,
        'fixed_profit_rate' => 3.0,
    ]);
    
    $this->user = User::factory()->create();
});

describe('DashboardService', function () {
    it('can get user dashboard overview', function () {
        // Create user investment
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'status' => 'active',
        ]);

        // Create profit shares
        ProfitShare::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
        ]);

        // Create commissions
        Commission::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 50,
        ]);

        $overview = $this->service->getDashboardOverview($this->user);

        expect($overview)->toHaveKeys([
            'total_investment',
            'current_tier',
            'total_profit_earned',
            'total_commission_earned',
            'total_earnings',
            'active_investments_count',
            'referral_count',
        ]);

        expect($overview['total_investment'])->toBe(1000.0);
        expect($overview['current_tier'])->toBe('Basic');
        expect($overview['total_profit_earned'])->toBe(100.0);
        expect($overview['total_commission_earned'])->toBe(50.0);
        expect($overview['total_earnings'])->toBe(150.0);
    });

    it('can get investment performance metrics', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'created_at' => now()->subMonths(6),
        ]);

        ProfitShare::factory()->create([
            'user_id' => $this->user->id,
            'investment_id' => $investment->id,
            'amount' => 150,
        ]);

        $performance = $this->service->getInvestmentPerformance($this->user);

        expect($performance)->toHaveKeys([
            'total_invested',
            'total_returns',
            'roi_percentage',
            'monthly_average',
            'performance_trend',
        ]);

        expect($performance['total_invested'])->toBe(1000.0);
        expect($performance['total_returns'])->toBe(150.0);
        expect($performance['roi_percentage'])->toBe(15.0);
    });

    it('can get referral statistics', function () {
        $referredUser1 = User::factory()->create();
        $referredUser2 = User::factory()->create();

        Referral::factory()->create([
            'referrer_id' => $this->user->id,
            'referred_id' => $referredUser1->id,
            'level' => 1,
        ]);

        Referral::factory()->create([
            'referrer_id' => $this->user->id,
            'referred_id' => $referredUser2->id,
            'level' => 1,
        ]);

        Commission::factory()->create([
            'user_id' => $this->user->id,
            'referral_id' => $referredUser1->id,
            'amount' => 75,
        ]);

        $stats = $this->service->getReferralStatistics($this->user);

        expect($stats)->toHaveKeys([
            'total_referrals',
            'direct_referrals',
            'total_commission_earned',
            'active_referrals',
            'referral_levels',
        ]);

        expect($stats['total_referrals'])->toBe(2);
        expect($stats['direct_referrals'])->toBe(2);
        expect($stats['total_commission_earned'])->toBe(75.0);
    });

    it('can get matrix position data', function () {
        MatrixPosition::factory()->create([
            'user_id' => $this->user->id,
            'level' => 1,
            'position' => 1,
            'sponsor_id' => null,
        ]);

        $matrixData = $this->service->getMatrixPosition($this->user);

        expect($matrixData)->toHaveKeys([
            'current_position',
            'matrix_level',
            'downline_count',
            'matrix_earnings',
            'spillover_received',
        ]);

        expect($matrixData['matrix_level'])->toBe(1);
        expect($matrixData['current_position'])->toBe(1);
    });

    it('can get withdrawal eligibility', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'created_at' => now()->subMonths(6), // 6 months old
        ]);

        $eligibility = $this->service->getWithdrawalEligibility($this->user);

        expect($eligibility)->toHaveKeys([
            'can_withdraw',
            'lock_period_remaining',
            'available_amount',
            'penalty_amount',
            'net_withdrawal_amount',
        ]);

        expect($eligibility['can_withdraw'])->toBeBool();
        expect($eligibility['lock_period_remaining'])->toBeInt();
    });

    it('can get recent activities', function () {
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

        $activities = $this->service->getRecentActivities($this->user, 10);

        expect($activities)->toHaveCount(3);
        expect($activities->first()['type'])->toBe('commission');
        expect($activities->last()['type'])->toBe('investment');
    });

    it('can get earnings breakdown', function () {
        ProfitShare::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
            'type' => 'annual',
        ]);

        ProfitShare::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 50,
            'type' => 'quarterly',
        ]);

        Commission::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 75,
            'type' => 'referral',
        ]);

        $breakdown = $this->service->getEarningsBreakdown($this->user);

        expect($breakdown)->toHaveKeys([
            'profit_shares',
            'referral_commissions',
            'matrix_bonuses',
            'reinvestment_bonuses',
            'total_earnings',
        ]);

        expect($breakdown['profit_shares'])->toBe(150.0);
        expect($breakdown['referral_commissions'])->toBe(75.0);
        expect($breakdown['total_earnings'])->toBe(225.0);
    });

    it('handles users with no data gracefully', function () {
        $newUser = User::factory()->create();

        $overview = $this->service->getDashboardOverview($newUser);

        expect($overview['total_investment'])->toBe(0.0);
        expect($overview['total_profit_earned'])->toBe(0.0);
        expect($overview['total_commission_earned'])->toBe(0.0);
        expect($overview['total_earnings'])->toBe(0.0);
        expect($overview['active_investments_count'])->toBe(0);
        expect($overview['referral_count'])->toBe(0);
    });

    it('can get tier upgrade progress', function () {
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 750, // Between Basic (500) and next tier
        ]);

        $nextTier = InvestmentTier::factory()->create([
            'name' => 'Starter',
            'minimum_amount' => 1000,
        ]);

        $progress = $this->service->getTierUpgradeProgress($this->user);

        expect($progress)->toHaveKeys([
            'current_tier',
            'next_tier',
            'current_amount',
            'required_amount',
            'progress_percentage',
            'amount_needed',
        ]);

        expect($progress['current_tier'])->toBe('Basic');
        expect($progress['current_amount'])->toBe(750.0);
        expect($progress['progress_percentage'])->toBeFloat();
    });
});