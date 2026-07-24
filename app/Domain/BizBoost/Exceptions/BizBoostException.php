<?php

namespace App\Domain\BizBoost\Exceptions;

class BizBoostException extends \RuntimeException
{
    public static function notFound(string $entity): self
    {
        return new self("{$entity} not found");
    }

    public static function limitReached(string $feature): self
    {
        return new self("Limit reached for: {$feature}");
    }
}