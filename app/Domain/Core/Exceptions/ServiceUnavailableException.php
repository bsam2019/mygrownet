<?php

namespace App\Domain\Core\Exceptions;

class ServiceUnavailableException extends \RuntimeException implements RetryableExceptionInterface
{
    public function __construct(string $message = 'Service unavailable', int $code = 503, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function retryDelayMs(int $attempt): int
    {
        return (int) (5000 * pow(2, $attempt - 1));
    }
}
