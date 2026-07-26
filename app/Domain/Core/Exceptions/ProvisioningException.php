<?php

namespace App\Domain\Core\Exceptions;

class ProvisioningException extends \RuntimeException implements NonRetryableExceptionInterface
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
