<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageUsage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get all active plans
        $plans = StoragePlan::where('is_active', true)
            ->orderBy('quota_bytes')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'quota_bytes' => $plan->quota_bytes,
                    'max_file_size_bytes' => $plan->max_file_size_bytes,
                    'price_monthly' => $plan->price_monthly ?? 0,
                    'price_annual' => $plan->price_annual ?? 0,
                    'allow_sharing' => $plan->allow_sharing,
                    'allow_public_profile_files' => $plan->allow_public_profile_files,
                    'features' => $plan->features ? json_decode($plan->features, true) : [],
                    'is_popular' => $plan->is_popular ?? false,
                ];
            });
        
        // Get current subscription
        $subscription = UserStorageSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('storagePlan')
            ->first();
        
        // Check if subscription exists and plan is active
        if (!$subscription || !$subscription->storagePlan->is_active) {
            // Migrate to free plan if subscription doesn't exist or plan is inactive
            $freePlan = StoragePlan::where('slug', 'free')->where('is_active', true)->first();
            
            if (!$freePlan) {
                // Fallback to first active plan if free doesn't exist
                $freePlan = StoragePlan::where('is_active', true)->orderBy('quota_bytes')->first();
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
                    'user_id' => $user->id,
                    'storage_plan_id' => $freePlan->id,
                    'status' => 'active',
                    'started_at' => now(),
                ]);
                $subscription->load('storagePlan');
            }
        }
        
        $currentPlan = [
            'id' => $subscription->storagePlan->id,
            'name' => $subscription->storagePlan->name,
            'slug' => $subscription->storagePlan->slug,
            'quota_bytes' => $subscription->storagePlan->quota_bytes,
            'max_file_size_bytes' => $subscription->storagePlan->max_file_size_bytes,
        ];
        
        // Get usage
        $usage = StorageUsage::where('user_id', $user->id)->first();
        
        if (!$usage) {
            $usage = StorageUsage::create([
                'user_id' => $user->id,
                'used_bytes' => 0,
                'files_count' => 0,
            ]);
        }
        
        return Inertia::render('GrowBackup/Subscription', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
            'usage' => [
                'used_bytes' => $usage->used_bytes ?? 0,
                'files_count' => $usage->files_count ?? 0,
                'percent_used' => ($usage->used_bytes ?? 0) > 0 
                    ? round((($usage->used_bytes ?? 0) / $subscription->storagePlan->quota_bytes) * 100, 2)
                    : 0,
            ],
        ]);
    }
    
    public function upgrade(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|uuid|exists:storage_plans,id',
            'billing_cycle' => 'required|in:monthly,annual',
        ]);
        
        $user = auth()->user();
        $newPlan = StoragePlan::findOrFail($validated['plan_id']);
        
        // Get current subscription
        $currentSubscription = UserStorageSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        
        // Check if it's actually an upgrade
        if ($currentSubscription && $newPlan->quota_bytes <= $currentSubscription->storagePlan->quota_bytes) {
            return back()->with('error', 'You can only upgrade to a higher tier plan');
        }
        
        // TODO: Process payment here
        // For now, just update the subscription
        
        if ($currentSubscription) {
            $currentSubscription->update([
                'storage_plan_id' => $newPlan->id,
                'billing_cycle' => $validated['billing_cycle'],
            ]);
        } else {
            UserStorageSubscription::create([
                'user_id' => $user->id,
                'storage_plan_id' => $newPlan->id,
                'billing_cycle' => $validated['billing_cycle'],
                'status' => 'active',
                'started_at' => now(),
            ]);
        }
        
        return redirect()->route('growbackup.dashboard')
            ->with('success', "Successfully upgraded to {$newPlan->name} plan!");
    }
}
