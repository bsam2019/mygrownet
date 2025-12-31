<?php

namespace App\Services\GrowBuilder;

use App\Domain\Module\Services\SubscriptionService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Centralized service for GrowBuilder tier restrictions
 * Enforces feature access based on user's subscription tier
 * 
 * NOTE: Limits are pulled from database via TierConfigurationService.
 * Fallback values are used if database config is missing.
 */
class TierRestrictionService
{
    private const MODULE_ID = 'growbuilder';
    private const CACHE_TTL = 300; // 5 minutes

    // Tier hierarchy (higher index = higher tier)
    private const TIER_HIERARCHY = [
        'free' => 0,
        'starter' => 1,
        'business' => 2,
        'agency' => 3,
    ];

    // Fallback limits (used if not in database)
    private const DEFAULT_SITES_LIMIT = [
        'free' => 1,
        'starter' => 1,
        'business' => 1,
        'agency' => 20,
    ];

    private const DEFAULT_STORAGE_LIMIT = [
        'free' => 524288000,       // 500 MB
        'starter' => 1073741824,   // 1 GB
        'business' => 2147483648,  // 2 GB
        'agency' => 10737418240,   // 10 GB
    ];

    private const DEFAULT_PRODUCTS_LIMIT = [
        'free' => 0,
        'starter' => 20,
        'business' => -1,
        'agency' => -1,
    ];

    private const DEFAULT_AI_PROMPTS_LIMIT = [
        'free' => 5,
        'starter' => 100,
        'business' => -1,
        'agency' => -1,
    ];

    public function __construct(
        private SubscriptionService $subscriptionService,
        private TierConfigurationService $tierConfigService
    ) {}

    /**
     * Get user's current tier
     */
    public function getUserTier(User $user): string
    {
        return $this->subscriptionService->getUserTier($user, self::MODULE_ID) ?? 'free';
    }

    /**
     * Get all restrictions for a user's tier
     */
    public function getRestrictions(User $user): array
    {
        $tier = $this->getUserTier($user);
        $cacheKey = "growbuilder_restrictions:{$user->id}:{$tier}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tier) {
            return $this->buildRestrictions($tier);
        });
    }
    
    /**
     * Build restrictions array for a tier
     */
    private function buildRestrictions(string $tier): array
    {
        $tierConfig = $this->tierConfigService->getTierConfig(self::MODULE_ID, $tier);
        $features = $this->getTierFeatures($tier, $tierConfig);
        
        $sitesLimit = $this->getSitesLimitForTier($tier, $tierConfig);
        $storageLimit = $this->getStorageLimitForTier($tier, $tierConfig);
        $productsLimit = $this->getProductsLimitForTier($tier, $tierConfig);
        $aiPromptsLimit = $this->getAIPromptsLimitForTier($tier, $tierConfig);

        return [
            'tier' => $tier,
            'tier_name' => $tierConfig['name'] ?? ucfirst($tier),
            'sites_limit' => $sitesLimit,
            'storage_limit' => $storageLimit,
            'storage_limit_formatted' => $this->formatBytes($storageLimit),
            'products_limit' => $productsLimit,
            'products_unlimited' => $productsLimit === -1,
            'ai_prompts_limit' => $aiPromptsLimit,
            'ai_unlimited' => $aiPromptsLimit === -1,
            'features' => $features,
        ];
    }

    /**
     * Get features for a tier from database or defaults
     */
    private function getTierFeatures(string $tier, ?array $tierConfig): array
    {
        // Try to get from database config
        if ($tierConfig && isset($tierConfig['features'])) {
            $dbFeatures = [];
            foreach ($tierConfig['features'] as $key => $feature) {
                $dbFeatures[$key] = is_array($feature) ? ($feature['value'] ?? false) : $feature;
            }
            return $dbFeatures;
        }

        // Fallback to hardcoded defaults
        return $this->getDefaultFeatures($tier);
    }

    /**
     * Get default features for a tier (fallback)
     */
    private function getDefaultFeatures(string $tier): array
    {
        $defaults = [
            'free' => [
                'subdomain' => true,
                'limited_templates' => true,
                'basic_editor' => true,
                'custom_domain' => false,
                'payment_integration' => false,
                'ecommerce' => false,
                'marketing_tools' => false,
                'remove_branding' => false,
                'ai_seo' => false,
                'ai_priority' => false,
                'white_label' => false,
            ],
            'starter' => [
                'subdomain' => true,
                'custom_domain' => true,
                'manual_payments' => true,
                'ecommerce' => true,
                'shared_smtp' => true,
                'ai_section_generator' => true,
                'payment_integration' => false,
                'marketing_tools' => false,
                'remove_branding' => false,
                'ai_seo' => false,
                'ai_priority' => false,
                'white_label' => false,
            ],
            'business' => [
                'subdomain' => true,
                'custom_domain' => true,
                'free_domain_after_3mo' => true,
                'payment_integration' => true,
                'ecommerce' => true,
                'unlimited_products' => true,
                'marketing_tools' => true,
                'remove_branding' => true,
                'own_smtp' => true,
                'ai_section_generator' => true,
                'ai_seo' => true,
                'priority_support' => true,
                'ai_priority' => false,
                'white_label' => false,
            ],
            'agency' => [
                'subdomain' => true,
                'custom_domain' => true,
                'free_domain_after_3mo' => true,
                'payment_integration' => true,
                'ecommerce' => true,
                'unlimited_products' => true,
                'marketing_tools' => true,
                'remove_branding' => true,
                'own_smtp' => true,
                'ai_section_generator' => true,
                'ai_seo' => true,
                'ai_priority' => true,
                'ai_early_access' => true,
                'white_label' => true,
                'multi_site' => true,
                'priority_support' => true,
            ],
        ];

        return $defaults[$tier] ?? $defaults['free'];
    }

    /**
     * Get sites limit for tier
     */
    private function getSitesLimitForTier(string $tier, ?array $tierConfig): int
    {
        // Check database config first
        if ($tierConfig) {
            if (isset($tierConfig['limits']['sites'])) {
                return (int) $tierConfig['limits']['sites'];
            }
            // Check features for multi_sites indicator
            if (isset($tierConfig['features']['multi_sites'])) {
                return 20; // Agency default
            }
        }
        return self::DEFAULT_SITES_LIMIT[$tier] ?? 1;
    }

    /**
     * Get storage limit for tier in bytes
     */
    private function getStorageLimitForTier(string $tier, ?array $tierConfig): int
    {
        // Check database config first (storage_limit_mb)
        if ($tierConfig && isset($tierConfig['storage_limit_mb'])) {
            return (int) $tierConfig['storage_limit_mb'] * 1024 * 1024;
        }
        if ($tierConfig && isset($tierConfig['limits']['storage_mb'])) {
            return (int) $tierConfig['limits']['storage_mb'] * 1024 * 1024;
        }
        return self::DEFAULT_STORAGE_LIMIT[$tier] ?? self::DEFAULT_STORAGE_LIMIT['free'];
    }

    /**
     * Get products limit for tier
     */
    private function getProductsLimitForTier(string $tier, ?array $tierConfig): int
    {
        // Check database config first
        if ($tierConfig && isset($tierConfig['limits']['products'])) {
            return (int) $tierConfig['limits']['products'];
        }
        // Check for unlimited_products feature
        if ($tierConfig && isset($tierConfig['features']['unlimited_products'])) {
            $value = is_array($tierConfig['features']['unlimited_products']) 
                ? $tierConfig['features']['unlimited_products']['value'] 
                : $tierConfig['features']['unlimited_products'];
            if ($value) return -1;
        }
        return self::DEFAULT_PRODUCTS_LIMIT[$tier] ?? 0;
    }

    /**
     * Get AI prompts limit for tier
     */
    private function getAIPromptsLimitForTier(string $tier, ?array $tierConfig): int
    {
        // Check database config first
        if ($tierConfig && isset($tierConfig['limits']['ai_prompts'])) {
            return (int) $tierConfig['limits']['ai_prompts'];
        }
        // Check for ai_unlimited feature
        if ($tierConfig && isset($tierConfig['features']['ai_unlimited'])) {
            $value = is_array($tierConfig['features']['ai_unlimited']) 
                ? $tierConfig['features']['ai_unlimited']['value'] 
                : $tierConfig['features']['ai_unlimited'];
            if ($value) return -1;
        }
        return self::DEFAULT_AI_PROMPTS_LIMIT[$tier] ?? 5;
    }

    /**
     * Check if user has access to a specific feature
     */
    public function hasFeature(User $user, string $feature): bool
    {
        $restrictions = $this->getRestrictions($user);
        return $restrictions['features'][$feature] ?? false;
    }

    /**
     * Check if user can create more sites
     */
    public function canCreateSite(User $user, int $currentSiteCount): bool
    {
        $restrictions = $this->getRestrictions($user);
        return $currentSiteCount < $restrictions['sites_limit'];
    }

    /**
     * Check if user can add more products
     */
    public function canAddProduct(User $user, int $currentProductCount): bool
    {
        $restrictions = $this->getRestrictions($user);
        $limit = $restrictions['products_limit'];
        
        if ($limit === -1) {
            return true; // Unlimited
        }
        
        return $currentProductCount < $limit;
    }

    /**
     * Get products limit for user
     */
    public function getProductsLimit(User $user): int
    {
        $restrictions = $this->getRestrictions($user);
        return $restrictions['products_limit'];
    }

    /**
     * Get storage limit for user in bytes
     */
    public function getStorageLimit(User $user): int
    {
        $restrictions = $this->getRestrictions($user);
        return $restrictions['storage_limit'];
    }

    /**
     * Get sites limit for user
     */
    public function getSitesLimit(User $user): int
    {
        $restrictions = $this->getRestrictions($user);
        return $restrictions['sites_limit'];
    }

    /**
     * Check if tier is at least a certain level
     */
    public function isAtLeastTier(User $user, string $requiredTier): bool
    {
        $userTier = $this->getUserTier($user);
        $userLevel = self::TIER_HIERARCHY[$userTier] ?? 0;
        $requiredLevel = self::TIER_HIERARCHY[$requiredTier] ?? 0;
        return $userLevel >= $requiredLevel;
    }

    /**
     * Get upgrade message for a feature
     */
    public function getUpgradeMessage(string $feature): string
    {
        $messages = [
            'custom_domain' => 'Upgrade to Starter plan to use custom domains.',
            'payment_integration' => 'Upgrade to Business plan for full payment integrations.',
            'ecommerce' => 'Upgrade to Starter plan to sell products.',
            'marketing_tools' => 'Upgrade to Business plan for marketing tools.',
            'remove_branding' => 'Upgrade to Business plan to remove MyGrowNet branding.',
            'ai_seo' => 'Upgrade to Business plan for AI SEO assistant.',
            'ai_unlimited' => 'Upgrade to Business plan for unlimited AI prompts.',
            'white_label' => 'Upgrade to Agency plan for white-label options.',
            'multi_site' => 'Upgrade to Agency plan to manage multiple sites.',
        ];

        return $messages[$feature] ?? 'Upgrade your plan to access this feature.';
    }

    /**
     * Get minimum tier required for a feature
     */
    public function getRequiredTier(string $feature): string
    {
        return $this->tierConfigService->getRequiredTierForFeature(self::MODULE_ID, $feature) ?? 'agency';
    }

    /**
     * Clear cache for user
     */
    public function clearCache(User $user): void
    {
        $tier = $this->getUserTier($user);
        Cache::forget("growbuilder_restrictions:{$user->id}:{$tier}");
        // Also clear tier config cache
        $this->tierConfigService->clearCache(self::MODULE_ID);
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        if ($bytes <= 0) return '0 B';
        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }
}
