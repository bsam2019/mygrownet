<?php

namespace App\Application\UseCases\Announcement;

use App\Domain\Announcement\Services\AnnouncementService;
use App\Domain\Announcement\Services\PersonalizedAnnouncementService;
use App\Domain\Announcement\Services\EventBasedAnnouncementService;
use App\Models\User;

/**
 * Get User Announcements Use Case
 * 
 * Application service for retrieving user-specific announcements
 * Combines admin, event-based, and personalized announcements
 */
class GetUserAnnouncementsUseCase
{
    public function __construct(
        private AnnouncementService $announcementService,
        private PersonalizedAnnouncementService $personalizedAnnouncementService,
        private EventBasedAnnouncementService $eventBasedAnnouncementService
    ) {}

    public function execute(int $userId, string $userTier): array
    {
        // Get user to check starter kit status
        $user = User::find($userId);
        $hasStarterKit = $user ? $user->has_starter_kit : false;
        
        // Get admin-created announcements
        $adminAnnouncements = $this->announcementService->getUnreadAnnouncementsForUser($userId, $userTier, $hasStarterKit);

        $adminAnnouncementsArray = array_map(function ($announcement) {
            return [
                'id' => $announcement->getId(),
                'title' => $announcement->getTitle(),
                'message' => $announcement->getMessage(),
                'type' => $announcement->getType()->value(),
                'is_urgent' => $announcement->isUrgent(),
                'is_personalized' => false,
                'created_at' => $announcement->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $adminAnnouncements);

        // Get event-based announcements (user-specific, time-limited)
        $eventBasedAnnouncements = $this->eventBasedAnnouncementService->getUserSpecificAnnouncements($userId);
        
        // Get personalized announcements (dynamic, generated on-the-fly)
        $personalizedAnnouncements = [];
        
        if ($user) {
            $personalizedAnnouncements = $this->personalizedAnnouncementService->generateForUser($user);
        }

        // Combine and prioritize:
        // 1. Urgent admin announcements first
        // 2. Event-based announcements (congratulations, etc.)
        // 3. Personalized announcements (progress, opportunities)
        // 4. Regular admin announcements
        $urgentAdmin = array_filter($adminAnnouncementsArray, fn($a) => $a['is_urgent']);
        $regularAdmin = array_filter($adminAnnouncementsArray, fn($a) => !$a['is_urgent']);

        $allAnnouncements = array_merge(
            $urgentAdmin,
            $eventBasedAnnouncements,
            $personalizedAnnouncements,
            $regularAdmin
        );

        // Limit to top 5 to avoid overwhelming users
        return array_slice($allAnnouncements, 0, 5);
    }

    public function getUnreadCount(int $userId, string $userTier): int
    {
        // Get user to check starter kit status
        $user = User::find($userId);
        $hasStarterKit = $user ? $user->has_starter_kit : false;
        
        // Count admin announcements
        $adminCount = $this->announcementService->getUnreadCount($userId, $userTier, $hasStarterKit);
        
        // Count event-based announcements
        $eventBasedAnnouncements = $this->eventBasedAnnouncementService->getUserSpecificAnnouncements($userId);
        $eventBasedCount = count($eventBasedAnnouncements);
        
        // Count personalized announcements
        $personalizedCount = 0;
        
        if ($user) {
            $personalizedAnnouncements = $this->personalizedAnnouncementService->generateForUser($user);
            $personalizedCount = count($personalizedAnnouncements);
        }

        return $adminCount + $eventBasedCount + $personalizedCount;
    }
}
