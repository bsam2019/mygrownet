<?php

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\PlatformQuota;

class TenantUsageMeter
{
    /**
     * Check if tenant is within quota limits.
     */
    public function canUploadMinutes(int $organizationId, int $durationSeconds): bool
    {
        $quota = PlatformQuota::firstOrCreate(
            ['organization_id' => $organizationId],
            ['storage_minutes_limit' => 1000, 'delivery_gb_limit' => 100]
        );

        $durationMinutes = (int) ceil($durationSeconds / 60);

        return ($quota->current_storage_minutes + $durationMinutes) <= $quota->storage_minutes_limit;
    }

    /**
     * Record video storage minutes added.
     */
    public function recordStorageAdded(int $organizationId, int $durationSeconds): void
    {
        $durationMinutes = (int) ceil($durationSeconds / 60);

        $quota = PlatformQuota::firstOrCreate(
            ['organization_id' => $organizationId],
            ['storage_minutes_limit' => 1000, 'delivery_gb_limit' => 100]
        );

        $quota->increment('current_storage_minutes', $durationMinutes);
    }

    /**
     * Get usage metrics percentage.
     */
    public function getUsageSummary(int $organizationId): array
    {
        $quota = PlatformQuota::firstOrCreate(
            ['organization_id' => $organizationId],
            ['storage_minutes_limit' => 1000, 'delivery_gb_limit' => 100]
        );

        $storagePct = $quota->storage_minutes_limit > 0
            ? min(100, round(($quota->current_storage_minutes / $quota->storage_minutes_limit) * 100))
            : 0;

        $deliveryPct = $quota->delivery_gb_limit > 0
            ? min(100, round(($quota->current_delivery_gb / $quota->delivery_gb_limit) * 100))
            : 0;

        $deliveryMinutesLimit = ($quota->delivery_gb_limit ?? 100) * 50; // 100 GB ≈ 5,000 watch minutes
        $currentDeliveryMinutes = ($quota->current_delivery_gb ?? 0) * 50;

        return [
            'storage_minutes_limit' => $quota->storage_minutes_limit,
            'current_storage_minutes' => $quota->current_storage_minutes,
            'storage_percentage' => $storagePct,
            'delivery_minutes_limit' => $deliveryMinutesLimit,
            'current_delivery_minutes' => $currentDeliveryMinutes,
            'delivery_gb_limit' => $quota->delivery_gb_limit,
            'current_delivery_gb' => $quota->current_delivery_gb,
            'delivery_percentage' => $deliveryPct,
            'is_storage_exceeded' => $quota->current_storage_minutes >= $quota->storage_minutes_limit,
            'is_delivery_exceeded' => $quota->current_delivery_gb >= $quota->delivery_gb_limit,
        ];
    }
}
