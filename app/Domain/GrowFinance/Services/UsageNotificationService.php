<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\Module\Services\SubscriptionService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class UsageNotificationService
{
    private const NOTIFICATION_THRESHOLDS = [80, 90, 100];
    private const CACHE_PREFIX = 'growfinance_usage_notified_';

    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Check usage and send notifications if thresholds are crossed
     */
    public function checkAndNotify(User $user): array
    {
        $notifications = [];
        $usage = $this->subscriptionService->getUsageSummary($user);

        // Check transactions
        if ($usage['transactions']['limit'] !== -1) {
            $notification = $this->checkThreshold(
                $user,
                'transactions',
                $usage['transactions']['used'],
                $usage['transactions']['limit']
            );
            if ($notification) {
                $notifications[] = $notification;
            }
        }

        // Check invoices
        if ($usage['invoices']['limit'] !== -1) {
            $notification = $this->checkThreshold(
                $user,
                'invoices',
                $usage['invoices']['used'],
                $usage['invoices']['limit']
            );
            if ($notification) {
                $notifications[] = $notification;
            }
        }

        // Check customers
        if ($usage['customers']['limit'] !== -1) {
            $notification = $this->checkThreshold(
                $user,
                'customers',
                $usage['customers']['used'],
                $usage['customers']['limit']
            );
            if ($notification) {
                $notifications[] = $notification;
            }
        }

        // Check vendors
        if ($usage['vendors']['limit'] !== -1) {
            $notification = $this->checkThreshold(
                $user,
                'vendors',
                $usage['vendors']['used'],
                $usage['vendors']['limit']
            );
            if ($notification) {
                $notifications[] = $notification;
            }
        }

        // Check storage
        if ($usage['storage']['limit_mb'] > 0) {
            $notification = $this->checkThreshold(
                $user,
                'storage',
                $usage['storage']['used_mb'],
                $usage['storage']['limit_mb']
            );
            if ($notification) {
                $notifications[] = $notification;
            }
        }

        return $notifications;
    }

    /**
     * Check if a threshold has been crossed and send notification
     */
    private function checkThreshold(
        User $user,
        string $resource,
        int|float $used,
        int|float $limit
    ): ?array {
        if ($limit <= 0) {
            return null;
        }

        $percentage = ($used / $limit) * 100;
        $crossedThreshold = null;

        foreach (self::NOTIFICATION_THRESHOLDS as $threshold) {
            if ($percentage >= $threshold) {
                $crossedThreshold = $threshold;
            }
        }

        if (!$crossedThreshold) {
            return null;
        }

        // Check if we've already notified for this threshold this month
        $cacheKey = self::CACHE_PREFIX . "{$user->id}_{$resource}_{$crossedThreshold}_" . now()->format('Y-m');
        if (Cache::has($cacheKey)) {
            return null;
        }

        // Send notification
        $this->sendNotification($user, $resource, $used, $limit, $crossedThreshold);

        // Mark as notified
        Cache::put($cacheKey, true, now()->endOfMonth());

        return [
            'resource' => $resource,
            'threshold' => $crossedThreshold,
            'used' => $used,
            'limit' => $limit,
        ];
    }

    /**
     * Send usage notification email
     */
    private function sendNotification(
        User $user,
        string $resource,
        int|float $used,
        int|float $limit,
        int $threshold
    ): void {
        // Create in-app notification
        $this->createInAppNotification($user, $resource, $used, $limit, $threshold);

        // Send email notification
        $this->sendEmailNotification($user, $resource, $used, $limit, $threshold);
    }

    /**
     * Create in-app notification
     */
    private function createInAppNotification(
        User $user,
        string $resource,
        int|float $used,
        int|float $limit,
        int $threshold
    ): void {
        $resourceNames = [
            'transactions' => 'monthly transactions',
            'invoices' => 'monthly invoices',
            'customers' => 'customers',
            'vendors' => 'vendors',
            'storage' => 'storage space',
        ];

        $resourceName = $resourceNames[$resource] ?? $resource;

        $title = $threshold >= 100
            ? "You've reached your {$resourceName} limit"
            : "You're at {$threshold}% of your {$resourceName} limit";

        $message = $threshold >= 100
            ? "You've used all {$limit} {$resourceName}. Upgrade your plan to continue."
            : "You've used {$used} of {$limit} {$resourceName}. Consider upgrading soon.";

        DB::table('growfinance_notifications')->insert([
            'user_id' => $user->id,
            'type' => $threshold >= 100 ? 'limit_reached' : 'limit_warning',
            'title' => $title,
            'message' => $message,
            'data' => json_encode([
                'resource' => $resource,
                'used' => $used,
                'limit' => $limit,
                'threshold' => $threshold,
            ]),
            'action_url' => route('growfinance.upgrade'),
            'action_text' => 'Upgrade Plan',
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(
        User $user,
        string $resource,
        int|float $used,
        int|float $limit,
        int $threshold
    ): void {
        $resourceNames = [
            'transactions' => 'monthly transactions',
            'invoices' => 'monthly invoices',
            'customers' => 'customers',
            'vendors' => 'vendors',
            'storage' => 'storage space',
        ];

        $resourceName = $resourceNames[$resource] ?? $resource;

        $subject = $threshold >= 100
            ? "[GrowFinance] You've reached your {$resourceName} limit"
            : "[GrowFinance] Usage Alert: {$threshold}% of {$resourceName} used";

        $data = [
            'user' => $user,
            'resource' => $resource,
            'resourceName' => $resourceName,
            'used' => $used,
            'limit' => $limit,
            'threshold' => $threshold,
            'upgradeUrl' => route('growfinance.upgrade'),
        ];

        // Queue the email
        // Mail::to($user->email)->queue(new UsageLimitNotification($data));

        // For now, log it (implement actual email later)
        \Log::info("GrowFinance Usage Notification", [
            'user_id' => $user->id,
            'email' => $user->email,
            'subject' => $subject,
            'data' => $data,
        ]);
    }

    /**
     * Get pending notifications for a user
     */
    public function getPendingNotifications(User $user): array
    {
        return DB::table('growfinance_notifications')
            ->where('user_id', $user->id)
            ->whereIn('type', ['limit_warning', 'limit_reached'])
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Clear notification cache for a user (call when they upgrade)
     */
    public function clearNotificationCache(User $user): void
    {
        $resources = ['transactions', 'invoices', 'customers', 'vendors', 'storage'];
        $month = now()->format('Y-m');

        foreach ($resources as $resource) {
            foreach (self::NOTIFICATION_THRESHOLDS as $threshold) {
                Cache::forget(self::CACHE_PREFIX . "{$user->id}_{$resource}_{$threshold}_{$month}");
            }
        }
    }
}
