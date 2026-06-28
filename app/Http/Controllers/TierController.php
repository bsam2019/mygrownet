<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\InvestmentTier;
use Illuminate\Http\Request;

class TierController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $currentTier = $user->currentInvestmentTier;
        
        // Get all active tiers
        $tiers = InvestmentTier::active()->ordered()->get()->map(function ($tier) use ($user, $currentTier) {
            return [
                'id' => $tier->id,
                'name' => $tier->name,
                'minimum_investment' => $tier->minimum_investment,
                'benefits' => $tier->getTierSpecificBenefits(),
                'is_current' => $currentTier && $currentTier->id === $tier->id,
                'can_upgrade' => !$currentTier || $tier->minimum_investment > $currentTier->minimum_investment
            ];
        });
        
        // Get upgrade information
        $upgradeInfo = $user->checkTierUpgradeEligibility();
        $tierProgress = $user->getTierProgressPercentage();
        
        // Get upgrade benefits if there's a next tier
        $upgradeBenefits = null;
        if ($currentTier) {
            $upgradeBenefits = $currentTier->getUpgradeBenefits();
        }
        
        return Inertia::render('Tiers/Index', [
            'tiers' => $tiers,
            'currentTier' => $currentTier,
            'upgradeInfo' => $upgradeInfo,
            'tierProgress' => $tierProgress,
            'upgradeBenefits' => $upgradeBenefits
        ]);
    }
    
    public function compare(Request $request)
    {
        $user = auth()->user();
        $currentTier = $user->currentInvestmentTier;
        
        $tierIds = $request->input('tiers', []);
        if (empty($tierIds) && $currentTier) {
            $nextTier = $currentTier->getNextTier();
            if ($nextTier) {
                $tierIds = [$currentTier->id, $nextTier->id];
            }
        }
        
        $tiers = InvestmentTier::whereIn('id', $tierIds)->get();
        $comparisons = [];
        
        if ($tiers->count() >= 2) {
            $baseTier = $tiers->first();
            foreach ($tiers->skip(1) as $tier) {
                $comparisons[] = $baseTier->compareWith($tier);
            }
        }
        
        return Inertia::render('Tiers/Compare', [
            'tiers' => $tiers,
            'comparisons' => $comparisons,
            'currentTier' => $currentTier
        ]);
    }
    
    public function show(InvestmentTier $tier)
    {
        $user = auth()->user();
        $currentTier = $user->currentInvestmentTier;
        
        $tierBenefits = $tier->getTierSpecificBenefits();
        $maxEarnings = $tier->calculateMaxMatrixEarnings(1000); // Example with K1000 investment
        
        $canUpgrade = !$currentTier || $tier->minimum_investment > $currentTier->minimum_investment;
        $upgradeRequirements = null;
        
        if ($canUpgrade && $currentTier) {
            $upgradeRequirements = $currentTier->compareWith($tier);
        }
        
        return Inertia::render('Tiers/Show', [
            'tier' => $tier,
            'tierBenefits' => $tierBenefits,
            'maxEarnings' => $maxEarnings,
            'canUpgrade' => $canUpgrade,
            'upgradeRequirements' => $upgradeRequirements,
            'currentTier' => $currentTier
        ]);
    }
}