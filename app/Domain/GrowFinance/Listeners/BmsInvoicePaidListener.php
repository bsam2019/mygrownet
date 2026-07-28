<?php

namespace App\Domain\GrowFinance\Listeners;

use App\Domain\Core\Listeners\InboxAware;
use App\Domain\Core\Services\InboxService;
use App\Domain\GrowFinance\Services\AutoJournalingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class BmsInvoicePaidListener implements ShouldQueue
{
    use InteractsWithQueue, InboxAware;

    public function __construct(
        private AutoJournalingService $autoJournaling,
        private InboxService $inbox,
    ) {}

    public function handle(array $payload): void
    {
        $this->processWithInbox(
            eventId: 'bms-payment-' . ($payload['invoice_id'] ?? ''),
            eventName: 'bms.invoice.paid.v1',
            payload: $payload,
            publisher: 'bms',
            handler: fn() => $this->autoJournaling->onBmsInvoicePaid($payload),
        );
    }

    public function failed(array $payload, \Throwable $e): void
    {
        $this->inbox->markFailed('bms-payment-' . ($payload['invoice_id'] ?? ''));
        Log::error("BmsInvoicePaidListener failed", ['error' => $e->getMessage()]);
    }
}
