<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\PaymentServiceProvider::class,
    App\Providers\ProfitSharingServiceProvider::class,
    App\Providers\WorkshopServiceProvider::class,
    App\Providers\LibraryServiceProvider::class,
    App\Providers\StarterKitServiceProvider::class,
    // App\Providers\EmployeeServiceProvider::class, // Disabled - causing memory issues
    // App\Providers\EmployeeCacheServiceProvider::class, // Disabled - causing memory issues
    // App\Providers\MLMRepositoryServiceProvider::class, // Disabled - causing memory issues
];
