<?php

namespace Tests\Integration\Repositories;

use Tests\TestCase;
use App\Infrastructure\Persistence\Repositories\EloquentCommissionRepository;
use App\Domain\MLM\Entities\Commission;
use App\Domain\MLM\ValueObjects\CommissionId;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\CommissionLevel;
use App\Domain\MLM\ValueObjects\CommissionAmount;
use App\Domain\MLM\ValueObjects\CommissionType;
use App\Domain\MLM\ValueObjects\CommissionStatus;
use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\UserNetwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DateTimeImmutable;

class EloquentCommissionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentCommissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentCommissionRepository();
    }

    public function test_can_save_and_find_commission_by_id()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $commission = Commission::create(
            CommissionId::fromInt(1),
            UserId::fromInt($user1->id),
            UserId::fromInt($user2->id),
            CommissionLevel::fromInt(1),
            CommissionAmount::fromFloat(120.00),
            CommissionType::fromString('REFERRAL')
        );

        $this->repository->save($commission);

        $found = $this->repository->findById(CommissionId::fromInt(1));
        
        $this->assertNotNull($found);
        $this->assertEquals($commission->getEarnerId()->value(), $found->getEarnerId()->value());
        $this->assertEquals($commission->getAmount()->value(), $found->getAmount()->value());
    }

    public function test_can_find_commissions_by_earner_id()
    {
        $earner = User::factory()->create();
        $source1 = User::factory()->create();
        $source2 = User::factory()->create();

        ReferralCommission::factory()->create([
            'referrer_id' => $earner->id,
            'referred_id' => $source1->id,
            'level' => 1,
            'amount' => 120.00,
            'commission_type' => 'REFERRAL'
        ]);

        ReferralCommission::factory()->create([
            'referrer_id' => $earner->id,
            'referred_id' => $source2->id,
            'level' => 2,
            'amount' => 60.00,
            'commission_type' => 'REFERRAL'
        ]);

        $commissions = $this->repository->findByEarnerId(UserId::fromInt($earner->id));

        $this->assertCount(2, $commissions);
        $this->assertEquals($earner->id, $commissions[0]->getEarnerId()->value());
        $this->assertEquals($earner->id, $commissions[1]->getEarnerId()->value());
    }

    public function test_can_find_pending_commissions()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        ReferralCommission::factory()->create([
            'referrer_id' => $user1->id,
            'referred_id' => $user2->id,
            'status' => 'pending',
            'amount' => 120.00
        ]);

        ReferralCommission::factory()->create([
            'referrer_id' => $user1->id,
            'referred_id' => $user2->id,
            'status' => 'paid',
            'amount' => 60.00
        ]);

        $pendingCommissions = $this->repository->findPendingCommissions();

        $this->assertCount(1, $pendingCommissions);
        $this->assertTrue($pendingCommissions[0]->getStatus()->isPending());
    }

    public function test_can_calculate_total_commissions_for_period()
    {
        $earner = User::factory()->create();
        $source = User::factory()->create();

        $startDate = new DateTimeImmutable('2024-01-01');
        $endDate = new DateTimeImmutable('2024-01-31');

        // Commission within period
        ReferralCommission::factory()->create([
            'referrer_id' => $earner->id,
            'referred_id' => $source->id,
            'amount' => 120.00,
            'status' => 'paid',
            'created_at' => '2024-01-15'
        ]);

        // Commission outside period
        ReferralCommission::factory()->create([
            'referrer_id' => $earner->id,
            'referred_id' => $source->id,
            'amount' => 60.00,
            'status' => 'paid',
            'created_at' => '2024-02-15'
        ]);

        $total = $this->repository->calculateTotalCommissions(
            UserId::fromInt($earner->id),
            $startDate,
            $endDate
        );

        $this->assertEquals(120.00, $total);
    }

    public function test_can_get_commission_stats_by_level()
    {
        $earner = User::factory()->create();
        $source = User::factory()->create();

        // Level 1 commissions
        ReferralCommission::factory()->create([
            'referrer_id' => $earner->id,
            'referred_id' => $source->id,
            'level' => 1,
            'amount' => 120.00,
            'status' => 'paid'
        ]);

        ReferralCommission::factory()->create([
            'referrer_id' => $earner->id,
            'referred_id' => $source->id,
            'level' => 1,
            'amount' => 100.00,
            'status' => 'pending'
        ]);

        // Level 2 commission
        ReferralCommission::factory()->create([
            'referrer_id' => $earner->id,
            'referred_id' => $source->id,
            'level' => 2,
            'amount' => 60.00,
            'status' => 'paid'
        ]);

        $stats = $this->repository->getCommissionStatsByLevel(UserId::fromInt($earner->id));

        $this->assertArrayHasKey(1, $stats);
        $this->assertArrayHasKey(2, $stats);
        
        $this->assertEquals(2, $stats[1]['count']);
        $this->assertEquals(220.00, $stats[1]['total_amount']);
        $this->assertEquals(120.00, $stats[1]['paid_amount']);
        
        $this->assertEquals(1, $stats[2]['count']);
        $this->assertEquals(60.00, $stats[2]['total_amount']);
        $this->assertEquals(60.00, $stats[2]['paid_amount']);
    }

    public function test_can_find_network_commissions_efficiently()
    {
        $rootUser = User::factory()->create();
        $level1User = User::factory()->create();
        $level2User = User::factory()->create();

        // Create network structure
        UserNetwork::create([
            'user_id' => $level1User->id,
            'referrer_id' => $rootUser->id,
            'level' => 1,
            'path' => $level1User->id
        ]);

        UserNetwork::create([
            'user_id' => $level2User->id,
            'referrer_id' => $rootUser->id,
            'level' => 2,
            'path' => $level1User->id . '.' . $level2User->id
        ]);

        // Create commissions
        ReferralCommission::factory()->create([
            'referrer_id' => $rootUser->id,
            'referred_id' => $level1User->id,
            'level' => 1,
            'amount' => 120.00
        ]);

        ReferralCommission::factory()->create([
            'referrer_id' => $rootUser->id,
            'referred_id' => $level2User->id,
            'level' => 2,
            'amount' => 60.00
        ]);

        $networkCommissions = $this->repository->findNetworkCommissions(
            UserId::fromInt($rootUser->id),
            5
        );

        $this->assertCount(2, $networkCommissions);
    }

    public function test_can_bulk_update_commission_status()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $commission1 = ReferralCommission::factory()->create([
            'referrer_id' => $user1->id,
            'referred_id' => $user2->id,
            'status' => 'pending'
        ]);

        $commission2 = ReferralCommission::factory()->create([
            'referrer_id' => $user1->id,
            'referred_id' => $user2->id,
            'status' => 'pending'
        ]);

        $this->repository->bulkUpdateStatus(
            [$commission1->id, $commission2->id],
            CommissionStatus::paid()
        );

        $this->assertDatabaseHas('referral_commissions', [
            'id' => $commission1->id,
            'status' => 'paid'
        ]);

        $this->assertDatabaseHas('referral_commissions', [
            'id' => $commission2->id,
            'status' => 'paid'
        ]);
    }

    public function test_repository_performance_with_large_dataset()
    {
        $earner = User::factory()->create();
        $sources = User::factory()->count(100)->create();

        // Create 100 commissions
        foreach ($sources as $source) {
            ReferralCommission::factory()->create([
                'referrer_id' => $earner->id,
                'referred_id' => $source->id,
                'amount' => rand(50, 200),
                'level' => rand(1, 5)
            ]);
        }

        $startTime = microtime(true);
        
        $commissions = $this->repository->findByEarnerId(UserId::fromInt($earner->id));
        $stats = $this->repository->getCommissionStatsByLevel(UserId::fromInt($earner->id));
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertCount(100, $commissions);
        $this->assertNotEmpty($stats);
        $this->assertLessThan(1.0, $executionTime, 'Repository queries should complete within 1 second');
    }
}