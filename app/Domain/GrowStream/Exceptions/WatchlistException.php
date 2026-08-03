<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Exceptions;

class WatchlistException extends GrowStreamException
{
    public static function alreadyAdded(): self
    {
        return new self('Video is already in your watchlist');
    }
}
