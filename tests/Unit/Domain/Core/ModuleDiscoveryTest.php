<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Services\ModuleDiscovery;
use App\Domain\Core\ValueObjects\ModuleManifest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModuleDiscoveryTest extends TestCase
{
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
            contracts: ['App\Contracts\IdentityProvider'],
            events: ['user.created'],
        ));
        $this->discovery->register(new ModuleManifest(
            id: 'stockflow',
            name: 'StockFlow',
            version: '2.0',
            category: 'business',
            capabilities: ['inventory'],
            contracts: ['App\Contracts\InventoryProvider'],
            events: ['stock.adjusted'],
        ));
        $this->discovery->register(new ModuleManifest(
            id: 'growfinance',
            name: 'GrowFinance',
            version: '1.5',
            category: 'finance',
            capabilities: ['inventory'],
            contracts: ['App\Contracts\AccountingProvider'],
            events: ['payment.received'],
        ));
    }

    #[Test]
    public function register_adds_manifest()
    {
        $d = new ModuleDiscovery();
        $d->register(new ModuleManifest(
            id: 'new-mod',
            name: 'New',
            version: '1.0',
            category: 'tools',
        ));

        $this->assertTrue($d->has('new-mod'));
        $this->assertEquals(1, $d->count());
    }

    #[Test]
    public function find_returns_manifest()
    {
        $manifest = $this->discovery->find('stockflow');

        $this->assertNotNull($manifest);
        $this->assertEquals('StockFlow', $manifest->name);
    }

    #[Test]
    public function find_returns_null_for_unknown()
    {
        $this->assertNull($this->discovery->find('unknown'));
    }

    #[Test]
    public function has_returns_correctly()
    {
        $this->assertTrue($this->discovery->has('platform-core'));
        $this->assertFalse($this->discovery->has('non-existent'));
    }

    #[Test]
    public function all_returns_array_of_manifest_arrays()
    {
        $all = $this->discovery->all();

        $this->assertCount(3, $all);
        $this->assertEquals('platform-core', $all[0]['id']);
        $this->assertEquals('stockflow', $all[1]['id']);
    }

    #[Test]
    public function allManifests_returns_manifest_objects()
    {
        $manifests = $this->discovery->allManifests();

        $this->assertCount(3, $manifests);
        $this->assertInstanceOf(ModuleManifest::class, $manifests[0]);
    }

    #[Test]
    public function count_returns_number_of_registered()
    {
        $this->assertEquals(3, $this->discovery->count());
    }

    #[Test]
    public function capabilities_returns_array()
    {
        $this->assertEquals(['identity', 'notification'], $this->discovery->capabilities('platform-core'));
        $this->assertEquals(['inventory'], $this->discovery->capabilities('stockflow'));
    }

    #[Test]
    public function capabilities_returns_empty_for_unknown()
    {
        $this->assertEquals([], $this->discovery->capabilities('unknown'));
    }

    #[Test]
    public function hasCapability_checks_correctly()
    {
        $this->assertTrue($this->discovery->hasCapability('platform-core', 'identity'));
        $this->assertFalse($this->discovery->hasCapability('platform-core', 'inventory'));
        $this->assertTrue($this->discovery->hasCapability('stockflow', 'inventory'));
    }

    #[Test]
    public function findProviders_returns_module_ids()
    {
        $providers = $this->discovery->findProviders('inventory');

        $this->assertCount(2, $providers);
        $this->assertContains('stockflow', $providers);
        $this->assertContains('growfinance', $providers);
    }

    #[Test]
    public function findProviders_returns_empty_for_unknown()
    {
        $this->assertEquals([], $this->discovery->findProviders('nonexistent'));
    }

    #[Test]
    public function findByContract_returns_matching_manifest()
    {
        $manifest = $this->discovery->findByContract('App\Contracts\InventoryProvider');

        $this->assertNotNull($manifest);
        $this->assertEquals('stockflow', $manifest->id);
    }

    #[Test]
    public function findByContract_returns_null_for_unknown()
    {
        $this->assertNull($this->discovery->findByContract('Unknown\Contract'));
    }

    #[Test]
    public function findByCapability_returns_first_match()
    {
        $manifest = $this->discovery->findByCapability('inventory');

        $this->assertNotNull($manifest);
        $this->assertContains($manifest->id, ['stockflow', 'growfinance']);
    }

    #[Test]
    public function findByCapability_returns_null_for_unknown()
    {
        $this->assertNull($this->discovery->findByCapability('unknown'));
    }

    #[Test]
    public function contracts_returns_array()
    {
        $this->assertContains('App\Contracts\IdentityProvider', $this->discovery->contracts('platform-core'));
        $this->assertEquals([], $this->discovery->contracts('unknown'));
    }

    #[Test]
    public function events_returns_array()
    {
        $this->assertEquals(['user.created'], $this->discovery->events('platform-core'));
        $this->assertEquals([], $this->discovery->events('unknown'));
    }
}
