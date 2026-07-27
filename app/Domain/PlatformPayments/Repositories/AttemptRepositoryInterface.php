<?php

namespace App\Domain\PlatformPayments\Repositories;

use App\Domain\PlatformPayments\Entities\PaymentAttempt;

interface AttemptRepositoryInterface
{
    public function findByTransaction(int $transactionId): array;
    public function findLastAttempt(int $transactionId): ?PaymentAttempt;
    public function save(PaymentAttempt $attempt): PaymentAttempt;
}
