<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\UserRegistered::class => [
            \App\Listeners\AwardRegistrationPoints::class,
        ],
        \App\Events\UserReferred::class => [
            \App\Listeners\AwardReferralPoints::class,
        ],
        \App\Events\CourseCompleted::class => [
            \App\Listeners\AwardCourseCompletionPoints::class,
        ],
        \App\Events\ProductSold::class => [
            \App\Listeners\AwardProductSalePoints::class,
        ],
        \App\Events\UserLevelAdvanced::class => [
            \App\Listeners\AwardDownlineAdvancementPoints::class,
        ],
        \App\Domain\Payment\Events\PaymentVerified::class => [
            \App\Listeners\ProcessMLMCommissions::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
