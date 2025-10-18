<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\TeamVolume;
use App\Models\InvestmentTier;
use App\Services\MLMAdministrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;

class MLMAdministrationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create(['is_admin' => false]);
    }

    public function test_admin_can_access_mlm_dashboard()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.mlm.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/MLM/Dashboard')
                ->has('overview')
                ->has('commissionStats')
                ->has('networkAnalytics')
                ->has('performanceMetrics')
                ->has('alerts')
        );
    }

    public function test_non_admin_cannot_access_mlm_dashboard()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('admin.mlm.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_commissions_page()
    {
        $this->actingAs($this->admin);

        // Create test commissions
        $referrer = User::factory()->create();
        $referee = User::factory()->create();
        
        ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'status' => 'pending'
        ]);

        $response = $this->get(route('admin.mlm.commissions'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/MLM/Commissions')
                ->has('commissions')
                ->has('statistics')
                ->has('pendingCount')
        );
    }

    public function test_admin_can_filter_commissions()
    {
        $this->actingAs($this->admin);

        $referrer = User::factory()->create();
        $referee = User::factory()->create();
        
        ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'status' => 'pending',
            'commission_type' => 'REFERRAL'
        ]);

        $response = $this->get(route('admin.mlm.commissions', [
            'status' => 'pending',
            'type' => 'REFERRAL'
        ]));

        $response->assertStatus(200);
    }

    public function test_admin_can_adjust_commission()
    {
        $this->actingAs($this->admin);

        $referrer = User::factory()->create();
        $referee = User::factory()->create();
        
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'amount' => 100.00,
            'status' => 'pending'
        ]);

        $response = $this->postJson(route('admin.mlm.adjust-commission'), [
            'commission_id' => $commission->id,
            'new_amount' => 150.00,
            'reason' => 'Adjustment for special circumstances'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $commission->refresh();
        $this->assertEquals(150.00, $commission->amount);
        $this->assertEquals($this->admin->id, $commission->adjusted_by);
        $this->assertEquals('Adjustment for special circumstances', $commission->adjustment_reason);
        $this->assertNotNull($commission->adjusted_at);
    }

    public function test_commission_adjustment_validation()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.mlm.adjust-commission'), [
            'commission_id' => 999999, // Non-existent commission
            'new_amount' => -10, // Invalid amount
            'reason' => '' // Empty reason
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['commission_id', 'new_amount', 'reason']);
    }

    public function test_admin_can_process_pending_commissions_in_bulk()
    {
        $this->actingAs($this->admin);

        $referrer = User::factory()->create();
        $referee1 = User::factory()->create();
        $referee2 = User::factory()->create();
        
        $commission1 = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee1->id,
            'status' => 'pending'
        ]);
        
        $commission2 = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee2->id,
            'status' => 'pending'
        ]);

        $response = $this->postJson(route('admin.mlm.process-pending-commissions'), [
            'commission_ids' => [$commission1->id, $commission2->id],
            'action' => 'approve'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $commission1->refresh();
        $commission2->refresh();
        
        $this->assertEquals('paid', $commission1->status);
        $this->assertEquals('paid', $commission2->status);
    }

    public function test_admin_can_reject_commissions_in_bulk()
    {
        $this->actingAs($this->admin);

        $referrer = User::factory()->create();
        $referee = User::factory()->create();
        
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'status' => 'pending'
        ]);

        $response = $this->postJson(route('admin.mlm.process-pending-commissions'), [
            'commission_ids' => [$commission->id],
            'action' => 'reject'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $commission->refresh();
        $this->assertEquals('rejected', $commission->status);
        $this->assertEquals($this->admin->id, $commission->rejected_by);
        $this->assertNotNull($commission->rejected_at);
    }

    public function test_admin_can_get_commission_details()
    {
        $this->actingAs($this->admin);

        $referrer = User::factory()->create();
        $referee = User::factory()->create();
        
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id
        ]);

        $response = $this->getJson(route('admin.mlm.commission-details', $commission));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'commission',
                'referrer_stats',
                'referee_stats',
                'related_commissions',
                'audit_trail'
            ]
        ]);
    }

    public function test_admin_can_access_network_analysis()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.mlm.network-analysis'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/MLM/NetworkAnalysis')
                ->has('topPerformers')
                ->has('networkMetrics')
                ->has('growthTrends')
        );
    }

    public function test_admin_can_access_performance_monitoring()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.mlm.performance-monitoring'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/MLM/PerformanceMonitoring')
                ->has('performanceData')
                ->has('tierDistribution')
                ->has('volumeAnalytics')
                ->has('complianceMetrics')
        );
    }

    public function test_admin_can_recalculate_network()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.mlm.recalculate-network'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_admin_can_export_commissions()
    {
        $this->actingAs($this->admin);

        $referrer = User::factory()->create();
        $referee = User::factory()->create();
        
        ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id
        ]);

        $response = $this->get(route('admin.mlm.export-commissions'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_export_network_analysis()
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create();

        $response = $this->get(route('admin.mlm.export-network-analysis', [
            'user_id' => $user->id,
            'depth' => 3
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_dashboard_data_endpoint_returns_real_time_data()
    {
        $this->actingAs($this->admin);

        $response = $this->getJson(route('admin.mlm.dashboard-data'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'overview',
            'alerts',
            'recentActivity'
        ]);
    }

    public function test_commission_adjustment_updates_user_balance_if_already_paid()
    {
        $this->actingAs($this->admin);

        $referrer = User::factory()->create(['balance' => 1000.00]);
        $referee = User::factory()->create();
        
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'amount' => 100.00,
            'status' => 'paid'
        ]);

        $response = $this->postJson(route('admin.mlm.adjust-commission'), [
            'commission_id' => $commission->id,
            'new_amount' => 150.00,
            'reason' => 'Bonus adjustment'
        ]);

        $response->assertStatus(200);

        $referrer->refresh();
        $this->assertEquals(1050.00, $referrer->balance); // Original 1000 + difference of 50
    }

    public function test_bulk_processing_handles_mixed_results()
    {
        $this->actingAs($this->admin);

        $referrer = User::factory()->create();
        $referee1 = User::factory()->create();
        $referee2 = User::factory()->create();
        
        $validCommission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee1->id,
            'status' => 'pending'
        ]);
        
        $alreadyPaidCommission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee2->id,
            'status' => 'paid' // This should fail processing
        ]);

        $response = $this->postJson(route('admin.mlm.process-pending-commissions'), [
            'commission_ids' => [$validCommission->id, $alreadyPaidCommission->id],
            'action' => 'approve'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'processed',
                'failed',
                'summary'
            ]
        ]);
    }

    public function test_middleware_blocks_non_admin_users()
    {
        $this->actingAs($this->regularUser);

        $routes = [
            'admin.mlm.dashboard',
            'admin.mlm.commissions',
            'admin.mlm.network-analysis',
            'admin.mlm.performance-monitoring'
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(403);
        }
    }

    public function test_ajax_endpoints_require_admin_access()
    {
        $this->actingAs($this->regularUser);

        $ajaxRoutes = [
            ['method' => 'post', 'route' => 'admin.mlm.adjust-commission', 'data' => []],
            ['method' => 'post', 'route' => 'admin.mlm.process-pending-commissions', 'data' => []],
            ['method' => 'post', 'route' => 'admin.mlm.recalculate-network', 'data' => []],
        ];

        foreach ($ajaxRoutes as $routeInfo) {
            $response = $this->{$routeInfo['method'] . 'Json'}(route($routeInfo['route']), $routeInfo['data']);
            $response->assertStatus(403);
        }
    }
}