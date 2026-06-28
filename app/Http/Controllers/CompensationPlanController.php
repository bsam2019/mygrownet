<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ReferralCommission;

class CompensationPlanController extends Controller
{
    /**
     * Display the compensation plan
     */
    public function show()
    {
        // Referral Bonus Structure (7 Levels)
        $referralBonusStructure = [
            [
                'level' => 1,
                'team_size' => 3,
                'commission_percentage' => 15,
                'potential_earnings' => 225,
                'per_person' => 75,
            ],
            [
                'level' => 2,
                'team_size' => 9,
                'commission_percentage' => 10,
                'potential_earnings' => 450,
                'per_person' => 50,
            ],
            [
                'level' => 3,
                'team_size' => 27,
                'commission_percentage' => 8,
                'potential_earnings' => 1080,
                'per_person' => 40,
            ],
            [
                'level' => 4,
                'team_size' => 81,
                'commission_percentage' => 6,
                'potential_earnings' => 2430,
                'per_person' => 30,
            ],
            [
                'level' => 5,
                'team_size' => 243,
                'commission_percentage' => 4,
                'potential_earnings' => 4860,
                'per_person' => 20,
            ],
            [
                'level' => 6,
                'team_size' => 729,
                'commission_percentage' => 3,
                'potential_earnings' => 10935,
                'per_person' => 15,
            ],
            [
                'level' => 7,
                'team_size' => 2187,
                'commission_percentage' => 2,
                'potential_earnings' => 21870,
                'per_person' => 10,
            ],
        ];
        
        $totalPotential = array_sum(array_column($referralBonusStructure, 'potential_earnings'));
        $totalTeamSize = array_sum(array_column($referralBonusStructure, 'team_size'));
        
        return Inertia::render('CompensationPlan/Show', [
            'registrationAmount' => 500,
            'referralBonusStructure' => $referralBonusStructure,
            'totalPotential' => $totalPotential,
            'totalTeamSize' => $totalTeamSize,
            'commissionRates' => ReferralCommission::COMMISSION_RATES,
            'levelNames' => ReferralCommission::LEVEL_NAMES,
        ]);
    }
}
