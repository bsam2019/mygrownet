<?php

namespace App\Domain\GrowFinance\Listeners;

use App\Domain\BMS\Core\Events\ExpenseRecorded;
use App\Domain\Core\Listeners\InboxAware;
use App\Domain\Core\Services\InboxService;
use App\Domain\GrowFinance\Services\AutoJournalingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class BmsExpenseRecordedListener implements ShouldQueue
{
    use InteractsWithQueue, InboxAware;

    public function __construct(
        private AutoJournalingService $autoJournaling,
        private InboxService $inbox,
    ) {}

    public function handle(ExpenseRecorded $event): void
    {
        $payload = $event->toPayload();

        $this->processWithInbox(
            eventId: 'bms-expense-' . $event->expenseId,
            eventName: ExpenseRecorded::NAME,
            payload: $payload,
            publisher: 'bms',
            handler: fn() => $this->autoJournaling->onBmsExpenseRecorded($payload),
        );
    }

    public function failed(ExpenseRecorded $event, \Throwable $e): void
    {
        $this->inbox->markFailed('bms-expense-' . $event->expenseId);
        Log::error("BmsExpenseRecordedListener failed", ['error' => $e->getMessage()]);
    }
}
