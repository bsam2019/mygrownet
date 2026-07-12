<?php

namespace App\Http\Controllers\GrowMart;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Services\CartService;
use App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $notifications = NotificationModel::forUser(Auth::id())
            ->byModule('growmart')
            ->notArchived()
            ->orderBy('created_at', 'desc')
            ->take($request->get('limit', 50))
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'category' => $n->category,
                'action_url' => $n->action_url,
                'action_text' => $n->action_text,
                'data' => $n->data,
                'read_at' => $n->read_at?->toISOString(),
                'created_at' => $n->created_at->toISOString(),
            ]);

        $unreadCount = NotificationModel::forUser(Auth::id())
            ->byModule('growmart')
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function page(): \Inertia\Response
    {
        $notifications = NotificationModel::forUser(Auth::id())
            ->byModule('growmart')
            ->notArchived()
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'category' => $n->category,
                'action_url' => $n->action_url,
                'action_text' => $n->action_text,
                'data' => $n->data,
                'read_at' => $n->read_at?->toISOString(),
                'created_at' => $n->created_at->toISOString(),
            ]);

        $unreadCount = NotificationModel::forUser(Auth::id())
            ->byModule('growmart')
            ->unread()
            ->count();

        return Inertia::render('GrowMart/Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'cartCount' => $this->cartService->getSummary(Auth::id())['item_count'],
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        $count = NotificationModel::forUser(Auth::id())
            ->byModule('growmart')
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead(string $id): JsonResponse
    {
        $notification = NotificationModel::forUser(Auth::id())
            ->byModule('growmart')
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(): JsonResponse
    {
        NotificationModel::forUser(Auth::id())
            ->byModule('growmart')
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $id): JsonResponse
    {
        $notification = NotificationModel::forUser(Auth::id())
            ->byModule('growmart')
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }
}
