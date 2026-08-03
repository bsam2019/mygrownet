<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Exceptions;

class ProcessingFailedException extends GrowStreamException
{
    public static function withMessage(string $message): self
    {
        return new self($message);
    }
}
