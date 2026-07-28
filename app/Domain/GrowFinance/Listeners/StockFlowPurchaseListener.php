<?php

namespace App\Domain\GrowFinance\Listeners;

use App\Domain\Core\Listeners\InboxAware;
use App\Domain\Core\Services\InboxService;
use App\Domain\GrowFinance\Services\AutoJournalingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class StockFlowPurchaseListener implements ShouldQueue
{
    use InteractsWithQueue, InboxAware;

    public function __construct(
        private AutoJournalingService $autoJournaling,
        private InboxService $inbox,
    ) {}

    public function handle(array $payload): void
    {
        $this->processWithInbox(
            eventId: 'purchase-' . ($payload['purchase_order_id'] ?? ''),
            eventName: 'stockflow.purchase.received.v1',
            payload: $payload,
            publisher: 'stockflow',
            handler: fn() => $this->autoJournaling->onPurchaseReceived($payload),
        );
    }

    public function failed(array $payload, \Throwable $e): void
    {
        $this->inbox->markFailed('purchase-' . ($payload['purchase_order_id'] ?? ''));
        Log::error("StockFlowPurchaseListener failed", ['error' => $e->getMessage()]);
    }
}
