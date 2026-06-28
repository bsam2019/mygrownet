<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get all notifications for the user
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'type' => $notification->data['type'] ?? 'system',
                    'read_at' => $notification->read_at?->toISOString(),
                    'created_at' => $notification->created_at->toISOString(),
                    'data' => $notification->data,
                ];
            });

        $stats = [
            'total' => $notifications->count(),
            'unread' => $notifications->where('read_at', null)->count(),
        ];

        return Inertia::render('CMS/Notifications/Index', [
            'notifications' => $notifications,
            'stats' => $stats,
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, string $id)
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->find($id);
        
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
        }

        return back();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        
        $user->unreadNotifications->markAsRead();

        return back();
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, string $id)
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->find($id);
        
        if ($notification) {
            $notification->delete();
        }

        return back();
    }

    /**
     * Get recent notifications for header dropdown
     */
    public function recent(Request $request)
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'type' => $notification->data['type'] ?? 'system',
                    'read_at' => $notification->read_at?->toISOString(),
                    'created_at' => $notification->created_at->toISOString(),
                    'data' => $notification->data,
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }
}
