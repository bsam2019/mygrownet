<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\Core\Services\ModuleDiscovery;

class GeoPamuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/geopamu'));

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'geopamu',
            name: 'GeoPamu Blog',
            version: '1.0.0',
            category: 'consumer',
            description: 'Blog and content publishing platform',
            supportsSubdomain: true,
            capabilities: ['blogging', 'content_management'],
            permissions: ['manage_posts', 'manage_categories'],
        ));
    }
}
