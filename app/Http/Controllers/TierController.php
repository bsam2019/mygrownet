<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\ProfessionalLevel;
use Illuminate\Http\Request;

class TierController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $currentTier = $user->membershipTier;
        
        // Get all active professional levels
        $tiers = ProfessionalLevel::where('is_active', true)
            ->orderBy('level')
            ->get()
            ->map(function ($tier) use ($user, $currentTier) {
                return [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'level' => $tier->level,
                    'monthly_fee' => $tier->monthly_subscription_fee ?? 0,
                    'benefits' => $tier->benefits ?? [],
                    'is_current' => $currentTier && $currentTier->id === $tier->id,
                    'can_upgrade' => !$currentTier || $tier->level > $currentTier->level
                ];
            });
        
        // Get upgrade information
        $upgradeInfo = $user->checkMyGrowNetTierUpgradeEligibility();
        
        return Inertia::render('Tiers/Index', [
            'tiers' => $tiers,
            'currentTier' => $currentTier,
            'upgradeInfo' => $upgradeInfo,
        ]);
    }
    
    public function compare(Request $request)
    {
        $user = auth()->user();
        $currentTier = $user->membershipTier;
        
        $tierIds = $request->input('tiers', []);
        if (empty($tierIds) && $currentTier) {
            $nextTier = ProfessionalLevel::where('level', '>', $currentTier->level)
                ->orderBy('level')
                ->first();
            if ($nextTier) {
                $tierIds = [$currentTier->id, $nextTier->id];
            }
        }
        
        $tiers = ProfessionalLevel::whereIn('id', $tierIds)->get();
        
        return Inertia::render('Tiers/Compare', [
            'tiers' => $tiers,
            'currentTier' => $currentTier
        ]);
    }
    
    public function show(ProfessionalLevel $tier)
    {
        $user = auth()->user();
        $currentTier = $user->membershipTier;
        
        $canUpgrade = !$currentTier || $tier->level > $currentTier->level;
        
        return Inertia::render('Tiers/Show', [
            'tier' => $tier,
            'canUpgrade' => $canUpgrade,
            'currentTier' => $currentTier
        ]);
    }
}