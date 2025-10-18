<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ReferralResource;
use App\Http\Resources\UserResource;

class ReferralController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function index()
    {
        $referrals = auth()->user()->directReferrals()
            ->with(['investments' => function($query) {
                $query->where('status', 'active');
            }])
            ->latest()
            ->paginate(10);

        return ReferralResource::collection($referrals);
    }

    public function stats()
    {
        $user = Auth::user();
        $stats = $this->referralService->getUserReferralStats($user->id);

        return response()->json([
            'total_referrals' => $stats['total_referrals'],
            'active_referrals' => $stats['active_referrals'],
            'total_commission' => $stats['total_commission'],
            'pending_commission' => $stats['pending_commission']
        ]);
    }

    public function tree()
    {
        $user = Auth::user();
        $tree = $this->referralService->getReferralTree($user->id);

        return response()->json($tree);
    }

    public function commissions()
    {
        $user = Auth::user();
        $commissions = $this->referralService->getUserCommissions($user->id);

        return response()->json($commissions);
    }

    public function link()
    {
        $user = Auth::user();
        $referralLink = route('register', ['ref' => $user->referral_code]);

        return response()->json([
            'referral_link' => $referralLink
        ]);
    }

    public function linkStatus($userId)
    {
        $referrer = auth()->user();
        $referred = $referrer->directReferrals()
            ->where('id', $userId)
            ->first();

        return response()->json([
            'is_referred' => $referred !== null,
            'active_investments' => $referred ? $referred->investments()
                ->where('status', 'active')
                ->count() : 0,
            'total_invested' => $referred ? $referred->investments()
                ->where('status', 'active')
                ->sum('amount') : 0
        ]);
    }
}
