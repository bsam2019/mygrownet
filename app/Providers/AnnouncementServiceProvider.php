<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Announcement\Repositories\AnnouncementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Announcement\EloquentAnnouncementRepository;

/**
 * Announcement Service Provider
 * 
 * Binds interfaces to implementations
 */
class AnnouncementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(
            AnnouncementRepositoryInterface::class,
            EloquentAnnouncementRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
