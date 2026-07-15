<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\StockFlowNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private StockFlowNotificationService $notificationService,
    ) {}

    public function count(Request $request)
    {
        $userId = $request->user('stockflow')?->id;
        $companyId = $request->session()->get('stockflow_company_id');

        if (!$userId) return response()->json(['count' => 0]);

        return response()->json([
            'count' => $this->notificationService->getUnreadCount($userId, $companyId),
        ]);
    }

    public function list(Request $request)
    {
        $userId = $request->user('stockflow')?->id;
        $companyId = $request->session()->get('stockflow_company_id');

        if (!$userId) return response()->json(['notifications' => []]);

        return response()->json([
            'notifications' => $this->notificationService->getNotifications($userId, $companyId, 50),
        ]);
    }

    public function markAsRead(Request $request, int $notificationId)
    {
        $this->notificationService->markAsRead($notificationId);
        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $userId = $request->user('stockflow')?->id;
        $companyId = $request->session()->get('stockflow_company_id');

        if ($userId) {
            $this->notificationService->markAllAsRead($userId, $companyId);
        }

        return response()->json(['success' => true]);
    }
}
