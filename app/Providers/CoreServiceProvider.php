<?php

namespace App\Providers;

use App\Domain\Core\Contracts\IdentityProvider;
use App\Domain\Core\Services\LaravelIdentityProvider;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IdentityProvider::class, LaravelIdentityProvider::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/core'));
    }
}
