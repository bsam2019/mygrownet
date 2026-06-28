<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Exceptions;

class DocumentNotFoundException extends QuickInvoiceException
{
    public function __construct(string $documentId)
    {
        parent::__construct("Document with ID '{$documentId}' not found", 404);
    }
}
