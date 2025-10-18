<?php

namespace App\Http\Controllers;

use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function getStatistics()
    {
        $user = Auth::user();
        
        // Use VBIF-specific methods
        $referralStats = $user->getReferralStats();
        $earnings = $user->calculateTotalEarningsDetailed();
        
        return response()->json([
            'totalReferrals' => $referralStats['total_referrals'],
            'activeReferrals' => $referralStats['active_referrals'],
            'totalCommission' => $referralStats['total_commission'],
            'pendingCommission' => $referralStats['pending_commission'],
            'matrixCommissions' => $earnings['matrix_commissions'],
            'reinvestmentBonuses' => $earnings['reinvestment_bonuses']
        ]);
    }

    public function getReferralTree()
    {
        $user = Auth::user();
        
        // Use VBIF-specific matrix structure
        $matrixStructure = $user->buildMatrixStructure(3);
        $traditionalTree = $user->getReferralTree(3);
        
        return response()->json([
            'matrixStructure' => $matrixStructure,
            'traditionalTree' => $traditionalTree
        ]);
    }

    public function getCommissionHistory()
    {
        $user = Auth::user();
        
        // Get commission history from referral commissions
        $commissions = $user->referralCommissions()
            ->with(['investment.user', 'investment.tier'])
            ->latest()
            ->paginate(20);
        
        return response()->json($commissions);
    }

    public function getReferralLink()
    {
        $user = Auth::user();
        
        // Generate referral code if not exists
        if (!$user->referral_code) {
            $user->generateUniqueReferralCode();
        }
        
        $link = url('/register?ref=' . $user->referral_code);
        
        return response()->json([
            'link' => $link,
            'referral_code' => $user->referral_code
        ]);
    }
} 