<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Web;

use App\Domain\GrowStream\Services\NotificationService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    private const MODULE = 'growstream';

    public function __construct(
        private NotificationService $notifications,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $notifications = NotificationModel::forUser($user->id)
            ->byModule(self::MODULE)
            ->notArchived()
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn ($notification) => [
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
            ->byModule(self::MODULE)
            ->unread()
            ->notArchived()
            ->count();

        return Inertia::render('GrowStream/Notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function dropdown(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = NotificationModel::forUser($user->id)
            ->byModule(self::MODULE)
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
            ->byModule(self::MODULE)
            ->unread()
            ->notArchived()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = NotificationModel::forUser($request->user()->id)
            ->byModule(self::MODULE)
            ->unread()
            ->notArchived()
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = NotificationModel::forUser($request->user()->id)
            ->byModule(self::MODULE)
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        NotificationModel::forUser($request->user()->id)
            ->byModule(self::MODULE)
            ->unread()
            ->notArchived()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function archive(Request $request, string $id): JsonResponse
    {
        $notification = NotificationModel::forUser($request->user()->id)
            ->byModule(self::MODULE)
            ->findOrFail($id);

        $notification->update(['archived_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = NotificationModel::forUser($request->user()->id)
            ->byModule(self::MODULE)
            ->findOrFail($id);

        $notification->delete();

        return response()->json(['success' => true]);
    }
}
