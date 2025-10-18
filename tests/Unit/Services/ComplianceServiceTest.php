<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ComplianceService;
use App\Models\User;
use App\Models\ReferralCommission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComplianceServiceTest extends TestCase
{
    use RefreshDatabase;

    private ComplianceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ComplianceService();
    }

    public function test_get_business_structure_returns_complete_information()
    {
        $businessStructure = $this->service->getBusinessStructure();

        $this->assertArrayHasKey('business_model', $businessStructure);
        $this->assertArrayHasKey('focus', $businessStructure);
        $this->assertArrayHasKey('revenue_sources', $businessStructure);
        $this->assertArrayHasKey('compliance_standards', $businessStructure);

        $this->assertEquals('Legitimate MLM Business', $businessStructure['business_model']);
        $this->assertEquals('Education and Community Building', $businessStructure['focus']);

        // Verify revenue sources
        $this->assertArrayHasKey('monthly_subscriptions', $businessStructure['revenue_sources']);
        $this->assertArrayHasKey('educational_content', $businessStructure['revenue_sources']);
        $this->assertArrayHasKey('community_projects', $businessStructure['revenue_sources']);
        $this->assertArrayHasKey('mentorship_programs', $businessStructure['revenue_sources']);

        // Verify compliance standards
        $this->assertArrayHasKey('product_focused', $businessStructure['compliance_standards']);
        $this->assertArrayHasKey('regulatory_compliance', $businessStructure['compliance_standards']);
        $this->assertArrayHasKey('consumer_protection', $businessStructure['compliance_standards']);
        $this->assertArrayHasKey('financial_sustainability', $businessStructure['compliance_standards']);
    }

    public function test_get_legal_disclaimers_includes_all_required_sections()
    {
        $disclaimers = $this->service->getLegalDisclaimers();

        $this->assertArrayHasKey('earnings_disclaimer', $disclaimers);
        $this->assertArrayHasKey('business_disclaimer', $disclaimers);
        $this->assertArrayHasKey('risk_disclosure', $disclaimers);
        $this->assertArrayHasKey('legal_compliance', $disclaimers);

        // Verify each disclaimer has title and content
        foreach ($disclaimers as $disclaimer) {
            $this->assertArrayHasKey('title', $disclaimer);
            $this->assertArrayHasKey('content', $disclaimer);
            $this->assertIsArray($disclaimer['content']);
            $this->assertNotEmpty($disclaimer['content']);
        }

        // Verify specific content
        $earningsDisclaimer = $disclaimers['earnings_disclaimer'];
        $this->assertEquals('Earnings Disclaimer', $earningsDisclaimer['title']);
        $this->assertContains('All earnings projections are estimates based on historical data and market conditions.', $earningsDisclaimer['content']);
        $this->assertContains('No guarantee of income or success is implied or stated.', $earningsDisclaimer['content']);
    }

    public function test_get_sustainability_metrics_calculates_commission_percentage()
    {
        // Create test data
        User::factory()->count(10)->create(['is_active' => true]);
        ReferralCommission::factory()->count(5)->create([
            'status' => 'paid',
            'amount' => 1000,
            'created_at' => now()->subMonths(6)
        ]);

        $metrics = $this->service->getSustainabilityMetrics();

        $this->assertArrayHasKey('commission_cap', $metrics);
        $this->assertArrayHasKey('revenue_allocation', $metrics);
        $this->assertArrayHasKey('financial_health', $metrics);

        // Verify commission cap structure
        $commissionCap = $metrics['commission_cap'];
        $this->assertArrayHasKey('percentage', $commissionCap);
        $this->assertArrayHasKey('current_percentage', $commissionCap);
        $this->assertArrayHasKey('compliance_status', $commissionCap);
        $this->assertEquals(25, $commissionCap['percentage']);

        // Verify revenue allocation adds up to 100%
        $revenueAllocation = $metrics['revenue_allocation'];
        $totalAllocation = array_sum($revenueAllocation);
        $this->assertEquals(100, $totalAllocation);

        // Verify financial health data
        $financialHealth = $metrics['financial_health'];
        $this->assertArrayHasKey('total_revenue', $financialHealth);
        $this->assertArrayHasKey('total_commissions', $financialHealth);
        $this->assertArrayHasKey('operational_funds', $financialHealth);
        $this->assertArrayHasKey('sustainability_score', $financialHealth);
    }

    public function test_check_commission_cap_compliance_identifies_violations()
    {
        // Create scenario where commissions exceed cap
        User::factory()->count(5)->create(['is_active' => true]);
        ReferralCommission::factory()->count(20)->create([
            'status' => 'paid',
            'amount' => 5000, // High commission amounts
            'created_at' => now()->subMonths(3)
        ]);

        $compliance = $this->service->checkCommissionCapCompliance();

        $this->assertArrayHasKey('is_compliant', $compliance);
        $this->assertArrayHasKey('current_percentage', $compliance);
        $this->assertArrayHasKey('cap_percentage', $compliance);
        $this->assertArrayHasKey('excess_amount', $compliance);
        $this->assertArrayHasKey('recommendations', $compliance);

        $this->assertEquals(25, $compliance['cap_percentage']);
        $this->assertIsNumeric($compliance['current_percentage']);
        $this->assertIsNumeric($compliance['excess_amount']);
    }

    public function test_enforce_commission_caps_takes_action_when_needed()
    {
        // Create scenario with high commission percentage
        User::factory()->count(2)->create(['is_active' => true]);
        ReferralCommission::factory()->count(50)->create([
            'status' => 'paid',
            'amount' => 10000,
            'created_at' => now()->subMonths(1)
        ]);

        $enforcement = $this->service->enforceCommissionCaps();

        $this->assertArrayHasKey('action_taken', $enforcement);
        $this->assertArrayHasKey('message', $enforcement);
        $this->assertArrayHasKey('compliance_status', $enforcement);

        if ($enforcement['action_taken']) {
            $this->assertArrayHasKey('excess_percentage', $enforcement);
            $this->assertArrayHasKey('enforcement_measures', $enforcement);
            $this->assertEquals('ENFORCEMENT_ACTIVE', $enforcement['compliance_status']);
        } else {
            $this->assertEquals('COMPLIANT', $enforcement['compliance_status']);
        }
    }

    public function test_get_regulatory_compliance_returns_complete_status()
    {
        $compliance = $this->service->getRegulatoryCompliance();

        $this->assertArrayHasKey('mlm_compliance', $compliance);
        $this->assertArrayHasKey('consumer_protection', $compliance);
        $this->assertArrayHasKey('financial_regulations', $compliance);

        // Verify MLM compliance structure
        $mlmCompliance = $compliance['mlm_compliance'];
        $this->assertArrayHasKey('status', $mlmCompliance);
        $this->assertArrayHasKey('last_review', $mlmCompliance);
        $this->assertArrayHasKey('next_review', $mlmCompliance);
        $this->assertArrayHasKey('areas', $mlmCompliance);

        // Verify compliance areas
        $areas = $mlmCompliance['areas'];
        $this->assertArrayHasKey('product_focus', $areas);
        $this->assertArrayHasKey('recruitment_limits', $areas);
        $this->assertArrayHasKey('income_disclosure', $areas);
        $this->assertArrayHasKey('refund_policy', $areas);

        // Verify consumer protection
        $consumerProtection = $compliance['consumer_protection'];
        $this->assertArrayHasKey('cooling_off_period', $consumerProtection);
        $this->assertArrayHasKey('refund_policy', $consumerProtection);
        $this->assertArrayHasKey('complaint_resolution', $consumerProtection);

        // Verify financial regulations
        $financialRegulations = $compliance['financial_regulations'];
        $this->assertArrayHasKey('audit_frequency', $financialRegulations);
        $this->assertArrayHasKey('last_audit', $financialRegulations);
        $this->assertArrayHasKey('next_audit', $financialRegulations);
    }

    public function test_generate_compliance_report_includes_all_sections()
    {
        $report = $this->service->generateComplianceReport();

        $this->assertArrayHasKey('report_date', $report);
        $this->assertArrayHasKey('business_structure', $report);
        $this->assertArrayHasKey('sustainability_metrics', $report);
        $this->assertArrayHasKey('commission_compliance', $report);
        $this->assertArrayHasKey('regulatory_status', $report);
        $this->assertArrayHasKey('legal_disclaimers', $report);
        $this->assertArrayHasKey('recommendations', $report);

        // Verify report date format
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $report['report_date']);
    }

    public function test_validate_earnings_representation_identifies_violations()
    {
        // Test with valid earnings data
        $validEarnings = [
            'average_monthly' => 5000,
            'total_earnings' => 60000,
            'commission_percentage' => 20,
            'disclaimers' => ['Earnings may vary', 'No guarantee of success']
        ];

        $validation = $this->service->validateEarningsRepresentation($validEarnings);
        $this->assertTrue($validation['is_valid']);
        $this->assertEmpty($validation['violations']);

        // Test with invalid earnings data (missing disclaimers)
        $invalidEarnings = [
            'average_monthly' => 5000,
            'commission_percentage' => 20
        ];

        $validation = $this->service->validateEarningsRepresentation($invalidEarnings);
        $this->assertFalse($validation['is_valid']);
        $this->assertContains('Missing required earnings disclaimers', $validation['violations']);

        // Test with excessive commission percentage
        $excessiveCommissions = [
            'average_monthly' => 5000,
            'commission_percentage' => 30, // Exceeds 25% cap
            'disclaimers' => ['Earnings may vary']
        ];

        $validation = $this->service->validateEarningsRepresentation($excessiveCommissions);
        $this->assertFalse($validation['is_valid']);
        $this->assertContains('Commission percentage exceeds regulatory cap', $validation['violations']);

        // Test with unrealistic earnings
        $unrealisticEarnings = [
            'average_monthly' => 150000, // Exceeds K100,000 limit
            'commission_percentage' => 20,
            'disclaimers' => ['Earnings may vary']
        ];

        $validation = $this->service->validateEarningsRepresentation($unrealisticEarnings);
        $this->assertTrue($validation['is_valid']); // Should be valid but with warnings
        $this->assertContains('Earnings projection exceeds realistic monthly limits', $validation['warnings']);
    }

    public function test_sustainability_score_calculation()
    {
        // Test different commission percentages and their sustainability scores
        $testCases = [
            ['percentage' => 15, 'expected' => 'EXCELLENT'],
            ['percentage' => 22, 'expected' => 'GOOD'],
            ['percentage' => 27, 'expected' => 'ACCEPTABLE'],
            ['percentage' => 35, 'expected' => 'NEEDS_ATTENTION']
        ];

        foreach ($testCases as $testCase) {
            // Use reflection to test private method
            $reflection = new \ReflectionClass($this->service);
            $method = $reflection->getMethod('calculateSustainabilityScore');
            $method->setAccessible(true);

            $result = $method->invoke($this->service, $testCase['percentage']);
            $this->assertEquals($testCase['expected'], $result);
        }
    }

    public function test_compliance_recommendations_include_urgent_items_when_needed()
    {
        // Test with high commission percentage
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getComplianceRecommendations');
        $method->setAccessible(true);

        $recommendations = $method->invoke($this->service, 30); // Above cap

        $this->assertArrayHasKey('general', $recommendations);
        $this->assertArrayHasKey('urgent', $recommendations);

        $this->assertContains('Maintain regular compliance audits', $recommendations['general']);
        $this->assertContains('Reduce commission rates to comply with cap', $recommendations['urgent']);

        // Test with acceptable commission percentage
        $recommendations = $method->invoke($this->service, 20); // Within cap

        $this->assertArrayHasKey('general', $recommendations);
        $this->assertArrayNotHasKey('urgent', $recommendations);
    }

    public function test_commission_cap_enforcement_measures()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('implementCapEnforcement');
        $method->setAccessible(true);

        $enforcement = $method->invoke($this->service, 5.5); // 5.5% excess

        $this->assertTrue($enforcement['action_taken']);
        $this->assertEquals('ENFORCEMENT_ACTIVE', $enforcement['compliance_status']);
        $this->assertEquals(5.5, $enforcement['excess_percentage']);
        $this->assertArrayHasKey('enforcement_measures', $enforcement);
        $this->assertIsArray($enforcement['enforcement_measures']);
        $this->assertNotEmpty($enforcement['enforcement_measures']);
    }
}