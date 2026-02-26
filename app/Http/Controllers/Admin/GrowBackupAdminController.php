<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GrowBackupAdminController extends Controller
{
    /**
     * Show GrowBackup admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_users' => UserStorageSubscription::distinct('user_id')->count(),
            'total_storage_used' => StorageUsage::sum('used_bytes'),
            'total_files' => StorageUsage::sum('files_count'),
            'active_subscriptions' => UserStorageSubscription::where('status', 'active')->count(),
        ];

        $planStats = StoragePlan::where('is_active', true)
            ->withCount(['subscriptions' => function ($query) {
                $query->where('status', 'active');
            }])
            ->get()
            ->map(function ($plan) {
                return [
                    'name' => $plan->name,
                    'subscribers' => $plan->subscriptions_count,
                ];
            });

        return Inertia::render('Admin/GrowBackup/Dashboard', [
            'stats' => $stats,
            'planStats' => $planStats,
        ]);
    }

    /**
     * List all storage plans
     */
    public function plans()
    {
        $plans = StoragePlan::where('is_active', true)
            ->orderBy('quota_bytes')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'quota_gb' => $plan->quota_bytes / (1024 * 1024 * 1024),
                    'max_file_size_mb' => $plan->max_file_size_bytes / (1024 * 1024),
                    'price_monthly' => $plan->price_monthly,
                    'price_annual' => $plan->price_annual,
                    'features' => $plan->features ? json_decode($plan->features, true) : [],
                    'is_popular' => $plan->is_popular,
                    'is_active' => $plan->is_active,
                    'allow_sharing' => $plan->allow_sharing,
                    'allow_public_profile_files' => $plan->allow_public_profile_files,
                    'subscribers_count' => $plan->subscriptions()->where('status', 'active')->count(),
                ];
            });

        return Inertia::render('Admin/GrowBackup/Plans', [
            'plans' => $plans,
        ]);
    }

    /**
     * Update a storage plan
     */
    public function updatePlan(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quota_gb' => 'required|numeric|min:0',
            'max_file_size_mb' => 'required|numeric|min:0',
            'price_monthly' => 'required|numeric|min:0',
            'price_annual' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'allow_sharing' => 'boolean',
            'allow_public_profile_files' => 'boolean',
        ]);

        $plan = StoragePlan::findOrFail($id);

        $plan->update([
            'name' => $validated['name'],
            'quota_bytes' => $validated['quota_gb'] * 1024 * 1024 * 1024,
            'max_file_size_bytes' => $validated['max_file_size_mb'] * 1024 * 1024,
            'price_monthly' => $validated['price_monthly'],
            'price_annual' => $validated['price_annual'],
            'features' => json_encode($validated['features'] ?? []),
            'is_popular' => $validated['is_popular'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'allow_sharing' => $validated['allow_sharing'] ?? false,
            'allow_public_profile_files' => $validated['allow_public_profile_files'] ?? false,
        ]);

        return back()->with('success', 'Plan updated successfully');
    }

    /**
     * List users with storage subscriptions
     */
    public function users(Request $request)
    {
        $search = $request->input('search');

        $users = UserStorageSubscription::with(['user', 'storagePlan'])
            ->where('status', 'active')
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate(20)
            ->through(function ($subscription) {
                $usage = StorageUsage::where('user_id', $subscription->user_id)->first();
                
                return [
                    'id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'user_name' => $subscription->user->name,
                    'user_email' => $subscription->user->email,
                    'plan_name' => $subscription->storagePlan->name,
                    'quota_gb' => $subscription->storagePlan->quota_bytes / (1024 * 1024 * 1024),
                    'used_gb' => $usage ? $usage->used_bytes / (1024 * 1024 * 1024) : 0,
                    'files_count' => $usage ? $usage->files_count : 0,
                    'percent_used' => $usage && $subscription->storagePlan->quota_bytes > 0
                        ? round(($usage->used_bytes / $subscription->storagePlan->quota_bytes) * 100, 2)
                        : 0,
                    'bonus_quota_bytes' => $subscription->bonus_quota_bytes ?? 0,
                    'started_at' => $subscription->started_at?->toIso8601String(),
                ];
            });

        return Inertia::render('Admin/GrowBackup/Users', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Award bonus storage to a user
     */
    public function awardBonusStorage(Request $request, string $subscriptionId)
    {
        $validated = $request->validate([
            'bonus_gb' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $subscription = UserStorageSubscription::findOrFail($subscriptionId);
        
        $bonusBytes = $validated['bonus_gb'] * 1024 * 1024 * 1024;
        
        $subscription->update([
            'bonus_quota_bytes' => ($subscription->bonus_quota_bytes ?? 0) + $bonusBytes,
        ]);

        // Log the bonus award
        DB::table('activity_logs')->insert([
            'user_id' => auth()->id(),
            'action' => 'awarded_bonus_storage',
            'description' => "Awarded {$validated['bonus_gb']} GB bonus storage to user {$subscription->user->name}. Reason: " . ($validated['reason'] ?? 'N/A'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Awarded {$validated['bonus_gb']} GB bonus storage successfully");
    }

    /**
     * Remove bonus storage from a user
     */
    public function removeBonusStorage(Request $request, string $subscriptionId)
    {
        $subscription = UserStorageSubscription::findOrFail($subscriptionId);
        
        $subscription->update([
            'bonus_quota_bytes' => 0,
        ]);

        return back()->with('success', 'Bonus storage removed successfully');
    }
}
