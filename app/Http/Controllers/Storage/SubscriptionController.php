<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Domain\Storage\Repositories\StorageSubscriptionRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function __construct(
        private StorageSubscriptionRepositoryInterface $subscriptionRepo
    ) {}

    public function index()
    {
        $user = auth()->user();

        $plans = collect($this->subscriptionRepo->getActivePlans())->map(function ($plan) {
            return [
                'id' => $plan['id'],
                'name' => $plan['name'],
                'slug' => $plan['slug'],
                'quota_bytes' => $plan['quota_bytes'],
                'max_file_size_bytes' => $plan['max_file_size_bytes'],
                'price_monthly' => $plan['price_monthly'] ?? 0,
                'price_annual' => $plan['price_annual'] ?? 0,
                'allow_sharing' => $plan['allow_sharing'],
                'allow_public_profile_files' => $plan['allow_public_profile_files'],
                'features' => isset($plan['features']) ? (is_string($plan['features']) ? json_decode($plan['features'], true) : $plan['features']) : [],
                'is_popular' => $plan['is_popular'] ?? false,
            ];
        })->all();

        $subscription = $this->subscriptionRepo->getActiveSubscription($user->id);

        if (!$subscription || !$subscription['storage_plan']['is_active']) {
            $freePlan = $this->subscriptionRepo->findFreePlan();

            if (!$freePlan) {
                $activePlans = collect($this->subscriptionRepo->getActivePlans());
                $freePlan = $activePlans->sortBy('quota_bytes')->first();
            }

            if ($freePlan) {
                $this->subscriptionRepo->createOrUpdateSubscription($user->id, $freePlan['id']);
                $subscription = $this->subscriptionRepo->getActiveSubscription($user->id);
            }
        }

        $currentPlan = $subscription ? [
            'id' => $subscription['storage_plan']['id'],
            'name' => $subscription['storage_plan']['name'],
            'slug' => $subscription['storage_plan']['slug'],
            'quota_bytes' => $subscription['storage_plan']['quota_bytes'],
            'max_file_size_bytes' => $subscription['storage_plan']['max_file_size_bytes'],
        ] : [];

        $usage = $this->subscriptionRepo->getOrCreateUsage($user->id);

        $quotaBytes = $subscription['storage_plan']['quota_bytes'] ?? 0;
        $usedBytes = $usage['used_bytes'] ?? 0;

        return Inertia::render('GrowBackup/Subscription', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
            'usage' => [
                'used_bytes' => $usedBytes,
                'files_count' => $usage['files_count'] ?? 0,
                'percent_used' => $quotaBytes > 0 ? round(($usedBytes / $quotaBytes) * 100, 2) : 0,
            ],
        ]);
    }

    public function upgrade(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|string',
            'billing_cycle' => 'required|in:monthly,annual',
        ]);

        $user = auth()->user();
        $newPlan = $this->subscriptionRepo->findPlanById($validated['plan_id']);

        if (!$newPlan) {
            return back()->with('error', 'Plan not found');
        }

        $currentSubscription = $this->subscriptionRepo->getActiveSubscription($user->id);

        if ($currentSubscription && $newPlan['quota_bytes'] <= $currentSubscription['storage_plan']['quota_bytes']) {
            return back()->with('error', 'You can only upgrade to a higher tier plan');
        }

        $this->subscriptionRepo->createOrUpdateSubscription(
            $user->id,
            $validated['plan_id'],
            $validated['billing_cycle']
        );

        return redirect()->route('growbackup.dashboard')
            ->with('success', "Successfully upgraded to {$newPlan['name']} plan!");
    }
}