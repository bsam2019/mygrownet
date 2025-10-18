<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class MatrixController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Generate referral code if not exists
        if (!$user->referral_code) {
            $user->generateUniqueReferralCode();
            $user->refresh(); // Refresh the model to get the updated referral_code
        }
        
        // Get matrix structure (3 levels deep)
        $matrixStructure = $user->buildMatrixStructure(3);
        
        // Get downline counts by level
        $downlineCounts = $user->getMatrixDownlineCount();
        
        // Get referral stats
        $referralStats = $user->getReferralStats();
        
        // Get matrix position info
        $matrixPosition = $user->getMatrixPosition();
        
        // Get referral tree for traditional view
        $referralTree = $user->getReferralTree(3);
        
        return Inertia::render('Matrix/Index', [
            'matrixStructure' => $matrixStructure,
            'downlineCounts' => $downlineCounts,
            'referralStats' => $referralStats,
            'matrixPosition' => $matrixPosition,
            'referralTree' => $referralTree,
            'referralCode' => $user->referral_code ?? '',
            'referralLink' => $user->referral_code ? url('/register?ref=' . $user->referral_code) : url('/register')
        ]);
    }
    
    public function generateReferralCode()
    {
        $user = auth()->user();
        
        if (!$user->referral_code) {
            $code = $user->generateUniqueReferralCode();
            return response()->json([
                'success' => true,
                'referral_code' => $code,
                'referral_link' => url('/register?ref=' . $code)
            ]);
        }
        
        return response()->json([
            'success' => true,
            'referral_code' => $user->referral_code,
            'referral_link' => url('/register?ref=' . $user->referral_code)
        ]);
    }
    
    public function getMatrixData()
    {
        $user = auth()->user();
        
        return response()->json([
            'matrixStructure' => $user->buildMatrixStructure(3),
            'downlineCounts' => $user->getMatrixDownlineCount(),
            'matrixPosition' => $user->getMatrixPosition()
        ]);
    }
}