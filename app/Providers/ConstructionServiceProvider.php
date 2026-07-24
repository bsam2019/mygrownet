<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ConstructionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/construction'));
    }
}
