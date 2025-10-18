<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ReferralCommission;
use App\Models\ProfitShare;
use App\Models\WithdrawalRequest;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $investmentTier;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test investment tier
        $this->investmentTier = InvestmentTier::factory()->create([
            'name' => 'Starter',
            'minimum_investment' => 1000,
            'fixed_profit_rate' => 5.0,
            'direct_referral_rate' => 7.0,
            'level2_referral_rate' => 2.0,
            'level3_referral_rate' => 0.0,
            'is_active' => true
        ]);

        // Create test user
        $this->user = User::factory()->create([
            'total_investment_amount' => 2500,
            'total_referral_earnings' => 150,
            'total_profit_earnings' => 200,
            'current_investment_tier_id' => $this->investmentTier->id,
            'referral_code' => 'VBIF123456'
        ]);
    }

    /** @test */
    public function it_displays_comprehensive_dashboard_data()
    {
        // Create test investments
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $this->investmentTier->id,
            'amount' => 1500,
            'status' => 'active',
            'investment_date' => now()->subMonths(6)
        ]);

        // Create test referral commissions
        ReferralCommission::factory()->create([
            'referrer_id' => $this->user->id,
            'amount' => 75,
            'level' => 1,
            'status' => 'paid'
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Investors/Dashboard/index')
                ->has('portfolio')
                ->has('earnings')
                ->has('tierInfo')
                ->has('matrixStructure')
                ->has('investmentMetrics')
                ->has('withdrawal_summary')
                ->has('recent_activities')
                ->has('notifications')
        );
    }

    /** @test */
    public function it_returns_real_time_earnings_data()
    {
        // Create test data
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $this->investmentTier->id,
            'amount' => 2000,
            'status' => 'active'
        ]);

        ReferralCommission::factory()->create([
            'referrer_id' => $this->user->id,
            'amount' => 100,
            'level' => 1,
            'status' => 'paid'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.real-time-earnings'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'earnings' => [
                    'referral_commissions',
                    'profit_shares',
                    'matrix_commissions',
                    'total_earnings',
                    'pending_earnings'
                ],
                'investment_performance' => [
                    'total_invested',
                    'current_value',
                    'total_profit',
                    'average_roi',
                    'investment_count'
                ],
                'recent_earnings',
                'last_updated'
            ]
        ]);

        $this->assertTrue($response->json('success'));
    }

    /** @test */
    public function it_returns_withdrawal_eligibility_for_specific_investment()
    {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $this->investmentTier->id,
            'amount' => 1500,
            'status' => 'active',
            'investment_date' => now()->subMonths(6),
            'lock_in_period_end' => now()->addMonths(6)
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.withdrawal-eligibility', [
                'investment_id' => $investment->id,
                'withdrawal_type' => 'full'
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'investment' => [
                    'id',
                    'amount',
                    'current_value',
                    'tier'
                ],
                'eligibility' => [
                    'is_eligible',
                    'withdrawal_type',
                    'eligibility_checks',
                    'reasons'
                ],
                'penalties',
                'scenarios',
                'withdrawable_amount'
            ]
        ]);
    }

    /** @test */
    public function it_returns_general_withdrawal_eligibility_when_no_investment_specified()
    {
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $this->investmentTier->id,
            'amount' => 1500,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.withdrawal-eligibility'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'general_eligibility',
                'user_investments'
            ]
        ]);
    }

    /** @test */
    public function it_returns_penalty_preview_for_withdrawal_scenario()
    {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $this->investmentTier->id,
            'amount' => 2000,
            'status' => 'active',
            'investment_date' => now()->subMonths(3),
            'lock_in_period_end' => now()->addMonths(9)
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.penalty-preview'), [
                'investment_id' => $investment->id,
                'withdrawal_type' => 'emergency',
                'amount' => 1000,
                'withdrawal_date' => now()->addDays(30)->toDateString()
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'investment',
                'withdrawal_details' => [
                    'type',
                    'requested_amount',
                    'withdrawal_date',
                    'withdrawable_amount'
                ],
                'penalty_details' => [
                    'penalty_applicable',
                    'total_penalty_amount',
                    'net_withdrawable_amount'
                ],
                'eligibility',
                'net_amount'
            ]
        ]);
    }

    /** @test */
    public function it_returns_404_for_penalty_preview_with_invalid_investment()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard.penalty-preview'), [
                'investment_id' => 99999,
                'withdrawal_type' => 'full'
            ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Investment not found'
        ]);
    }

    /** @test */
    public function it_returns_notifications_and_activity_feed()
    {
        // Create test activity log
        ActivityLog::factory()->create([
            'user_id' => $this->user->id,
            'action' => 'investment_created',
            'description' => 'Created new investment'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.notifications-activity'), [
                'limit' => 10,
                'type' => 'all'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'notifications',
                'activities',
                'combined_feed'
            ]
        ]);
    }

    /** @test */
    public function it_returns_only_notifications_when_type_specified()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard.notifications-activity'), [
                'type' => 'notifications'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'notifications'
            ]
        ]);

        $response->assertJsonMissing(['activities', 'combined_feed']);
    }

    /** @test */
    public function it_returns_comprehensive_dashboard_metrics()
    {
        // Create test data
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $this->investmentTier->id,
            'amount' => 1500,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.metrics'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'portfolio_overview' => [
                    'total_invested',
                    'current_value',
                    'total_profit',
                    'roi_percentage',
                    'investment_count'
                ],
                'earnings_breakdown',
                'investment_performance',
                'referral_metrics',
                'tier_information',
                'matrix_performance',
                'withdrawal_summary',
                'growth_trends'
            ]
        ]);
    }

    /** @test */
    public function it_returns_investment_trends_for_portfolio()
    {
        Investment::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'tier_id' => $this->investmentTier->id,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.investment-trends'), [
                'period' => 'month'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'portfolio_performance',
                'trend_analysis',
                'growth_projections'
            ]
        ]);
    }

    /** @test */
    public function it_returns_investment_trends_for_specific_investment()
    {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $this->investmentTier->id,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.investment-trends'), [
                'investment_id' => $investment->id,
                'period' => 'quarter'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'investment_id',
                'performance_metrics',
                'projections',
                'historical_performance'
            ]
        ]);
    }

    /** @test */
    public function it_returns_tier_upgrade_recommendations()
    {
        // Create higher tier for upgrade
        $higherTier = InvestmentTier::factory()->create([
            'name' => 'Builder',
            'minimum_investment' => 2500,
            'fixed_profit_rate' => 7.0,
            'order' => $this->investmentTier->order + 1
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.tier-upgrade-recommendations'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'eligibility' => [
                    'eligible',
                    'current_tier',
                    'next_tier',
                    'required_amount',
                    'remaining_amount'
                ],
                'recommendations',
                'tier_comparison',
                'upgrade_benefits'
            ]
        ]);
    }

    /** @test */
    public function it_returns_matrix_data_and_performance()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard.matrix-data'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'structure',
                'position_details',
                'downline_counts',
                'performance_metrics',
                'spillover_opportunities',
                'commission_potential'
            ]
        ]);
    }

    /** @test */
    public function it_caches_dashboard_data_for_performance()
    {
        // First request should hit the database
        $response1 = $this->actingAs($this->user)->get(route('dashboard'));
        $response1->assertStatus(200);

        // Second request should use cached data
        $response2 = $this->actingAs($this->user)->get(route('dashboard'));
        $response2->assertStatus(200);

        // Both responses should be identical
        $this->assertEquals(
            $response1->getOriginalContent()->getData()['page']['props']['portfolio'],
            $response2->getOriginalContent()->getData()['page']['props']['portfolio']
        );
    }

    /** @test */
    public function it_handles_user_with_no_investments_gracefully()
    {
        $userWithoutInvestments = User::factory()->create([
            'total_investment_amount' => 0,
            'total_referral_earnings' => 0,
            'total_profit_earnings' => 0
        ]);

        $response = $this->actingAs($userWithoutInvestments)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('portfolio')
                ->where('portfolio.total_investment', 0)
                ->where('portfolio.total_earnings', 0)
        );
    }

    /** @test */
    public function it_handles_user_with_no_tier_gracefully()
    {
        $userWithoutTier = User::factory()->create([
            'current_investment_tier_id' => null
        ]);

        $response = $this->actingAs($userWithoutTier)
            ->get(route('dashboard.metrics'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'tier_information'
            ]
        ]);
    }

    /** @test */
    public function it_validates_penalty_preview_request_parameters()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard.penalty-preview'), [
                'investment_id' => 'invalid',
                'withdrawal_type' => 'invalid_type'
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_returns_appropriate_notifications_based_on_user_status()
    {
        // Create investment eligible for withdrawal
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $this->investmentTier->id,
            'amount' => 1500,
            'status' => 'active',
            'investment_date' => now()->subYear(), // Past lock-in period
            'lock_in_period_end' => now()->subMonth()
        ]);

        // Create pending commission
        ReferralCommission::factory()->create([
            'referrer_id' => $this->user->id,
            'amount' => 50,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.notifications-activity'), [
                'type' => 'notifications'
            ]);

        $response->assertStatus(200);
        
        $notifications = $response->json('data.notifications');
        
        // Should have notifications for withdrawal availability and pending commissions
        $this->assertGreaterThan(0, count($notifications));
        
        $notificationTypes = collect($notifications)->pluck('type')->toArray();
        $this->assertContains('withdrawal_available', $notificationTypes);
        $this->assertContains('pending_commissions', $notificationTypes);
    }

    /** @test */
    public function it_calculates_growth_trends_correctly()
    {
        // Create investments over different months
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $this->investmentTier->id,
            'amount' => 1000,
            'status' => 'active',
            'created_at' => now()->subMonths(3)
        ]);

        Investment::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $this->investmentTier->id,
            'amount' => 1500,
            'status' => 'active',
            'created_at' => now()->subMonth()
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.metrics'));

        $response->assertStatus(200);
        
        $growthTrends = $response->json('data.growth_trends');
        
        $this->assertArrayHasKey('monthly_data', $growthTrends);
        $this->assertArrayHasKey('total_growth_rate', $growthTrends);
        $this->assertCount(12, $growthTrends['monthly_data']); // 12 months of data
    }

    /** @test */
    public function it_handles_matrix_data_for_user_without_matrix_position()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard.matrix-data'));

        $response->assertStatus(200);
        
        $matrixData = $response->json('data');
        
        // Should handle gracefully even without matrix position
        $this->assertArrayHasKey('structure', $matrixData);
        $this->assertArrayHasKey('downline_counts', $matrixData);
        $this->assertArrayHasKey('spillover_opportunities', $matrixData);
    }
}