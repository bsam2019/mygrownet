<?php

namespace App\Domain\Core\Exceptions;

class ConfigurationException extends \RuntimeException implements NonRetryableExceptionInterface
{
    public function __construct(string $message = 'Configuration error', int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
