<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use App\Domain\GrowStart\Repositories\TaskRepositoryInterface;
use App\Domain\GrowStart\Repositories\StageRepositoryInterface;
use App\Infrastructure\GrowStart\Repositories\EloquentJourneyRepository;
use App\Infrastructure\GrowStart\Repositories\EloquentTaskRepository;
use App\Infrastructure\GrowStart\Repositories\EloquentStageRepository;
use App\Infrastructure\GrowStart\Repositories\EloquentTemplateRepository;
use App\Infrastructure\GrowStart\Repositories\EloquentBadgeRepository;
use App\Infrastructure\GrowStart\Repositories\EloquentProviderRepository;
use App\Infrastructure\GrowStart\Repositories\EloquentIndustryRepository;
use App\Infrastructure\GrowStart\Repositories\EloquentCountryRepository;
use App\Domain\GrowStart\Repositories\TemplateRepositoryInterface;
use App\Domain\GrowStart\Repositories\BadgeRepositoryInterface;
use App\Domain\GrowStart\Repositories\ProviderRepositoryInterface;
use App\Domain\GrowStart\Repositories\IndustryRepositoryInterface;
use App\Domain\GrowStart\Repositories\CountryRepositoryInterface;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\Core\Services\ModuleDiscovery;

class GrowStartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(JourneyRepositoryInterface::class, EloquentJourneyRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, EloquentTaskRepository::class);
        $this->app->bind(StageRepositoryInterface::class, EloquentStageRepository::class);
        $this->app->bind(TemplateRepositoryInterface::class, EloquentTemplateRepository::class);
        $this->app->bind(BadgeRepositoryInterface::class, EloquentBadgeRepository::class);
        $this->app->bind(ProviderRepositoryInterface::class, EloquentProviderRepository::class);
        $this->app->bind(IndustryRepositoryInterface::class, EloquentIndustryRepository::class);
        $this->app->bind(CountryRepositoryInterface::class, EloquentCountryRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/growstart'));

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'growstart',
            name: 'GrowStart',
            version: '1.0.0',
            category: 'business',
            description: 'Business startup journeys with tasks, stages, badges, and industry presets',
            capabilities: ['journey_management', 'task_management', 'badges', 'onboarding'],
            permissions: ['manage_journeys', 'manage_tasks', 'manage_templates'],
            settings: ['default_industry'],
        ));

        $this->loadRoutesFrom(base_path('routes/growstart.php'));
    }
}
