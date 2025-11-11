<?php

namespace App\Application\UseCases\Announcement;

use App\Domain\Announcement\Services\AnnouncementService;
use App\Domain\Announcement\Services\PersonalizedAnnouncementService;

/**
 * Mark Announcement as Read Use Case
 * 
 * Handles both admin announcements and personalized announcements
 */
class MarkAnnouncementAsReadUseCase
{
    public function __construct(
        private AnnouncementService $announcementService,
        private PersonalizedAnnouncementService $personalizedAnnouncementService
    ) {}

    public function execute(int|string $announcementId, int $userId): void
    {
        // Check if it's a personalized announcement (string ID with underscore pattern)
        if (is_string($announcementId) || !is_numeric($announcementId)) {
            // Personalized announcement - dismiss it
            $this->personalizedAnnouncementService->dismissAnnouncement($userId, (string)$announcementId);
        } else {
            // Admin announcement - mark as read
            $this->announcementService->markAsRead((int)$announcementId, $userId);
        }
    }
}
