<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Exceptions;

class InvalidDocumentDataException extends QuickInvoiceException
{
    public function __construct(string $message)
    {
        parent::__construct("Invalid document data: {$message}", 422);
    }
}
