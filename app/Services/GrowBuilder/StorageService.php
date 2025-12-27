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
     */
    public const TIER_LIMITS = [
        'free' => 52428800,       // 50 MB
        'member' => 209715200,    // 200 MB
        'standard' => 1073741824, // 1 GB (1000 MB)
        'ecommerce' => 5368709120, // 5 GB (5000 MB)
    ];

    public function __construct(
        private ?TierConfigurationService $tierConfigService = null
    ) {}

    /**
     * Get storage limit for a tier from configuration (in bytes)
     */
    public function getStorageLimitForTier(string $tier): int
    {
        // Try to get from tier configuration
        if ($this->tierConfigService) {
            $limits = $this->tierConfigService->getTierLimits(self::MODULE_ID, $tier);
            if (isset($limits['storage_mb'])) {
                return $limits['storage_mb'] * 1024 * 1024; // Convert MB to bytes
            }
        }

        // Fallback to hardcoded limits
        return self::TIER_LIMITS[$tier] ?? self::TIER_LIMITS['free'];
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
