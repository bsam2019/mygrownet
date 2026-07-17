<?php

namespace App\Extensions\Electronics;

use App\Extensions\ExtensionServiceProvider;

class ElectronicsServiceProvider extends ExtensionServiceProvider
{
    public function getCode(): string
    {
        return 'electronics';
    }

    public function getName(): string
    {
        return 'Electronics Extension';
    }

    public function getDescription(): ?string
    {
        return 'Serial number tracking per unit, warranty management (activation, claims, transfers), repair/RMA tracking, and trade-in valuation for electronics and appliance retailers.';
    }

    public function getVersion(): ?string
    {
        return '1.0.0';
    }

    public function getFeatures(): array
    {
        return [
            'serial-tracking',
            'warranty-management',
            'repair-rma',
            'trade-in',
        ];
    }

    public function getDefaultSettings(): array
    {
        return [
            'default_warranty_days' => 365,
            'auto_warranty_activation_on_sale' => true,
            'enable_serial_mandatory' => false,
        ];
    }

    public function boot(): void
    {
        $this->loadExtensionMigrations();
        $this->loadExtensionRoutes();
    }

    public function register(): void
    {
    }
}
