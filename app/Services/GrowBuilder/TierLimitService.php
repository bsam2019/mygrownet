<?php

namespace App\Services\GrowBuilder;

use App\Domain\Module\Services\SubscriptionService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Models\User;

/**
 * Service to get GrowBuilder tier limits from database
 * Replaces hardcoded SitePlan limits with dynamic database-driven limits
 */
class TierLimitService
{
    private const MODULE_ID = 'growbuilder';

    public function __construct(
        private SubscriptionService $subscriptionService,
        private TierConfigurationService $tierConfigService
    ) {}

    /**
     * Get page limit for a user's current tier
     */
    public function getPageLimit(User $user): int
    {
        $tier = $this->subscriptionService->getUserTier($user, self::MODULE_ID) ?? 'free';
        return $this->tierConfigService->getLimit(self::MODULE_ID, $tier, 'pages');
    }

    /**
     * Get product limit for a user's current tier
     */
    public function getProductLimit(User $user): int
    {
        $tier = $this->subscriptionService->getUserTier($user, self::MODULE_ID) ?? 'free';
        return $this->tierConfigService->getLimit(self::MODULE_ID, $tier, 'products');
    }

    /**
     * Get storage limit (in MB) for a user's current tier
     */
    public function getStorageLimit(User $user): int
    {
        $tier = $this->subscriptionService->getUserTier($user, self::MODULE_ID) ?? 'free';
        return $this->tierConfigService->getLimit(self::MODULE_ID, $tier, 'storage_mb');
    }

    /**
     * Get site limit for a user's current tier
     */
    public function getSiteLimit(User $user): int
    {
        $tier = $this->subscriptionService->getUserTier($user, self::MODULE_ID) ?? 'free';
        return $this->tierConfigService->getLimit(self::MODULE_ID, $tier, 'sites');
    }

    /**
     * Check if user can create more pages
     */
    public function canCreatePage(User $user, int $currentPageCount): bool
    {
        $limit = $this->getPageLimit($user);
        
        // -1 means unlimited
        if ($limit === -1) {
            return true;
        }

        return $currentPageCount < $limit;
    }

    /**
     * Check if user can create more products
     */
    public function canCreateProduct(User $user, int $currentProductCount): bool
    {
        $limit = $this->getProductLimit($user);
        
        // -1 means unlimited
        if ($limit === -1) {
            return true;
        }

        return $currentProductCount < $limit;
    }

    /**
     * Check if user can create more sites
     */
    public function canCreateSite(User $user, int $currentSiteCount): bool
    {
        $limit = $this->getSiteLimit($user);
        
        // -1 means unlimited
        if ($limit === -1) {
            return true;
        }

        return $currentSiteCount < $limit;
    }

    /**
     * Get all limits for a user
     */
    public function getAllLimits(User $user): array
    {
        $tier = $this->subscriptionService->getUserTier($user, self::MODULE_ID) ?? 'free';
        
        return [
            'pages' => $this->tierConfigService->getLimit(self::MODULE_ID, $tier, 'pages'),
            'products' => $this->tierConfigService->getLimit(self::MODULE_ID, $tier, 'products'),
            'storage_mb' => $this->tierConfigService->getLimit(self::MODULE_ID, $tier, 'storage_mb'),
            'sites' => $this->tierConfigService->getLimit(self::MODULE_ID, $tier, 'sites'),
        ];
    }
}
