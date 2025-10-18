<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\InvestmentTier;

class OpportunityController extends Controller
{
    public function index()
    {
        $opportunities = InvestmentTier::where('is_active', true)
            ->orderBy('minimum_investment')
            ->get()
            ->map(function ($tier) {
                return [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'description' => $tier->description ?? "Invest in the {$tier->name} tier and earn {$tier->fixed_profit_rate}% annual returns",
                    'minimum_investment' => $tier->minimum_investment,
                    'expected_returns' => $tier->fixed_profit_rate,
                    'risk_level' => $this->getRiskLevel($tier->minimum_investment),
                    'duration' => 12, // months
                    'features' => [
                        "Annual profit share: {$tier->fixed_profit_rate}%",
                        "Referral commission: {$tier->direct_referral_rate}%",
                        "Matrix bonus eligible",
                        "Quarterly performance bonuses"
                    ]
                ];
            });

        return Inertia::render('Opportunities/Index', [
            'opportunities' => $opportunities
        ]);
    }

    public function show($id)
    {
        $tier = InvestmentTier::findOrFail($id);
        
        $opportunity = [
            'id' => $tier->id,
            'name' => $tier->name,
            'description' => $tier->description ?? "Invest in the {$tier->name} tier and earn {$tier->fixed_profit_rate}% annual returns",
            'minimum_investment' => $tier->minimum_investment,
            'expected_returns' => $tier->fixed_profit_rate,
            'risk_level' => $this->getRiskLevel($tier->minimum_investment),
            'duration' => 12,
            'features' => [
                "Annual profit share: {$tier->fixed_profit_rate}%",
                "Referral commission: {$tier->direct_referral_rate}%",
                "Matrix bonus eligible",
                "Quarterly performance bonuses"
            ],
            'benefits' => [
                'Guaranteed annual returns',
                'Multi-level referral commissions',
                'Matrix spillover benefits',
                'Quarterly bonus distributions',
                'Tier upgrade opportunities'
            ]
        ];

        return Inertia::render('Opportunities/Show', [
            'opportunity' => $opportunity
        ]);
    }

    private function getRiskLevel($amount)
    {
        if ($amount <= 1000) return 'low';
        if ($amount <= 5000) return 'medium';
        return 'high';
    }
}