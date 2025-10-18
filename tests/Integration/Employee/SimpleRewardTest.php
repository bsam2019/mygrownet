<?php

namespace Tests\Integration\Employee;

use Tests\TestCase;
use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ReferralCommission;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SimpleRewardTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_reward_integration()
    {
        // Create test data
        $tier = InvestmentTier::firstOrCreate(
            ['name' => 'Basic'],
            [
                'minimum_investment' => 500,
                'fixed_profit_rate' => 3.0,
                'direct_referral_rate' => 5.0,
                'level2_referral_rate' => 2.0,
                'level3_referral_rate' => 1.0,
                'benefits' => [],
                'is_active' => true,
                'description' => 'Basic tier',
                'order' => 1,
            ]
        );

        $referrer = User::factory()->create(['email' => 'referrer@test.com']);
        $investor = User::factory()->create([
            'email' => 'investor@test.com',
            'referrer_id' => $referrer->id,
        ]);

        $investment = Investment::factory()->create([
            'user_id' => $investor->id,
            'tier' => $tier->name,
            'amount' => 1000,
            'status' => 'active',
            'investment_date' => now(),
            'lock_in_period_end' => now()->addYear(),
        ]);

        // Test referral service
        $referralService = app(ReferralService::class);
        $referralService->processReferralCommission($investment);

        // Assert referral commission was created
        $commission = ReferralCommission::where('investment_id', $investment->id)->first();
        $this->assertNotNull($commission);
        $this->assertEquals($referrer->id, $commission->referrer_id);
        $this->assertEquals($investor->id, $commission->referred_id);
        $this->assertEquals(50.0, $commission->amount); // 1000 * 5%
    }
}