<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\LifePlusNotificationService;
use App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    private const MODULE = 'lifeplus';

    public function __construct(
        private readonly LifePlusNotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $userId = auth()->id();
        
        // Create welcome notifications for first-time users
        $this->notificationService->createWelcomeNotifications($userId);
        
        // Get notifications for this user from the lifeplus module
        $notifications = NotificationModel::forUser($userId)
            ->where(function ($query) {
                $query->where('module', self::MODULE)
                      ->orWhere('module', 'core');
            })
            ->notArchived()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $this->mapCategoryToType($notification->category),
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'time' => $notification->created_at->toISOString(),
                    'read' => $notification->read_at !== null,
                    'action_url' => $notification->action_url,
                ];
            });

        return Inertia::render('LifePlus/Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = NotificationModel::forUser(auth()->id())
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->update(['read_at' => now()]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marked as read');
    }

    public function markAllAsRead(Request $request)
    {
        NotificationModel::forUser(auth()->id())
            ->where(function ($query) {
                $query->where('module', self::MODULE)
                      ->orWhere('module', 'core');
            })
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read');
    }

    public function destroy(Request $request, $id)
    {
        $notification = NotificationModel::forUser(auth()->id())
            ->where('id', $id)
            ->first();

        if ($notification) {
            // Archive instead of delete to preserve history
            $notification->update(['archived_at' => now()]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification deleted');
    }

    /**
     * Map notification category to frontend type
     */
    private function mapCategoryToType(?string $category): string
    {
        return match ($category) {
            'success', 'achievement', 'welcome' => 'success',
            'warning', 'alert' => 'warning',
            'reminder', 'task' => 'reminder',
            default => 'info',
        };
    }
}
