<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use App\Domain\Notification\Repositories\NotificationPreferencesRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Notification\EloquentNotificationRepository;
use App\Infrastructure\Persistence\Eloquent\Notification\EloquentNotificationPreferencesRepository;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\Core\Services\ModuleDiscovery;

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
        $this->loadMigrationsFrom(database_path('migrations/notification'));

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'notification',
            name: 'Notifications',
            version: '1.0.0',
            category: 'platform',
            description: 'User notifications, messages, and communication preferences',
            requiresOrganization: false,
            capabilities: ['notifications', 'messaging'],
            permissions: ['send_notifications', 'manage_templates'],
            settings: ['default_channel', 'retention_days'],
        ));
    }
}
