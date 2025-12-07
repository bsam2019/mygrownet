<?php

namespace App\Domain\Module\Services;

use Illuminate\Support\Facades\Config;

/**
 * Tier Configuration Service
 * 
 * Centralized service for loading and accessing module tier configurations.
 * All tier limits, features, and pricing are defined in config/modules/*.php
 */
class TierConfigurationService
{
    private array $loadedConfigs = [];

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
     * Get all tiers for a module
     */
    public function getTiers(string $moduleId): array
    {
        $config = $this->getModuleConfig($moduleId);
        return $config['tiers'] ?? [];
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
            'monthly' => $tierConfig['price_monthly'] ?? 0,
            'annual' => $tierConfig['price_annual'] ?? 0,
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
        return in_array($feature, $features, true);
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
        $tierOrder = ['free', 'basic', 'professional', 'business'];
        $tiers = $this->getTiers($moduleId);

        foreach ($tierOrder as $tier) {
            if (!isset($tiers[$tier])) {
                continue;
            }
            
            $features = $tiers[$tier]['features'] ?? [];
            if (in_array($feature, $features, true)) {
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
        $tierOrder = ['free', 'basic', 'professional', 'business'];
        $tiers = $this->getTiers($moduleId);

        foreach ($tierOrder as $tier) {
            if (!isset($tiers[$tier])) {
                continue;
            }
            
            $limit = $tiers[$tier]['limits'][$limitKey] ?? 0;
            
            // -1 means unlimited
            if ($limit === -1 || $limit >= $requiredValue) {
                return $tier;
            }
        }

        return 'business'; // Default to highest tier
    }

    /**
     * Get all tier information formatted for upgrade page display
     */
    public function getAllTiersForDisplay(string $moduleId): array
    {
        $tiers = $this->getTiers($moduleId);
        $config = $this->getModuleConfig($moduleId);
        $featureLabels = $config['feature_labels'] ?? [];
        $limitLabels = $config['limit_labels'] ?? [];
        $result = [];

        foreach ($tiers as $tierKey => $tierConfig) {
            // Convert feature keys to labeled features
            $features = $tierConfig['features'] ?? [];
            $labeledFeatures = [];
            foreach ($features as $feature) {
                $labeledFeatures[] = [
                    'key' => $feature,
                    'label' => $featureLabels[$feature] ?? $this->formatFeatureKey($feature),
                ];
            }
            
            // Convert limit keys to labeled limits
            $limits = $tierConfig['limits'] ?? [];
            $labeledLimits = [];
            foreach ($limits as $limitKey => $limitValue) {
                $labeledLimits[$limitKey] = [
                    'key' => $limitKey,
                    'label' => $limitLabels[$limitKey] ?? $this->formatFeatureKey($limitKey),
                    'value' => $limitValue,
                ];
            }

            $result[$tierKey] = [
                'key' => $tierKey,
                'name' => $tierConfig['name'] ?? ucfirst($tierKey),
                'description' => $tierConfig['description'] ?? '',
                'price_monthly' => $tierConfig['price_monthly'] ?? 0,
                'price_annual' => $tierConfig['price_annual'] ?? 0,
                'popular' => $tierConfig['popular'] ?? false,
                'limits' => $limits,
                'labeled_limits' => $labeledLimits,
                'features' => $features,
                'labeled_features' => $labeledFeatures,
                'reports' => $tierConfig['reports'] ?? [],
            ];
        }

        return $result;
    }
    
    /**
     * Format a feature key into a human-readable label
     */
    private function formatFeatureKey(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }

    /**
     * Get usage metric definitions for a module
     */
    public function getUsageMetrics(string $moduleId): array
    {
        $config = $this->getModuleConfig($moduleId);
        return $config['usage_metrics'] ?? [];
    }

    /**
     * Check if a limit is unlimited (-1)
     */
    public function isUnlimited(int $limit): bool
    {
        return $limit === -1;
    }

    /**
     * Check if a feature/limit is not available (0)
     */
    public function isNotAvailable(int $limit): bool
    {
        return $limit === 0;
    }
}
