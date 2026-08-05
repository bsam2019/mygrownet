<?php

namespace App\Domain\GrowStream\Infrastructure\Listeners;

use App\Domain\Core\Listeners\InboxAware;
use App\Domain\PlatformPayments\Events\PaymentCompleted;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRentalRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * When a PPV rental payment completes, activate the corresponding video
 * rental so the viewer can watch the video they paid for.
 */
class ActivateVideoRentalOnPaymentCompleted implements ShouldQueue
{
    use InteractsWithQueue, InboxAware;

    public function __construct(
        private readonly TransactionRepositoryInterface $transactions,
        private readonly VideoRentalRepositoryInterface $rentals,
    ) {}

    public function handle(PaymentCompleted $event): void
    {
        $transaction = $this->transactions->findById($event->transactionId);

        if (! $transaction) {
            return;
        }

        $metadata = $transaction->metadata() ?: [];
        $rentalId = $metadata['rental_id'] ?? null;

        if (! $rentalId) {
            return;
        }

        $rental = $this->rentals->findById((int) $rentalId);

        if (! $rental || $rental->status !== 'pending') {
            return;
        }

        $this->processWithInbox(
            eventId: 'rental-payment-' . $event->transactionId,
            eventName: $event->eventName(),
            payload: ['rental_id' => $rentalId],
            publisher: $event->publisher,
            handler: function (array $payload) use ($rental) {
                $this->rentals->update($rental, [
                    'status' => 'active',
                    'granted_at' => now(),
                    'expires_at' => now()->addHours(48),
                ]);

                Log::info('Video rental activated', [
                    'rental_id' => $rental->id,
                    'video_id' => $rental->video_id,
                ]);

                return true;
            },
        );
    }

    public function failed(PaymentCompleted $event, \Throwable $e): void
    {
        Log::error('ActivateVideoRentalOnPaymentCompleted failed', ['error' => $e->getMessage()]);
    }
}
