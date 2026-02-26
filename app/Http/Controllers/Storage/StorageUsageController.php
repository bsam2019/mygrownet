<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageUsage;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;

class StorageUsageController extends Controller
{
    public function show()
    {
        $userId = auth()->id();

        $subscription = UserStorageSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->with('storagePlan')
            ->first();

        // Check if subscription exists and plan is active
        if (!$subscription || !$subscription->storagePlan->is_active) {
            // Migrate to free plan if subscription doesn't exist or plan is inactive
            $freePlan = \App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan::where('slug', 'free')
                ->where('is_active', true)
                ->first();
            
            if (!$freePlan) {
                // Fallback to first active plan if free doesn't exist
                $freePlan = \App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan::where('is_active', true)
                    ->orderBy('quota_bytes')
                    ->first();
            }
            
            if (!$freePlan) {
                return response()->json([
                    'error' => 'No active storage plans available'
                ], 404);
            }
            
            if ($subscription) {
                // Update existing subscription to free plan
                $subscription->update([
                    'storage_plan_id' => $freePlan->id,
                ]);
                $subscription->load('storagePlan');
            } else {
                // Create new subscription
                $subscription = UserStorageSubscription::create([
                    'user_id' => $userId,
                    'storage_plan_id' => $freePlan->id,
                    'status' => 'active',
                    'started_at' => now(),
                ]);
                $subscription->load('storagePlan');
            }
        }

        $usage = StorageUsage::where('user_id', $userId)->first();
        
        if (!$usage) {
            $usage = StorageUsage::create([
                'user_id' => $userId,
                'used_bytes' => 0,
                'files_count' => 0,
            ]);
        }

        $quotaBytes = $subscription->storagePlan->quota_bytes;
        $usedBytes = $usage->used_bytes ?? 0;
        $percentUsed = $quotaBytes > 0 ? ($usedBytes / $quotaBytes) * 100 : 0;

        return response()->json([
            'used_bytes' => $usedBytes,
            'quota_bytes' => $quotaBytes,
            'remaining_bytes' => max(0, $quotaBytes - $usedBytes),
            'percent_used' => round($percentUsed, 2),
            'files_count' => $usage->files_count ?? 0,
            'plan_name' => $subscription->storagePlan->name,
            'plan_slug' => $subscription->storagePlan->slug,
            'near_limit' => $percentUsed >= 80,
            'formatted_used' => $usage->formatted_used ?? '0 B',
            'formatted_quota' => $subscription->storagePlan->formatted_quota ?? '0 B',
        ]);
    }
}
