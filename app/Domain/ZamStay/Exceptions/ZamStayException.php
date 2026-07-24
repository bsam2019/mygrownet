<?php

namespace App\Domain\ZamStay\Exceptions;

class ZamStayException extends \RuntimeException
{
    public static function notFound(string $entity): self
    {
        return new self("{$entity} not found.");
    }

    public static function unauthorized(): self
    {
        return new self('You are not authorized to perform this action.');
    }

    public static function invalidOperation(string $message): self
    {
        return new self($message);
    }
}
