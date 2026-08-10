<?php

namespace App\Providers;

use App\Domain\GrowBuilder\Repositories\OrderRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\ProductRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\TemplateRepositoryInterface;
use App\Domain\GrowBuilder\Services\GrowBuilderBillingIntegration;
use App\Domain\GrowBuilder\Contracts\AiGeneratorEngineInterface;
use App\Domain\GrowBuilder\Contracts\SsgDeploymentEngineInterface;
use App\Infrastructure\Persistence\Repositories\GrowBuilder\EloquentOrderRepository;
use App\Infrastructure\Persistence\Repositories\GrowBuilder\EloquentPageRepository;
use App\Infrastructure\Persistence\Repositories\GrowBuilder\EloquentProductRepository;
use App\Infrastructure\Persistence\Repositories\GrowBuilder\EloquentSiteRepository;
use App\Infrastructure\Persistence\Repositories\GrowBuilder\EloquentTemplateRepository;
use App\Services\GrowBuilder\BusinessProfileService;
use App\Services\GrowBuilder\PageRevisionService;
use App\Services\GrowBuilder\QrCodeService;
use App\Services\GrowBuilder\RetentionDigestService;
use App\Services\GrowBuilder\SeoSchemaService;
use App\Services\GrowBuilder\SsgPublisherService;
use App\Services\GrowBuilder\TemplateVersionService;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\Core\Services\ModuleDiscovery;
use Illuminate\Support\ServiceProvider;

class GrowBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Repository bindings ──
        $this->app->bind(SiteRepositoryInterface::class, EloquentSiteRepository::class);
        $this->app->bind(PageRepositoryInterface::class, EloquentPageRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(TemplateRepositoryInterface::class, EloquentTemplateRepository::class);

        $this->app->singleton(GrowBuilderBillingIntegration::class);

        // ── Strategy Pattern: AI Generator Engine ──
        // Resolves the active AI provider from config('services.ai.growbuilder_provider')
        // or falls back to config('services.ai.provider').
        // To add a new provider: implement AiGeneratorEngineInterface and change the config value.
        $this->app->bind(AiGeneratorEngineInterface::class, function ($app) {
            // GrowBuilder delegates AI generation to the platform-wide AIContentService
            // which already handles Gemini, OpenAI, Groq, NVIDIA, and Mock providers.
            // The interface contract is available for future dedicated generator implementations.
            return $app->make(\App\Services\GrowBuilder\AIContentService::class);
        });

        // ── Strategy Pattern: SSG Deployment Engine ──
        // 'local' driver = storage/app/ssg/ (development & staging)
        // 'cloudflare' or 's3' driver = CDN deployment (production)
        $this->app->singleton(SsgDeploymentEngineInterface::class, function ($app) {
            // Currently backed by SsgPublisherService local driver
            return $app->make(SsgPublisherService::class);
        });

        // ── Domain Services (singletons for performance) ──
        $this->app->singleton(BusinessProfileService::class);
        $this->app->singleton(PageRevisionService::class);
        $this->app->singleton(SeoSchemaService::class);
        $this->app->singleton(QrCodeService::class);
        $this->app->singleton(TemplateVersionService::class);
        $this->app->singleton(RetentionDigestService::class);
        $this->app->singleton(SsgPublisherService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/growbuilder'));

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'growbuilder',
            name: 'GrowBuilder',
            version: '2.0.0',
            category: 'business',
            description: 'AI-Powered Business Digital Presence Platform — website builder, e-commerce, Business Profile, SSG, QR codes, and analytics',
            supportsSubdomain: true,
            capabilities: ['site_builder', 'ecommerce', 'media', 'ai_tools', 'payments', 'business_profile', 'ssg', 'qr_codes', 'seo'],
            permissions: ['manage_sites', 'manage_products', 'manage_media', 'manage_payments', 'manage_business_profile'],
            settings: ['default_template', 'media_storage_limit', 'ai_credit_limit', 'ssg_driver'],
        ));
    }
}
