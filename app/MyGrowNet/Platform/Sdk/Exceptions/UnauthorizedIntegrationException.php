<?php

namespace MyGrowNet\Platform\Sdk\Exceptions;

class UnauthorizedIntegrationException extends \RuntimeException
{
    public function __construct(string $message = 'Unauthorized integration access', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
