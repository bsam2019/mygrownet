<?php

namespace App\Providers;

use App\Domain\StarterKit\Repositories\StarterKitPurchaseRepositoryInterface;
use App\Domain\StarterKit\Repositories\ContentItemRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\StarterKit\EloquentStarterKitPurchaseRepository;
use Illuminate\Support\ServiceProvider;

class StarterKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repository Interfaces to Implementations
        $this->app->bind(
            StarterKitPurchaseRepositoryInterface::class,
            EloquentStarterKitPurchaseRepository::class
        );

        // ContentItem repository will be implemented later
        // $this->app->bind(
        //     ContentItemRepositoryInterface::class,
        //     EloquentContentItemRepository::class
        // );
    }

    public function boot(): void
    {
        //
    }
}
