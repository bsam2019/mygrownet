<?php

namespace App\Domain\Module\Services;

use App\Domain\Module\Entities\Module;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Enums\AccountType;
use App\Models\User;

/**
 * Module Access Service
 * 
 * Domain service for checking module access permissions.
 * Supports freemium model with free tier access.
 */
class ModuleAccessService
{
    public function __construct(
        private readonly ModuleRepositoryInterface $moduleRepository,
        private readonly ModuleSubscriptionRepositoryInterface $subscriptionRepository
    ) {}

    public function canAccess(User $user, ModuleId $moduleId): bool
    {
        $module = $this->moduleRepository->findById($moduleId);
        
        if (!$module) {
            return false;
        }

        if (!$module->isActive()) {
            return false;
        }

        // Check if user has required account type
        if (!$this->hasRequiredAccountType($user, $module)) {
            return false;
        }

        // If module doesn't require subscription, grant access
        if (!$module->requiresSubscription()) {
            return true;
        }

        // If module has free tier, grant basic access
        if ($module->hasFreeTier()) {
            return true;
        }

        // Check if user has active subscription
        return $this->hasActiveSubscription($user->id, $moduleId);
    }

    /**
     * Get the user's access level for a module
     * Returns: 'none', 'free', or the subscription tier name
     */
    public function getAccessLevel(User $user, ModuleId $moduleId): string
    {
        $module = $this->moduleRepository->findById($moduleId);
        
        if (!$module || !$module->isActive()) {
            return 'none';
        }

        if (!$this->hasRequiredAccountType($user, $module)) {
            return 'none';
        }

        // Check for active subscription first
        $subscription = $this->subscriptionRepository->findByUserAndModule($user->id, $moduleId);
        
        if ($subscription && $subscription->isActive()) {
            return $subscription->getTier();
        }

        // If module has free tier, return 'free'
        if ($module->hasFreeTier()) {
            return 'free';
        }

        // If module doesn't require subscription, return 'full'
        if (!$module->requiresSubscription()) {
            return 'full';
        }

        return 'none';
    }

    /**
     * Check if user can access a specific feature within a module
     */
    public function canAccessFeature(User $user, ModuleId $moduleId, string $feature): bool
    {
        $accessLevel = $this->getAccessLevel($user, $moduleId);
        
        if ($accessLevel === 'none') {
            return false;
        }

        $module = $this->moduleRepository->findById($moduleId);
        
        if (!$module) {
            return false;
        }

        return $module->isFeatureAvailableForTier($feature, $accessLevel);
    }

    /**
     * Get the limits for a user's current access level
     */
    public function getUserLimits(User $user, ModuleId $moduleId): array
    {
        $accessLevel = $this->getAccessLevel($user, $moduleId);
        
        if ($accessLevel === 'none') {
            return [];
        }

        $module = $this->moduleRepository->findById($moduleId);
        
        if (!$module) {
            return [];
        }

        return $module->getLimitsForTier($accessLevel);
    }

    /**
     * Get available features for user's current access level
     */
    public function getUserFeatures(User $user, ModuleId $moduleId): array
    {
        $accessLevel = $this->getAccessLevel($user, $moduleId);
        
        if ($accessLevel === 'none') {
            return [];
        }

        $module = $this->moduleRepository->findById($moduleId);
        
        if (!$module) {
            return [];
        }

        return $module->getFeaturesForTier($accessLevel);
    }

    public function getUserModules(User $user): array
    {
        $accountTypes = $user->account_types ?? [];
        $accessibleModules = [];

        foreach ($accountTypes as $accountType) {
            $modules = $this->moduleRepository->findByAccountType($accountType);
            
            foreach ($modules as $module) {
                if ($this->canAccess($user, $module->getId())) {
                    $accessibleModules[$module->getId()->value()] = $module;
                }
            }
        }

        return array_values($accessibleModules);
    }

    public function getAvailableModules(User $user): array
    {
        $accountTypes = $user->account_types ?? [];
        $availableModules = [];

        foreach ($accountTypes as $accountType) {
            $modules = $this->moduleRepository->findByAccountType($accountType);
            
            foreach ($modules as $module) {
                if (!$this->canAccess($user, $module->getId())) {
                    $availableModules[$module->getId()->value()] = $module;
                }
            }
        }

        return array_values($availableModules);
    }

    private function hasRequiredAccountType(User $user, Module $module): bool
    {
        $userAccountTypes = $user->account_types ?? [];
        $requiredTypes = $module->getAccountTypes();

        // Convert AccountType enums to strings for comparison
        $requiredTypeStrings = array_map(
            fn($type) => $type instanceof \App\Enums\AccountType ? $type->value : $type,
            $requiredTypes
        );

        foreach ($userAccountTypes as $userType) {
            if (in_array($userType, $requiredTypeStrings, true)) {
                return true;
            }
        }

        return false;
    }

    private function hasActiveSubscription(int $userId, ModuleId $moduleId): bool
    {
        $subscription = $this->subscriptionRepository->findByUserAndModule($userId, $moduleId);
        
        return $subscription && $subscription->isActive();
    }
}
