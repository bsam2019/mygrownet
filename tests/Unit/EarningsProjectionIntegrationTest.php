<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\EarningsProjectionService;

class EarningsProjectionIntegrationTest extends TestCase
{
    private EarningsProjectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EarningsProjectionService();
    }

    public function test_service_provides_earning_ranges()
    {
        $ranges = $this->service->getEarningRanges();

        $this->assertIsArray($ranges);
        $this->assertArrayHasKey('Bronze', $ranges);
        $this->assertArrayHasKey('Elite', $ranges);
        
        foreach ($ranges as $tier => $range) {
            $this->assertArrayHasKey('min', $range);
            $this->assertArrayHasKey('max', $range);
            $this->assertGreaterThan($range['min'], $range['max']);
        }
    }

    public function test_service_generates_scenarios()
    {
        $scenarios = $this->service->generateScenarios('Gold');

        $this->assertIsArray($scenarios);
        $this->assertArrayHasKey('conservative', $scenarios);
        $this->assertArrayHasKey('realistic', $scenarios);
        $this->assertArrayHasKey('optimistic', $scenarios);

        foreach ($scenarios as $scenario) {
            $this->assertArrayHasKey('total_earnings', $scenario);
            $this->assertArrayHasKey('average_monthly', $scenario);
            $this->assertArrayHasKey('income_breakdown', $scenario);
        }
    }

    public function test_income_breakdown_percentages_sum_to_100()
    {
        $scenarios = $this->service->generateScenarios('Silver');
        
        foreach ($scenarios as $scenario) {
            $breakdown = $scenario['income_breakdown'];
            $total = array_sum($breakdown);
            $this->assertEquals(100, $total);
        }
    }

    public function test_higher_tiers_have_higher_earning_potential()
    {
        $bronzeScenarios = $this->service->generateScenarios('Bronze');
        $eliteScenarios = $this->service->generateScenarios('Elite');

        $bronzeRealistic = $bronzeScenarios['realistic']['total_earnings'];
        $eliteRealistic = $eliteScenarios['realistic']['total_earnings'];

        $this->assertGreaterThan($bronzeRealistic, $eliteRealistic);
    }

    public function test_commission_calculations_are_reasonable()
    {
        $ranges = $this->service->getEarningRanges();
        
        foreach ($ranges as $tier => $range) {
            $scenarios = $this->service->generateScenarios($tier);
            $realisticEarnings = $scenarios['realistic']['average_monthly'];
            
            // Earnings should fall within the expected range
            $this->assertGreaterThanOrEqual($range['min'], $realisticEarnings);
            $this->assertLessThanOrEqual($range['max'], $realisticEarnings);
        }
    }

    public function test_earnings_grow_with_network_size()
    {
        $smallNetwork = $this->service->generateScenarios('Gold')['conservative'];
        $largeNetwork = $this->service->generateScenarios('Gold')['optimistic'];

        $this->assertGreaterThan(
            $smallNetwork['total_earnings'],
            $largeNetwork['total_earnings']
        );
    }
}