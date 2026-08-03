<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Exceptions;

class CreatorNotFoundException extends GrowStreamException
{
    public static function alreadyExistsForUser(int $userId): self
    {
        return new self("Creator profile already exists for user {$userId}");
    }

    public static function forId(int $id): self
    {
        return new self("Creator profile not found: {$id}");
    }

    public static function forUser(int $userId): self
    {
        return new self("Creator profile not found for user {$userId}");
    }
}
