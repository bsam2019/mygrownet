<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display notifications center page
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $notifications = NotificationModel::forUser($user->id)
            ->where('module', 'bizboost')
            ->notArchived()
            ->orderByDesc('created_at')
            ->paginate(20);

        $transformedNotifications = $notifications->through(fn ($notification) => [
            'id' => $notification->id,
            'type' => $notification->type,
            'category' => $notification->category,
            'title' => $notification->title,
            'message' => $notification->message,
            'actionUrl' => $notification->action_url,
            'actionText' => $notification->action_text,
            'data' => $notification->data,
            'priority' => $notification->priority,
            'read' => $notification->read_at !== null,
            'readAt' => $notification->read_at?->toISOString(),
            'createdAt' => $notification->created_at->toISOString(),
            'timeAgo' => $notification->created_at->diffForHumans(),
        ]);

        $unreadCount = NotificationModel::forUser($user->id)
            ->where('module', 'bizboost')
            ->unread()
            ->notArchived()
            ->count();

        return Inertia::render('BizBoost/Notifications/Index', [
            'notifications' => $transformedNotifications,
            'unreadCount' => $unreadCount,
        ]);
    }


    /**
     * Get notifications for dropdown (AJAX)
     */
    public function dropdown(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $notifications = NotificationModel::forUser($user->id)
            ->where('module', 'bizboost')
            ->notArchived()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'category' => $notification->category,
                'title' => $notification->title,
                'message' => $notification->message,
                'actionUrl' => $notification->action_url,
                'actionText' => $notification->action_text,
                'priority' => $notification->priority,
                'read' => $notification->read_at !== null,
                'createdAt' => $notification->created_at->toISOString(),
                'timeAgo' => $notification->created_at->diffForHumans(),
            ]);

        $unreadCount = NotificationModel::forUser($user->id)
            ->where('module', 'bizboost')
            ->unread()
            ->notArchived()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Get unread count (for polling/badge updates)
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = NotificationModel::forUser($request->user()->id)
            ->where('module', 'bizboost')
            ->unread()
            ->notArchived()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = NotificationModel::forUser($request->user()->id)
            ->where('module', 'bizboost')
            ->findOrFail($id);
        
        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        NotificationModel::forUser($request->user()->id)
            ->where('module', 'bizboost')
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Archive a notification
     */
    public function archive(Request $request, string $id): JsonResponse
    {
        $notification = NotificationModel::forUser($request->user()->id)
            ->where('module', 'bizboost')
            ->findOrFail($id);
        
        $notification->update(['archived_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = NotificationModel::forUser($request->user()->id)
            ->where('module', 'bizboost')
            ->findOrFail($id);
        
        $notification->delete();

        return response()->json(['success' => true]);
    }
}
