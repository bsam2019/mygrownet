<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\Notification\Core\Services\NotificationDataService;
use App\Domain\GrowStream\Repositories\CreatorSubscriptionRepositoryInterface;
use App\Models\User;

class NotificationService
{
    private const MODULE = 'growstream';

    public function __construct(
        private NotificationDataService $notifications,
        private CreatorSubscriptionRepositoryInterface $subscriptionRepo,
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
    ) {
        return $this->notifications->create(
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
    }

    /**
     * Intent 1: creator's content was approved (or approved & published).
     */
    public function notifyContentApproved(User $creator, string $videoTitle, int $videoId, string $videoSlug, bool $published = false): void
    {
        $this->notify(
            user: $creator,
            type: 'content.approved',
            title: $published ? 'Content published' : 'Content approved',
            message: "Your video \"{$videoTitle}\" has been {$this->approvalPhrase($published)} and is now live on GrowStream.",
            actionUrl: route('growstream.video.detail', ['slug' => $videoSlug]),
            actionText: 'View Video',
            category: 'content',
            priority: 'normal',
            data: ['video_id' => $videoId, 'video_title' => $videoTitle, 'video_slug' => $videoSlug, 'published' => $published],
        );
    }

    /**
     * Intent 1b: creator's content was rejected.
     */
    public function notifyContentRejected(User $creator, string $videoTitle, int $videoId, string $reason): void
    {
        $this->notify(
            user: $creator,
            type: 'content.rejected',
            title: 'Content not approved',
            message: "Your video \"{$videoTitle}\" was not approved. Reason: {$reason}",
            actionUrl: route('growstream.creator.videos.edit', $videoId),
            actionText: 'Edit Video',
            category: 'content',
            priority: 'high',
            data: ['video_id' => $videoId, 'video_title' => $videoTitle, 'reason' => $reason],
        );
    }

    /**
     * Intent 2: a subscribed creator uploaded new content.
     * Notifies all active subscribers of the creator.
     */
    public function notifySubscribersOfNewVideo(int $creatorId, string $videoTitle, string $videoSlug): int
    {
        $subscribers = $this->subscriptionRepo->activeSubscriberIdsForCreator($creatorId);

        foreach ($subscribers as $userId) {
            $user = User::find($userId);
            if (!$user) {
                continue;
            }

            $this->notify(
                user: $user,
                type: 'creator.upload',
                title: 'New from your creator',
                message: "A creator you follow uploaded \"{$videoTitle}\".",
                actionUrl: route('growstream.video.detail', ['slug' => $videoSlug]),
                actionText: 'Watch Now',
                category: 'content',
                priority: 'normal',
                data: ['creator_id' => $creatorId, 'video_title' => $videoTitle, 'video_slug' => $videoSlug],
            );
        }

        return count($subscribers);
    }

    /**
     * Intent 3: creator subscription confirmation / renewal.
     */
    public function notifySubscriptionConfirmation(User $user, string $creatorName, float $amount): void
    {
        $this->notify(
            user: $user,
            type: 'subscription.confirmed',
            title: 'Subscription active',
            message: "You're now subscribed to {$creatorName} (K" . number_format($amount, 2) . "/month).",
            actionUrl: route('growstream.home'),
            actionText: 'Explore',
            category: 'subscription',
            priority: 'normal',
            data: ['creator_name' => $creatorName, 'amount' => $amount],
        );
    }

    public function notifySubscriptionCancelled(User $user, string $creatorName): void
    {
        $this->notify(
            user: $user,
            type: 'subscription.cancelled',
            title: 'Subscription cancelled',
            message: "Your subscription to {$creatorName} has been cancelled.",
            actionUrl: route('growstream.home'),
            actionText: 'Explore',
            category: 'subscription',
            priority: 'normal',
            data: ['creator_name' => $creatorName],
        );
    }

    private function approvalPhrase(bool $published): string
    {
        return $published ? 'approved and published' : 'approved';
    }
}
