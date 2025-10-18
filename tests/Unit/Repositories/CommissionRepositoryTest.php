<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Infrastructure\Persistence\Repositories\EloquentCommissionRepository;
use App\Domain\MLM\ValueObjects\CommissionId;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\CommissionLevel;
use App\Domain\MLM\ValueObjects\CommissionAmount;
use App\Domain\MLM\ValueObjects\CommissionType;
use App\Domain\MLM\ValueObjects\CommissionStatus;
use App\Models\User;
use App\Models\ReferralCommission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommissionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentCommissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentCommissionRepository();
    }

    public function test_can_find_commission_by_id()
    {
        // Create users manually to avoid factory memory issues
        $user1 = User::create([
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
        ]);

        $commission = ReferralCommission::create([
            'referrer_id' => $user1->id,
            'referred_id' => $user2->id,
            'level' => 1,
            'amount' => 120.00,
            'commission_type' => 'REFERRAL',
            'status' => 'pending',
            'percentage' => 12.0,
        ]);

        $found = $this->repository->findById(CommissionId::fromInt($commission->id));
        
        $this->assertNotNull($found);
        $this->assertEquals($user1->id, $found->getEarnerId()->value());
        $this->assertEquals($user2->id, $found->getSourceId()->value());
        $this->assertEquals(1, $found->getLevel()->value());
        $this->assertEquals(120.00, $found->getAmount()->value());
    }

    public function test_can_find_pending_commissions()
    {
        $user1 = User::create([
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
        ]);

        ReferralCommission::create([
            'referrer_id' => $user1->id,
            'referred_id' => $user2->id,
            'status' => 'pending',
            'amount' => 120.00,
            'level' => 1,
            'commission_type' => 'REFERRAL',
            'percentage' => 12.0,
        ]);

        ReferralCommission::create([
            'referrer_id' => $user1->id,
            'referred_id' => $user2->id,
            'status' => 'paid',
            'amount' => 60.00,
            'level' => 2,
            'commission_type' => 'REFERRAL',
            'percentage' => 6.0,
        ]);

        $pendingCommissions = $this->repository->findPendingCommissions();

        $this->assertCount(1, $pendingCommissions);
        $this->assertTrue($pendingCommissions[0]->getStatus()->isPending());
    }
}