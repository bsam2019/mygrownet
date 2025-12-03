<?php

namespace App\Http\Controllers\GrowFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display notifications page or return JSON for API requests.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->where('module', 'growfinance')
            ->orderBy('created_at', 'desc')
            ->take($request->get('limit', 50))
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => class_basename($notification->type),
                    'data' => $notification->data,
                    'read_at' => $notification->read_at?->toISOString(),
                    'created_at' => $notification->created_at->toISOString(),
                ];
            });

        $unreadCount = $user->unreadNotifications()
            ->where('module', 'growfinance')
            ->count();

        // Return JSON for explicit AJAX requests (not Inertia)
        // Check for X-Requested-With header which is set by fetch/axios but NOT by Inertia
        if ($request->ajax() && !$request->header('X-Inertia')) {
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);
        }

        // Return Inertia page for regular and Inertia requests
        return Inertia::render('GrowFinance/Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(): JsonResponse
    {
        $count = Auth::user()
            ->unreadNotifications()
            ->where('module', 'growfinance')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->where('module', 'growfinance')
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all GrowFinance notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        Auth::user()
            ->unreadNotifications()
            ->where('module', 'growfinance')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id): JsonResponse
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->where('module', 'growfinance')
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }
}
