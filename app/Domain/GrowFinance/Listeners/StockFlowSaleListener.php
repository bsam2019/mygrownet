<?php

namespace App\Domain\GrowFinance\Listeners;

use App\Domain\Core\Listeners\InboxAware;
use App\Domain\Core\Services\InboxService;
use App\Domain\GrowFinance\Services\AutoJournalingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class StockFlowSaleListener implements ShouldQueue
{
    use InteractsWithQueue, InboxAware;

    public function __construct(
        private AutoJournalingService $autoJournaling,
        private InboxService $inbox,
    ) {}

    public function handle(array $payload): void
    {
        $this->processWithInbox(
            eventId: 'sale-' . ($payload['sale_id'] ?? ''),
            eventName: 'stockflow.sale.completed.v1',
            payload: $payload,
            publisher: 'stockflow',
            handler: fn() => $this->autoJournaling->onSaleCompleted($payload),
        );
    }

    public function failed(array $payload, \Throwable $e): void
    {
        $this->inbox->markFailed('sale-' . ($payload['sale_id'] ?? ''));
        Log::error("StockFlowSaleListener failed", ['error' => $e->getMessage()]);
    }
}
