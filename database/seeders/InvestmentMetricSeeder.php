<?php

namespace Database\Seeders;

use App\Models\InvestmentMetric;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvestmentMetricSeeder extends Seeder
{
    public function run(): void
    {
        // Generate metrics for the last 30 days
        $date = Carbon::now()->subDays(30);

        while ($date <= Carbon::now()) {
            InvestmentMetric::updateOrCreate(
                ['date' => $date->format('Y-m-d')],
                [
                    'total_value' => fake()->randomFloat(2, 100000, 1000000),
                    'total_count' => fake()->numberBetween(50, 500),
                    'average_roi' => fake()->randomFloat(2, 5, 25), // 5-25% ROI
                    'active_investors' => fake()->numberBetween(20, 200),
                    'success_rate' => fake()->randomFloat(2, 75, 95), // 75-95% success rate
                ]
            );

            $date->addDay();
        }
    }
}
