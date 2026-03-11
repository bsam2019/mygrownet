<?php

namespace App\Domain\GrowStream;

use App\Domain\GrowStream\Infrastructure\Events\VideoProcessingCompleted;
use App\Domain\GrowStream\Infrastructure\Events\VideoProcessingFailed;
use App\Domain\GrowStream\Infrastructure\Events\VideoUploaded;
use App\Domain\GrowStream\Infrastructure\Listeners\HandleVideoUpload;
use App\Domain\GrowStream\Infrastructure\Listeners\NotifyVideoProcessingCompleted;
use App\Domain\GrowStream\Infrastructure\Listeners\NotifyVideoProcessingFailed;
use App\Domain\GrowStream\Infrastructure\Providers\DigitalOceanSpacesProvider;
use App\Domain\GrowStream\Infrastructure\Providers\VideoProviderInterface;
use App\Domain\GrowStream\Infrastructure\Providers\VideoProviderFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class GrowStreamServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            base_path('config/growstream.php'),
            'growstream'
        );

        // Register video provider
        $this->app->bind(VideoProviderInterface::class, function ($app) {
            return VideoProviderFactory::make();
        });

        // Register specific providers
        $this->app->singleton(DigitalOceanSpacesProvider::class, function ($app) {
            return new DigitalOceanSpacesProvider();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(database_path('migrations'));

        // Publish config
        $this->publishes([
            base_path('config/growstream.php') => config_path('growstream.php'),
        ], 'growstream-config');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/Presentation/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/Presentation/routes/web.php');

        // Register event listeners
        $this->registerEventListeners();

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Domain\GrowStream\Presentation\Console\Commands\AggregateAnalyticsCommand::class,
                \App\Domain\GrowStream\Presentation\Console\Commands\ProcessPendingVideosCommand::class,
                \App\Domain\GrowStream\Presentation\Console\Commands\CleanupOldAnalyticsCommand::class,
                \App\Domain\GrowStream\Presentation\Console\Commands\GrowStreamStatsCommand::class,
            ]);
        }
    }

    /**
     * Register event listeners for GrowStream
     */
    protected function registerEventListeners(): void
    {
        Event::listen(VideoUploaded::class, HandleVideoUpload::class);
        Event::listen(VideoProcessingCompleted::class, NotifyVideoProcessingCompleted::class);
        Event::listen(VideoProcessingFailed::class, NotifyVideoProcessingFailed::class);
    }
}
