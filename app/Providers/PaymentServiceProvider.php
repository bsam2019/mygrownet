<?php

namespace App\Providers;

use App\Domain\Payment\Repositories\MemberPaymentRepositoryInterface;
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
    }

    public function boot(): void
    {
        //
    }
}
