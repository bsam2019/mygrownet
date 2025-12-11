<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Exceptions;

use Exception;

class GrowStartException extends Exception
{
    protected string $errorCode = 'GROWSTART_ERROR';

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
