<?php

namespace App\Domain\Module\Services;

use App\Infrastructure\Persistence\Eloquent\ModuleIntegrationModel;
use App\Domain\POS\Services\POSService;
use App\Domain\Inventory\Services\InventoryService;

/**
 * Service for managing module integrations
 * 
 * Allows parent modules (GrowBiz, BizBoost) to enable/disable
 * sub-modules (POS, Inventory) and access their services
 */
class ModuleIntegrationService
{
    /**
     * Available integrations
     */
    public const INTEGRATIONS = [
        'pos' => [
            'name' => 'Point of Sale',
            'description' => 'Quick sales recording with shift management',
            'icon' => '🛒',
        ],
        'inventory' => [
            'name' => 'Inventory Management',
            'description' => 'Track stock levels and movements',
            'icon' => '📦',
        ],
    ];

    /**
     * Modules that can have integrations
     */
    public const PARENT_MODULES = ['growbiz', 'bizboost', 'ecommerce'];

    /**
     * Check if an integration is enabled for a user in a specific module
     */
    public function isEnabled(int $userId, string $parentModule, string $integratedModule): bool
    {
        return ModuleIntegrationModel::isEnabled($userId, $parentModule, $integratedModule);
    }

    /**
     * Enable an integration
     */
    public function enable(int $userId, string $parentModule, string $integratedModule, array $settings = []): ModuleIntegrationModel
    {
        return ModuleIntegrationModel::enable($userId, $parentModule, $integratedModule, $settings);
    }

    /**
     * Disable an integration
     */
    public function disable(int $userId, string $parentModule, string $integratedModule): bool
    {
        return ModuleIntegrationModel::disable($userId, $parentModule, $integratedModule);
    }

    /**
     * Get all integrations for a user in a specific module
     */
    public function getIntegrations(int $userId, string $parentModule): array
    {
        $enabled = ModuleIntegrationModel::where('user_id', $userId)
            ->where('parent_module', $parentModule)
            ->where('is_enabled', true)
            ->pluck('integrated_module')
            ->toArray();

        $result = [];
        foreach (self::INTEGRATIONS as $key => $info) {
            $result[$key] = array_merge($info, [
                'key' => $key,
                'is_enabled' => in_array($key, $enabled),
            ]);
        }

        return $result;
    }

    /**
     * Get POS service configured for a specific module context
     */
    public function getPOSService(string $moduleContext, int $userId = null): POSService
    {
        $service = new POSService();
        $service->forModule($moduleContext);
        
        if ($userId) {
            $service->forUser($userId);
        }

        return $service;
    }

    /**
     * Get Inventory service configured for a specific module context
     */
    public function getInventoryService(string $moduleContext = 'inventory', int $userId = null): InventoryService
    {
        $service = new InventoryService();
        $service->forModule($moduleContext);
        
        if ($userId) {
            $service->forUser($userId);
        }

        return $service;
    }

    /**
     * Toggle an integration
     */
    public function toggle(int $userId, string $parentModule, string $integratedModule): bool
    {
        $isEnabled = $this->isEnabled($userId, $parentModule, $integratedModule);
        
        if ($isEnabled) {
            return $this->disable($userId, $parentModule, $integratedModule);
        } else {
            $this->enable($userId, $parentModule, $integratedModule);
            return true;
        }
    }

    /**
     * Get integration settings
     */
    public function getSettings(int $userId, string $parentModule, string $integratedModule): array
    {
        $integration = ModuleIntegrationModel::where('user_id', $userId)
            ->where('parent_module', $parentModule)
            ->where('integrated_module', $integratedModule)
            ->first();

        return $integration?->settings ?? [];
    }

    /**
     * Update integration settings
     */
    public function updateSettings(int $userId, string $parentModule, string $integratedModule, array $settings): bool
    {
        return ModuleIntegrationModel::where('user_id', $userId)
            ->where('parent_module', $parentModule)
            ->where('integrated_module', $integratedModule)
            ->update(['settings' => $settings]) > 0;
    }
}
