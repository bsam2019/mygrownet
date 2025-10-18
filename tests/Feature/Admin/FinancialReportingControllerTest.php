<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\Subscription;
use App\Models\PaymentTransaction;
use App\Services\FinancialReportingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;

class FinancialReportingControllerTest extends TestCase
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

    public function test_admin_can_access_financial_dashboard()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.financial.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/Financial/Dashboard')
                ->has('overview')
                ->has('complianceMetrics')
                ->has('sustainabilityMetrics')
                ->has('commissionCapTracking')
                ->has('revenueAnalysis')
        );
    }

    public function test_non_admin_cannot_access_financial_dashboard()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('admin.financial.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_financial_reports()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.financial.reports', [
            'type' => 'comprehensive',
            'period' => 'month'
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/Financial/Reports')
                ->has('reportData')
                ->has('availableReports')
        );
    }

    public function test_admin_can_view_sustainability_metrics()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.financial.sustainability'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/Financial/Sustainability')
                ->has('sustainabilityData')
                ->has('commissionCapData')
                ->has('riskAssessment')
                ->has('projections')
        );
    }

    public function test_admin_can_view_compliance_dashboard()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.financial.compliance'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/Financial/Compliance')
                ->has('complianceData')
                ->has('regulatoryMetrics')
                ->has('auditTrail')
                ->has('complianceAlerts')
        );
    }

    public function test_admin_can_generate_custom_report()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.financial.generate-custom-report'), [
            'report_type' => 'comprehensive',
            'period' => 'month',
            'include_metrics' => ['revenue', 'commissions', 'compliance'],
            'format' => 'json'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'report_info',
                'metrics'
            ]
        ]);
    }

    public function test_custom_report_validation()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.financial.generate-custom-report'), [
            'report_type' => '', // Invalid
            'period' => 'invalid_period', // Invalid
            'format' => 'invalid_format' // Invalid
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['report_type', 'period', 'format']);
    }

    public function test_admin_can_get_commission_cap_data()
    {
        $this->actingAs($this->admin);

        // Create test data
        $user = User::factory()->create();
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'amount' => 1000,
            'status' => 'paid'
        ]);

        $response = $this->getJson(route('admin.financial.commission-cap-data', [
            'period' => 'month'
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'current_settings',
                'enforcement_history',
                'violation_incidents',
                'impact_analysis'
            ]
        ]);
    }

    public function test_admin_can_update_commission_cap()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.financial.update-commission-cap'), [
            'cap_percentage' => 20.0,
            'enforcement_level' => 'strict',
            'alert_threshold' => 18.0,
            'reason' => 'Regulatory compliance adjustment'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
    }

    public function test_commission_cap_update_validation()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.financial.update-commission-cap'), [
            'cap_percentage' => 150, // Invalid (over 100)
            'enforcement_level' => 'invalid', // Invalid
            'alert_threshold' => -5, // Invalid (negative)
            'reason' => '' // Invalid (empty)
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cap_percentage', 'enforcement_level', 'alert_threshold', 'reason']);
    }

    public function test_admin_can_get_financial_projections()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.financial.get-projections'), [
            'projection_type' => 'revenue',
            'time_horizon' => '6_months',
            'scenario' => 'realistic'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonHasKey('data');
    }

    public function test_projections_validation()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.financial.get-projections'), [
            'projection_type' => 'invalid_type',
            'time_horizon' => 'invalid_horizon',
            'scenario' => 'invalid_scenario'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['projection_type', 'time_horizon', 'scenario']);
    }

    public function test_admin_can_export_financial_report()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.financial.export-report', [
            'report_type' => 'comprehensive',
            'period' => 'month',
            'format' => 'csv'
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_get_real_time_metrics()
    {
        $this->actingAs($this->admin);

        $response = $this->getJson(route('admin.financial.real-time-metrics'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'current_revenue',
                'pending_commissions',
                'commission_ratio',
                'active_members',
                'cash_flow_status',
                'compliance_status'
            ],
            'timestamp'
        ]);
    }

    public function test_admin_can_generate_compliance_report()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.financial.generate-compliance-report'), [
            'report_period' => 'quarter',
            'compliance_areas' => ['commission_caps', 'payout_timing', 'volume_legitimacy']
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'report_period',
                'generated_at',
                'compliance_areas',
                'overall_compliance_score'
            ]
        ]);
    }

    public function test_admin_can_get_financial_health_score()
    {
        $this->actingAs($this->admin);

        $response = $this->getJson(route('admin.financial.financial-health-score', [
            'period' => 'month'
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'overall_score',
                'grade',
                'component_scores',
                'recommendations'
            ]
        ]);
    }

    public function test_admin_can_get_revenue_breakdown()
    {
        $this->actingAs($this->admin);

        $response = $this->getJson(route('admin.financial.revenue-breakdown', [
            'period' => 'month',
            'breakdown_type' => 'source'
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonHasKey('data');
    }

    public function test_admin_can_schedule_automated_report()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.financial.schedule-report'), [
            'report_type' => 'comprehensive',
            'frequency' => 'monthly',
            'recipients' => ['admin@example.com', 'finance@example.com'],
            'format' => 'pdf',
            'include_metrics' => ['revenue', 'commissions', 'compliance']
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
    }

    public function test_schedule_report_validation()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.financial.schedule-report'), [
            'report_type' => '',
            'frequency' => 'invalid_frequency',
            'recipients' => ['invalid_email'],
            'format' => 'invalid_format'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['report_type', 'frequency', 'recipients.0', 'format']);
    }

    public function test_admin_can_get_commission_distribution()
    {
        $this->actingAs($this->admin);

        // Create test commissions
        $user = User::factory()->create();
        ReferralCommission::factory()->count(5)->create([
            'referrer_id' => $user->id,
            'level' => 1,
            'status' => 'paid'
        ]);

        $response = $this->getJson(route('admin.financial.commission-distribution', [
            'period' => 'month',
            'analysis_type' => 'by_level'
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonHasKey('data');
    }

    public function test_admin_can_get_cost_analysis()
    {
        $this->actingAs($this->admin);

        $response = $this->getJson(route('admin.financial.cost-analysis', [
            'period' => 'month'
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'commission_costs',
                'operational_costs',
                'asset_costs',
                'technology_costs',
                'total_costs',
                'cost_efficiency_metrics'
            ]
        ]);
    }

    public function test_middleware_blocks_non_admin_users()
    {
        $this->actingAs($this->regularUser);

        $routes = [
            'admin.financial.dashboard',
            'admin.financial.reports',
            'admin.financial.sustainability',
            'admin.financial.compliance'
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
            ['method' => 'post', 'route' => 'admin.financial.generate-custom-report', 'data' => []],
            ['method' => 'post', 'route' => 'admin.financial.update-commission-cap', 'data' => []],
            ['method' => 'get', 'route' => 'admin.financial.real-time-metrics', 'data' => []],
        ];

        foreach ($ajaxRoutes as $routeInfo) {
            $response = $this->{$routeInfo['method'] . 'Json'}(route($routeInfo['route']), $routeInfo['data']);
            $response->assertStatus(403);
        }
    }

    public function test_commission_cap_compliance_calculation()
    {
        $this->actingAs($this->admin);

        // Create test data with known commission ratio
        $user = User::factory()->create();
        
        // Create subscription revenue
        Subscription::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => 'active'
        ]);
        
        // Create commissions (should be 20% of revenue = 200)
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'amount' => 200,
            'status' => 'paid'
        ]);

        $response = $this->getJson(route('admin.financial.commission-cap-data'));

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Commission ratio should be 20% (compliant with 25% cap)
        $this->assertEquals('compliant', $data['compliance_status']);
    }

    public function test_export_report_with_different_formats()
    {
        $this->actingAs($this->admin);

        $formats = ['csv', 'pdf', 'excel'];

        foreach ($formats as $format) {
            $response = $this->get(route('admin.financial.export-report', [
                'report_type' => 'comprehensive',
                'period' => 'month',
                'format' => $format
            ]));

            $response->assertStatus(200);
            // Each format should return appropriate content type
        }
    }
}