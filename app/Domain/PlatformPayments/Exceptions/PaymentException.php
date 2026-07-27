<?php

namespace App\Domain\PlatformPayments\Exceptions;

use App\Domain\Core\Exceptions\IntegrationException;

class PaymentException extends IntegrationException
{
    public static function processingFailed(string $reason): self
    {
        return new self("Payment processing failed: {$reason}");
    }

    public static function transactionNotFound(int $id): self
    {
        return new self("Transaction not found: {$id}");
    }

    public static function allRetriesExhausted(int $transactionId): self
    {
        return new self("All retries exhausted for transaction: {$transactionId}");
    }

    public static function gatewayNotConfigured(string $gateway): self
    {
        return new self("Gateway not configured: {$gateway}");
    }
}
