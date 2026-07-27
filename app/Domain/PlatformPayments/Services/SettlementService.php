<?php

namespace App\Domain\PlatformPayments\Services;

use App\Domain\PlatformPayments\Contracts\SettlementProvider;
use App\Domain\PlatformPayments\Entities\Settlement;
use App\Domain\PlatformPayments\Events\SettlementReconciled;
use App\Domain\PlatformPayments\Repositories\SettlementRepositoryInterface;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Domain\Core\Contracts\IntegrationEventDispatcher;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    public function __construct(
        private readonly SettlementRepositoryInterface $settlements,
        private readonly TransactionRepositoryInterface $transactions,
        private readonly SettlementProvider $provider,
        private readonly IntegrationEventDispatcher $events,
    ) {}

    public function importSettlements(string $provider, \DateTimeImmutable $from, \DateTimeImmutable $to): int
    {
        $imported = 0;
        $data = $this->provider->getReconciliationData($from, $to);

        foreach ($data as $record) {
            $existing = $this->settlements->findByProvider($provider, $from, $to);
            $alreadyImported = collect($existing)->first(
                fn(Settlement $s) => $s->toArray()['provider_settlement_id'] === $record['settlement_id']
            );

            if ($alreadyImported) {
                continue;
            }

            $settlement = Settlement::create(
                organizationId: $record['organization_id'],
                provider: $provider,
                providerSettlementId: $record['settlement_id'],
                expectedAmount: (float) $record['expected_amount'],
                actualAmount: (float) $record['actual_amount'],
                feeAmount: (float) $record['fee'],
                currency: $record['currency'],
                settlementDate: new \DateTimeImmutable($record['settlement_date']),
            );

            $this->settlements->save($settlement);
            $imported++;
        }

        return $imported;
    }

    public function reconcileUnsettledTransactions(): int
    {
        $unreconciled = $this->settlements->findUnreconciled();
        $reconciled = 0;

        foreach ($unreconciled as $settlement) {
            try {
                $result = $this->provider->reconcile(
                    settlementId: $settlement->toArray()['provider_settlement_id'],
                );

                if (($result['status'] ?? '') === 'matched') {
                    $settlement->reconcile();
                } else {
                    $settlement->flagDiscrepancy($result['notes'] ?? 'Unmatched settlement');
                }

                DB::transaction(function () use ($settlement) {
                    $this->settlements->save($settlement);
                    $this->events->dispatch(new SettlementReconciled(
                        settlementId: $settlement->id(),
                        organizationId: $settlement->toArray()['organization_id'],
                        expectedAmount: $settlement->expectedAmount(),
                        actualAmount: $settlement->toArray()['actual_amount'],
                        currency: $settlement->toArray()['currency'],
                        status: $settlement->status(),
                    ));
                });

                $reconciled++;
            } catch (\Throwable $e) {
                $settlement->flagDiscrepancy("Reconciliation error: {$e->getMessage()}");
                $this->settlements->save($settlement);
            }
        }

        return $reconciled;
    }
}
