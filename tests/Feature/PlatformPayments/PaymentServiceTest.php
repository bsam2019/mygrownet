<?php

namespace Tests\Feature\PlatformPayments;

use App\Domain\Core\Contracts\IntegrationEventDispatcher;
use App\Domain\Core\Events\PlatformEvent;
use App\Domain\Core\ValueObjects\PlatformContext;
use App\Domain\PlatformPayments\Contracts\PaymentGateway;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Entities\TransactionStatus;
use App\Domain\PlatformPayments\Events\PaymentAttemptFailed;
use App\Domain\PlatformPayments\Events\PaymentCompleted;
use App\Domain\PlatformPayments\Events\PaymentFailed;
use App\Domain\PlatformPayments\Events\PaymentInitiated;
use App\Domain\PlatformPayments\Repositories\AttemptRepositoryInterface;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Domain\PlatformPayments\Services\PaymentService;
use App\Domain\PlatformPayments\Services\RetryOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $service;
    private TransactionRepositoryInterface $transactions;
    private AttemptRepositoryInterface $attempts;
    private array $dispatchedEvents = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactions = $this->app->make(TransactionRepositoryInterface::class);
        $this->attempts = $this->app->make(AttemptRepositoryInterface::class);

        $this->dispatchedEvents = [];

        $dispatcher = new class($this->dispatchedEvents) implements IntegrationEventDispatcher {
            public array $events;
            public function __construct(array &$events) { $this->events = &$events; }
            public function dispatch(PlatformEvent $event): void {
                $this->events[] = $event;
            }
        };

        $gateway = new class implements PaymentGateway {
            private int $counter = 0;
            public function capability(): string { return 'payment_gateway'; }
            public function process(float $amount, string $currency, string $reference, array $metadata = []): array {
                $this->counter++;
                return ['transaction_id' => 'gw_txn_' . $this->counter, 'reference' => 'ref_' . $this->counter, 'status' => 'success'];
            }
            public function refund(string $transactionId, float $amount): string {
                return 'refund_ref_1';
            }
            public function verify(string $transactionId): array {
                return ['status' => 'completed'];
            }
            public function query(array $criteria): array {
                return [];
            }
        };

        $retry = new RetryOrchestrator($dispatcher);

        $this->service = new PaymentService(
            transactions: $this->transactions,
            attempts: $this->attempts,
            provider: $gateway,
            retryOrchestrator: $retry,
            events: $dispatcher,
        );
    }

    public function test_initiate_creates_transaction_and_dispatches_event(): void
    {
        $tx = $this->service->initiate(
            organizationId: 1,
            amount: 150.00,
            currency: 'ZMW',
            paymentMethod: PaymentMethod::MTNMoMo,
            provider: 'pawapay',
            metadata: ['order_id' => 42],
        );

        $this->assertNotNull($tx->id());
        $this->assertEquals(TransactionStatus::Initiated, $tx->status());
        $this->assertEquals(150.00, $tx->amount());

        $found = $this->transactions->findById($tx->id());
        $this->assertNotNull($found);
        $this->assertEquals(150.00, $found->amount());

        $hasInitEvent = false;
        foreach ($this->dispatchedEvents as $event) {
            if ($event instanceof PaymentInitiated) {
                $hasInitEvent = true;
                $this->assertEquals(150.00, $event->amount);
            }
        }
        $this->assertTrue($hasInitEvent);
    }

    public function test_process_completes_transaction(): void
    {
        $tx = $this->service->initiate(1, 200.00, 'ZMW', PaymentMethod::AirtelMoney, 'pawapay');

        $result = $this->service->process($tx);

        $this->assertEquals(TransactionStatus::Completed, $result->status());

        $hasCompleted = false;
        foreach ($this->dispatchedEvents as $event) {
            if ($event instanceof PaymentCompleted) {
                $hasCompleted = true;
            }
        }
        $this->assertTrue($hasCompleted);

        $attempts = $this->attempts->findByTransaction($tx->id());
        $this->assertCount(1, $attempts);
        $this->assertEquals('success', $attempts[0]->status());
    }

    public function test_process_failure_retries_before_max(): void
    {
        $gateway = new class implements PaymentGateway {
            public function capability(): string { return 'payment_gateway'; }
            public function process(float $amount, string $currency, string $reference, array $metadata = []): array {
                throw new \RuntimeException('Gateway timeout');
            }
            public function refund(string $transactionId, float $amount): string { return ''; }
            public function verify(string $transactionId): array { return []; }
            public function query(array $criteria): array { return []; }
        };

        $dispatcher = new class implements IntegrationEventDispatcher {
            public array $events = [];
            public function dispatch(PlatformEvent $event): void {
                $this->events[] = $event;
            }
        };

        $retry = new RetryOrchestrator($dispatcher);
        $service = new PaymentService($this->transactions, $this->attempts, $gateway, $retry, $dispatcher);

        $tx = $service->initiate(1, 100, 'ZMW', PaymentMethod::MTNMoMo, 'pawapay');
        $result = $service->process($tx);

        $this->assertEquals(TransactionStatus::Failed, $result->status());
        $this->assertEquals(1, $result->attemptCount());

        $hasAttemptFailed = false;
        $hasRetryScheduled = false;
        foreach ($dispatcher->events as $event) {
            if ($event instanceof PaymentAttemptFailed) $hasAttemptFailed = true;
            if ($event instanceof \App\Domain\PlatformPayments\Events\PaymentRetryScheduled) $hasRetryScheduled = true;
        }
        $this->assertTrue($hasAttemptFailed);
        $this->assertTrue($hasRetryScheduled);
    }

    public function test_process_failure_exhausts_retries(): void
    {
        $gateway = new class implements PaymentGateway {
            public function capability(): string { return 'payment_gateway'; }
            public function process(float $amount, string $currency, string $reference, array $metadata = []): array {
                throw new \RuntimeException('Permanent failure');
            }
            public function refund(string $transactionId, float $amount): string { return ''; }
            public function verify(string $transactionId): array { return []; }
            public function query(array $criteria): array { return []; }
        };

        $dispatcher = new class implements IntegrationEventDispatcher {
            public array $events = [];
            public function dispatch(PlatformEvent $event): void {
                $this->events[] = $event;
            }
        };

        $retry = new RetryOrchestrator($dispatcher);
        $service = new PaymentService($this->transactions, $this->attempts, $gateway, $retry, $dispatcher);

        $tx = $service->initiate(1, 100, 'ZMW', PaymentMethod::MTNMoMo, 'pawapay');
        $tx->markFailed('Attempt 1');
        $tx->markFailed('Attempt 2');
        $tx->markFailed('Attempt 3');
        $this->transactions->save($tx);

        $result = $service->process($tx);
        $this->assertEquals(TransactionStatus::Failed, $result->status());
        $this->assertEquals(4, $result->attemptCount());

        $hasFinalFailure = false;
        foreach ($dispatcher->events as $event) {
            if ($event instanceof PaymentFailed) $hasFinalFailure = true;
        }
        $this->assertTrue($hasFinalFailure);
    }

    public function test_refund_throws_on_non_completed(): void
    {
        $this->expectException(\App\Domain\PlatformPayments\Exceptions\PaymentException::class);

        $tx = PaymentTransaction::create(1, 100, 'ZMW', PaymentMethod::Card, 'stripe');
        $this->service->refund($tx, 50);
    }

    public function test_process_pending_transactions(): void
    {
        $tx1 = $this->service->initiate(1, 100, 'ZMW', PaymentMethod::MTNMoMo, 'pawapay');
        $tx2 = $this->service->initiate(1, 200, 'ZMW', PaymentMethod::AirtelMoney, 'pawapay');

        $initiated = $this->transactions->findPending();
        $this->assertCount(2, $initiated);

        $count = $this->service->processPendingTransactions();
        $this->assertEquals(2, $count);

        $completed = $this->transactions->findPending();
        $this->assertCount(0, $completed);
    }
}
