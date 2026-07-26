<?php

namespace App\Domain\Core\Exceptions;

class AuthorizationException extends \RuntimeException implements NonRetryableExceptionInterface
{
    public function __construct(string $message = 'Unauthorized', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
