<?php

namespace App\Providers;

use App\Domain\ZamStay\Repositories\AgentRepositoryInterface;
use App\Domain\ZamStay\Repositories\BookingRepositoryInterface;
use App\Domain\ZamStay\Repositories\PropertyRepositoryInterface;
use App\Domain\ZamStay\Repositories\ReviewRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\ZamStay\EloquentAgentRepository;
use App\Infrastructure\Persistence\Repositories\ZamStay\EloquentBookingRepository;
use App\Infrastructure\Persistence\Repositories\ZamStay\EloquentPropertyRepository;
use App\Infrastructure\Persistence\Repositories\ZamStay\EloquentReviewRepository;
use Illuminate\Support\ServiceProvider;

class ZamStayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PropertyRepositoryInterface::class, EloquentPropertyRepository::class);
        $this->app->bind(BookingRepositoryInterface::class, EloquentBookingRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, EloquentReviewRepository::class);
        $this->app->bind(AgentRepositoryInterface::class, EloquentAgentRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/zamstay'));
    }
}
