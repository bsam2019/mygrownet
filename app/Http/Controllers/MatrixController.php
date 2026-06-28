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
        
        // Get matrix structure (3 levels deep) and flatten it
        $matrixStructureNested = $user->buildMatrixStructure(3);
        $matrixStructure = $this->flattenMatrixStructure($matrixStructureNested);
        
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
    
    /**
     * Flatten nested matrix structure to flat array
     */
    private function flattenMatrixStructure(array $structure): array
    {
        if (empty($structure)) {
            return [];
        }
        
        $flattened = [];
        
        // Add current node
        $flattened[] = [
            'id' => $structure['user']['id'] ?? null,
            'level' => $structure['level'] ?? 0,
            'position' => $structure['position'] ?? 0,
            'user' => $structure['user'] ?? [],
            'children' => []
        ];
        
        // Recursively add children
        if (!empty($structure['children'])) {
            foreach ($structure['children'] as $child) {
                $childFlattened = $this->flattenMatrixStructure($child);
                $flattened = array_merge($flattened, $childFlattened);
            }
        }
        
        return $flattened;
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
        
        $matrixStructureNested = $user->buildMatrixStructure(3);
        $matrixStructure = $this->flattenMatrixStructure($matrixStructureNested);
        
        return response()->json([
            'matrixStructure' => $matrixStructure,
            'downlineCounts' => $user->getMatrixDownlineCount(),
            'matrixPosition' => $user->getMatrixPosition()
        ]);
    }
}