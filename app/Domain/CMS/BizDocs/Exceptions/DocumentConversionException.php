<?php

namespace App\Domain\CMS\BizDocs\Exceptions;

class DocumentConversionException extends \Exception
{
    public static function missingRequiredField(string $field, string $documentType): self
    {
        return new self("Missing required field '{$field}' for {$documentType}");
    }

    public static function invalidData(string $message): self
    {
        return new self("Invalid document data: {$message}");
    }

    public static function conversionFailed(string $documentType, \Throwable $previous): self
    {
        return new self(
            "Failed to convert {$documentType}: " . $previous->getMessage(),
            0,
            $previous
        );
    }
}
