<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\Services\TierConfigurationService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TierConfigurationServiceTest extends TestCase
{
    private TierConfigurationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test configuration
        Config::set('modules.growfinance', [
            'name' => 'GrowFinance',
            'tiers' => [
                'free' => [
                    'name' => 'Free',
                    'price_monthly' => 0,
                    'price_annual' => 0,
                    'limits' => [
                        'accounts' => 3,
                        'transactions_per_month' => 50,
                        'customers' => 10,
                    ],
                    'features' => ['basic_dashboard', 'manual_transactions'],
                    'reports' => ['income_statement'],
                ],
                'basic' => [
                    'name' => 'Basic',
                    'price_monthly' => 99,
                    'price_annual' => 990,
                    'limits' => [
                        'accounts' => 10,
                        'transactions_per_month' => 500,
                        'customers' => 100,
                    ],
                    'features' => ['basic_dashboard', 'manual_transactions', 'invoicing', 'receipt_upload'],
                    'reports' => ['income_statement', 'balance_sheet', 'cash_flow'],
                ],
                'professional' => [
                    'name' => 'Professional',
                    'price_monthly' => 249,
                    'price_annual' => 2490,
                    'popular' => true,
                    'limits' => [
                        'accounts' => -1, // unlimited
                        'transactions_per_month' => -1,
                        'customers' => -1,
                    ],
                    'features' => ['basic_dashboard', 'manual_transactions', 'invoicing', 'receipt_upload', 'budgeting', 'api_access'],
                    'reports' => ['income_statement', 'balance_sheet', 'cash_flow', 'budget_variance', 'custom_reports'],
                ],
            ],
            'usage_metrics' => [
                'accounts' => ['label' => 'Accounts', 'unit' => 'accounts'],
                'transactions_per_month' => ['label' => 'Monthly Transactions', 'unit' => 'transactions'],
                'customers' => ['label' => 'Customers', 'unit' => 'customers'],
            ],
        ]);

        $this->service = new TierConfigurationService();
    }


    /** @test */
    public function it_gets_module_config(): void
    {
        $config = $this->service->getModuleConfig('growfinance');

        $this->assertNotNull($config);
        $this->assertEquals('GrowFinance', $config['name']);
        $this->assertArrayHasKey('tiers', $config);
    }

    /** @test */
    public function it_returns_null_for_unknown_module(): void
    {
        $config = $this->service->getModuleConfig('unknown_module');

        $this->assertNull($config);
    }

    /** @test */
    public function it_gets_all_tiers(): void
    {
        $tiers = $this->service->getTiers('growfinance');

        $this->assertCount(3, $tiers);
        $this->assertArrayHasKey('free', $tiers);
        $this->assertArrayHasKey('basic', $tiers);
        $this->assertArrayHasKey('professional', $tiers);
    }

    /** @test */
    public function it_gets_tier_config(): void
    {
        $tierConfig = $this->service->getTierConfig('growfinance', 'basic');

        $this->assertNotNull($tierConfig);
        $this->assertEquals('Basic', $tierConfig['name']);
        $this->assertEquals(99, $tierConfig['price_monthly']);
    }

    /** @test */
    public function it_returns_null_for_unknown_tier(): void
    {
        $tierConfig = $this->service->getTierConfig('growfinance', 'enterprise');

        $this->assertNull($tierConfig);
    }

    /** @test */
    public function it_gets_tier_limits(): void
    {
        $limits = $this->service->getTierLimits('growfinance', 'free');

        $this->assertEquals(3, $limits['accounts']);
        $this->assertEquals(50, $limits['transactions_per_month']);
        $this->assertEquals(10, $limits['customers']);
    }

    /** @test */
    public function it_gets_specific_limit(): void
    {
        $limit = $this->service->getLimit('growfinance', 'basic', 'accounts');

        $this->assertEquals(10, $limit);
    }

    /** @test */
    public function it_returns_unlimited_as_negative_one(): void
    {
        $limit = $this->service->getLimit('growfinance', 'professional', 'accounts');

        $this->assertEquals(-1, $limit);
        $this->assertTrue($this->service->isUnlimited($limit));
    }

    /** @test */
    public function it_returns_zero_for_unknown_limit(): void
    {
        $limit = $this->service->getLimit('growfinance', 'free', 'unknown_limit');

        $this->assertEquals(0, $limit);
        $this->assertTrue($this->service->isNotAvailable($limit));
    }

    /** @test */
    public function it_gets_tier_features(): void
    {
        $features = $this->service->getTierFeatures('growfinance', 'basic');

        $this->assertContains('basic_dashboard', $features);
        $this->assertContains('invoicing', $features);
        $this->assertContains('receipt_upload', $features);
    }

    /** @test */
    public function it_checks_feature_availability(): void
    {
        $this->assertTrue($this->service->hasFeature('growfinance', 'basic', 'invoicing'));
        $this->assertFalse($this->service->hasFeature('growfinance', 'free', 'invoicing'));
        $this->assertTrue($this->service->hasFeature('growfinance', 'professional', 'api_access'));
    }

    /** @test */
    public function it_gets_tier_reports(): void
    {
        $reports = $this->service->getTierReports('growfinance', 'professional');

        $this->assertContains('income_statement', $reports);
        $this->assertContains('budget_variance', $reports);
        $this->assertContains('custom_reports', $reports);
    }

    /** @test */
    public function it_checks_report_availability(): void
    {
        $this->assertTrue($this->service->hasReport('growfinance', 'basic', 'balance_sheet'));
        $this->assertFalse($this->service->hasReport('growfinance', 'free', 'balance_sheet'));
    }

    /** @test */
    public function it_gets_tier_pricing(): void
    {
        $pricing = $this->service->getTierPricing('growfinance', 'basic');

        $this->assertEquals(99, $pricing['monthly']);
        $this->assertEquals(990, $pricing['annual']);
        $this->assertEquals('ZMW', $pricing['currency']);
    }

    /** @test */
    public function it_gets_required_tier_for_feature(): void
    {
        $tier = $this->service->getRequiredTierForFeature('growfinance', 'invoicing');

        $this->assertEquals('basic', $tier);
    }

    /** @test */
    public function it_gets_required_tier_for_limit(): void
    {
        // Need at least 100 customers
        $tier = $this->service->getRequiredTierForLimit('growfinance', 'customers', 100);

        $this->assertEquals('basic', $tier);

        // Need unlimited customers
        $tier = $this->service->getRequiredTierForLimit('growfinance', 'customers', 1000);

        $this->assertEquals('professional', $tier);
    }

    /** @test */
    public function it_gets_all_tiers_for_display(): void
    {
        $tiers = $this->service->getAllTiersForDisplay('growfinance');

        $this->assertCount(3, $tiers);
        
        $professional = $tiers['professional'];
        $this->assertEquals('Professional', $professional['name']);
        $this->assertTrue($professional['popular']);
        $this->assertEquals(249, $professional['price_monthly']);
    }

    /** @test */
    public function it_gets_usage_metrics(): void
    {
        $metrics = $this->service->getUsageMetrics('growfinance');

        $this->assertArrayHasKey('accounts', $metrics);
        $this->assertEquals('Accounts', $metrics['accounts']['label']);
    }
}
