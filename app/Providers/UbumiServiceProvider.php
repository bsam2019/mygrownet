<?php

namespace App\Providers;

use App\Infrastructure\Ubumi\Eloquent\FamilyModel;
use App\Infrastructure\Ubumi\Eloquent\PersonModel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class UbumiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repository bindings
        $this->app->bind(
            \App\Domain\Ubumi\Repositories\FamilyRepositoryInterface::class,
            \App\Infrastructure\Ubumi\Repositories\EloquentFamilyRepository::class
        );

        $this->app->bind(
            \App\Domain\Ubumi\Repositories\PersonRepositoryInterface::class,
            \App\Infrastructure\Ubumi\Repositories\EloquentPersonRepository::class
        );

        $this->app->bind(
            \App\Domain\Ubumi\Repositories\RelationshipRepositoryInterface::class,
            \App\Infrastructure\Ubumi\Repositories\EloquentRelationshipRepository::class
        );

        $this->app->bind(
            \App\Domain\Ubumi\Repositories\CheckInRepositoryInterface::class,
            \App\Infrastructure\Ubumi\Repositories\EloquentCheckInRepository::class
        );

        // Register services
        $this->app->singleton(\App\Domain\Ubumi\Services\DuplicateDetectionService::class);
        $this->app->singleton(\App\Domain\Ubumi\Services\RelationshipService::class);
        $this->app->singleton(\App\Domain\Ubumi\Services\SlugGeneratorService::class);
        $this->app->singleton(\App\Domain\Ubumi\Services\AlertService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register route model binding for Family using slug
        Route::bind('family', function ($value) {
            return FamilyModel::where('slug', $value)->firstOrFail();
        });

        // Register scoped route model binding for Person using slug within family context
        Route::bind('person', function ($value, $route) {
            $family = $route->parameter('family');
            
            if ($family instanceof FamilyModel) {
                return PersonModel::where('family_id', $family->id)
                    ->where('slug', $value)
                    ->whereNull('deleted_at')
                    ->firstOrFail();
            }
            
            // Fallback for routes without family context
            return PersonModel::where('slug', $value)
                ->whereNull('deleted_at')
                ->firstOrFail();
        });
    }
}
