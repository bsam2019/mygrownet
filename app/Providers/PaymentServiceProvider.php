<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Payment\Services\PaymentService;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService();
        });
    }

    public function boot(): void
    {
        //
    }
}
