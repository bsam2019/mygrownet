<?php

namespace App\Domain\Module\Services;

use App\Models\ModuleTier;
use App\Models\ModuleDiscount;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

/**
 * Tier Configuration Service
 * 
 * Centralized service for loading and accessing module tier configurations.
 * Reads from database first (admin-configured), falls back to config/modules.php
 */
class TierConfigurationService
{
    private array $loadedConfigs = [];
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Get the full configuration for a module
     */
    public function getModuleConfig(string $moduleId): ?array
    {
        if (!isset($this->loadedConfigs[$moduleId])) {
            $config = Config::get("modules.{$moduleId}");
            $this->loadedConfigs[$moduleId] = $config;
        }

        return $this->loadedConfigs[$moduleId];
    }

    /**
     * Get all tiers for a module (DB first, then config fallback)
     */
    public function getTiers(string $moduleId): array
    {
        $cacheKey = "module_tiers:{$moduleId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($moduleId) {
            // Try database first
            $dbTiers = ModuleTier::forModule($moduleId)
                ->active()
                ->ordered()
                ->with('activeFeatures')
                ->get();

            if ($dbTiers->isNotEmpty()) {
                $tiers = [];
                foreach ($dbTiers as $tier) {
                    $tiers[$tier->tier_key] = $this->formatDbTier($tier);
                }
                return $tiers;
            }

            // Fallback to config
            $config = $this->getModuleConfig($moduleId);
            return $config['subscription_tiers'] ?? $config['tiers'] ?? [];
        });
    }

    /**
     * Format a database tier to match config structure
     */
    private function formatDbTier(ModuleTier $tier): array
    {
        $features = [];
        $limits = [];

        foreach ($tier->activeFeatures as $feature) {
            if ($feature->feature_type === 'boolean' && $feature->value_boolean) {
                $features[$feature->feature_key] = true;
            } elseif ($feature->feature_type === 'limit') {
                $limits[$feature->feature_key] = $feature->value_limit;
                $features[$feature->feature_key] = $feature->value_limit;
            } elseif ($feature->feature_type === 'text') {
                $features[$feature->feature_key] = $feature->value_text;
            }
        }

        return [
            'name' => $tier->name,
            'description' => $tier->description,
            'price' => $tier->price_monthly,
            'price_monthly' => $tier->price_monthly,
            'price_annual' => $tier->price_annual,
            'billing_cycle' => 'monthly',
            'user_limit' => $tier->user_limit,
            'storage_limit_mb' => $tier->storage_limit_mb,
            'features' => $features,
            'limits' => $limits,
            'is_default' => $tier->is_default,
        ];
    }

    /**
     * Get configuration for a specific tier
     */
    public function getTierConfig(string $moduleId, string $tier): ?array
    {
        $tiers = $this->getTiers($moduleId);
        return $tiers[$tier] ?? null;
    }

    /**
     * Get limits for a specific tier
     */
    public function getTierLimits(string $moduleId, string $tier): array
    {
        $tierConfig = $this->getTierConfig($moduleId, $tier);
        return $tierConfig['limits'] ?? [];
    }

    /**
     * Get features for a specific tier
     */
    public function getTierFeatures(string $moduleId, string $tier): array
    {
        $tierConfig = $this->getTierConfig($moduleId, $tier);
        return $tierConfig['features'] ?? [];
    }

    /**
     * Get reports available for a specific tier (for finance modules)
     */
    public function getTierReports(string $moduleId, string $tier): array
    {
        $tierConfig = $this->getTierConfig($moduleId, $tier);
        return $tierConfig['reports'] ?? [];
    }

    /**
     * Get pricing for a specific tier
     */
    public function getTierPricing(string $moduleId, string $tier): array
    {
        $tierConfig = $this->getTierConfig($moduleId, $tier);
        
        return [
            'monthly' => $tierConfig['price_monthly'] ?? $tierConfig['price'] ?? 0,
            'annual' => $tierConfig['price_annual'] ?? (($tierConfig['price'] ?? 0) * 10),
            'currency' => 'ZMW',
        ];
    }

    /**
     * Get a specific limit value for a tier
     * Returns -1 for unlimited, 0 for not available
     */
    public function getLimit(string $moduleId, string $tier, string $limitKey): int
    {
        $limits = $this->getTierLimits($moduleId, $tier);
        return $limits[$limitKey] ?? 0;
    }

    /**
     * Check if a feature is available for a tier
     */
    public function hasFeature(string $moduleId, string $tier, string $feature): bool
    {
        $features = $this->getTierFeatures($moduleId, $tier);
        
        // Handle both array format and key-value format
        if (is_array($features)) {
            if (isset($features[$feature])) {
                return (bool) $features[$feature];
            }
            return in_array($feature, $features, true);
        }
        
        return false;
    }

    /**
     * Check if a report is available for a tier
     */
    public function hasReport(string $moduleId, string $tier, string $report): bool
    {
        $reports = $this->getTierReports($moduleId, $tier);
        return in_array($report, $reports, true);
    }

    /**
     * Get the minimum tier required for a feature
     */
    public function getRequiredTierForFeature(string $moduleId, string $feature): ?string
    {
        $tierOrder = ['free', 'basic', 'professional', 'business', 'premium', 'enterprise'];
        $tiers = $this->getTiers($moduleId);

        foreach ($tierOrder as $tier) {
            if (!isset($tiers[$tier])) {
                continue;
            }
            
            if ($this->hasFeature($moduleId, $tier, $feature)) {
                return $tier;
            }
        }

        return null;
    }

    /**
     * Get the minimum tier required for a specific limit value
     */
    public function getRequiredTierForLimit(string $moduleId, string $limitKey, int $requiredValue): ?string
    {
        $tierOrder = ['free', 'basic', 'professional', 'business', 'premium', 'enterprise'];
        $tiers = $this->getTiers($moduleId);

        foreach ($tierOrder as $tier) {
            if (!isset($tiers[$tier])) {
                continue;
            }
            
            $limit = $tiers[$tier]['limits'][$limitKey] ?? 0;
            
            // -1 or null means unlimited
            if ($limit === -1 || $limit === null || $limit >= $requiredValue) {
                return $tier;
            }
        }

        return 'business'; // Default to highest tier
    }

    /**
     * Get all tiers formatted for display on upgrade pages
     */
    public function getAllTiersForDisplay(string $moduleId): array
    {
        $tiers = $this->getTiers($moduleId);
        $discounts = $this->getApplicableDiscounts($moduleId);
        $displayTiers = [];

        $tierOrder = ['free', 'member', 'basic', 'starter', 'standard', 'professional', 'business', 'premium', 'ecommerce', 'enterprise'];
        $sortOrder = 0;

        foreach ($tierOrder as $tierKey) {
            if (!isset($tiers[$tierKey])) {
                continue;
            }

            $tierConfig = $tiers[$tierKey];
            $monthlyPrice = $tierConfig['price_monthly'] ?? $tierConfig['price'] ?? 0;
            $annualPrice = $tierConfig['price_annual'] ?? ($monthlyPrice * 10);

            // Apply any active discounts
            $discountedMonthly = $monthlyPrice;
            $discountedAnnual = $annualPrice;
            $activeDiscount = null;

            foreach ($discounts as $discount) {
                if ($this->discountAppliesToTier($discount, $tierKey)) {
                    $activeDiscount = $discount;
                    if ($discount['discount_type'] === 'percentage') {
                        $discountedMonthly = $monthlyPrice * (1 - $discount['discount_value'] / 100);
                        $discountedAnnual = $annualPrice * (1 - $discount['discount_value'] / 100);
                    } else {
                        $discountedMonthly = max(0, $monthlyPrice - $discount['discount_value']);
                        $discountedAnnual = max(0, $annualPrice - $discount['discount_value']);
                    }
                    break;
                }
            }

            $displayTiers[$tierKey] = [
                'key' => $tierKey,
                'name' => $tierConfig['name'] ?? ucfirst($tierKey),
                'description' => $tierConfig['description'] ?? '',
                'price_monthly' => $monthlyPrice,
                'price_annual' => $annualPrice,
                'discounted_monthly' => $discountedMonthly,
                'discounted_annual' => $discountedAnnual,
                'has_discount' => $activeDiscount !== null,
                'discount' => $activeDiscount,
                'features' => $this->formatFeaturesForDisplay($tierConfig['features'] ?? []),
                'limits' => $tierConfig['limits'] ?? [],
                'reports' => $tierConfig['reports'] ?? [],
                'user_limit' => $tierConfig['user_limit'] ?? null,
                'storage_limit_mb' => $tierConfig['storage_limit_mb'] ?? null,
                'is_popular' => $tierConfig['popular'] ?? ($tierKey === 'professional'),
                'is_default' => $tierConfig['is_default'] ?? ($tierKey === 'free'),
                'sort_order' => $sortOrder++,
            ];
        }

        return $displayTiers;
    }

    /**
     * Format features for display (convert to human-readable format)
     */
    private function formatFeaturesForDisplay(array $features): array
    {
        $formatted = [];

        foreach ($features as $key => $value) {
            $formatted[] = [
                'key' => $key,
                'name' => $this->formatFeatureKey($key),
                'value' => $value,
                'display' => $this->formatFeatureValue($value),
                'available' => $this->isFeatureAvailable($value),
            ];
        }

        return $formatted;
    }

    /**
     * Format a feature key to human-readable name
     */
    private function formatFeatureKey(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }

    /**
     * Format a feature value for display
     */
    private function formatFeatureValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '✓' : '✗';
        }

        if ($value === -1 || $value === null) {
            return 'Unlimited';
        }

        if (is_numeric($value)) {
            return number_format($value);
        }

        return (string) $value;
    }

    /**
     * Check if a feature value indicates availability
     */
    private function isFeatureAvailable(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === 0 || $value === '0' || $value === false) {
            return false;
        }

        return true;
    }

    /**
     * Check if a limit value represents unlimited
     */
    public function isUnlimited(mixed $value): bool
    {
        return $value === -1 || $value === null || $value === 'unlimited';
    }

    /**
     * Check if a limit value represents not available
     */
    public function isNotAvailable(mixed $value): bool
    {
        return $value === 0 || $value === false || $value === '0';
    }

    /**
     * Get usage metric definitions for a module
     */
    public function getUsageMetrics(string $moduleId): array
    {
        $moduleConfig = $this->getModuleConfig($moduleId);
        
        // Default metrics based on module type
        $defaultMetrics = [
            'transactions' => ['name' => 'Transactions', 'unit' => 'count'],
            'storage' => ['name' => 'Storage Used', 'unit' => 'mb'],
            'users' => ['name' => 'Team Members', 'unit' => 'count'],
        ];

        return $moduleConfig['usage_metrics'] ?? $defaultMetrics;
    }

    /**
     * Get applicable discounts for a module
     */
    public function getApplicableDiscounts(string $moduleId, ?string $tierKey = null): array
    {
        $cacheKey = "module_discounts:{$moduleId}";
        
        $discounts = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($moduleId) {
            return ModuleDiscount::query()
                ->where(function ($query) use ($moduleId) {
                    $query->where('module_id', $moduleId)
                        ->orWhereNull('module_id'); // Global discounts
                })
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('max_uses')
                        ->orWhereColumn('current_uses', '<', 'max_uses');
                })
                ->orderBy('discount_value', 'desc')
                ->get()
                ->toArray();
        });

        if ($tierKey) {
            return array_filter($discounts, fn($d) => $this->discountAppliesToTier($d, $tierKey));
        }

        return $discounts;
    }

    /**
     * Check if a discount applies to a specific tier
     */
    private function discountAppliesToTier(array $discount, string $tierKey): bool
    {
        $appliesTo = $discount['applies_to'] ?? 'all_tiers';
        
        if ($appliesTo === 'all_tiers') {
            return true;
        }

        if ($appliesTo === 'specific_tiers') {
            $tierKeys = $discount['tier_keys'] ?? [];
            return in_array($tierKey, $tierKeys, true);
        }

        return true;
    }

    /**
     * Clear cache for a module's tiers
     */
    public function clearCache(?string $moduleId = null): void
    {
        if ($moduleId) {
            Cache::forget("module_tiers:{$moduleId}");
            Cache::forget("module_discounts:{$moduleId}");
        } else {
            // Clear all module tier caches
            $modules = array_keys(Config::get('modules', []));
            foreach ($modules as $module) {
                if ($module !== 'settings' && $module !== 'categories') {
                    Cache::forget("module_tiers:{$module}");
                    Cache::forget("module_discounts:{$module}");
                }
            }
        }

        // Clear loaded configs
        $this->loadedConfigs = [];
    }

    /**
     * Compare two tiers and return upgrade info
     */
    public function getUpgradeInfo(string $moduleId, string $fromTier, string $toTier): array
    {
        $fromConfig = $this->getTierConfig($moduleId, $fromTier);
        $toConfig = $this->getTierConfig($moduleId, $toTier);

        if (!$fromConfig || !$toConfig) {
            return ['valid' => false, 'error' => 'Invalid tier'];
        }

        $fromPrice = $fromConfig['price_monthly'] ?? $fromConfig['price'] ?? 0;
        $toPrice = $toConfig['price_monthly'] ?? $toConfig['price'] ?? 0;

        $newFeatures = [];
        $toFeatures = $toConfig['features'] ?? [];
        $fromFeatures = $fromConfig['features'] ?? [];

        foreach ($toFeatures as $key => $value) {
            if (!isset($fromFeatures[$key]) || $fromFeatures[$key] !== $value) {
                $newFeatures[$key] = $value;
            }
        }

        return [
            'valid' => true,
            'is_upgrade' => $toPrice > $fromPrice,
            'price_difference' => $toPrice - $fromPrice,
            'new_features' => $newFeatures,
            'from_tier' => $fromConfig,
            'to_tier' => $toConfig,
        ];
    }
}
