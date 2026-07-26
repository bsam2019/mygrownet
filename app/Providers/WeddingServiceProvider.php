<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Wedding\Repositories\WeddingEventRepositoryInterface;
use App\Domain\Wedding\Repositories\WeddingVendorRepositoryInterface;
use App\Domain\Wedding\Repositories\WeddingRsvpRepositoryInterface;
use App\Domain\Wedding\Repositories\WeddingGuestRepositoryInterface;
use App\Domain\Wedding\Repositories\WeddingTemplateRepositoryInterface;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\Core\Services\ModuleDiscovery;
use App\Infrastructure\Persistence\Repositories\Wedding\EloquentWeddingEventRepository;
use App\Infrastructure\Persistence\Repositories\Wedding\EloquentWeddingVendorRepository;
use App\Infrastructure\Persistence\Repositories\Wedding\EloquentWeddingRsvpRepository;
use App\Infrastructure\Persistence\Repositories\Wedding\EloquentWeddingGuestRepository;
use App\Infrastructure\Persistence\Repositories\Wedding\EloquentWeddingTemplateRepository;

class WeddingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(
            WeddingEventRepositoryInterface::class,
            EloquentWeddingEventRepository::class
        );

        $this->app->bind(
            WeddingVendorRepositoryInterface::class,
            EloquentWeddingVendorRepository::class
        );

        $this->app->bind(
            WeddingRsvpRepositoryInterface::class,
            EloquentWeddingRsvpRepository::class
        );

        $this->app->bind(
            WeddingGuestRepositoryInterface::class,
            EloquentWeddingGuestRepository::class
        );

        $this->app->bind(
            WeddingTemplateRepositoryInterface::class,
            EloquentWeddingTemplateRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/wedding'));

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'wedding',
            name: 'Wedding',
            version: '1.0.0',
            category: 'consumer',
            description: 'Wedding website builder with RSVP, guest management, and templates',
            supportsSubdomain: true,
            capabilities: ['wedding_planning', 'website_builder', 'guest_management'],
            permissions: ['manage_weddings', 'manage_guests', 'manage_templates'],
        ));
    }
}