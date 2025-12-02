<?php

namespace App\Application\DTOs;

use App\Domain\Module\Entities\Module;

class ModuleDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $category,
        public readonly ?string $description,
        public readonly ?string $icon,
        public readonly ?string $color,
        public readonly ?string $thumbnail,
        public readonly array $accountTypes,
        public readonly bool $requiresSubscription,
        public readonly array $routes,
        public readonly array $pwaConfig,
        public readonly array $features,
        public readonly ?array $subscriptionTiers,
        public readonly string $status,
        public readonly string $version
    ) {}

    public static function fromEntity(Module $module): self
    {
        $config = $module->getConfiguration();
        
        return new self(
            id: $module->getId()->value(),
            name: $module->getName()->value(),
            slug: $module->getSlug()->value(),
            category: $module->getCategory()->value,
            description: $module->getDescription(),
            icon: $config->getIcon(),
            color: $config->getColor(),
            thumbnail: $config->getThumbnail(),
            accountTypes: array_map(fn($type) => $type->value, $module->getAccountTypes()),
            requiresSubscription: $module->requiresSubscription(),
            routes: $config->getRoutes(),
            pwaConfig: $config->getPWAConfig(),
            features: $config->getFeatures(),
            subscriptionTiers: $config->getSubscriptionTiers(),
            status: $module->getStatus()->value,
            version: $module->getVersion()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'thumbnail' => $this->thumbnail,
            'account_types' => $this->accountTypes,
            'requires_subscription' => $this->requiresSubscription,
            'routes' => $this->routes,
            'pwa_config' => $this->pwaConfig,
            'features' => $this->features,
            'subscription_tiers' => $this->subscriptionTiers,
            'status' => $this->status,
            'version' => $this->version,
        ];
    }
}
