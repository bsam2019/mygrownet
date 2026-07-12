<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Exceptions;

class JourneyNotFoundException extends GrowStartException
{
    protected string $errorCode = 'JOURNEY_NOT_FOUND';

    public function __construct(string $message = 'Journey not found')
    {
        parent::__construct($message);
    }
}
