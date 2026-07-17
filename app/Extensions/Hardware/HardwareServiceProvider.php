<?php

namespace App\Extensions\Hardware;

use App\Extensions\ExtensionServiceProvider;

class HardwareServiceProvider extends ExtensionServiceProvider
{
    public function getCode(): string
    {
        return 'hardware';
    }

    public function getName(): string
    {
        return 'Hardware Extension';
    }

    public function getDescription(): ?string
    {
        return 'Kit/assembly management (composite items), tool rentals with deposit tracking, special/custom orders, bulk pricing by length/area, and vendor-managed inventory for hardware stores.';
    }

    public function getVersion(): ?string
    {
        return '1.0.0';
    }

    public function getFeatures(): array
    {
        return [
            'kit-assembly',
            'tool-rentals',
            'special-orders',
            'bulk-pricing',
        ];
    }

    public function getDefaultSettings(): array
    {
        return [
            'default_rental_period_days' => 1,
            'enable_deposit_tracking' => true,
            'auto_special_order_reminder_days' => 14,
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
