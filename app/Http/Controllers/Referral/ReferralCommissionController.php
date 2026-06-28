<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Models\ReferralCommission;
use App\Models\Investment;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralCommissionController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * Process referral commissions for a new investment
     */
    public function processNewInvestment(int $investmentId)
    {
        try {
            $investment = Investment::findOrFail($investmentId);
            $this->referralService->processNewInvestment($investment);
            
            return response()->json([
                'success' => true,
                'message' => 'Referral commissions processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get referral earnings for a user
     */
    public function getReferralEarnings(int $userId)
    {
        $user = User::findOrFail($userId);
        $stats = $this->referralService->getUserReferralStats($user);
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Process all pending commissions
     */
    public function processPendingCommissions()
    {
        try {
            $this->referralService->processPendingCommissions();
            
            return response()->json([
                'success' => true,
                'message' => 'Pending commissions processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get commission details
     */
    public function getCommissionDetails(int $commissionId)
    {
        $commission = ReferralCommission::with(['referrer', 'referee', 'investment'])
            ->findOrFail($commissionId);
            
        return response()->json([
            'success' => true,
            'data' => $commission
        ]);
    }

    /**
     * Get user's referral tree
     */
    public function getReferralTree(int $userId)
    {
        $user = User::findOrFail($userId);
        $tree = $this->buildReferralTree($user);
        
        return response()->json([
            'success' => true,
            'data' => $tree
        ]);
    }

    /**
     * Build referral tree recursively
     */
    protected function buildReferralTree($user, $level = 1, $maxLevel = 3)
    {
        if ($level > $maxLevel) {
            return null;
        }

        $children = $user->directReferrals()
            ->with(['investments' => function($query) {
                $query->where('status', 'active');
            }])
            ->get()
            ->map(function($referral) use ($level, $maxLevel) {
                return [
                    'id' => $referral->id,
                    'name' => $referral->name,
                    'email' => $referral->email,
                    'activeInvestments' => $referral->investments->count(),
                    'totalInvested' => $referral->investments->sum('amount'),
                    'children' => $this->buildReferralTree($referral, $level + 1, $maxLevel)
                ];
            });

        return $children->isEmpty() ? null : $children;
    }
}