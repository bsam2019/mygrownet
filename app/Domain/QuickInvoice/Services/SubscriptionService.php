<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Repositories\AdminSettingRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionTierRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\UsageTrackingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly SubscriptionTierRepositoryInterface $tierRepository,
        private readonly AdminSettingRepositoryInterface $adminSettingRepository,
        private readonly UsageTrackingRepositoryInterface $usageTrackingRepository,
    ) {}

    public function getPlans(): array
    {
        $tiers = $this->tierRepository->findAllActive();

        return array_map(function ($tier) {
            return [
                'id' => $tier['id'],
                'name' => $tier['name'],
                'price' => (float) $tier['price'],
                'currency' => $tier['currency'],
                'formatted_price' => $this->formatPrice($tier),
                'documents_per_month' => $tier['documents_per_month'],
                'features' => $tier['features'],
                'is_free' => (float) $tier['price'] == 0,
            ];
        }, $tiers);
    }

    public function getUserSubscriptionStatus(int $userId): array
    {
        $subscription = $this->subscriptionRepository->getOrCreateFreeSubscription($userId);
        $monthlyUsage = $this->usageTrackingRepository->getUserMonthlyUsage($userId);

        return [
            'id' => $subscription['id'],
            'tier' => [
                'id' => $subscription['tier']['id'],
                'name' => $subscription['tier']['name'],
                'price' => (float) $subscription['tier']['price'],
                'formatted_price' => $this->formatPrice($subscription['tier']),
                'documents_per_month' => $subscription['tier']['documents_per_month'],
            ],
            'status' => $this->getStatusLabel($subscription),
            'is_trial' => $this->onTrial($subscription),
            'trial_ends_at' => isset($subscription['trial_ends_at']) ? date('Y-m-d', strtotime($subscription['trial_ends_at'])) : null,
            'expires_at' => isset($subscription['expires_at']) ? date('Y-m-d', strtotime($subscription['expires_at'])) : null,
            'monthly_usage' => $monthlyUsage,
            'remaining_documents' => $this->getRemainingDocuments($subscription, $monthlyUsage),
            'usage_percentage' => $this->getUsagePercentage($subscription, $monthlyUsage),
            'can_create_document' => $this->canCreateDocument($subscription, $monthlyUsage),
            'is_paid' => $this->isPaid($subscription),
            'last_payment_at' => isset($subscription['last_payment_at']) ? date('Y-m-d', strtotime($subscription['last_payment_at'])) : null,
            'payment_method' => $subscription['payment_method'] ?? null,
        ];
    }

    public function canCreateDocument(int $userId): bool
    {
        $subscription = $this->subscriptionRepository->getOrCreateFreeSubscription($userId);
        $monthlyUsage = $this->usageTrackingRepository->getUserMonthlyUsage($userId);
        return $this->canCreateDocumentFromData($subscription, $monthlyUsage);
    }

    public function hasReachedLimit(int $userId): ?array
    {
        if (!$this->adminSettingRepository->isUsageLimitsEnabled()) {
            return null;
        }

        $subscription = $this->subscriptionRepository->getOrCreateFreeSubscription($userId);
        $monthlyUsage = $this->usageTrackingRepository->getUserMonthlyUsage($userId);

        if (!$this->canCreateDocumentFromData($subscription, $monthlyUsage)) {
            return [
                'tier_name' => $subscription['tier']['name'],
                'documents_per_month' => $subscription['tier']['documents_per_month'],
                'remaining_documents' => $this->getRemainingDocuments($subscription, $monthlyUsage),
                'usage_percentage' => $this->getUsagePercentage($subscription, $monthlyUsage),
            ];
        }

        return null;
    }

    public function trackUsage(int $userId): void
    {
        $subscription = $this->subscriptionRepository->getCurrentSubscription($userId);
        if ($subscription) {
            $this->subscriptionRepository->incrementUsage($subscription['id']);
        }
    }

    public function initiateUpgrade(int $userId, int $tierId): array
    {
        $tier = $this->tierRepository->findById($tierId);

        if (!$tier) {
            throw new \InvalidArgumentException('Tier not found');
        }

        if ((float) $tier['price'] <= 0) {
            throw new \InvalidArgumentException('Cannot initiate payment for free tier');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        return [
            'tier_id' => $tier['id'],
            'tier_name' => $tier['name'],
            'amount' => (float) $tier['price'],
            'currency' => $tier['currency'],
            'wallet_balance' => (float) ($user->balance ?? 0),
            'can_pay_with_wallet' => ($user->balance ?? 0) >= (float) $tier['price'],
        ];
    }

    public function completePayment(int $userId, int $tierId, string $paymentMethod = 'wallet'): array
    {
        $tier = $this->tierRepository->findById($tierId);

        if (!$tier) {
            throw new \InvalidArgumentException('Tier not found');
        }

        $user = \App\Models\User::findOrFail($userId);

        return DB::transaction(function () use ($user, $tier, $tierId, $paymentMethod) {
            if ($paymentMethod === 'wallet') {
                if (($user->balance ?? 0) < (float) $tier['price']) {
                    throw new \RuntimeException('Insufficient wallet balance');
                }
                $user->decrement('balance', (float) $tier['price']);
            }

            $this->subscriptionRepository->deactivateByUser($user->id);

            $subscription = $this->subscriptionRepository->create([
                'user_id' => $user->id,
                'tier_id' => $tierId,
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
                'documents_used' => 0,
                'is_active' => true,
                'billing_cycle' => 'monthly',
                'last_payment_at' => now(),
                'last_payment_amount' => (float) $tier['price'],
                'payment_method' => $paymentMethod,
                'payment_reference' => 'WALLET-' . strtoupper(uniqid()),
            ]);

            \App\Models\PaymentTransaction::create([
                'user_id' => $user->id,
                'type' => 'subscription_payment',
                'amount' => (float) $tier['price'],
                'status' => 'completed',
                'payment_method' => $paymentMethod,
                'reference' => 'QINV-SUB-' . $subscription['id'] . '-' . now()->format('YmdHis'),
                'payment_details' => [
                    'tier_name' => $tier['name'],
                    'tier_id' => $tier['id'],
                    'subscription_id' => $subscription['id'],
                    'billing_cycle' => 'monthly',
                ],
                'completed_at' => now(),
            ]);

            return $subscription;
        });
    }

    public function getAdminStats(): array
    {
        $subscriptions = $this->subscriptionRepository->findAllActive();

        $total = count($subscriptions);
        $onTrial = 0;
        $paid = 0;
        $free = 0;
        $expired = 0;
        $trialExpired = 0;
        $byTier = [];

        foreach ($subscriptions as $s) {
            $tierName = $s['tier']['name'] ?? 'Unknown';
            $byTier[$tierName] = ($byTier[$tierName] ?? 0) + 1;

            if ($this->onTrial($s)) {
                $onTrial++;
            } elseif ($this->isPaid($s)) {
                $paid++;
            } elseif (isset($s['tier']['price']) && (float) $s['tier']['price'] == 0 && !$this->onTrial($s)) {
                $free++;
            }

            if (isset($s['expires_at']) && strtotime($s['expires_at']) < time()) {
                $expired++;
            }

            if ($this->trialExpired($s)) {
                $trialExpired++;
            }
        }

        $totalRevenue = $this->subscriptionRepository->sumLastPaymentAmount();

        return [
            'total_subscriptions' => $total,
            'on_trial' => $onTrial,
            'paid' => $paid,
            'free' => $free,
            'expired' => $expired,
            'trial_expired' => $trialExpired,
            'by_tier' => $byTier,
            'total_revenue' => $totalRevenue,
        ];
    }

    public function getSubscriptionForDashboard(int $userId): ?array
    {
        $subscription = $this->subscriptionRepository->getOrCreateFreeSubscription($userId);
        $monthlyUsage = $this->usageTrackingRepository->getUserMonthlyUsage($userId);

        return [
            'tier_name' => $subscription['tier']['name'],
            'tier_price' => $this->formatPrice($subscription['tier']),
            'documents_per_month' => $subscription['tier']['documents_per_month'] == -1 ? 'Unlimited' : $subscription['tier']['documents_per_month'],
            'documents_used' => $monthlyUsage,
            'remaining_documents' => $subscription['tier']['documents_per_month'] == -1 ? 'Unlimited' : $this->getRemainingDocuments($subscription, $monthlyUsage),
            'usage_percentage' => $this->getUsagePercentage($subscription, $monthlyUsage),
            'features' => $subscription['tier']['features'],
            'expires_at' => isset($subscription['expires_at']) ? date('M j, Y', strtotime($subscription['expires_at'])) : null,
        ];
    }

    private function canCreateDocumentFromData(array $subscription, int $monthlyUsage): bool
    {
        if (!$subscription['is_active']) {
            return false;
        }

        if ($this->onTrial($subscription)) {
            return $this->checkDocumentLimit($subscription, $monthlyUsage);
        }

        if (isset($subscription['expires_at']) && strtotime($subscription['expires_at']) < time()) {
            return false;
        }

        return $this->checkDocumentLimit($subscription, $monthlyUsage);
    }

    private function checkDocumentLimit(array $subscription, int $monthlyUsage): bool
    {
        if ($subscription['tier']['documents_per_month'] == -1) {
            return true;
        }

        return $monthlyUsage < $subscription['tier']['documents_per_month'];
    }

    private function getRemainingDocuments(array $subscription, int $monthlyUsage): int
    {
        if ($subscription['tier']['documents_per_month'] == -1) {
            return 999999;
        }

        return max(0, $subscription['tier']['documents_per_month'] - $monthlyUsage);
    }

    private function getUsagePercentage(array $subscription, int $monthlyUsage): float
    {
        if ($subscription['tier']['documents_per_month'] == -1 || $subscription['tier']['documents_per_month'] == 0) {
            return 0;
        }

        return min(100, ($monthlyUsage / $subscription['tier']['documents_per_month']) * 100);
    }

    private function onTrial(array $subscription): bool
    {
        return isset($subscription['trial_ends_at']) && strtotime($subscription['trial_ends_at']) > time();
    }

    private function trialExpired(array $subscription): bool
    {
        return isset($subscription['trial_ends_at']) && strtotime($subscription['trial_ends_at']) < time();
    }

    private function isPaid(array $subscription): bool
    {
        return !empty($subscription['last_payment_at']) && isset($subscription['tier']['price']) && (float) $subscription['tier']['price'] > 0;
    }

    private function getStatusLabel(array $subscription): string
    {
        if ($this->onTrial($subscription)) return 'trial';
        if ($this->isPaid($subscription)) return 'active';
        if (isset($subscription['expires_at']) && strtotime($subscription['expires_at']) < time()) return 'expired';
        return 'free';
    }

    public function getUserManagementList(?string $search, ?string $tierFilter): array
    {
        $paginated = $this->subscriptionRepository->paginateWithFilters($search, $tierFilter);

        $items = array_map(function ($subscription) {
            $userId = $subscription['user_id'];
            $monthlyUsage = $this->usageTrackingRepository->getUserMonthlyUsage($userId);

            $status = 'free';
            if ($this->onTrial($subscription)) $status = 'trial';
            elseif ($this->isPaid($subscription)) $status = 'active';
            elseif (isset($subscription['expires_at']) && strtotime($subscription['expires_at']) < time()) $status = 'expired';

            return [
                'id' => $subscription['id'],
                'user' => [
                    'id' => $subscription['user']['id'] ?? $userId,
                    'name' => $subscription['user']['name'] ?? 'Unknown',
                    'email' => $subscription['user']['email'] ?? '',
                ],
                'tier' => [
                    'name' => $subscription['tier']['name'] ?? 'Free',
                    'documents_per_month' => $subscription['tier']['documents_per_month'] ?? -1,
                    'formatted_price' => isset($subscription['tier']['price']) ? $this->formatPrice($subscription['tier']) : 'Free',
                ],
                'status' => $status,
                'is_trial' => $this->onTrial($subscription),
                'trial_ends_at' => isset($subscription['trial_ends_at']) ? date('M j, Y', strtotime($subscription['trial_ends_at'])) : null,
                'is_paid' => $this->isPaid($subscription),
                'last_payment_at' => isset($subscription['last_payment_at']) ? date('M j, Y', strtotime($subscription['last_payment_at'])) : null,
                'last_payment_amount' => $subscription['last_payment_amount'] ?? null,
                'payment_method' => $subscription['payment_method'] ?? null,
                'monthly_usage' => $monthlyUsage,
                'remaining_documents' => $this->getRemainingDocuments($subscription, $monthlyUsage),
                'usage_percentage' => $this->getUsagePercentage($subscription, $monthlyUsage),
                'starts_at' => isset($subscription['starts_at']) ? date('M j, Y', strtotime($subscription['starts_at'])) : null,
                'expires_at' => isset($subscription['expires_at']) ? date('M j, Y', strtotime($subscription['expires_at'])) : null,
            ];
        }, $paginated['data'] ?? $paginated);

        return [
            'data' => $items,
            'current_page' => $paginated['current_page'] ?? 1,
            'last_page' => $paginated['last_page'] ?? 1,
            'per_page' => $paginated['per_page'] ?? 20,
            'total' => $paginated['total'] ?? count($items),
        ];
    }

    private function formatPrice(array $tier): string
    {
        if ((float) $tier['price'] == 0) return 'Free';

        $symbol = match ($tier['currency'] ?? 'ZMW') {
            'ZMW' => 'K',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $tier['currency'] ?? 'K',
        };

        return $symbol . number_format((float) $tier['price'], 0);
    }
}