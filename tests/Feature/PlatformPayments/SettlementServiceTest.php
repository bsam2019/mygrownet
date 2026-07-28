<?php

namespace Tests\Feature\PlatformPayments;

use App\Domain\Core\Contracts\IntegrationEventDispatcher;
use App\Domain\Core\Events\PlatformEvent;
use App\Domain\PlatformPayments\Contracts\SettlementProvider;
use App\Domain\PlatformPayments\Services\SettlementService;
use App\Domain\PlatformPayments\Repositories\SettlementRepositoryInterface;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementServiceTest extends TestCase
{
    use RefreshDatabase;

    private SettlementService $service;
    private SettlementRepositoryInterface $settlements;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settlements = $this->app->make(SettlementRepositoryInterface::class);
        $transactions = $this->app->make(TransactionRepositoryInterface::class);

        $dispatcher = new class implements IntegrationEventDispatcher {
            public array $events = [];
            public function dispatch(PlatformEvent $event): void {
                $this->events[] = $event;
            }
        };

        $provider = new class implements SettlementProvider {
            public function capability(): string { return 'settlement'; }
            public function getReconciliationData(\DateTimeImmutable $from, \DateTimeImmutable $to): array {
                return [
                    [
                        'settlement_id' => 'stl_prov_1',
                        'organization_id' => 1,
                        'expected_amount' => 1000.00,
                        'actual_amount' => 1000.00,
                        'fee' => 50.00,
                        'currency' => 'ZMW',
                        'settlement_date' => '2026-07-28',
                    ],
                ];
            }
            public function getSettlementReports(\DateTimeImmutable $from, \DateTimeImmutable $to): array {
                return [];
            }
            public function reconcile(string $settlementId): array {
                return ['status' => 'matched'];
            }
        };

        $this->service = new SettlementService($this->settlements, $transactions, $provider, $dispatcher);
    }

    public function test_import_settlements(): void
    {
        $count = $this->service->importSettlements(
            provider: 'pawapay',
            from: new \DateTimeImmutable('2026-07-01'),
            to: new \DateTimeImmutable('2026-07-31'),
        );

        $this->assertEquals(1, $count);

        $all = $this->settlements->findByOrganization(1);
        $this->assertCount(1, $all);
        $this->assertEquals(1000.00, $all[0]->expectedAmount());
    }

    public function test_import_skips_duplicate_settlements(): void
    {
        $this->service->importSettlements('pawapay', new \DateTimeImmutable('2026-07-01'), new \DateTimeImmutable('2026-07-31'));
        $count = $this->service->importSettlements('pawapay', new \DateTimeImmutable('2026-07-01'), new \DateTimeImmutable('2026-07-31'));

        $this->assertEquals(0, $count);
    }

    public function test_reconcile_unsettled(): void
    {
        $this->service->importSettlements('pawapay', new \DateTimeImmutable('2026-07-01'), new \DateTimeImmutable('2026-07-31'));

        $reconciled = $this->service->reconcileUnsettledTransactions();

        $this->assertEquals(1, $reconciled);
    }
}
