<?php

namespace App\Http\Controllers\Api;

use App\Domain\GrowNet\Services\AccountActivationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GrowNetActivationController extends Controller
{
    public function __construct(
        protected AccountActivationService $activationService
    ) {}

    /**
     * POST /api/grownet/activate
     * Enable/activate GrowNet account for authenticated user.
     */
    public function activate(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $sponsorCode = $request->input('sponsor_code');
        $result = $this->activationService->activateGrowNet($user, $sponsorCode);

        $status = $result['success'] ? 200 : 422;
        return response()->json($result, $status);
    }

    /**
     * GET /api/grownet/status
     * Get active GrowNet account status and point totals for current user.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['is_active' => false], 200);
        }

        $levelTitle = \DB::table('professional_levels')
            ->where('slug', $user->current_professional_level ?? 'starter')
            ->value('name') ?? 'Level 1: Starter';

        $pbPoints = \DB::table('user_points')->where('user_id', $user->id)->value('lifetime_points') ?? 0;
        $mpPoints = \DB::table('user_points')->where('user_id', $user->id)->value('monthly_points') ?? 0;

        return response()->json([
            'is_active' => (bool) ($user->is_grownet_active || !empty($user->referral_code)),
            'level_slug' => $user->current_professional_level ?? 'starter',
            'level_title' => $levelTitle,
            'referral_code' => $user->referral_code,
            'referral_link' => $user->referral_code ? url('/register?ref=' . $user->referral_code) : null,
            'pb_points' => (int) $pbPoints,
            'mp_points' => (int) $mpPoints,
            'portal_url' => config('app.url') . '/grownet',
        ]);
    }
}
