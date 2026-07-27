<?php

namespace App\Domain\PlatformPayments\Services;

use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Events\PaymentRetryScheduled;
use App\Domain\Core\Contracts\IntegrationEventDispatcher;

class RetryOrchestrator
{
    private const BACKOFF_HOURS = [1, 6, 24];

    public function __construct(
        private readonly IntegrationEventDispatcher $events,
    ) {}

    public function scheduleRetry(PaymentTransaction $transaction): void
    {
        $attemptNumber = $transaction->attemptCount();
        $delayHours = $this->getDelayHours($attemptNumber);

        if ($delayHours === null) {
            return;
        }

        $scheduledAt = (new \DateTimeImmutable())->modify("+{$delayHours} hours");

        $this->events->dispatch(new PaymentRetryScheduled(
            transactionId: $transaction->id(),
            organizationId: $transaction->organizationId(),
            attemptNumber: $attemptNumber,
            scheduledAt: $scheduledAt,
        ));
    }

    private function getDelayHours(int $attemptNumber): ?int
    {
        $index = $attemptNumber - 1;

        if ($index < 0 || $index >= count(self::BACKOFF_HOURS)) {
            return null;
        }

        return self::BACKOFF_HOURS[$index];
    }
}
