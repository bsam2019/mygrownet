<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\EarningsProjectionService;
use App\Models\InvestmentTier;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EarningsProjectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private EarningsProjectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EarningsProjectionService();
        
        // Create test investment tiers
        $this->createTestTiers();
    }

    private function createTestTiers(): void
    {
        $tiers = [
            ['name' => 'Bronze', 'minimum_amount' => 150],
            ['name' => 'Silver', 'minimum_amount' => 300],
            ['name' => 'Gold', 'minimum_amount' => 500],
            ['name' => 'Diamond', 'minimum_amount' => 1000],
            ['name' => 'Elite', 'minimum_amount' => 1500],
        ];

        foreach ($tiers as $tier) {
            InvestmentTier::create($tier);
        }
    }

    /** @test */
    public function it_calculates_basic_projection_for_bronze_tier()
    {
        $projection = $this->service->calculateProjection('Bronze', 5, 3, 12);

        $this->assertArrayHasKey('tier', $projection);
        $this->assertArrayHasKey('scenario', $projection);
        $this->assertArrayHasKey('monthly_projections', $projection);
        $this->assertArrayHasKey('total_earnings', $projection);
        $this->assertArrayHasKey('average_monthly', $projection);
        $this->assertArrayHasKey('income_breakdown', $projection);

        $this->assertEquals('Bronze', $projection['tier']);
        $this->assertEquals(5, $projection['scenario']['active_referrals']);
        $this->assertEquals(3, $projection['scenario']['network_depth']);
        $this->assertEquals(12, $projection['scenario']['months']);
        $this->assertCount(12, $projection['monthly_projections']);
    }

    /** @test */
    public function it_calculates_different_earnings_for_different_tiers()
    {
        $bronzeProjection = $this->service->calculateProjection('Bronze', 5, 3, 1);
        $eliteProjection = $this->service->calculateProjection('Elite', 5, 3, 1);

        // Elite tier should have higher earnings than Bronze
        $this->assertGreaterThan(
            $bronzeProjection['total_earnings'],
            $eliteProjection['total_earnings']
        );

        // Elite should have higher subscription share
        $this->assertGreaterThan(
            $bronzeProjection['monthly_projections'][0]['subscription_share'],
            $eliteProjection['monthly_projections'][0]['subscription_share']
        );
    }

    /** @test */
    public function it_includes_all_income_sources_in_monthly_projections()
    {
        $projection = $this->service->calculateProjection('Gold', 5, 3, 1);
        $monthlyProjection = $projection['monthly_projections'][0];

        $this->assertArrayHasKey('subscription_share', $monthlyProjection);
        $this->assertArrayHasKey('multilevel_commissions', $monthlyProjection);
        $this->assertArrayHasKey('team_volume_bonus', $monthlyProjection);
        $this->assertArrayHasKey('profit_sharing', $monthlyProjection);
        $this->assertArrayHasKey('achievement_bonus', $monthlyProjection);
        $this->assertArrayHasKey('total', $monthlyProjection);

        // Verify total is sum of all components
        $expectedTotal = $monthlyProjection['subscription_share'] +
                        $monthlyProjection['multilevel_commissions'] +
                        $monthlyProjection['team_volume_bonus'] +
                        $monthlyProjection['profit_sharing'] +
                        $monthlyProjection['achievement_bonus'];

        $this->assertEquals($expectedTotal, $monthlyProjection['total']);
    }

    /** @test */
    public function it_calculates_multilevel_commissions_correctly()
    {
        $projection = $this->service->calculateProjection('Silver', 3, 2, 1);
        $monthlyProjection = $projection['monthly_projections'][0];

        // Should have multilevel commissions for 2 levels
        $this->assertGreaterThan(0, $monthlyProjection['multilevel_commissions']);

        // Test with deeper network
        $deeperProjection = $this->service->calculateProjection('Silver', 3, 5, 1);
        $deeperMonthly = $deeperProjection['monthly_projections'][0];

        // Deeper network should generate more commissions
        $this->assertGreaterThan(
            $monthlyProjection['multilevel_commissions'],
            $deeperMonthly['multilevel_commissions']
        );
    }

    /** @test */
    public function it_applies_team_volume_bonuses_correctly_by_tier()
    {
        $bronzeProjection = $this->service->calculateProjection('Bronze', 5, 3, 1);
        $silverProjection = $this->service->calculateProjection('Silver', 5, 3, 1);
        $goldProjection = $this->service->calculateProjection('Gold', 5, 3, 1);

        $bronzeBonus = $bronzeProjection['monthly_projections'][0]['team_volume_bonus'];
        $silverBonus = $silverProjection['monthly_projections'][0]['team_volume_bonus'];
        $goldBonus = $goldProjection['monthly_projections'][0]['team_volume_bonus'];

        // Bronze should have no team volume bonus
        $this->assertEquals(0, $bronzeBonus);

        // Silver should have team volume bonus
        $this->assertGreaterThan(0, $silverBonus);

        // Gold should have higher team volume bonus than Silver
        $this->assertGreaterThan($silverBonus, $goldBonus);
    }

    /** @test */
    public function it_includes_quarterly_profit_sharing_for_eligible_tiers()
    {
        // Gold tier should get quarterly profit sharing in month 3
        $goldProjection = $this->service->calculateProjection('Gold', 5, 3, 3);
        
        $month1 = $goldProjection['monthly_projections'][0]['profit_sharing'];
        $month3 = $goldProjection['monthly_projections'][2]['profit_sharing'];

        $this->assertEquals(0, $month1); // No profit sharing in month 1
        $this->assertGreaterThan(0, $month3); // Profit sharing in month 3

        // Bronze tier should not get profit sharing
        $bronzeProjection = $this->service->calculateProjection('Bronze', 5, 3, 3);
        $bronzeMonth3 = $bronzeProjection['monthly_projections'][2]['profit_sharing'];
        
        $this->assertEquals(0, $bronzeMonth3);
    }

    /** @test */
    public function it_includes_achievement_bonuses_in_first_month()
    {
        $silverProjection = $this->service->calculateProjection('Silver', 5, 3, 2);
        
        $month1Achievement = $silverProjection['monthly_projections'][0]['achievement_bonus'];
        $month2Achievement = $silverProjection['monthly_projections'][1]['achievement_bonus'];

        // Achievement bonus should be in first month only
        $this->assertGreaterThan(0, $month1Achievement);
        $this->assertEquals(0, $month2Achievement);

        // Bronze should not have achievement bonus
        $bronzeProjection = $this->service->calculateProjection('Bronze', 5, 3, 1);
        $bronzeAchievement = $bronzeProjection['monthly_projections'][0]['achievement_bonus'];
        
        $this->assertEquals(0, $bronzeAchievement);
    }

    /** @test */
    public function it_provides_correct_earning_ranges()
    {
        $ranges = $this->service->getEarningRanges();

        $this->assertArrayHasKey('Bronze', $ranges);
        $this->assertArrayHasKey('Silver', $ranges);
        $this->assertArrayHasKey('Gold', $ranges);
        $this->assertArrayHasKey('Diamond', $ranges);
        $this->assertArrayHasKey('Elite', $ranges);

        // Each range should have min and max
        foreach ($ranges as $tier => $range) {
            $this->assertArrayHasKey('min', $range);
            $this->assertArrayHasKey('max', $range);
            $this->assertGreaterThan($range['min'], $range['max']);
        }

        // Higher tiers should have higher ranges
        $this->assertGreaterThan($ranges['Bronze']['max'], $ranges['Silver']['min']);
        $this->assertGreaterThan($ranges['Silver']['max'], $ranges['Gold']['min']);
        $this->assertGreaterThan($ranges['Gold']['max'], $ranges['Diamond']['min']);
        $this->assertGreaterThan($ranges['Diamond']['max'], $ranges['Elite']['min']);
    }

    /** @test */
    public function it_generates_multiple_scenarios_correctly()
    {
        $scenarios = $this->service->generateScenarios('Gold');

        $this->assertArrayHasKey('conservative', $scenarios);
        $this->assertArrayHasKey('realistic', $scenarios);
        $this->assertArrayHasKey('optimistic', $scenarios);

        // Conservative should have lowest earnings
        $conservative = $scenarios['conservative']['total_earnings'];
        $realistic = $scenarios['realistic']['total_earnings'];
        $optimistic = $scenarios['optimistic']['total_earnings'];

        $this->assertLessThan($realistic, $conservative);
        $this->assertLessThan($optimistic, $realistic);
    }

    /** @test */
    public function it_includes_correct_income_breakdown_percentages()
    {
        $projection = $this->service->calculateProjection('Diamond', 5, 3, 1);
        $breakdown = $projection['income_breakdown'];

        $this->assertEquals(25, $breakdown['multilevel_commissions']);
        $this->assertEquals(15, $breakdown['team_volume_bonuses']);
        $this->assertEquals(30, $breakdown['subscription_shares']);
        $this->assertEquals(20, $breakdown['profit_sharing']);
        $this->assertEquals(10, $breakdown['achievement_bonuses']);

        // Total should be 100%
        $total = array_sum($breakdown);
        $this->assertEquals(100, $total);
    }

    /** @test */
    public function it_handles_invalid_tier_gracefully()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid tier: InvalidTier');

        $this->service->calculateProjection('InvalidTier', 5, 3, 12);
    }

    /** @test */
    public function it_calculates_total_and_average_correctly()
    {
        $projection = $this->service->calculateProjection('Gold', 5, 3, 6);

        $calculatedTotal = array_sum(array_column($projection['monthly_projections'], 'total'));
        $calculatedAverage = $calculatedTotal / 6;

        $this->assertEquals($calculatedTotal, $projection['total_earnings']);
        $this->assertEquals($calculatedAverage, $projection['average_monthly']);
    }

    /** @test */
    public function it_shows_growth_over_time()
    {
        $projection = $this->service->calculateProjection('Silver', 5, 3, 12);

        // Team volume should grow over time, affecting bonuses
        $month1Total = $projection['monthly_projections'][0]['total'];
        $month6Total = $projection['monthly_projections'][5]['total'];
        $month12Total = $projection['monthly_projections'][11]['total'];

        // Later months should generally have higher earnings due to team volume growth
        $this->assertGreaterThanOrEqual($month1Total, $month6Total);
        $this->assertGreaterThanOrEqual($month6Total, $month12Total);
    }

    /** @test */
    public function it_validates_commission_calculation_accuracy()
    {
        // Test specific commission calculation for known scenario
        $projection = $this->service->calculateProjection('Silver', 2, 2, 1);
        $monthlyProjection = $projection['monthly_projections'][0];

        // With 2 referrals at 2 levels:
        // Level 1: 2 referrals * 300 (Silver package) * 12% = 72
        // Level 2: 5 referrals (2 * 2.5) * 300 * 6% = 90
        // Expected total: 162
        $expectedCommissions = (2 * 300 * 0.12) + (5 * 300 * 0.06);
        
        $this->assertEquals($expectedCommissions, $monthlyProjection['multilevel_commissions']);
    }

    /** @test */
    public function it_ensures_compliance_with_commission_caps()
    {
        // Test that commission calculations don't exceed reasonable limits
        $projection = $this->service->calculateProjection('Elite', 10, 5, 1);
        $monthlyProjection = $projection['monthly_projections'][0];

        // Total monthly earnings should be within realistic ranges
        $this->assertLessThanOrEqual(100000, $monthlyProjection['total']); // Max K100,000 per month
        
        // Multilevel commissions should not exceed 50% of total earnings
        $commissionPercentage = ($monthlyProjection['multilevel_commissions'] / $monthlyProjection['total']) * 100;
        $this->assertLessThanOrEqual(50, $commissionPercentage);
    }
}