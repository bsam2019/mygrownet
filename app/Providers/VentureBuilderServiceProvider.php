<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\VentureBuilder\Repositories\CategoryRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\DividendRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\DocumentRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\InvestmentRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ResolutionRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ShareholderRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ShareTransferRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\UpdateRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VoteRepositoryInterface;
use App\Domain\VentureBuilder\Services\VentureCacheService;
use App\Domain\VentureBuilder\Services\VentureDividendService;
use App\Domain\VentureBuilder\Services\VentureInvestmentService;
use App\Domain\VentureBuilder\Services\VentureKycService;
use App\Domain\VentureBuilder\Services\VentureLockInService;
use App\Domain\VentureBuilder\Services\VentureService;
use App\Domain\VentureBuilder\Services\VentureShareTransferService;
use App\Domain\VentureBuilder\Services\VentureVoteService;
use App\Infrastructure\Persistence\Repositories\VentureBuilder\EloquentCategoryRepository;
use App\Infrastructure\Persistence\Repositories\VentureBuilder\EloquentDividendRepository;
use App\Infrastructure\Persistence\Repositories\VentureBuilder\EloquentDocumentRepository;
use App\Infrastructure\Persistence\Repositories\VentureBuilder\EloquentInvestmentRepository;
use App\Infrastructure\Persistence\Repositories\VentureBuilder\EloquentResolutionRepository;
use App\Infrastructure\Persistence\Repositories\VentureBuilder\EloquentShareholderRepository;
use App\Infrastructure\Persistence\Repositories\VentureBuilder\EloquentShareTransferRepository;
use App\Infrastructure\Persistence\Repositories\VentureBuilder\EloquentUpdateRepository;
use App\Infrastructure\Persistence\Repositories\VentureBuilder\EloquentVentureRepository;
use App\Infrastructure\Persistence\Repositories\VentureBuilder\EloquentVoteRepository;
use Illuminate\Support\ServiceProvider;

class VentureBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(VentureRepositoryInterface::class, EloquentVentureRepository::class);
        $this->app->bind(InvestmentRepositoryInterface::class, EloquentInvestmentRepository::class);
        $this->app->bind(ShareholderRepositoryInterface::class, EloquentShareholderRepository::class);
        $this->app->bind(DividendRepositoryInterface::class, EloquentDividendRepository::class);
        $this->app->bind(ShareTransferRepositoryInterface::class, EloquentShareTransferRepository::class);
        $this->app->bind(ResolutionRepositoryInterface::class, EloquentResolutionRepository::class);
        $this->app->bind(VoteRepositoryInterface::class, EloquentVoteRepository::class);
        $this->app->bind(DocumentRepositoryInterface::class, EloquentDocumentRepository::class);
        $this->app->bind(UpdateRepositoryInterface::class, EloquentUpdateRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);

        $this->app->singleton(VentureService::class);
        $this->app->singleton(VentureLockInService::class);
        $this->app->singleton(VentureKycService::class);
        $this->app->singleton(VentureCacheService::class);
        $this->app->singleton(VentureInvestmentService::class);
        $this->app->singleton(VentureDividendService::class);
        $this->app->singleton(VentureShareTransferService::class);
        $this->app->singleton(VentureVoteService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/venturebuilder'));
    }
}
