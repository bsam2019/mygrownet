<?php

namespace App\Domain\Announcement\Services;

use App\Domain\Announcement\Entities\Announcement;
use App\Domain\Announcement\Repositories\AnnouncementRepositoryInterface;
use DateTimeImmutable;

/**
 * Announcement Domain Service
 * 
 * Handles business logic for announcements
 */
class AnnouncementService
{
    public function __construct(
        private AnnouncementRepositoryInterface $repository
    ) {}

    /**
     * Get active announcements for a specific user
     */
    public function getActiveAnnouncementsForUser(int $userId, string $userTier, bool $hasStarterKit = false): array
    {
        $now = new DateTimeImmutable();
        $allAnnouncements = $this->repository->getActiveAnnouncements();

        return array_filter(
            $allAnnouncements,
            function(Announcement $announcement) use ($userId, $userTier, $now, $hasStarterKit) {
                // First check basic visibility (active, dates, tier)
                if (!$announcement->isVisibleToUser($userTier, $now)) {
                    return false;
                }
                
                // Check for user-specific targeting
                if ($announcement->getTargetAudience()->isUserSpecific()) {
                    return $announcement->getTargetAudience()->getUserId() === $userId;
                }
                
                // Check for starter kit owners
                if ($announcement->getTargetAudience()->isStarterKitOwners()) {
                    return $hasStarterKit;
                }
                
                return true;
            }
        );
    }

    /**
     * Get unread announcements for a user
     */
    public function getUnreadAnnouncementsForUser(int $userId, string $userTier, bool $hasStarterKit = false): array
    {
        $activeAnnouncements = $this->getActiveAnnouncementsForUser($userId, $userTier, $hasStarterKit);
        $readAnnouncementIds = $this->repository->getReadAnnouncementIds($userId);

        return array_filter(
            $activeAnnouncements,
            fn(Announcement $announcement) => !in_array($announcement->getId(), $readAnnouncementIds)
        );
    }

    /**
     * Mark announcement as read
     */
    public function markAsRead(int $announcementId, int $userId): void
    {
        $this->repository->markAsRead($announcementId, $userId);
    }

    /**
     * Get count of unread announcements
     */
    public function getUnreadCount(int $userId, string $userTier, bool $hasStarterKit = false): int
    {
        return count($this->getUnreadAnnouncementsForUser($userId, $userTier, $hasStarterKit));
    }
}
