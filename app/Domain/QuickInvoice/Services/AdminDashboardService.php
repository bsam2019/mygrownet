<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Repositories\AdminSettingRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionTierRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\UsageTrackingRepositoryInterface;

class AdminDashboardService
{
    public function __construct(
        private readonly UsageTrackingRepositoryInterface $usageTrackingRepository,
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly SubscriptionTierRepositoryInterface $tierRepository,
        private readonly AdminSettingRepositoryInterface $adminSettingRepository,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function getDashboardData(): array
    {
        $today = now()->format('Y-m-d');
        $weekAgo = now()->subWeek()->format('Y-m-d');
        $monthAgo = now()->subMonth()->format('Y-m-d');

        $todayStats = $this->usageTrackingRepository->getStats($today, $today);
        $weekStats = $this->usageTrackingRepository->getStats($weekAgo, $today);
        $monthStats = $this->usageTrackingRepository->getStats($monthAgo, $today);

        $subscriptionStats = [
            'total_users' => $this->subscriptionRepository->countDistinctUsers(),
            'active_subscriptions' => $this->subscriptionRepository->countActive(),
            'by_tier' => $this->getSubscriptionByTier(),
        ];

        $recentActivity = $this->usageTrackingRepository->getRecentActivity(10);
        $monetizationSettings = $this->adminSettingRepository->getMonetizationSettings();
        $trialSettings = $this->adminSettingRepository->get('trial_settings', [
            'trial_days' => 30,
            'tier_on_trial' => 'Basic',
            'require_payment_after_trial' => true,
        ]);

        $billingStats = $this->subscriptionService->getAdminStats();

        return [
            'stats' => [
                'today' => $todayStats,
                'week' => $weekStats,
                'month' => $monthStats,
            ],
            'subscriptionStats' => $subscriptionStats,
            'recentActivity' => $recentActivity,
            'monetizationSettings' => $monetizationSettings,
            'trialSettings' => $trialSettings,
            'billingStats' => $billingStats,
        ];
    }

    public function getUsageAnalytics(string $period = '30d'): array
    {
        $days = match ($period) {
            '7d' => 7,
            '90d' => 90,
            default => 30,
        };

        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $dailyUsage = $this->usageTrackingRepository->getDailyUsage($startDate, $endDate);
        $overallStats = $this->usageTrackingRepository->getOverallStats($startDate, $endDate);
        $topUsers = $this->usageTrackingRepository->getTopUsers($startDate, $endDate, 10);

        return [
            'period' => $period,
            'dailyUsage' => $dailyUsage,
            'overallStats' => $overallStats,
            'topUsers' => $topUsers,
        ];
    }

    public function getTiers(): array
    {
        return array_map(function ($tier) {
            return [
                'id' => $tier['id'],
                'name' => $tier['name'],
                'price' => (float) $tier['price'],
                'currency' => $tier['currency'],
                'formatted_price' => $this->formatPrice($tier),
                'documents_per_month' => $tier['documents_per_month'],
                'features' => $tier['features'],
                'is_active' => $tier['is_active'],
                'created_at' => isset($tier['created_at']) ? date('M j, Y', strtotime($tier['created_at'])) : null,
            ];
        }, $this->tierRepository->findAll());
    }

    private function getSubscriptionByTier(): array
    {
        $active = $this->subscriptionRepository->findAllActive();
        $byTier = [];

        foreach ($active as $s) {
            $name = $s['tier']['name'] ?? 'Unknown';
            $byTier[$name] = ($byTier[$name] ?? 0) + 1;
        }

        return $byTier;
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