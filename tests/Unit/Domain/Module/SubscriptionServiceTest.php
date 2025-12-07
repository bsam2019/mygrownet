<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\Services\ModuleAccessService;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Domain\Module\Services\UsageLimitService;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Models\User;
use Mockery;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    private SubscriptionService $service;
    private UsageLimitService $usageLimitService;
    private TierConfigurationService $tierConfig;
    private ModuleAccessService $accessService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->usageLimitService = Mockery::mock(UsageLimitService::class);
        $this->tierConfig = Mockery::mock(TierConfigurationService::class);
        $this->accessService = Mockery::mock(ModuleAccessService::class);
        
        $this->service = new SubscriptionService(
            $this->usageLimitService,
            $this->tierConfig,
            $this->accessService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_gets_user_tier(): void
    {
        $user = new User(['id' => 1]);
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->with($user, Mockery::type(ModuleId::class))
            ->andReturn('professional');

        $tier = $this->service->getUserTier($user, 'growfinance');

        $this->assertEquals('professional', $tier);
    }

    /** @test */
    public function it_gets_user_limits(): void
    {
        $user = new User(['id' => 1]);
        $expectedLimits = ['accounts' => 10, 'transactions' => 500];
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->with($user, Mockery::type(ModuleId::class))
            ->andReturn('basic');
        
        $this->tierConfig->shouldReceive('getTierLimits')
            ->with('growfinance', 'basic')
            ->andReturn($expectedLimits);

        $limits = $this->service->getUserLimits($user, 'growfinance');

        $this->assertEquals($expectedLimits, $limits);
    }

    /** @test */
    public function it_checks_feature_access(): void
    {
        $user = new User(['id' => 1]);
        
        $this->usageLimitService->shouldReceive('hasFeature')
            ->with($user, 'growfinance', 'invoicing')
            ->andReturn(true);

        $this->assertTrue($this->service->hasFeature($user, 'invoicing', 'growfinance'));
    }

    /** @test */
    public function it_checks_action_permission(): void
    {
        $user = new User(['id' => 1]);
        
        $this->usageLimitService->shouldReceive('hasFeature')
            ->with($user, 'growbiz', 'advanced_reports')
            ->andReturn(false);

        $this->assertFalse($this->service->canPerformAction($user, 'advanced_reports', 'growbiz'));
    }

    /** @test */
    public function it_checks_increment_permission(): void
    {
        $user = new User(['id' => 1]);
        $expectedResult = [
            'allowed' => true,
            'remaining' => 5,
            'limit' => 10,
            'used' => 5,
        ];
        
        $this->usageLimitService->shouldReceive('canIncrement')
            ->with($user, 'growfinance', 'accounts')
            ->andReturn($expectedResult);

        $result = $this->service->canIncrement($user, 'accounts', 'growfinance');

        $this->assertEquals($expectedResult, $result);
    }

    /** @test */
    public function it_checks_report_access(): void
    {
        $user = new User(['id' => 1]);
        
        $this->usageLimitService->shouldReceive('canAccessReport')
            ->with($user, 'growfinance', 'balance_sheet')
            ->andReturn(true);

        $this->assertTrue($this->service->canAccessReport($user, 'balance_sheet', 'growfinance'));
    }

    /** @test */
    public function it_gets_required_tier_for_feature(): void
    {
        $this->tierConfig->shouldReceive('getRequiredTierForFeature')
            ->with('growfinance', 'api_access')
            ->andReturn('professional');

        $tier = $this->service->getRequiredTierForFeature('api_access', 'growfinance');

        $this->assertEquals('professional', $tier);
    }

    /** @test */
    public function it_gets_usage_summary(): void
    {
        $user = new User(['id' => 1]);
        $expectedSummary = [
            'tier' => 'basic',
            'tier_name' => 'Basic',
            'metrics' => [],
        ];
        
        $this->usageLimitService->shouldReceive('getUsageSummary')
            ->with($user, 'growfinance')
            ->andReturn($expectedSummary);

        $summary = $this->service->getUsageSummary($user, 'growfinance');

        $this->assertEquals($expectedSummary, $summary);
    }

    /** @test */
    public function it_gets_upgrade_suggestions(): void
    {
        $user = new User(['id' => 1]);
        $expectedSuggestions = [
            ['metric' => 'accounts', 'suggested_tier' => 'professional'],
        ];
        
        $this->usageLimitService->shouldReceive('getUpgradeSuggestions')
            ->with($user, 'growfinance')
            ->andReturn($expectedSuggestions);

        $suggestions = $this->service->getUpgradeSuggestions($user, 'growfinance');

        $this->assertEquals($expectedSuggestions, $suggestions);
    }

    /** @test */
    public function it_gets_all_tiers(): void
    {
        $expectedTiers = [
            'free' => ['name' => 'Free'],
            'basic' => ['name' => 'Basic'],
        ];
        
        $this->tierConfig->shouldReceive('getAllTiersForDisplay')
            ->with('growfinance')
            ->andReturn($expectedTiers);

        $tiers = $this->service->getAllTiers('growfinance');

        $this->assertEquals($expectedTiers, $tiers);
    }

    /** @test */
    public function it_gets_tier_pricing(): void
    {
        $expectedPricing = [
            'monthly' => 99,
            'annual' => 990,
            'currency' => 'ZMW',
        ];
        
        $this->tierConfig->shouldReceive('getTierPricing')
            ->with('growfinance', 'basic')
            ->andReturn($expectedPricing);

        $pricing = $this->service->getTierPricing('basic', 'growfinance');

        $this->assertEquals($expectedPricing, $pricing);
    }

    /** @test */
    public function it_checks_file_upload_permission(): void
    {
        $user = new User(['id' => 1]);
        $expectedResult = ['allowed' => true];
        
        $this->usageLimitService->shouldReceive('canUploadFile')
            ->with($user, 'growfinance', 1024)
            ->andReturn($expectedResult);

        $result = $this->service->canUploadFile($user, 1024, 'growfinance');

        $this->assertEquals($expectedResult, $result);
    }

    /** @test */
    public function it_uses_default_module_id(): void
    {
        $user = new User(['id' => 1]);
        
        // When no module ID is provided, it should default to 'growfinance'
        $this->accessService->shouldReceive('getAccessLevel')
            ->with($user, Mockery::on(function ($moduleId) {
                return $moduleId instanceof ModuleId && $moduleId->value() === 'growfinance';
            }))
            ->andReturn('free');

        $tier = $this->service->getUserTier($user);

        $this->assertEquals('free', $tier);
    }
}
