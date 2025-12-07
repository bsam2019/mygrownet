<?php

namespace App\Domain\Module\Services;

use App\Domain\Module\ValueObjects\ModuleId;
use App\Models\User;

/**
 * Unified Subscription Service
 * 
 * Facade service that provides a simple interface for subscription checks.
 * Used by middleware and controllers across all modules.
 */
class SubscriptionService
{
    public function __construct(
        private readonly UsageLimitService $usageLimitService,
        private readonly TierConfigurationService $tierConfig,
        private readonly ModuleAccessService $accessService
    ) {}

    /**
     * Check if user is an admin (has admin or superadmin role)
     * Admins have full access to all premium features
     */
    public function isAdmin(User $user): bool
    {
        // Check using Spatie permissions (primary method)
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole(['admin', 'superadmin']);
        }
        
        // Fallback: check is_admin attribute
        if (property_exists($user, 'is_admin') && $user->is_admin) {
            return true;
        }
        
        return false;
    }

    /**
     * Get user's current tier for a module
     * Admins always get 'business' (highest tier)
     */
    public function getUserTier(User $user, string $moduleId = 'growfinance'): string
    {
        return $this->accessService->getAccessLevel($user, ModuleId::fromString($moduleId));
    }

    /**
     * Get user's limits for a module
     */
    public function getUserLimits(User $user, string $moduleId = 'growfinance'): array
    {
        $tier = $this->getUserTier($user, $moduleId);
        return $this->tierConfig->getTierLimits($moduleId, $tier);
    }

    /**
     * Check if user has access to a feature
     */
    public function hasFeature(User $user, string $feature, string $moduleId = 'growfinance'): bool
    {
        return $this->usageLimitService->hasFeature($user, $moduleId, $feature);
    }

    /**
     * Check if user can perform an action (feature check)
     */
    public function canPerformAction(User $user, string $action, string $moduleId = 'growfinance'): bool
    {
        return $this->hasFeature($user, $action, $moduleId);
    }

    /**
     * Check if user can increment a metric (usage limit check)
     */
    public function canIncrement(User $user, string $metricKey, string $moduleId = 'growfinance'): array
    {
        return $this->usageLimitService->canIncrement($user, $moduleId, $metricKey);
    }

    /**
     * Check if user can access a report
     */
    public function canAccessReport(User $user, string $report, string $moduleId = 'growfinance'): bool
    {
        return $this->usageLimitService->canAccessReport($user, $moduleId, $report);
    }

    /**
     * Get the required tier for a feature
     */
    public function getRequiredTierForFeature(string $feature, string $moduleId = 'growfinance'): ?string
    {
        return $this->tierConfig->getRequiredTierForFeature($moduleId, $feature);
    }

    /**
     * Get complete usage summary for a user
     */
    public function getUsageSummary(User $user, string $moduleId = 'growfinance'): array
    {
        return $this->usageLimitService->getUsageSummary($user, $moduleId);
    }

    /**
     * Get upgrade suggestions based on current usage
     */
    public function getUpgradeSuggestions(User $user, string $moduleId = 'growfinance'): array
    {
        return $this->usageLimitService->getUpgradeSuggestions($user, $moduleId);
    }

    /**
     * Get all tiers for display on upgrade page
     */
    public function getAllTiers(string $moduleId = 'growfinance'): array
    {
        return $this->tierConfig->getAllTiersForDisplay($moduleId);
    }

    /**
     * Get tier pricing
     */
    public function getTierPricing(string $tier, string $moduleId = 'growfinance'): array
    {
        return $this->tierConfig->getTierPricing($moduleId, $tier);
    }

    /**
     * Check if user can upload a file
     */
    public function canUploadFile(User $user, int $fileSizeBytes, string $moduleId = 'growfinance'): array
    {
        return $this->usageLimitService->canUploadFile($user, $moduleId, $fileSizeBytes);
    }

    /**
     * Clear subscription/usage cache for a user
     */
    public function clearCache(User $user, string $moduleId = 'growfinance'): void
    {
        $this->usageLimitService->clearCache($user, $moduleId);
    }

    /**
     * Alias for clearCache - used by controllers
     */
    public function clearUsageCache(User $user, string $moduleId = 'growfinance'): void
    {
        $this->clearCache($user, $moduleId);
    }

    // =========================================================================
    // GrowFinance-specific convenience methods
    // =========================================================================

    /**
     * Check if user can create an invoice (usage limit check)
     */
    public function canCreateInvoice(User $user): array
    {
        return $this->canIncrement($user, 'invoices_per_month', 'growfinance');
    }

    /**
     * Check if user can create a transaction (usage limit check)
     */
    public function canCreateTransaction(User $user): array
    {
        return $this->canIncrement($user, 'transactions_per_month', 'growfinance');
    }

    /**
     * Check if user can add a vendor (usage limit check)
     */
    public function canAddVendor(User $user): array
    {
        return $this->canIncrement($user, 'vendors', 'growfinance');
    }

    /**
     * Check if user can add a customer (usage limit check)
     */
    public function canAddCustomer(User $user): array
    {
        return $this->canIncrement($user, 'customers', 'growfinance');
    }

    /**
     * Check if user can add an account (usage limit check)
     */
    public function canAddAccount(User $user): array
    {
        return $this->canIncrement($user, 'bank_accounts', 'growfinance');
    }

    /**
     * Check if user can upload a receipt (storage limit check)
     */
    public function canUploadReceipt(User $user, int $fileSizeBytes): array
    {
        return $this->canUploadFile($user, $fileSizeBytes, 'growfinance');
    }
}
