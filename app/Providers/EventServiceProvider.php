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
        
        // BMS Integration Events
        \App\Events\BMS\ExpenseApproved::class => [
            \App\Listeners\SyncApprovedExpenseToTransaction::class, // Sync approved expenses to transactions table
        ],
        \App\Events\BMS\InvoiceCreated::class => [
            \App\Listeners\BMS\NotifyGrowBuilderOfInvoice::class,
            \App\Listeners\BMS\NotifyGrowMarketOfInvoice::class,
            \App\Listeners\BMS\GrowFinanceSync\InvoiceCreatedListener::class, // Sync to GrowFinance
        ],
        \App\Events\BMS\ExpenseCreated::class => [
            \App\Listeners\BMS\GrowFinanceSync\ExpenseCreatedListener::class, // Sync to GrowFinance
        ],
        \App\Events\BMS\PaymentRecorded::class => [
            \App\Listeners\BMS\GrowFinanceSync\PaymentRecordedListener::class, // Sync to GrowFinance
        ],
        \App\Events\BMS\InventoryUpdated::class => [
            \App\Listeners\BMS\SyncInventoryToGrowMarket::class,
        ],

        // Backup Events
        \Spatie\Backup\Events\BackupWasSuccessful::class => [
            \App\Listeners\SendBackupSuccessNotification::class,
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

        // StockFlow Domain Events
        \App\Domain\StockFlow\Events\SaleCompleted::class => [
            [\App\Domain\StockFlow\Listeners\ActivityLogListener::class, 'onSaleCompleted'],
        ],
        \App\Domain\StockFlow\Events\StockAdjusted::class => [
            [\App\Domain\StockFlow\Listeners\ActivityLogListener::class, 'onStockAdjusted'],
        ],
        \App\Domain\StockFlow\Events\PurchaseOrderReceived::class => [
            [\App\Domain\StockFlow\Listeners\ActivityLogListener::class, 'onPurchaseOrderReceived'],
        ],
        \App\Domain\StockFlow\Events\StockCountFinalized::class => [
            [\App\Domain\StockFlow\Listeners\ActivityLogListener::class, 'onStockCountFinalized'],
        ],
        \App\Domain\StockFlow\Events\CashDiscrepancyDetected::class => [
            [\App\Domain\StockFlow\Listeners\ActivityLogListener::class, 'onCashDiscrepancyDetected'],
        ],

        // Platform Core Events
        \App\Domain\Core\Events\OrganizationCreated::class => [
            \App\Domain\StockFlow\Listeners\SyncOrganizationToCompany::class,
            \App\Domain\BMS\Listeners\SyncOrganizationToBmsCompany::class,
        ],
        \App\Domain\Core\Events\OrganizationArchived::class => [
            \App\Domain\StockFlow\Listeners\SyncOrganizationToCompany::class,
            \App\Domain\BMS\Listeners\SyncOrganizationToBmsCompany::class,
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
