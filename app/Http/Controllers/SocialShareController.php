<?php

namespace App\Http\Controllers;

use App\Services\SocialShareTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialShareController extends Controller
{
    public function __construct(
        private SocialShareTrackingService $shareTrackingService
    ) {}

    /**
     * Record a social share event
     */
    public function recordShare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'share_type' => 'required|string|in:referral_link,content,platform_link',
            'platform' => 'nullable|string|in:facebook,twitter,whatsapp,linkedin,copy_link,other',
            'shared_url' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();

        $this->shareTrackingService->recordShare(
            $userId,
            $validated['share_type'],
            $validated['platform'] ?? null,
            $validated['shared_url'] ?? null,
            $request->ip(),
            $request->userAgent()
        );

        $stats = $this->shareTrackingService->getUserStats($userId);

        return response()->json([
            'success' => true,
            'message' => 'Share recorded successfully',
            'stats' => $stats,
        ]);
    }

    /**
     * Get user's sharing statistics
     */
    public function getStats(): JsonResponse
    {
        $userId = Auth::id();
        $stats = $this->shareTrackingService->getUserStats($userId);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Get user's recent shares
     */
    public function getRecentShares(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $limit = $request->query('limit', 10);

        $shares = $this->shareTrackingService->getRecentShares($userId, $limit);

        return response()->json([
            'success' => true,
            'shares' => $shares,
        ]);
    }
}
