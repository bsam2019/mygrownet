<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class WithdrawalLimitsSeeder extends Seeder
{
    public function run(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'withdrawal_limits'],
            [
                'value' => [
                    'basic' => [
                        'daily_zmw' => 1000,
                        'daily_usd' => 40,
                        'monthly_zmw' => 10000,
                        'monthly_usd' => 400,
                        'single_zmw' => 500,
                        'single_usd' => 20,
                    ],
                    'enhanced' => [
                        'daily_zmw' => 5000,
                        'daily_usd' => 200,
                        'monthly_zmw' => 50000,
                        'monthly_usd' => 2000,
                        'single_zmw' => 2000,
                        'single_usd' => 80,
                    ],
                    'premium' => [
                        'daily_zmw' => 20000,
                        'daily_usd' => 800,
                        'monthly_zmw' => 200000,
                        'monthly_usd' => 8000,
                        'single_zmw' => 10000,
                        'single_usd' => 400,
                    ],
                ],
                'description' => 'Withdrawal limits per verification level (ZMW and USD)',
            ]
        );
    }
}
