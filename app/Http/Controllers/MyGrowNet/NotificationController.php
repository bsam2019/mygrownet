<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepository
    ) {}

    /**
     * Get unread notification count
     */
    public function count(Request $request)
    {
        $count = $this->notificationRepository->countUnreadByUserId($request->user()->id);
        
        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications
     */
    public function index(Request $request)
    {
        $notifications = $this->notificationRepository->findByUserId(
            $request->user()->id,
            limit: 50
        );
        
        return response()->json([
            'notifications' => array_map(fn($n) => [
                'id' => $n->id(),
                'title' => $n->title(),
                'message' => $n->message(),
                'action_url' => $n->actionUrl(),
                'action_text' => $n->actionText(),
                'priority' => $n->priority()->value,
                'read_at' => $n->readAt()?->format('Y-m-d H:i:s'),
                'created_at' => $n->createdAt()->format('Y-m-d H:i:s'),
            ], $notifications)
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $id)
    {
        $this->notificationRepository->markAsRead($id);
        
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $this->notificationRepository->markAllAsRead($request->user()->id);
        
        return response()->json(['success' => true]);
    }

    /**
     * Notification center page
     */
    public function center(Request $request)
    {
        return Inertia::render('MyGrowNet/Notifications');
    }
}
