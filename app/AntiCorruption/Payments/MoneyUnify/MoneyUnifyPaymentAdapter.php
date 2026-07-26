<?php

namespace App\AntiCorruption\Payments\MoneyUnify;

use App\Domain\Platform\Contracts\PaymentGateway;
use App\Exceptions\IntegrationException;

class MoneyUnifyPaymentAdapter implements PaymentGateway
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiSecret,
    ) {}

    public function charge(array $params): array
    {
        try {
            return ['transaction_id' => 'mu_' . uniqid(), 'status' => 'pending', 'provider' => 'moneyunify'];
        } catch (\Throwable $e) {
            throw new IntegrationException('MoneyUnify charge failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(string $transactionId, ?float $amount = null): array
    {
        return ['transaction_id' => $transactionId, 'status' => 'refund_manual'];
    }

    public function verify(string $transactionId): array
    {
        return ['transaction_id' => $transactionId, 'status' => 'completed'];
    }

    public function webhook(array $payload): array
    {
        return ['event' => $payload['event_type'] ?? 'unknown', 'status' => 'processed'];
    }
}
