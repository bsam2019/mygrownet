<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\BizBoost\Repositories\AdCampaignRepositoryInterface;
use App\Domain\BizBoost\Repositories\AiUsageLogRepositoryInterface;
use App\Domain\BizBoost\Repositories\AnalyticsEventRepositoryInterface;
use App\Domain\BizBoost\Repositories\BusinessRepositoryInterface;
use App\Domain\BizBoost\Repositories\CampaignRepositoryInterface;
use App\Domain\BizBoost\Repositories\CategoryRepositoryInterface;
use App\Domain\BizBoost\Repositories\CustomerRepositoryInterface;
use App\Domain\BizBoost\Repositories\CustomerTagRepositoryInterface;
use App\Domain\BizBoost\Repositories\CustomTemplateRepositoryInterface;
use App\Domain\BizBoost\Repositories\FollowUpReminderRepositoryInterface;
use App\Domain\BizBoost\Repositories\IntegrationRepositoryInterface;
use App\Domain\BizBoost\Repositories\LocationRepositoryInterface;
use App\Domain\BizBoost\Repositories\OmnichannelLogRepositoryInterface;
use App\Domain\BizBoost\Repositories\OrderRepositoryInterface;
use App\Domain\BizBoost\Repositories\PostRepositoryInterface;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use App\Domain\BizBoost\Repositories\QrCodeRepositoryInterface;
use App\Domain\BizBoost\Repositories\SaleRepositoryInterface;
use App\Domain\BizBoost\Repositories\TeamMemberRepositoryInterface;
use App\Domain\BizBoost\Repositories\TemplateRepositoryInterface;
use App\Domain\BizBoost\Services\AiUsageService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Services\CustomerService;
use App\Domain\BizBoost\Services\DashboardService;
use App\Domain\BizBoost\Services\FollowUpReminderService;
use App\Domain\BizBoost\Services\IntegrationService;
use App\Domain\BizBoost\Services\MarketingService;
use App\Domain\BizBoost\Services\ProductService;
use App\Domain\BizBoost\Services\QrCodeService;
use App\Domain\BizBoost\Services\SaleService;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentAdCampaignRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentAiUsageLogRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentAnalyticsEventRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentBusinessRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentCampaignRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentCategoryRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentCustomerRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentCustomerTagRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentCustomTemplateRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentFollowUpReminderRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentIntegrationRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentLocationRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentOmnichannelLogRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentOrderRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentPostRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentProductRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentQrCodeRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentSaleRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentTeamMemberRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloquentTemplateRepository;
use Illuminate\Support\ServiceProvider;

class BizBoostServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BusinessRepositoryInterface::class, EloquentBusinessRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(CustomerTagRepositoryInterface::class, EloquentCustomerTagRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, EloquentSaleRepository::class);
        $this->app->bind(PostRepositoryInterface::class, EloquentPostRepository::class);
        $this->app->bind(TemplateRepositoryInterface::class, EloquentTemplateRepository::class);
        $this->app->bind(CustomTemplateRepositoryInterface::class, EloquentCustomTemplateRepository::class);
        $this->app->bind(CampaignRepositoryInterface::class, EloquentCampaignRepository::class);
        $this->app->bind(IntegrationRepositoryInterface::class, EloquentIntegrationRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, EloquentLocationRepository::class);
        $this->app->bind(TeamMemberRepositoryInterface::class, EloquentTeamMemberRepository::class);
        $this->app->bind(QrCodeRepositoryInterface::class, EloquentQrCodeRepository::class);
        $this->app->bind(FollowUpReminderRepositoryInterface::class, EloquentFollowUpReminderRepository::class);
        $this->app->bind(AiUsageLogRepositoryInterface::class, EloquentAiUsageLogRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(AdCampaignRepositoryInterface::class, EloquentAdCampaignRepository::class);
        $this->app->bind(OmnichannelLogRepositoryInterface::class, EloquentOmnichannelLogRepository::class);
        $this->app->bind(AnalyticsEventRepositoryInterface::class, EloquentAnalyticsEventRepository::class);

        $this->app->singleton(BusinessService::class);
        $this->app->singleton(ProductService::class);
        $this->app->singleton(CustomerService::class);
        $this->app->singleton(SaleService::class);
        $this->app->singleton(MarketingService::class);
        $this->app->singleton(IntegrationService::class);
        $this->app->singleton(FollowUpReminderService::class);
        $this->app->singleton(QrCodeService::class);
        $this->app->singleton(AiUsageService::class);
        $this->app->singleton(DashboardService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/bizboost'));
    }
}