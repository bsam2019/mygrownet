<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Workshop\Repositories\WorkshopRepository;
use App\Domain\Workshop\Repositories\WorkshopRegistrationRepository;
use App\Infrastructure\Persistence\Repositories\Workshop\EloquentWorkshopRepository;
use App\Infrastructure\Persistence\Repositories\Workshop\EloquentWorkshopRegistrationRepository;

class WorkshopServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            WorkshopRepository::class,
            EloquentWorkshopRepository::class
        );

        $this->app->bind(
            WorkshopRegistrationRepository::class,
            EloquentWorkshopRegistrationRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
