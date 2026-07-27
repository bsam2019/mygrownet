<?php

namespace App\Domain\FinancialServicesCore\Exceptions;

use App\Domain\Core\Exceptions\IntegrationException;

class FinancialServicesCoreException extends IntegrationException
{
    public static function unsupportedCurrency(string $code): self
    {
        return new self("Unsupported currency: {$code}");
    }

    public static function rateNotFound(string $from, string $to, ?\DateTimeImmutable $date = null): self
    {
        $msg = "Exchange rate not found: {$from} → {$to}";
        if ($date) {
            $msg .= " on {$date->format('Y-m-d')}";
        }
        return new self($msg);
    }

    public static function rateFetchFailed(string $base, string $reason): self
    {
        return new self("Failed to fetch rates for {$base}: {$reason}");
    }
}
