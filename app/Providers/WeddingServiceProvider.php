<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Wedding\Repositories\WeddingEventRepositoryInterface;
use App\Domain\Wedding\Repositories\WeddingVendorRepositoryInterface;
use App\Domain\Wedding\Repositories\WeddingRsvpRepositoryInterface;
use App\Domain\Wedding\Repositories\WeddingGuestRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\Wedding\EloquentWeddingEventRepository;
use App\Infrastructure\Persistence\Repositories\Wedding\EloquentWeddingVendorRepository;
use App\Infrastructure\Persistence\Repositories\Wedding\EloquentWeddingRsvpRepository;
use App\Infrastructure\Persistence\Repositories\Wedding\EloquentWeddingGuestRepository;

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
    }

    public function boot(): void
    {
        //
    }
}