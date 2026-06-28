<?php

namespace App\Domain\Module\Entities;

use App\Domain\Module\ValueObjects\ModuleId;
use App\Domain\Module\ValueObjects\ModuleName;
use App\Domain\Module\ValueObjects\ModuleSlug;
use App\Domain\Module\ValueObjects\ModuleCategory;
use App\Domain\Module\ValueObjects\ModuleStatus;
use App\Domain\Module\ValueObjects\ModuleConfiguration;
use App\Enums\AccountType;

/**
 * Module Entity
 * 
 * Represents a modular application within the MyGrowNet platform.
 * Can function as both an integrated module and standalone PWA.
 */
class Module
{
    private function __construct(
        private ModuleId $id,
        private ModuleName $name,
        private ModuleSlug $slug,
        private ModuleCategory $category,
        private string $description,
        private array $accountTypes,
        private ModuleConfiguration $configuration,
        private ModuleStatus $status,
        private string $version,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt
    ) {
        $this->validateAccountTypes();
    }

    public static function create(
        ModuleId $id,
        ModuleName $name,
        ModuleSlug $slug,
        ModuleCategory $category,
        string $description,
        array $accountTypes,
        ModuleConfiguration $configuration
    ): self {
        return new self(
            id: $id,
            name: $name,
            slug: $slug,
            category: $category,
            description: $description,
            accountTypes: $accountTypes,
            configuration: $configuration,
            status: ModuleStatus::ACTIVE,
            version: '1.0.0',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
    }

    /**
     * Reconstitute entity from persistence (for repositories)
     */
    public static function reconstitute(
        ModuleId $id,
        ModuleName $name,
        ModuleSlug $slug,
        ModuleCategory $category,
        string $description,
        array $accountTypes,
        ModuleConfiguration $configuration,
        ModuleStatus $status,
        string $version,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            name: $name,
            slug: $slug,
            category: $category,
            description: $description,
            accountTypes: $accountTypes,
            configuration: $configuration,
            status: $status,
            version: $version,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    /**
     * Create Module from config array (for config-based repositories)
     */
    public static function fromConfig(string $moduleId, array $config): self
    {
        $accountTypes = [];
        foreach ($config['account_types'] ?? ['sme'] as $type) {
            $accountTypes[] = AccountType::tryFrom($type) ?? AccountType::SME;
        }

        return new self(
            id: ModuleId::fromString($moduleId),
            name: ModuleName::fromString($config['name']),
            slug: ModuleSlug::fromString($config['slug'] ?? $moduleId),
            category: ModuleCategory::fromString($config['category'] ?? 'general'),
            description: $config['description'] ?? '',
            accountTypes: $accountTypes,
            configuration: ModuleConfiguration::fromArray($config),
            status: ModuleStatus::fromString($config['status'] ?? 'active'),
            version: $config['version'] ?? '1.0.0',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
    }

    public function isAccessibleBy(AccountType $accountType): bool
    {
        return in_array($accountType, $this->accountTypes, true);
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isBeta(): bool
    {
        return $this->status->isBeta();
    }

    public function requiresSubscription(): bool
    {
        return $this->configuration->requiresSubscription();
    }

    public function isPWAEnabled(): bool
    {
        return $this->configuration->isPWAEnabled();
    }

    public function supportsOffline(): bool
    {
        return $this->configuration->supportsOffline();
    }

    // Freemium methods
    public function hasFreeTier(): bool
    {
        return $this->configuration->hasFreeTier();
    }

    public function getFreeTierFeatures(): array
    {
        return $this->configuration->getFreeTierFeatures();
    }

    public function getFreeTierLimits(): array
    {
        return $this->configuration->getFreeTierLimits();
    }

    public function getLimitsForTier(string $tier): array
    {
        return $this->configuration->getLimitsForTier($tier);
    }

    public function isFeatureAvailableForTier(string $feature, string $tier): bool
    {
        return $this->configuration->isFeatureAvailableForTier($feature, $tier);
    }

    public function getFeaturesForTier(string $tier): array
    {
        return $this->configuration->getFeaturesForTier($tier);
    }

    public function activate(): void
    {
        $this->status = ModuleStatus::ACTIVE;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->status = ModuleStatus::INACTIVE;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsBeta(): void
    {
        $this->status = ModuleStatus::BETA;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateVersion(string $version): void
    {
        $this->version = $version;
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function validateAccountTypes(): void
    {
        if (empty($this->accountTypes)) {
            throw new \DomainException('Module must have at least one account type');
        }

        foreach ($this->accountTypes as $type) {
            if (!$type instanceof AccountType) {
                throw new \DomainException('Invalid account type');
            }
        }
    }

    // Getters
    public function getId(): ModuleId { return $this->id; }
    public function getName(): ModuleName { return $this->name; }
    public function getSlug(): ModuleSlug { return $this->slug; }
    public function getCategory(): ModuleCategory { return $this->category; }
    public function getDescription(): string { return $this->description; }
    public function getAccountTypes(): array { return $this->accountTypes; }
    public function getConfiguration(): ModuleConfiguration { return $this->configuration; }
    public function getStatus(): ModuleStatus { return $this->status; }
    public function getVersion(): string { return $this->version; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}
