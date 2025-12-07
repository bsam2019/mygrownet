<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\Contracts\ModuleUsageProviderInterface;
use App\Domain\Module\Services\ModuleAccessService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Domain\Module\Services\UsageLimitService;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\TestCase;

class UsageLimitServiceTest extends TestCase
{
    private UsageLimitService $service;
    private TierConfigurationService $tierConfig;
    private $accessService;
    private $usageProvider;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test configuration
        Config::set('modules.testmodule', [
            'name' => 'Test Module',
            'tiers' => [
                'free' => [
                    'name' => 'Free',
                    'limits' => [
                        'items' => 10,
                        'storage_mb' => 0, // not available
                    ],
                    'features' => ['basic'],
                    'reports' => ['summary'],
                ],
                'basic' => [
                    'name' => 'Basic',
                    'price_monthly' => 99,
                    'limits' => [
                        'items' => 100,
                        'storage_mb' => 500,
                    ],
                    'features' => ['basic', 'advanced'],
                    'reports' => ['summary', 'detailed'],
                ],
                'professional' => [
                    'name' => 'Professional',
                    'price_monthly' => 249,
                    'limits' => [
                        'items' => -1, // unlimited
                        'storage_mb' => -1,
                    ],
                    'features' => ['basic', 'advanced', 'premium'],
                    'reports' => ['summary', 'detailed', 'custom'],
                ],
            ],
            'usage_metrics' => [
                'items' => ['label' => 'Items', 'unit' => 'items'],
                'storage_mb' => ['label' => 'Storage', 'unit' => 'MB'],
            ],
        ]);

        $this->tierConfig = new TierConfigurationService();
        
        // Mock the access service
        $this->accessService = Mockery::mock(ModuleAccessService::class);
        
        // Mock the usage provider
        $this->usageProvider = Mockery::mock(ModuleUsageProviderInterface::class);
        $this->usageProvider->shouldReceive('getModuleId')->andReturn('testmodule');
        
        $this->service = new UsageLimitService($this->tierConfig, $this->accessService);
        $this->service->registerProvider($this->usageProvider);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_registers_and_retrieves_providers(): void
    {
        $provider = $this->service->getProvider('testmodule');

        $this->assertNotNull($provider);
        $this->assertEquals('testmodule', $provider->getModuleId());
    }

    /** @test */
    public function it_returns_null_for_unregistered_provider(): void
    {
        $provider = $this->service->getProvider('unknown');

        $this->assertNull($provider);
    }

    /** @test */
    public function it_allows_increment_when_under_limit(): void
    {
        $user = new User(['id' => 1]);
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->andReturn('free');
        
        $this->usageProvider->shouldReceive('getMetric')
            ->with($user, 'items')
            ->andReturn(5);

        $result = $this->service->canIncrement($user, 'testmodule', 'items');

        $this->assertTrue($result['allowed']);
        $this->assertEquals(5, $result['remaining']);
        $this->assertEquals(10, $result['limit']);
        $this->assertEquals(5, $result['used']);
    }

    /** @test */
    public function it_denies_increment_when_at_limit(): void
    {
        $user = new User(['id' => 1]);
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->andReturn('free');
        
        $this->usageProvider->shouldReceive('getMetric')
            ->with($user, 'items')
            ->andReturn(10);

        $result = $this->service->canIncrement($user, 'testmodule', 'items');

        $this->assertFalse($result['allowed']);
        $this->assertEquals(0, $result['remaining']);
        $this->assertArrayHasKey('reason', $result);
    }

    /** @test */
    public function it_allows_unlimited_usage(): void
    {
        $user = new User(['id' => 1]);
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->andReturn('professional');
        
        $this->usageProvider->shouldReceive('getMetric')
            ->with($user, 'items')
            ->andReturn(10000);

        $result = $this->service->canIncrement($user, 'testmodule', 'items');

        $this->assertTrue($result['allowed']);
        $this->assertEquals(-1, $result['remaining']);
        $this->assertEquals(-1, $result['limit']);
    }

    /** @test */
    public function it_denies_when_feature_not_available(): void
    {
        $user = new User(['id' => 1]);
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->andReturn('free');

        $result = $this->service->canIncrement($user, 'testmodule', 'storage_mb');

        $this->assertFalse($result['allowed']);
        $this->assertEquals(0, $result['limit']);
        $this->assertStringContainsString('not available', $result['reason']);
    }

    /** @test */
    public function it_checks_feature_availability(): void
    {
        $user = new User(['id' => 1]);
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->andReturn('basic');

        $this->assertTrue($this->service->hasFeature($user, 'testmodule', 'advanced'));
        $this->assertFalse($this->service->hasFeature($user, 'testmodule', 'premium'));
    }

    /** @test */
    public function it_checks_report_access(): void
    {
        $user = new User(['id' => 1]);
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->andReturn('basic');

        $this->assertTrue($this->service->canAccessReport($user, 'testmodule', 'detailed'));
        $this->assertFalse($this->service->canAccessReport($user, 'testmodule', 'custom'));
    }

    /** @test */
    public function it_generates_usage_summary(): void
    {
        $user = new User(['id' => 1]);
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->andReturn('basic');
        
        $this->usageProvider->shouldReceive('getMetric')
            ->with($user, 'items')
            ->andReturn(50);
        
        $this->usageProvider->shouldReceive('getMetric')
            ->with($user, 'storage_mb')
            ->andReturn(250);

        $summary = $this->service->getUsageSummary($user, 'testmodule');

        $this->assertEquals('basic', $summary['tier']);
        $this->assertEquals('Basic', $summary['tier_name']);
        $this->assertArrayHasKey('metrics', $summary);
        
        $itemsMetric = $summary['metrics']['items'];
        $this->assertEquals(50, $itemsMetric['used']);
        $this->assertEquals(100, $itemsMetric['limit']);
        $this->assertEquals(50, $itemsMetric['remaining']);
        $this->assertEquals(50, $itemsMetric['percentage']);
    }

    /** @test */
    public function it_suggests_upgrades_when_usage_is_high(): void
    {
        $user = new User(['id' => 1]);
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->andReturn('basic');
        
        // 85% usage - should trigger upgrade suggestion
        $this->usageProvider->shouldReceive('getMetric')
            ->with($user, 'items')
            ->andReturn(85);
        
        $this->usageProvider->shouldReceive('getMetric')
            ->with($user, 'storage_mb')
            ->andReturn(100); // 20% - no suggestion

        $suggestions = $this->service->getUpgradeSuggestions($user, 'testmodule');

        $this->assertCount(1, $suggestions);
        $this->assertEquals('items', $suggestions[0]['metric']);
        $this->assertEquals('professional', $suggestions[0]['suggested_tier']);
    }

    /** @test */
    public function it_checks_file_upload_within_storage_limit(): void
    {
        $user = new User(['id' => 1]);
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->andReturn('basic');
        
        // 250MB used of 500MB limit
        $this->usageProvider->shouldReceive('getStorageUsed')
            ->with($user)
            ->andReturn(250 * 1024 * 1024);

        // Try to upload 100MB file
        $result = $this->service->canUploadFile($user, 'testmodule', 100 * 1024 * 1024, 'storage_mb');

        $this->assertTrue($result['allowed']);
    }

    /** @test */
    public function it_denies_file_upload_exceeding_storage_limit(): void
    {
        $user = new User(['id' => 1]);
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->andReturn('basic');
        
        // 450MB used of 500MB limit
        $this->usageProvider->shouldReceive('getStorageUsed')
            ->with($user)
            ->andReturn(450 * 1024 * 1024);

        // Try to upload 100MB file (would exceed limit)
        $result = $this->service->canUploadFile($user, 'testmodule', 100 * 1024 * 1024, 'storage_mb');

        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('Storage limit exceeded', $result['reason']);
    }

    /** @test */
    public function it_denies_file_upload_when_storage_not_available(): void
    {
        $user = new User(['id' => 1]);
        
        $this->accessService->shouldReceive('getAccessLevel')
            ->andReturn('free'); // Free tier has 0 storage

        $result = $this->service->canUploadFile($user, 'testmodule', 1024, 'storage_mb');

        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('not available', $result['reason']);
    }
}
