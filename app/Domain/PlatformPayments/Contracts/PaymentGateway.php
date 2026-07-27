<?php

namespace App\Domain\PlatformPayments\Contracts;

use App\Domain\Core\Contracts\ProviderContract;

interface PaymentGateway extends ProviderContract
{
    public function process(float $amount, string $currency, string $reference, array $metadata = []): array;
    public function refund(string $transactionId, float $amount): string;
    public function verify(string $transactionId): array;
    public function query(array $criteria): array;
}
