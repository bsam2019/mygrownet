<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\ValueObjects\ModuleManifest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModuleManifestTest extends TestCase
{
    #[Test]
    public function creates_with_required_fields()
    {
        $manifest = new ModuleManifest(
            id: 'stockflow',
            name: 'StockFlow',
            version: '1.0.0',
            category: 'business',
        );

        $this->assertEquals('stockflow', $manifest->id);
        $this->assertEquals('StockFlow', $manifest->name);
        $this->assertEquals('1.0.0', $manifest->version);
        $this->assertEquals('business', $manifest->category);
        $this->assertEquals('tenant', $manifest->type);
        $this->assertFalse($manifest->supportsSubdomain);
        $this->assertTrue($manifest->supportsWorkspaceLaunch);
        $this->assertTrue($manifest->requiresOrganization);
        $this->assertEmpty($manifest->permissions);
        $this->assertEmpty($manifest->capabilities);
        $this->assertEmpty($manifest->contracts);
    }

    #[Test]
    public function creates_with_all_fields()
    {
        $manifest = new ModuleManifest(
            id: 'platform-core',
            name: 'Platform Core',
            version: '2.0.0',
            category: 'core',
            type: 'platform',
            description: 'Core platform services',
            minPlatformVersion: '2.0',
            maxPlatformVersion: '99.x',
            entrypoint: '/core',
            icon: 'core-icon',
            supportsSubdomain: true,
            supportsWorkspaceLaunch: false,
            requiresOrganization: false,
            permissions: ['manage_users', 'manage_orgs'],
            capabilities: ['identity', 'notification'],
            contracts: ['App\Contracts\NotificationProvider'],
            events: ['user.created'],
            listens: ['user.created'],
            requiredCapabilities: ['storage'],
            optionalCapabilities: ['reporting'],
            settings: ['theme' => ['type' => 'string']],
            healthChecks: ['database' => ['type' => 'connection']],
        );

        $this->assertEquals('platform-core', $manifest->id);
        $this->assertEquals('Platform Core', $manifest->name);
        $this->assertEquals('2.0.0', $manifest->version);
        $this->assertEquals('core', $manifest->category);
        $this->assertEquals('platform', $manifest->type);
        $this->assertTrue($manifest->supportsSubdomain);
        $this->assertFalse($manifest->supportsWorkspaceLaunch);
        $this->assertFalse($manifest->requiresOrganization);
    }

    #[Test]
    public function toArray_returns_all_fields_in_snake_case()
    {
        $manifest = new ModuleManifest(
            id: 'test',
            name: 'Test Module',
            version: '1.0.0',
            category: 'core',
            description: 'A test',
            icon: 'test-icon',
            permissions: ['test'],
        );

        $data = $manifest->toArray();

        $this->assertEquals('test', $data['id']);
        $this->assertEquals('Test Module', $data['name']);
        $this->assertEquals('1.0.0', $data['version']);
        $this->assertEquals('core', $data['category']);
        $this->assertEquals('A test', $data['description']);
        $this->assertEquals('test-icon', $data['icon']);
        $this->assertFalse($data['supports_subdomain']);
        $this->assertTrue($data['supports_workspace_launch']);
        $this->assertTrue($data['requires_organization']);
        $this->assertEquals(['test'], $data['permissions']);
    }

    #[Test]
    public function defaults_are_set_correctly()
    {
        $manifest = new ModuleManifest(
            id: 'test',
            name: 'Test',
            version: '1.0.0',
            category: 'core',
        );

        $this->assertEquals('tenant', $manifest->type);
        $this->assertEquals('', $manifest->description);
        $this->assertEquals('1.0', $manifest->minPlatformVersion);
        $this->assertEquals('99.x', $manifest->maxPlatformVersion);
        $this->assertNull($manifest->entrypoint);
        $this->assertNull($manifest->icon);
        $this->assertFalse($manifest->supportsSubdomain);
        $this->assertTrue($manifest->supportsWorkspaceLaunch);
        $this->assertTrue($manifest->requiresOrganization);
        $this->assertIsArray($manifest->permissions);
        $this->assertIsArray($manifest->capabilities);
        $this->assertIsArray($manifest->contracts);
        $this->assertIsArray($manifest->events);
        $this->assertIsArray($manifest->listens);
        $this->assertIsArray($manifest->requiredCapabilities);
        $this->assertIsArray($manifest->optionalCapabilities);
        $this->assertIsArray($manifest->settings);
        $this->assertIsArray($manifest->healthChecks);
    }
}
