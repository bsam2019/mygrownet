<?php

namespace App\Domain\Core\Exceptions;

class ConcurrencyException extends \RuntimeException implements RetryableExceptionInterface
{
    public function __construct(string $message = 'Concurrency conflict', int $code = 409, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function retryDelayMs(int $attempt): int
    {
        return (int) (100 * pow(2, $attempt - 1));
    }
}
