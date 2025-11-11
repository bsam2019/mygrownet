<?php

namespace App\Infrastructure\Persistence\Eloquent\Settings;

use Illuminate\Database\Eloquent\Model;

class GiftSettingsModel extends Model
{
    protected $table = 'gift_settings';

    protected $fillable = [
        'max_gifts_per_month',
        'max_gift_amount_per_month',
        'min_wallet_balance_to_gift',
        'gift_feature_enabled',
        'gift_fee_percentage',
    ];

    protected $casts = [
        'max_gifts_per_month' => 'integer',
        'max_gift_amount_per_month' => 'integer',
        'min_wallet_balance_to_gift' => 'integer',
        'gift_feature_enabled' => 'boolean',
        'gift_fee_percentage' => 'decimal:2',
    ];

    public static function get(): array
    {
        $settings = self::first();
        
        if (!$settings) {
            return [
                'max_gifts_per_month' => 5,
                'max_gift_amount_per_month' => 5000,
                'min_wallet_balance_to_gift' => 1000,
                'gift_feature_enabled' => true,
                'gift_fee_percentage' => 0,
            ];
        }

        return $settings->toArray();
    }
}
