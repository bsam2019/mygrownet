<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Domain\Notification\Core\Services\NotificationDataService;
use App\Events\BizBoost\NotificationReceived;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private const MODULE = 'bizboost';

    public function __construct(
        private NotificationDataService $notifications,
    ) {}

    public function notify(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $actionText = null,
        string $category = 'general',
        string $priority = 'normal',
        array $data = [],
        bool $broadcast = true
    ) {
        $notification = $this->notifications->create(
            userId: $user->id,
            type: $type,
            title: $title,
            message: $message,
            module: self::MODULE,
            actionUrl: $actionUrl,
            actionText: $actionText,
            category: $category,
            priority: $priority,
            data: $data,
        );

        if ($broadcast) {
            $this->broadcastNotification($user, $notification);
        }

        return $notification;
    }

    private function broadcastNotification(User $user, $notification): void
    {
        try {
            $business = \App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel::where('user_id', $user->id)->first();

            if ($business) {
                broadcast(new NotificationReceived($business->id, [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'category' => $notification->category,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'action_url' => $notification->action_url,
                    'action_text' => $notification->action_text,
                    'priority' => $notification->priority,
                ]))->toOthers();
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast BizBoost notification', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function notifySale(User $user, float $amount, string $customerName, int $saleId)
    {
        return $this->notify(
            $user, 'sale', 'New Sale Recorded',
            "K" . number_format($amount, 2) . " from {$customerName}",
            "/bizboost/sales", 'View Sales', 'sales', 'normal',
            ['sale_id' => $saleId, 'amount' => $amount, 'customer' => $customerName]
        );
    }

    public function notifyNewCustomer(User $user, string $customerName, int $customerId)
    {
        return $this->notify(
            $user, 'customer', 'New Customer Added',
            "{$customerName} has been added to your customer list",
            "/bizboost/customers/{$customerId}", 'View Customer', 'customers', 'normal',
            ['customer_id' => $customerId, 'customer_name' => $customerName]
        );
    }

    public function notifyPostPublished(User $user, string $postTitle, int $postId)
    {
        return $this->notify(
            $user, 'post', 'Post Published',
            "Your post \"{$postTitle}\" has been published successfully",
            "/bizboost/posts/{$postId}", 'View Post', 'posts', 'normal',
            ['post_id' => $postId, 'post_title' => $postTitle]
        );
    }

    public function notifyPostScheduled(User $user, string $postTitle, string $scheduledFor, int $postId)
    {
        return $this->notify(
            $user, 'post', 'Post Scheduled',
            "Your post \"{$postTitle}\" is scheduled for {$scheduledFor}",
            "/bizboost/calendar", 'View Calendar', 'posts', 'normal',
            ['post_id' => $postId, 'post_title' => $postTitle, 'scheduled_for' => $scheduledFor]
        );
    }

    public function notifyLowStock(User $user, string $productName, int $quantity, int $productId)
    {
        return $this->notify(
            $user, 'warning', 'Low Stock Alert',
            "{$productName} has only {$quantity} items left in stock",
            "/bizboost/products/{$productId}", 'View Product', 'products', 'high',
            ['product_id' => $productId, 'product_name' => $productName, 'quantity' => $quantity]
        );
    }

    public function notifyAiContentGenerated(User $user, string $contentType)
    {
        return $this->notify(
            $user, 'ai', 'AI Content Ready',
            "Your {$contentType} has been generated and is ready to use",
            "/bizboost/ai", 'View Content', 'ai', 'normal',
            ['content_type' => $contentType]
        );
    }

    public function notifyTeamInvitation(User $user, string $businessName, string $role)
    {
        return $this->notify(
            $user, 'team', 'Team Invitation',
            "You've been invited to join {$businessName} as {$role}",
            "/bizboost/team", 'View Invitation', 'team', 'high',
            ['business_name' => $businessName, 'role' => $role]
        );
    }

    public function notifyReminderDue(User $user, string $reminderTitle, int $reminderId)
    {
        return $this->notify(
            $user, 'reminder', 'Reminder Due', $reminderTitle,
            "/bizboost/reminders", 'View Reminders', 'reminders', 'high',
            ['reminder_id' => $reminderId]
        );
    }

    public function notifyCampaignStarted(User $user, string $campaignName, int $campaignId)
    {
        return $this->notify(
            $user, 'campaign', 'Campaign Started',
            "Your campaign \"{$campaignName}\" is now active",
            "/bizboost/campaigns/{$campaignId}", 'View Campaign', 'campaigns', 'normal',
            ['campaign_id' => $campaignId, 'campaign_name' => $campaignName]
        );
    }

    public function notifySubscriptionUpgrade(User $user, string $newTier)
    {
        return $this->notify(
            $user, 'subscription', 'Subscription Upgraded',
            "Congratulations! You've upgraded to the {$newTier} plan",
            "/bizboost/usage", 'View Benefits', 'subscription', 'normal',
            ['tier' => $newTier]
        );
    }

    public function notifyInfo(User $user, string $title, string $message, ?string $actionUrl = null)
    {
        return $this->notify(
            $user, 'info', $title, $message, $actionUrl,
            $actionUrl ? 'Learn More' : null, 'general', 'normal'
        );
    }

    public function notifyWarning(User $user, string $title, string $message, ?string $actionUrl = null)
    {
        return $this->notify(
            $user, 'warning', $title, $message, $actionUrl,
            $actionUrl ? 'Take Action' : null, 'general', 'high'
        );
    }
}
