<?php

namespace App\Domain\Announcement\Services;

use App\Infrastructure\Persistence\Eloquent\Announcement\AnnouncementModel;
use Carbon\Carbon;

/**
 * Event-Based Announcement Service
 * 
 * Creates time-limited, user-specific announcements triggered by events
 */
class EventBasedAnnouncementService
{
    /**
     * Create a starter kit congratulations announcement
     */
    public function createStarterKitCongratulations(int $userId, int $createdBy = 1): void
    {
        AnnouncementModel::create([
            'title' => 'Congratulations on Your Starter Kit! 🌟',
            'message' => 'You now have access to exclusive learning resources, shop credit, and enhanced earning opportunities. Visit the Learning Center to get started!',
            'type' => 'success',
            'target_audience' => 'user:' . $userId,
            'user_id' => $userId,
            'is_urgent' => false,
            'is_active' => true,
            'starts_at' => now(),
            'expires_at' => now()->addDays(7),
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Create a tier advancement congratulations announcement
     */
    public function createTierAdvancementCongratulations(int $userId, string $newTier, int $createdBy = 1): void
    {
        AnnouncementModel::create([
            'title' => "Welcome to {$newTier} Level! 🎉",
            'message' => "Congratulations on advancing to {$newTier}! You now have access to higher commissions, exclusive benefits, and new opportunities. Keep up the great work!",
            'type' => 'success',
            'target_audience' => 'user:' . $userId,
            'user_id' => $userId,
            'is_urgent' => false,
            'is_active' => true,
            'starts_at' => now(),
            'expires_at' => now()->addDays(7),
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Create a starter kit gift received announcement
     */
    public function createStarterKitGift(int $recipientId, string $gifterName, string $tier, int $createdBy = 1): void
    {
        $tierLabel = ucfirst($tier);
        
        AnnouncementModel::create([
            'title' => "🎁 You Received a Starter Kit Gift!",
            'message' => "{$gifterName} gifted you a {$tierLabel} Starter Kit! You now have access to exclusive learning resources, shop credit, and enhanced earning opportunities. Thank your sponsor and start exploring!",
            'type' => 'success',
            'target_audience' => 'user:' . $recipientId,
            'user_id' => $recipientId,
            'is_urgent' => false,
            'is_active' => true,
            'starts_at' => now(),
            'expires_at' => now()->addDays(7),
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Get user-specific announcements
     */
    public function getUserSpecificAnnouncements(int $userId): array
    {
        return AnnouncementModel::where('user_id', $userId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'message' => $announcement->message,
                    'type' => $announcement->type,
                    'is_urgent' => $announcement->is_urgent,
                    'is_personalized' => false,
                    'is_event_based' => true,
                    'created_at' => $announcement->created_at->toISOString(),
                ];
            })
            ->toArray();
    }

    /**
     * Clean up expired announcements
     */
    public function cleanupExpiredAnnouncements(): int
    {
        return AnnouncementModel::where('expires_at', '<', now())
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
