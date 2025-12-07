<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Events\BizBoost\NotificationReceived;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Centralized notification service for BizBoost module
 * Uses the platform's unified notification system
 */
class NotificationService
{
    private const MODULE = 'bizboost';

    /**
     * Send a notification to a user
     */
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
    ): NotificationModel {
        $notification = NotificationModel::create([
            'id' => Str::uuid()->toString(),
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => $type,
            'category' => $category,
            'module' => self::MODULE,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'data' => $data,
            'priority' => $priority,
            'created_at' => now(),
        ]);

        // Broadcast real-time notification if enabled
        if ($broadcast) {
            $this->broadcastNotification($user, $notification);
        }

        return $notification;
    }

    /**
     * Broadcast notification to user's BizBoost channel
     */
    private function broadcastNotification(User $user, NotificationModel $notification): void
    {
        try {
            $business = BizBoostBusinessModel::where('user_id', $user->id)->first();
            
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
            // Log but don't fail if broadcast fails
            \Log::warning('Failed to broadcast BizBoost notification', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify about a new sale
     */
    public function notifySale(User $user, float $amount, string $customerName, int $saleId): NotificationModel
    {
        return $this->notify(
            $user,
            'sale',
            'New Sale Recorded',
            "K" . number_format($amount, 2) . " from {$customerName}",
            "/bizboost/sales",
            'View Sales',
            'sales',
            'normal',
            ['sale_id' => $saleId, 'amount' => $amount, 'customer' => $customerName]
        );
    }

    /**
     * Notify about a new customer
     */
    public function notifyNewCustomer(User $user, string $customerName, int $customerId): NotificationModel
    {
        return $this->notify(
            $user,
            'customer',
            'New Customer Added',
            "{$customerName} has been added to your customer list",
            "/bizboost/customers/{$customerId}",
            'View Customer',
            'customers',
            'normal',
            ['customer_id' => $customerId, 'customer_name' => $customerName]
        );
    }

    /**
     * Notify about post published
     */
    public function notifyPostPublished(User $user, string $postTitle, int $postId): NotificationModel
    {
        return $this->notify(
            $user,
            'post',
            'Post Published',
            "Your post \"{$postTitle}\" has been published successfully",
            "/bizboost/posts/{$postId}",
            'View Post',
            'posts',
            'normal',
            ['post_id' => $postId, 'post_title' => $postTitle]
        );
    }

    /**
     * Notify about post scheduled
     */
    public function notifyPostScheduled(User $user, string $postTitle, string $scheduledFor, int $postId): NotificationModel
    {
        return $this->notify(
            $user,
            'post',
            'Post Scheduled',
            "Your post \"{$postTitle}\" is scheduled for {$scheduledFor}",
            "/bizboost/calendar",
            'View Calendar',
            'posts',
            'normal',
            ['post_id' => $postId, 'post_title' => $postTitle, 'scheduled_for' => $scheduledFor]
        );
    }

    /**
     * Notify about low stock
     */
    public function notifyLowStock(User $user, string $productName, int $quantity, int $productId): NotificationModel
    {
        return $this->notify(
            $user,
            'warning',
            'Low Stock Alert',
            "{$productName} has only {$quantity} items left in stock",
            "/bizboost/products/{$productId}",
            'View Product',
            'products',
            'high',
            ['product_id' => $productId, 'product_name' => $productName, 'quantity' => $quantity]
        );
    }

    /**
     * Notify about AI content generated
     */
    public function notifyAiContentGenerated(User $user, string $contentType): NotificationModel
    {
        return $this->notify(
            $user,
            'ai',
            'AI Content Ready',
            "Your {$contentType} has been generated and is ready to use",
            "/bizboost/ai",
            'View Content',
            'ai',
            'normal',
            ['content_type' => $contentType]
        );
    }

    /**
     * Notify about team invitation
     */
    public function notifyTeamInvitation(User $user, string $businessName, string $role): NotificationModel
    {
        return $this->notify(
            $user,
            'team',
            'Team Invitation',
            "You've been invited to join {$businessName} as {$role}",
            "/bizboost/team",
            'View Invitation',
            'team',
            'high',
            ['business_name' => $businessName, 'role' => $role]
        );
    }

    /**
     * Notify about reminder due
     */
    public function notifyReminderDue(User $user, string $reminderTitle, int $reminderId): NotificationModel
    {
        return $this->notify(
            $user,
            'reminder',
            'Reminder Due',
            $reminderTitle,
            "/bizboost/reminders",
            'View Reminders',
            'reminders',
            'high',
            ['reminder_id' => $reminderId]
        );
    }

    /**
     * Notify about campaign started
     */
    public function notifyCampaignStarted(User $user, string $campaignName, int $campaignId): NotificationModel
    {
        return $this->notify(
            $user,
            'campaign',
            'Campaign Started',
            "Your campaign \"{$campaignName}\" is now active",
            "/bizboost/campaigns/{$campaignId}",
            'View Campaign',
            'campaigns',
            'normal',
            ['campaign_id' => $campaignId, 'campaign_name' => $campaignName]
        );
    }

    /**
     * Notify about subscription upgrade
     */
    public function notifySubscriptionUpgrade(User $user, string $newTier): NotificationModel
    {
        return $this->notify(
            $user,
            'subscription',
            'Subscription Upgraded',
            "Congratulations! You've upgraded to the {$newTier} plan",
            "/bizboost/usage",
            'View Benefits',
            'subscription',
            'normal',
            ['tier' => $newTier]
        );
    }

    /**
     * Send a general info notification
     */
    public function notifyInfo(User $user, string $title, string $message, ?string $actionUrl = null): NotificationModel
    {
        return $this->notify(
            $user,
            'info',
            $title,
            $message,
            $actionUrl,
            $actionUrl ? 'Learn More' : null,
            'general',
            'normal'
        );
    }

    /**
     * Send a warning notification
     */
    public function notifyWarning(User $user, string $title, string $message, ?string $actionUrl = null): NotificationModel
    {
        return $this->notify(
            $user,
            'warning',
            $title,
            $message,
            $actionUrl,
            $actionUrl ? 'Take Action' : null,
            'general',
            'high'
        );
    }
}
