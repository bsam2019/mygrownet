<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LgrSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('lgr_settings')->insert([
            'key' => 'cycle_duration_days',
            'value' => '90',
            'description' => 'Duration of each LGR cycle in days',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('lgr_settings')->insert([
            'key' => 'min_qualification_activities',
            'value' => '5',
            'description' => 'Minimum number of activities required to qualify',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('lgr_settings')->insert([
            'key' => 'pool_percentage',
            'value' => '60',
            'description' => 'Percentage of profits allocated to LGR pool',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('lgr_settings')->insert([
            'key' => 'max_payout_per_member',
            'value' => '50000',
            'description' => 'Maximum payout amount per member per cycle (in ngwee)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('lgr_settings')->insert([
            'key' => 'activity_weights',
            'value' => json_encode([
                'starter_kit_purchase' => 10,
                'product_purchase' => 5,
                'referral' => 8,
                'course_completion' => 7,
                'workshop_attendance' => 6,
                'subscription_renewal' => 5,
                'venture_investment' => 10,
                'community_engagement' => 3,
            ]),
            'description' => 'Point weights for different activity types',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('lgr_settings')->insert([
            'key' => 'premium_tier_multiplier',
            'value' => '1.5',
            'description' => 'Multiplier for premium starter kit tier members',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('lgr_settings')->insert([
            'key' => 'auto_start_cycles',
            'value' => '1',
            'description' => 'Automatically start new cycles when previous one ends',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
