<?php

namespace App\Domain\PlatformPayments\Contracts;

use App\Domain\Core\Contracts\ProviderContract;

interface SettlementProvider extends ProviderContract
{
    public function getReconciliationData(\DateTimeImmutable $from, \DateTimeImmutable $to): array;
    public function getSettlementReports(\DateTimeImmutable $from, \DateTimeImmutable $to): array;
    public function reconcile(string $transactionId): array;
}
