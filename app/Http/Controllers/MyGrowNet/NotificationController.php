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
     * Get unread notification count (API endpoint)
     */
    public function count(Request $request)
    {
        // Force JSON response for API calls
        if ($request->wantsJson() || !$request->header('X-Inertia')) {
            $count = $this->notificationRepository->countUnreadByUserId($request->user()->id);
            return response()->json(['count' => $count]);
        }
        
        // If somehow called as Inertia request, return back
        return back();
    }

    /**
     * Get recent notifications (API endpoint)
     */
    public function index(Request $request)
    {
        // Force JSON response for API calls
        if ($request->wantsJson() || !$request->header('X-Inertia')) {
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
        
        // If somehow called as Inertia request, return back
        return back();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $id)
    {
        \Log::info('Marking notification as read', ['id' => $id, 'user_id' => $request->user()->id]);
        
        $this->notificationRepository->markAsRead($id);
        
        // Return JSON for API calls, Inertia response for page requests
        if ($request->wantsJson() || !$request->header('X-Inertia')) {
            return response()->json(['success' => true]);
        }
        
        return back();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $this->notificationRepository->markAllAsRead($request->user()->id);
        
        // Return JSON for API calls, Inertia response for page requests
        if ($request->wantsJson() || !$request->header('X-Inertia')) {
            return response()->json(['success' => true]);
        }
        
        return back();
    }

    /**
     * Notification center page
     */
    public function center(Request $request)
    {
        return Inertia::render('MyGrowNet/Notifications');
    }
}
