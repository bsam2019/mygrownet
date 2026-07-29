<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\Entities\Module;
use App\Domain\Module\ValueObjects\ModuleCategory;
use App\Domain\Module\ValueObjects\ModuleConfiguration;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Domain\Module\ValueObjects\ModuleName;
use App\Domain\Module\ValueObjects\ModuleSlug;
use App\Domain\Module\ValueObjects\ModuleStatus;
use App\Enums\AccountType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModuleEntityTest extends TestCase
{
    private ModuleId $id;
    private ModuleName $name;
    private ModuleSlug $slug;
    private ModuleConfiguration $config;

    protected function setUp(): void
    {
        $this->id = ModuleId::fromString('stockflow');
        $this->name = ModuleName::fromString('StockFlow');
        $this->slug = ModuleSlug::fromString('stockflow');
        $this->config = ModuleConfiguration::create(
            icon: 'ChartIcon',
            color: 'blue',
            routes: ['integrated' => '/stock-audit'],
            pwaConfig: ['enabled' => true],
            features: ['offline' => true],
            requiresSubscription: true,
            hasFreeTier: true,
            freeTierFeatures: ['basic_dashboard'],
            freeTierLimits: ['items' => 10],
            tierLimits: ['pro' => ['items' => 500]],
        );
    }

    #[Test]
    public function create_sets_initial_state()
    {
        $module = Module::create(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            category: ModuleCategory::SME,
            description: 'Inventory management',
            accountTypes: [AccountType::BUSINESS],
            configuration: $this->config,
        );

        $this->assertSame($this->id, $module->getId());
        $this->assertSame($this->name, $module->getName());
        $this->assertSame($this->slug, $module->getSlug());
        $this->assertSame(ModuleCategory::SME, $module->getCategory());
        $this->assertEquals('Inventory management', $module->getDescription());
        $this->assertEquals([AccountType::BUSINESS], $module->getAccountTypes());
        $this->assertSame($this->config, $module->getConfiguration());
        $this->assertTrue($module->isActive());
        $this->assertEquals('1.0.0', $module->getVersion());
        $this->assertNotNull($module->getCreatedAt());
        $this->assertNotNull($module->getUpdatedAt());
    }

    #[Test]
    public function create_throws_on_empty_account_types()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Module must have at least one account type');

        Module::create(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            category: ModuleCategory::CORE,
            description: '',
            accountTypes: [],
            configuration: $this->config,
        );
    }

    #[Test]
    public function reconstitute_restores_with_existing_state()
    {
        $created = new \DateTimeImmutable('2026-01-01');
        $updated = new \DateTimeImmutable('2026-06-15');

        $module = Module::reconstitute(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            category: ModuleCategory::ENTERPRISE,
            description: 'Restored',
            accountTypes: [AccountType::BUSINESS, AccountType::MEMBER],
            configuration: $this->config,
            status: ModuleStatus::BETA,
            version: '2.0.0',
            createdAt: $created,
            updatedAt: $updated,
        );

        $this->assertSame($created, $module->getCreatedAt());
        $this->assertSame($updated, $module->getUpdatedAt());
        $this->assertTrue($module->isBeta());
        $this->assertEquals('2.0.0', $module->getVersion());
        $this->assertCount(2, $module->getAccountTypes());
    }

    #[Test]
    public function isAccessibleBy_checks_account_types()
    {
        $module = Module::create(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            category: ModuleCategory::SME,
            description: '',
            accountTypes: [AccountType::BUSINESS, AccountType::MEMBER],
            configuration: $this->config,
        );

        $this->assertTrue($module->isAccessibleBy(AccountType::BUSINESS));
        $this->assertTrue($module->isAccessibleBy(AccountType::MEMBER));
        $this->assertFalse($module->isAccessibleBy(AccountType::CLIENT));
    }

    #[Test]
    public function activate_sets_status()
    {
        $module = Module::create(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            category: ModuleCategory::SME,
            description: '',
            accountTypes: [AccountType::BUSINESS],
            configuration: $this->config,
        );

        $module->deactivate();
        $this->assertFalse($module->isActive());
        $this->assertSame(ModuleStatus::INACTIVE, $module->getStatus());

        $module->activate();
        $this->assertTrue($module->isActive());
        $this->assertSame(ModuleStatus::ACTIVE, $module->getStatus());
    }

    #[Test]
    public function markAsBeta_sets_beta_status()
    {
        $module = Module::create(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            category: ModuleCategory::SME,
            description: '',
            accountTypes: [AccountType::BUSINESS],
            configuration: $this->config,
        );

        $module->markAsBeta();
        $this->assertTrue($module->isBeta());
        $this->assertSame(ModuleStatus::BETA, $module->getStatus());
    }

    #[Test]
    public function updateVersion_changes_version()
    {
        $module = Module::create(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            category: ModuleCategory::SME,
            description: '',
            accountTypes: [AccountType::BUSINESS],
            configuration: $this->config,
        );

        $module->updateVersion('2.1.0');
        $this->assertEquals('2.1.0', $module->getVersion());
    }

    #[Test]
    public function delegates_freemium_methods()
    {
        $module = Module::create(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            category: ModuleCategory::SME,
            description: '',
            accountTypes: [AccountType::BUSINESS],
            configuration: $this->config,
        );

        $this->assertTrue($module->hasFreeTier());
        $this->assertEquals(['basic_dashboard'], $module->getFreeTierFeatures());
        $this->assertEquals(['items' => 10], $module->getFreeTierLimits());
        $this->assertEquals(['items' => 10], $module->getLimitsForTier('free'));
        $this->assertEquals(['items' => 500], $module->getLimitsForTier('pro'));
    }

    #[Test]
    public function fromConfig_creates_from_array()
    {
        $module = Module::fromConfig('stockflow', [
            'name' => 'StockFlow',
            'slug' => 'stock-audit',
            'category' => 'sme',
            'description' => 'Stock management',
            'account_types' => ['business'],
            'icon' => 'ChartIcon',
            'color' => 'blue',
            'status' => 'active',
            'version' => '3.0.0',
        ]);

        $this->assertEquals('StockFlow', $module->getName()->value());
        $this->assertEquals('stock-audit', $module->getSlug()->value());
        $this->assertSame(ModuleCategory::SME, $module->getCategory());
        $this->assertTrue($module->isActive());
        $this->assertEquals('3.0.0', $module->getVersion());
        $this->assertEquals('ChartIcon', $module->getConfiguration()->getIcon());
        $this->assertTrue($module->isAccessibleBy(AccountType::BUSINESS));
        $this->assertInstanceOf(AccountType::class, $module->getAccountTypes()[0]);
    }

    #[Test]
    public function fromConfig_uses_defaults()
    {
        $module = Module::fromConfig('test-module', [
            'name' => 'Test',
        ]);

        $this->assertEquals('test-module', $module->getSlug()->value());
        $this->assertEquals('1.0.0', $module->getVersion());
        $this->assertSame(ModuleStatus::ACTIVE, $module->getStatus());
        $this->assertContainsOnlyInstancesOf(AccountType::class, $module->getAccountTypes());
    }

    #[Test]
    public function requiresSubscription_delegates_to_config()
    {
        $module = Module::create(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            category: ModuleCategory::SME,
            description: '',
            accountTypes: [AccountType::BUSINESS],
            configuration: $this->config,
        );

        $this->assertTrue($module->requiresSubscription());
    }

    #[Test]
    public function isPWAEnabled_delegates_to_config()
    {
        $module = Module::create(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            category: ModuleCategory::SME,
            description: '',
            accountTypes: [AccountType::BUSINESS],
            configuration: $this->config,
        );

        $this->assertTrue($module->isPWAEnabled());
    }

    #[Test]
    public function supportsOffline_delegates_to_config()
    {
        $module = Module::create(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            category: ModuleCategory::SME,
            description: '',
            accountTypes: [AccountType::BUSINESS],
            configuration: $this->config,
        );

        $this->assertTrue($module->supportsOffline());
    }
}
