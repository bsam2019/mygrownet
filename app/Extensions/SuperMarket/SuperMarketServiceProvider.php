<?php

namespace App\Extensions\SuperMarket;

use App\Extensions\ExtensionServiceProvider;

class SuperMarketServiceProvider extends ExtensionServiceProvider
{
    public function getCode(): string
    {
        return 'supermarket';
    }

    public function getName(): string
    {
        return 'SuperMarket Extension';
    }

    public function getDescription(): ?string
    {
        return 'Weighable items (price per kg), promotions (BOGO, percentage, mix-and-match), deli/butchery portioning, fresh produce spoilage tracking, and loyalty programs for supermarkets and grocery stores.';
    }

    public function getVersion(): ?string
    {
        return '1.0.0';
    }

    public function getFeatures(): array
    {
        return [
            'weighable-items',
            'promotions',
            'fresh-produce',
            'deli-butchery',
            'loyalty-programs',
        ];
    }

    public function getDefaultSettings(): array
    {
        return [
            'default_weight_uom' => 'kg',
            'enable_bogo_promotions' => true,
            'enable_loyalty_points' => true,
            'auto_spoilage_check_days' => 3,
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
