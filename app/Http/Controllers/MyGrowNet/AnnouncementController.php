<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Application\UseCases\Announcement\GetUserAnnouncementsUseCase;
use App\Application\UseCases\Announcement\MarkAnnouncementAsReadUseCase;
use Illuminate\Http\Request;

/**
 * Announcement Controller
 * 
 * Handles HTTP requests for announcements
 */
class AnnouncementController extends Controller
{
    public function __construct(
        private GetUserAnnouncementsUseCase $getUserAnnouncementsUseCase,
        private MarkAnnouncementAsReadUseCase $markAsReadUseCase
    ) {}

    /**
     * Get announcements for current user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userTier = $user->currentMembershipTier->name ?? 'Associate';

        $announcements = $this->getUserAnnouncementsUseCase->execute($user->id, $userTier);

        return response()->json([
            'announcements' => $announcements,
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $userTier = $user->currentMembershipTier->name ?? 'Associate';

        $count = $this->getUserAnnouncementsUseCase->getUnreadCount($user->id, $userTier);

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Mark announcement as read
     * Handles both numeric IDs (admin announcements) and string IDs (personalized)
     */
    public function markAsRead(Request $request, string $id)
    {
        $this->markAsReadUseCase->execute($id, $request->user()->id);

        return response()->json([
            'success' => true,
        ]);
    }
}
