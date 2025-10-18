<?php

namespace Tests\Unit\Models;

use App\Models\Commission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_belongs_to_user()
    {
        $commission = Commission::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->assertInstanceOf(User::class, $commission->user);
        $this->assertEquals($this->user->id, $commission->user->id);
    }

    public function test_can_scope_by_type()
    {
        Commission::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'referral',
            'amount' => 100,
        ]);

        Commission::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'matrix',
            'amount' => 50,
        ]);

        $referralCommissions = Commission::byType('referral')->get();
        $matrixCommissions = Commission::byType('matrix')->get();

        $this->assertCount(1, $referralCommissions);
        $this->assertCount(1, $matrixCommissions);
        $this->assertEquals('referral', $referralCommissions->first()->type);
        $this->assertEquals('matrix', $matrixCommissions->first()->type);
    }

    public function test_can_calculate_total_for_user()
    {
        Commission::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'amount' => 100,
        ]);

        $total = Commission::where('user_id', $this->user->id)->sum('amount');

        $this->assertEquals(300.0, $total);
    }

    public function test_can_get_recent_commissions()
    {
        Commission::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
            'created_at' => now()->subDays(2),
        ]);

        Commission::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 150,
            'created_at' => now()->subDay(),
        ]);

        $recentCommissions = Commission::where('user_id', $this->user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->assertCount(2, $recentCommissions);
        $this->assertEquals(150, $recentCommissions->first()->amount);
        $this->assertEquals(100, $recentCommissions->last()->amount);
    }

    public function test_commission_amount_is_cast_to_float()
    {
        $commission = Commission::factory()->create([
            'user_id' => $this->user->id,
            'amount' => '123.45',
        ]);

        $this->assertIsFloat($commission->amount);
        $this->assertEquals(123.45, $commission->amount);
    }

    public function test_can_filter_by_date_range()
    {
        $startDate = now()->subDays(10);
        $endDate = now()->subDays(5);

        Commission::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(7), // Within range
        ]);

        Commission::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(15), // Outside range
        ]);

        $commissionsInRange = Commission::whereBetween('created_at', [$startDate, $endDate])->get();

        $this->assertCount(1, $commissionsInRange);
    }
}