<?php

namespace App\Domain\Announcement\Repositories;

use App\Domain\Announcement\Entities\Announcement;

/**
 * Announcement Repository Interface
 * 
 * Defines contract for announcement data access
 */
interface AnnouncementRepositoryInterface
{
    /**
     * Get all active announcements
     * 
     * @return Announcement[]
     */
    public function getActiveAnnouncements(): array;

    /**
     * Get announcement by ID
     */
    public function findById(int $id): ?Announcement;

    /**
     * Get IDs of announcements read by user
     * 
     * @return int[]
     */
    public function getReadAnnouncementIds(int $userId): array;

    /**
     * Mark announcement as read by user
     */
    public function markAsRead(int $announcementId, int $userId): void;

    /**
     * Save announcement
     */
    public function save(Announcement $announcement): void;
}
