<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Payment\Repositories\MemberPaymentRepositoryInterface;
use App\Domain\Payment\Services\PaymentService;
use App\Infrastructure\Persistence\Repositories\Payment\EloquentMemberPaymentRepository;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(
            MemberPaymentRepositoryInterface::class,
            EloquentMemberPaymentRepository::class
        );
        
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService();
        });
    }

    public function boot(): void
    {
        //
    }
}
