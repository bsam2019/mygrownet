<?php

namespace App\Application\DTOs;

use App\Domain\Module\Entities\Module;
use App\Domain\Module\Entities\ModuleSubscription;

class ModuleCardDTO
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
        public readonly bool $hasAccess,
        public readonly bool $isSubscribed,
        public readonly ?string $subscriptionStatus,
        public readonly ?string $subscriptionTier,
        public readonly ?string $subscriptionEndDate,
        public readonly bool $requiresSubscription,
        public readonly ?array $subscriptionTiers,
        public readonly string $primaryRoute,
        public readonly bool $isPWA,
        public readonly string $status
    ) {}

    public static function fromEntity(
        Module $module,
        bool $hasAccess = false,
        ?ModuleSubscription $subscription = null
    ): self {
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
            hasAccess: $hasAccess,
            isSubscribed: $subscription !== null && $subscription->isActive(),
            subscriptionStatus: $subscription?->getStatus(),
            subscriptionTier: $subscription?->getSubscriptionTier(),
            subscriptionEndDate: $subscription?->getExpiresAt()?->format('Y-m-d H:i:s'),
            requiresSubscription: $module->requiresSubscription(),
            subscriptionTiers: $config->getSubscriptionTiers(),
            primaryRoute: $config->getIntegratedRoute() ?? '#',
            isPWA: $config->isPWAEnabled(),
            status: $module->getStatus()->value
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
            'has_access' => $this->hasAccess,
            'is_subscribed' => $this->isSubscribed,
            'subscription_status' => $this->subscriptionStatus,
            'subscription_tier' => $this->subscriptionTier,
            'subscription_end_date' => $this->subscriptionEndDate,
            'requires_subscription' => $this->requiresSubscription,
            'subscription_tiers' => $this->subscriptionTiers,
            'primary_route' => $this->primaryRoute,
            'is_pwa' => $this->isPWA,
            'status' => $this->status,
        ];
    }
}
