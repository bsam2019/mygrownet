<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\ProfitSharing\Repositories\QuarterlyProfitShareRepository;
use App\Domain\ProfitSharing\Repositories\MemberProfitShareRepository;
use App\Infrastructure\Persistence\Repositories\ProfitSharing\EloquentQuarterlyProfitShareRepository;
use App\Infrastructure\Persistence\Repositories\ProfitSharing\EloquentMemberProfitShareRepository;

class ProfitSharingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            QuarterlyProfitShareRepository::class,
            EloquentQuarterlyProfitShareRepository::class
        );

        $this->app->bind(
            MemberProfitShareRepository::class,
            EloquentMemberProfitShareRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
