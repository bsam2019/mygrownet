<?php

namespace App\Domain\GrowStream;

use App\Domain\Core\Services\ModuleDiscovery;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\GrowStream\Infrastructure\Events\VideoProcessingCompleted;
use App\Domain\GrowStream\Infrastructure\Events\VideoProcessingFailed;
use App\Domain\GrowStream\Infrastructure\Events\VideoUploaded;
use App\Domain\GrowStream\Infrastructure\Listeners\HandleVideoUpload;
use App\Domain\GrowStream\Infrastructure\Listeners\NotifyVideoProcessingCompleted;
use App\Domain\GrowStream\Infrastructure\Listeners\NotifyVideoProcessingFailed;
use App\Domain\GrowStream\Infrastructure\Providers\CloudflareStreamProvider;
use App\Domain\GrowStream\Infrastructure\Providers\DigitalOceanSpacesProvider;
use App\Domain\GrowStream\Infrastructure\Providers\VideoProviderFactory;
use App\Domain\GrowStream\Infrastructure\Providers\VideoProviderInterface;
use App\Domain\GrowStream\Presentation\Console\Commands\AggregateAnalyticsCommand;
use App\Domain\GrowStream\Presentation\Console\Commands\CalculateRevenueCommand;
use App\Domain\GrowStream\Presentation\Console\Commands\CleanupOldAnalyticsCommand;
use App\Domain\GrowStream\Presentation\Console\Commands\GrowStreamStatsCommand;
use App\Domain\GrowStream\Presentation\Console\Commands\ProcessPayoutsCommand;
use App\Domain\GrowStream\Presentation\Console\Commands\ProcessPendingVideosCommand;
use App\Domain\GrowStream\Repositories\CreatorAgreementRepositoryInterface;
use App\Domain\GrowStream\Repositories\CreatorEarningRepositoryInterface;
use App\Domain\GrowStream\Repositories\CreatorPayoutRepositoryInterface;
use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use App\Domain\GrowStream\Repositories\CreatorSubscriptionRepositoryInterface;
use App\Domain\GrowStream\Repositories\CreatorTipRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoCategoryRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRentalRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoSeriesRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoTagRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoViewRepositoryInterface;
use App\Domain\GrowStream\Repositories\WatchHistoryRepositoryInterface;
use App\Domain\GrowStream\Repositories\WatchlistRepositoryInterface;
use App\Domain\GrowStream\Services\AccessControlService;
use App\Domain\GrowStream\Services\CreatorSubscriptionService;
use App\Domain\GrowStream\Services\PayoutService;
use App\Domain\GrowStream\Services\RentalService;
use App\Domain\GrowStream\Services\RevenuePoolService;
use App\Domain\GrowStream\Services\TipService;
use App\Domain\GrowStream\Services\WatchService;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentCreatorAgreementRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentCreatorEarningRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentCreatorPayoutRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentCreatorProfileRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentCreatorSubscriptionRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentCreatorTipRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentVideoCategoryRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentVideoRentalRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentVideoRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentVideoSeriesRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentVideoTagRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentVideoViewRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentWatchHistoryRepository;
use App\Infrastructure\Persistence\Repositories\GrowStream\EloquentWatchlistRepository;
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
            return new DigitalOceanSpacesProvider;
        });

        $this->app->singleton(CloudflareStreamProvider::class, function ($app) {
            return new CloudflareStreamProvider;
        });

        // Register repository bindings
        $this->app->bind(VideoRepositoryInterface::class, EloquentVideoRepository::class);
        $this->app->bind(VideoSeriesRepositoryInterface::class, EloquentVideoSeriesRepository::class);
        $this->app->bind(VideoCategoryRepositoryInterface::class, EloquentVideoCategoryRepository::class);
        $this->app->bind(CreatorProfileRepositoryInterface::class, EloquentCreatorProfileRepository::class);
        $this->app->bind(WatchHistoryRepositoryInterface::class, EloquentWatchHistoryRepository::class);
        $this->app->bind(WatchlistRepositoryInterface::class, EloquentWatchlistRepository::class);
        $this->app->bind(VideoViewRepositoryInterface::class, EloquentVideoViewRepository::class);
        $this->app->bind(VideoTagRepositoryInterface::class, EloquentVideoTagRepository::class);
        $this->app->bind(CreatorAgreementRepositoryInterface::class, EloquentCreatorAgreementRepository::class);
        $this->app->bind(CreatorEarningRepositoryInterface::class, EloquentCreatorEarningRepository::class);
        $this->app->bind(CreatorPayoutRepositoryInterface::class, EloquentCreatorPayoutRepository::class);
        $this->app->bind(CreatorSubscriptionRepositoryInterface::class, EloquentCreatorSubscriptionRepository::class);
        $this->app->bind(CreatorTipRepositoryInterface::class, EloquentCreatorTipRepository::class);
        $this->app->bind(VideoRentalRepositoryInterface::class, EloquentVideoRentalRepository::class);

        $this->app->singleton(AccessControlService::class);
        $this->app->singleton(RentalService::class);
        $this->app->singleton(CreatorSubscriptionService::class);
        $this->app->singleton(TipService::class);
        $this->app->singleton(RevenuePoolService::class);
        $this->app->singleton(PayoutService::class);

        // WatchService has optional constructor dependencies (AccessControlService,
        // RentalService). Without explicit bindings the container substitutes their
        // default null values, silently disabling access gating. Build it explicitly.
        $this->app->bind(WatchService::class, function ($app) {
            return new WatchService(
                $app->make(VideoRepositoryInterface::class),
                $app->make(WatchHistoryRepositoryInterface::class),
                $app->make(VideoViewRepositoryInterface::class),
                $app->make(AccessControlService::class),
                $app->make(RentalService::class),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(database_path('migrations/growstream'));

        // Publish config
        $this->publishes([
            base_path('config/growstream.php') => config_path('growstream.php'),
        ], 'growstream-config');

        // Load API routes
        $this->loadRoutesFrom(__DIR__.'/Presentation/routes/api.php');
        // Web routes are loaded from routes/growstream.php (bootstrap/app.php)
        // to support both main-domain (/growstream) and subdomain (growstream.mygrownet.com)
        // $this->loadRoutesFrom(__DIR__ . '/Presentation/routes/web.php');

        // Register event listeners
        $this->registerEventListeners();

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                AggregateAnalyticsCommand::class,
                ProcessPendingVideosCommand::class,
                CleanupOldAnalyticsCommand::class,
                GrowStreamStatsCommand::class,
                CalculateRevenueCommand::class,
                ProcessPayoutsCommand::class,
            ]);
        }

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'growstream',
            name: 'GrowStream',
            version: '1.0.0',
            category: 'consumer',
            description: 'Video streaming platform with creator profiles, categories, and analytics',
            capabilities: ['video_streaming', 'media_management', 'analytics'],
            permissions: ['manage_videos', 'manage_creators', 'view_analytics'],
            settings: ['max_upload_size', 'encoding_profile'],
        ));
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
