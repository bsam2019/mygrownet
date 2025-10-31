<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LgrSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'lgr_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable or disable the LGR system',
            ],
            [
                'key' => 'lgr_daily_rate',
                'value' => '25.00',
                'type' => 'decimal',
                'description' => 'Daily LGC earning rate',
            ],
            [
                'key' => 'lgr_cycle_duration',
                'value' => '70',
                'type' => 'integer',
                'description' => 'LGR cycle duration in days',
            ],
            [
                'key' => 'lgr_max_cash_conversion',
                'value' => '40',
                'type' => 'integer',
                'description' => 'Maximum percentage of LGC convertible to cash',
            ],
            [
                'key' => 'lgr_min_cash_conversion',
                'value' => '100',
                'type' => 'decimal',
                'description' => 'Minimum amount for cash conversion',
            ],
            [
                'key' => 'lgr_pool_reserve_percentage',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Percentage of pool to maintain as reserve',
            ],
            [
                'key' => 'lgr_registration_fee_percentage',
                'value' => '20',
                'type' => 'integer',
                'description' => 'Percentage of registration fees allocated to LGR pool',
            ],
            [
                'key' => 'lgr_product_sale_percentage',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Percentage of product sales allocated to LGR pool',
            ],
            [
                'key' => 'lgr_marketplace_fee_percentage',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Percentage of marketplace fees allocated to LGR pool',
            ],
            [
                'key' => 'lgr_venture_fee_percentage',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Percentage of venture fees allocated to LGR pool',
            ],
            [
                'key' => 'lgr_subscription_percentage',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Percentage of subscription renewals allocated to LGR pool',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('lgr_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
