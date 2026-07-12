<?php

namespace App\Services\QuickInvoice;

use App\Models\QuickInvoice\AdminSetting;
use App\Models\QuickInvoice\SubscriptionTier;
use App\Models\QuickInvoice\UserSubscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuickInvoiceSubscriptionService
{
    /**
     * Get all available subscription plans
     */
    public function getPlans(): array
    {
        $tiers = SubscriptionTier::where('is_active', true)
            ->orderBy('price')
            ->get();

        return $tiers->map(function ($tier) {
            return [
                'id' => $tier->id,
                'name' => $tier->name,
                'price' => (float) $tier->price,
                'currency' => $tier->currency,
                'formatted_price' => $tier->formatted_price,
                'documents_per_month' => $tier->documents_per_month,
                'features' => $tier->features,
                'is_free' => $tier->price == 0,
            ];
        })->toArray();
    }

    /**
     * Get user's subscription status with usage info
     */
    public function getUserSubscriptionStatus(int $userId): array
    {
        $subscription = UserSubscription::getOrCreateFreeSubscription($userId);
        $subscription->load('tier');

        $monthlyUsage = \App\Models\QuickInvoice\UsageTracking::getUserMonthlyUsage($userId);

        return [
            'id' => $subscription->id,
            'tier' => [
                'id' => $subscription->tier->id,
                'name' => $subscription->tier->name,
                'price' => (float) $subscription->tier->price,
                'formatted_price' => $subscription->tier->formatted_price,
                'documents_per_month' => $subscription->tier->documents_per_month,
            ],
            'status' => $this->getStatusLabel($subscription),
            'is_trial' => $subscription->onTrial(),
            'trial_ends_at' => $subscription->trial_ends_at?->format('Y-m-d'),
            'expires_at' => $subscription->expires_at?->format('Y-m-d'),
            'monthly_usage' => $monthlyUsage,
            'remaining_documents' => $subscription->getRemainingDocuments(),
            'usage_percentage' => $subscription->getUsagePercentage(),
            'can_create_document' => $subscription->canCreateDocument(),
            'is_paid' => $subscription->isPaid(),
            'last_payment_at' => $subscription->last_payment_at?->format('Y-m-d'),
            'payment_method' => $subscription->payment_method,
        ];
    }

    /**
     * Initiate upgrade to a paid tier
     * Returns payment details needed to complete the upgrade
     */
    public function initiateUpgrade(int $userId, int $tierId): array
    {
        $tier = SubscriptionTier::findOrFail($tierId);
        $user = User::findOrFail($userId);

        if ($tier->price <= 0) {
            throw new \InvalidArgumentException('Cannot initiate payment for free tier');
        }

        return [
            'tier_id' => $tier->id,
            'tier_name' => $tier->name,
            'amount' => (float) $tier->price,
            'currency' => $tier->currency,
            'wallet_balance' => (float) ($user->balance ?? 0),
            'can_pay_with_wallet' => ($user->balance ?? 0) >= $tier->price,
        ];
    }

    /**
     * Complete payment and activate/upgrade subscription
     */
    public function completePayment(int $userId, int $tierId, string $paymentMethod = 'wallet'): UserSubscription
    {
        $tier = SubscriptionTier::findOrFail($tierId);
        $user = User::findOrFail($userId);

        return DB::transaction(function () use ($user, $tier, $tierId, $paymentMethod) {
            // Deduct from wallet if wallet payment
            if ($paymentMethod === 'wallet') {
                if (($user->balance ?? 0) < $tier->price) {
                    throw new \RuntimeException('Insufficient wallet balance');
                }
                $user->decrement('balance', $tier->price);
            }

            // Deactivate old subscription
            UserSubscription::where('user_id', $user->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Create new paid subscription
            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'tier_id' => $tier->id,
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
                'documents_used' => 0,
                'is_active' => true,
                'billing_cycle' => 'monthly',
                'last_payment_at' => now(),
                'last_payment_amount' => $tier->price,
                'payment_method' => $paymentMethod,
                'payment_reference' => 'WALLET-' . strtoupper(uniqid()),
            ]);

            // Record payment transaction
            \App\Models\PaymentTransaction::create([
                'user_id' => $user->id,
                'type' => 'subscription_payment',
                'amount' => $tier->price,
                'status' => 'completed',
                'payment_method' => $paymentMethod,
                'reference' => 'QINV-SUB-' . $subscription->id . '-' . now()->format('YmdHis'),
                'payment_details' => [
                    'tier_name' => $tier->name,
                    'tier_id' => $tier->id,
                    'subscription_id' => $subscription->id,
                    'billing_cycle' => 'monthly',
                ],
                'completed_at' => now(),
            ]);

            return $subscription;
        });
    }

    /**
     * Get admin statistics including trial info
     */
    public function getAdminStats(): array
    {
        $subscriptions = UserSubscription::where('is_active', true)->with('tier', 'user')->get();

        $total = $subscriptions->count();
        $onTrial = $subscriptions->filter(fn($s) => $s->onTrial())->count();
        $paid = $subscriptions->filter(fn($s) => $s->isPaid())->count();
        $free = $subscriptions->filter(fn($s) => $s->tier->price == 0 && !$s->onTrial())->count();
        $expired = $subscriptions->filter(fn($s) => $s->expires_at && $s->expires_at->isPast())->count();
        $trialExpired = $subscriptions->filter(fn($s) => $s->trialExpired())->count();

        $byTier = $subscriptions->groupBy(fn($s) => $s->tier->name)
            ->map(fn($group) => $group->count())
            ->toArray();

        $totalRevenue = UserSubscription::whereNotNull('last_payment_amount')
            ->sum('last_payment_amount');

        return [
            'total_subscriptions' => $total,
            'on_trial' => $onTrial,
            'paid' => $paid,
            'free' => $free,
            'expired' => $expired,
            'trial_expired' => $trialExpired,
            'by_tier' => $byTier,
            'total_revenue' => (float) $totalRevenue,
        ];
    }

    private function getStatusLabel(UserSubscription $subscription): string
    {
        if ($subscription->onTrial()) {
            return 'trial';
        }
        if ($subscription->isPaid()) {
            return 'active';
        }
        if ($subscription->expires_at && $subscription->expires_at->isPast()) {
            return 'expired';
        }
        return 'free';
    }
}
