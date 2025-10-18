<?php

use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new AdminDashboardService();
    
    // Create test data
    $this->tier = InvestmentTier::factory()->create([
        'name' => 'Basic',
        'minimum_amount' => 500,
        'fixed_profit_rate' => 3.0,
    ]);
    
    $this->users = User::factory()->count(5)->create();
});

describe('AdminDashboardService', function () {
    it('can get system overview statistics', function () {
        // Create test investments
        Investment::factory()->count(3)->create([
            'user_id' => $this->users[0]->id,
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'status' => 'active',
        ]);

        // Create withdrawal requests
        WithdrawalRequest::factory()->count(2)->create([
            'user_id' => $this->users[1]->id,
            'status' => 'pending',
        ]);

        $overview = $this->service->getSystemOverview();

        expect($overview)->toHaveKeys([
            'total_users',
            'total_investments',
            'total_investment_amount',
            'pending_withdrawals',
            'active_investments',
        ]);

        expect($overview['total_users'])->toBe(5);
        expect($overview['total_investments'])->toBe(3);
        expect($overview['total_investment_amount'])->toBe(3000.0);
        expect($overview['pending_withdrawals'])->toBe(2);
        expect($overview['active_investments'])->toBe(3);
    });

    it('can get user growth metrics', function () {
        // Create users with different registration dates
        User::factory()->create(['created_at' => now()->subDays(30)]);
        User::factory()->create(['created_at' => now()->subDays(15)]);
        User::factory()->create(['created_at' => now()->subDays(5)]);

        $growth = $this->service->getUserGrowthMetrics(30);

        expect($growth)->toHaveKeys(['daily_registrations', 'total_growth']);
        expect($growth['total_growth'])->toBe(3);
        expect($growth['daily_registrations'])->toBeArray();
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
        expect($distribution->where('tier_name', 'Basic')->first()['count'])->toBe(2);
        expect($distribution->where('tier_name', 'Starter')->first()['count'])->toBe(3);
    });

    it('can get top investors', function () {
        Investment::factory()->create([
            'user_id' => $this->users[0]->id,
            'amount' => 5000,
        ]);

        Investment::factory()->create([
            'user_id' => $this->users[1]->id,
            'amount' => 3000,
        ]);

        Investment::factory()->create([
            'user_id' => $this->users[2]->id,
            'amount' => 1000,
        ]);

        $topInvestors = $this->service->getTopInvestors(2);

        expect($topInvestors)->toHaveCount(2);
        expect($topInvestors->first()['total_investment'])->toBe(5000.0);
        expect($topInvestors->last()['total_investment'])->toBe(3000.0);
    });

    it('can get recent activities', function () {
        Investment::factory()->count(3)->create([
            'created_at' => now()->subHours(2),
        ]);

        WithdrawalRequest::factory()->count(2)->create([
            'created_at' => now()->subHour(),
        ]);

        $activities = $this->service->getRecentActivities(10);

        expect($activities)->toHaveCount(5);
        expect($activities->first()['type'])->toBe('withdrawal_request');
        expect($activities->last()['type'])->toBe('investment');
    });

    it('can get withdrawal statistics', function () {
        WithdrawalRequest::factory()->create([
            'status' => 'pending',
            'amount' => 1000,
        ]);

        WithdrawalRequest::factory()->create([
            'status' => 'approved',
            'amount' => 2000,
        ]);

        WithdrawalRequest::factory()->create([
            'status' => 'rejected',
            'amount' => 500,
        ]);

        $stats = $this->service->getWithdrawalStatistics();

        expect($stats)->toHaveKeys([
            'pending_count',
            'pending_amount',
            'approved_count',
            'approved_amount',
            'rejected_count',
            'rejected_amount',
        ]);

        expect($stats['pending_count'])->toBe(1);
        expect($stats['pending_amount'])->toBe(1000.0);
        expect($stats['approved_count'])->toBe(1);
        expect($stats['approved_amount'])->toBe(2000.0);
    });

    it('can get revenue metrics', function () {
        Investment::factory()->count(3)->create([
            'amount' => 1000,
            'created_at' => now()->subDays(15),
        ]);

        Investment::factory()->count(2)->create([
            'amount' => 2000,
            'created_at' => now()->subDays(5),
        ]);

        $revenue = $this->service->getRevenueMetrics(30);

        expect($revenue)->toHaveKeys(['total_revenue', 'daily_revenue']);
        expect($revenue['total_revenue'])->toBe(7000.0);
        expect($revenue['daily_revenue'])->toBeArray();
    });

    it('handles empty data gracefully', function () {
        // Clear all data
        Investment::query()->delete();
        User::query()->delete();
        WithdrawalRequest::query()->delete();

        $overview = $this->service->getSystemOverview();

        expect($overview['total_users'])->toBe(0);
        expect($overview['total_investments'])->toBe(0);
        expect($overview['total_investment_amount'])->toBe(0.0);
        expect($overview['pending_withdrawals'])->toBe(0);
    });
});