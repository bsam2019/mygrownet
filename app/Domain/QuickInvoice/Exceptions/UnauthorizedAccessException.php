<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Exceptions;

class UnauthorizedAccessException extends QuickInvoiceException
{
    public function __construct(string $documentId)
    {
        parent::__construct("Unauthorized access to document '{$documentId}'", 403);
    }
}
