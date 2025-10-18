<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\TeamVolume;
use App\Models\InvestmentTier;
use App\Services\MLMAdministrationService;
use App\Services\MLMCommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use Mockery;

class MLMAdministrationServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected MLMAdministrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MLMAdministrationService();
    }

    public function test_get_overview_metrics_returns_correct_structure()
    {
        // Create test data
        $user = User::factory()->create();
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'amount' => 100.00,
            'status' => 'pending'
        ]);

        $metrics = $this->service->getOverviewMetrics('month');

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('total_commissions', $metrics);
        $this->assertArrayHasKey('pending_commissions', $metrics);
        $this->assertArrayHasKey('active_members', $metrics);
        $this->assertArrayHasKey('network_growth', $metrics);
        $this->assertArrayHasKey('total_volume', $metrics);
        $this->assertArrayHasKey('compliance_score', $metrics);
    }

    public function test_get_commission_statistics_groups_data_correctly()
    {
        $user = User::factory()->create();
        
        // Create commissions of different types and levels
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'commission_type' => 'REFERRAL',
            'level' => 1,
            'amount' => 100.00,
            'status' => 'paid'
        ]);
        
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'commission_type' => 'TEAM_VOLUME',
            'level' => 2,
            'amount' => 50.00,
            'status' => 'pending'
        ]);

        $stats = $this->service->getCommissionStatistics('month');

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('by_type', $stats);
        $this->assertArrayHasKey('by_level', $stats);
        $this->assertArrayHasKey('by_status', $stats);
        $this->assertArrayHasKey('trends', $stats);
    }

    public function test_get_network_analytics_returns_distribution_data()
    {
        // Create users with network levels
        User::factory()->create(['network_level' => 1]);
        User::factory()->create(['network_level' => 2]);
        User::factory()->create(['network_level' => 3]);

        $analytics = $this->service->getNetworkAnalytics();

        $this->assertIsArray($analytics);
        $this->assertArrayHasKey('depth_distribution', $analytics);
        $this->assertArrayHasKey('tier_distribution', $analytics);
        $this->assertArrayHasKey('top_performers', $analytics);
        $this->assertArrayHasKey('growth_metrics', $analytics);
    }

    public function test_get_system_alerts_detects_high_pending_commissions()
    {
        $user = User::factory()->create();
        
        // Create more than 100 pending commissions to trigger alert
        ReferralCommission::factory()->count(101)->create([
            'referrer_id' => $user->id,
            'status' => 'pending'
        ]);

        $alerts = $this->service->getSystemAlerts();

        $this->assertIsArray($alerts);
        $this->assertNotEmpty($alerts);
        
        $highPendingAlert = collect($alerts)->firstWhere('type', 'warning');
        $this->assertNotNull($highPendingAlert);
        $this->assertStringContainsString('High Pending Commissions', $highPendingAlert['title']);
    }

    public function test_get_system_alerts_detects_low_compliance_score()
    {
        // Mock the compliance score calculation to return low score
        $service = Mockery::mock(MLMAdministrationService::class)->makePartial();
        $service->shouldReceive('getComplianceScore')->andReturn(80.0); // Below 85% threshold

        $alerts = $service->getSystemAlerts();

        $complianceAlert = collect($alerts)->firstWhere('type', 'error');
        $this->assertNotNull($complianceAlert);
        $this->assertStringContainsString('Compliance Alert', $complianceAlert['title']);
    }

    public function test_get_commissions_with_filters_applies_status_filter()
    {
        $user = User::factory()->create();
        
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'status' => 'pending'
        ]);
        
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'status' => 'paid'
        ]);

        $result = $this->service->getCommissionsWithFilters(['status' => 'pending']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('pending', $result->first()->status);
    }

    public function test_get_commissions_with_filters_applies_search_filter()
    {
        $user1 = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $user2 = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        
        ReferralCommission::factory()->create(['referrer_id' => $user1->id]);
        ReferralCommission::factory()->create(['referrer_id' => $user2->id]);

        $result = $this->service->getCommissionsWithFilters(['search' => 'John']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals($user1->id, $result->first()->referrer_id);
    }

    public function test_adjust_commission_updates_amount_and_logs_change()
    {
        $admin = User::factory()->create();
        $referrer = User::factory()->create();
        $referee = User::factory()->create();
        
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'amount' => 100.00,
            'status' => 'pending'
        ]);

        $result = $this->service->adjustCommission(
            $commission->id,
            150.00,
            'Test adjustment',
            $admin->id
        );

        $commission->refresh();
        
        $this->assertEquals(150.00, $commission->amount);
        $this->assertEquals($admin->id, $commission->adjusted_by);
        $this->assertEquals('Test adjustment', $commission->adjustment_reason);
        $this->assertNotNull($commission->adjusted_at);
        
        $this->assertArrayHasKey('commission', $result);
        $this->assertArrayHasKey('adjustment', $result);
        $this->assertEquals(50.00, $result['adjustment']['difference']);
    }

    public function test_adjust_commission_updates_user_balance_for_paid_commission()
    {
        $admin = User::factory()->create();
        $referrer = User::factory()->create(['balance' => 500.00]);
        $referee = User::factory()->create();
        
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'amount' => 100.00,
            'status' => 'paid'
        ]);

        $this->service->adjustCommission(
            $commission->id,
            150.00,
            'Bonus adjustment',
            $admin->id
        );

        $referrer->refresh();
        $this->assertEquals(550.00, $referrer->balance); // 500 + (150 - 100)
    }

    public function test_process_pending_commissions_approves_valid_commissions()
    {
        $admin = User::factory()->create();
        $referrer = User::factory()->create();
        $referee = User::factory()->create();
        
        $commission1 = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'status' => 'pending'
        ]);
        
        $commission2 = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'status' => 'pending'
        ]);

        $result = $this->service->processPendingCommissions(
            [$commission1->id, $commission2->id],
            'approve',
            $admin->id
        );

        $this->assertCount(2, $result['processed']);
        $this->assertEmpty($result['failed']);
        
        $commission1->refresh();
        $commission2->refresh();
        
        $this->assertEquals('paid', $commission1->status);
        $this->assertEquals('paid', $commission2->status);
    }

    public function test_process_pending_commissions_rejects_commissions()
    {
        $admin = User::factory()->create();
        $referrer = User::factory()->create();
        $referee = User::factory()->create();
        
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'status' => 'pending'
        ]);

        $result = $this->service->processPendingCommissions(
            [$commission->id],
            'reject',
            $admin->id
        );

        $this->assertCount(1, $result['processed']);
        
        $commission->refresh();
        $this->assertEquals('rejected', $commission->status);
        $this->assertEquals($admin->id, $commission->rejected_by);
        $this->assertNotNull($commission->rejected_at);
    }

    public function test_get_network_structure_builds_tree_correctly()
    {
        $rootUser = User::factory()->create([
            'network_path' => '1',
            'network_level' => 0
        ]);
        
        $child1 = User::factory()->create([
            'referrer_id' => $rootUser->id,
            'network_path' => '1.2',
            'network_level' => 1
        ]);
        
        $child2 = User::factory()->create([
            'referrer_id' => $rootUser->id,
            'network_path' => '1.3',
            'network_level' => 1
        ]);

        $structure = $this->service->getNetworkStructure($rootUser->id, 3);

        $this->assertArrayHasKey('root_user', $structure);
        $this->assertArrayHasKey('network_tree', $structure);
        $this->assertArrayHasKey('statistics', $structure);
        
        $this->assertEquals($rootUser->id, $structure['root_user']['id']);
    }

    public function test_get_commission_details_returns_comprehensive_data()
    {
        $referrer = User::factory()->create();
        $referee = User::factory()->create();
        
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id
        ]);

        $details = $this->service->getCommissionDetails($commission->id);

        $this->assertArrayHasKey('commission', $details);
        $this->assertArrayHasKey('referrer_stats', $details);
        $this->assertArrayHasKey('referee_stats', $details);
        $this->assertArrayHasKey('related_commissions', $details);
        $this->assertArrayHasKey('audit_trail', $details);
    }

    public function test_export_commissions_generates_csv_response()
    {
        $referrer = User::factory()->create(['name' => 'John Doe']);
        $referee = User::factory()->create(['name' => 'Jane Smith']);
        
        ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'amount' => 100.00,
            'commission_type' => 'REFERRAL',
            'level' => 1
        ]);

        $response = $this->service->exportCommissions([]);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_compliance_score_calculation_considers_commission_ratio()
    {
        // Create test data that would affect compliance score
        $user = User::factory()->create(['total_invested' => 10000]);
        
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'amount' => 2000, // 20% of total invested
            'status' => 'paid'
        ]);

        $score = $this->service->getComplianceScore();

        $this->assertIsFloat($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_get_performance_metrics_returns_volume_and_advancement_data()
    {
        $user = User::factory()->create();
        
        TeamVolume::factory()->create([
            'user_id' => $user->id,
            'team_volume' => 5000,
            'period_start' => now()->startOfMonth()
        ]);

        $metrics = $this->service->getPerformanceMetrics('month');

        $this->assertArrayHasKey('volume_performance', $metrics);
        $this->assertArrayHasKey('tier_advancement', $metrics);
        $this->assertArrayHasKey('retention_rates', $metrics);
        $this->assertArrayHasKey('conversion_rates', $metrics);
    }

    public function test_get_pending_commissions_count_returns_accurate_count()
    {
        $user = User::factory()->create();
        
        ReferralCommission::factory()->count(5)->create([
            'referrer_id' => $user->id,
            'status' => 'pending'
        ]);
        
        ReferralCommission::factory()->count(3)->create([
            'referrer_id' => $user->id,
            'status' => 'paid'
        ]);

        $count = $this->service->getPendingCommissionsCount();

        $this->assertEquals(5, $count);
    }

    public function test_recalculate_network_structure_calls_mlm_service()
    {
        $mlmServiceMock = Mockery::mock(MLMCommissionService::class);
        $mlmServiceMock->shouldReceive('rebuildNetworkPaths')->once();
        
        $this->app->instance(MLMCommissionService::class, $mlmServiceMock);

        $this->service->recalculateNetworkStructure();

        // Assertion is handled by Mockery's shouldReceive expectation
        $this->assertTrue(true);
    }
}