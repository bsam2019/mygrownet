<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Exceptions;

class VideoNotAvailableException extends GrowStreamException
{
    public static function notPublished(): self
    {
        return new self('Video is not available for playback');
    }

    public static function notReady(): self
    {
        return new self('Video is not available for playback');
    }
}
