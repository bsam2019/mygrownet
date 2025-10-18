<?php

namespace Tests\Unit\Infrastructure\Persistence\Repositories;

use App\Infrastructure\Persistence\Repositories\EloquentWithdrawalRepository;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\Investment;
use App\Models\InvestmentTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class EloquentWithdrawalRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentWithdrawalRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentWithdrawalRepository();
        $this->seedTestData();
    }

    private function seedTestData(): void
    {
        InvestmentTier::create([
            'name' => 'Basic',
            'minimum_investment' => 500,
            'fixed_profit_rate' => 3.00,
            'order' => 1,
        ]);
    }

    public function test_creates_withdrawal_request(): void
    {
        $user = User::factory()->create();
        
        $data = [
            'user_id' => $user->id,
            'amount' => 500,
            'type' => 'partial',
            'reason' => 'Emergency funds needed',
            'penalty_amount' => 50,
            'net_amount' => 450,
        ];

        $withdrawalRequest = $this->repository->create($data);

        $this->assertInstanceOf(WithdrawalRequest::class, $withdrawalRequest);
        $this->assertEquals($user->id, $withdrawalRequest->user_id);
        $this->assertEquals(500, $withdrawalRequest->amount);
        $this->assertEquals('partial', $withdrawalRequest->type);
        $this->assertEquals('pending', $withdrawalRequest->status);
    }

    public function test_finds_withdrawal_request_by_id(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
        ]);

        $found = $this->repository->findById($withdrawalRequest->id);

        $this->assertInstanceOf(WithdrawalRequest::class, $found);
        $this->assertEquals($withdrawalRequest->id, $found->id);
    }

    public function test_returns_null_for_non_existent_withdrawal(): void
    {
        $found = $this->repository->findById(999);

        $this->assertNull($found);
    }

    public function test_finds_withdrawals_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        WithdrawalRequest::factory()->count(3)->create(['user_id' => $user1->id]);
        WithdrawalRequest::factory()->count(2)->create(['user_id' => $user2->id]);

        $user1Withdrawals = $this->repository->findByUser($user1);

        $this->assertCount(3, $user1Withdrawals);
        $user1Withdrawals->each(function ($withdrawal) use ($user1) {
            $this->assertEquals($user1->id, $withdrawal->user_id);
        });
    }

    public function test_finds_withdrawals_by_status(): void
    {
        $user = User::factory()->create();

        WithdrawalRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        WithdrawalRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $pendingWithdrawals = $this->repository->findByStatus('pending');

        $this->assertCount(2, $pendingWithdrawals);
        $pendingWithdrawals->each(function ($withdrawal) {
            $this->assertEquals('pending', $withdrawal->status);
        });
    }

    public function test_finds_withdrawals_by_type(): void
    {
        $user = User::factory()->create();

        WithdrawalRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'type' => 'emergency',
        ]);

        WithdrawalRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'type' => 'partial',
        ]);

        $emergencyWithdrawals = $this->repository->findByType('emergency');

        $this->assertCount(2, $emergencyWithdrawals);
        $emergencyWithdrawals->each(function ($withdrawal) {
            $this->assertEquals('emergency', $withdrawal->type);
        });
    }

    public function test_finds_withdrawals_by_date_range(): void
    {
        $user = User::factory()->create();
        $startDate = Carbon::now()->subDays(10);
        $endDate = Carbon::now()->subDays(5);

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'requested_at' => $startDate->copy()->addDays(2),
        ]);

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'requested_at' => Carbon::now()->subDays(15), // Outside range
        ]);

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'requested_at' => Carbon::now()->subDays(2), // Outside range
        ]);

        $withdrawalsInRange = $this->repository->findByDateRange($startDate, $endDate);

        $this->assertCount(1, $withdrawalsInRange);
        $this->assertTrue($withdrawalsInRange->first()->requested_at->between($startDate, $endDate));
    }

    public function test_updates_withdrawal_request(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $updateData = [
            'status' => 'approved',
            'admin_notes' => 'Approved by admin',
            'approved_at' => now(),
        ];

        $updated = $this->repository->update($withdrawalRequest->id, $updateData);

        $this->assertTrue($updated);
        
        $withdrawalRequest->refresh();
        $this->assertEquals('approved', $withdrawalRequest->status);
        $this->assertEquals('Approved by admin', $withdrawalRequest->admin_notes);
        $this->assertNotNull($withdrawalRequest->approved_at);
    }

    public function test_deletes_withdrawal_request(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
        ]);

        $deleted = $this->repository->delete($withdrawalRequest->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('withdrawal_requests', [
            'id' => $withdrawalRequest->id,
        ]);
    }

    public function test_gets_pending_withdrawals(): void
    {
        $user = User::factory()->create();

        WithdrawalRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        WithdrawalRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $pendingWithdrawals = $this->repository->getPendingWithdrawals();

        $this->assertCount(3, $pendingWithdrawals);
        $pendingWithdrawals->each(function ($withdrawal) {
            $this->assertEquals('pending', $withdrawal->status);
        });
    }

    public function test_gets_user_withdrawal_statistics(): void
    {
        $user = User::factory()->create();

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'status' => 'processed',
        ]);

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 300,
            'status' => 'pending',
        ]);

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 200,
            'status' => 'rejected',
        ]);

        $stats = $this->repository->getUserWithdrawalStatistics($user->id);

        $this->assertIsArray($stats);
        $this->assertEquals(3, $stats['total_requests']);
        $this->assertEquals(1000, $stats['total_amount_requested']);
        $this->assertEquals(500, $stats['total_amount_processed']);
        $this->assertEquals(1, $stats['pending_requests']);
        $this->assertEquals(300, $stats['pending_amount']);
    }

    public function test_finds_withdrawals_requiring_approval(): void
    {
        $user = User::factory()->create();

        WithdrawalRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'type' => 'emergency',
            'status' => 'pending',
        ]);

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'partial',
            'status' => 'pending',
        ]);

        $requiresApproval = $this->repository->findWithdrawalsRequiringApproval();

        $this->assertCount(2, $requiresApproval);
        $requiresApproval->each(function ($withdrawal) {
            $this->assertEquals('emergency', $withdrawal->type);
            $this->assertEquals('pending', $withdrawal->status);
        });
    }

    public function test_gets_withdrawal_summary_by_period(): void
    {
        $user = User::factory()->create();
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'status' => 'processed',
            'requested_at' => $startDate->copy()->addDays(5),
        ]);

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 300,
            'status' => 'processed',
            'requested_at' => $startDate->copy()->addDays(10),
        ]);

        // Outside period
        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 200,
            'status' => 'processed',
            'requested_at' => $startDate->copy()->subDays(5),
        ]);

        $summary = $this->repository->getWithdrawalSummaryByPeriod($startDate, $endDate);

        $this->assertIsArray($summary);
        $this->assertEquals(2, $summary['total_withdrawals']);
        $this->assertEquals(800, $summary['total_amount']);
        $this->assertArrayHasKey('by_type', $summary);
        $this->assertArrayHasKey('by_status', $summary);
    }

    public function test_finds_large_withdrawals(): void
    {
        $user = User::factory()->create();
        $threshold = 1000;

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 1500, // Above threshold
        ]);

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500, // Below threshold
        ]);

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 2000, // Above threshold
        ]);

        $largeWithdrawals = $this->repository->findLargeWithdrawals($threshold);

        $this->assertCount(2, $largeWithdrawals);
        $largeWithdrawals->each(function ($withdrawal) use ($threshold) {
            $this->assertGreaterThanOrEqual($threshold, $withdrawal->amount);
        });
    }

    public function test_gets_recent_withdrawals(): void
    {
        $user = User::factory()->create();
        $limit = 5;

        WithdrawalRequest::factory()->count(10)->create([
            'user_id' => $user->id,
        ]);

        $recentWithdrawals = $this->repository->getRecentWithdrawals($limit);

        $this->assertCount($limit, $recentWithdrawals);
        
        // Check they are ordered by most recent first
        $timestamps = $recentWithdrawals->pluck('requested_at')->toArray();
        $sortedTimestamps = collect($timestamps)->sortDesc()->toArray();
        $this->assertEquals($sortedTimestamps, $timestamps);
    }

    public function test_counts_withdrawals_by_status(): void
    {
        $user = User::factory()->create();

        WithdrawalRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        WithdrawalRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        WithdrawalRequest::factory()->count(1)->create([
            'user_id' => $user->id,
            'status' => 'processed',
        ]);

        $counts = $this->repository->countWithdrawalsByStatus();

        $this->assertIsArray($counts);
        $this->assertEquals(3, $counts['pending']);
        $this->assertEquals(2, $counts['approved']);
        $this->assertEquals(1, $counts['processed']);
    }
}