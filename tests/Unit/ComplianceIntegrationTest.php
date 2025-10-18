<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ComplianceService;

class ComplianceIntegrationTest extends TestCase
{
    private ComplianceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ComplianceService();
    }

    public function test_compliance_service_provides_business_structure()
    {
        $businessStructure = $this->service->getBusinessStructure();

        $this->assertIsArray($businessStructure);
        $this->assertArrayHasKey('business_model', $businessStructure);
        $this->assertArrayHasKey('focus', $businessStructure);
        $this->assertArrayHasKey('revenue_sources', $businessStructure);
        $this->assertArrayHasKey('compliance_standards', $businessStructure);

        $this->assertEquals('Legitimate MLM Business', $businessStructure['business_model']);
        $this->assertEquals('Education and Community Building', $businessStructure['focus']);
    }

    public function test_compliance_service_provides_legal_disclaimers()
    {
        $disclaimers = $this->service->getLegalDisclaimers();

        $this->assertIsArray($disclaimers);
        $this->assertArrayHasKey('earnings_disclaimer', $disclaimers);
        $this->assertArrayHasKey('business_disclaimer', $disclaimers);
        $this->assertArrayHasKey('risk_disclosure', $disclaimers);
        $this->assertArrayHasKey('legal_compliance', $disclaimers);

        // Verify each disclaimer has required structure
        foreach ($disclaimers as $disclaimer) {
            $this->assertArrayHasKey('title', $disclaimer);
            $this->assertArrayHasKey('content', $disclaimer);
            $this->assertIsArray($disclaimer['content']);
            $this->assertNotEmpty($disclaimer['content']);
        }
    }

    public function test_compliance_service_validates_earnings_representations()
    {
        // Test valid earnings data
        $validEarnings = [
            'average_monthly' => 5000,
            'commission_percentage' => 20,
            'disclaimers' => ['Earnings may vary']
        ];

        $validation = $this->service->validateEarningsRepresentation($validEarnings);
        $this->assertTrue($validation['is_valid']);
        $this->assertEmpty($validation['violations']);

        // Test invalid earnings data (missing disclaimers)
        $invalidEarnings = [
            'average_monthly' => 5000,
            'commission_percentage' => 20
        ];

        $validation = $this->service->validateEarningsRepresentation($invalidEarnings);
        $this->assertFalse($validation['is_valid']);
        $this->assertContains('Missing required earnings disclaimers', $validation['violations']);

        // Test excessive commission percentage
        $excessiveCommissions = [
            'commission_percentage' => 30, // Exceeds 25% cap
            'disclaimers' => ['Earnings may vary']
        ];

        $validation = $this->service->validateEarningsRepresentation($excessiveCommissions);
        $this->assertFalse($validation['is_valid']);
        $this->assertContains('Commission percentage exceeds regulatory cap', $validation['violations']);
    }

    public function test_compliance_service_generates_complete_report()
    {
        $report = $this->service->generateComplianceReport();

        $this->assertIsArray($report);
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

    public function test_commission_cap_compliance_checking()
    {
        $compliance = $this->service->checkCommissionCapCompliance();

        $this->assertIsArray($compliance);
        $this->assertArrayHasKey('is_compliant', $compliance);
        $this->assertArrayHasKey('current_percentage', $compliance);
        $this->assertArrayHasKey('cap_percentage', $compliance);
        $this->assertArrayHasKey('excess_amount', $compliance);
        $this->assertArrayHasKey('recommendations', $compliance);

        $this->assertEquals(25, $compliance['cap_percentage']);
        $this->assertIsBool($compliance['is_compliant']);
        $this->assertIsNumeric($compliance['current_percentage']);
        $this->assertIsNumeric($compliance['excess_amount']);
    }

    public function test_regulatory_compliance_status()
    {
        $compliance = $this->service->getRegulatoryCompliance();

        $this->assertIsArray($compliance);
        $this->assertArrayHasKey('mlm_compliance', $compliance);
        $this->assertArrayHasKey('consumer_protection', $compliance);
        $this->assertArrayHasKey('financial_regulations', $compliance);

        // Verify MLM compliance structure
        $mlmCompliance = $compliance['mlm_compliance'];
        $this->assertArrayHasKey('status', $mlmCompliance);
        $this->assertArrayHasKey('areas', $mlmCompliance);
        $this->assertEquals('COMPLIANT', $mlmCompliance['status']);

        // Verify consumer protection
        $consumerProtection = $compliance['consumer_protection'];
        $this->assertArrayHasKey('cooling_off_period', $consumerProtection);
        $this->assertArrayHasKey('refund_policy', $consumerProtection);
        $this->assertEquals('7 days', $consumerProtection['cooling_off_period']);
        $this->assertEquals('Full refund within 30 days', $consumerProtection['refund_policy']);
    }

    public function test_sustainability_metrics_calculation()
    {
        $metrics = $this->service->getSustainabilityMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('commission_cap', $metrics);
        $this->assertArrayHasKey('revenue_allocation', $metrics);
        $this->assertArrayHasKey('financial_health', $metrics);

        // Verify commission cap
        $commissionCap = $metrics['commission_cap'];
        $this->assertEquals(25, $commissionCap['percentage']);
        $this->assertContains($commissionCap['compliance_status'], ['COMPLIANT', 'EXCEEDS_CAP']);

        // Verify revenue allocation sums to 100%
        $revenueAllocation = $metrics['revenue_allocation'];
        $totalAllocation = array_sum($revenueAllocation);
        $this->assertEquals(100, $totalAllocation);

        // Verify financial health
        $financialHealth = $metrics['financial_health'];
        $this->assertArrayHasKey('sustainability_score', $financialHealth);
        $this->assertContains($financialHealth['sustainability_score'], 
            ['EXCELLENT', 'GOOD', 'ACCEPTABLE', 'NEEDS_ATTENTION']);
    }

    public function test_earnings_validation_with_warnings()
    {
        // Test earnings that should generate warnings but still be valid
        $highEarnings = [
            'average_monthly' => 150000, // Very high but not invalid
            'commission_percentage' => 20,
            'disclaimers' => ['Earnings may vary']
        ];

        $validation = $this->service->validateEarningsRepresentation($highEarnings);
        $this->assertTrue($validation['is_valid']);
        $this->assertNotEmpty($validation['warnings']);
        $this->assertContains('Earnings projection exceeds realistic monthly limits', $validation['warnings']);
    }

    public function test_commission_cap_enforcement()
    {
        $enforcement = $this->service->enforceCommissionCaps();

        $this->assertIsArray($enforcement);
        $this->assertArrayHasKey('action_taken', $enforcement);
        $this->assertArrayHasKey('message', $enforcement);
        $this->assertArrayHasKey('compliance_status', $enforcement);

        $this->assertIsBool($enforcement['action_taken']);
        $this->assertContains($enforcement['compliance_status'], ['COMPLIANT', 'ENFORCEMENT_ACTIVE']);
    }
}