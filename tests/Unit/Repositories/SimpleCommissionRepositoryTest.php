<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Domain\MLM\ValueObjects\CommissionId;
use App\Domain\MLM\ValueObjects\UserId;
use App\Models\User;
use App\Models\ReferralCommission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SimpleCommissionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_find_commission()
    {
        // Create users manually
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

        // Create commission directly
        $commission = ReferralCommission::create([
            'referrer_id' => $user1->id,
            'referred_id' => $user2->id,
            'level' => 1,
            'amount' => 120.00,
            'commission_type' => 'REFERRAL',
            'status' => 'pending',
            'percentage' => 12.0,
        ]);

        // Test basic database operations
        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $user1->id,
            'referred_id' => $user2->id,
            'amount' => 120.00,
            'status' => 'pending'
        ]);

        // Test finding commission
        $found = ReferralCommission::find($commission->id);
        $this->assertNotNull($found);
        $this->assertEquals($user1->id, $found->referrer_id);
        $this->assertEquals(120.00, $found->amount);
    }

    public function test_commission_value_objects_work()
    {
        $commissionId = CommissionId::fromInt(1);
        $userId = UserId::fromInt(123);
        
        $this->assertEquals(1, $commissionId->value());
        $this->assertEquals(123, $userId->value());
    }
}