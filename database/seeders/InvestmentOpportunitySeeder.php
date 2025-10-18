<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvestmentOpportunity;

class InvestmentOpportunitySeeder extends Seeder
{
    public function run(): void
    {
        $opportunities = [
            [
                'name' => 'Agricultural Development Fund',
                'risk_level' => 'low',
                'duration' => 12,
                'minimum_investment' => 5000.00,
                'expected_returns' => 15.00,
                'description' => 'Support local agricultural projects with stable returns',
                'status' => 'active'
            ],
            [
                'name' => 'Tech Startup Growth Fund',
                'risk_level' => 'high',
                'duration' => 24,
                'minimum_investment' => 10000.00,
                'expected_returns' => 25.00,
                'description' => 'High-growth potential technology startups',
                'status' => 'active'
            ],
            [
                'name' => 'Real Estate Development',
                'risk_level' => 'medium',
                'duration' => 18,
                'minimum_investment' => 15000.00,
                'expected_returns' => 20.00,
                'description' => 'Commercial and residential property development',
                'status' => 'active'
            ]
        ];

        foreach ($opportunities as $opportunity) {
            InvestmentOpportunity::firstOrCreate(
                ['name' => $opportunity['name']],
                $opportunity
            );
        }
    }
} 