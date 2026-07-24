<?php

namespace App\Domain\Investment\Exceptions;

class InvestmentException extends \RuntimeException
{
    public static function notFound(string $entity): self
    {
        return new self("{$entity} not found.");
    }

    public static function invalidAmount(string $message): self
    {
        return new self($message);
    }

    public static function notAllowed(): self
    {
        return new self('You are not allowed to perform this action.');
    }
}
