<?php

namespace App\Domain\Platform\Contracts;

interface PaymentGateway
{
    public function charge(array $params): array;
    public function refund(string $transactionId, ?float $amount = null): array;
    public function verify(string $transactionId): array;
    public function webhook(array $payload): array;
}
