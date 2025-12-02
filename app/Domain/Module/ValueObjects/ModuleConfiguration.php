<?php

namespace App\Domain\Module\ValueObjects;

class ModuleConfiguration
{
    private function __construct(
        private readonly string $icon,
        private readonly string $color,
        private readonly ?string $thumbnail,
        private readonly array $routes,
        private readonly array $pwaConfig,
        private readonly array $features,
        private readonly array $subscriptionTiers,
        private readonly bool $requiresSubscription,
        private readonly bool $hasFreeTier = false,
        private readonly array $freeTierFeatures = [],
        private readonly array $freeTierLimits = [],
        private readonly array $tierLimits = [],
        private readonly array $featureAccess = []
    ) {}

    public static function create(
        string $icon,
        string $color,
        array $routes,
        array $pwaConfig = [],
        array $features = [],
        array $subscriptionTiers = [],
        bool $requiresSubscription = true,
        ?string $thumbnail = null,
        bool $hasFreeTier = false,
        array $freeTierFeatures = [],
        array $freeTierLimits = [],
        array $tierLimits = [],
        array $featureAccess = []
    ): self {
        return new self(
            icon: $icon,
            color: $color,
            thumbnail: $thumbnail,
            routes: $routes,
            pwaConfig: $pwaConfig,
            features: $features,
            subscriptionTiers: $subscriptionTiers,
            requiresSubscription: $requiresSubscription,
            hasFreeTier: $hasFreeTier,
            freeTierFeatures: $freeTierFeatures,
            freeTierLimits: $freeTierLimits,
            tierLimits: $tierLimits,
            featureAccess: $featureAccess
        );
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getIntegratedRoute(): ?string
    {
        return $this->routes['integrated'] ?? null;
    }

    public function getStandaloneRoute(): ?string
    {
        return $this->routes['standalone'] ?? null;
    }

    public function getPWAConfig(): array
    {
        return $this->pwaConfig;
    }

    public function isPWAEnabled(): bool
    {
        return ($this->pwaConfig['enabled'] ?? false) === true;
    }

    public function isInstallable(): bool
    {
        return ($this->pwaConfig['installable'] ?? false) === true;
    }

    public function supportsOffline(): bool
    {
        return ($this->features['offline'] ?? false) === true;
    }

    public function supportsDataSync(): bool
    {
        return ($this->features['dataSync'] ?? false) === true;
    }

    public function supportsNotifications(): bool
    {
        return ($this->features['notifications'] ?? false) === true;
    }

    public function isMultiUser(): bool
    {
        return ($this->features['multiUser'] ?? false) === true;
    }

    public function getFeatures(): array
    {
        return $this->features;
    }

    public function getSubscriptionTiers(): array
    {
        return $this->subscriptionTiers;
    }

    public function requiresSubscription(): bool
    {
        return $this->requiresSubscription;
    }

    public function hasFreeTier(): bool
    {
        return $this->hasFreeTier;
    }

    public function getFreeTierFeatures(): array
    {
        return $this->freeTierFeatures;
    }

    public function getFreeTierLimits(): array
    {
        return $this->freeTierLimits;
    }

    public function getTierLimits(): array
    {
        return $this->tierLimits;
    }

    public function getFeatureAccess(): array
    {
        return $this->featureAccess;
    }

    /**
     * Get limits for a specific tier
     */
    public function getLimitsForTier(string $tier): array
    {
        if ($tier === 'free') {
            return $this->freeTierLimits;
        }
        return $this->tierLimits[$tier] ?? [];
    }

    /**
     * Check if a feature is available for a specific tier
     */
    public function isFeatureAvailableForTier(string $feature, string $tier): bool
    {
        // If no feature access defined, all features available
        if (empty($this->featureAccess)) {
            return true;
        }

        $allowedTiers = $this->featureAccess[$feature] ?? [];
        return in_array($tier, $allowedTiers);
    }

    /**
     * Get all features available for a specific tier
     */
    public function getFeaturesForTier(string $tier): array
    {
        if ($tier === 'free') {
            return $this->freeTierFeatures;
        }

        if (empty($this->featureAccess)) {
            return array_keys($this->features);
        }

        return array_keys(array_filter(
            $this->featureAccess,
            fn($tiers) => in_array($tier, $tiers)
        ));
    }

    public function toArray(): array
    {
        return [
            'icon' => $this->icon,
            'color' => $this->color,
            'thumbnail' => $this->thumbnail,
            'routes' => $this->routes,
            'pwa_config' => $this->pwaConfig,
            'features' => $this->features,
            'subscription_tiers' => $this->subscriptionTiers,
            'requires_subscription' => $this->requiresSubscription,
            'has_free_tier' => $this->hasFreeTier,
            'free_tier_features' => $this->freeTierFeatures,
            'free_tier_limits' => $this->freeTierLimits,
            'tier_limits' => $this->tierLimits,
            'feature_access' => $this->featureAccess,
        ];
    }
}
