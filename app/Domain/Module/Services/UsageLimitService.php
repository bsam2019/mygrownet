<?php

namespace App\Domain\Module\Services;

use App\Domain\Module\Contracts\ModuleUsageProviderInterface;
use App\Models\User;

/**
 * Usage Limit Service
 * 
 * Centralized service for checking usage limits across all modules.
 * Uses TierConfigurationService for limits and ModuleUsageProviders for current usage.
 */
class UsageLimitService
{
    private array $providers = [];

    public function __construct(
        private readonly TierConfigurationService $tierConfig,
        private readonly ModuleAccessService $accessService
    ) {}

    /**
     * Register a usage provider for a module
     */
    public function registerProvider(ModuleUsageProviderInterface $provider): void
    {
        $this->providers[$provider->getModuleId()] = $provider;
    }

    /**
     * Get the provider for a module
     */
    public function getProvider(string $moduleId): ?ModuleUsageProviderInterface
    {
        return $this->providers[$moduleId] ?? null;
    }

    /**
     * Check if user is an admin (has admin or superadmin role)
     * 
     * Uses Spatie Permission package roles:
     * - superadmin: Super Administrator - Highest level access
     * - admin/Admin/Administrator: Administrator - Full platform access
     */
    private function isAdmin(User $user): bool
    {
        // Check using Spatie permissions (primary method)
        // Support multiple naming conventions used in the system
        if (method_exists($user, 'hasRole')) {
            $adminRoles = ['admin', 'superadmin', 'Admin', 'Administrator', 'Super Admin', 'super-admin'];
            if ($user->hasRole($adminRoles)) {
                return true;
            }
            
            // Also check if any of the user's roles contain 'admin' (case-insensitive)
            foreach ($user->roles as $role) {
                if (stripos($role->name, 'admin') !== false) {
                    return true;
                }
            }
        }
        
        // Fallback: check is_admin attribute
        if (isset($user->is_admin) && $user->is_admin) {
            return true;
        }
        
        // Fallback: check role attribute (legacy)
        if (isset($user->role) && stripos($user->role, 'admin') !== false) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can perform an action that would increase a metric
     * 
     * @return array{allowed: bool, remaining: int, limit: int, used: int, reason?: string}
     */
    public function canIncrement(User $user, string $moduleId, string $metricKey): array
    {
        // Admins have unlimited access
        if ($this->isAdmin($user)) {
            return [
                'allowed' => true,
                'remaining' => -1,
                'limit' => -1,
                'used' => $this->getCurrentUsage($user, $moduleId, $metricKey),
            ];
        }

        $tier = $this->getUserTier($user, $moduleId);
        $limit = $this->tierConfig->getLimit($moduleId, $tier, $metricKey);

        // Unlimited
        if ($limit === -1) {
            return [
                'allowed' => true,
                'remaining' => -1,
                'limit' => -1,
                'used' => $this->getCurrentUsage($user, $moduleId, $metricKey),
            ];
        }

        // Not available on this tier
        if ($limit === 0) {
            $requiredTier = $this->tierConfig->getRequiredTierForLimit($moduleId, $metricKey, 1);
            return [
                'allowed' => false,
                'remaining' => 0,
                'limit' => 0,
                'used' => 0,
                'reason' => "This feature is not available on the {$tier} plan. Upgrade to {$requiredTier} or higher.",
            ];
        }

        $currentUsage = $this->getCurrentUsage($user, $moduleId, $metricKey);
        $remaining = max(0, $limit - $currentUsage);

        if ($currentUsage >= $limit) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'limit' => $limit,
                'used' => $currentUsage,
                'reason' => "You've reached your limit of {$limit}. Upgrade your plan for more.",
            ];
        }

        return [
            'allowed' => true,
            'remaining' => $remaining,
            'limit' => $limit,
            'used' => $currentUsage,
        ];
    }

    /**
     * Check if user has access to a feature
     * Admins have access to all features
     */
    public function hasFeature(User $user, string $moduleId, string $feature): bool
    {
        // Admins have access to all features
        if ($this->isAdmin($user)) {
            return true;
        }
        
        $tier = $this->getUserTier($user, $moduleId);
        return $this->tierConfig->hasFeature($moduleId, $tier, $feature);
    }

    /**
     * Check if user can access a report
     * Admins have access to all reports
     */
    public function canAccessReport(User $user, string $moduleId, string $report): bool
    {
        // Admins have access to all reports
        if ($this->isAdmin($user)) {
            return true;
        }
        
        $tier = $this->getUserTier($user, $moduleId);
        return $this->tierConfig->hasReport($moduleId, $tier, $report);
    }

    /**
     * Check if user can upload a file of given size
     * Admins have unlimited storage
     */
    public function canUploadFile(User $user, string $moduleId, int $fileSizeBytes, string $storageMetricKey = 'receipt_storage_mb'): array
    {
        // Admins have unlimited storage
        if ($this->isAdmin($user)) {
            return ['allowed' => true];
        }

        $tier = $this->getUserTier($user, $moduleId);
        $storageLimitMb = $this->tierConfig->getLimit($moduleId, $tier, $storageMetricKey);

        // Not available
        if ($storageLimitMb === 0) {
            return [
                'allowed' => false,
                'reason' => 'File storage is not available on your plan. Upgrade to Basic or higher.',
            ];
        }

        // Unlimited
        if ($storageLimitMb === -1) {
            return ['allowed' => true];
        }

        $provider = $this->getProvider($moduleId);
        $usedBytes = $provider ? $provider->getStorageUsed($user) : 0;
        $limitBytes = $storageLimitMb * 1024 * 1024;
        $remainingBytes = $limitBytes - $usedBytes;

        if ($fileSizeBytes > $remainingBytes) {
            return [
                'allowed' => false,
                'reason' => 'Storage limit exceeded. Upgrade your plan for more storage.',
                'used_mb' => round($usedBytes / 1024 / 1024, 2),
                'limit_mb' => $storageLimitMb,
                'remaining_mb' => round($remainingBytes / 1024 / 1024, 2),
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Get complete usage summary for a user
     * 
     * Returns a flat structure with direct metric keys for frontend compatibility:
     * - transactions, invoices, customers, vendors (as UsageItem objects)
     * - storage (as {used_mb, limit_mb})
     * - features, reports (as arrays)
     * 
     * Admins get 'business' tier with unlimited access
     */
    public function getUsageSummary(User $user, string $moduleId): array
    {
        $isAdmin = $this->isAdmin($user);
        $tier = $this->getUserTier($user, $moduleId);
        $tierConfig = $this->tierConfig->getTierConfig($moduleId, $tier);
        
        // Handle case where tier is 'none' or config not found - default to free tier
        if (!$tierConfig) {
            $tierConfig = $this->tierConfig->getTierConfig($moduleId, 'free');
            $tier = 'free';
        }
        
        $limits = $tierConfig['limits'] ?? [];
        
        // For admins, set all limits to unlimited (-1)
        if ($isAdmin) {
            foreach ($limits as $key => $value) {
                $limits[$key] = -1;
            }
        }

        $summary = [
            'tier' => $tier,
            'tier_name' => $isAdmin ? 'Administrator' : ($tierConfig['name'] ?? ucfirst($tier)),
            'is_admin' => $isAdmin,
            'features' => $tierConfig['features'] ?? [],
            'reports' => $tierConfig['reports'] ?? [],
        ];

        // Map config keys to frontend-friendly keys
        $keyMapping = [
            'transactions_per_month' => 'transactions',
            'invoices_per_month' => 'invoices',
            'customers' => 'customers',
            'vendors' => 'vendors',
            'bank_accounts' => 'accounts',
            'receipt_storage_mb' => 'storage',
            'team_members' => 'team_members',
        ];

        // Build flat metric structure for frontend compatibility
        foreach ($limits as $metricKey => $limit) {
            $currentUsage = $this->getCurrentUsage($user, $moduleId, $metricKey);
            $frontendKey = $keyMapping[$metricKey] ?? $metricKey;

            // Handle storage separately with different structure
            if ($frontendKey === 'storage') {
                $summary['storage'] = [
                    'used_mb' => $currentUsage,
                    'limit_mb' => $limit === -1 ? 0 : $limit,
                ];
                continue;
            }

            // Standard metrics get flat structure
            $summary[$frontendKey] = [
                'used' => $currentUsage,
                'limit' => $limit,
                'remaining' => $limit === -1 ? -1 : max(0, $limit - $currentUsage),
                'allowed' => $limit === -1 || $currentUsage < $limit,
            ];
        }

        // Ensure all expected keys exist with defaults for frontend compatibility
        $defaultMetrics = ['transactions', 'invoices', 'customers', 'vendors', 'accounts'];
        foreach ($defaultMetrics as $key) {
            if (!isset($summary[$key])) {
                $summary[$key] = [
                    'used' => 0,
                    'limit' => 0,
                    'remaining' => 0,
                    'allowed' => false,
                ];
            }
        }

        // Ensure storage key exists even if not in limits
        if (!isset($summary['storage'])) {
            $summary['storage'] = ['used_mb' => 0, 'limit_mb' => 0];
        }

        return $summary;
    }

    /**
     * Get upgrade suggestions based on current usage
     */
    public function getUpgradeSuggestions(User $user, string $moduleId): array
    {
        $tier = $this->getUserTier($user, $moduleId);
        $summary = $this->getUsageSummary($user, $moduleId);
        $suggestions = [];

        foreach ($summary['metrics'] as $metricKey => $metric) {
            // Suggest upgrade if usage is above 80% of limit
            if (!$metric['unlimited'] && !$metric['not_available'] && $metric['percentage'] >= 80) {
                $nextTier = $this->getNextTierWithHigherLimit($moduleId, $tier, $metricKey);
                if ($nextTier) {
                    $suggestions[] = [
                        'metric' => $metricKey,
                        'label' => $metric['label'],
                        'current_usage' => $metric['percentage'] . '%',
                        'suggested_tier' => $nextTier,
                        'pricing' => $this->tierConfig->getTierPricing($moduleId, $nextTier),
                    ];
                }
            }
        }

        return $suggestions;
    }

    /**
     * Get user's current tier for a module
     */
    private function getUserTier(User $user, string $moduleId): string
    {
        return $this->accessService->getAccessLevel(
            $user,
            \App\Domain\Module\ValueObjects\ModuleId::fromString($moduleId)
        );
    }

    /**
     * Get current usage for a metric
     */
    private function getCurrentUsage(User $user, string $moduleId, string $metricKey): int
    {
        $provider = $this->getProvider($moduleId);
        
        if (!$provider) {
            return 0;
        }

        return $provider->getMetric($user, $metricKey);
    }

    /**
     * Get the next tier that has a higher limit for a metric
     */
    private function getNextTierWithHigherLimit(string $moduleId, string $currentTier, string $metricKey): ?string
    {
        $tierOrder = ['free', 'basic', 'professional', 'business'];
        $currentIndex = array_search($currentTier, $tierOrder);
        
        if ($currentIndex === false) {
            return null;
        }

        $currentLimit = $this->tierConfig->getLimit($moduleId, $currentTier, $metricKey);

        for ($i = $currentIndex + 1; $i < count($tierOrder); $i++) {
            $nextTier = $tierOrder[$i];
            $nextLimit = $this->tierConfig->getLimit($moduleId, $nextTier, $metricKey);
            
            // Higher limit or unlimited
            if ($nextLimit === -1 || $nextLimit > $currentLimit) {
                return $nextTier;
            }
        }

        return null;
    }

    /**
     * Clear usage cache for a user
     */
    public function clearCache(User $user, string $moduleId): void
    {
        $provider = $this->getProvider($moduleId);
        
        if ($provider && method_exists($provider, 'clearCache')) {
            $provider->clearCache($user);
        }
        
        // Also clear any cached tier/access data
        $this->accessService->clearCache($user, $moduleId);
    }
}
