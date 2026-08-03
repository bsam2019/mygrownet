<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Exceptions;

class VideoNotFoundException extends GrowStreamException
{
    public static function notFound(): self
    {
        return new self('Video not found');
    }

    public static function forId(int $id): self
    {
        return new self("Video not found: {$id}");
    }

    public static function forSlug(string $slug): self
    {
        return new self("Video not found by slug: {$slug}");
    }
}
