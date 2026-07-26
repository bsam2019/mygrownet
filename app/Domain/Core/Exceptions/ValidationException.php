<?php

namespace App\Domain\Core\Exceptions;

class ValidationException extends \RuntimeException implements NonRetryableExceptionInterface
{
    public function __construct(string $message = 'Validation failed', int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
