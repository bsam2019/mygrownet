<?php

namespace App\Domain\PlatformPayments\Repositories;

use App\Domain\PlatformPayments\Entities\PaymentTransaction;

interface TransactionRepositoryInterface
{
    public function findById(int $id): ?PaymentTransaction;
    public function findByOrganization(int $organizationId): array;
    public function findByProviderTransactionId(string $providerTransactionId): ?PaymentTransaction;
    public function findByReference(string $reference): ?PaymentTransaction;
    public function findPending(): array;
    public function findFailed(): array;
    public function findSettled(): array;
    public function save(PaymentTransaction $transaction): PaymentTransaction;
}
