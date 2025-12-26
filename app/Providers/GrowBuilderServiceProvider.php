<?php

namespace App\Providers;

use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\TemplateRepositoryInterface;
use App\Infrastructure\GrowBuilder\Repositories\EloquentPageRepository;
use App\Infrastructure\GrowBuilder\Repositories\EloquentSiteRepository;
use App\Infrastructure\GrowBuilder\Repositories\EloquentTemplateRepository;
use Illuminate\Support\ServiceProvider;

class GrowBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(SiteRepositoryInterface::class, EloquentSiteRepository::class);
        $this->app->bind(PageRepositoryInterface::class, EloquentPageRepository::class);
        $this->app->bind(TemplateRepositoryInterface::class, EloquentTemplateRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
