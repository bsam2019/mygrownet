<?php

namespace Tests\Unit\Models;

use App\Models\Commission;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ProfitShare;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserOptimizedTest extends TestCase
{
    use RefreshDatabase;

    protected InvestmentTier $tier;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
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
    }

    public function test_has_many_investments()
    {
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

        $this->assertCount(2, $this->user->investments);
        $this->assertInstanceOf(Investment::class, $this->user->investments->first());
    }

    public function test_has_many_profit_shares()
    {
        // Create profit shares directly without factory
        ProfitShare::create([
            'user_id' => $this->user->id,
            'amount' => 100.00,
            'distribution_date' => now(),
            'status' => 'paid'
        ]);

        $this->assertCount(1, $this->user->profitShares);
        $this->assertInstanceOf(ProfitShare::class, $this->user->profitShares->first());
    }

    public function test_has_many_commissions()
    {
        // Create commission directly without factory
        Commission::create([
            'user_id' => $this->user->id,
            'referral_user_id' => $this->user->id,
            'amount' => 50.00,
            'level' => 1,
            'type' => 'referral',
            'status' => 'paid'
        ]);

        $this->assertCount(1, $this->user->commissions);
        $this->assertInstanceOf(Commission::class, $this->user->commissions->first());
    }

    public function test_can_calculate_total_investment_amount()
    {
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

        $this->assertEquals(2500, $this->user->getTotalInvestmentAmount());
    }

    public function test_can_get_current_investment_tier()
    {
        $this->assertEquals('Basic', $this->user->currentInvestmentTier->name);
    }

    public function test_handles_users_with_no_data_gracefully()
    {
        $emptyUser = User::create([
            'name' => 'Empty User',
            'email' => 'empty@example.com',
            'password' => 'password'
        ]);

        $this->assertEquals(0, $emptyUser->getTotalInvestmentAmount());
        $this->assertNull($emptyUser->currentInvestmentTier);
    }
}