<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Display notifications list
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $notifications = $user->notifications()
            ->where(function ($query) {
                $query->where('type', 'like', '%GrowBiz%')
                    ->orWhere('data->type', 'like', 'growbiz_%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $transformedNotifications = $notifications->through(fn ($notification) => [
            'id' => $notification->id,
            'type' => $notification->data['type'] ?? 'notification',
            'title' => $this->getNotificationTitle($notification),
            'message' => $notification->data['message'] ?? '',
            'url' => $notification->data['url'] ?? null,
            'read' => $notification->read_at !== null,
            'readAt' => $notification->read_at?->toISOString(),
            'createdAt' => $notification->created_at->toISOString(),
            'timeAgo' => $notification->created_at->diffForHumans(),
        ]);

        $unreadCount = $user->unreadNotifications()
            ->where(function ($query) {
                $query->where('type', 'like', '%GrowBiz%')
                    ->orWhere('data->type', 'like', 'growbiz_%');
            })
            ->count();

        // Return JSON for AJAX requests (dropdown)
        if ($request->wantsJson()) {
            return response()->json([
                'notifications' => $transformedNotifications,
                'unreadCount' => $unreadCount,
            ]);
        }

        return Inertia::render('GrowBiz/Notifications/Index', [
            'notifications' => $transformedNotifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications()
            ->where(function ($query) {
                $query->where('type', 'like', '%GrowBiz%')
                    ->orWhere('data->type', 'like', 'growbiz_%');
            })
            ->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read');
    }

    /**
     * Get unread count (API endpoint)
     */
    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()
            ->where(function ($query) {
                $query->where('type', 'like', '%GrowBiz%')
                    ->orWhere('data->type', 'like', 'growbiz_%');
            })
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get notification title based on type
     */
    private function getNotificationTitle($notification): string
    {
        $type = $notification->data['type'] ?? '';
        
        return match ($type) {
            'growbiz_task_assigned' => 'New Task Assigned',
            'growbiz_task_status_changed' => 'Task Status Updated',
            'growbiz_task_comment' => 'New Comment on Task',
            'growbiz_task_due_reminder' => 'Task Due Reminder',
            'growbiz_employee_invitation' => 'Team Invitation',
            default => 'Notification',
        };
    }
}
