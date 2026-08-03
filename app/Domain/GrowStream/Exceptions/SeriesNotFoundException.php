<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Exceptions;

class SeriesNotFoundException extends GrowStreamException
{
    public static function forId(int $id): self
    {
        return new self("Series not found: {$id}");
    }
}
