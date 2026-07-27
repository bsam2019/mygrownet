<?php

namespace App\Domain\PlatformPayments\Infrastructure;

use App\Domain\PlatformPayments\Contracts\PaymentGateway;

class PaymentGatewayImpl implements PaymentGateway
{
    public function capability(): string
    {
        return 'payment_processing';
    }

    public function process(float $amount, string $currency, string $reference, array $metadata = []): array
    {
        throw new \RuntimeException('PaymentGatewayImpl::process() not implemented — delegate to gateway adapter');
    }

    public function refund(string $transactionId, float $amount): string
    {
        throw new \RuntimeException('PaymentGatewayImpl::refund() not implemented — delegate to gateway adapter');
    }

    public function verify(string $transactionId): array
    {
        throw new \RuntimeException('PaymentGatewayImpl::verify() not implemented — delegate to gateway adapter');
    }

    public function query(array $criteria): array
    {
        throw new \RuntimeException('PaymentGatewayImpl::query() not implemented — delegate to gateway adapter');
    }
}
