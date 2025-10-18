<?php

namespace Tests\Integration;

use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\TeamVolume;
use App\Models\InvestmentTier;
use App\Services\MLMAdministrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MLMAdministrationWorkflowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected MLMAdministrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->service = new MLMAdministrationService();
    }

    public function test_complete_commission_oversight_workflow()
    {
        // Step 1: Create a network with commissions
        $rootUser = User::factory()->create(['network_path' => '1', 'network_level' => 0]);
        $level1User = User::factory()->create([
            'referrer_id' => $rootUser->id,
            'network_path' => '1.2',
            'network_level' => 1
        ]);
        $level2User = User::factory()->create([
            'referrer_id' => $level1User->id,
            'network_path' => '1.2.3',
            'network_level' => 2
        ]);

        // Create commissions at different levels
        $commission1 = ReferralCommission::factory()->create([
            'referrer_id' => $rootUser->id,
            'referred_id' => $level1User->id,
            'level' => 1,
            'amount' => 120.00,
            'status' => 'pending',
            'commission_type' => 'REFERRAL'
        ]);

        $commission2 = ReferralCommission::factory()->create([
            'referrer_id' => $level1User->id,
            'referred_id' => $level2User->id,
            'level' => 1,
            'amount' => 60.00,
            'status' => 'pending',
            'commission_type' => 'REFERRAL'
        ]);

        // Step 2: Admin views dashboard and sees pending commissions
        $this->actingAs($this->admin);
        
        $overview = $this->service->getOverviewMetrics('month');
        $this->assertEquals(2, $overview['pending_commissions']['count']);
        $this->assertEquals(180.00, $overview['pending_commissions']['amount']);

        // Step 3: Admin reviews commission details
        $commissionDetails = $this->service->getCommissionDetails($commission1->id);
        $this->assertEquals($commission1->id, $commissionDetails['commission']->id);
        $this->assertArrayHasKey('referrer_stats', $commissionDetails);
        $this->assertArrayHasKey('referee_stats', $commissionDetails);

        // Step 4: Admin adjusts one commission
        $adjustmentResult = $this->service->adjustCommission(
            $commission1->id,
            150.00,
            'Performance bonus adjustment',
            $this->admin->id
        );

        $commission1->refresh();
        $this->assertEquals(150.00, $commission1->amount);
        $this->assertEquals($this->admin->id, $commission1->adjusted_by);

        // Step 5: Admin processes commissions in bulk
        $bulkResult = $this->service->processPendingCommissions(
            [$commission1->id, $commission2->id],
            'approve',
            $this->admin->id
        );

        $this->assertCount(2, $bulkResult['processed']);
        $this->assertEmpty($bulkResult['failed']);

        // Step 6: Verify commissions are now paid
        $commission1->refresh();
        $commission2->refresh();
        
        $this->assertEquals('paid', $commission1->status);
        $this->assertEquals('paid', $commission2->status);
        $this->assertNotNull($commission1->paid_at);
        $this->assertNotNull($commission2->paid_at);

        // Step 7: Verify updated metrics
        $updatedOverview = $this->service->getOverviewMetrics('month');
        $this->assertEquals(0, $updatedOverview['pending_commissions']['count']);
        $this->assertEquals(210.00, $updatedOverview['total_commissions']['current']); // 150 + 60
    }

    public function test_network_analysis_and_performance_monitoring_workflow()
    {
        // Create a complex network structure
        $rootUser = User::factory()->create([
            'network_path' => '1',
            'network_level' => 0,
            'current_team_volume' => 50000
        ]);

        // Create multiple levels
        for ($i = 1; $i <= 3; $i++) {
            $levelUsers = User::factory()->count(2)->create([
                'network_level' => $i,
                'current_team_volume' => 10000 - ($i * 2000)
            ]);

            foreach ($levelUsers as $user) {
                ReferralCommission::factory()->create([
                    'referrer_id' => $rootUser->id,
                    'referred_id' => $user->id,
                    'level' => $i,
                    'amount' => 100 - ($i * 10),
                    'status' => 'paid'
                ]);
            }
        }

        // Create team volumes
        TeamVolume::factory()->create([
            'user_id' => $rootUser->id,
            'team_volume' => 50000,
            'personal_volume' => 5000,
            'period_start' => now()->startOfMonth()
        ]);

        $this->actingAs($this->admin);

        // Test network analysis
        $networkAnalytics = $this->service->getNetworkAnalytics();
        $this->assertArrayHasKey('depth_distribution', $networkAnalytics);
        $this->assertArrayHasKey('top_performers', $networkAnalytics);

        // Test network structure for specific user
        $networkStructure = $this->service->getNetworkStructure($rootUser->id, 5);
        $this->assertEquals($rootUser->id, $networkStructure['root_user']['id']);
        $this->assertArrayHasKey('network_tree', $networkStructure);
        $this->assertArrayHasKey('statistics', $networkStructure);

        // Test performance metrics
        $performanceMetrics = $this->service->getPerformanceMetrics('month');
        $this->assertArrayHasKey('volume_performance', $performanceMetrics);
        $this->assertArrayHasKey('tier_advancement', $performanceMetrics);
    }

    public function test_compliance_monitoring_and_alerts_workflow()
    {
        // Create scenario that triggers compliance alerts
        $users = User::factory()->count(10)->create();

        // Create high volume of pending commissions (trigger alert)
        foreach ($users as $user) {
            ReferralCommission::factory()->count(15)->create([
                'referrer_id' => $user->id,
                'status' => 'pending',
                'amount' => 100.00
            ]);
        }

        $this->actingAs($this->admin);

        // Check system alerts
        $alerts = $this->service->getSystemAlerts();
        $this->assertNotEmpty($alerts);

        // Should have high pending commissions alert
        $pendingAlert = collect($alerts)->firstWhere('type', 'warning');
        $this->assertNotNull($pendingAlert);
        $this->assertStringContainsString('High Pending Commissions', $pendingAlert['title']);

        // Test compliance score calculation
        $complianceScore = $this->service->getComplianceScore();
        $this->assertIsFloat($complianceScore);
        $this->assertGreaterThanOrEqual(0, $complianceScore);
        $this->assertLessThanOrEqual(100, $complianceScore);
    }

    public function test_export_functionality_workflow()
    {
        // Create test data for export
        $referrer = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $referee = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        ReferralCommission::factory()->count(5)->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'commission_type' => 'REFERRAL',
            'status' => 'paid'
        ]);

        $this->actingAs($this->admin);

        // Test commission export
        $commissionExport = $this->service->exportCommissions([
            'status' => 'paid',
            'type' => 'REFERRAL'
        ]);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $commissionExport);

        // Test network analysis export
        $networkExport = $this->service->exportNetworkAnalysis($referrer->id, 3);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $networkExport);
    }

    public function test_real_time_dashboard_updates_workflow()
    {
        // Create initial state
        $user = User::factory()->create();
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
            'amount' => 100.00
        ]);

        $this->actingAs($this->admin);

        // Get initial dashboard data
        $initialOverview = $this->service->getOverviewMetrics('month');
        $this->assertEquals(1, $initialOverview['pending_commissions']['count']);

        // Process the commission
        $commission = ReferralCommission::where('referrer_id', $user->id)->first();
        $this->service->processPendingCommissions(
            [$commission->id],
            'approve',
            $this->admin->id
        );

        // Get updated dashboard data
        $updatedOverview = $this->service->getOverviewMetrics('month');
        $this->assertEquals(0, $updatedOverview['pending_commissions']['count']);
        $this->assertEquals(100.00, $updatedOverview['total_commissions']['current']);

        // Test recent activity
        $recentActivity = $this->service->getRecentActivity();
        $this->assertIsArray($recentActivity);
        $this->assertNotEmpty($recentActivity);
    }

    public function test_error_handling_and_recovery_workflow()
    {
        $this->actingAs($this->admin);

        // Test handling of non-existent commission
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->service->getCommissionDetails(999999);
    }

    public function test_bulk_processing_with_mixed_results_workflow()
    {
        $referrer = User::factory()->create();
        $referee1 = User::factory()->create();
        $referee2 = User::factory()->create();

        // Create one valid pending commission
        $validCommission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee1->id,
            'status' => 'pending'
        ]);

        // Create one already processed commission (should fail)
        $processedCommission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee2->id,
            'status' => 'paid'
        ]);

        $this->actingAs($this->admin);

        // Attempt to process both
        $result = $this->service->processPendingCommissions(
            [$validCommission->id, $processedCommission->id],
            'approve',
            $this->admin->id
        );

        // Should have mixed results
        $this->assertCount(1, $result['processed']); // Only the valid one
        $this->assertArrayHasKey('summary', $result);
        $this->assertEquals(1, $result['summary']['processed']);
        
        // Verify the valid commission was processed
        $validCommission->refresh();
        $this->assertEquals('paid', $validCommission->status);
    }

    public function test_commission_adjustment_with_balance_update_workflow()
    {
        $referrer = User::factory()->create(['balance' => 1000.00]);
        $referee = User::factory()->create();

        // Create a paid commission
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'amount' => 100.00,
            'status' => 'paid'
        ]);

        $this->actingAs($this->admin);

        // Adjust the commission upward
        $this->service->adjustCommission(
            $commission->id,
            150.00,
            'Performance bonus',
            $this->admin->id
        );

        // Verify commission was adjusted
        $commission->refresh();
        $this->assertEquals(150.00, $commission->amount);

        // Verify user balance was updated with the difference
        $referrer->refresh();
        $this->assertEquals(1050.00, $referrer->balance); // 1000 + (150 - 100)

        // Now adjust downward
        $this->service->adjustCommission(
            $commission->id,
            75.00,
            'Correction needed',
            $this->admin->id
        );

        // Verify balance reflects the reduction
        $referrer->refresh();
        $this->assertEquals(975.00, $referrer->balance); // 1050 + (75 - 150)
    }
}