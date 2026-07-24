<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Domain\Storage\Repositories\StorageSubscriptionRepositoryInterface;

class StorageUsageController extends Controller
{
    public function __construct(
        private StorageSubscriptionRepositoryInterface $subscriptionRepo
    ) {}

    public function show()
    {
        $userId = auth()->id();

        $subscription = $this->subscriptionRepo->getActiveSubscription($userId);

        if (!$subscription || !$subscription['storage_plan']['is_active']) {
            $freePlan = $this->subscriptionRepo->findFreePlan();

            if (!$freePlan) {
                $activePlans = collect($this->subscriptionRepo->getActivePlans());
                $freePlan = $activePlans->sortBy('quota_bytes')->first();
            }

            if (!$freePlan) {
                return response()->json(['error' => 'No active storage plans available'], 404);
            }

            $this->subscriptionRepo->createOrUpdateSubscription($userId, $freePlan['id']);
            $subscription = $this->subscriptionRepo->getActiveSubscription($userId);
        }

        $usage = $this->subscriptionRepo->getOrCreateUsage($userId);

        $quotaBytes = $subscription['storage_plan']['quota_bytes'];
        $usedBytes = $usage['used_bytes'] ?? 0;
        $percentUsed = $quotaBytes > 0 ? ($usedBytes / $quotaBytes) * 100 : 0;

        return response()->json([
            'used_bytes' => $usedBytes,
            'quota_bytes' => $quotaBytes,
            'remaining_bytes' => max(0, $quotaBytes - $usedBytes),
            'percent_used' => round($percentUsed, 2),
            'files_count' => $usage['files_count'] ?? 0,
            'plan_name' => $subscription['storage_plan']['name'],
            'plan_slug' => $subscription['storage_plan']['slug'],
            'near_limit' => $percentUsed >= 80,
            'formatted_used' => $this->formatBytes($usedBytes),
            'formatted_quota' => $this->formatBytes($quotaBytes),
        ]);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        $val = $bytes;
        while ($val >= 1024 && $i < count($units) - 1) {
            $val /= 1024;
            $i++;
        }
        return round($val, 2) . ' ' . $units[$i];
    }
}