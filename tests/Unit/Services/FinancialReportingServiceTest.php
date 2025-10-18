<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\Subscription;
use App\Models\PaymentTransaction;
use App\Services\FinancialReportingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class FinancialReportingServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected FinancialReportingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FinancialReportingService();
    }

    public function test_get_financial_overview_returns_correct_structure()
    {
        // Create test data
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'status' => 'active'
        ]);
        
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'amount' => 100,
            'status' => 'paid'
        ]);

        $overview = $this->service->getFinancialOverview('month');

        $this->assertIsArray($overview);
        $this->assertArrayHasKey('revenue_metrics', $overview);
        $this->assertArrayHasKey('commission_metrics', $overview);
        $this->assertArrayHasKey('profitability', $overview);
        $this->assertArrayHasKey('cash_flow', $overview);
        $this->assertArrayHasKey('growth_metrics', $overview);
        $this->assertArrayHasKey('financial_health', $overview);
    }

    public function test_get_compliance_metrics_returns_all_required_metrics()
    {
        $metrics = $this->service->getComplianceMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('commission_cap_compliance', $metrics);
        $this->assertArrayHasKey('payout_timing_compliance', $metrics);
        $this->assertArrayHasKey('volume_legitimacy_score', $metrics);
        $this->assertArrayHasKey('financial_transparency', $metrics);
        $this->assertArrayHasKey('regulatory_adherence', $metrics);
    }

    public function test_get_sustainability_metrics_calculates_correctly()
    {
        // Create test data
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => 'active'
        ]);

        $metrics = $this->service->getSustainabilityMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('commission_to_revenue_ratio', $metrics);
        $this->assertArrayHasKey('member_retention_rate', $metrics);
        $this->assertArrayHasKey('revenue_diversification', $metrics);
        $this->assertArrayHasKey('cost_efficiency', $metrics);
        $this->assertArrayHasKey('long_term_viability', $metrics);
    }

    public function test_get_commission_cap_tracking_identifies_violations()
    {
        $user = User::factory()->create();
        
        // Create revenue
        Subscription::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => 'active'
        ]);
        
        // Create commissions that exceed 25% cap (300 > 250)
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'amount' => 300,
            'status' => 'paid'
        ]);

        $tracking = $this->service->getCommissionCapTracking();

        $this->assertIsArray($tracking);
        $this->assertArrayHasKey('current_ratio', $tracking);
        $this->assertArrayHasKey('cap_threshold', $tracking);
        $this->assertArrayHasKey('compliance_status', $tracking);
        $this->assertEquals(25.0, $tracking['cap_threshold']);
        
        // Should detect violation (30% > 25%)
        $this->assertEquals('violation', $tracking['compliance_status']);
    }

    public function test_get_commission_cap_tracking_shows_compliance()
    {
        $user = User::factory()->create();
        
        // Create revenue
        Subscription::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => 'active'
        ]);
        
        // Create commissions within 25% cap (200 < 250)
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'amount' => 200,
            'status' => 'paid'
        ]);

        $tracking = $this->service->getCommissionCapTracking();

        // Should show compliance (20% < 25%)
        $this->assertEquals('compliant', $tracking['compliance_status']);
        $this->assertLessThanOrEqual(25.0, $tracking['current_ratio']);
    }

    public function test_get_revenue_analysis_breaks_down_sources()
    {
        $user = User::factory()->create();
        
        // Create different revenue sources
        Subscription::factory()->count(3)->create([
            'user_id' => $user->id,
            'amount' => 500,
            'status' => 'active'
        ]);

        $analysis = $this->service->getRevenueAnalysis('month');

        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('subscription_revenue', $analysis);
        $this->assertArrayHasKey('tier_upgrade_revenue', $analysis);
        $this->assertArrayHasKey('community_project_revenue', $analysis);
        $this->assertArrayHasKey('asset_income', $analysis);
        $this->assertArrayHasKey('revenue_by_tier', $analysis);
        $this->assertArrayHasKey('revenue_trends', $analysis);
    }

    public function test_generate_report_handles_different_types()
    {
        $reportTypes = ['comprehensive', 'commission_analysis', 'sustainability', 'compliance', 'revenue_breakdown'];

        foreach ($reportTypes as $type) {
            $report = $this->service->generateReport($type, 'month');
            $this->assertIsArray($report);
        }
    }

    public function test_generate_report_throws_exception_for_invalid_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->generateReport('invalid_type', 'month');
    }

    public function test_get_available_reports_returns_all_report_types()
    {
        $reports = $this->service->getAvailableReports();

        $this->assertIsArray($reports);
        $this->assertArrayHasKey('comprehensive', $reports);
        $this->assertArrayHasKey('commission_analysis', $reports);
        $this->assertArrayHasKey('sustainability', $reports);
        $this->assertArrayHasKey('compliance', $reports);
        $this->assertArrayHasKey('revenue_breakdown', $reports);

        // Each report should have required structure
        foreach ($reports as $report) {
            $this->assertArrayHasKey('name', $report);
            $this->assertArrayHasKey('description', $report);
            $this->assertArrayHasKey('metrics', $report);
        }
    }

    public function test_get_sustainability_analysis_assesses_all_areas()
    {
        $analysis = $this->service->getSustainabilityAnalysis('month');

        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('financial_sustainability', $analysis);
        $this->assertArrayHasKey('member_growth_sustainability', $analysis);
        $this->assertArrayHasKey('commission_sustainability', $analysis);
        $this->assertArrayHasKey('revenue_sustainability', $analysis);
        $this->assertArrayHasKey('risk_factors', $analysis);
        $this->assertArrayHasKey('sustainability_score', $analysis);
    }

    public function test_get_commission_cap_analysis_provides_comprehensive_data()
    {
        $analysis = $this->service->getCommissionCapAnalysis('month');

        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('current_enforcement', $analysis);
        $this->assertArrayHasKey('historical_compliance', $analysis);
        $this->assertArrayHasKey('cap_impact_analysis', $analysis);
        $this->assertArrayHasKey('optimization_recommendations', $analysis);
    }

    public function test_get_risk_assessment_identifies_all_risk_categories()
    {
        $assessment = $this->service->getRiskAssessment();

        $this->assertIsArray($assessment);
        $this->assertArrayHasKey('financial_risks', $assessment);
        $this->assertArrayHasKey('operational_risks', $assessment);
        $this->assertArrayHasKey('compliance_risks', $assessment);
        $this->assertArrayHasKey('market_risks', $assessment);
        $this->assertArrayHasKey('overall_risk_score', $assessment);
    }

    public function test_get_financial_projections_generates_all_projection_types()
    {
        $projections = $this->service->getFinancialProjections('month');

        $this->assertIsArray($projections);
        $this->assertArrayHasKey('revenue_projections', $projections);
        $this->assertArrayHasKey('commission_projections', $projections);
        $this->assertArrayHasKey('growth_projections', $projections);
        $this->assertArrayHasKey('sustainability_projections', $projections);
    }

    public function test_get_compliance_analysis_covers_all_areas()
    {
        $analysis = $this->service->getComplianceAnalysis('month');

        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('commission_cap_analysis', $analysis);
        $this->assertArrayHasKey('payout_timing_analysis', $analysis);
        $this->assertArrayHasKey('volume_legitimacy_analysis', $analysis);
        $this->assertArrayHasKey('transparency_analysis', $analysis);
        $this->assertArrayHasKey('overall_compliance_score', $analysis);
    }

    public function test_get_regulatory_metrics_provides_compliance_scores()
    {
        $metrics = $this->service->getRegulatoryMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('mlm_compliance_score', $metrics);
        $this->assertArrayHasKey('financial_reporting_compliance', $metrics);
        $this->assertArrayHasKey('consumer_protection_compliance', $metrics);
        $this->assertArrayHasKey('data_protection_compliance', $metrics);
    }

    public function test_get_audit_trail_tracks_all_activities()
    {
        $trail = $this->service->getAuditTrail('month');

        $this->assertIsArray($trail);
        $this->assertArrayHasKey('financial_transactions', $trail);
        $this->assertArrayHasKey('commission_adjustments', $trail);
        $this->assertArrayHasKey('system_changes', $trail);
        $this->assertArrayHasKey('compliance_actions', $trail);
    }

    public function test_get_compliance_alerts_detects_violations()
    {
        $user = User::factory()->create();
        
        // Create scenario that should trigger alerts
        Subscription::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => 'active'
        ]);
        
        // Create excessive commissions (30% > 25% cap)
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'amount' => 300,
            'status' => 'paid'
        ]);

        $alerts = $this->service->getComplianceAlerts();

        $this->assertIsArray($alerts);
        
        // Should detect commission cap violation
        $commissionCapAlert = collect($alerts)->firstWhere('category', 'commission_cap');
        $this->assertNotNull($commissionCapAlert);
        $this->assertEquals('critical', $commissionCapAlert['type']);
        $this->assertTrue($commissionCapAlert['action_required']);
    }

    public function test_generate_custom_report_with_json_format()
    {
        $report = $this->service->generateCustomReport(
            'comprehensive',
            'month',
            null,
            null,
            ['revenue', 'commissions'],
            'json'
        );

        $this->assertIsArray($report);
        $this->assertArrayHasKey('report_info', $report);
        $this->assertArrayHasKey('metrics', $report);
        $this->assertArrayHasKey('revenue', $report['metrics']);
        $this->assertArrayHasKey('commissions', $report['metrics']);
    }

    public function test_generate_custom_report_with_custom_date_range()
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        $report = $this->service->generateCustomReport(
            'comprehensive',
            'custom',
            $startDate,
            $endDate,
            ['revenue'],
            'json'
        );

        $this->assertIsArray($report);
        $this->assertEquals($startDate, $report['report_info']['date_range']['from']);
        $this->assertEquals($endDate, $report['report_info']['date_range']['to']);
    }

    public function test_update_commission_cap_stores_settings()
    {
        $settings = $this->service->updateCommissionCap(
            20.0,
            'strict',
            18.0,
            'Regulatory compliance update',
            1
        );

        $this->assertIsArray($settings);
        $this->assertEquals(20.0, $settings['cap_percentage']);
        $this->assertEquals('strict', $settings['enforcement_level']);
        $this->assertEquals(18.0, $settings['alert_threshold']);
        $this->assertEquals('Regulatory compliance update', $settings['reason']);
        $this->assertEquals(1, $settings['updated_by']);
    }

    public function test_generate_projections_with_different_scenarios()
    {
        $scenarios = ['conservative', 'realistic', 'optimistic'];
        
        foreach ($scenarios as $scenario) {
            $projections = $this->service->generateProjections(
                'revenue',
                '6_months',
                $scenario
            );
            
            $this->assertIsArray($projections);
        }
    }

    public function test_generate_projections_throws_exception_for_invalid_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->generateProjections('invalid_type', '6_months', 'realistic');
    }

    public function test_export_report_returns_streamed_response()
    {
        $response = $this->service->exportReport('comprehensive', 'month', 'csv');

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
    }

    public function test_export_report_throws_exception_for_invalid_format()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->exportReport('comprehensive', 'month', 'invalid_format');
    }

    public function test_get_real_time_metrics_provides_current_data()
    {
        $metrics = $this->service->getRealTimeMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('current_revenue', $metrics);
        $this->assertArrayHasKey('pending_commissions', $metrics);
        $this->assertArrayHasKey('commission_ratio', $metrics);
        $this->assertArrayHasKey('active_members', $metrics);
        $this->assertArrayHasKey('cash_flow_status', $metrics);
        $this->assertArrayHasKey('compliance_status', $metrics);
    }

    public function test_generate_compliance_report_covers_specified_areas()
    {
        $areas = ['commission_caps', 'payout_timing', 'volume_legitimacy'];
        
        $report = $this->service->generateComplianceReport('quarter', $areas);

        $this->assertIsArray($report);
        $this->assertArrayHasKey('report_period', $report);
        $this->assertArrayHasKey('compliance_areas', $report);
        $this->assertArrayHasKey('overall_compliance_score', $report);
        
        foreach ($areas as $area) {
            $this->assertArrayHasKey($area, $report['compliance_areas']);
        }
    }

    public function test_calculate_financial_health_score_provides_comprehensive_assessment()
    {
        $healthScore = $this->service->calculateFinancialHealthScore('month');

        $this->assertIsArray($healthScore);
        $this->assertArrayHasKey('overall_score', $healthScore);
        $this->assertArrayHasKey('grade', $healthScore);
        $this->assertArrayHasKey('component_scores', $healthScore);
        $this->assertArrayHasKey('recommendations', $healthScore);
        
        // Score should be between 0 and 100
        $this->assertGreaterThanOrEqual(0, $healthScore['overall_score']);
        $this->assertLessThanOrEqual(100, $healthScore['overall_score']);
    }

    public function test_get_revenue_breakdown_handles_different_breakdown_types()
    {
        $breakdownTypes = ['source', 'tier', 'geography', 'time'];
        
        foreach ($breakdownTypes as $type) {
            $breakdown = $this->service->getRevenueBreakdown('month', $type);
            $this->assertIsArray($breakdown);
        }
    }

    public function test_get_revenue_breakdown_throws_exception_for_invalid_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->getRevenueBreakdown('month', 'invalid_type');
    }

    public function test_schedule_automated_report_creates_schedule()
    {
        $schedule = $this->service->scheduleAutomatedReport(
            'comprehensive',
            'monthly',
            ['admin@example.com'],
            'pdf',
            ['revenue', 'commissions'],
            1
        );

        $this->assertIsArray($schedule);
        $this->assertArrayHasKey('report_type', $schedule);
        $this->assertArrayHasKey('frequency', $schedule);
        $this->assertArrayHasKey('recipients', $schedule);
        $this->assertArrayHasKey('format', $schedule);
        $this->assertArrayHasKey('scheduled_by', $schedule);
        $this->assertArrayHasKey('next_run', $schedule);
        $this->assertEquals('active', $schedule['status']);
    }

    public function test_get_commission_distribution_handles_different_analysis_types()
    {
        $analysisTypes = ['by_level', 'by_tier', 'by_type', 'by_member'];
        
        foreach ($analysisTypes as $type) {
            $distribution = $this->service->getCommissionDistribution('month', $type);
            $this->assertIsArray($distribution);
        }
    }

    public function test_get_commission_distribution_throws_exception_for_invalid_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->getCommissionDistribution('month', 'invalid_type');
    }

    public function test_get_cost_analysis_provides_comprehensive_breakdown()
    {
        $analysis = $this->service->getCostAnalysis('month');

        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('commission_costs', $analysis);
        $this->assertArrayHasKey('operational_costs', $analysis);
        $this->assertArrayHasKey('asset_costs', $analysis);
        $this->assertArrayHasKey('technology_costs', $analysis);
        $this->assertArrayHasKey('total_costs', $analysis);
        $this->assertArrayHasKey('cost_efficiency_metrics', $analysis);
    }
}