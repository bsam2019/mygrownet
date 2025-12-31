<?php

namespace App\Services\GrowBuilder;

use App\Domain\Module\Services\TierConfigurationService;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderMedia;
use Illuminate\Support\Facades\Storage;

class StorageService
{
    private const MODULE_ID = 'growbuilder';

    /**
     * Default storage limits by tier (in bytes) - fallback if not in tier config
     * 
     * For Free/Starter/Business: Each subscription = 1 site with full tier storage
     * For Agency: 20 sites sharing 10GB total = 500MB per site
     */
    public const TIER_LIMITS = [
        'free' => 524288000,       // 500 MB per site (1 site per subscription)
        'starter' => 1073741824,   // 1 GB per site (1 site per subscription)
        'business' => 2147483648,  // 2 GB per site (1 site per subscription)
        'agency' => 524288000,     // 500 MB per site (up to 20 sites, 10GB total)
        // Legacy tiers (for backward compatibility)
        'member' => 524288000,     // 500 MB (maps to free)
        'standard' => 1073741824,  // 1 GB (maps to starter)
        'ecommerce' => 2147483648, // 2 GB (maps to business)
    ];

    /**
     * Total storage allocation per tier (for display purposes)
     */
    public const TIER_TOTAL_STORAGE = [
        'free' => 524288000,       // 500 MB total
        'starter' => 1073741824,   // 1 GB total
        'business' => 2147483648,  // 2 GB total
        'agency' => 10737418240,   // 10 GB total (shared across up to 20 sites)
    ];

    public function __construct(
        private ?TierConfigurationService $tierConfigService = null
    ) {}

    /**
     * Get storage limit for a tier from configuration (in bytes)
     * This returns the PER-SITE storage allocation
     * 
     * - Free/Starter/Business: Full tier storage per site (1 site per subscription)
     * - Agency: 500MB per site (10GB shared across up to 20 sites)
     */
    public function getStorageLimitForTier(string $tier): int
    {
        // Try to get from tier configuration
        if ($this->tierConfigService) {
            $limits = $this->tierConfigService->getTierLimits(self::MODULE_ID, $tier);
            
            // For agency, always use 500MB per site regardless of DB config
            if ($tier === 'agency') {
                return 524288000; // 500 MB per site
            }
            
            if (isset($limits['storage_mb'])) {
                return $limits['storage_mb'] * 1024 * 1024; // Convert MB to bytes
            }
        }

        // Fallback to hardcoded limits
        return self::TIER_LIMITS[$tier] ?? self::TIER_LIMITS['free'];
    }

    /**
     * Get total storage allocation for a tier (for display purposes)
     * This is the total storage the tier provides, not per-site
     */
    public function getTotalStorageForTier(string $tier): int
    {
        return self::TIER_TOTAL_STORAGE[$tier] ?? self::TIER_TOTAL_STORAGE['free'];
    }

    /**
     * Get storage limit for a site based on its plan
     */
    public function getStorageLimitForSite(GrowBuilderSite $site): int
    {
        $plan = $site->plan ?? 'free';
        return $this->getStorageLimitForTier($plan);
    }

    /**
     * Get total storage used by a user across all their sites (for display purposes)
     */
    public function getTotalStorageUsedByUser(int $userId): int
    {
        return GrowBuilderSite::where('user_id', $userId)
            ->whereNull('deleted_at')
            ->sum('storage_used');
    }

    /**
     * Get user's storage summary across all sites
     */
    public function getUserStorageStats(int $userId, string $defaultTier = 'free'): array
    {
        $sites = GrowBuilderSite::where('user_id', $userId)
            ->whereNull('deleted_at')
            ->get();
        
        $totalUsed = $sites->sum('storage_used');
        $totalAllocated = $sites->sum('storage_limit');
        
        // Group by plan
        $byPlan = $sites->groupBy('plan')->map(function ($planSites, $plan) {
            return [
                'plan' => $plan ?: 'free',
                'sites_count' => $planSites->count(),
                'storage_used' => $planSites->sum('storage_used'),
                'storage_limit' => $planSites->sum('storage_limit'),
            ];
        })->values()->toArray();
        
        return [
            'total_sites' => $sites->count(),
            'total_used' => $totalUsed,
            'total_used_formatted' => $this->formatBytes($totalUsed),
            'total_allocated' => $totalAllocated,
            'total_allocated_formatted' => $this->formatBytes($totalAllocated),
            'by_plan' => $byPlan,
        ];
    }

    /**
     * Calculate storage used by a site
     */
    public function calculateStorageUsed(int $siteId): int
    {
        $totalSize = 0;
        $directory = "growbuilder/{$siteId}";

        // Calculate from media records
        $mediaSize = GrowBuilderMedia::where('site_id', $siteId)->sum('size');
        $totalSize += $mediaSize;

        // Also check actual disk usage for variants
        if (Storage::disk('public')->exists($directory)) {
            $files = Storage::disk('public')->allFiles($directory);
            foreach ($files as $file) {
                $totalSize += Storage::disk('public')->size($file);
            }
            // Avoid double counting - subtract media size since we counted files
            $totalSize = max($totalSize - $mediaSize, $mediaSize);
        }

        return $totalSize;
    }

    /**
     * Update storage usage for a site
     */
    public function updateStorageUsage(GrowBuilderSite $site): GrowBuilderSite
    {
        $storageUsed = $this->calculateStorageUsed($site->id);
        
        $site->update([
            'storage_used' => $storageUsed,
            'storage_calculated_at' => now(),
        ]);

        return $site->fresh();
    }

    /**
     * Check if site has available storage
     */
    public function hasAvailableStorage(GrowBuilderSite $site, int $requiredBytes = 0): bool
    {
        return ($site->storage_used + $requiredBytes) <= $site->storage_limit;
    }

    /**
     * Get storage percentage used
     */
    public function getStoragePercentage(GrowBuilderSite $site): float
    {
        if ($site->storage_limit <= 0) {
            return 100;
        }
        return min(100, round(($site->storage_used / $site->storage_limit) * 100, 2));
    }

    /**
     * Get remaining storage in bytes
     */
    public function getRemainingStorage(GrowBuilderSite $site): int
    {
        return max(0, $site->storage_limit - $site->storage_used);
    }

    /**
     * Format bytes to human readable
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        if ($bytes <= 0) {
            return '0 B';
        }

        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    /**
     * Get default storage limit for a plan
     */
    public function getDefaultLimitForPlan(string $plan): int
    {
        return $this->getStorageLimitForTier($plan);
    }

    /**
     * Get all tier limits for display
     */
    public function getAllTierLimits(): array
    {
        $limits = [];
        foreach (array_keys(self::TIER_LIMITS) as $tier) {
            $limitBytes = $this->getStorageLimitForTier($tier);
            $limits[$tier] = [
                'bytes' => $limitBytes,
                'formatted' => $this->formatBytes($limitBytes),
                'mb' => round($limitBytes / 1024 / 1024),
            ];
        }
        return $limits;
    }

    /**
     * Update storage limit for a site
     */
    public function setStorageLimit(GrowBuilderSite $site, int $limitBytes): GrowBuilderSite
    {
        $site->update(['storage_limit' => $limitBytes]);
        return $site->fresh();
    }

    /**
     * Recalculate storage for all sites
     */
    public function recalculateAllSites(): int
    {
        $count = 0;
        GrowBuilderSite::chunk(100, function ($sites) use (&$count) {
            foreach ($sites as $site) {
                $this->updateStorageUsage($site);
                $count++;
            }
        });
        return $count;
    }

    /**
     * Get storage stats for admin dashboard
     */
    public function getGlobalStorageStats(): array
    {
        $sites = GrowBuilderSite::whereNull('deleted_at')
            ->orWhere('status', '!=', 'deleted')
            ->get();

        $totalUsed = $sites->sum('storage_used');
        $totalAllocated = $sites->sum('storage_limit');
        $sitesOverLimit = $sites->filter(fn($s) => $s->storage_used > $s->storage_limit)->count();
        $sitesNearLimit = $sites->filter(fn($s) => 
            $s->storage_limit > 0 && 
            ($s->storage_used / $s->storage_limit) >= 0.8 &&
            $s->storage_used <= $s->storage_limit
        )->count();

        return [
            'total_used' => $totalUsed,
            'total_used_formatted' => $this->formatBytes($totalUsed),
            'total_allocated' => $totalAllocated,
            'total_allocated_formatted' => $this->formatBytes($totalAllocated),
            'sites_over_limit' => $sitesOverLimit,
            'sites_near_limit' => $sitesNearLimit,
            'average_usage' => $sites->count() > 0 ? round($totalUsed / $sites->count()) : 0,
            'average_usage_formatted' => $sites->count() > 0 ? $this->formatBytes(round($totalUsed / $sites->count())) : '0 B',
        ];
    }
}
