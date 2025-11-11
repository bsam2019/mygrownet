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
    public function getActiveAnnouncementsForUser(int $userId, string $userTier): array
    {
        $now = new DateTimeImmutable();
        $allAnnouncements = $this->repository->getActiveAnnouncements();

        return array_filter(
            $allAnnouncements,
            fn(Announcement $announcement) => $announcement->isVisibleToUser($userTier, $now)
        );
    }

    /**
     * Get unread announcements for a user
     */
    public function getUnreadAnnouncementsForUser(int $userId, string $userTier): array
    {
        $activeAnnouncements = $this->getActiveAnnouncementsForUser($userId, $userTier);
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
    public function getUnreadCount(int $userId, string $userTier): int
    {
        return count($this->getUnreadAnnouncementsForUser($userId, $userTier));
    }
}
