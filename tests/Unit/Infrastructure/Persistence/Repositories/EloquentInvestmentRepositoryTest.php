<?php

use App\Infrastructure\Persistence\Repositories\EloquentInvestmentRepository;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new EloquentInvestmentRepository();
    
    $this->tier = InvestmentTier::factory()->create([
        'name' => 'Basic',
        'minimum_amount' => 500,
        'fixed_profit_rate' => 3.0,
    ]);
    
    $this->user = User::factory()->create();
});

describe('EloquentInvestmentRepository', function () {
    it('can find active investments by user', function () {
        // Create active investments
        Investment::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'active',
            'amount' => 1000,
        ]);

        // Create inactive investment
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'inactive',
            'amount' => 500,
        ]);

        $activeInvestments = $this->repository->findActiveInvestmentsByUser($this->user);

        expect($activeInvestments)->toHaveCount(2);
        expect($activeInvestments->every(fn($investment) => $investment->status === 'active'))->toBeTrue();
        expect($activeInvestments->every(fn($investment) => $investment->user_id === $this->user->id))->toBeTrue();
    });

    it('can calculate total investment pool', function () {
        // Create active investments
        Investment::factory()->count(3)->create([
            'investment_tier_id' => $this->tier->id,
            'status' => 'active',
            'amount' => 1000,
        ]);

        // Create inactive investment (should not be included)
        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'status' => 'inactive',
            'amount' => 2000,
        ]);

        $totalPool = $this->repository->getTotalInvestmentPool();

        expect($totalPool)->toBe(3000.0);
    });

    it('can get investments by date range', function () {
        $startDate = now()->subDays(10);
        $endDate = now()->subDays(5);

        // Investment within range
        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'created_at' => now()->subDays(7),
        ]);

        // Investment outside range (too old)
        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 1500,
            'created_at' => now()->subDays(15),
        ]);

        // Investment outside range (too recent)
        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 2000,
            'created_at' => now()->subDays(2),
        ]);

        $investments = $this->repository->getInvestmentsByDateRange($startDate, $endDate);

        expect($investments)->toHaveCount(1);
        expect($investments->first()->amount)->toBe(1000.0);
    });

    it('can calculate user pool percentage', function () {
        // Create total pool of 10,000
        Investment::factory()->count(9)->create([
            'investment_tier_id' => $this->tier->id,
            'status' => 'active',
            'amount' => 1000,
        ]);

        // User investment of 1,000
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'active',
            'amount' => 1000,
        ]);

        $percentage = $this->repository->calculateUserPoolPercentage($this->user);

        expect($percentage)->toBe(10.0); // 1000/10000 * 100
    });

    it('can find investments by tier', function () {
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

        $basicInvestments = $this->repository->findInvestmentsByTier($this->tier);
        $starterInvestments = $this->repository->findInvestmentsByTier($starterTier);

        expect($basicInvestments)->toHaveCount(2);
        expect($starterInvestments)->toHaveCount(3);
    });

    it('can get user total investment amount', function () {
        Investment::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'active',
            'amount' => 1000,
        ]);

        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'active',
            'amount' => 500,
        ]);

        // Inactive investment should not be counted
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_tier_id' => $this->tier->id,
            'status' => 'inactive',
            'amount' => 2000,
        ]);

        $totalAmount = $this->repository->getUserTotalInvestmentAmount($this->user);

        expect($totalAmount)->toBe(2500.0);
    });

    it('can find investments with minimum amount', function () {
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

        $investmentsAbove1000 = $this->repository->findInvestmentsWithMinimumAmount(1000);

        expect($investmentsAbove1000)->toHaveCount(2);
        expect($investmentsAbove1000->every(fn($investment) => $investment->amount >= 1000))->toBeTrue();
    });

    it('can get investment statistics', function () {
        Investment::factory()->count(5)->create([
            'investment_tier_id' => $this->tier->id,
            'status' => 'active',
            'amount' => 1000,
        ]);

        Investment::factory()->count(2)->create([
            'investment_tier_id' => $this->tier->id,
            'status' => 'inactive',
            'amount' => 500,
        ]);

        $stats = $this->repository->getInvestmentStatistics();

        expect($stats)->toHaveKeys([
            'total_investments',
            'active_investments',
            'inactive_investments',
            'total_amount',
            'average_amount',
        ]);

        expect($stats['total_investments'])->toBe(7);
        expect($stats['active_investments'])->toBe(5);
        expect($stats['inactive_investments'])->toBe(2);
        expect($stats['total_amount'])->toBe(6000.0);
        expect($stats['average_amount'])->toBe(857.14); // 6000/7 rounded
    });

    it('can find recent investments', function () {
        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'created_at' => now()->subDays(2),
        ]);

        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 1500,
            'created_at' => now()->subDay(),
        ]);

        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 2000,
            'created_at' => now()->subHours(12),
        ]);

        // Old investment
        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 500,
            'created_at' => now()->subDays(10),
        ]);

        $recentInvestments = $this->repository->findRecentInvestments(3, 5); // Last 3 investments within 5 days

        expect($recentInvestments)->toHaveCount(3);
        expect($recentInvestments->first()->amount)->toBe(2000.0); // Most recent first
        expect($recentInvestments->last()->amount)->toBe(1000.0);
    });

    it('can get monthly investment trends', function () {
        // Create investments across different months
        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'created_at' => now()->subMonths(2)->startOfMonth(),
        ]);

        Investment::factory()->count(2)->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 1500,
            'created_at' => now()->subMonth()->startOfMonth(),
        ]);

        Investment::factory()->count(3)->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 2000,
            'created_at' => now()->startOfMonth(),
        ]);

        $trends = $this->repository->getMonthlyInvestmentTrends(3);

        expect($trends)->toHaveCount(3);
        expect($trends->first()['total_amount'])->toBe(6000.0); // Current month
        expect($trends->first()['investment_count'])->toBe(3);
    });

    it('handles empty results gracefully', function () {
        $newUser = User::factory()->create();

        $activeInvestments = $this->repository->findActiveInvestmentsByUser($newUser);
        expect($activeInvestments)->toHaveCount(0);

        $totalPool = $this->repository->getTotalInvestmentPool();
        expect($totalPool)->toBe(0.0);

        $userPercentage = $this->repository->calculateUserPoolPercentage($newUser);
        expect($userPercentage)->toBe(0.0);

        $userTotal = $this->repository->getUserTotalInvestmentAmount($newUser);
        expect($userTotal)->toBe(0.0);
    });

    it('can find investments by status', function () {
        Investment::factory()->count(3)->create([
            'investment_tier_id' => $this->tier->id,
            'status' => 'active',
            'amount' => 1000,
        ]);

        Investment::factory()->count(2)->create([
            'investment_tier_id' => $this->tier->id,
            'status' => 'pending',
            'amount' => 1500,
        ]);

        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'status' => 'cancelled',
            'amount' => 2000,
        ]);

        $activeInvestments = $this->repository->findInvestmentsByStatus('active');
        $pendingInvestments = $this->repository->findInvestmentsByStatus('pending');

        expect($activeInvestments)->toHaveCount(3);
        expect($pendingInvestments)->toHaveCount(2);
    });

    it('can get investment growth rate', function () {
        // Create investments over time
        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 1000,
            'created_at' => now()->subDays(30),
        ]);

        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 1500,
            'created_at' => now()->subDays(15),
        ]);

        Investment::factory()->create([
            'investment_tier_id' => $this->tier->id,
            'amount' => 2000,
            'created_at' => now()->subDays(5),
        ]);

        $growthRate = $this->repository->getInvestmentGrowthRate(30);

        expect($growthRate)->toBeFloat();
        expect($growthRate)->toBeGreaterThan(0);
    });
});