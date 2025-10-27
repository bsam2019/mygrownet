<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ReferralCommission;
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

    public function index()
    {
        $user = Auth::user();
        
        // Generate referral code if not exists
        if (!$user->referral_code) {
            $user->generateUniqueReferralCode();
        }
        
        // Get referral statistics
        $referralStats = $this->referralService->getReferralStatistics($user);
        $earningsBreakdown = $this->referralService->getEarningsBreakdown($user);
        $performance = $this->referralService->getPerformanceMetrics($user);
        $recentActivity = $this->referralService->getRecentActivity($user);
        $tierDistribution = $this->referralService->getTierDistribution($user);
        
        // Get matrix data
        $matrixData = $this->referralService->getMatrixData($user);
        $spilloverInfo = $this->referralService->getSpilloverInfo($user);
        $matrixStats = $this->referralService->getMatrixStats($user);
        
        // Get spillover data
        $spilloverData = $this->referralService->getSpilloverData($user);
        $level1Referrals = $this->referralService->getLevel1Referrals($user);
        $spilloverPlacements = $this->referralService->getSpilloverPlacements($user);
        $spilloverHistory = $this->referralService->getSpilloverHistory($user);
        $spilloverOpportunities = $this->referralService->getSpilloverOpportunities($user);
        $spilloverStats = $this->referralService->getSpilloverStats($user);
        
        // Get referral link data
        $referralLink = url('/register?ref=' . $user->referral_code);
        $codeStats = $this->referralService->getCodeStats($user);
        $linkStats = $this->referralService->getLinkStats($user);
        $messageTemplates = $this->referralService->getMessageTemplates();
        
        return inertia('Referrals/Index', [
            'referralStats' => $referralStats,
            'earningsBreakdown' => $earningsBreakdown,
            'performance' => $performance,
            'recentActivity' => $recentActivity,
            'tierDistribution' => $tierDistribution,
            'matrixData' => $matrixData,
            'spilloverInfo' => $spilloverInfo,
            'matrixStats' => $matrixStats,
            'spilloverData' => $spilloverData,
            'level1Referrals' => $level1Referrals,
            'spilloverPlacements' => $spilloverPlacements,
            'spilloverHistory' => $spilloverHistory,
            'spilloverOpportunities' => $spilloverOpportunities,
            'spilloverStats' => $spilloverStats,
            'referralCode' => $user->referral_code,
            'referralLink' => $referralLink,
            'shortLink' => null,
            'codeStats' => $codeStats,
            'linkStats' => $linkStats,
            'messageTemplates' => $messageTemplates,
            'currentUserTier' => $user->currentInvestmentTier?->name,
            'teamMembers' => $level1Referrals, // Add teamMembers for the table
            'totalTeamMembers' => count($level1Referrals),
        ]);
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

    public function tree()
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

    public function statistics()
    {
        return $this->getStatistics();
    }

    public function commissions()
    {
        return $this->getCommissionHistory();
    }

    public function matrixPosition()
    {
        $user = Auth::user();
        
        return response()->json([
            'position' => $user->matrix_position ?? null,
            'level' => $user->matrix_level ?? 1,
        ]);
    }

    public function matrixGenealogy()
    {
        $user = Auth::user();
        
        $matrixStructure = $user->buildMatrixStructure(3);
        
        return inertia('Referrals/MatrixGenealogy', [
            'matrixData' => $matrixStructure,
        ]);
    }

    public function referralsByLevel()
    {
        $user = Auth::user();
        
        $byLevel = [];
        for ($level = 1; $level <= 7; $level++) {
            $byLevel[$level] = $user->referralCommissions()
                ->where('level', $level)
                ->with(['referred'])
                ->get();
        }
        
        return response()->json($byLevel);
    }

    public function performanceReport()
    {
        $user = Auth::user();
        
        $performance = $this->referralService->getPerformanceMetrics($user);
        $stats = $this->referralService->getReferralStatistics($user);
        
        return inertia('Referrals/PerformanceReport', [
            'performance' => $performance,
            'stats' => $stats,
        ]);
    }

    public function generateReferralCode()
    {
        $user = Auth::user();
        
        if (!$user->referral_code) {
            $user->generateUniqueReferralCode();
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $user->referral_code,
                'referral_link' => url('/register?ref=' . $user->referral_code),
                'message' => 'Referral code generated successfully'
            ]
        ]);
    }

    public function validateReferralCode(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string|max:20',
        ]);
        
        $referrer = User::where('referral_code', $request->referral_code)->first();
        
        if (!$referrer) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid referral code'
            ], 404);
        }
        
        return response()->json([
            'valid' => true,
            'referrer' => [
                'name' => $referrer->name,
                'id' => $referrer->id,
            ]
        ]);
    }

    public function calculateCommission(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'level' => 'required|integer|min:1|max:7',
        ]);
        
        $percentage = ReferralCommission::getCommissionRate($request->level);
        $amount = $request->amount * ($percentage / 100);
        
        return response()->json([
            'percentage' => $percentage,
            'commission_amount' => $amount,
        ]);
    }

    public function export()
    {
        $user = Auth::user();
        
        // Export referral data
        $referrals = $user->referrals()->with('subscriptions')->get();
        $commissions = $user->referralCommissions()->with('referred')->get();
        
        return response()->json([
            'referrals' => $referrals,
            'commissions' => $commissions,
        ]);
    }
} 