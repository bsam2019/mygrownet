<?php

namespace App\Domain\LifePlus\Exceptions;

class LifePlusException extends \RuntimeException
{
    public static function notFound(string $entity): self
    {
        return new self("{$entity} not found.");
    }

    public static function notOwned(): self
    {
        return new self('You do not own this resource.');
    }

    public static function limitReached(string $feature): self
    {
        return new self("You have reached the limit for {$feature}.");
    }
}
