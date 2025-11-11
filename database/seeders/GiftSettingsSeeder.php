<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\Settings\GiftSettingsModel;

class GiftSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GiftSettingsModel::firstOrCreate(
            ['id' => 1],
            [
                'max_gifts_per_month' => 5,
                'max_gift_amount_per_month' => 5000,
                'min_wallet_balance_to_gift' => 1000,
                'gift_feature_enabled' => true,
                'gift_fee_percentage' => 0,
            ]
        );
    }
}
