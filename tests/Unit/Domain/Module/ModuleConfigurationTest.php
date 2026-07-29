<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\ValueObjects\ModuleConfiguration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModuleConfigurationTest extends TestCase
{
    #[Test]
    public function create_sets_all_properties()
    {
        $config = ModuleConfiguration::create(
            icon: 'ChartIcon',
            color: 'blue',
            routes: ['integrated' => '/finance', 'standalone' => 'https://finance.app'],
            pwaConfig: ['enabled' => true, 'installable' => true],
            features: ['offline' => true, 'dataSync' => true, 'notifications' => true, 'multiUser' => false],
            subscriptionTiers: ['free', 'pro'],
            requiresSubscription: true,
            thumbnail: 'thumb.jpg',
            hasFreeTier: true,
            freeTierFeatures: ['basic_dashboard'],
            freeTierLimits: ['accounts' => 3],
            tierLimits: ['pro' => ['accounts' => 100]],
            featureAccess: ['advanced_reports' => ['pro']],
        );

        $this->assertEquals('ChartIcon', $config->getIcon());
        $this->assertEquals('blue', $config->getColor());
        $this->assertEquals('thumb.jpg', $config->getThumbnail());
        $this->assertEquals('/finance', $config->getIntegratedRoute());
        $this->assertEquals('https://finance.app', $config->getStandaloneRoute());
        $this->assertTrue($config->isPWAEnabled());
        $this->assertTrue($config->isInstallable());
        $this->assertTrue($config->supportsOffline());
        $this->assertTrue($config->supportsDataSync());
        $this->assertTrue($config->supportsNotifications());
        $this->assertFalse($config->isMultiUser());
        $this->assertTrue($config->requiresSubscription());
        $this->assertTrue($config->hasFreeTier());
        $this->assertEquals(['free', 'pro'], $config->getSubscriptionTiers());
        $this->assertEquals(['basic_dashboard'], $config->getFreeTierFeatures());
        $this->assertEquals(['accounts' => 3], $config->getFreeTierLimits());
        $this->assertEquals(['pro' => ['accounts' => 100]], $config->getTierLimits());
    }

    #[Test]
    public function create_defaults()
    {
        $config = ModuleConfiguration::create(
            icon: 'DefaultIcon',
            color: 'gray',
            routes: [],
        );

        $this->assertEquals([], $config->getRoutes());
        $this->assertNull($config->getIntegratedRoute());
        $this->assertNull($config->getStandaloneRoute());
        $this->assertFalse($config->isPWAEnabled());
        $this->assertFalse($config->isInstallable());
        $this->assertNull($config->getThumbnail());
        $this->assertTrue($config->requiresSubscription());
        $this->assertFalse($config->hasFreeTier());
    }

    #[Test]
    public function getLimitsForTier_free_returns_free_limits()
    {
        $config = ModuleConfiguration::create(
            icon: 'i',
            color: 'c',
            routes: [],
            freeTierLimits: ['items' => 10],
            tierLimits: ['pro' => ['items' => 100]],
        );

        $this->assertEquals(['items' => 10], $config->getLimitsForTier('free'));
        $this->assertEquals(['items' => 100], $config->getLimitsForTier('pro'));
        $this->assertEquals([], $config->getLimitsForTier('unknown'));
    }

    #[Test]
    public function isFeatureAvailableForTier_checks_feature_access()
    {
        $config = ModuleConfiguration::create(
            icon: 'i',
            color: 'c',
            routes: [],
            featureAccess: ['premium_feature' => ['pro', 'enterprise']],
        );

        $this->assertTrue($config->isFeatureAvailableForTier('premium_feature', 'pro'));
        $this->assertTrue($config->isFeatureAvailableForTier('premium_feature', 'enterprise'));
        $this->assertFalse($config->isFeatureAvailableForTier('premium_feature', 'free'));
        $this->assertFalse($config->isFeatureAvailableForTier('premium_feature', 'basic'));
    }

    #[Test]
    public function isFeatureAvailableForTier_returns_true_when_no_access_defined()
    {
        $config = ModuleConfiguration::create(
            icon: 'i',
            color: 'c',
            routes: [],
        );

        $this->assertTrue($config->isFeatureAvailableForTier('any_feature', 'any_tier'));
    }

    #[Test]
    public function getFeaturesForTier_free_returns_free_features()
    {
        $config = ModuleConfiguration::create(
            icon: 'i',
            color: 'c',
            routes: [],
            freeTierFeatures: ['basic'],
            featureAccess: ['advanced' => ['pro']],
        );

        $this->assertEquals(['basic'], $config->getFeaturesForTier('free'));
        $this->assertEquals(['advanced'], $config->getFeaturesForTier('pro'));

        $noAccess = ModuleConfiguration::create(
            icon: 'i',
            color: 'c',
            routes: [],
            features: ['a' => true, 'b' => true],
        );

        $this->assertEquals(['a', 'b'], $noAccess->getFeaturesForTier('any'));
    }

    #[Test]
    public function toArray_returns_all_fields()
    {
        $config = ModuleConfiguration::create(
            icon: 'Icon',
            color: 'red',
            routes: ['integrated' => '/app'],
        );

        $data = $config->toArray();

        $this->assertEquals('Icon', $data['icon']);
        $this->assertEquals('red', $data['color']);
        $this->assertEquals(['integrated' => '/app'], $data['routes']);
        $this->assertArrayHasKey('pwa_config', $data);
        $this->assertArrayHasKey('features', $data);
        $this->assertArrayHasKey('free_tier_features', $data);
    }

    #[Test]
    public function fromArray_creates_from_config()
    {
        $config = ModuleConfiguration::fromArray([
            'icon' => 'CustomIcon',
            'color' => 'purple',
            'routes' => ['integrated' => '/custom'],
            'tiers' => [
                'free' => [
                    'features' => ['basic'],
                    'limits' => ['items' => 5],
                ],
                'pro' => [
                    'limits' => ['items' => 100],
                ],
            ],
            'feature_access' => ['advanced' => ['pro']],
        ]);

        $this->assertEquals('CustomIcon', $config->getIcon());
        $this->assertEquals('purple', $config->getColor());
        $this->assertEquals('/custom', $config->getIntegratedRoute());
        $this->assertTrue($config->hasFreeTier());
        $this->assertEquals(['basic'], $config->getFreeTierFeatures());
        $this->assertEquals(['items' => 5], $config->getFreeTierLimits());
        $this->assertTrue($config->isFeatureAvailableForTier('advanced', 'pro'));
        $this->assertFalse($config->isFeatureAvailableForTier('advanced', 'free'));
    }

    #[Test]
    public function fromArray_without_tiers()
    {
        $config = ModuleConfiguration::fromArray([]);

        $this->assertEquals('CurrencyDollarIcon', $config->getIcon());
        $this->assertEquals('emerald', $config->getColor());
        $this->assertFalse($config->hasFreeTier());
        $this->assertTrue($config->requiresSubscription());
    }
}
