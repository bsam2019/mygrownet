<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Domain\Storage\Repositories\StorageSubscriptionRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StorageSubscriptionController extends Controller
{
    public function __construct(
        private StorageSubscriptionRepositoryInterface $subscriptionRepo
    ) {}

    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $plans = collect($this->subscriptionRepo->getActivePlans())->map(function ($plan) {
            $product = null;
            if (isset($plan['product']) && $plan['product']) {
                $product = [
                    'id' => $plan['product']['id'],
                    'name' => $plan['product']['name'],
                    'price' => $plan['product']['price'],
                    'bp_value' => $plan['product']['bp_value'] ?? null,
                ];
            }

            return [
                'id' => $plan['id'],
                'name' => $plan['name'],
                'slug' => $plan['slug'],
                'quota_bytes' => $plan['quota_bytes'],
                'max_file_size_bytes' => $plan['max_file_size_bytes'],
                'allow_sharing' => $plan['allow_sharing'],
                'product_id' => $plan['product_id'] ?? null,
                'product' => $product,
            ];
        })->all();

        $subscription = $this->subscriptionRepo->getActiveSubscription($userId);
        $usage = $this->subscriptionRepo->getOrCreateUsage($userId);
        $quotaBytes = $subscription ? $subscription['storage_plan']['quota_bytes'] : 0;

        return Inertia::render('GrowBackup/Subscription', [
            'plans' => $plans,
            'currentSubscription' => $subscription ? [
                'id' => $subscription['id'],
                'storage_plan_id' => $subscription['storage_plan_id'],
                'status' => $subscription['status'],
                'start_at' => $subscription['start_at'] ?? null,
                'end_at' => $subscription['end_at'] ?? null,
            ] : null,
            'usage' => [
                'used_bytes' => $usage['used_bytes'] ?? 0,
                'quota_bytes' => $quotaBytes,
                'percent_used' => $quotaBytes > 0 ? (($usage['used_bytes'] ?? 0) / $quotaBytes) * 100 : 0,
            ],
        ]);
    }
}