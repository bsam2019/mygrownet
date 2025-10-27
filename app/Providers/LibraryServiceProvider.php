<?php

namespace App\Providers;

use App\Domain\Library\Repositories\LibraryResourceRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Library\EloquentLibraryResourceRepository;
use Illuminate\Support\ServiceProvider;

class LibraryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repository Interface to Implementation
        $this->app->bind(
            LibraryResourceRepositoryInterface::class,
            EloquentLibraryResourceRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
