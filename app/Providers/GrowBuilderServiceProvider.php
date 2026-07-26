<?php

namespace App\Providers;

use App\Domain\GrowBuilder\Repositories\OrderRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\ProductRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\TemplateRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\GrowBuilder\EloquentOrderRepository;
use App\Infrastructure\Persistence\Repositories\GrowBuilder\EloquentPageRepository;
use App\Infrastructure\Persistence\Repositories\GrowBuilder\EloquentProductRepository;
use App\Infrastructure\Persistence\Repositories\GrowBuilder\EloquentSiteRepository;
use App\Infrastructure\Persistence\Repositories\GrowBuilder\EloquentTemplateRepository;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\Core\Services\ModuleDiscovery;
use Illuminate\Support\ServiceProvider;

class GrowBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SiteRepositoryInterface::class, EloquentSiteRepository::class);
        $this->app->bind(PageRepositoryInterface::class, EloquentPageRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(TemplateRepositoryInterface::class, EloquentTemplateRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/growbuilder'));

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'growbuilder',
            name: 'GrowBuilder',
            version: '1.0.0',
            category: 'business',
            description: 'Website builder, e-commerce sites, media, AI usage, and payments',
            supportsSubdomain: true,
            capabilities: ['site_builder', 'ecommerce', 'media', 'ai_tools', 'payments'],
            permissions: ['manage_sites', 'manage_products', 'manage_media', 'manage_payments'],
            settings: ['default_template', 'media_storage_limit', 'ai_credit_limit'],
        ));
    }
}
