<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Registered Extensions
    |--------------------------------------------------------------------------
    |
    | List extension service provider classes here. Extensions are discovered
    | and registered during application boot. Each extension can add features,
    | routes, migrations, and UI components to StockFlow.
    |
    */
    'extensions' => [
        \App\Extensions\Pharmacy\PharmacyServiceProvider::class,
        \App\Extensions\Manufacturing\ManufacturingServiceProvider::class,
        \App\Extensions\Restaurant\RestaurantServiceProvider::class,
        // \App\Extensions\SuperMarket\SuperMarketServiceProvider::class,
        // \App\Extensions\Hardware\HardwareServiceProvider::class,
        // \App\Extensions\Electronics\ElectronicsServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Extension Discovery Path
    |--------------------------------------------------------------------------
    |
    | If you prefer auto-discovery, extensions placed in this directory will
    | be automatically registered if they extend the base ExtensionServiceProvider.
    |
    */
    'extensions_path' => app_path('Extensions'),
];
