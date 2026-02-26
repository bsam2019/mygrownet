<?php

namespace App\Domain\Storage\Services;

use App\Domain\Storage\ValueObjects\FileSize;
use App\Domain\Storage\ValueObjects\StorageQuota;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageUsage;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;
use Illuminate\Support\Facades\DB;

class QuotaEnforcementService
{
    public function canUpload(int $userId, FileSize $fileSize): bool
    {
        $quota = $this->getQuota($userId);
        return $quota->canAccommodate($fileSize);
    }

    public function getQuota(int $userId): StorageQuota
    {
        $subscription = $this->getActiveSubscription($userId);
        $usage = StorageUsage::firstOrCreate(['user_id' => $userId]);

        $limit = FileSize::fromBytes($subscription->storagePlan->quota_bytes);
        $used = FileSize::fromBytes($usage->used_bytes);

        return StorageQuota::create($limit, $used);
    }

    public function getRemainingQuota(int $userId): FileSize
    {
        $quota = $this->getQuota($userId);
        return $quota->getRemaining();
    }

    public function incrementUsage(int $userId, FileSize $fileSize): void
    {
        DB::transaction(function () use ($userId, $fileSize) {
            StorageUsage::where('user_id', $userId)->increment('used_bytes', $fileSize->toBytes());
            StorageUsage::where('user_id', $userId)->increment('files_count');
        });
    }

    public function decrementUsage(int $userId, FileSize $fileSize): void
    {
        DB::transaction(function () use ($userId, $fileSize) {
            $usage = StorageUsage::where('user_id', $userId)->lockForUpdate()->first();
            
            if (!$usage) {
                return; // No usage record to decrement
            }
            
            // Safe decrement - prevent going below 0
            $newBytes = max(0, $usage->used_bytes - $fileSize->toBytes());
            $newCount = max(0, $usage->files_count - 1);
            
            $usage->update([
                'used_bytes' => $newBytes,
                'files_count' => $newCount,
            ]);
        });
    }

    private function getActiveSubscription(int $userId): UserStorageSubscription
    {
        return UserStorageSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->with('storagePlan')
            ->firstOrFail();
    }
}
