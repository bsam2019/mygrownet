<?php

namespace App\Domain\QuickInvoice\Listeners;

use App\Domain\Core\Listeners\InboxAware;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\PlatformPayments\Events\PaymentCompleted;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionTierRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Mirrors a completed module subscription payment for the quickinvoice module
 * into the legacy QuickInvoice subscription tables.
 *
 * The unified checkout flow stores the module subscription's provider reference
 * on the payment transaction, and activates the row in `module_subscriptions`.
 * QuickInvoice's usage limits (documents_per_month) and dashboard still read
 * from `quick_invoice_user_subscriptions`, so this listener keeps that table in
 * sync whenever a PawaPay payment completes.
 */
class MirrorQuickInvoiceSubscriptionOnPaymentCompleted implements ShouldQueue
{
    use InteractsWithQueue, InboxAware;

    public function __construct(
        private readonly TransactionRepositoryInterface $transactions,
        private readonly ModuleSubscriptionRepositoryInterface $moduleSubscriptions,
        private readonly SubscriptionRepositoryInterface $quickInvoiceSubscriptions,
        private readonly SubscriptionTierRepositoryInterface $quickInvoiceTiers,
    ) {}

    public function handle(PaymentCompleted $event): void
    {
        $transaction = $this->transactions->findById($event->transactionId);

        if (!$transaction) {
            Log::warning('MirrorQuickInvoiceSubscriptionOnPaymentCompleted: transaction not found', [
                'transaction_id' => $event->transactionId,
            ]);
            return;
        }

        $reference = $transaction->providerReference();

        if (!$reference) {
            return;
        }

        $subscription = $this->moduleSubscriptions->findByProviderReference($reference);

        if (!$subscription || $subscription->getModuleId() !== 'quickinvoice') {
            return;
        }

        $this->processWithInbox(
            eventId: 'quickinvoice-subscription-payment-' . $transaction->id(),
            eventName: $event->eventName(),
            payload: $transaction->toArray(),
            publisher: $event->publisher,
            handler: function (array $payload) use ($subscription, $reference) {
                try {
                    $this->mirrorActivatedSubscription($subscription, $reference);
                } catch (\DomainException $e) {
                    Log::warning('MirrorQuickInvoiceSubscriptionOnPaymentCompleted: subscription not mirrored', [
                        'provider_reference' => $reference,
                        'error' => $e->getMessage(),
                    ]);
                }

                return true;
            },
        );
    }

    private function mirrorActivatedSubscription(
        \App\Domain\Module\Entities\ModuleSubscription $subscription,
        string $providerReference,
    ): void {
        $userId = $subscription->getUserId();
        $tierKey = $subscription->getTier();
        $tierName = ucfirst($tierKey);

        $tier = $this->quickInvoiceTiers->findByName($tierName);

        if (!$tier) {
            throw new \DomainException("QuickInvoice tier [{$tierName}] not found");
        }

        $this->quickInvoiceSubscriptions->deactivateByUser($userId);

        $this->quickInvoiceSubscriptions->create([
            'user_id' => $userId,
            'tier_id' => $tier['id'],
            'starts_at' => now(),
            'expires_at' => $subscription->getExpiresAt()?->format('Y-m-d H:i:s') ?? now()->addMonth(),
            'documents_used' => 0,
            'is_active' => true,
            'billing_cycle' => $subscription->getBillingCycle(),
            'last_payment_at' => now(),
            'last_payment_amount' => (float) $subscription->getAmount()->amount(),
            'payment_method' => 'pawapay',
            'payment_reference' => $providerReference,
        ]);
    }

    public function failed(PaymentCompleted $event, \Throwable $e): void
    {
        Log::error('MirrorQuickInvoiceSubscriptionOnPaymentCompleted failed', ['error' => $e->getMessage()]);
    }
}
