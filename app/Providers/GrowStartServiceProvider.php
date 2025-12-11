<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use App\Domain\GrowStart\Repositories\TaskRepositoryInterface;
use App\Domain\GrowStart\Repositories\StageRepositoryInterface;
use App\Infrastructure\GrowStart\Repositories\EloquentJourneyRepository;
use App\Infrastructure\GrowStart\Repositories\EloquentTaskRepository;
use App\Infrastructure\GrowStart\Repositories\EloquentStageRepository;

class GrowStartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(JourneyRepositoryInterface::class, EloquentJourneyRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, EloquentTaskRepository::class);
        $this->app->bind(StageRepositoryInterface::class, EloquentStageRepository::class);
    }

    public function boot(): void
    {
        // Load GrowStart routes
        $this->loadRoutesFrom(base_path('routes/growstart.php'));
    }
}
