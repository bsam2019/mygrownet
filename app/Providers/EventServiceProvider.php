<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;
use App\Listeners\SetNotificationModule;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        NotificationSent::class => [
            SetNotificationModule::class,
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
            \App\Listeners\RecordPaymentTransaction::class, // CRITICAL: Record transaction for verified payment
            \App\Listeners\ProcessMLMCommissions::class,
        ],
        
        // CMS Integration Events
        \App\Events\CMS\ExpenseApproved::class => [
            \App\Listeners\SyncApprovedExpenseToTransaction::class, // Sync approved expenses to transactions table
        ],
        \App\Events\CMS\InvoiceCreated::class => [
            \App\Listeners\CMS\NotifyGrowBuilderOfInvoice::class,
            \App\Listeners\CMS\NotifyGrowMarketOfInvoice::class,
            \App\Listeners\CMS\GrowFinanceSync\InvoiceCreatedListener::class, // Sync to GrowFinance
        ],
        \App\Events\CMS\ExpenseCreated::class => [
            \App\Listeners\CMS\GrowFinanceSync\ExpenseCreatedListener::class, // Sync to GrowFinance
        ],
        \App\Events\CMS\PaymentRecorded::class => [
            \App\Listeners\CMS\GrowFinanceSync\PaymentRecordedListener::class, // Sync to GrowFinance
        ],
        \App\Events\CMS\InventoryUpdated::class => [
            \App\Listeners\CMS\SyncInventoryToGrowMarket::class,
        ],

        // Venture Builder Events
        \App\Events\VentureBuilder\VentureInvestmentConfirmed::class => [
            \App\Listeners\VentureBuilder\SendInvestmentConfirmationNotification::class,
        ],
        \App\Events\VentureBuilder\VentureStatusChanged::class => [
            \App\Listeners\VentureBuilder\SendVentureStatusNotification::class,
        ],
        \App\Events\VentureBuilder\VentureFundingCompleted::class => [
            \App\Listeners\VentureBuilder\NotifyVentureFundingComplete::class,
        ],
        \App\Events\VentureBuilder\VentureDividendPaid::class => [
            \App\Listeners\VentureBuilder\SendDividendPaymentNotification::class,
        ],

        // Backup Events
        \Spatie\Backup\Events\BackupWasSuccessful::class => [
            \App\Listeners\SendBackupSuccessNotification::class,
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
