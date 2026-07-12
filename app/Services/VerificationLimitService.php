<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class VerificationLimitService
{
    private const CACHE_KEY = 'withdrawal_limits';
    private const CACHE_TTL = 3600;
    private const SETTING_KEY = 'withdrawal_limits';

    public function getLimits(string $level, string $currency = 'ZMW'): array
    {
        $allLimits = $this->getAllLimits();
        $levelLimits = $allLimits[$level] ?? $allLimits['basic'];
        $suffix = $currency === 'USD' ? '_usd' : '_zmw';

        return [
            'daily_withdrawal' => $levelLimits["daily{$suffix}"] ?? $levelLimits['daily_zmw'],
            'monthly_withdrawal' => $levelLimits["monthly{$suffix}"] ?? $levelLimits['monthly_zmw'],
            'single_transaction' => $levelLimits["single{$suffix}"] ?? $levelLimits['single_zmw'],
        ];
    }

    public function getAllLimits(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $setting = SystemSetting::where('key', self::SETTING_KEY)->first();
            if ($setting && $setting->value) {
                return $setting->value;
            }
            return $this->defaults();
        });
    }

    public function updateLimits(array $limits): void
    {
        SystemSetting::updateOrCreate(
            ['key' => self::SETTING_KEY],
            [
                'value' => $limits,
                'description' => 'Withdrawal limits per verification level (ZMW and USD)',
            ]
        );
        Cache::forget(self::CACHE_KEY);
    }

    public function defaults(): array
    {
        return [
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
        ];
    }
}
