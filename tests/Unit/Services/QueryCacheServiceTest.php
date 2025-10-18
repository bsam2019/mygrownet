<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\QueryCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Investment;
use App\Models\ReferralCommission;

class QueryCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    private QueryCacheService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QueryCacheService();
    }

    public function test_get_dashboard_metrics_returns_cached_data()
    {
        // Create test data
        User::factory()->count(5)->create();
        Investment::factory()->count(3)->create(['status' => 'active']);
        
        // First call should cache the data
        $metrics1 = $this->service->getDashboardMetrics();
        
        // Second call should return cached data
        $metrics2 = $this->service->getDashboardMetrics();
        
        $this->assertEquals($metrics1, $metrics2);
        $this->assertArrayHasKey('total_users', $metrics1);
        $this->assertArrayHasKey('active_investments', $metrics1);
        $this->assertArrayHasKey('total_investment_amount', $metrics1);
    }

    public function test_get_user_stats_caches_user_specific_data()
    {
        $user = User::factory()->create();
        Investment::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'active',
            'amount' => 1000
        ]);

        $stats = $this->service->getUserStats($user->id);

        $this->assertArrayHasKey('total_investments', $stats);
        $this->assertArrayHasKey('total_earnings', $stats);
        $this->assertArrayHasKey('referral_count', $stats);
        $this->assertEquals(2000, $stats['total_investments']);
    }

    public function test_get_tier_stats_returns_tier_breakdown()
    {
        Investment::factory()->create(['tier' => 'basic', 'amount' => 500, 'status' => 'active']);
        Investment::factory()->create(['tier' => 'starter', 'amount' => 1000, 'status' => 'active']);
        Investment::factory()->create(['tier' => 'basic', 'amount' => 500, 'status' => 'active']);

        $stats = $this->service->getTierStats();

        $this->assertArrayHasKey('basic', $stats);
        $this->assertArrayHasKey('starter', $stats);
        $this->assertEquals(2, $stats['basic']['investment_count']);
        $this->assertEquals(1000, $stats['basic']['total_amount']);
    }

    public function test_get_commission_stats_returns_commission_breakdown()
    {
        ReferralCommission::factory()->create([
            'amount' => 100,
            'level' => 1,
            'status' => 'paid'
        ]);
        ReferralCommission::factory()->create([
            'amount' => 50,
            'level' => 2,
            'status' => 'paid'
        ]);

        $stats = $this->service->getCommissionStats();

        $this->assertArrayHasKey('total_commissions', $stats);
        $this->assertArrayHasKey('level_breakdown', $stats);
        $this->assertEquals(150, $stats['total_commissions']);
        $this->assertEquals(2, $stats['commission_count']);
    }

    public function test_get_performance_trends_returns_monthly_data()
    {
        // Create investments from different months
        Investment::factory()->create([
            'investment_date' => now()->subMonth(),
            'amount' => 1000,
            'status' => 'active'
        ]);
        Investment::factory()->create([
            'investment_date' => now(),
            'amount' => 2000,
            'status' => 'active'
        ]);

        $trends = $this->service->getPerformanceTrends(3);

        $this->assertArrayHasKey('investment_trends', $trends);
        $this->assertArrayHasKey('commission_trends', $trends);
        $this->assertArrayHasKey('withdrawal_trends', $trends);
        $this->assertIsArray($trends['investment_trends']);
    }

    public function test_get_top_performers_returns_limited_results()
    {
        // Create users with different investment amounts
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Investment::factory()->create([
            'user_id' => $user1->id,
            'amount' => 5000,
            'status' => 'active'
        ]);
        Investment::factory()->create([
            'user_id' => $user2->id,
            'amount' => 3000,
            'status' => 'active'
        ]);

        $performers = $this->service->getTopPerformers(5);

        $this->assertArrayHasKey('top_investors', $performers);
        $this->assertArrayHasKey('top_referrers', $performers);
        $this->assertCount(2, $performers['top_investors']);
        
        // Check ordering (highest first)
        $this->assertEquals(5000, $performers['top_investors'][0]['total_invested']);
        $this->assertEquals(3000, $performers['top_investors'][1]['total_invested']);
    }

    public function test_clear_user_cache_removes_user_specific_keys()
    {
        $userId = 123;
        
        // Set some cache values
        Cache::put("user_stats_{$userId}", ['test' => 'data'], 300);
        Cache::put("active_investments_{$userId}", ['test' => 'data'], 300);
        
        $this->assertTrue(Cache::has("user_stats_{$userId}"));
        $this->assertTrue(Cache::has("active_investments_{$userId}"));
        
        // Clear user cache
        $this->service->clearUserCache($userId);
        
        $this->assertFalse(Cache::has("user_stats_{$userId}"));
        $this->assertFalse(Cache::has("active_investments_{$userId}"));
    }

    public function test_clear_dashboard_cache_removes_dashboard_keys()
    {
        // Set some cache values
        Cache::put('dashboard_metrics', ['test' => 'data'], 300);
        Cache::put('tier_statistics', ['test' => 'data'], 300);
        
        $this->assertTrue(Cache::has('dashboard_metrics'));
        $this->assertTrue(Cache::has('tier_statistics'));
        
        // Clear dashboard cache
        $this->service->clearDashboardCache();
        
        $this->assertFalse(Cache::has('dashboard_metrics'));
        $this->assertFalse(Cache::has('tier_statistics'));
    }

    public function test_warm_up_caches_populates_cache()
    {
        // Ensure cache is empty
        Cache::flush();
        
        // Warm up caches
        $this->service->warmUpCaches();
        
        // Check that key caches are populated
        $this->assertTrue(Cache::has('dashboard_metrics'));
        $this->assertTrue(Cache::has('tier_statistics'));
        $this->assertTrue(Cache::has('matrix_statistics'));
        $this->assertTrue(Cache::has('commission_statistics'));
    }

    public function test_get_cache_stats_returns_cache_status()
    {
        // Set some cache values
        Cache::put('dashboard_metrics', ['test' => 'data'], 300);
        
        $stats = $this->service->getCacheStats();
        
        $this->assertArrayHasKey('dashboard_metrics', $stats);
        $this->assertTrue($stats['dashboard_metrics']['exists']);
        $this->assertEquals('cached', $stats['dashboard_metrics']['ttl']);
    }

    public function test_cache_ttl_constants_are_defined()
    {
        $this->assertEquals(300, QueryCacheService::SHORT_CACHE);
        $this->assertEquals(900, QueryCacheService::MEDIUM_CACHE);
        $this->assertEquals(3600, QueryCacheService::LONG_CACHE);
        $this->assertEquals(86400, QueryCacheService::DAILY_CACHE);
    }

    public function test_get_withdrawal_stats_returns_withdrawal_data()
    {
        // This test would require withdrawal_requests table
        // For now, just test that the method exists and returns array
        $stats = $this->service->getWithdrawalStats();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_withdrawals', $stats);
        $this->assertArrayHasKey('pending_withdrawals', $stats);
    }

    public function test_get_matrix_stats_returns_matrix_data()
    {
        $stats = $this->service->getMatrixStats();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_positions', $stats);
        $this->assertArrayHasKey('filled_positions', $stats);
        $this->assertArrayHasKey('average_level', $stats);
    }
}