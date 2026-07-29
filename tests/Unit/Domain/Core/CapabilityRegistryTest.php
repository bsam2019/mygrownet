<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Services\CapabilityRegistry;
use App\Domain\Core\Services\ModuleDiscovery;
use App\Domain\Core\ValueObjects\ModuleManifest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CapabilityRegistryTest extends TestCase
{
    private CapabilityRegistry $registry;
    private ModuleDiscovery $discovery;

    protected function setUp(): void
    {
        $this->discovery = new ModuleDiscovery();
        $this->discovery->register(new ModuleManifest(
            id: 'platform-core',
            name: 'Platform Core',
            version: '1.0',
            category: 'core',
            capabilities: ['identity', 'notification'],
        ));
        $this->discovery->register(new ModuleManifest(
            id: 'stockflow',
            name: 'StockFlow',
            version: '2.0',
            category: 'business',
            capabilities: ['inventory'],
        ));

        $this->registry = new CapabilityRegistry($this->discovery);
    }

    #[Test]
    public function findProviders_delegates_to_discovery()
    {
        $this->assertEquals(['platform-core'], $this->registry->findProviders('identity'));
    }

    #[Test]
    public function findProvider_returns_manifest()
    {
        $manifest = $this->registry->findProvider('identity');

        $this->assertNotNull($manifest);
        $this->assertEquals('platform-core', $manifest->id);
    }

    #[Test]
    public function hasCapability_delegates_to_discovery()
    {
        $this->assertTrue($this->registry->hasCapability('platform-core', 'identity'));
        $this->assertFalse($this->registry->hasCapability('platform-core', 'inventory'));
    }

    #[Test]
    public function capabilities_delegates_to_discovery()
    {
        $this->assertEquals(['identity', 'notification'], $this->registry->capabilities('platform-core'));
    }

    #[Test]
    public function allCapabilities_aggregates_all()
    {
        $all = $this->registry->allCapabilities();

        $this->assertArrayHasKey('identity', $all);
        $this->assertArrayHasKey('notification', $all);
        $this->assertArrayHasKey('inventory', $all);
        $this->assertEquals(['platform-core'], $all['identity']);
        $this->assertEquals(['stockflow'], $all['inventory']);
    }

    #[Test]
    public function modulesWithCapability_returns_module_ids()
    {
        $this->assertEquals(['platform-core'], $this->registry->modulesWithCapability('identity'));
    }

    #[Test]
    public function count_delegates_to_discovery()
    {
        $this->assertEquals(2, $this->registry->count());
    }
}
