<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Exceptions;

class InsufficientAccessException extends GrowStreamException
{
    public static function accessDenied(): self
    {
        return new self('Access denied');
    }
}
