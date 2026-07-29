<?php

namespace App\Domain\GrowFinance\Listeners;

use App\Domain\Core\Events\PlatformEvent;
use App\Domain\Core\Listeners\InboxAware;
use App\Domain\Core\Services\InboxService;
use App\Domain\GrowFinance\Services\AutoJournalingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PlatformPaymentsListener implements ShouldQueue
{
    use InteractsWithQueue, InboxAware;

    public function __construct(
        private AutoJournalingService $autoJournaling,
        private InboxService $inbox,
    ) {}

    public function handle(PlatformEvent $event): void
    {
        $payload = $event->payload;

        $this->processWithInbox(
            eventId: 'payment-' . ($payload['transaction_id'] ?? ''),
            eventName: $event->eventName(),
            payload: $payload,
            publisher: $event->publisher,
            handler: function (array $payload) use ($event) {
                return $this->autoJournaling->onPaymentSettled(
                    organizationId: (int) ($payload['organization_id'] ?? $event->context->organizationId ?? 0),
                    settledAmount: (float) ($payload['settled_amount'] ?? 0),
                    fee: (float) ($payload['fee'] ?? 0),
                    currency: (string) ($payload['currency'] ?? 'ZMW'),
                );
            },
        );
    }

    public function failed(PlatformEvent $event, \Throwable $e): void
    {
        $this->inbox->markFailed('payment-' . ($event->payload['transaction_id'] ?? ''));
        Log::error("PlatformPaymentsListener failed", ['error' => $e->getMessage()]);
    }
}
