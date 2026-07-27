<?php

namespace App\Domain\PlatformPayments\Infrastructure;

use App\Domain\PlatformPayments\Contracts\SettlementProvider;
use App\Domain\Core\Contracts\ProviderContract;

class SettlementProviderImpl implements SettlementProvider
{
    public function capability(): string
    {
        return 'settlement_reconciliation';
    }

    public function getReconciliationData(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        throw new \RuntimeException('SettlementProviderImpl::getReconciliationData() not implemented — delegate to gateway adapter');
    }

    public function getSettlementReports(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        throw new \RuntimeException('SettlementProviderImpl::getSettlementReports() not implemented — delegate to gateway adapter');
    }

    public function reconcile(string $settlementId): array
    {
        throw new \RuntimeException('SettlementProviderImpl::reconcile() not implemented — delegate to gateway adapter');
    }
}
