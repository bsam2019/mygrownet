<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Notification\Services\TelegramNotificationService;
use App\Infrastructure\External\TelegramApiService;

class TelegramServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Telegram service interface to implementation
        $this->app->bind(
            TelegramNotificationService::class,
            TelegramApiService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
