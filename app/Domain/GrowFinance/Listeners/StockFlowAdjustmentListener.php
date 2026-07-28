<?php

namespace App\Domain\GrowFinance\Listeners;

use App\Domain\Core\Listeners\InboxAware;
use App\Domain\Core\Services\InboxService;
use App\Domain\GrowFinance\Services\AutoJournalingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class StockFlowAdjustmentListener implements ShouldQueue
{
    use InteractsWithQueue, InboxAware;

    public function __construct(
        private AutoJournalingService $autoJournaling,
        private InboxService $inbox,
    ) {}

    public function handle(array $payload): void
    {
        $this->processWithInbox(
            eventId: 'adjustment-' . ($payload['item_id'] ?? '') . '-' . ($payload['occurred_at'] ?? ''),
            eventName: 'stockflow.stock.adjusted.v1',
            payload: $payload,
            publisher: 'stockflow',
            handler: fn() => $this->autoJournaling->onStockAdjusted($payload),
        );
    }

    public function failed(array $payload, \Throwable $e): void
    {
        $this->inbox->markFailed('adjustment-' . ($payload['item_id'] ?? '') . '-' . ($payload['occurred_at'] ?? ''));
        Log::error("StockFlowAdjustmentListener failed", ['error' => $e->getMessage()]);
    }
}
