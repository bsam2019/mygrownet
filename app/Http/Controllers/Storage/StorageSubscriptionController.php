<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\StoragePlan;
use App\Infrastructure\Persistence\Eloquent\UserStorageSubscription;
use App\Infrastructure\Persistence\Eloquent\StorageUsage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StorageSubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;
        
        // Get all storage plans with their products
        $plans = StoragePlan::with('product')
            ->where('is_active', true)
            ->orderBy('quota_bytes')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'quota_bytes' => $plan->quota_bytes,
                    'max_file_size_bytes' => $plan->max_file_size_bytes,
                    'allow_sharing' => $plan->allow_sharing,
                    'product_id' => $plan->product_id,
                    'product' => $plan->product ? [
                        'id' => $plan->product->id,
                        'name' => $plan->product->name,
                        'price' => $plan->product->price,
                        'bp_value' => $plan->product->bp_value,
                    ] : null,
                ];
            });
        
        // Get current subscription
        $subscription = UserStorageSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->first();
        
        // Get usage
        $usage = StorageUsage::firstOrCreate(['user_id' => $userId]);
        $quotaBytes = $subscription ? $subscription->storagePlan->quota_bytes : 0;
        
        return Inertia::render('GrowBackup/Subscription', [
            'plans' => $plans,
            'currentSubscription' => $subscription ? [
                'id' => $subscription->id,
                'storage_plan_id' => $subscription->storage_plan_id,
                'status' => $subscription->status,
                'start_at' => $subscription->start_at->toISOString(),
                'end_at' => $subscription->end_at?->toISOString(),
            ] : null,
            'usage' => [
                'used_bytes' => $usage->used_bytes,
                'quota_bytes' => $quotaBytes,
                'percent_used' => $quotaBytes > 0 ? ($usage->used_bytes / $quotaBytes) * 100 : 0,
            ],
        ]);
    }
}
