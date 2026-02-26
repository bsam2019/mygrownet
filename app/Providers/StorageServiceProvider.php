<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Storage\Repositories\StorageFileRepositoryInterface;
use App\Domain\Storage\Repositories\StorageFolderRepositoryInterface;
use App\Infrastructure\Storage\Persistence\Repositories\EloquentStorageFileRepository;
use App\Infrastructure\Storage\Persistence\Repositories\EloquentStorageFolderRepository;

class StorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(
            StorageFileRepositoryInterface::class,
            EloquentStorageFileRepository::class
        );

        $this->app->bind(
            StorageFolderRepositoryInterface::class,
            EloquentStorageFolderRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
