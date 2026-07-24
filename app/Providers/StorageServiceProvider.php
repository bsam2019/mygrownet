<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Storage\Repositories\StorageFileRepositoryInterface;
use App\Domain\Storage\Repositories\StorageFolderRepositoryInterface;
use App\Infrastructure\Storage\Persistence\Repositories\EloquentStorageFileRepository;
use App\Infrastructure\Storage\Persistence\Repositories\EloquentStorageFolderRepository;
use App\Infrastructure\Storage\Persistence\Repositories\EloquentStorageSubscriptionRepository;
use App\Infrastructure\Storage\Persistence\Repositories\EloquentFileShareRepository;
use App\Domain\Storage\Repositories\StorageSubscriptionRepositoryInterface;
use App\Domain\Storage\Repositories\FileShareRepositoryInterface;

class StorageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StorageFileRepositoryInterface::class, EloquentStorageFileRepository::class);
        $this->app->bind(StorageFolderRepositoryInterface::class, EloquentStorageFolderRepository::class);
        $this->app->bind(StorageSubscriptionRepositoryInterface::class, EloquentStorageSubscriptionRepository::class);
        $this->app->bind(FileShareRepositoryInterface::class, EloquentFileShareRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/storage'));
    }
}
