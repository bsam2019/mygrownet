<?php

namespace App\Domain\Module\Services;

use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Models\User;

/**
 * Feature Access Service
 * 
 * Domain service for checking feature-level access within modules.
 * Supports freemium model with tiered feature access.
 */
class FeatureAccessService
{
    public function __construct(
        private readonly ModuleAccessService $moduleAccessService,
        private readonly ModuleRepositoryInterface $moduleRepository
    ) {}

    /**
     * Check if user can access a specific feature
     */
    public function canAccess(User $user, string $moduleId, string $feature): bool
    {
        return $this->moduleAccessService->canAccessFeature(
            $user,
            new ModuleId($moduleId),
            $feature
        );
    }

    /**
     * Get all features available to the user for a module
     */
    public function getAvailableFeatures(User $user, string $moduleId): array
    {
        return $this->moduleAccessService->getUserFeatures(
            $user,
            new ModuleId($moduleId)
        );
    }

    /**
     * Get features that require upgrade
     */
    public function getLockedFeatures(User $user, string $moduleId): array
    {
        $module = $this->moduleRepository->findById(new ModuleId($moduleId));
        
        if (!$module) {
            return [];
        }

        $accessLevel = $this->moduleAccessService->getAccessLevel($user, new ModuleId($moduleId));
        $availableFeatures = $module->getFeaturesForTier($accessLevel);
        $allFeatures = array_keys($module->getConfiguration()->getFeatureAccess());

        return array_diff($allFeatures, $availableFeatures);
    }

    /**
     * Get the minimum tier required for a feature
     */
    public function getRequiredTierForFeature(string $moduleId, string $feature): ?string
    {
        $module = $this->moduleRepository->findById(new ModuleId($moduleId));
        
        if (!$module) {
            return null;
        }

        $featureAccess = $module->getConfiguration()->getFeatureAccess();
        $allowedTiers = $featureAccess[$feature] ?? [];

        if (empty($allowedTiers)) {
            return null; // Feature available to all
        }

        // Return the lowest tier that has access
        $tierOrder = ['free', 'basic', 'premium', 'business', 'enterprise'];
        
        foreach ($tierOrder as $tier) {
            if (in_array($tier, $allowedTiers)) {
                return $tier;
            }
        }

        return $allowedTiers[0] ?? null;
    }

    /**
     * Check if user needs to upgrade to access a feature
     */
    public function needsUpgradeForFeature(User $user, string $moduleId, string $feature): bool
    {
        $canAccess = $this->canAccess($user, $moduleId, $feature);
        
        if ($canAccess) {
            return false;
        }

        // Check if feature exists at all
        $requiredTier = $this->getRequiredTierForFeature($moduleId, $feature);
        
        return $requiredTier !== null;
    }

    /**
     * Get upgrade suggestion for a feature
     */
    public function getUpgradeSuggestion(User $user, string $moduleId, string $feature): ?array
    {
        if (!$this->needsUpgradeForFeature($user, $moduleId, $feature)) {
            return null;
        }

        $module = $this->moduleRepository->findById(new ModuleId($moduleId));
        
        if (!$module) {
            return null;
        }

        $requiredTier = $this->getRequiredTierForFeature($moduleId, $feature);
        $subscriptionTiers = $module->getConfiguration()->getSubscriptionTiers();
        $tierInfo = $subscriptionTiers[$requiredTier] ?? null;

        if (!$tierInfo) {
            return null;
        }

        return [
            'feature' => $feature,
            'required_tier' => $requiredTier,
            'tier_name' => $tierInfo['name'] ?? ucfirst($requiredTier),
            'price' => $tierInfo['price'] ?? 0,
            'currency' => $tierInfo['currency'] ?? 'ZMW',
            'billing_cycle' => $tierInfo['billing_cycle'] ?? 'monthly',
        ];
    }
}
