<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\PaymentServiceProvider::class,
    App\Providers\ProfitSharingServiceProvider::class,
    App\Providers\WorkshopServiceProvider::class,
    App\Providers\LibraryServiceProvider::class,
    App\Providers\StarterKitServiceProvider::class,
    App\Providers\NotificationServiceProvider::class,
    App\Providers\MessagingServiceProvider::class,
    App\Providers\SupportServiceProvider::class,
    App\Providers\EmailMarketingServiceProvider::class,
    App\Providers\TelegramServiceProvider::class,
    App\Providers\InvestorServiceProvider::class,
    // App\Providers\EmployeeServiceProvider::class, // Disabled - causing memory issues
    // App\Providers\EmployeeCacheServiceProvider::class, // Disabled - causing memory issues
    // App\Providers\MLMRepositoryServiceProvider::class, // Disabled - causing memory issues
];
