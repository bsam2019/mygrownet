<?php

namespace App\Application\UseCases\Module;

use App\Domain\Module\Services\FeatureAccessService;
use App\Models\User;

/**
 * Check Feature Access Use Case
 * 
 * Checks if a user can access a specific feature within a module.
 */
class CheckFeatureAccessUseCase
{
    public function __construct(
        private FeatureAccessService $featureAccessService
    ) {}

    /**
     * Check if user can access a feature
     */
    public function execute(User $user, string $moduleId, string $feature): array
    {
        $canAccess = $this->featureAccessService->canAccess($user, $moduleId, $feature);
        
        if ($canAccess) {
            return [
                'has_access' => true,
                'feature' => $feature,
                'module_id' => $moduleId,
            ];
        }

        // Get upgrade suggestion if feature is locked
        $needsUpgrade = $this->featureAccessService->needsUpgradeForFeature($user, $moduleId, $feature);
        
        if ($needsUpgrade) {
            $suggestion = $this->featureAccessService->getUpgradeSuggestion($user, $moduleId, $feature);
            
            return [
                'has_access' => false,
                'feature' => $feature,
                'module_id' => $moduleId,
                'reason' => 'upgrade_required',
                'upgrade_suggestion' => $suggestion,
            ];
        }

        return [
            'has_access' => false,
            'feature' => $feature,
            'module_id' => $moduleId,
            'reason' => 'feature_not_available',
        ];
    }

    /**
     * Get all available features for user
     */
    public function getAvailableFeatures(User $user, string $moduleId): array
    {
        return $this->featureAccessService->getAvailableFeatures($user, $moduleId);
    }

    /**
     * Get all locked features for user
     */
    public function getLockedFeatures(User $user, string $moduleId): array
    {
        return $this->featureAccessService->getLockedFeatures($user, $moduleId);
    }
}
