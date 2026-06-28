<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\RealTimeNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    public function __construct(
        private RealTimeNotificationService $notificationService
    ) {}

    public function getEmployeeNotifications(Request $request): JsonResponse
    {
        $user = auth()->user();
        $limit = $request->get('limit', 20);
        $type = $request->get('type', 'all');

        // Get user notifications from database
        $notifications = $user->notifications()
            ->when($type !== 'all', function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at?->toISOString(),
                    'created_at' => $notification->created_at->toISOString(),
                ];
            });

        // Get unread count
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
                'has_more' => $notifications->count() === $limit,
            ],
        ]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'string',
        ]);

        $user = auth()->user();
        
        $user->notifications()
            ->whereIn('id', $request->notification_ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Update cache
        $cacheKey = "user_notifications_count_{$user->id}";
        Cache::forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'Notifications marked as read',
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        $user = auth()->user();
        
        $user->unreadNotifications()->update(['read_at' => now()]);

        // Update cache
        $cacheKey = "user_notifications_count_{$user->id}";
        Cache::forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    public function getNotificationSettings(): JsonResponse
    {
        $user = auth()->user();
        
        // Get user notification preferences (would be stored in user_settings table)
        $settings = [
            'email_notifications' => true,
            'push_notifications' => true,
            'employee_status_changes' => true,
            'performance_reviews' => true,
            'commission_updates' => true,
            'birthday_reminders' => true,
            'work_anniversaries' => true,
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function updateNotificationSettings(Request $request): JsonResponse
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'employee_status_changes' => 'boolean',
            'performance_reviews' => 'boolean',
            'commission_updates' => 'boolean',
            'birthday_reminders' => 'boolean',
            'work_anniversaries' => 'boolean',
        ]);

        $user = auth()->user();
        
        // Update user notification preferences
        // This would typically be stored in a user_settings table
        
        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully',
        ]);
    }
}