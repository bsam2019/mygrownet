<?php

namespace App\Providers;

use App\Domain\Workspace\Services\AppLaunchService;
use App\Domain\Workspace\Services\ApplicationAccessService;
use App\Domain\Workspace\Services\ContextResolverService;
use App\Domain\Workspace\Services\DomainResolverService;
use App\Domain\Workspace\Services\OrganizationAccessService;
use Illuminate\Support\ServiceProvider;

class WorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DomainResolverService::class);
        $this->app->singleton(ContextResolverService::class);
        $this->app->singleton(ApplicationAccessService::class);
        $this->app->singleton(OrganizationAccessService::class);
        $this->app->singleton(AppLaunchService::class);
    }

    public function boot(): void
    {
    }
}
