<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class CommissionSettingsService
{
    private const CACHE_KEY = 'commission_settings';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get commission base percentage (default: 50%)
     * This is the percentage of purchase price used as commission base
     */
    public function getBasePercentage(): float
    {
        return (float) $this->getSetting('commission_base_percentage', 50);
    }

    /**
     * Get non-kit member multiplier (default: 50%)
     * Members without starter kit earn this percentage of normal commission
     */
    public function getNonKitMultiplier(): float
    {
        return (float) $this->getSetting('commission_non_kit_multiplier', 50) / 100;
    }

    /**
     * Get commission rates per level
     */
    public function getLevelRates(): array
    {
        $rates = $this->getSetting('commission_level_rates', null);
        
        if (!$rates) {
            return [
                1 => 15.0,
                2 => 10.0,
                3 => 8.0,
                4 => 6.0,
                5 => 4.0,
                6 => 3.0,
                7 => 2.0,
            ];
        }

        // Already decoded by cache or is array
        if (is_array($rates)) {
            // Convert string keys to integers
            $result = [];
            foreach ($rates as $key => $value) {
                $result[(int) $key] = (float) $value;
            }
            return $result;
        }
        
        // Decode if string
        $decoded = json_decode($rates, true);
        if (is_array($decoded)) {
            $result = [];
            foreach ($decoded as $key => $value) {
                $result[(int) $key] = (float) $value;
            }
            return $result;
        }

        return [
            1 => 15.0,
            2 => 10.0,
            3 => 8.0,
            4 => 6.0,
            5 => 4.0,
            6 => 3.0,
            7 => 2.0,
        ];
    }

    /**
     * Get commission rate for a specific level
     */
    public function getRateForLevel(int $level): float
    {
        $rates = $this->getLevelRates();
        return (float) ($rates[$level] ?? 0);
    }

    /**
     * Check if commissions are enabled
     */
    public function isEnabled(): bool
    {
        return (bool) $this->getSetting('commission_enabled', true);
    }

    /**
     * Calculate commission base amount from purchase price
     */
    public function calculateBaseAmount(float $purchaseAmount): float
    {
        return $purchaseAmount * ($this->getBasePercentage() / 100);
    }

    /**
     * Calculate commission amount for a level
     */
    public function calculateCommission(
        float $purchaseAmount,
        int $level,
        bool $referrerHasKit = true
    ): array {
        $basePercentage = $this->getBasePercentage();
        $baseAmount = $purchaseAmount * ($basePercentage / 100);
        $levelRate = $this->getRateForLevel($level);
        $nonKitMultiplier = $referrerHasKit ? 1.0 : $this->getNonKitMultiplier();
        
        $commissionAmount = $baseAmount * ($levelRate / 100) * $nonKitMultiplier;

        return [
            'purchase_amount' => $purchaseAmount,
            'base_percentage' => $basePercentage,
            'base_amount' => $baseAmount,
            'level' => $level,
            'level_rate' => $levelRate,
            'referrer_has_kit' => $referrerHasKit,
            'non_kit_multiplier' => $nonKitMultiplier,
            'commission_amount' => round($commissionAmount, 2),
        ];
    }

    /**
     * Get all commission settings for admin display
     */
    public function getAllSettings(): array
    {
        return [
            'base_percentage' => $this->getBasePercentage(),
            'non_kit_multiplier_percentage' => $this->getSetting('commission_non_kit_multiplier', 50),
            'level_rates' => $this->getLevelRates(),
            'enabled' => $this->isEnabled(),
            'total_payout_percentage' => array_sum($this->getLevelRates()),
        ];
    }

    /**
     * Update commission settings
     */
    public function updateSettings(array $settings): void
    {
        if (isset($settings['base_percentage'])) {
            $this->saveSetting('commission_base_percentage', (float) $settings['base_percentage']);
        }

        if (isset($settings['non_kit_multiplier_percentage'])) {
            $this->saveSetting('commission_non_kit_multiplier', (float) $settings['non_kit_multiplier_percentage']);
        }

        if (isset($settings['level_rates']) && is_array($settings['level_rates'])) {
            $this->saveSetting('commission_level_rates', $settings['level_rates']);
        }

        if (isset($settings['enabled'])) {
            $this->saveSetting('commission_enabled', (bool) $settings['enabled']);
        }

        $this->clearCache();
    }

    /**
     * Get a setting value
     */
    private function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->getCachedSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Save a setting value
     */
    private function saveSetting(string $key, mixed $value): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($value)]
        );
    }

    /**
     * Get cached settings
     */
    private function getCachedSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $settings = SystemSetting::where('key', 'like', 'commission_%')->get();
            
            $result = [];
            foreach ($settings as $setting) {
                // Value is already decoded by model cast
                $result[$setting->key] = $setting->value;
            }
            
            return $result;
        });
    }

    /**
     * Clear settings cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
