<?php

namespace App\Domain\Module\Listeners;

use App\Domain\Core\Listeners\InboxAware;
use App\Domain\Module\Services\ModuleSubscriptionService;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Events\PaymentCompleted;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Activates a pending module subscription once its payment completes.
 *
 * The shared checkout flow stores the module subscription's provider
 * reference on the payment transaction, so a completed PawaPay payment can
 * be matched back to the subscription row it belongs to.
 */
class ActivateSubscriptionOnPaymentCompleted implements ShouldQueue
{
    use InteractsWithQueue, InboxAware;

    public function __construct(
        private readonly TransactionRepositoryInterface $transactions,
        private readonly ModuleSubscriptionService $subscriptions,
    ) {}

    public function handle(PaymentCompleted $event): void
    {
        $transaction = $this->transactions->findById($event->transactionId);

        if (!$transaction) {
            Log::warning('ActivateSubscriptionOnPaymentCompleted: transaction not found', [
                'transaction_id' => $event->transactionId,
            ]);
            return;
        }

        $reference = $transaction->providerReference();

        if (!$reference) {
            return;
        }

        $this->processWithInbox(
            eventId: 'subscription-payment-' . $transaction->id(),
            eventName: $event->eventName(),
            payload: $transaction->toArray(),
            publisher: $event->publisher,
            handler: function (array $payload) use ($reference) {
                try {
                    $this->subscriptions->activateOnPayment($reference);
                } catch (\DomainException $e) {
                    Log::warning('ActivateSubscriptionOnPaymentCompleted: subscription not activated', [
                        'provider_reference' => $reference,
                        'error' => $e->getMessage(),
                    ]);
                }

                return true;
            },
        );
    }

    public function failed(PaymentCompleted $event, \Throwable $e): void
    {
        Log::error('ActivateSubscriptionOnPaymentCompleted failed', ['error' => $e->getMessage()]);
    }
}
