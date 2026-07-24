<?php

namespace App\Infrastructure\Storage\Persistence\Repositories;

use App\Domain\Storage\Repositories\StorageSubscriptionRepositoryInterface;
use App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageUsage;

class EloquentStorageSubscriptionRepository implements StorageSubscriptionRepositoryInterface
{
    public function getActivePlans(): array
    {
        return StoragePlan::where('is_active', true)
            ->orderBy('quota_bytes')
            ->get()
            ->toArray();
    }

    public function findPlanById(string $planId): ?array
    {
        $plan = StoragePlan::with('product')->find($planId);
        return $plan ? $plan->toArray() : null;
    }

    public function findFreePlan(): ?array
    {
        $plan = StoragePlan::where('slug', 'free')->where('is_active', true)->first();
        return $plan ? $plan->toArray() : null;
    }

    public function getActiveSubscription(int $userId): ?array
    {
        $subscription = UserStorageSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->with('storagePlan')
            ->first();

        return $subscription ? $subscription->toArray() : null;
    }

    public function createOrUpdateSubscription(int $userId, string $planId, string $billingCycle = 'monthly', string $status = 'active'): void
    {
        $plan = StoragePlan::findOrFail($planId);

        UserStorageSubscription::updateOrCreate(
            ['user_id' => $userId, 'status' => 'active'],
            [
                'storage_plan_id' => $planId,
                'billing_cycle' => $billingCycle,
                'status' => $status,
                'started_at' => now(),
            ]
        );
    }

    public function getOrCreateUsage(int $userId): array
    {
        $usage = StorageUsage::firstOrCreate(
            ['user_id' => $userId],
            ['used_bytes' => 0, 'files_count' => 0]
        );

        return $usage->toArray();
    }

    public function updateUsage(int $userId, int $usedBytes, int $filesCount): void
    {
        StorageUsage::updateOrCreate(
            ['user_id' => $userId],
            ['used_bytes' => $usedBytes, 'files_count' => $filesCount]
        );
    }
}