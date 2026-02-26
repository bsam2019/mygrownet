<?php

namespace App\Application\Storage\Services;

use App\Infrastructure\Persistence\Eloquent\StoragePlan;
use App\Infrastructure\Persistence\Eloquent\UserStorageSubscription;
use Illuminate\Support\Facades\DB;

class StorageSubscriptionService
{
    /**
     * Provision storage from a GrowNet product purchase
     */
    public function provisionFromProduct(int $userId, $product): void
    {
        $metadata = is_string($product->metadata) 
            ? json_decode($product->metadata, true) 
            : $product->metadata;
            
        $planSlug = $metadata['plan_slug'] ?? null;
        
        if (!$planSlug) {
            throw new \DomainException('Product does not have storage plan metadata');
        }
        
        $storagePlan = StoragePlan::where('slug', $planSlug)->firstOrFail();
        
        // Create or update storage subscription
        UserStorageSubscription::updateOrCreate(
            ['user_id' => $userId],
            [
                'storage_plan_id' => $storagePlan->id,
                'status' => 'active',
                'start_at' => now(),
                'end_at' => $product->recurring_interval ? now()->addMonth() : null,
                'source' => 'grownet',
            ]
        );
    }
    
    /**
     * Sync storage subscription from GrowNet product subscriptions
     */
    public function syncFromGrowNetSubscription(int $userId): void
    {
        // Check if UserProductSubscription model exists
        if (!class_exists(\App\Models\UserProductSubscription::class)) {
            // Fallback: ensure user has at least starter plan
            $this->ensureStarterPlan($userId);
            return;
        }
        
        // Get active storage product subscription
        $subscription = \App\Models\UserProductSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->whereHas('product', function ($query) {
                $query->where('product_type', 'storage');
            })
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($subscription) {
            $this->provisionFromProduct($userId, $subscription->product);
        } else {
            // Downgrade to starter plan
            $this->downgradeToStarter($userId);
        }
    }
    
    /**
     * Downgrade user to starter plan
     */
    private function downgradeToStarter(int $userId): void
    {
        $starterPlan = StoragePlan::where('slug', 'starter')->firstOrFail();
        
        UserStorageSubscription::updateOrCreate(
            ['user_id' => $userId],
            [
                'storage_plan_id' => $starterPlan->id,
                'status' => 'active',
                'start_at' => now(),
                'end_at' => null,
                'source' => 'grownet',
            ]
        );
    }
    
    /**
     * Ensure user has at least starter plan
     */
    private function ensureStarterPlan(int $userId): void
    {
        $existing = UserStorageSubscription::where('user_id', $userId)->first();
        
        if (!$existing) {
            $this->downgradeToStarter($userId);
        }
    }
    
    /**
     * Check if user can upgrade to a plan
     */
    public function canUpgradeTo(int $userId, string $planSlug): bool
    {
        $currentSubscription = UserStorageSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->with('storagePlan')
            ->first();
        
        if (!$currentSubscription) {
            return true; // Can upgrade from nothing
        }
        
        $targetPlan = StoragePlan::where('slug', $planSlug)->first();
        
        if (!$targetPlan) {
            return false;
        }
        
        // Can upgrade if target plan has more quota
        return $targetPlan->quota_bytes > $currentSubscription->storagePlan->quota_bytes;
    }
}
