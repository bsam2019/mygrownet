<?php

namespace App\Providers;

use App\Domain\ZamStay\Repositories\AgentRepositoryInterface;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\Core\Services\ModuleDiscovery;
use App\Domain\ZamStay\Repositories\BookingRepositoryInterface;
use App\Domain\ZamStay\Repositories\PropertyRepositoryInterface;
use App\Domain\ZamStay\Repositories\ReviewRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\ZamStay\EloquentAgentRepository;
use App\Infrastructure\Persistence\Repositories\ZamStay\EloquentBookingRepository;
use App\Infrastructure\Persistence\Repositories\ZamStay\EloquentPropertyRepository;
use App\Infrastructure\Persistence\Repositories\ZamStay\EloquentReviewRepository;
use Illuminate\Support\ServiceProvider;

class ZamStayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PropertyRepositoryInterface::class, EloquentPropertyRepository::class);
        $this->app->bind(BookingRepositoryInterface::class, EloquentBookingRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, EloquentReviewRepository::class);
        $this->app->bind(AgentRepositoryInterface::class, EloquentAgentRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/zamstay'));

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'zamstay',
            name: 'ZamStay',
            version: '1.0.0',
            category: 'business',
            description: 'Property booking platform with properties, bookings, reviews, and agents',
            supportsSubdomain: true,
            capabilities: ['property_booking', 'reservations', 'reviews'],
            permissions: ['manage_properties', 'manage_bookings', 'manage_agents'],
            settings: ['default_currency', 'booking_policy'],
        ));
    }
}
