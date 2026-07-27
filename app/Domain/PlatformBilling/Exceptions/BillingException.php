<?php

namespace App\Domain\PlatformBilling\Exceptions;

use App\Domain\Core\Exceptions\IntegrationException;

class BillingException extends IntegrationException
{
    public static function subscriptionNotFound(int $id): self
    {
        return new self("Subscription not found: {$id}");
    }

    public static function planNotFound(int $id): self
    {
        return new self("Plan not found: {$id}");
    }

    public static function invalidTransition(string $from, string $to): self
    {
        return new self("Cannot transition subscription from {$from} to {$to}");
    }

    public static function invoiceGenerationFailed(string $reason): self
    {
        return new self("Invoice generation failed: {$reason}");
    }

    public static function paymentProcessingFailed(string $reason): self
    {
        return new self("Payment processing failed: {$reason}");
    }
}
