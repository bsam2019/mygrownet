<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Entities\Application;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    #[Test]
    public function constructor_sets_all_properties()
    {
        $app = new Application(
            id: '1',
            name: 'StockFlow',
            slug: 'stockflow',
            type: 'module',
            url: 'https://stockflow.mygrownet.com',
            isActive: true,
            category: 'business',
            accessModel: 'subscription',
            contextSupport: 'organization',
            requiresOrganizationContext: true,
            subscriptionRequired: true,
            lifecycle: 'active',
            operationalStatus: 'operational',
            replacementAppId: null,
            migrationDeadline: null,
            isVisible: true,
            config: ['version' => '1.0'],
        );

        $this->assertEquals('1', $app->id);
        $this->assertEquals('StockFlow', $app->name);
        $this->assertEquals('stockflow', $app->slug);
        $this->assertEquals('module', $app->type);
        $this->assertEquals('https://stockflow.mygrownet.com', $app->url);
        $this->assertTrue($app->isActive);
        $this->assertEquals('business', $app->category);
        $this->assertEquals('subscription', $app->accessModel);
        $this->assertEquals('organization', $app->contextSupport);
        $this->assertTrue($app->requiresOrganizationContext);
        $this->assertTrue($app->subscriptionRequired);
        $this->assertEquals('active', $app->lifecycle);
        $this->assertEquals('operational', $app->operationalStatus);
        $this->assertNull($app->replacementAppId);
        $this->assertNull($app->migrationDeadline);
        $this->assertTrue($app->isVisible);
        $this->assertEquals(['version' => '1.0'], $app->config);
    }

    #[Test]
    public function constructor_accepts_null_replacement_info()
    {
        $app = new Application(
            id: '2',
            name: 'Old App',
            slug: 'old',
            type: 'legacy',
            url: null,
            isActive: false,
            category: 'tools',
            accessModel: 'free',
            contextSupport: 'personal',
            requiresOrganizationContext: false,
            subscriptionRequired: false,
            lifecycle: 'archived',
            operationalStatus: 'deprecated',
            replacementAppId: 'app-3',
            migrationDeadline: '2026-12-31',
            isVisible: false,
            config: [],
        );

        $this->assertEquals('app-3', $app->replacementAppId);
        $this->assertEquals('2026-12-31', $app->migrationDeadline);
        $this->assertFalse($app->isVisible);
        $this->assertNull($app->url);
    }
}
