<?php

namespace Tests\Unit\PlatformPayments;

use App\Domain\Core\Contracts\IntegrationEventDispatcher;
use App\Domain\Core\Events\PlatformEvent;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Events\PaymentRetryScheduled;
use App\Domain\PlatformPayments\Services\RetryOrchestrator;
use PHPUnit\Framework\TestCase;

class RetryOrchestratorTest extends TestCase
{
    private array $dispatchedEvents = [];
    private RetryOrchestrator $orchestrator;

    protected function setUp(): void
    {
        $this->dispatchedEvents = [];

        $dispatcher = new class($this->dispatchedEvents) implements IntegrationEventDispatcher {
            public array $events;
            public function __construct(array &$events) { $this->events = &$events; }
            public function dispatch(PlatformEvent $event): void {
                $this->events[] = $event;
            }
        };

        $this->orchestrator = new RetryOrchestrator($dispatcher);
    }

    private function makeTransaction(int $attemptCount): PaymentTransaction
    {
        $now = new \DateTimeImmutable();
        $tx = PaymentTransaction::reconstitute(
            id: 42,
            organizationId: 1,
            amount: 100.00,
            currency: 'ZMW',
            paymentMethod: 'mtn_momo',
            status: 'failed',
            providerTransactionId: null,
            providerReference: null,
            provider: 'pawapay',
            fee: 0.0,
            settledAmount: null,
            settledAt: null,
            metadata: [],
            failureReason: "Attempt $attemptCount failed",
            attemptCount: $attemptCount,
            createdAt: $now,
            updatedAt: $now,
        );
        return $tx;
    }

    public function test_schedule_retry_attempt_1_dispatches_1_hour(): void
    {
        $tx = $this->makeTransaction(1);
        $this->orchestrator->scheduleRetry($tx);

        $this->assertCount(1, $this->dispatchedEvents);
        $event = $this->dispatchedEvents[0];
        $this->assertInstanceOf(PaymentRetryScheduled::class, $event);
        $this->assertEquals(1, $event->attemptNumber);
    }

    public function test_schedule_retry_attempt_2_dispatches_6_hours(): void
    {
        $tx = $this->makeTransaction(2);
        $this->orchestrator->scheduleRetry($tx);

        $this->assertCount(1, $this->dispatchedEvents);
    }

    public function test_schedule_retry_attempt_3_dispatches_24_hours(): void
    {
        $tx = $this->makeTransaction(3);
        $this->orchestrator->scheduleRetry($tx);

        $this->assertCount(1, $this->dispatchedEvents);
    }

    public function test_schedule_retry_attempt_4_does_not_schedule(): void
    {
        $tx = $this->makeTransaction(4);
        $this->orchestrator->scheduleRetry($tx);

        $this->assertCount(0, $this->dispatchedEvents);
    }
}
