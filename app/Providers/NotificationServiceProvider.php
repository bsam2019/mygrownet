<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use App\Domain\Notification\Repositories\NotificationPreferencesRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Notification\EloquentNotificationRepository;
use App\Infrastructure\Persistence\Eloquent\Notification\EloquentNotificationPreferencesRepository;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(
            NotificationRepositoryInterface::class,
            EloquentNotificationRepository::class
        );

        $this->app->bind(
            NotificationPreferencesRepositoryInterface::class,
            EloquentNotificationPreferencesRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
