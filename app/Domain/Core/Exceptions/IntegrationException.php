<?php

namespace App\Domain\Core\Exceptions;

class IntegrationException extends \RuntimeException implements RetryableExceptionInterface
{
    public function __construct(string $message = 'Integration error', int $code = 502, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function retryDelayMs(int $attempt): int
    {
        return (int) (1000 * pow(2, $attempt - 1));
    }
}
