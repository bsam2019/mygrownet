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
        'stockflow.sale.completed.v1' => [
            [\App\Domain\StockFlow\Listeners\ActivityLogListener::class, 'onSaleCompleted'],
            \App\Domain\GrowFinance\Listeners\StockFlowSaleListener::class,
        ],
        'stockflow.stock.adjusted.v1' => [
            [\App\Domain\StockFlow\Listeners\ActivityLogListener::class, 'onStockAdjusted'],
            \App\Domain\GrowFinance\Listeners\StockFlowAdjustmentListener::class,
        ],
        'stockflow.count.finalized.v1' => [
            [\App\Domain\StockFlow\Listeners\ActivityLogListener::class, 'onStockCountFinalized'],
        ],
        'stockflow.cash.discrepancy.v1' => [
            [\App\Domain\StockFlow\Listeners\ActivityLogListener::class, 'onCashDiscrepancyDetected'],
        ],
        'stockflow.purchase.received.v1' => [
            \App\Domain\GrowFinance\Listeners\StockFlowPurchaseListener::class,
        ],
        \App\Domain\StockFlow\Events\PurchaseOrderReceived::class => [
            [\App\Domain\StockFlow\Listeners\ActivityLogListener::class, 'onPurchaseOrderReceived'],
        ],

        // Platform Payments → GrowFinance Auto-Journaling
        'platform.payment.settled.v1' => [
            \App\Domain\GrowFinance\Listeners\PlatformPaymentsListener::class,
        ],

        // Platform Payments → Module Subscription Activation
        \App\Domain\PlatformPayments\Events\PaymentCompleted::class => [
            \App\Domain\Module\Listeners\ActivateSubscriptionOnPaymentCompleted::class,
        ],

        // BMS Events → GrowFinance Auto-Journaling
        'bms.invoice.created.v1' => [
            \App\Domain\GrowFinance\Listeners\BmsInvoiceCreatedListener::class,
        ],
        'bms.invoice.paid.v1' => [
            \App\Domain\GrowFinance\Listeners\BmsInvoicePaidListener::class,
        ],
        \App\Domain\BMS\Core\Events\ExpenseRecorded::class => [
            \App\Domain\GrowFinance\Listeners\BmsExpenseRecordedListener::class,
        ],

        // Platform Core Events
        \App\Domain\Core\Events\OrganizationCreated::class => [
            \App\Domain\StockFlow\Listeners\SyncOrganizationToCompany::class,
            \App\Domain\BMS\Listeners\SyncOrganizationToBmsCompany::class,
            \App\Domain\Core\Listeners\SyncOrganizationToApplicationInstallations::class,
        ],
        \App\Domain\Core\Events\OrganizationArchived::class => [
            \App\Domain\StockFlow\Listeners\SyncOrganizationToCompany::class,
            \App\Domain\BMS\Listeners\SyncOrganizationToBmsCompany::class,
            \App\Domain\Core\Listeners\SyncOrganizationToApplicationInstallations::class,
        ],
        \App\Domain\Core\Events\OrganizationMemberRemoved::class => [
            \App\Domain\Core\Listeners\SyncOrganizationToApplicationInstallations::class,
        ],

        // Application Lifecycle Events
        \App\Domain\Core\Events\ApplicationEnabled::class => [
            \App\Domain\StockFlow\Listeners\SyncOrganizationToCompany::class,
            \App\Domain\Core\Listeners\SyncOrganizationToApplicationInstallations::class,
        ],
        \App\Domain\Core\Events\ApplicationDisabled::class => [
            \App\Domain\Core\Listeners\SyncOrganizationToApplicationInstallations::class,
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
